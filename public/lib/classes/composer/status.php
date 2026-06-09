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

namespace core\composer;

defined('MOODLE_INTERNAL') || die();

/**
 * Composer runtime status.
 *
 * This class is immutable. It is used to represent the current state of Composer runtime.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class status {

    /**
     * Constructor.
     *
     * @param bool $installed Whether Composer dependencies are installed.
     * @param bool $current Whether all packages are current.
     * @param package_status[] $packages The array of package status objects representing the state of each package.
     */
    public function __construct(
        public readonly bool $installed,
        public readonly bool $current,
        public readonly array $packages
    ) {
    }

    /**
     * Filters the current (up-to-date) packages.
     *
     * @return array The current (up-to-date) packages array.
     */
    public function current_packages(): array {
        return array_filter(
            $this->packages,
            function ($status) : bool {
                return $status->current === true;
            }
        );
    }

    /**
     * Filters the missing packages.
     *
     * @return array The missing packages array.
     */
    public function missing_packages(): array {
        return array_filter(
            $this->packages,
            function ($status) : bool {
                return $status->installed === false;
            }
        );
    }

    /**
     * Filters the outdated packages.
     *
     * @return array The outdated packages array.
     */
    public function outdated_packages(): array {
        return array_filter(
            $this->packages,
            function ($status) : bool {
                return $status->installed === true && $status->current === false;
            }
        );
    }
}
