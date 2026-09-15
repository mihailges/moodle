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

use core\message\message;
use core\oauth2\server\entity\client_entity;
use dml_exception;
use stdClass;

/**
 * Scheduled task to send notifications about expiring OAuth2 client secrets.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_oauth2_secret_expiry_notifications_task extends scheduled_task {
    /**
     * Number of days before expiry for which notifications are sent.
     */
    private const NOTIFICATION_DAYS = [1, 7, 30];

    /**
     * Get a descriptive name for this task.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('oauth2server_sendsecretexpirynotifications', 'admin');
    }

    /**
     * Execute the task.
     *
     * @return void
     * @throws dml_exception
     */
    public function execute(): void {
        // Ensure that the 'league/oauth2-server' composer package is installed.
        // If it's not installed, OAuth2 authentication and client/secret management is not available, so there's no
        // need to execute this task further.
        $package = \core\di::get(\core\composer::class)->get_package_status('league/oauth2-server');

        if (!$package->installed) {
            return;
        }

        // Fetch users with explicit capability to manage OAuth 2 clients.
        $users = get_users_by_capability(
            \context_system::instance(),
            'moodle/site:manageoauth2clients',
            'u.*',
        );

        // Primary site administrators ($CFG->siteadmins) do not always have explicit system role assignments,
        // which causes get_users_by_capability() to miss them.
        $users += get_admins();

        if (empty($users)) {
            return;
        }

        foreach (self::NOTIFICATION_DAYS as $days) {
            $clients = $this->get_clients_with_expiring_secrets_for_day($days);

            foreach ($clients as $client) {
                $this->send_notification($users, $client);
            }
        }
    }

    /**
     * Get OAuth2 clients with secrets expiring on a specific calendar day.
     *
     * Calculates a half-open time interval [starttime, endtime) covering the full 24-hour calendar day
     * ($days from today) using midnight-to-midnight boundaries in the site's server timezone.
     *
     * @param int $days Number of days from today (e.g., 1, 7, 30).
     * @return array List of client objects containing client details and expiring secret counts.
     * @throws dml_exception
     */
    private function get_clients_with_expiring_secrets_for_day(int $days): array {
        global $DB;

        // Establish midnight today in the site's server timezone as the baseline reference point.
        $today = \core\di::get(\core\clock::class)
            ->now()
            ->setTimezone(new \DateTimeZone(\core_date::get_server_timezone()))
            ->setTime(0, 0, 0);

        // Define a 24-hour window [starttime, endtime) for the target calendar day.
        // E.g., for $days = 30, this covers Day 30 00:00:00 up to (but excluding) Day 31 00:00:00.
        $starttime = $today->modify("+{$days} days")->getTimestamp();
        $endtime = $today->modify('+' . ($days + 1) . ' days')->getTimestamp();

        $sql = "SELECT c.id AS clientid, c.clientidentifier, c.name AS clientname, :days as expirydays, COUNT(s.id) AS secretcount
                  FROM {oauth2_server_client_secrets} s
                  JOIN {oauth2_server_clients} c
                    ON c.clientidentifier = s.clientidentifier
                 WHERE s.revoked = :revoked
                   AND s.expirytime >= :starttime
                   AND s.expirytime < :endtime
                   AND c.status = :status
              GROUP BY c.id, c.clientidentifier, c.name";

        return $DB->get_records_sql(
            $sql,
            [
                'starttime' => $starttime,
                'endtime' => $endtime,
                'revoked' => client_entity::SECRET_REVOKED_NO,
                'status' => client_entity::STATUS_ACTIVE,
                'days' => $days,
            ],
        );
    }

    /**
     * Send an expiry notification to users that have a capability to manage client secrets.
     *
     * @param array $users List of users that can manage client secrets.
     * @param stdClass $client OAuth2 client and expiry counts.
     * @return void
     */
    private function send_notification(array $users, stdClass $client): void {
        global $OUTPUT;

        if ($client->expirydays > 1) {
            $subject = get_string(
                'oauth2server_secretexpireindaysnotificationsubject',
                'admin',
                (object) [
                    'clientname' => $client->clientname,
                    'expirydays' => $client->expirydays,
                ],
            );
        } else {
            $subject = get_string(
                'oauth2server_secretexpireindaynotificationsubject',
                'admin',
                $client->clientname,
            );
        }

        $warningtext = $this->get_expiry_warning($client->clientname, $client->expirydays, $client->secretcount);

        $managesecretsurl = \core\router\util::get_path_for_callable(
            [\core_admin\route\controller\oauth2\server\client_management::class, 'manage_client_secrets'],
            ['client' => $client->clientid],
        );

        $actiontext = get_string('oauth2server_rotateexpiringsecrets', 'admin', $managesecretsurl->out());

        $messagehtml = $OUTPUT->render_from_template(
            'core_admin/oauth2/server/client_secret_expiry_notification',
            [
                'warningtext' => $warningtext,
                'actiontext' => $actiontext,
            ],
        );

        foreach ($users as $user) {
            // Skip suspended user accounts.
            if (!empty($user->suspended)) {
                continue;
            }

            $eventdata = new message();
            $eventdata->component = 'moodle';
            $eventdata->name = 'oauth2_secret_expiry_warning';
            $eventdata->userfrom = \core_user::get_noreply_user();
            $eventdata->subject = $subject;
            $eventdata->fullmessage = html_to_text($messagehtml);
            $eventdata->fullmessageformat = FORMAT_HTML;
            $eventdata->fullmessagehtml = $messagehtml;
            $eventdata->smallmessage = $warningtext;
            $eventdata->notification = 1;
            $eventdata->userto = $user;

            message_send($eventdata);
        }
    }

    /**
     * Get the appropriate warning message based on the defined expiry period and number of secrets.
     *
     * @param string $clientname Name of the client.
     * @param int $days Number of days before expiry.
     * @param int $count Number of secrets expiring.
     * @return string
     */
    private function get_expiry_warning(string $clientname, int $days, int $count): string {
        // Return the appropriate singular or plural warning message based on the expiry period and number of secrets.
        if ($days > 1) {
            if ($count > 1) {
                return get_string(
                    'oauth2server_secretsexpireindays',
                    'admin',
                    (object) [
                        'clientname' => $clientname,
                        'secretcount' => $count,
                        'expirydays' => $days,
                    ],
                );
            }

            return get_string(
                'oauth2server_secretexpiresindays',
                'admin',
                (object) [
                    'clientname' => $clientname,
                    'expirydays' => $days,
                ],
            );
        }

        if ($count > 1) {
            return get_string(
                'oauth2server_secretsexpireinday',
                'admin',
                (object) [
                    'clientname' => $clientname,
                    'secretcount' => $count,
                ],
            );
        }

        return get_string('oauth2server_secretexpiresinday', 'admin', $clientname);
    }
}
