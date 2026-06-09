<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace core;

use core\composer\status;
use core\composer\package_state_collection;
use core\composer\package_status;
use core\composer\package_versions;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixtures/testable_composer.php');

/**
 * Composer helper tests.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihail@moodle.com>
 * @covers     \core\composer
 */
final class composer_test extends \advanced_testcase {

    /**
     * Setup test environment.
     */
    protected function setUp(): void {
        parent::setUp();

        $tempdir = make_request_directory();

        \testable_composer::$vendordir = $tempdir . '/vendor';
        \testable_composer::$lockfilepath = $tempdir . '/composer.lock';

        \testable_composer::reset_caches();
    }

    /**
     * Test is_installed().
     *
     * @dataProvider is_installed_provider
     * @param bool $installed
     */
    public function test_is_installed(bool $installed): void {

        if ($installed) {
            mkdir(\testable_composer::$vendordir . '/composer', 0777, true);
            file_put_contents(\testable_composer::$vendordir . '/autoload.php', '');
            file_put_contents(\testable_composer::$vendordir . '/composer/installed.php', '');
        }

        $this->assertSame($installed, \testable_composer::is_installed());
    }

    /**
     * Data provider for test_is_installed().
     *
     * @return array[]
     */
    public static function is_installed_provider(): array {
        return [
            'Composer installed' => [true],
            'Composer not installed' => [false],
        ];
    }

    /**
     * Test get_status().
     *
     * @dataProvider get_status_provider
     * @param bool $composerinstalled Whether Composer is installed.
     * @param array $requiredpackages Required packages.
     * @param array $installedpackages Installed packages.
     * @param status $expected Expected status.
     */
    public function test_get_status(
        bool $composerinstalled,
        array $requiredpackages,
        array $installedpackages,
        status $expected
    ): void {

        if ($composerinstalled) {
            mkdir(\testable_composer::$vendordir . '/composer', 0777, true);
            file_put_contents(\testable_composer::$vendordir . '/autoload.php', '');
            file_put_contents(\testable_composer::$vendordir . '/composer/installed.php', '');
        }

        $this->create_lockfile($requiredpackages);

        \testable_composer::$installedversions = $installedpackages;

        $this->assertEquals($expected, \testable_composer::get_status());
    }

    /**
     * Data provider for test_get_status().
     *
     * @return array[]
     */
    public static function get_status_provider(): array {
        return [
            'Composer not installed' => [
                false,
                [
                    'package/test1' => 'v1.0.0',
                    'package/test2' => '2.0.0',
                ],
                [],
                new status(
                    installed: false,
                    current: false,
                    packages: [
                        'package/test1' => new package_status(
                            installed: false,
                            current: false,
                            requiredversion: '1.0.0',
                            installedversion: null
                        ),
                        'package/test2' => new package_status(
                            installed: false,
                            current: false,
                            requiredversion: '2.0.0',
                            installedversion: null
                        ),

                    ]
                ),
            ],
            'Packages not up-to-date (missing packages)' => [
                true,
                [
                    'package/current' => '2.0.0',
                    'package/missing1' => 'v1.0.0',
                    'package/missing2' => 'v1.5.0',
                ],
                [
                    'package/current' => '2.0.0',
                ],
                new status(
                    true,
                    false,
                    [
                        'package/current' => new package_status(
                            true,
                            true,
                            '2.0.0',
                            '2.0.0'
                        ),
                        'package/missing1' => new package_status(
                            false,
                            false,
                            '1.0.0',
                            null
                        ),
                        'package/missing2' => new package_status(
                            false,
                            false,
                            '1.5.0',
                            null
                        ),

                    ]
                ),
            ],
            'Packages not up-to-date (missing and outdated packages)' => [
                true,
                [
                    'package/missing1' => 'v1.0.0',
                    'package/missing2' => 'v1.5.0',
                    'package/outdated' => '2.0.0',
                ],
                [
                    'package/outdated' => '1.0.0',
                ],
                new status(
                    true,
                    false,
                    [
                        'package/missing1' => new package_status(
                            false,
                            false,
                            '1.0.0',
                            null
                        ),
                        'package/missing2' => new package_status(
                            false,
                            false,
                            '1.5.0',
                            null
                        ),
                        'package/outdated' => new package_status(
                            true,
                            false,
                            '2.0.0',
                             '1.0.0'
                        ),
                    ]
                ),
            ],
            'Packages not up-to-date (outdated packages)' => [
                true,
                [
                    'package/current1' => 'v1.0.0',
                    'package/current2' => 'v1.5.0',
                    'package/outdated' => '2.0.0',
                ],
                [
                    'package/current1' => '1.0.0',
                    'package/current2' => '1.5.0',
                    'package/outdated' => '1.0.0',
                ],
                new status(
                    true,
                    false,
                    [
                        'package/current1' => new package_status(
                            true,
                            true,
                            '1.0.0',
                            '1.0.0'
                        ),
                        'package/current2' => new package_status(
                            true,
                            true,
                            '1.5.0',
                            '1.5.0'
                        ),
                        'package/outdated' => new package_status(
                            true,
                            false,
                            '2.0.0',
                            '1.0.0'
                        ),
                    ]
                ),
            ],
            'Packages up-to-date' => [
                true,
                [
                    'package/current1' => 'v1.0.0',
                    'package/current2' => 'v1.5.0',
                    'package/current3' => '2.0.0',
                ],
                [
                    'package/current1' => '1.0.0',
                    'package/current2' => '1.5.0',
                    'package/current3' => '2.0.0',
                ],
                new status(
                    true,
                    true,
                    [
                        'package/current1' => new package_status(
                            true,
                            true,
                            '1.0.0',
                            '1.0.0'
                        ),
                        'package/current2' => new package_status(
                            true,
                            true,
                            '1.5.0',
                            '1.5.0'
                        ),
                        'package/current3' => new package_status(
                            true,
                            true,
                            '2.0.0',
                            '2.0.0'
                        ),
                    ]
                ),
            ],
        ];
    }

