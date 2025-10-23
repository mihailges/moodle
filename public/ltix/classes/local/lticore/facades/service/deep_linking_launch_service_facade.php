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
//field
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace core_ltix\local\lticore\facades\service;

/**
 * Service facade for deep linking launches.
 *
 * This facade provides the necessary interface implementation for deep linking
 * while handling the specifics of content selection flows.
 *
 * @package    core_ltix
 * @copyright  2025 Muhammad Arnaldo <muhammad.arnaldo@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deep_linking_launch_service_facade implements launch_service_facade_interface {
    /**
     * Constructor.
     *
     * @param \stdClass $toolconfig the tool configuration.
     * @param \core\context $context the launch context.
     * @param int $userid the user ID performing the launch.
     * @param string $returnurl the return URL for deep linking.
     * @param string $messagetype the message type.
     */
    public function __construct(
        protected \stdClass $toolconfig,
        protected \core\context $context,
        protected int $userid,
        protected string $returnurl,
        protected string $messagetype = 'LtiDeepLinkingRequest',
    ) {
    }

    /**
     * Get the target link URI for the deep linking request.
     *
     * @return string the target link URI.
     */
    public function get_target_link_uri(): string {
        // Call into each of the services, allowing them a chance to change the target_link_uri of the launch.
        $targetlinkuri = $this->toolconfig->lti_toolurl;

        foreach (\core_ltix\helper::get_services() as $service) {
            $targetlinkuri = $service->override_target_link_uri(
                toolconfig: $this->toolconfig,
                messagetype: $this->messagetype,
                targetlinkuri: $targetlinkuri,
                context: $this->context,
                userid: $this->userid,
            );
        }
        return $targetlinkuri;
    }

    /**
     * Get launch parameters specific to deep linking.
     *
     * @return array array of launch parameters.
     */
    public function get_launch_parameters(): array {
        $params = [];
        foreach (\core_ltix\helper::get_services() as $service) {
            $params = $service->get_launch_params(
                toolconfig: $this->toolconfig,
                messagetype: $this->messagetype,
                targetlinkuri: $this->toolconfig->lti_toolurl,
                context: $this->context,
                userid: $this->userid,
            );
        }
        return $params;
    }

    /**
     * Parse custom parameter values for deep linking.
     *
     * @param string $value the parameter value to parse.
     * @return string the parsed parameter value.
     */
    public function parse_custom_param_value(string $value): string {
        $val = $value;
        foreach (\core_ltix\helper::get_services() as $service) {
            $value = $service->parse_value($val);
            if ($val != $value) {
                break;
            }
        }
        return $value;
    }
}
