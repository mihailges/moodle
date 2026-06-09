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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
//
// See the GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

namespace core;

use core\composer\status;
use core\composer\package_status;

/**
 * Composer runtime status utility class.
 *
 * This class provides runtime checks for composer-managed dependencies.
 *
 * It can detect:
 * - Whether Composer dependencies are installed
 * - Whether installed dependencies are outdated relative to composer.lock
 * - Missing packages from the installed vendor state
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class composer {

    /** @var bool|null Cache for the composer installation state. */
    protected static ?bool $isinstalledcache = null;

    /** @var array|null Cache for parsed lockfile packages. */
    protected static ?array $lockedpackagescache = null;

    /**
     * Determine whether composer is installed.
     *
     * @return bool
     */
    public static function is_installed(): bool {
        if (static::$isinstalledcache !== null) {
            return static::$isinstalledcache;
        }

        $vendordir = static::get_vendor_dir();

        $isinstalled = file_exists($vendordir . '/autoload.php') &&
            file_exists($vendordir . '/composer/installed.php');

        return static::$isinstalledcache = $isinstalled;
    }

    /**
     * Get a detailed composer runtime status.
     *
     * @return status The current composer runtime status, including installation state and package statuses.
     */
    public static function get_status(): status {
        $iscurrent = true;
        $packages = [];

        foreach (static::get_locked_packages() as $package => $version) {
            $packagestatus = static::get_package_status($package);
            $packages[$package] = $packagestatus;
            // If any package is not installed or not up-to-date, the overall status is not current.
            if ($packagestatus->installed === false || $packagestatus->current === false) {
                $iscurrent = false;
            }
        }

        return new status(
            installed: static::is_installed(),
            current: $iscurrent,
            packages: $packages
        );
    }

    /**
     * Get the status of a specific composer package.
     *
     * @param string $package The package name.
     * @return package_status The package status, including installation state, version information, and whether it's up-to-date.
     * @throws \InvalidArgumentException If the package is not found in the lockfile.
     */
    public static function get_package_status(string $package): package_status {
        $lockedpackages = static::get_locked_packages();

        if (!array_key_exists($package, $lockedpackages)) {
            throw new \InvalidArgumentException("Package '{$package}' not found in composer.lock");
        }

        $requiredversion = $lockedpackages[$package];
        $installedversion = static::get_installed_package_version($package);

        $isinstalled = $installedversion !== null;
        $iscurrent = $isinstalled && $requiredversion === $installedversion;

        return new package_status(
            installed: $isinstalled,
            current: $iscurrent,
            requiredversion: $requiredversion,
            installedversion: $installedversion
        );
    }

    /**
     * Reset the internal runtime caches.
     **
     * @return void
     */
    public static function reset_caches(): void {
        static::$isinstalledcache = null;
        static::$lockedpackagescache = null;
    }

    /**
     * Get the vendor directory path.
     *
     * @return string
     */
    protected static function get_vendor_dir(): string {
        global $CFG;

        return $CFG->root . '/vendor';
    }

    /**
     * Get the composer.lock path.
     *
     * @return string
     */
    protected static function get_lockfile_path(): string {
        global $CFG;

        return $CFG->root . '/composer.lock';
    }

    /**
     * Get packages defined in composer.lock.
     *
     * @return array An array of package names and their required versions.
     */
    protected static function get_locked_packages(): array {
        if (static::$lockedpackagescache !== null) {
            return static::$lockedpackagescache;
        }

        $lockfile = static::get_lockfile_path();

        if (!file_exists($lockfile)) {
            return static::$lockedpackagescache = [];
        }

        $contents = file_get_contents($lockfile);

        if ($contents === false) {
            return static::$lockedpackagescache = [];
        }

        $json = json_decode($contents, true);

        if (!is_array($json)) {
            return static::$lockedpackagescache = [];
        }

        $packages = [];

        foreach ($json['packages'] ?? [] as $package) {
            // Skip invalid composer.lock entries (missing name or version).
            // Lockfile validation is outside the scope of this API; data is processed on a best-effort basis.
            if (empty($package['name']) || empty($package['version'])) {
                continue;
            }

            // Strip leading 'v' from version.
            $packages[$package['name']] = ltrim($package['version'], 'v');
        }

        return static::$lockedpackagescache = $packages;
    }

    /**
     * Get installed package version.
     *
     * @param string $package The package name.
     * @return string|null The installed package version or null if not installed.
     */
    protected static function get_installed_package_version(string $package): ?string {
        try {
            $version = static::is_installed() ? \Composer\InstalledVersions::getPrettyVersion($package) : null;

            if ($version === null) {
                return null;
            }
            // Strip leading 'v' from version.
            return ltrim($version, 'v');

        } catch (\Exception $e) {
            return null;
        }
    }
}
