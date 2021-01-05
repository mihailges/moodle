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
 * Exported post builder class.
 *
 * @package    mod_book
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_book\local\builders\navigation;

defined('MOODLE_INTERNAL') || die();

use mod_book\local\builders\builder as builder;

/**
 * Exported builder class.
 *
 * This class is an implementation of the builder pattern (loosely). It is responsible
 * for taking a set of related forums, discussions, and posts and generate the exported
 * version of the posts.
 *
 * It encapsulates the complexity involved with exporting posts. All of the relevant
 * additional resources will be loaded by this class in order to ensure the exporting
 * process can happen.
 *
 * See this doc for more information on the builder pattern:
 * https://designpatternsphp.readthedocs.io/en/latest/Creational/Builder/README.html
 *
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navigation_builder implements builder {

    /** @var array $course ID */
    public $course;
    /** @var \stdClass $book Picture item id */
    public $book;
    /** @var \stdClass $cm Picture item id */
    public $cm;
    /** @var \stdClass $chapter Picture item id */
    public $chapter;
    /** @var bool $edit Last name */
    public $edit;

    /**
     * Constructor.
     *
     * @param \stdClass $course Core renderer
     * @param \stdClass $book Vault factory
     * @param \stdClass $cm Vault factory
     * @param \stdClass $chapter Legacy data mapper factory
     * @param bool $edit Rating manager
     */
    public function __construct(\stdClass $course, \stdClass $book, \stdClass $cm, \stdClass $chapter, bool $edit) {
        $this->course = $course;
        $this->book = $book;
        $this->cm = $cm;
        $this->chapter = $chapter;
        $this->edit = $edit;
    }

    /**
     * Build the exported posts for a given set of forums, discussions, and posts.
     *
     * This will typically be used for a list of posts in the same discussion/forum however
     * it does support exporting any arbitrary list of posts as long as the caller also provides
     * a unique list of all discussions for the list of posts and all forums for the list of discussions.
     *
     * Increasing the number of different forums being processed will increase the processing time
     * due to processing multiple contexts (for things like capabilities, files, etc). The code attempts
     * to load the additional resources as efficiently as possible but there is no way around some of
     * the additional overhead.
     *
     * Note: Some posts will be removed as part of the build process according to capabilities.
     * A one-to-one mapping should not be expected.
     *
     * @return \stdClass List of exported posts in the same order as the $posts array.
     */
    public function build() {
        global $DB;

        $context = \context_module::instance($this->cm->id);
        // Read chapters.
        $chapters = book_preload_chapters($this->book);
        $viewhidden = has_capability('mod/book:viewhiddenchapters', $context);

        // Prepare chapter navigation icons.
        $previd = null;
        $prevtitle = null;
        $nextid = null;
        $nexttitle = null;
        $last = null;
        foreach ($chapters as $ch) {
            if (!$this->edit and ($ch->hidden && !$viewhidden)) {
                continue;
            }
            if ($last == $this->chapter->id) {
                $nextid = $this->nextchapterid = $ch->id;
                $nexttitle = book_get_chapter_title($ch->id, $chapters, $this->book, $context);
                break;
            }
            if ($ch->id != $this->chapter->id) {
                $previd = $ch->id;
                $prevtitle = book_get_chapter_title($ch->id, $chapters, $this->book, $context);
            }
            $last = $ch->id;
        }

        $data = new \stdClass();
        $data->isrighttoleft = right_to_left();
        $data->hasprevious = !empty($previd);
        if ($data->hasprevious) {
            $data->prevurl = new \moodle_url('/mod/book/view.php', ['id' => $this->cm->id, 'chapterid' => $previd]);
            $data->prevtitle = $prevtitle;
        }
        $data->hasnext = !empty($nextid);
        if ($data->hasnext) {
            $data->nexturl = new \moodle_url('/mod/book/view.php', ['id' => $this->cm->id, 'chapterid' => $nextid]);
            $data->nexttitle = $nexttitle;
        } else {
            $sec = $DB->get_field('course_sections', 'section', array('id' => $this->cm->section));
            $data->returnurl = course_get_url($this->course, $sec);
        }

        return $data;
    }
}
