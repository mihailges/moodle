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

namespace core\oauth2\server;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for {@see expiry_helper}.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(expiry_helper::class)]
final class expiry_helper_test extends \advanced_testcase {
    /**
     * Freeze the clock at a fixed, non-midnight instant in a given timezone.
     *
     * @param string $time The date/time string to freeze the clock at.
     * @param string|null $timezone The timezone to use (defaults to the site's configured server timezone).
     * @return DateTimeImmutable The frozen instant.
     */
    private function freeze_clock_at(string $time, ?string $timezone = null): DateTimeImmutable {
        $this->resetAfterTest();

        $timezone ??= \core_date::get_server_timezone();
        set_config('timezone', $timezone);
        $now = new DateTimeImmutable($time, new DateTimeZone($timezone));

        $this->mock_clock_with_frozen($now->getTimestamp());

        return $now;
    }

    /**
     * get_midnight_today() truncates the current frozen instant down to midnight, in the server
     * timezone, even when the frozen instant is not itself midnight.
     */
    public function test_get_midnight_today_truncates_to_midnight(): void {
        $now = $this->freeze_clock_at('2026-03-01 14:35:10');

        $midnight = expiry_helper::get_midnight_today();

        $this->assertSame('2026-03-01 00:00:00', $midnight->format('Y-m-d H:i:s'));
        $this->assertLessThan($now->getTimestamp(), $midnight->getTimestamp());
    }

    /**
     * get_midnight_today() truncates using the configured server timezone, not UTC, so the result
     * changes depending on which server timezone is configured for an identical UTC instant.
     */
    public function test_get_midnight_today_uses_server_timezone(): void {
        // 2026-03-01 23:30:00 UTC is already 2026-03-02 in a UTC+1 (e.g. Europe/Paris) timezone.
        $utcinstant = new DateTimeImmutable('2026-03-01 23:30:00', new DateTimeZone('UTC'));
        $this->resetAfterTest();
        set_config('timezone', 'Europe/Paris');
        $this->mock_clock_with_frozen($utcinstant->getTimestamp());

        $midnight = expiry_helper::get_midnight_today();

        $this->assertSame('Europe/Paris', $midnight->getTimezone()->getName());
        $this->assertSame('2026-03-02 00:00:00', $midnight->format('Y-m-d H:i:s'));
    }

    /**
     * get_expiry_threshold_timestamp() returns the timestamp of midnight, N calendar days from today,
     * even across a daylight-saving-time transition (where a calendar day is not exactly 24 hours).
     */
    public function test_get_expiry_threshold_timestamp_is_dst_aware(): void {
        // In Europe/London, clocks go forward 1 hour on the last Sunday of March (29 March 2026),
        // making 2026-03-29 a 23-hour day. A naive "+N * 86400 seconds" calculation would land on the
        // wrong wall-clock time; calendar-based arithmetic must not be affected.
        $this->freeze_clock_at('2026-03-28 06:00:00', 'Europe/London');

        $threshold = expiry_helper::get_expiry_threshold_timestamp(2);

        $expected = new DateTimeImmutable('2026-03-30 00:00:00', new DateTimeZone('Europe/London'));
        $this->assertSame($expected->getTimestamp(), $threshold);
        // Sanity check: this really is a DST-affected offset, i.e. not simply midnight + (2 * 86400).
        $this->assertNotSame(
            (new DateTimeImmutable('2026-03-28 00:00:00', new DateTimeZone('Europe/London')))->getTimestamp() + (2 * 86400),
            $threshold,
        );
    }

    /**
     * get_expiry_threshold_timestamp(0) returns today's midnight.
     */
    public function test_get_expiry_threshold_timestamp_zero_days(): void {
        $this->freeze_clock_at('2026-03-01 09:00:00');

        $this->assertSame(
            expiry_helper::get_midnight_today()->getTimestamp(),
            expiry_helper::get_expiry_threshold_timestamp(0),
        );
    }

    /**
     * Data provider of milestone (days, lower) pairs mirroring
     * send_oauth2_secret_expiry_notifications_task::EXPIRY_NOTIFICATION_MILESTONES.
     *
     * @return array[]
     */
    public static function milestone_provider(): array {
        return [
            '1 day bucket' => [1, 0],
            '7 day bucket' => [7, 1],
            '30 day bucket' => [30, 7],
        ];
    }

