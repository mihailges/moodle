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
 * My Courses - Define the system level my courses page that  personal instances are based off.
 *
 * - each user can currently have their own page (cloned from system and then customised)
 * - only the user can see their own dashboard
 * - users can add any blocks they want
 * - the administrators can define a default site dashboard for users who have
 *   not created their own dashboard
 *
 * This script implements the user's view of the dashboard, and allows editing
 * of the dashboard.
 *
 * @package    core
 * @subpackage my
 * @copyright  2021 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);
require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->libdir.'/adminlib.php');

$edit   = optional_param('edit', -1, PARAM_BOOL);    // Turn editing on and off.
$resetall = optional_param('resetall', false, PARAM_BOOL);

$header = "$SITE->shortname: ".get_string('mycourses')." (".get_string('mypage', 'admin').")";

// Lock down editing on this page to elevated roles only.
$PAGE->set_blocks_editing_capability('moodle/site:manageblocks');
admin_externalpage_setup('mypage', '', null, '', array('pagelayout' => 'mydashboard'));

// If we are resetting all, just output a progress bar.
if ($resetall && confirm_sesskey()) {
    echo $OUTPUT->header($header);
    echo $OUTPUT->heading(get_string('resettingdashboards', 'my'), 3);

    $progressbar = new progress_bar();
    $progressbar->create();

    \core\session\manager::write_close();
    my_reset_page_for_all_users(MY_PAGE_PRIVATE, 'my-index', $progressbar, MY_PAGE_COURSES);
    core\notification::success(get_string('alldashboardswerereset', 'my'));
    echo $OUTPUT->continue_button(new moodle_url('/my/coursessys.php'));
    echo $OUTPUT->footer();
    die();
}

// Override pagetype to show blocks properly.
$PAGE->set_pagetype('my-index');
$PAGE->has_secondary_navigation_setter(false);

$PAGE->set_title($header);
$PAGE->set_heading($header);
$PAGE->set_url('/my/coursessys.php');
$PAGE->blocks->add_region('content');

// Get the My Moodle page info.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page(null, MY_PAGE_PRIVATE, MY_PAGE_COURSES)) {
    throw new Exception('mymoodlesetup');
}
$PAGE->set_subpage($currentpage->id);

// Display a button to reset everyone's dashboard.
$url = new moodle_url('/my/coursessys.php');
$url->params(['resetall' => true, 'sesskey' => sesskey()]);
$button = $OUTPUT->single_button($url, get_string('reseteveryonesdashboard', 'my'));

// Add button for editing page.
$editurl = new moodle_url('/my/coursessys.php');
if ($PAGE->user_allowed_editing()) {
    if ($edit != -1) {
        $USER->editing = $edit;
    }
    $editbutton = $OUTPUT->edit_button($editurl);
}

$PAGE->set_button($button . $editbutton);

echo $OUTPUT->header();

echo $OUTPUT->custom_block_region('content');

echo $OUTPUT->footer();
