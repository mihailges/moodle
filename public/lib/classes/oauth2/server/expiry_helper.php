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

namespace core\oauth2\server;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Utility helper for OAuth 2 client secret expiry calculations.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class expiry_helper {
    /**
     * Get midnight today in the site's server timezone.
     *
     * @return DateTimeImmutable
     */
    public static function get_midnight_today(): DateTimeImmutable {
        return \core\di::get(\core\clock::class)
            ->now()
            ->setTimezone(new DateTimeZone(\core_date::get_server_timezone()))
            ->setTime(0, 0, 0);
    }

    /**
     * Get the UNIX timestamp threshold for midnight after a given number of days.
     *
     * @param int $days Number of days from today.
     * @return int
     */
    public static function get_expiry_threshold_timestamp(int $days): int {
        return self::get_midnight_today()->modify("+{$days} days")->getTimestamp();
    }

    /**
     * Get milestone timestamp boundaries [lowerbound, upperbound] for a given day range.
     *
     * @param int $days Target milestone days (e.g. 30, 7, 1).
     * @param int $lower Lower bound days for the bucket (e.g. 7, 1, 0).
     * @return array Array containing [int $lowerbound, int $upperbound].
     */
    public static function get_milestone_bounds(int $days, int $lower): array {
        $clock = \core\di::get(\core\clock::class);
        $now = $clock->time();
        $today = self::get_midnight_today();

        $upperbound = $today->modify("+{$days} days")->getTimestamp();
        $lowerbound = $lower > 0 ? $today->modify("+{$lower} days")->getTimestamp() : $now;

        return [$lowerbound, $upperbound];
    }

    /**
     * Calculate remaining calendar days from midnight today until the given expiry timestamp.
     *
     * @param int $expirytime UNIX timestamp of secret expiry.
     * @return int Number of calendar days (minimum 1 for active/non-expired secrets).
     */
    public static function get_remaining_calendar_days(int $expirytime): int {
        $today = self::get_midnight_today();
        $expirydate = (new DateTimeImmutable('@' . $expirytime))
            ->setTimezone(new DateTimeZone(\core_date::get_server_timezone()))
            ->setTime(0, 0, 0);

        $actualdays = (int) $today->diff($expirydate)->format('%a');

        return max(1, $actualdays);
    }

    /**
     * Check if a secret has already received a notification for a given milestone.
     *
     * @param int|null $lastexpirynotification Timestamp of last notification, or null if never notified.
     * @param int $expirytime UNIX timestamp of secret expiry.
     * @param int $days Milestone days threshold (e.g. 30, 7, 1).
     * @return bool True if already notified for this milestone, false otherwise.
     */
    public static function has_been_notified_for_milestone(?int $lastexpirynotification, int $expirytime, int $days): bool {
        if (empty($lastexpirynotification)) {
            return false;
        }

        // Compute the exact DST-aware timestamp when the secret entered this milestone window.
        $milestoneentry = (new DateTimeImmutable('@' . $expirytime))
            ->setTimezone(new DateTimeZone(\core_date::get_server_timezone()))
            ->modify("-{$days} days")
            ->getTimestamp();

        return $lastexpirynotification >= $milestoneentry;
    }
}