    /**
     * get_milestone_bounds() returns an upper bound of midnight + $days days, for every milestone.
     *
     * @param int $days
     * @param int $lower
     */
    #[DataProvider('milestone_provider')]
    public function test_get_milestone_bounds_upperbound(int $days, int $lower): void {
        $this->freeze_clock_at('2026-03-01 09:00:00');

        [, $upperbound] = expiry_helper::get_milestone_bounds($days, $lower);

        $this->assertSame(
            expiry_helper::get_midnight_today()->modify("+{$days} days")->getTimestamp(),
            $upperbound,
        );
    }

    /**
     * get_milestone_bounds() returns a lowerbound of midnight + $lower days for buckets whose lower
     * bound is greater than zero.
     */
    public function test_get_milestone_bounds_lowerbound_uses_midnight_when_positive(): void {
        $this->freeze_clock_at('2026-03-01 09:00:00');

        [$lowerbound7] = expiry_helper::get_milestone_bounds(7, 1);
        [$lowerbound30] = expiry_helper::get_milestone_bounds(30, 7);

        $this->assertSame(
            expiry_helper::get_midnight_today()->modify('+1 days')->getTimestamp(),
            $lowerbound7,
        );
        $this->assertSame(
            expiry_helper::get_midnight_today()->modify('+7 days')->getTimestamp(),
            $lowerbound30,
        );
    }

    /**
     * get_milestone_bounds() returns the raw current instant (not midnight) as the lower bound for the
     * innermost bucket (lower === 0), so that a secret expiring later today is caught immediately
     * rather than only from the next midnight onwards.
     */
    public function test_get_milestone_bounds_lowerbound_is_now_when_zero(): void {
        $now = $this->freeze_clock_at('2026-03-01 14:35:10');

        [$lowerbound, $upperbound] = expiry_helper::get_milestone_bounds(1, 0);

        $this->assertSame($now->getTimestamp(), $lowerbound);
        $this->assertNotSame(expiry_helper::get_midnight_today()->getTimestamp(), $lowerbound);
        $this->assertSame(
            expiry_helper::get_midnight_today()->modify('+1 days')->getTimestamp(),
            $upperbound,
        );
    }

    /**
     * The three real milestone buckets are contiguous and non-overlapping: each bucket's lower bound
     * equals the previous bucket's upper bound.
     */
    public function test_milestone_buckets_are_contiguous(): void {
        $this->freeze_clock_at('2026-03-01 09:00:00');

        [, $upper1] = expiry_helper::get_milestone_bounds(1, 0);
        [$lower7, $upper7] = expiry_helper::get_milestone_bounds(7, 1);
        [$lower30] = expiry_helper::get_milestone_bounds(30, 7);

        $this->assertSame($upper1, $lower7);
        $this->assertSame($upper7, $lower30);
    }

    /**
     * get_remaining_calendar_days() returns the exact number of calendar days between today and an
     * expiry that falls precisely at a future midnight.
     */
    public function test_get_remaining_calendar_days_exact_midnight(): void {
        $today = $this->freeze_clock_at('2026-03-01 09:00:00')->setTime(0, 0, 0);

        $expiry = $today->modify('+15 days')->getTimestamp();

        $this->assertSame(15, expiry_helper::get_remaining_calendar_days($expiry));
    }

    /**
     * get_remaining_calendar_days() is floored at a minimum of 1, even for an expiry later today (same
     * calendar day as "now"), so that an actively-expiring secret is never reported as expiring in "0
     * days".
     */
    public function test_get_remaining_calendar_days_same_day_floors_to_one(): void {
        $today = $this->freeze_clock_at('2026-03-01 09:00:00')->setTime(0, 0, 0);

        // Expires later today, i.e. zero whole calendar days away.
        $expiry = $today->modify('+18 hours')->getTimestamp();

        $this->assertSame(1, expiry_helper::get_remaining_calendar_days($expiry));
    }

    /**
     * get_remaining_calendar_days() is floored at a minimum of 1 even for an expiry earlier the same
     * day (already technically in the past relative to "now"), reflecting that the secret is still
     * being reported as part of the current catch-up run.
     */
    public function test_get_remaining_calendar_days_earlier_today_floors_to_one(): void {
        $today = $this->freeze_clock_at('2026-03-01 18:00:00')->setTime(0, 0, 0);

        $expiry = $today->modify('+2 hours')->getTimestamp();

        $this->assertSame(1, expiry_helper::get_remaining_calendar_days($expiry));
    }

