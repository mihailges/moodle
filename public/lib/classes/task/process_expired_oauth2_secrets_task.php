<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace core\task;

use core\event\oauth2_client_secret_expired;
use core\oauth2\server\entity\client_entity;
use dml_exception;

/**
 * Scheduled task to find all OAuth 2 client secrets that have naturally expired and process them accordingly.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_expired_oauth2_secrets_task extends scheduled_task {
    /**
     * Get a descriptive name for this task.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskprocessexpiredoauth2clientsecrets');
    }

    /**
     * Execute the task.
     *
     * @return void
     * @throws dml_exception
     */
    public function execute(): void {
        global $DB;

        // Ensure that the 'league/oauth2-server' composer package is installed.
        // If it's not installed, OAuth2 authentication and client/secret management is not available, so there's no
        // need to execute this task further.
        $package = \core\di::get(\core\composer::class)->get_package_status('league/oauth2-server');

        if (!$package->installed) {
            return;
        }

        $now = \core\di::get(\core\clock::class)->time();

        // Fetch active secrets belonging to an active client that naturally expired and are pending processing.
        $sql = "SELECT s.id,
                       s.clientidentifier,
                       s.expirytime,
                       c.id AS clientid,
                       c.name AS clientname
                  FROM {oauth2_server_client_secrets} s
                  JOIN {oauth2_server_clients} c ON c.clientidentifier = s.clientidentifier
                 WHERE s.revoked = :secretrevoked
                   AND c.status = :clientstatus
                   AND s.expirytime <= :now
                   AND s.expiredprocessed = :secretexpiredprocessed";

        $params = [
            'secretrevoked' => client_entity::SECRET_REVOKED_NO,
            'clientstatus' => client_entity::STATUS_ACTIVE,
            'now' => $now,
            'secretexpiredprocessed' => client_entity::SECRET_EXPIRED_NOT_PROCESSED,
        ];

        $expiredsecrets = $DB->get_records_sql($sql, $params);

        // Early return if there are no unprocessed expired secrets.
        if (empty($expiredsecrets)) {
            return;
        }

        // Fetch all users that are capable to manage OAuth 2 clients and secrets. These users are eligible recipients of
        // notifications about expired secrets.
        $recipients = $this->get_client_secret_managers();

        foreach ($expiredsecrets as $secret) {
            $this->trigger_secret_expired_event($secret->id, $secret->clientid);

            // If the client has a newer active secret in use, the secret was successfully rotated and the old one was
            // allowed to naturally expire. Since there is no service disruption in this case, notifications are
            // suppressed to avoid unnecessary noise. If a replacement secret in use is not detected, notification should
            // be sent to all relevant users to alert them of the expired secret and potential service disruption.
            $sendnotification = !$this->has_active_replacement_secret($secret->clientidentifier, $secret->id);

            if (!$sendnotification) {
                // Mark the expired secret as processed (notifications suppressed) to avoid repeated evaluation by the
                // cron.
                $DB->set_field(
                    'oauth2_server_client_secrets',
                    'expiredprocessed',
                    client_entity::SECRET_EXPIRED_PROCESSED_NOTIFICATION_SUPPRESSED,
                    ['id' => $secret->id],
                );

                continue;
            }

            // When notifications are sent, wrap operations in a delegated transaction for atomic safety.
            $transaction = $DB->start_delegated_transaction();

            // Mark the expired secret as processed (notifications sent) to avoid repeated evaluation by the cron.
            $DB->set_field(
                'oauth2_server_client_secrets',
                'expiredprocessed',
                client_entity::SECRET_EXPIRED_PROCESSED_NOTIFICATION_SENT,
                ['id' => $secret->id],
            );

            $this->queue_secret_expired_notifications(
                $recipients,
                $secret->clientid,
                $secret->clientname,
                $secret->expirytime,
            );

            $transaction->allow_commit();
        }
    }

    /**
     * Triggers the 'oauth2_client_secret_expired' event for audit logging.
     *
     * @param int $expiredsecretid The ID of the expired secret.
     * @param int $clientid The ID of the client associated to the expired secret.
     * @return void
     */
    private function trigger_secret_expired_event(int $expiredsecretid, int $clientid): void {
        $event = oauth2_client_secret_expired::create([
            'objectid' => $expiredsecretid,
            'context'  => \context_system::instance(),
            'other'    => [
                'clientid' => $clientid,
            ],
        ]);

        $event->trigger();
    }

    /**
     * Whether the client associated to the expired secret has a newer active secret in use.
     *
     * @param string $clientidentifier The client identifier.
     * @param int $expiredsecretid The ID of the expired secret.
     * @return bool
     */
    private function has_active_replacement_secret(string $clientidentifier, int $expiredsecretid): bool {
        global $DB;

        $now = \core\di::get(\core\clock::class)->time();

        return $DB->record_exists_select(
            'oauth2_server_client_secrets',
            'clientidentifier = :clientidentifier
            AND revoked = 0
            AND expirytime > :now
            AND id != :expiredsecretid
            AND lastaccessed IS NOT NULL',
            [
                'clientidentifier' => $clientidentifier,
                'now' => $now,
                'expiredsecretid' => $expiredsecretid,
            ]
        );
    }

    /**
     * Queue adhoc tasks for all eligible recipient users to receive a notification for the expired secret.
     *
     * @param array $recipients List of eligible recipient users.
     * @param int $clientid The ID of the client associated to the expired secret.
     * @param string $clientname The name of the client associated to the expired secret.
     * @param int $secretexpiredtime The timestamp of when the secret expired.
     * @return void
     */
    private function queue_secret_expired_notifications(
        array $recipients,
        int $clientid,
        string $clientname,
        int $secretexpiredtime,
    ): void {
        // Early return if there are no eligible recipient users to notify.
        if (empty($recipients)) {
            return;
        }

        // Queue an adhoc task per recipient user.
        foreach ($recipients as $user) {
            $adhoctask = new \core\task\send_oauth2_secret_expired_notification_adhoc_task();
            $adhoctask->set_custom_data([
                'clientid' => $clientid,
                'clientname' => $clientname,
                'secretexpiredtime' => $secretexpiredtime,
            ]);
            // Ensures that the notification is sent in the recipient user's localized context.
            $adhoctask->set_userid($user->id);

            manager::queue_adhoc_task($adhoctask);
        }
    }

    /**
     * Return users with explicit capability to manage OAuth 2 clients and secrets.
     *
     * @return array List of users.
     */
    private function get_client_secret_managers(): array {
        $users = get_users_by_capability(
            \context_system::instance(),
            'moodle/site:manageoauth2clients',
            'u.*',
        );

        // Primary site administrators ($CFG->siteadmins) do not always have explicit system role assignments,
        // which causes get_users_by_capability() to miss them.
        $users += get_admins();

        // Suspended users cannot receive notifications, so drop them.
        return array_filter($users, fn($user) => empty($user->suspended));
    }
}
