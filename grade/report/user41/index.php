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
 * The gradebook user41 report
 *
 * @package   gradereport_user41
 * @copyright 2022 Mathew May (Mathew.solutions)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//defined('MOODLE_INTERNAL') || die();

require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/user/lib.php';
require_once($CFG->dirroot.'/grade/report/user41/lib.php');

$courseid = required_param('id', PARAM_INT);
$userid   = optional_param('userid', $USER->id, PARAM_INT);
$userview = optional_param('userview', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/grade/report/user41/index.php', array('id'=>$courseid)));
$PAGE->requires->js_call_amd('gradereport_user41/user', 'init');

if ($userview == 0) {
    $userview = get_user_preferences('gradereport_user41_view_user', GRADE_REPORT_USER_VIEW_USER);
} else {
    set_user_preference('gradereport_user_view_user41', $userview);
}

/// basic access checks
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    throw new \moodle_exception('invalidcourseid');
}
require_login($course);
$PAGE->set_pagelayout('report');

$context = context_course::instance($course->id);

require_capability('gradereport/user41:view', $context);

if (empty($userid)) {
    require_capability('moodle/grade:viewall', $context);

} else {
    if (!$DB->get_record('user', array('id'=>$userid, 'deleted'=>0)) or isguestuser($userid)) {
        throw new \moodle_exception('invaliduser');
    }
}

/**
 * @param $context
 * @param $userid
 * @param $course
 * @return bool
 * @throws coding_exception
 */
function has_access($context, $userid, $course): bool {
    if (has_capability('moodle/grade:viewall', $context)) {
        //ok - can view all course grades
        $access = true;

    } else if ($userid == $USER->id and has_capability('moodle/grade:view', $context) and $course->showgrades) {
        //ok - can view own grades
        $access = true;

    } else if (has_capability('moodle/grade:viewall', context_user::instance($userid)) and $course->showgrades) {
        // ok - can view grades of this user- parent most probably
        $access = true;
    } else {
        $access = false;
    }
    return $access;
}
$access = has_access($context, $USER, $userid, $course);
if (!$access) {
    // no access to grades!
    throw new \moodle_exception('nopermissiontoviewgrades', 'error',  $CFG->wwwroot.'/course/view.php?id='.$courseid);
}

/// return tracking object
$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'user41', 'courseid'=>$courseid, 'userid'=>$userid));

/// last selected report session tracking
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = array();
}
$USER->grade_last_report[$course->id] = 'user41';

// First make sure we have proper final grades.
grade_regrade_final_grades_if_required($course);

if (has_capability('moodle/grade:viewall', $context)) { //Teachers will see all student reports
    $groupmode    = groups_get_course_groupmode($course);   // Groups are being used
    $currentgroup = $gpr->groupid;

    if (!$currentgroup) {      // To make some other functions work better later
        $currentgroup = NULL;
    }

    $isseparategroups = ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));

    if ($isseparategroups and (!$currentgroup)) {
        // no separate group access, can view only self
        $userid = $USER->id;
        $user_selector = false;
    } else {
        $user_selector = true;
    }

    $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
    $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
    $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $context);

    if ($userview == GRADE_REPORT_USER_VIEW_USER) {
        $viewasuser = true;
    } else {
        $viewasuser = false;
    }

    if (empty($userid)) {
        // Show all users.
    } else { // Only show one user's report
    }
} else { //Students will see just their own report
}

if (isset($report)) {
    // Trigger report viewed event.
    $report->viewed();
} else {
    // No students warning.
}

// Print header
print_grade_page_head($COURSE->id, 'report', 'user41', ' ', false);

$defaulttype = $userid ? 'user' : 'select';
$itemid = optional_param('itemid', null, PARAM_INT);
$itemtype = optional_param('item', $defaulttype, PARAM_TEXT);
$report = new gradereport_user41($courseid, $gpr, $context, $itemtype, $itemid);
//echo $OUTPUT->header();
echo $report->output();
echo $OUTPUT->footer();
