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

declare(strict_types=1);

namespace core\task;

use core\composer;
use core\composer\package_status;
use core\context\system;
use core\oauth2\server\client_manager;
use core\oauth2\server\entity\client_entity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for {@see send_oauth2_secret_expiry_notifications_task}.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(send_oauth2_secret_expiry_notifications_task::class)]
final class send_oauth2_secret_expiry_notifications_task_test extends \advanced_testcase {
    /**
     * Freeze the clock at midnight, in the server timezone, on a fixed reference date.
     *
     * Every test measures secret expiry windows relative to this instant, so the task's internal
     * "today at midnight" calculation is deterministic no matter what timezone the test host uses.
     *
     * @return \DateTimeImmutable Midnight on the reference date, in the server timezone.
     */
    private function freeze_clock(): \DateTimeImmutable {
        $timezone = new \DateTimeZone(\core_date::get_server_timezone());
        $today = new \DateTimeImmutable('2026-03-01 00:00:00', $timezone);

        $this->mock_clock_with_frozen($today->getTimestamp());

        return $today;
    }

    /**
     * Calculate the half-open [start, end) expiry window the task uses for a given day offset.
     *
     * Mirrors send_oauth2_secret_expiry_notifications_task::get_clients_with_expiring_secrets_for_day().
     *
     * @param \DateTimeImmutable $today Midnight today, as returned by {@see self::freeze_clock()}.
     * @param int $days Number of days from today (e.g. 1, 7, 30).
     * @return int[] [starttime, endtime).
     */
    private function day_window(\DateTimeImmutable $today, int $days): array {
        return [
            $today->modify("+{$days} days")->getTimestamp(),
            $today->modify('+' . ($days + 1) . ' days')->getTimestamp(),
        ];
    }

    /**
     * Create an active, confidential OAuth2 client owned by the system context.
     *
     * @param string $name The client name.
     * @return client_entity The created client.
     */
    private function create_client(string $name = 'Test client'): client_entity {
        return \core\di::get(client_manager::class)->create_client(
            name: $name,
            ownercontext: system::instance(),
            granttypes: [client_entity::GRANT_TYPE_CLIENT_CREDENTIALS],
        );
    }

    /**
     * Create a non-revoked secret for a client with a specific expiry time.
     *
     * @param client_entity $client The client to attach the secret to.
     * @param int $expirytime The expiry timestamp for the secret.
     * @return int The ID of the created secret record.
     */
    private function create_secret(client_entity $client, int $expirytime): int {
        global $DB;

        \core\di::get(client_manager::class)->create_secret($client->get_id(), $expirytime);

        return (int) $DB->get_field_sql(
            'SELECT MAX(id) FROM {oauth2_server_client_secrets} WHERE clientidentifier = ?',
            [$client->getIdentifier()],
        );
    }

    /**
     * Create a user who holds the capability required to manage OAuth2 clients (and therefore is
     * eligible to receive expiry notifications), by assigning them the manager role at system context.
     *
     * This exercises the get_users_by_capability() path in execute(), independently of the
     * get_admins() fallback for primary site administrators.
     *
     * @param array $userdata Additional user fields (e.g. ['suspended' => 1]).
     * @return \stdClass The created user.
     */
    private function create_eligible_user(array $userdata = []): \stdClass {
        global $DB;

        $user = $this->getDataGenerator()->create_user($userdata);
        $managerroleid = $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        role_assign($managerroleid, $user->id, system::instance()->id);

        return $user;
    }

    /**
     * Make a user a primary site administrator (i.e. present in $CFG->siteadmins), without granting
     * them any capability via role assignment.
     *
     * This exercises the get_admins() fallback path in execute(), which exists because primary site
     * administrators do not always hold an explicit role assignment granting the capability.
     *
     * @param \stdClass $user The user to promote to site administrator.
     */
    private function make_site_admin(\stdClass $user): void {
        set_config('siteadmins', (string) $user->id);
    }

    /**
     * Remove every primary site administrator, so that only explicit capability-based role
     * assignments make a user eligible to receive notifications.
     */
    private function clear_site_admins(): void {
        set_config('siteadmins', '');
    }

