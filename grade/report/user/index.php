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
 * The gradebook user report
 *
 * @package   gradereport_user
 * @copyright 2007 Moodle Pty Ltd (http://moodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/user/lib.php';

$courseid = required_param('id', PARAM_INT);
$userid   = optional_param('userid', null, PARAM_INT);
$userview = optional_param('userview', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/grade/report/user/index.php', ['id' => $courseid]));

if ($userview == 0) {
    $userview = get_user_preferences('gradereport_user_view_user', GRADE_REPORT_USER_VIEW_USER);
} else {
    set_user_preference('gradereport_user_view_user', $userview);
}

// Basic access checks.
if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    throw new \moodle_exception('invalidcourseid');
}
require_login($course);
$PAGE->set_pagelayout('report');

$context = context_course::instance($course->id);
require_capability('gradereport/user:view', $context);

$currentgroup = groups_get_course_group($course, true) ?? null;
$gradableusers = get_gradable_users($courseid, $currentgroup);

if ($userid === 0) {
    require_capability('moodle/grade:viewall', $context);
} else if ($userid && !array_key_exists($userid, $gradableusers)) {
    throw new \moodle_exception('invaliduser');
}

$access = false;
if (has_capability('moodle/grade:viewall', $context)) {
    // User can view all course grades.
    $access = true;
} else if (($userid == $USER->id || is_null($userid)) && has_capability('moodle/grade:view', $context) && $course->showgrades) {
    // User can view own grades.
    $access = true;
} else if (has_capability('moodle/grade:viewall', context_user::instance($userid)) && $course->showgrades) {
    // User can view grades of this user, The user is an parent most probably.
    $access = true;
}

if (!$access) {
    // The user has no access to grades.
    throw new \moodle_exception('nopermissiontoviewgrades', 'error',  $CFG->wwwroot.'/course/view.php?id='.$courseid);
}

// Initialise the grade tracking object.
$gpr = new grade_plugin_return(['type' => 'report', 'plugin' => 'user', 'courseid' => $courseid, 'userid' => $userid]);

// Infer the users previously selected report via session tracking.
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = [];
}
$USER->grade_last_report[$course->id] = 'user';

// First make sure we have proper final grades.
grade_regrade_final_grades_if_required($course);

$gradesrenderer = $PAGE->get_renderer('core_grades');

// Teachers will see all student reports.
if (has_capability('moodle/grade:viewall', $context)) {
    $isseparategroups = ($course->groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context));

    if ($isseparategroups && (!$currentgroup)) {
        // No separate group access, The user can view only themselves.
        $userid = $USER->id;
    }

    // If there is a stored (last viewed) user in a session variable and it is a valid gradable user, bypass the user
    // select zero state and display the report for that user.
    $lastvieweduserid = $SESSION->gradereport_user["useritem-{$context->id}"] ?? null;
    if (is_null($userid) && !is_null($lastvieweduserid) && array_key_exists($lastvieweduserid, $gradableusers)) {
        $userid = $lastvieweduserid;
    }

    $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
    $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
    $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $context);

    if ($userview == GRADE_REPORT_USER_VIEW_USER) {
        $viewasuser = true;
    } else {
        $viewasuser = false;
    }

    if (empty($gradableusers)) { // There are no available gradable users.
        $actionbar = new \gradereport_user\output\action_bar($context, $userview, null, $currentgroup);
        print_grade_page_head($courseid, 'report', 'user', ' ', false, null, true,
            null, null, null, $actionbar);
        echo html_writer::tag('div', '', ['class' => 'clearfix']);
        $message = $currentgroup ? get_string('nostudentsingroup') : get_string('nostudentsyet');
        echo $OUTPUT->notification($message);
    } else if (is_null($userid)) { // Zero state.
        $actionbar = new \gradereport_user\output\action_bar($context, $userview, null, $currentgroup, $gradableusers);
        // Print header.
        print_grade_page_head($courseid, 'report', 'user', ' ', false, null, true,
            null, null, null, $actionbar);
        $userreportrenderer = $PAGE->get_renderer('gradereport_user');
        // Output the zero state content.
        echo $userreportrenderer->zero_state();
    } else {
        // Store the id of the current user item in a session variable which represents the last viewed item.
        $SESSION->gradereport_user["useritem-{$context->id}"] = $userid;

        $gui = new graded_users_iterator($course, null, $currentgroup);
        $gui->require_active_enrolment($showonlyactiveenrol);
        $gui->init();

        if ($userid == 0) { // Show all reports.
            $actionbar = new \gradereport_user\output\action_bar($context, $userview, 0, $currentgroup, $gradableusers);
            print_grade_page_head($courseid, 'report', 'user', ' ', false, null, true,
                null, null, null, $actionbar);

            foreach ($gradableusers as $user) {
                $report = new gradereport_user\report\user($courseid, $gpr, $context, $user->id, $viewasuser);
                $userheading = $gradesrenderer->user_heading($report->user, $courseid, false);

                echo $OUTPUT->heading($userheading);

                if ($report->fill_table()) {
                    echo $report->print_table(true);
                }
            }
        } else { // Show one user's report.
            $report = new gradereport_user\report\user($courseid, $gpr, $context, $userid, $viewasuser);
            $actionbar = new \gradereport_user\output\action_bar($context, $userview, $report->user->id, $currentgroup,
                $gradableusers);

            print_grade_page_head($courseid, 'report', 'user',
                $gradesrenderer->user_heading($report->user, $courseid),
                false, false, true, null, null, null, $actionbar);

            // Make sure that the user is part of the current group (if applicable) before displaying the report.
            if ($currentgroup && !groups_is_member($currentgroup, $userid)) {
                echo $OUTPUT->notification(get_string('groupusernotmember', 'error'));
            } else {
                if ($report->fill_table()) {
                    echo $report->print_table(true);
                }
                $userreportrenderer = $PAGE->get_renderer('gradereport_user');
                // Add previous/next user navigation.
                echo $userreportrenderer->user_navigation($gui, $report->user->id, $courseid, $gradableusers);
            }
        }
    }
} else {
    // Students will see just their own report.
    // Create a report instance.
    $report = new gradereport_user\report\user($courseid, $gpr, $context, $userid ?? $USER->id);
    $userheading = $gradesrenderer->user_heading($report->user, $courseid, false);

    // Print the page.
    print_grade_page_head($courseid, 'report', 'user', ' ');

    echo $OUTPUT->heading($userheading);

    if ($report->fill_table()) {
        echo $report->print_table(true);
    }
}

if (isset($report)) {
    // Trigger report viewed event.
    $report->viewed();
}

echo $OUTPUT->footer();
