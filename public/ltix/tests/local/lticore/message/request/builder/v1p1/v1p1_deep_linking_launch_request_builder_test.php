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

namespace core_ltix\local\lticore\message\request\builder\v1p1;

use core_ltix\constants;
use core_ltix\local\lticore\message\request\builder\v1p1\v1p1_deep_linking_launch_request_builder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for the v1p1 deep linking launch request builder.
 *
 * @package    core_ltix
 * @copyright  2025 Muhammad Arnaldo <muhammad.arnaldo@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(v1p1_deep_linking_launch_request_builder::class)]
class v1p1_deep_linking_launch_request_builder_test extends \basic_testcase {
    /**
     * Test building the deep linking launch message.
     *
     * @param array $params Builder constructor parameters.
     * @param array $expected Expected message parameters.
     * @return void
     */
    #[DataProvider('build_message_provider')]
    public function test_build_message(array $params, array $expected): void {
        $message = (new v1p1_deep_linking_launch_request_builder(...$params))->build_message();

        // The message should be configured to be sent to the content-item selection URL.
        $this->assertEquals($expected['messageurl'], $message->get_url());

        // Verify all expected message parameters are present with correct values.
        $messageparams = $message->get_parameters();
        foreach ($expected['messageparams'] as $expectedparamname => $expectedparamvalue) {
            $this->assertEquals($expectedparamvalue, $messageparams[$expectedparamname]);
        }
    }

    /**
     * Provider for test_build_message().
     *
     * @return array Test cases.
     */
    public static function build_message_provider(): array {
        return [
            'basic content item selection' => [
                'params' => [
                    'toolconfig' => (object) [
                        'typeid' => 11111,
                        'lti_toolurl' => 'https://tool.example.com',
                        'lti_version' => 'LTI-1p0',
                        'lti_messagetype' => 'ContentItemSelectionRequest',
                        'lti_launchcontainer' => constants::LTI_LAUNCH_CONTAINER_DEFAULT,
                    ],
                    'userid' => 20001,
                    'launchid' => 'launch-123',
                    'returnurl' => 'https://moodle.example.org/return',
                ],
                'expected' => [
                    'messageurl' => 'https://tool.example.com',
                    'messageparams' => [
                        'lti_message_type' => 'ContentItemSelectionRequest',
                        'lti_version' => 'LTI-1p0',
                        'accept_media_types' => '*/*',
                        'accept_presentation_document_targets' => 'frame,iframe,window',
                        'accept_unsigned' => 'false',
                        'accept_multiple' => 'true',
                        'accept_copy_advice' => 'false',
                        'accept_types' => 'application/vnd.ims.lti.v1.ltilink',
                        'auto_create' => 'false',
                        'can_confirm' => 'false',
                        'content_item_return_url' => 'https://moodle.example.org/return',
                        'user_id' => '20001',
                        'data' => json_encode(['launchid' => 'launch-123']),
                    ],
                ],
            ],
            'deep linking with placement URL' => [
                'params' => [
                    'toolconfig' => (object) [
                        'typeid' => 11111,
                        'lti_toolurl' => 'https://tool.example.com',
                        'lti_version' => 'LTI-1p0',
                        'lti_messagetype' => 'ContentItemSelectionRequest',
                        'lti_launchcontainer' => constants::LTI_LAUNCH_CONTAINER_DEFAULT,
                        'placement_config' => (object) [
                            'deep_linking_url' => 'https://tool.example.com/content-select',
                        ],
                    ],
                    'userid' => 20001,
                    'launchid' => 'launch-123',
                    'returnurl' => 'https://moodle.example.org/return',
                    'accepttypes' => ['application/vnd.ims.lti.v1.ltilink'],
                    'acceptpresentationtargets' => ['frame'],
                    'acceptmediatypes' => ['text/html'],
                    'autocreate' => true,
                    'acceptmultiple' => false,
                    'placementtype' => 'core_ltix:editor',
                ],
                'expected' => [
                    'messageurl' => 'https://tool.example.com',
                    'messageparams' => [
                        'lti_message_type' => 'ContentItemSelectionRequest',
                        'lti_version' => 'LTI-1p0',
                        'accept_media_types' => 'text/html',
                        'accept_presentation_document_targets' => 'frame',
                        'accept_unsigned' => 'false',
                        'accept_multiple' => 'false',
                        'accept_copy_advice' => 'false',
                        'accept_types' => 'application/vnd.ims.lti.v1.ltilink',
                        'auto_create' => 'true',
                        'can_confirm' => 'false',
                        'content_item_return_url' => 'https://moodle.example.org/return',
                        'user_id' => '20001',
                        'data' => json_encode(['launchid' => 'launch-123']),
                    ],
                ],
            ],
        ];
    }
}