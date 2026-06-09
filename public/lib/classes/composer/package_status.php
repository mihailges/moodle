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
 * Composer package status.
 *
 * This class is immutable. It is used to represent the current state of a composer package.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class package_status {

    /**
     * Constructor.
     *
     * @param bool $installed Whether the package is installed.
     * @param bool $current Whether the installed version matches composer.lock.
     * @param string $requiredversion Required version from composer.lock.
     * @param string|null $installedversion Installed version.
     */
    public function __construct(
        public readonly bool $installed,
        public readonly bool $current,
        public readonly string $requiredversion,
        public readonly ?string $installedversion
    ) {
    }
}