    /**
     * Force the 'league/oauth2-server' composer package status reported to the task.
     *
     * @param bool $installed Whether the package should be reported as installed.
     */
    private function mock_composer_package_installed(bool $installed): void {
        $composer = $this->createMock(composer::class);
        $composer->method('get_package_status')
            ->with('league/oauth2-server')
            ->willReturn(new package_status($installed, true, '1.0.0', $installed ? '1.0.0' : null));

        \core\di::set(composer::class, $composer);
    }

    /**
     * No notification is sent when the 'league/oauth2-server' composer package is not installed, even
     * if there are secrets expiring soon and eligible users to notify.
     */
    public function test_execute_no_composer_package_installed(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->mock_composer_package_installed(false);
        $today = $this->freeze_clock();

        $client = $this->create_client();
        $this->create_secret($client, $today->modify('+1 day')->getTimestamp());

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $this->assertSame(0, $sink->count());
    }

    /**
     * No notification is sent when there are no secrets expiring in any of the notification windows.
     */
    public function test_execute_no_secrets_expiring(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->mock_composer_package_installed(true);
        $today = $this->freeze_clock();

        $client = $this->create_client();
        // Expires far outside any of the 1/7/30 day notification windows.
        $this->create_secret($client, $today->modify('+1 year')->getTimestamp());

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $this->assertSame(0, $sink->count());
    }

    /**
     * No notification is sent when there are secrets expiring soon, but nobody holds the capability to
     * manage OAuth2 clients and there are no primary site administrators.
     */
    public function test_execute_no_eligible_users(): void {
        $this->resetAfterTest();
        $this->mock_composer_package_installed(true);
        $this->clear_site_admins();
        $today = $this->freeze_clock();

        $client = $this->create_client();
        $this->create_secret($client, $today->modify('+1 day')->getTimestamp());

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $this->assertSame(0, $sink->count());
    }

    /**
     * A user who holds the 'moodle/site:manageoauth2clients' capability via an explicit role
     * assignment (but who is not a primary site administrator) receives the notification.
     */
    public function test_notification_sent_to_user_with_explicit_capability(): void {
        $this->clear_site_admins_after_reset();
        $today = $this->freeze_clock();
        $user = $this->create_eligible_user();

        $client = $this->create_client();
        $this->create_secret($client, $today->modify('+1 day')->getTimestamp());

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertEquals($user->id, $messages[0]->useridto);
    }

    /**
     * A primary site administrator (present in $CFG->siteadmins) receives the notification even
     * without an explicit role assignment granting the capability, via the get_admins() fallback.
     */
    public function test_notification_sent_to_site_admin_without_explicit_capability(): void {
        $this->resetAfterTest();
        $this->mock_composer_package_installed(true);
        $today = $this->freeze_clock();

        $user = $this->getDataGenerator()->create_user();
        $this->make_site_admin($user);

        $client = $this->create_client();
        $this->create_secret($client, $today->modify('+1 day')->getTimestamp());

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertEquals($user->id, $messages[0]->useridto);
    }

    /**
     * Data provider for singular/plural notification-window tests: one entry per notification window.
     *
     * @return array[]
     */
    public static function notification_day_provider(): array {
        return [
            '1 day window' => [1],
            '7 day window' => [7],
            '30 day window' => [30],
        ];
    }

    /**
     * A single secret expiring within a given notification window produces a notification using the
     * singular ("1 secret ...") wording, with the correct subject and smallmessage.
     *
     * @param int $days The notification window being tested (1, 7 or 30).
     */
    #[DataProvider('notification_day_provider')]
    public function test_singular_warning_for_notification_window(int $days): void {
        $this->clear_site_admins_after_reset();
        $today = $this->freeze_clock();
        $this->create_eligible_user();

        $client = $this->create_client('Singular client');
        [$starttime] = $this->day_window($today, $days);
        $this->create_secret($client, $starttime);

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);

        if ($days > 1) {
            $expectedsubject = get_string(
                'oauth2server_secretexpireindaysnotificationsubject',
                'admin',
                (object) ['clientname' => 'Singular client', 'expirydays' => $days],
            );
            $expectedwarning = get_string(
                'oauth2server_secretexpiresindays',
                'admin',
                (object) ['clientname' => 'Singular client', 'expirydays' => $days],
            );
        } else {
            $expectedsubject = get_string('oauth2server_secretexpireindaynotificationsubject', 'admin', 'Singular client');
            $expectedwarning = get_string('oauth2server_secretexpiresinday', 'admin', 'Singular client');
        }

