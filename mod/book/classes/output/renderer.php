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
 * Defines the renderer for the book module.
 *
 * @package    mod_book
 * @copyright  2020 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_book\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use context_module;
use moodle_url;
use moodle_exception;
use mod_book\local\factories\renderer as renderer_factory;

/**
 * The renderer for the book module.
 *
 * @copyright  2020 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /** @var int The id of the next chapter. */
    public $nextchapterid;

    /**
     * Render the navigation links for the book chapters.
     *
     * @param object $course The course object.
     * @param object $book The book object.
     * @param object $cm The course module object.
     * @param object $chapter The book chapter object.
     * @param bool $edit If editing is enabled.
     * @return string Book chapter navigation links.
     */
    public function render_navigation($course, $book, $cm, $chapter, $edit) {
        global $DB;

        $context = context_module::instance($cm->id);
        // Read chapters.
        $chapters = book_preload_chapters($book);

        // Prepare chapter navigation icons.
        $previd = null;
        $prevtitle = null;
        $nextid = null;
        $nexttitle = null;
        $last = null;
        foreach ($chapters as $ch) {
            if (!$edit and $ch->hidden) {
                continue;
            }
            if ($last == $chapter->id) {
                $nextid = $this->nextchapterid = $ch->id;
                $nexttitle = book_get_chapter_title($ch->id, $chapters, $book, $context);
                break;
            }
            if ($ch->id != $chapter->id) {
                $previd = $ch->id;
                $prevtitle = book_get_chapter_title($ch->id, $chapters, $book, $context);
            }
            $last = $ch->id;
        }

        $data = new \stdClass();
        $data->isrighttoleft = right_to_left();
        $data->hasprevious = !empty($previd);
        if ($data->hasprevious) {
            $data->prevurl = new moodle_url('/mod/book/view.php', ['id' => $cm->id, 'chapterid' => $previd]);
            $data->prevtitle = $prevtitle;
        }
        $data->hasnext = !empty($nextid);
        if ($data->hasnext) {
            $data->nexturl = new moodle_url('/mod/book/view.php', ['id' => $cm->id, 'chapterid' => $nextid]);
            $data->nexttitle = $nexttitle;
        } else {
            $sec = $DB->get_field('course_sections', 'section', array('id' => $cm->section));
            $data->returnurl = course_get_url($course, $sec);
        }

        // If style of navigation is set.
        if ($book->navstyle) {
            if ($book->navstyle == BOOK_LINK_IMAGE) { // If image navigation is set.
                return parent::render_from_template('mod_book/book_images_navigation', $data);
            } else if ($book->navstyle == BOOK_LINK_TEXT) { // If text navigation is set.
                return parent::render_from_template('mod_book/book_text_navigation', $data);
            }
        }

        return '';
    }

    /**
     * Render the view book page.
     *
     * @param view_book_page $page
     * @return string html for the page
     * @throws moodle_exception
     */
    public function render_view_book_page(view_book_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_book/view_book', $data);
    }

    /**
     * Render the index book page.
     *
     * @param view_book_page $page
     * @return string html for the page
     * @throws moodle_exception
     */
    public function render_index_book_page(index_book_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_book/index_book', $data);
    }
}