    /**
     * Test get_package_status().
     *
     * @dataProvider get_package_status_provider
     * @param bool $composerinstalled Whether Composer is installed.
     * @param array $requiredpackages Required packages.
     * @param array $installedpackages Installed packages.
     * @param string $packagename Package name.
     * @param package_status|null $expected Expected package status.
     * @param string|null $expectedexception Expected exception message.
     */
    public function test_get_package_status(
        bool $composerinstalled,
        array $requiredpackages,
        array $installedpackages,
        string $packagename,
        ?package_status $expected,
        ?string $expectedexception = null
    ): void {

        if ($composerinstalled) {
            mkdir(\testable_composer::$vendordir . '/composer', 0777, true);
            file_put_contents(\testable_composer::$vendordir . '/autoload.php', '');
            file_put_contents(\testable_composer::$vendordir . '/composer/installed.php', '');
        }

        $this->create_lockfile($requiredpackages);

        if ($expectedexception) {
            $this->expectExceptionMessage($expectedexception);
        }

        \testable_composer::$installedversions = $installedpackages;

        $this->assertEquals($expected, \testable_composer::get_package_status($packagename));
    }

    /**
     * Data provider for test_get_package_status().
     *
     * @return array[]
     */
    public static function get_package_status_provider(): array {
        return [
            'Composer not installed' => [
                false,
                [
                    'package/test1' => 'v1.0.0',
                    'package/test2' => '2.0.0',
                ],
                [],
                'package/test2',
                new package_status(
                    false,
                    false,
                    '2.0.0',
                    null
                ),
            ],
            'Missing (not installed) package' => [
                true,
                [
                    'package/current' => '2.0.0',
                    'package/missing' => 'v1.0.0',
                ],
                [
                    'package/current' => '2.0.0',
                ],
                'package/missing',
                new package_status(
                    false,
                    false,
                    '1.0.0',
                    null
                ),
            ],
            'Outdated package' => [
                true,
                [
                    'package/missing' => 'v1.0.0',
                    'package/outdated' => '2.0.0',
                ],
                [
                    'package/outdated' => '1.0.0',
                ],
                'package/outdated',
                new package_status(
                    true,
                    false,
                    '2.0.0',
                    '1.0.0'
                ),
            ],
            'Invalid package' => [
                true,
                [
                    'package/current1' => 'v1.0.0',
                    'package/current2' => 'v1.5.0',
                ],
                [
                    'package/current1' => '1.0.0',
                    'package/current2' => '1.5.0',
                ],
                'package/invalid',
                null,
                "Package 'package/invalid' not found in composer.lock"
            ],
            'Up-to-date package' => [
                true,
                [
                    'package/outdated' => 'v2.0.0',
                    'package/current' => 'v1.5.0',
                ],
                [
                    'package/outdated' => '1.0.0',
                    'package/current' => '1.5.0',
                ],
                'package/current',
                new package_status(
                    true,
                    true,
                    '1.5.0',
                    '1.5.0'
                ),
            ],
        ];
    }


    /**
     * Create a composer.lock fixture.
     *
     * @param array $packages Array containing package name and version pairs ['package/name' => 'version'].
     * @return void
     */
    protected function create_lockfile(array $packages): void {

        $contents = [];

        foreach ($packages as $name => $version) {
            $contents['packages'][] = [
                'name' => $name,
                'version' => $version,
            ];
        }

        file_put_contents(\testable_composer::$lockfilepath, json_encode($contents));
    }
}
