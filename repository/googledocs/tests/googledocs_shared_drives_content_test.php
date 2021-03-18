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

/**
 * Shared drives content browser unit tests.
 *
 * @package    repository_googledocs
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \repository_googledocs\browser\googledocs_shared_drives_content;

global $CFG;
require_once($CFG->dirroot . '/repository/googledocs/tests/googledocs_content_testcase.php');

/**
 * Tests for the shared drives browser class.
 *
 * @package    repository_googledocs
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_googledocs_shared_drives_browser_testcase extends googledocs_content_testcase {

    /**
     * Test get_content_nodes().
     *
     * @dataProvider get_content_nodes_provider
     * @param array $shareddrives The array containing the existing shared drives
     * @param bool $sortcontent Whether the contents should be sorted in alphabetical order
     * @param array $expected The expected array which contains the generated repository content nodes
     */
    public function test_get_content_nodes(array $shareddrives, bool $sortcontent, array $expected) {

        // Mock the service object.
        $servicemock = $this->getMockBuilder('repository_googledocs\rest')->disableOriginalConstructor()
            ->getMock();

        // Assert that the call() method is being called with the given arguments to fetch the existing shared drives.
        // Define the returned data object by this call.
        $servicemock->expects($this->at(0))
            ->method('call')
            ->with('shared_drives_list', [])
            ->willReturn((object)[
                'kind' => 'drive#driveList',
                'nextPageToken' => 'd838181f30b0f5',
                'drives' => $shareddrives
            ]);

        $path = \repository_googledocs::REPOSITORY_ROOT_ID . '|Google+Drive/' .
            \repository_googledocs::SHARED_DRIVES_ROOT_ID . '|Shared+Drives';
        $shareddrivesbrowser = new googledocs_shared_drives_content($servicemock, $path, $sortcontent);
        $contentnodes = $shareddrivesbrowser->get_content_nodes('', [$this, 'filter']);

        // Assert that the returned array of repository content nodes is equal to the expected one.
        $this->assertEquals($expected, $contentnodes);
    }

    /**
     * Data provider for test_get_content_nodes().
     *
     * @return array
     */
    public function get_content_nodes_provider(): array {

        $rootid = \repository_googledocs::REPOSITORY_ROOT_ID;
        $shareddrivesid = \repository_googledocs::SHARED_DRIVES_ROOT_ID;
        $shareddrivesstring = get_string('shareddrives', 'repository_googledocs');

        return [
            'Shared drives exist; ordering applied.' =>
                [
                    [
                        $this->create_google_drive_shared_drive_object('d85b21c0f86cb5', 'Shared Drive 1'),
                        $this->create_google_drive_shared_drive_object('d85b21c0f86cb0', 'Shared Drive 3'),
                        $this->create_google_drive_shared_drive_object('bed5a0f08d412a', 'Shared Drive 2')
                    ],
                    true,
                    [
                        $this->create_folder_content_node_array('d85b21c0f86cb5', 'Shared Drive 1',
                            "{$rootid}|Google+Drive/{$shareddrivesid}|" . urlencode($shareddrivesstring)),
                        $this->create_folder_content_node_array('bed5a0f08d412a', 'Shared Drive 2',
                            "{$rootid}|Google+Drive/{$shareddrivesid}|" . urlencode($shareddrivesstring)),
                        $this->create_folder_content_node_array('d85b21c0f86cb0', 'Shared Drive 3',
                            "{$rootid}|Google+Drive/{$shareddrivesid}|" . urlencode($shareddrivesstring))
                    ],
                ],
            'Shared drives exist; ordering not applied.' =>
                [
                    [
                        $this->create_google_drive_shared_drive_object('0c4ad262c65333', 'Shared Drive 3'),
                        $this->create_google_drive_shared_drive_object('d85b21c0f86cb0', 'Shared Drive 1'),
                        $this->create_google_drive_shared_drive_object('bed5a0f08d412a', 'Shared Drive 2'),
                    ],
                    false,
                    [
                        $this->create_folder_content_node_array('0c4ad262c65333', 'Shared Drive 3',
                            "{$rootid}|Google+Drive/{$shareddrivesid}|" . urlencode($shareddrivesstring)),
                        $this->create_folder_content_node_array('d85b21c0f86cb0', 'Shared Drive 1',
                            "{$rootid}|Google+Drive/{$shareddrivesid}|" . urlencode($shareddrivesstring)),
                        $this->create_folder_content_node_array('bed5a0f08d412a', 'Shared Drive 2',
                            "{$rootid}|Google+Drive/{$shareddrivesid}|" . urlencode($shareddrivesstring))
                    ],
                ],
            'Shared drives do not exist.' =>
                [
                    [],
                    false,
                    [],
                ]
        ];
    }

    /**
     * Test get_navigation().
     *
     * @dataProvider get_navigation_provider
     * @param string $nodepath The node path string
     * @param array $expected The expected array containing the repository navigation nodes
     */
    public function test_get_navigation(string $nodepath, array $expected) {
        // Mock the service object.
        $servicemock = $this->getMockBuilder('repository_googledocs\rest')->disableOriginalConstructor()
            ->getMock();

        $shareddrivesbrowser = new googledocs_shared_drives_content($servicemock, $nodepath);
        $navigation = $shareddrivesbrowser->get_navigation();

        // Assert that the returned array containing the navigation nodes is equal to the expected one.
        $this->assertEquals($expected, $navigation);
    }

    /**
     * Data provider for test_get_navigation().
     *
     * @return array
     */
    public function get_navigation_provider(): array {

        $rootid = \repository_googledocs::REPOSITORY_ROOT_ID;
        $shareddrivesid = \repository_googledocs::SHARED_DRIVES_ROOT_ID;
        $shareddrivesstring = get_string('shareddrives', 'repository_googledocs');

        return [
            'Return navigation nodes array from path where all nodes have a name.' =>
                [
                    "{$rootid}|Google+Drive/{$shareddrivesid}|" . urlencode($shareddrivesstring),
                    [
                        [
                            'name' => 'Google Drive',
                            'path' => "{$rootid}|Google+Drive"
                        ],
                        [
                            'name' => $shareddrivesstring,
                            'path' => "{$rootid}|Google+Drive/{$shareddrivesid}|" . urlencode($shareddrivesstring)
                        ]
                    ]
                ],
            'Return navigation nodes array from path where some nodes do not have a name.' =>
                [
                    "{$rootid}/{$shareddrivesid}|" . urlencode($shareddrivesstring),
                    [
                        [
                            'name' => $rootid,
                            'path' => "{$rootid}|{$rootid}"
                        ],
                        [
                            'name' => $shareddrivesstring,
                            'path' => "{$rootid}|{$rootid}/{$shareddrivesid}|" . urlencode($shareddrivesstring)
                        ]
                    ],
                ],
        ];
    }
}
