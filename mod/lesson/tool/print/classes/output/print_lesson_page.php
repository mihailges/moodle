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
 * Class containing data for the view book page.
 *
 * @package    lessontool_print
 * @copyright  2019 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lessontool_print\output;

defined('MOODLE_INTERNAL') || die();

use coding_exception;
use dml_exception;
use moodle_exception;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use context_module;

/**
 * Class containing data for the print lesson.
 *
 * @copyright  2019 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class print_lesson_page implements renderable, templatable {

    /**
     * @var object $book The book object.
     */
    protected $lesson;

    /**
     * @var object $cm The course module object.
     */
    protected $cm;

    /**
     * Construct this renderable.
     *
     * @param object $book The lesson
     * @param object $cm The course module
     */
    public function __construct($lesson) {
        $this->lesson = $lesson;
        //$this->cm = $cm;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        $pageid = $this->lesson->firstpageid;
        $data = new stdClass();
        while ($pageid != 0) {
            $page = $this->lesson->load_page($pageid);

//            $output->render_print_content_page($page);

//            $methods = get_class_methods($output);

//            if (!method_exists($output, $function = 'render_print_' . strtolower($page->get_typestring()) . '_page')) {
//                continue;
//            }

            $function = 'render_print_' . strtolower($page->get_idstring()) . '_page';
            list($pagecontent, $pagetype) = call_user_func(array($output, $function), $page);
//            $pagecontent = $output->render_print_content_page($page);
            $pagedata = new stdClass();
            $pagedata->content = $pagecontent;
            $pagedata->type = $pagetype;
            $data->pages[] = $pagedata;

            $data->pageid = $pageid;
            $pageid = $page->nextpageid;
        }



        //$context = context_module::instance($this->cm->id);



//        // Print dialog link.
//        $data->printdialoglink = $output->render_print_book_dialog_link();
//        $data->booktitle = $OUTPUT->heading(format_string($this->book->name, true,
//            array('context' => $context)), 1);
//        $data->bookintro = format_text($this->book->intro, $this->book->introformat,
//            array('noclean' => true, 'context' => $context));
//        $data->sitelink = \html_writer::link(new moodle_url($CFG->wwwroot),
//            format_string($SITE->fullname, true, array('context' => $context)));
//        $data->coursename = format_string($course->fullname, true, array('context' => $context));
//        $data->modulename = format_string($this->book->name, true, array('context' => $context));
//        $data->username = fullname($USER, true);
//        $data->printdate = userdate(time());
//        $data->toc = $output->render_print_book_toc($chapters, $this->book, $this->cm);
//        foreach ($chapters as $ch) {
//            list($chaptercontent, $chaptervisible) = $output->render_print_book_chapter($ch, $chapters, $this->book,
//                $this->cm);
//            $chapter = new stdClass();
//            $chapter->content = $chaptercontent;
//            $chapter->visible = $chaptervisible;
//            $data->chapters[] = $chapter;
//        }
        return $data;
    }
}
