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

namespace core_ltix\local\lticore\message\request\builder\v1p3;

/**
 * Handles creation of the init login request for an LtiDeepLinkingRequest message type launch.
 *
 * @package    core_ltix
 * @copyright  2025 Muhammad Arnaldo <muhammad.arnaldo@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class v1p3_deep_linking_launch_request_builder extends v1p3_launch_request_builder {
    /**
     * Constructor.
     *
     * @param \stdClass $toolconfig The tool configuration data, must be sourced from \core_ltix\helper::get_type_type_config().
     * @param string $issuer The issuer URL.
     * @param int $userid The ID of the user performing the launch.
     * @param string $launchid The launch ID for this request.
     * @param string $returnurl The return URL after content selection.
     * @param array $accepttypes Array of accepted content item types for this launch. Defaults to ['ltiResourceLink'].
     * @param array $acceptpresentationtargets Array of accepted presentation targets. Defaults to ['frame', 'iframe', 'window'].
     * @param array $acceptmediatypes Array of accepted media types.
     * @param bool $autocreate Whether auto creation is enabled. Defaults to false.
     * @param bool $acceptmultiple Whether multiple selections are allowed. Defaults to true.
     * @param string|null $placementtype The placement type for resolving deep linking URL. Defaults to null.
     * @param array $roles The LIS or extension roles the launching user has for this launch. Defaults to [].
     * @param array $extraclaims Any optional extra claims. Defaults to [].
     */
    public function __construct(
        protected \stdClass $toolconfig,
        string $issuer,
        int $userid,
        private string $launchid,
        private string $returnurl,
        private array $accepttypes = ['ltiResourceLink'], // Only LTI links are currently supported.
        private array $acceptpresentationtargets = ['frame', 'iframe', 'window'],
        private array $acceptmediatypes = ['*/*'],
        private bool $autocreate = false,
        private bool $acceptmultiple = true,
        private ?string $placementtype = null,
        array $roles = [],
        array $extraclaims = []
    ) {
        // Required claims trump extra claims.
        $claims = array_merge($extraclaims, $this->create_required_request_claims());

        parent::__construct(
            toolconfig: $toolconfig,
            messagetype: 'LtiDeepLinkingRequest',
            issuer: $issuer,
            targetlinkuri: $this->resolve_target_link_uri(),
            loginhint: strval($userid),
            roles: $roles,
            extraclaims: $claims
        );
    }

    /**
     * Adds required claims for this message type.
     *
     * @return array the array of claims.
     */
    protected function create_required_request_claims(): array {
        $claimprefix = \core_ltix\constants::LTI_JWT_CLAIM_PREFIX;
        return [
            $claimprefix . '-dl/claim/deep_linking_settings' => [
                'deep_link_return_url' => $this->returnurl,
                'accept_types' => $this->accepttypes,
                'accept_presentation_document_targets' => $this->acceptpresentationtargets,
                'accept_media_types' => $this->acceptmediatypes,
                'auto_create' => $this->autocreate,
                'accept_multiple' => $this->acceptmultiple,
                'data' => json_encode(['launchid' => $this->launchid]),
            ],
            $claimprefix . '/claim/target_link_uri' => $this->resolve_target_link_uri(),
        ];
    }

    /**
     * Resolve the target link URI from placement config or fall back to the tool URL.
     *
     * @return string the target link URI.
     */
    protected function resolve_target_link_uri(): string {
        // If placement type is provided, try to get the deep_linking_url from placement config.
        if ($this->placementtype) {
            $placementconfig = \core_ltix\helper::get_placement_config_by_placement_type(
                $this->toolconfig->typeid,
                $this->placementtype
            );

            if (!empty($placementconfig->deep_linking_url)) {
                return $placementconfig->deep_linking_url;
            }
        }

        // Fall back to the default tool URL if no deep linking URL is configured.
        return $this->toolconfig->lti_toolurl ?? '';
    }
}
