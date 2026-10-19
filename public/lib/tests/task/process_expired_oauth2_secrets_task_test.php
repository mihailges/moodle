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
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for {@see process_expired_oauth2_secrets_task}.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(process_expired_oauth2_secrets_task::class)]
final class process_expired_oauth2_secrets_task_test extends \advanced_testcase {
    /** @var int The frozen time used by every test in this class. */
    private const int NOW = 1750000000;

    /**
     * Freeze the clock so every timestamp written/read by the task is predictable.
     *
     * @return void
     */
    private function freeze_clock(): void {
        $this->mock_clock_with_frozen(self::NOW);
    }

    /**
     * Create an OAuth 2 client fixture.
     *
     * @param int $status The client status.
     * @return \stdClass The client record.
     */
    private function create_client(int $status = client_entity::STATUS_ACTIVE): \stdClass {
        global $DB;

        $manager = \core\di::get(\core\oauth2\server\client_manager::class);

        $client = $manager->create_client(
            name: 'Test client',
            ownercontext: \core\context\system::instance(),
            granttypes: [client_entity::GRANT_TYPE_CLIENT_CREDENTIALS],
        );

        $record = $DB->get_record(
            'oauth2_server_clients',
            ['clientidentifier' => $client->getIdentifier()],
            '*',
            MUST_EXIST,
        );

        if ($status !== client_entity::STATUS_ACTIVE) {
            $DB->set_field('oauth2_server_clients', 'status', $status, ['id' => $record->id]);
        }

        return $record;
    }

    /**
     * Create an OAuth 2 client secret fixture.
     *
     * @param string $clientidentifier The owning client's identifier.
     * @param int $expirytime The expiry timestamp.
     * @param int $revoked Whether the secret is revoked.
     * @param int $expiredprocessed The expired-processing state.
     * @param int|null $lastaccessed The last-accessed timestamp, or null if never used.
     * @return int The ID of the created secret.
     */
    private function create_secret(
        string $clientidentifier,
        int $expirytime,
        int $revoked = client_entity::SECRET_REVOKED_NO,
        int $expiredprocessed = client_entity::SECRET_EXPIRED_NOT_PROCESSED,
        ?int $lastaccessed = null,
    ): int {
        global $DB;

        return $DB->insert_record('oauth2_server_client_secrets', (object) [
            'clientidentifier' => $clientidentifier,
            'secret' => password_hash('secret-' . random_string(8), PASSWORD_DEFAULT),
            'expirytime' => $expirytime,
            'revoked' => $revoked,
            'timecreated' => self::NOW - DAYSECS,
            'lastaccessed' => $lastaccessed,
            'expiredprocessed' => $expiredprocessed,
        ]);
    }

    /**
     * Create a user with the capability to manage OAuth 2 clients and secrets at the system context.
     *
     * @return \stdClass The user record.
     */
    private function create_client_secret_manager(): \stdClass {
        $user = $this->getDataGenerator()->create_user();

        $roleid = $this->getDataGenerator()->create_role();
        assign_capability('moodle/site:manageoauth2clients', CAP_ALLOW, $roleid, \context_system::instance());
        role_assign($roleid, $user->id, \context_system::instance());

        return $user;
    }

    /**
     * The task reports a human-readable name.
     *
     * @return void
     */
    public function test_get_name(): void {
        $task = \core\di::make(process_expired_oauth2_secrets_task::class);

        $this->assertEquals(get_string('taskprocessexpiredoauth2clientsecrets'), $task->get_name());
    }

    /**
     * An expired secret with no active replacement triggers the audit event, is marked as processed with
     * notifications sent, and queues a notification adhoc task for every eligible recipient.
     *
     * @return void
     */
    public function test_execute_sends_notification_when_no_active_replacement_secret(): void {
        global $CFG, $DB;

        $this->resetAfterTest();

        // Ensure a clean, deterministic set of recipients by removing the default test site admin.
        $CFG->siteadmins = '';
        $this->freeze_clock();

        $client = $this->create_client();
        $secretid = $this->create_secret($client->clientidentifier, self::NOW - 1);

        $recipient = $this->create_client_secret_manager();

        $eventsink = $this->redirectEvents();

        (\core\di::make(process_expired_oauth2_secrets_task::class))->execute();

        // The audit event was triggered for the expired secret.
        $events = $eventsink->get_events();
        $eventsink->close();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(oauth2_client_secret_expired::class, $events[0]);
        $this->assertEquals($secretid, $events[0]->objectid);
        $this->assertEquals($client->id, $events[0]->other['clientid']);

        // The secret is marked as processed with the notification sent.
        $this->assertEquals(
            client_entity::SECRET_EXPIRED_PROCESSED_NOTIFICATION_SENT,
            $DB->get_field('oauth2_server_client_secrets', 'expiredprocessed', ['id' => $secretid]),
        );

        // An adhoc notification task was queued for the eligible recipient.
        $adhoctasks = manager::get_adhoc_tasks(send_oauth2_secret_expired_notification_adhoc_task::class);
        $this->assertCount(1, $adhoctasks);
        $task = reset($adhoctasks);
        $this->assertEquals($recipient->id, $task->get_userid());

        $customdata = $task->get_custom_data();
        $this->assertEquals($client->id, $customdata->clientid);
        $this->assertEquals($client->name, $customdata->clientname);
        $this->assertEquals(self::NOW - 1, $customdata->secretexpiredtime);
    }

