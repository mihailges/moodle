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

/**
 * Handles creation of the launch request for an LTI 1.1 ContentItemSelectionRequest message type launch.
 *
 * @package    core_ltix
 * @copyright  2025 Muhammad Arnaldo <muhammad.arnaldo@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class v1p1_deep_linking_launch_request_builder extends v1p1_launch_request_builder {
    /**
     * Constructor.
     *
     * @param \stdClass $toolconfig the tool configuration data.
     * @param int $userid The user ID performing the launch.
     * @param string $launchid The launch ID for this request.
     * @param string $returnurl The return URL after content selection.
     * @param array $accepttypes Array of accepted content item types.
     * @param array $acceptpresentationtargets Array of accepted presentation targets.
     * @param array $acceptmediatypes Array of accepted media types.
     * @param bool $autocreate Whether auto creation is enabled.
     * @param bool $acceptmultiple Whether multiple selections are allowed.
     * @param string|null $placementtype The placement type for resolving deep linking URL.
     * @param array $roles The LIS or extension roles the launching user has for this launch.
     * @param array $extraparams Any optional extra parameters.
     */
    public function __construct(
        protected \stdClass $toolconfig,
        private int $userid,
        private string $launchid,
        private string $returnurl,
        private array $accepttypes = ['application/vnd.ims.lti.v1.ltilink'],
        private array $acceptpresentationtargets = ['frame', 'iframe', 'window'],
        private array $acceptmediatypes = ['*/*'],
        private bool $autocreate = false,
        private bool $acceptmultiple = true,
        protected ?string $placementtype = null,
        array $roles = [],
        array $extraparams = [],
    ) {
        parent::__construct(
            toolconfig: $toolconfig,
            messagetype: 'ContentItemSelectionRequest',
            launchurl: $this->resolve_target_link_uri(),
            roles: $roles,
            // Required params take precedence over extra params.
            extraparams: array_merge($extraparams, $this->create_required_request_params())
        );
    }

    /**
     * Get the required launch parameters for the message.
     *
     * @return array
     */
    protected function create_required_request_params(): array {
        // Add deep linking specific parameters.
        $deeplinkingparams = [
            'content_item_return_url' => $this->returnurl,
            'user_id' => $this->userid,
            'accept_types' => implode(',', $this->accepttypes),
            'accept_media_types' => implode(',', $this->acceptmediatypes),
            'accept_presentation_document_targets' => implode(',', $this->acceptpresentationtargets),
            'accept_unsigned' => 'false',
            'auto_create' => $this->autocreate ? 'true' : 'false',
            'accept_multiple' => $this->acceptmultiple ? 'true' : 'false',
            'can_confirm' => 'false',
            'accept_copy_advice' => 'false',
            'data' => json_encode(['launchid' => $this->launchid]),
        ];
        return $deeplinkingparams;
    }

    /**
     * Resolve the target link URI from placement config or fall back to the provided URL.
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

        // Fall back to the provided URL.
        return $this->toolconfig->lti_toolurl ?? '';
    }
}
