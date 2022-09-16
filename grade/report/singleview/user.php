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
 * The gradebook singleview report
 *
 * @package   gradereport_singleview
 * @copyright 2022 Mathew May (Mathew.solutions)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/grade/lib.php');

use gradereport_singleview\report\singleview_user as reportbase;

$courseid = required_param('id', PARAM_INT);
$userid   = optional_param('userid', $USER->id, PARAM_INT);
$userview = optional_param('userview', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/grade/report/singleview/user.php', ['id' => $courseid]));
$PAGE->requires->js_call_amd('gradereport_singleview/user', 'init');

if ($userview == 0) {
    $userview = get_user_preferences('gradereport_singleview_view_user', 0);
} else {
    set_user_preference('gradereport_singleview_view_user', $userview);
}

// Basic access checks.
if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    throw new \moodle_exception('invalidcourseid');
}
require_login($course);
$PAGE->set_pagelayout('report');

$context = context_course::instance($course->id);

require_capability('gradereport/singleview:view', $context);

if (empty($userid)) {
    require_capability('moodle/grade:viewall', $context);
} else {
    if (!$DB->get_record('user', ['id' => $userid, 'deleted' => 0]) || isguestuser($userid)) {
        throw new \moodle_exception('invaliduser');
    }
}

// Return tracking object.
$gpr = new grade_plugin_return(['type' => 'report', 'plugin' => 'singleview', 'courseid' => $courseid, 'userid' => $userid]);

// Last selected report session tracking.
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = [];
}
$USER->grade_last_report[$course->id] = 'singleview';

// First make sure we have proper final grades.
grade_regrade_final_grades_if_required($course);

$defaulttype = $userid ? 'user' : 'select';
$itemid = optional_param('itemid', null, PARAM_INT);
$itemtype = optional_param('item', $defaulttype, PARAM_TEXT);
$report = new reportbase($courseid, $gpr, $context, $itemtype, $itemid);

if (isset($report)) {
    // Trigger report viewed event.
    $report->viewed();
} else {
    // No students warning.
    echo html_writer::tag('div', '', array('class' => 'clearfix'));
    echo $OUTPUT->notification(get_string('nostudentsyet'));
}

// Print header.
print_grade_page_head($COURSE->id, 'report', 'singleview', ' ', false);

echo $report->output();
echo $OUTPUT->footer();
