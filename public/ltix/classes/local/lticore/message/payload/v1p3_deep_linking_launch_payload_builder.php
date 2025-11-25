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

namespace core_ltix\local\lticore\message\payload;

use core_ltix\helper;
use core_ltix\local\lticore\facades\service\deep_linking_launch_service_facade;
use core_ltix\local\lticore\message\payload\custom\custom_param_parser;

/**
 * Generates payload data for a 1p3 deep linking launch.
 *
 * @package    core_ltix
 * @copyright  2025 Muhammad Arnaldo <muhammad.arnaldo@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class v1p3_deep_linking_launch_payload_builder implements v1p3_message_payload_builder_interface {
    /**
     * Constructor.
     *
     * @param \stdClass $toolconfig the tool configuration.
     * @param \stdClass $user the user data.
     * @param deep_linking_launch_service_facade $servicefacade the service facade.
     * @param custom_param_parser $customparamparser the custom parameter parser.
     * @param v1px_payload_converter_interface $claimconverter the claim converter.
     * @param int $contextid the launch context ID.
     */
    public function __construct(
        protected \stdClass $toolconfig,
        protected \stdClass $user,
        protected deep_linking_launch_service_facade $servicefacade,
        protected custom_param_parser $customparamparser,
        protected v1px_payload_converter_interface $claimconverter,
        protected int $contextid,
    ) {
    }

    /**
     * Get the array of all optional claims for this request type.
     *
     * @return array
     */
    public function get_claims(): array {
        // Create basic unformatted payload data
        $unformattedpayloaddata = [
            'context' => $this->get_unformatted_context_data(),
            'toolplatform' => $this->get_unformatted_tool_platform_data(),
        ];
        $unformattedpayloaddata = array_merge(...array_values($unformattedpayloaddata));

        // Add custom param data configured by the tool - do NOT substitute yet.
        $toolunformattedpayloaddata = $this->get_unformatted_custom_data();
        $unformattedpayloaddata = array_merge($toolunformattedpayloaddata, $unformattedpayloaddata);

        $serviceunformattedpayloaddata = $this->get_unformatted_service_custom_data();
        $unformattedpayloaddata = array_merge($unformattedpayloaddata, $serviceunformattedpayloaddata);

        // Perform substitution for custom params.
        $unformattedpayloaddata = $this->resolve_substitution($unformattedpayloaddata);

        // Now convert to 1p3 claims format.
        return $this->claimconverter->params_to_claims($unformattedpayloaddata);
    }

    /**
     * Resolve substitution for custom parameters.
     *
     * @param array $payloaddata the payload data.
     * @return array the resolved payload data.
     */
    protected function resolve_substitution(array $payloaddata): array {
        foreach ($payloaddata as $key => $value) {
            // Substitution is only performed for custom params.
            if (str_starts_with($key, 'custom_')) {
                $payloaddata[$key] = $this->customparamparser->parse($value, $payloaddata);
            }
        }

        return $payloaddata;
    }

    /**
     * Get the unformatted custom data from tool configuration.
     *
     * @return array the custom data.
     */
    protected function get_unformatted_custom_data(): array {
        $customdata = [];

        if (!empty($toolconfig->lti_customparameters)) {
            $toolcustom = helper::split_parameters($this->toolconfig->lti_customparameters);
            foreach ($toolcustom as $key => $val) {
                $key2 = helper::map_keyname($key);
                $customdata['custom_' . $key2] = $val;
            }
        }

        return $customdata;
    }

    /**
     * Get unformatted context data.
     *
     * @return array|null the context data, or null if not applicable.
     */
    protected function get_unformatted_context_data(): ?array {
        $context = \core\context::instance_by_id($this->contextid);

        if (($coursecontext = $context->get_course_context(false)) === false) {
            return null;
        }

        $course = get_course($coursecontext->instanceid);
        $contexttype = $course->format == 'site' ? 'Group'
            : 'CourseSection';

        return [
            'context_id' => $course->id,
            'context_label' => $context->get_context_name(),
            'context_title' => $context->get_context_name(),
            'context_type' => $contexttype
        ];
    }

    /**
     * Get unformatted tool platform data.
     *
     * @return array the tool platform data.
     */
    protected function get_unformatted_tool_platform_data(): array {
        global $CFG;
        if (!empty($CFG->ltix_institution_name)) {
            $name = trim(html_to_text($CFG->ltix_institution_name, 0));
        } else if (!empty($CFG->mod_lti_institution_name)) {
            // TODO final removal of the mod_lti_institution_name fallback code in Moodle 6.0.
            debugging('mod_lti_institution_name is deprecated. Please use ltix_institution_name instead.', DEBUG_DEVELOPER);
            $name = trim(html_to_text($CFG->mod_lti_institution_name, 0));
        } else {
            $name = get_site()->shortname;
        }

        return [
            'tool_consumer_info_product_family_code' => 'moodle',
            'tool_consumer_info_version' => strval($CFG->version),
            'tool_consumer_instance_guid' => helper::get_organizationid((array)$this->toolconfig), // TODO this expects get_type_config format and currently won't work.
            'tool_consumer_instance_name' => $name,
            'tool_consumer_instance_description' => trim(html_to_text(get_site()->fullname, 0)),
        ];
    }

    /**
     * Get unformatted service custom data.
     *
     * @return array the service custom data.
     */
    protected function get_unformatted_service_custom_data(): array {
        $servicecustomdata = [];
        foreach ($this->servicefacade->get_launch_parameters() as $param => $val) {
            $servicecustomdata['custom_' . $param] = $val;
        }
        return $servicecustomdata;
    }
}