    /**
     * An expired secret whose client already has an active, in-use replacement secret has its notification
     * suppressed: the event still fires, but no adhoc notification task is queued.
     *
     * @return void
     */
    public function test_execute_suppresses_notification_when_active_replacement_secret_exists(): void {
        global $DB;

        $this->resetAfterTest();
        $this->freeze_clock();

        $client = $this->create_client();
        $expiredsecretid = $this->create_secret($client->clientidentifier, self::NOW - 1);

        // A replacement secret that is active and has already been used to authenticate.
        $this->create_secret(
            $client->clientidentifier,
            self::NOW + DAYSECS,
            lastaccessed: self::NOW - MINSECS,
        );

        $this->create_client_secret_manager();

        $eventsink = $this->redirectEvents();

        (\core\di::make(process_expired_oauth2_secrets_task::class))->execute();

        $events = $eventsink->get_events();
        $eventsink->close();
        $this->assertCount(1, $events);
        $this->assertEquals($expiredsecretid, $events[0]->objectid);

        $this->assertEquals(
            client_entity::SECRET_EXPIRED_PROCESSED_NOTIFICATION_SUPPRESSED,
            $DB->get_field('oauth2_server_client_secrets', 'expiredprocessed', ['id' => $expiredsecretid]),
        );

        $this->assertCount(0, manager::get_adhoc_tasks(send_oauth2_secret_expired_notification_adhoc_task::class));
    }

    /**
     * A replacement secret that has never been used to authenticate does not count as an active replacement,
     * so the notification is still sent.
     *
     * @return void
     */
    public function test_execute_sends_notification_when_replacement_secret_never_used(): void {
        global $CFG, $DB;

        $this->resetAfterTest();

        // Ensure a clean, deterministic set of recipients by removing the default test site admin.
        $CFG->siteadmins = '';
        $this->freeze_clock();

        $client = $this->create_client();
        $expiredsecretid = $this->create_secret($client->clientidentifier, self::NOW - 1);

        // A replacement secret that is active, but has never been used (lastaccessed is null).
        $this->create_secret($client->clientidentifier, self::NOW + DAYSECS);

        $this->create_client_secret_manager();

        (\core\di::make(process_expired_oauth2_secrets_task::class))->execute();

        $this->assertEquals(
            client_entity::SECRET_EXPIRED_PROCESSED_NOTIFICATION_SENT,
            $DB->get_field('oauth2_server_client_secrets', 'expiredprocessed', ['id' => $expiredsecretid]),
        );
        $this->assertCount(1, manager::get_adhoc_tasks(send_oauth2_secret_expired_notification_adhoc_task::class));
    }

    /**
     * Secrets that have not yet expired are left untouched.
     *
     * @return void
     */
    public function test_execute_ignores_non_expired_secrets(): void {
        global $DB;

        $this->resetAfterTest();
        $this->freeze_clock();

        $client = $this->create_client();
        $secretid = $this->create_secret($client->clientidentifier, self::NOW + DAYSECS);

        $eventsink = $this->redirectEvents();

        (\core\di::make(process_expired_oauth2_secrets_task::class))->execute();

        $this->assertCount(0, $eventsink->get_events());
        $eventsink->close();

        $this->assertEquals(
            client_entity::SECRET_EXPIRED_NOT_PROCESSED,
            $DB->get_field('oauth2_server_client_secrets', 'expiredprocessed', ['id' => $secretid]),
        );
        $this->assertCount(0, manager::get_adhoc_tasks(send_oauth2_secret_expired_notification_adhoc_task::class));
    }

