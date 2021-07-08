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


namespace mod_data\form;

use context;
use context_user;
use moodle_exception;
use moodle_url;
use core_form\dynamic_form;
use tool_dataprivacy\api;
use tool_dataprivacy\external;

/**
 * Contact DPO modal form
 *
 * @package    tool_dataprivacy
 * @copyright  2021 Paul Holden <paulh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_as_preset extends dynamic_form {

    /**
     * Form definition
     */
    protected function definition() {

        $this->_form->addElement('hidden', 'd');
        $this->_form->setType('d', PARAM_INT);
        $this->_form->addElement('hidden', 'action', 'save2');
        $this->_form->setType('action', PARAM_ALPHANUM);
        $this->_form->addElement('text', 'name', get_string('name'));
        $this->_form->setType('name', PARAM_FILE);
        $this->_form->addRule('name', null, 'required');
        $this->_form->addElement('checkbox', 'overwrite', get_string('overwrite', 'data'), get_string('overrwritedesc', 'data'));
    }

    /**
     * Return form context
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        global $USER;

        return context_user::instance($USER->id);
    }

    /**
     * Check if current user has access to this form, otherwise throw exception
     *
     * @throws moodle_exception
     */
    protected function check_access_for_dynamic_submission(): void {
//        if (!api::can_contact_dpo()) {
//            throw new moodle_exception('errorcontactdpodisabled', 'tool_dataprivacy');
//        }
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * @return array
     */
    public function process_dynamic_submission() {
        global $DB;

        $data = $DB->get_record('data', array('id' => $this->get_data()->d), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $data->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('data', $data->id, $course->id, null, MUST_EXIST);
        $context = context_module::instance($cm->id, MUST_EXIST);

        if (!empty($this->get_data()->overwrite)) {
            $presets = data_get_available_presets($context);
            $selectedpreset = new stdClass();
            foreach ($presets as $preset) {
                if ($preset->name == $this->get_data()->name) {
                    $selectedpreset = $preset;
                    break;
                }
            }
            if (isset($selectedpreset->name)) {
                if (data_user_can_delete_preset($context, $selectedpreset)) {
                    data_delete_site_preset($this->get_data()->name);
                } else {
                    print_error('cannotoverwritepreset', 'data');
                }
            }
        }

        // If the preset exists now then we need to throw an error.
        $sitepresets = data_get_available_site_presets($context);
        foreach ($sitepresets as $key=>$preset) {
            if ($this->get_data()->name == $preset->name) {
                print_error('errorpresetexists', 'preset');
            }
        }

        // Save the preset now
        data_presets_save($course, $cm, $data, $this->get_data()->name);

        //return external::contact_dpo($this->get_data()->message);
    }

    /**
     * Load in existing data as form defaults (not applicable)
     */
    public function set_data_for_dynamic_submission(): void {
        return;
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        global $USER;

        return new moodle_url('/user/profile.php', ['id' => $USER->id]);
    }
}
