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

namespace core_ltix\lticore\message\request\builder\v1p3;

use core_ltix\constants;
use core_ltix\local\lticore\message\request\builder\v1p3\v1p3_deep_linking_launch_request_builder;
use core_ltix\local\ltiopenid\jwks_helper;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * v1p3 deep linking launch request builder tests.
 *
 * @package    core_ltix
 * @copyright  2025 Muhammad Arnaldo <muhammad.arnaldo@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(v1p3_deep_linking_launch_request_builder::class)]
class v1p3_deep_linking_launch_request_builder_test extends \basic_testcase {
    /**
     * Test building the deep linking initiate login message.
     *
     * @param array $params Builder constructor parameters.
     * @param array $expected Expected parts of generated message and claims.
     * @return void
     */
    #[DataProvider('build_message_provider')]
    public function test_build_message(array $params, array $expected): void {
        $builder = new v1p3_deep_linking_launch_request_builder(...$params);
        $message = $builder->build_message();

        // The message should target the tool's initiate login URL.
        $this->assertEquals($expected['ltimessageurl'], $message->get_url());

        $messageparams = $message->get_parameters();
        foreach ($expected['ltimessageparams'] as $k => $v) {
            $this->assertEquals($v, $messageparams[$k]);
        }

        // There should be a partially complete JWT in lti_message_hint.
        $this->assertNotEmpty($messageparams['lti_message_hint']);
        $decoded = JWT::decode($messageparams['lti_message_hint'], JWK::parseKeySet(jwks_helper::get_jwks()));
        $decoded = json_decode(json_encode($decoded), true);

        // Verify required claims inside the lti_message_hint token.
        foreach ($expected['ltimessagehintjwtclaims'] as $claim => $value) {
            $this->assertEquals($value, $decoded[$claim]);
        }

        $this->assertIsString($decoded['nonce']);
        $this->assertIsInt($decoded['exp']);
        $this->assertIsInt($decoded['iat']);
    }

    /**
     * Provider for test_build_message().
     *
     * @return array Test cases.
     */
    public static function build_message_provider(): array {
        return [
            'basic deep link' => [
                'params' => [
                    'toolconfig' => (object) [
                        'typeid' => 55555,
                        'lti_toolurl' => 'https://tool.example.com',
                        'lti_clientid' => 'client-1234',
                        'lti_ltiversion' => '1.3.0',
                        'lti_initiatelogin' => 'https://tool.example.com/lti/initiatelogin',
                        'lti_organizationid' => 'https://platform.example.com',
                        'lti_launchcontainer' => constants::LTI_LAUNCH_CONTAINER_EMBED_NO_BLOCKS,
                        'lti_acceptgrades' => constants::LTI_SETTING_NEVER,
                        'lti_customparameters' => "",
                    ],
                    'issuer' => 'https://moodle.example.org',
                    'userid' => 20001,
                    'launchid' => 'launch-id-1',
                    'returnurl' => 'https://moodle.example.org/return',
                ],
                'expected' => [
                    'ltimessageurl' => 'https://tool.example.com/lti/initiatelogin',
                    'ltimessageparams' => [
                        'iss' => 'https://moodle.example.org',
                        'target_link_uri' => 'https://tool.example.com',
                        'login_hint' => '20001',
                        'client_id' => 'client-1234',
                        'lti_deployment_id' => '55555',
                    ],
                    'ltimessagehintjwtclaims' => [
                        'tool_registration_id' => '55555',
                        'iss' => 'https://moodle.example.org',
                        'aud' => 'client-1234',
                        constants::LTI_JWT_CLAIM_PREFIX . '/claim/message_type' => 'LtiDeepLinkingRequest',
                        constants::LTI_JWT_CLAIM_PREFIX . '/claim/deployment_id' => '55555',
                        constants::LTI_JWT_CLAIM_PREFIX . '/claim/version' => constants::LTI_VERSION_1P3,
                        constants::LTI_JWT_CLAIM_PREFIX . '-dl/claim/deep_linking_settings' => [
                            'deep_link_return_url' => 'https://moodle.example.org/return',
                            'accept_types' => ['ltiResourceLink'],
                            'accept_presentation_document_targets' => ['frame', 'iframe', 'window'],
                            'accept_media_types' => ['*/*'],
                            'auto_create' => false,
                            'accept_multiple' => true,
                            'data' => json_encode(['launchid' => 'launch-id-1']),
                        ],
                        constants::LTI_JWT_CLAIM_PREFIX . '/claim/target_link_uri' => 'https://tool.example.com',
                    ],
                ],
            ],
        ];
    }
}