    /**
     * Expired secrets that were already processed on a previous run are not re-processed.
     *
     * @return void
     */
    public function test_execute_ignores_already_processed_secrets(): void {
        $this->resetAfterTest();
        $this->freeze_clock();

        $client = $this->create_client();
        $this->create_secret(
            $client->clientidentifier,
            self::NOW - 1,
            expiredprocessed: client_entity::SECRET_EXPIRED_PROCESSED_NOTIFICATION_SENT,
        );

        $eventsink = $this->redirectEvents();

        (\core\di::make(process_expired_oauth2_secrets_task::class))->execute();

        $this->assertCount(0, $eventsink->get_events());
        $eventsink->close();
        $this->assertCount(0, manager::get_adhoc_tasks(send_oauth2_secret_expired_notification_adhoc_task::class));
    }

    /**
     * Revoked secrets are ignored, even if they would otherwise be considered expired.
     *
     * @return void
     */
    public function test_execute_ignores_revoked_secrets(): void {
        $this->resetAfterTest();
        $this->freeze_clock();

        $client = $this->create_client();
        $this->create_secret(
            $client->clientidentifier,
            self::NOW - 1,
            revoked: client_entity::SECRET_REVOKED_YES,
        );

        $eventsink = $this->redirectEvents();

        (\core\di::make(process_expired_oauth2_secrets_task::class))->execute();

        $this->assertCount(0, $eventsink->get_events());
        $eventsink->close();
    }

    /**
     * Expired secrets belonging to a disabled client are ignored.
     *
     * @return void
     */
    public function test_execute_ignores_secrets_of_disabled_clients(): void {
        $this->resetAfterTest();
        $this->freeze_clock();

        $client = $this->create_client(client_entity::STATUS_DISABLED);
        $this->create_secret($client->clientidentifier, self::NOW - 1);

        $eventsink = $this->redirectEvents();

        (\core\di::make(process_expired_oauth2_secrets_task::class))->execute();

        $this->assertCount(0, $eventsink->get_events());
        $eventsink->close();
    }

    /**
     * When there are no eligible recipients, the secret is still marked as processed (notification sent), but
     * no adhoc tasks are queued.
     *
     * @return void
     */
    public function test_execute_marks_secret_processed_when_no_recipients(): void {
        global $CFG, $DB;

        $this->resetAfterTest();

        // Ensure a clean, deterministic set of recipients by removing the default test site admin.
        $CFG->siteadmins = '';
        $this->freeze_clock();

        $client = $this->create_client();
        $secretid = $this->create_secret($client->clientidentifier, self::NOW - 1);

        (\core\di::make(process_expired_oauth2_secrets_task::class))->execute();

        $this->assertEquals(
            client_entity::SECRET_EXPIRED_PROCESSED_NOTIFICATION_SENT,
            $DB->get_field('oauth2_server_client_secrets', 'expiredprocessed', ['id' => $secretid]),
        );
        $this->assertCount(0, manager::get_adhoc_tasks(send_oauth2_secret_expired_notification_adhoc_task::class));
    }

    /**
     * Suspended users with the capability are excluded from receiving notifications.
     *
     * @return void
     */
    public function test_execute_excludes_suspended_recipients(): void {
        global $CFG, $DB;

        $this->resetAfterTest();

        // Ensure a clean, deterministic set of recipients by removing the default test site admin.
        $CFG->siteadmins = '';
        $this->freeze_clock();

        $client = $this->create_client();
        $this->create_secret($client->clientidentifier, self::NOW - 1);

        $recipient = $this->create_client_secret_manager();
        $DB->set_field('user', 'suspended', 1, ['id' => $recipient->id]);

        (\core\di::make(process_expired_oauth2_secrets_task::class))->execute();

        $this->assertCount(0, manager::get_adhoc_tasks(send_oauth2_secret_expired_notification_adhoc_task::class));
    }

    /**
     * One adhoc notification task is queued per eligible recipient, including the primary administrator who is
     * not necessarily reachable via an explicit role assignment.
     *
     * @return void
     */
    public function test_execute_queues_one_task_per_recipient_including_site_admin(): void {
        global $CFG;

        $this->resetAfterTest();
        $this->freeze_clock();

        $client = $this->create_client();
        $this->create_secret($client->clientidentifier, self::NOW - 1);

        $roleholder = $this->create_client_secret_manager();

        $admin = $this->getDataGenerator()->create_user();
        $CFG->siteadmins = (string) $admin->id;

        (\core\di::make(process_expired_oauth2_secrets_task::class))->execute();

        $adhoctasks = manager::get_adhoc_tasks(send_oauth2_secret_expired_notification_adhoc_task::class);
        $this->assertCount(2, $adhoctasks);

        $recipientids = array_map(fn($task) => $task->get_userid(), $adhoctasks);
        $this->assertEqualsCanonicalizing([$roleholder->id, $admin->id], $recipientids);
    }
}
