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
 * This page lists all the instances of book in a particular course
 *
 * @package    mod_book
 * @copyright  2004-2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/locallib.php');

$id = required_param('id', PARAM_INT); // Course ID.

if (!$course = $DB->get_record('course', array('id' => $id), '*')) {
    print_error('invalidcourseid');
}

unset($id);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

// Get all required strings
$strbooks = get_string('modulenameplural', 'mod_book');

$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/book/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname . ': ' . $strbooks);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strbooks);
echo $OUTPUT->header();

\mod_book\event\course_module_instance_list_viewed::create_from_course($course)->trigger();

$renderer = $PAGE->get_renderer('mod_book');

$page = new mod_book\output\index_book_page($course);
$content = $renderer->render($page);

// Get all the appropriate data.
//echo $renderer->render_book_instances_in_course($course, $books);
echo $content;
echo $OUTPUT->footer();