        $this->assertSame($expectedsubject, $messages[0]->subject);
        $this->assertSame($expectedwarning, $messages[0]->smallmessage);
        // The rendered HTML body should include the call-to-action link text.
        $this->assertStringContainsString(get_string('oauth2server_managesecrets', 'admin'), $messages[0]->fullmessagehtml);
    }

    /**
     * Two secrets expiring within the same notification window, for the same client, produce a single
     * notification using the plural ("N secrets ...") wording.
     *
     * @param int $days The notification window being tested (1, 7 or 30).
     */
    #[DataProvider('notification_day_provider')]
    public function test_plural_warning_for_notification_window(int $days): void {
        $this->clear_site_admins_after_reset();
        $today = $this->freeze_clock();
        $this->create_eligible_user();

        $client = $this->create_client('Plural client');
        [$starttime, $endtime] = $this->day_window($today, $days);
        $this->create_secret($client, $starttime);
        $this->create_secret($client, $endtime - 1);

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);

        if ($days > 1) {
            $expectedwarning = get_string(
                'oauth2server_secretsexpireindays',
                'admin',
                (object) ['clientname' => 'Plural client', 'secretcount' => 2, 'expirydays' => $days],
            );
        } else {
            $expectedwarning = get_string(
                'oauth2server_secretsexpireinday',
                'admin',
                (object) ['clientname' => 'Plural client', 'secretcount' => 2],
            );
        }

        $this->assertSame($expectedwarning, $messages[0]->smallmessage);
    }

    /**
     * A client with secrets expiring in two different notification windows (e.g. one in 1 day and
     * another in 30 days) receives a separate notification per window, each with wording scoped to
     * that window's own secret count, rather than one aggregated notification.
     */
    public function test_client_with_secrets_in_multiple_windows_sends_separate_notifications(): void {
        $this->clear_site_admins_after_reset();
        $today = $this->freeze_clock();
        $this->create_eligible_user();

        $client = $this->create_client('Multi-window client');
        $this->create_secret($client, $today->modify('+1 day')->getTimestamp());
        $this->create_secret($client, $today->modify('+30 days')->getTimestamp());

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $messages = $sink->get_messages();
        $this->assertCount(2, $messages);

        $smallmessages = array_map(static fn($message) => $message->smallmessage, $messages);
        sort($smallmessages);

        $expected = [
            get_string('oauth2server_secretexpiresinday', 'admin', 'Multi-window client'),
            get_string(
                'oauth2server_secretexpiresindays',
                'admin',
                (object) ['clientname' => 'Multi-window client', 'expirydays' => 30],
            ),
        ];
        sort($expected);

        $this->assertSame($expected, $smallmessages);
    }

    /**
     * A revoked secret is never included in the expiry count, even if its expiry time falls within a
     * notification window.
     */
    public function test_revoked_secret_excluded(): void {
        global $DB;

        $this->clear_site_admins_after_reset();
        $today = $this->freeze_clock();
        $this->create_eligible_user();

        $client = $this->create_client();
        $secretid = $this->create_secret($client, $today->modify('+1 day')->getTimestamp());
        $DB->set_field('oauth2_server_client_secrets', 'revoked', client_entity::SECRET_REVOKED_YES, ['id' => $secretid]);

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $this->assertSame(0, $sink->count());
    }

    /**
     * A secret belonging to a disabled client is never included, even if it is not itself revoked and
     * its expiry time falls within a notification window.
     */
    public function test_disabled_client_secret_excluded(): void {
        $this->clear_site_admins_after_reset();
        $today = $this->freeze_clock();
        $this->create_eligible_user();

        $client = $this->create_client();
        $this->create_secret($client, $today->modify('+1 day')->getTimestamp());
        \core\di::get(client_manager::class)->disable_client($client->get_id());

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $this->assertSame(0, $sink->count());
    }

    /**
     * A secret expiring exactly at the start of a notification window is included (the window's start
     * boundary is inclusive).
     */
    public function test_secret_at_window_start_boundary_is_included(): void {
        $this->clear_site_admins_after_reset();
        $today = $this->freeze_clock();
        $this->create_eligible_user();

        $client = $this->create_client();
        [$starttime] = $this->day_window($today, 7);
        $this->create_secret($client, $starttime);

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $this->assertSame(1, $sink->count());
    }

    /**
     * A secret expiring exactly at the end of a notification window is excluded (the window's end
     * boundary is exclusive, since it belongs to the next calendar day).
     */
    public function test_secret_at_window_end_boundary_is_excluded(): void {
        $this->clear_site_admins_after_reset();
        $today = $this->freeze_clock();
        $this->create_eligible_user();

        $client = $this->create_client();
        [, $endtime] = $this->day_window($today, 7);
        $this->create_secret($client, $endtime);

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $this->assertSame(0, $sink->count());
    }

    /**
     * A secret whose expiry falls between the defined notification windows (e.g. 2 days from now, which
     * is not 1, 7 or 30 days) does not trigger a notification.
     */
    public function test_secret_outside_notification_windows(): void {
        $this->clear_site_admins_after_reset();
        $today = $this->freeze_clock();
        $this->create_eligible_user();

        $client = $this->create_client();
        $this->create_secret($client, $today->modify('+2 days')->getTimestamp());

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $this->assertSame(0, $sink->count());
    }

    /**
     * A suspended user is skipped, even if they otherwise hold the capability to manage OAuth2
     * clients.
     */
    public function test_suspended_user_skipped(): void {
        $this->clear_site_admins_after_reset();
        $today = $this->freeze_clock();
        $this->create_eligible_user(['suspended' => 1]);

        $client = $this->create_client();
        $this->create_secret($client, $today->modify('+1 day')->getTimestamp());

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $this->assertSame(0, $sink->count());
    }

    /**
     * Every eligible user is notified about the same client, not just the first one found.
     */
    public function test_multiple_eligible_users_all_notified(): void {
        $this->clear_site_admins_after_reset();
        $today = $this->freeze_clock();
        $user1 = $this->create_eligible_user();
        $user2 = $this->create_eligible_user();

        $client = $this->create_client();
        $this->create_secret($client, $today->modify('+1 day')->getTimestamp());

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $messages = $sink->get_messages();
        $recipients = array_map(static fn($message) => (int) $message->useridto, $messages);
        sort($recipients);

        $this->assertSame([(int) $user1->id, (int) $user2->id], $recipients);
    }

    /**
     * Multiple clients with secrets expiring in the same window are each notified independently.
     */
    public function test_multiple_clients_notified_independently(): void {
        $this->clear_site_admins_after_reset();
        $today = $this->freeze_clock();
        $this->create_eligible_user();

        $clienta = $this->create_client('Client A');
        $clientb = $this->create_client('Client B');
        $this->create_secret($clienta, $today->modify('+1 day')->getTimestamp());
        $this->create_secret($clientb, $today->modify('+1 day')->getTimestamp());

        $sink = $this->redirectMessages();
        (new send_oauth2_secret_expiry_notifications_task())->execute();

        $messages = $sink->get_messages();
        $this->assertCount(2, $messages);

        $subjects = array_map(static fn($message) => $message->subject, $messages);
        sort($subjects);

        $expected = [
            get_string('oauth2server_secretexpireindaynotificationsubject', 'admin', 'Client A'),
            get_string('oauth2server_secretexpireindaynotificationsubject', 'admin', 'Client B'),
        ];
        sort($expected);

        $this->assertSame($expected, $subjects);
    }

    /**
     * get_name() returns the human-readable task name used in the scheduled task admin UI.
     */
    public function test_get_name(): void {
        $this->resetAfterTest();

        $task = new send_oauth2_secret_expiry_notifications_task();

        $this->assertSame(get_string('oauth2server_sendsecretexpirynotifications', 'admin'), $task->get_name());
    }

    /**
     * Reset the test environment, mock the composer package as installed, and clear all primary site
     * administrators, so that only explicit capability-based role assignments (via
     * {@see self::create_eligible_user()}) make a user eligible for notifications.
     */
    private function clear_site_admins_after_reset(): void {
        $this->resetAfterTest();
        $this->mock_composer_package_installed(true);
        $this->clear_site_admins();
    }
}
