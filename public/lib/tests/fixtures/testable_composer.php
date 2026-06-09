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

defined('MOODLE_INTERNAL') || die();

/**
 * Testable composer helper subclass.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class testable_composer extends \core\composer {

    /** @var string The vendor directory. */
    public static $vendordir;

    /** @var string The path to the composer.lock file. */

    public static $lockfilepath;

    /** @var array The installed package versions. */

    public static $installedversions;

    /**
     * Get vendor directory.
     *
     * @return string
     */
    protected static function get_vendor_dir(): string {
        return static::$vendordir;
    }

    /**
     * Get lockfile path.
     *
     * @return string
     */
    protected static function get_lockfile_path(): string {
        return static::$lockfilepath;
    }

    /**
     * Get installed package version.
     *
     * @param string $package
     * @return string|null
     */
    protected static function get_installed_package_version(string $package): ?string {
        return static::$installedversions[$package] ?? null;
    }
}
