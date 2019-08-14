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
 * Defines the form for filtering report logs.
 *
 * @package    report_log
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_log;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->libdir.'/formslib.php');

/**
 * The form for filtering report logs.
 *
 * @copyright 2019 Mihail Geshoski <mihail@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_logs_filter_form extends \moodleform {

    /**
     * Form definition.
     */
    protected function definition() {

        $mform = $this->_form;
        $mform->addElement('header', 'content', get_string('filters'));
        $mform->setExpanded('content', true);

        $mform->addElement('hidden', 'chooselog', 1);
        $mform->setType('chooselog', PARAM_INT);

        $mform->addElement('hidden', 'showusers', $this->_customdata['showusers']);
        $mform->setType('showusers', PARAM_INT);

        $mform->addElement('hidden', 'showcourses',  $this->_customdata['showcourses']);
        $mform->setType('showcourses', PARAM_INT);

        // Add course selector.
        $selectedcourseid = empty($this->_customdata['course']) ? 0 : $this->_customdata['course']->id;
        $sitecontext = \context_system::instance();
        $courses = $this->_customdata['courses'];
        if (!empty($courses) && $this->_customdata['showcourses']) {
            $mform->addElement('select', 'id', get_string('selectacourse'), $courses);
            $mform->setDefault('id', $selectedcourseid);
        } else {
            $courses = array();
            $courses[$selectedcourseid] = get_course_display_name_for_list($this->_customdata['course']) .
                (($selectedcourseid == SITEID) ? ' (' . get_string('site') . ') ' : '');

            $mform->addElement('select', 'id', get_string('selectacourse'), $courses);
            $mform->setDefault('id', $selectedcourseid);

            // Check if user is admin and this came because of limitation on number of courses to show in dropdown.
            if (has_capability('report/log:view', $sitecontext)) {
                $a = new \stdClass();
                $a->url = new \moodle_url('/report/log/index.php', array('chooselog' => 0,
                    'group' => $this->_customdata['group'], 'user' => $this->_customdata['userid'],
                    'id' => $selectedcourseid, 'modid' => $this->_customdata['modid'],
                    'showcourses' => 1, 'showusers' => $this->_customdata['showusers']));
                $a->url = $a->url->out(false);
                $mform->addElement('static', 'morecourses', '',
                        get_string('logtoomanycourses', 'moodle', $a));
            }
        }

        // Add group selector.
        $groups = $this->_customdata['groups'];
        if (!empty($groups)) {
            $mform->addElement('select', 'group', get_string('selectagroup'), $groups);
            $mform->setDefault('group', $this->_customdata['groupid']);
        }

        // Add user selector.
        $users = $this->_customdata['users'];

        if ($this->_customdata['showusers']) {
            $mform->addElement('select', 'user', get_string('selctauser'),
                    array("" => get_string("allparticipants")) + $users);
            $mform->setDefault('user', $this->_customdata['userid']);
        } else {
            $users = array();
            if (!empty($this->_customdata['userid'])) {
                $users[$this->_customdata['userid']] = $this->_customdata['selecteduserfullname'];
            } else {
                $users[0] = get_string('allparticipants');
            }

            $mform->addElement('select', 'user', get_string('selctauser'), $users);
            $mform->setDefault('user', $this->_customdata['userid']);

            $a = new \stdClass();
            $a->url = new \moodle_url('/report/log/index.php', array('chooselog' => 0,
                'group' => $this->_customdata['group'], 'user' => $this->_customdata['userid'],
                'id' => $selectedcourseid, 'modid' => $this->_customdata['modid'],
                'showusers' => 1, 'showcourses' => $this->_customdata['showcourses']));
            $a->url = $a->url->out(false);
            $mform->addElement('static', 'moreusers', '', get_string('logtoomanyusers', 'moodle', $a));
        }

        // Add start date selector.
        $mform->addElement('date_selector', 'filterstartdate', get_string('from'), array('optional' => true));
        $mform->setType('filterstartdate', PARAM_INT);
        $mform->setDefault('filterstartdate', $this->_customdata['startdate']);

        // Add end date selector.
        $mform->addElement('date_selector', 'filterenddate', get_string('to'), array('optional' => true));
        $mform->setType('filterenddate', PARAM_INT);
        $mform->setDefault('filterenddate', $this->_customdata['enddate']);

        // Add activity selector.
        $modid = !empty($this->_customdata['modid']) ? $this->_customdata['modid'] : "";
        $activities = $this->_customdata['activities'];
        $defaultoption = array('' => get_string('allactivities'));
        // Check whether the activities array is multidimensional.
        $arrval = array_filter($activities, 'is_array');
        // If the activities array is multidimensional, present the returned data
        // in a selectgroups mform element, otherwise use a select mform element.
        if (count($arrval) > 0) {
            $options[''] = $defaultoption;
            foreach ($activities as $activity) {
                foreach ($activity as $key => $value) {
                    $options[$key] = $value;
                }
            }
            $mform->addElement('selectgroups', 'modid', get_string('activities'),
                    $options);
        } else {
            $mform->addElement('select', 'modid', get_string('activities'),
                    $defaultoption + $activities);
            $mform->setDefault('modid', $modid);
        }
        $mform->setDefault('modid', $modid);

        // Add actions selector.
        $mform->addElement('select', 'modaction', get_string('actions'),
                array_merge(array("" => get_string("allactions")), $this->_customdata['actions']));
        $mform->setDefault('modaction', $this->_customdata['action']);

        // Add origin.
        $origin = $this->_customdata['originoptions'];
        $mform->addElement('select', 'origin', get_string('origin', 'report_log'), $origin);
        $mform->setDefault('origin', $this->_customdata['origin']);

        // Add edulevel.
        $edulevel = $this->_customdata['eduleveloptions'];
        $mform->addElement('select', 'edulevel', get_string('edulevel'), $edulevel);
        $mform->addHelpButton('edulevel', 'edulevel');
        $mform->setDefault('edulevel', $this->_customdata['edulevel']);

        // Add reader option.
        // If there is some reader available then only show submit button.
        $readers = $this->_customdata['readers'];
        if (!empty($readers)) {
            if (count($readers) == 1) {
                $mform->addElement('hidden', 'logreader',  key($readers));
                $mform->setType('logreader', PARAM_INT);
            } else {
                $mform->addElement('select', 'logreader', get_string('selectlogreader', 'report_log'),
                        $readers);
                $mform->setDefault('logreader', $this->_customdata['selectedlogreader']);
            }
            $mform->addElement('submit', 'submitbutton', get_string('gettheselogs'));
        }
    }
}
