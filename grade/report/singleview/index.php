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
 * Displays the Single view
 *
 * @package   gradereport_singleview
 * @copyright 2014 Moodle Pty Ltd (http://moodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require_once('../../../config.php');
require_once($CFG->dirroot.'/lib/gradelib.php');
require_once($CFG->dirroot.'/grade/lib.php');

$courseid = required_param('id', PARAM_INT);
$groupid  = optional_param('group', null, PARAM_INT);

// Making this work with profile reports.
$userid   = optional_param('userid', null, PARAM_INT);
$itemid = optional_param('itemid', null, PARAM_INT);
$itemtype = optional_param('item', null, PARAM_TEXT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 100, PARAM_INT);

$edit = optional_param('edit', -1, PARAM_BOOL); // Sticky editing mode.

$courseparams = ['id' => $courseid];

if (!$course = $DB->get_record('course', $courseparams)) {
    throw new \moodle_exception('invalidcourseid');
}

require_login($course);
$context = context_course::instance($course->id);
// This is the normal requirements.
require_capability('gradereport/singleview:view', $context);
require_capability('moodle/grade:viewall', $context);
require_capability('moodle/grade:edit', $context);

$gpr = new grade_plugin_return([
    'type' => 'report',
    'plugin' => 'singleview',
    'courseid' => $courseid
]);

// Build editing on/off button for themes that need it.
$button = '';
if ($PAGE->user_allowed_editing() && !$PAGE->theme->haseditswitch) {
    if ($edit != - 1) {
        $USER->editing = $edit;
    }

    // Page params for the turn editing on button.
    $options = $gpr->get_options();
    $button = $OUTPUT->edit_button(new moodle_url($PAGE->url, $options), 'get');
}

// Last selected report session tracking.
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = [];
}
$USER->grade_last_report[$course->id] = 'singleview';

if ($itemtype === 'user' && is_null($itemid)) {
    $itemid = $userid;
}

$report = new gradereport_singleview\report\singleview($courseid, $gpr, $context, $itemtype, $itemid);

$currentgroup = $gpr->groupid;

$pageparams = [
    'id'        => $courseid,
    'userid'    => $userid,
    'itemid'    => $itemid,
    'item'      => $report->screen->name(),
    'page'      => $page,
    'perpage'   => $perpage,
];

if (!is_null($groupid)) {
    $pageparams['group'] = $groupid;
}

$PAGE->set_url(new moodle_url('/grade/report/singleview/index.php', $pageparams));
$PAGE->set_pagelayout('report');
$PAGE->set_other_editing_capability('moodle/grade:edit');

$reportname = $report->screen->heading();

if ($report->screen->name() == 'user' || $report->screen->name() == 'user_select') {
    $actionbar = new \gradereport_singleview\output\action_bar($context, $report, 'user');
} else if ($report->screen->name() == 'grade' || $report->screen->name() == 'grade_select') {
    $actionbar = new \gradereport_singleview\output\action_bar($context, $report, 'grade');
}

$useritem = $report->screen->name() == 'user' ? $report->screen->item : null;
print_grade_page_head($course->id, 'report', 'singleview', $reportname, false, $button,
        true, null, null, $useritem, $actionbar);

if ($data = data_submitted()) {
    // Must have a sesskey for all actions.
    require_sesskey();
    $result = $report->process_data($data);

    // If result is not null (because somedata was processed), warnings and success message should be displayed.
    if (!is_null($result)) {
        if (!empty($result->warnings)) {
            foreach ($result->warnings as $warning) {
                \core\notification::add($warning);
            }
        }

        // And notify the user of the success result.
        \core\notification::add(
            get_string('savegradessuccess', 'gradereport_singleview', count((array) $result->changecount)),
            \core\notification::SUCCESS
        );
    }
}

// Make sure we have proper final grades.
grade_regrade_final_grades_if_required($course);

echo $report->output();
$report->save_last_viewed();

if ($report->screen->get_itemid()) { // There is a selected item.
    $userreportrenderer = $PAGE->get_renderer('gradereport_singleview');
    // Add previous/next user navigation.
    echo $userreportrenderer->report_navigation($gpr, $courseid, $context, $report, $groupid, $report->screen->name(),
        $report->screen->get_itemid());
}

$event = \gradereport_singleview\event\grade_report_viewed::create(
    [
        'context' => $context,
        'courseid' => $courseid,
        'relateduserid' => $USER->id,
    ]
);
$event->trigger();

echo $OUTPUT->footer();
