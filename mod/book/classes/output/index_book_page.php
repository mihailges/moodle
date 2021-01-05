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
 * Class containing data for the index book page.
 *
 * @package    mod_book
 * @copyright  2019 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_book\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use stdClass;
use templatable;
use context_module;

/**
 * Class containing data for the index book page.
 *
 * @copyright  2019 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class index_book_page implements renderable, templatable {

    /** @var object The course object. */
    protected $course;
    /** @var object The book object. */
    protected $book;
    /** @var object The course module object. */
    protected $cm;
    /** @var object The book chapter object. */
    protected $chapter;
    /** @var bool If editing is enabled. */
    protected $edit;

    /**
     * Construct this renderable.
     *
     * @param object $course The course object.
     * @param object $book The book object.
     * @param object $cm The course module object.
     * @param object $chapter The book chapter object.
     * @param bool $edit If editing is enabled.
     */
    public function __construct($course) {
        $this->course = $course;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass $data
     */
    public function export_for_template(renderer_base $output) {
        global $OUTPUT;

        $data = new stdClass();
        $data->issectionscourseformat = $usersections = course_format_uses_sections($this->course->format);
        $data->formatname = $data->issectionscourseformat ?
            get_string('sectionname', 'format_' . $this->course->format) :
            get_string('lastmodified');
        $data->courseformat = $this->course->format;
        $books = get_all_instances_in_course('book', $this->course);

        $currentsection = '';
        $data->books = array_map(function($book) use ($usersections, &$currentsection) {
            $context = context_module::instance($book->coursemodule);
            $isfirstsection = false;
            if (!$iscurrentsection = $book->section == $currentsection) {
                $isfirstsection = ($currentsection == '');
                $currentsection = $book->section;
            }
            return array(
                'iscurrentsection' => $iscurrentsection,
                'isfirstsection' => $isfirstsection,
                'hassection' => !empty($book->section),
                'sectionname' => get_section_name($this->course, $book->section),
                'timemodified' => userdate($book->timemodified),
                'isvisible' => $book->visible,
                'url' => new \moodle_url('view.php', array('id' => $context->instanceid)),
                'name' => format_string($book->name),
                'description' => format_module_intro('book', $book, $context->instanceid)
            );
        }, get_all_instances_in_course('book', $this->course));

        return $data;
    }
}