    /**
     * get_remaining_calendar_days() counts whole calendar days across a daylight-saving-time
     * transition correctly (i.e. it is not thrown off by a 23- or 25-hour day).
     */
    public function test_get_remaining_calendar_days_is_dst_aware(): void {
        $this->freeze_clock_at('2026-03-28 06:00:00', 'Europe/London');

        $expiry = (new DateTimeImmutable('2026-04-02 06:00:00', new DateTimeZone('Europe/London')))->getTimestamp();

        $this->assertSame(5, expiry_helper::get_remaining_calendar_days($expiry));
    }

    /**
     * has_been_notified_for_milestone() returns false when the secret has never been notified before
     * (null $lastexpirynotification).
     */
    public function test_has_been_notified_for_milestone_never_notified(): void {
        $this->freeze_clock_at('2026-03-01 09:00:00');

        $expirytime = expiry_helper::get_midnight_today()->modify('+7 days')->getTimestamp();

        $this->assertFalse(expiry_helper::has_been_notified_for_milestone(null, $expirytime, 7));
    }

    /**
     * has_been_notified_for_milestone() returns false when $lastexpirynotification is 0, which is
     * treated the same as "never notified".
     */
    public function test_has_been_notified_for_milestone_zero_treated_as_never_notified(): void {
        $this->freeze_clock_at('2026-03-01 09:00:00');

        $expirytime = expiry_helper::get_midnight_today()->modify('+7 days')->getTimestamp();

        $this->assertFalse(expiry_helper::has_been_notified_for_milestone(0, $expirytime, 7));
    }

    /**
     * has_been_notified_for_milestone() returns true once the last notification timestamp is exactly
     * at the milestone entry point (expirytime - days), because the comparison is inclusive (>=).
     */
    public function test_has_been_notified_for_milestone_boundary_is_inclusive(): void {
        $this->freeze_clock_at('2026-03-01 09:00:00');

        $expirytime = (new DateTimeImmutable('2026-03-15 12:00:00', new DateTimeZone(\core_date::get_server_timezone())))
            ->getTimestamp();
        $milestoneentry = (new DateTimeImmutable('2026-03-15 12:00:00', new DateTimeZone(\core_date::get_server_timezone())))
            ->modify('-7 days')
            ->getTimestamp();

        $this->assertTrue(expiry_helper::has_been_notified_for_milestone($milestoneentry, $expirytime, 7));
        $this->assertFalse(expiry_helper::has_been_notified_for_milestone($milestoneentry - 1, $expirytime, 7));
    }

    /**
     * has_been_notified_for_milestone() is specific to the given milestone: a notification recorded
     * for a wider bucket (e.g. 30 days) does not suppress a later, separate notification for a
     * tighter bucket (e.g. 7 days) for the same secret.
     */
    public function test_has_been_notified_for_milestone_is_milestone_specific(): void {
        $this->freeze_clock_at('2026-03-01 09:00:00');

        $expirytime = (new DateTimeImmutable('2026-03-25 12:00:00', new DateTimeZone(\core_date::get_server_timezone())))
            ->getTimestamp();

        // Notified for the 30-day milestone, at the moment the secret entered that bucket.
        $lastexpirynotification = (
            new DateTimeImmutable('2026-03-25 12:00:00', new DateTimeZone(\core_date::get_server_timezone()))
        )
            ->modify('-30 days')
            ->getTimestamp();

        $this->assertTrue(expiry_helper::has_been_notified_for_milestone($lastexpirynotification, $expirytime, 30));
        // The same last-notification timestamp does not (yet) count as having been notified for the
        // tighter 7-day milestone, which has a later entry point.
        $this->assertFalse(expiry_helper::has_been_notified_for_milestone($lastexpirynotification, $expirytime, 7));
    }

    /**
     * has_been_notified_for_milestone() returns false when the last notification predates the
     * milestone's entry point (i.e. it was recorded for an earlier catch-up run that has since been
     * superseded by a tighter bucket).
     */
    public function test_has_been_notified_for_milestone_stale_notification_returns_false(): void {
        $this->freeze_clock_at('2026-03-01 09:00:00');

        $expirytime = (new DateTimeImmutable('2026-03-08 12:00:00', new DateTimeZone(\core_date::get_server_timezone())))
            ->getTimestamp();

        // Notified when the secret was still ~30 days out, long before it entered the 7-day bucket.
        $lastexpirynotification = (
            new DateTimeImmutable('2026-02-01 00:00:00', new DateTimeZone(\core_date::get_server_timezone()))
        )
            ->getTimestamp();

        $this->assertFalse(expiry_helper::has_been_notified_for_milestone($lastexpirynotification, $expirytime, 7));
    }
}
