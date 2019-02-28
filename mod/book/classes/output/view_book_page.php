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
 * Class containing data for the view book page.
 *
 * @copyright  2019 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_book_page implements renderable, templatable {

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
    public function __construct($course, $book, $cm, $chapter, $edit) {
        $this->course = $course;
        $this->book = $book;
        $this->cm = $cm;
        $this->chapter = $chapter;
        $this->edit = $edit;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass $data
     */
    public function export_for_template(renderer_base $output) {
        global $OUTPUT;

        $navclasses = book_get_nav_classes();
        $context = context_module::instance($this->cm->id);
        $chapters = book_preload_chapters($this->book);

        $data = new stdClass();
        $data->navigation = $output->navigation($this->course, $this->book, $this->cm, $this->chapter, $this->edit);
        $data->navigationclass = $navclasses[$this->book->navstyle];

        if (!$this->book->customtitles) {
            if (!$this->chapter->subchapter) {
                $data->chaptertitle = book_get_chapter_title($this->chapter->id, $chapters, $this->book, $context);
            } else {
                $data->chaptertitle = book_get_chapter_title($chapters[$this->chapter->id]->parent, $chapters,
                        $this->book, $context);
                $data->chaptersubtitle = book_get_chapter_title($this->chapter->id, $chapters, $this->book, $context);
            }
        }
        if (\core_tag_tag::is_enabled('mod_book', 'book_chapters')) {
            $data->taglist = $OUTPUT->tag_list(\core_tag_tag::get_item_tags('mod_book',
                    'book_chapters', $this->chapter->id), null, 'book-tags');
        }
        $chaptertext = file_rewrite_pluginfile_urls($this->chapter->content, 'pluginfile.php', $context->id,
                'mod_book', 'chapter', $this->chapter->id);
        $data->chaptertext = format_text($chaptertext, $this->chapter->contentformat, array('noclean' => true,
                'overflowdiv' => true, 'context' => $context));
        $data->chapterclass = $this->chapter->hidden ? 'dimmed_text' : '';

        return $data;
    }
}
