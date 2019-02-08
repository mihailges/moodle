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
 * @copyright  2019 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_book\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use html_writer;
use context_module;
use moodle_url;
use moodle_exception;

/**
 * The renderer for the book module.
 *
 * @copyright  2019 Mihail Geshoski
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
     * @return string $chnavigation Book chapter navigation links.
     */
    public function navigation($course, $book, $cm, $chapter, $edit) {
        global $DB;

        $context = context_module::instance($cm->id);
        // Read chapters.
        $chapters = book_preload_chapters($book);

        // Prepare chapter navigation icons.
        $previd = null;
        $prevtitle = null;
        $navprevtitle = null;
        $nextid = null;
        $nexttitle = null;
        $navnexttitle = null;
        $last = null;
        foreach ($chapters as $ch) {
            if (!$edit and $ch->hidden) {
                continue;
            }
            if ($last == $chapter->id) {
                $nextid = $this->nextchapterid = $ch->id;
                $nexttitle = book_get_chapter_title($ch->id, $chapters, $book, $context);
                $navnexttitle = get_string('navnexttitle', 'mod_book', $nexttitle);
                break;
            }
            if ($ch->id != $chapter->id) {
                $previd = $ch->id;
                $prevtitle = book_get_chapter_title($ch->id, $chapters, $book, $context);
                $navprevtitle = get_string('navprevtitle', 'mod_book', $prevtitle);
            }
            $last = $ch->id;
        }

        $chnavigation = '';

        if ($book->navstyle) {
            $navprevicon = right_to_left() ? 'nav_next' : 'nav_prev';
            $navnexticon = right_to_left() ? 'nav_prev' : 'nav_next';
            $navprevdisicon = right_to_left() ? 'nav_next_dis' : 'nav_prev_dis';

            if ($previd) {
                $navprev = get_string('navprev', 'book');
                if ($book->navstyle == 1) {
                     $linkcontent = $this->output->pix_icon($navprevicon, $navprevtitle, 'mod_book');
                     $chnavigation .= html_writer::link(
                            new moodle_url('/mod/book/view.php', array('id' => $cm->id, 'chapterid' => $previd)),
                            $linkcontent, ['title' => $navprevtitle, 'class' => 'bookprev']);
                } else {
                    $linkcontent = html_writer::span($this->output->larrow(), 'arrow') .
                            html_writer::span($navprev . ":") . html_writer::span($prevtitle, 'chaptername');
                    $chnavigation .= html_writer::link(
                            new moodle_url('/mod/book/view.php', array('id' => $cm->id, 'chapterid' => $previd)),
                            $linkcontent, ['title' => $navprev, 'class' => 'bookprev']);
                }
            } else {
                if ($book->navstyle == 1) {
                    $chnavigation .= $this->output->pix_icon($navprevdisicon, '', 'mod_book');
                }
            }
            if ($nextid) {
                $navnext = get_string('navnext', 'book');
                if ($book->navstyle == 1) {
                    $chnavigation .= html_writer::link(
                            new moodle_url('/mod/book/view.php', array('id' => $cm->id, 'chapterid' => $nextid)),
                            $this->output->pix_icon($navnexticon, $navnexttitle, 'mod_book'),
                            ['title' => $navnexttitle, 'class' => 'booknext']);
                } else {
                    $linkcontent = html_writer::span($this->output->rarrow(), 'arrow') .
                            html_writer::span($navnext . ":") . html_writer::span($nexttitle, 'chaptername');
                    $chnavigation .= html_writer::link(
                            new moodle_url('/mod/book/view.php', array('id' => $cm->id, 'chapterid' => $nextid)),
                            $linkcontent, ['title' => $navnext, 'class' => 'booknext']);
                }
            } else {
                $navexit = get_string('navexit', 'book');
                $sec = $DB->get_field('course_sections', 'section', array('id' => $cm->section));
                $returnurl = course_get_url($course, $sec);
                if ($book->navstyle == 1) {
                    $chnavigation .= html_writer::link(new moodle_url($returnurl),
                        $this->output->pix_icon('nav_exit', $navexit, 'mod_book'),
                        ['title' => $navexit, 'class' => 'booknext']);

                } else {
                    $linkcontent = html_writer::span($navexit, 'chaptername') .
                        html_writer::span($this->output->uarrow(), 'arrow');
                    $chnavigation .= html_writer::link(new moodle_url($returnurl), $linkcontent,
                            ['title' => $navexit, 'class' => 'booknext']);
                }
            }
        }
        return $chnavigation;
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
     * Render the table representing the book instances of a particular course.
     *
     * @param object $course
     * @return string html for the page
     */
    public function render_book_instances_in_course_table($course) {
        global $CFG;

        // Get all the appropriate data.
        if (!$books = get_all_instances_in_course('book', $course)) {
            $strbooks = get_string('modulenameplural', 'mod_book');
            return notice(get_string('thereareno', 'moodle', $strbooks),
                    "$CFG->wwwroot/course/view.php?id=$course->id");
        }

        // Get all required strings.
        $strname         = get_string('name');
        $strintro        = get_string('moduleintro');
        $strlastmodified = get_string('lastmodified');

        $table = new \html_table();
        $table->attributes['class'] = 'generaltable mod_index';

        if ($usesections = course_format_uses_sections($course->format)) {
            $strsectionname = get_string('sectionname', 'format_' . $course->format);
            $table->head  = array ($strsectionname, $strname, $strintro);
            $table->align = array ('center', 'left', 'left');
        } else {
            $table->head  = array ($strlastmodified, $strname, $strintro);
            $table->align = array ('left', 'left', 'left');
        }

        $modinfo = get_fast_modinfo($course);
        $currentsection = '';
        foreach ($books as $book) {
            $cm = $modinfo->get_cm($book->coursemodule);
            if ($usesections) {
                $printsection = '';
                if ($book->section !== $currentsection) {
                    if ($book->section) {
                        $printsection = get_section_name($course, $book->section);
                    }
                    if ($currentsection !== '') {
                        $table->data[] = 'hr';
                    }
                    $currentsection = $book->section;
                }
            } else {
                $printsection = html_writer::tag('span', userdate($book->timemodified), array('class' => 'smallinfo'));
            }

            $class = $book->visible ? null : array('class' => 'dimmed'); // Hidden modules are dimmed.

            $table->data[] = array (
                $printsection,
                html_writer::link(new moodle_url('view.php', array('id' => $cm->id)), format_string($book->name), $class),
                format_module_intro('book', $book, $cm->id));
        }

        return html_writer::table($table);
    }

    /**
     * Render toc structure.
     *
     * @param array $chapters
     * @param stdClass $chapter
     * @param stdClass $book
     * @param stdClass $cm
     * @param bool $edit
     * @return string
     */
    public function render_book_toc($chapters, $chapter, $book, $cm, $edit) {
        global $USER, $OUTPUT;

        $toc = '';
        $nch = 0;   // Chapter number.
        $ns = 0;    // Subchapter number.
        $first = 1;

        $context = context_module::instance($cm->id);
        $viewhidden = has_capability('mod/book:viewhiddenchapters', $context);

        switch ($book->numbering) {
            case BOOK_NUM_NONE:
                $toc .= html_writer::start_tag('div', array('class' => 'book_toc_none clearfix'));
                break;
            case BOOK_NUM_NUMBERS:
                $toc .= html_writer::start_tag('div', array('class' => 'book_toc_numbered clearfix'));
                break;
            case BOOK_NUM_BULLETS:
                $toc .= html_writer::start_tag('div', array('class' => 'book_toc_bullets clearfix'));
                break;
            case BOOK_NUM_INDENTED:
                $toc .= html_writer::start_tag('div', array('class' => 'book_toc_indented clearfix'));
                break;
        }

        if ($edit) { // Editing on (Teacher's TOC).
            $toc .= html_writer::start_tag('ul');
            $i = 0;
            foreach ($chapters as $ch) {
                $i++;
                $title = trim(format_string($ch->title, true, array('context' => $context)));
                $titleunescaped = trim(format_string($ch->title, true, array('context' => $context,
                        'escape' => false)));
                $titleout = $title;

                if (!$ch->subchapter) {

                    if ($first) {
                        $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                    } else {
                        $toc .= html_writer::end_tag('ul');
                        $toc .= html_writer::end_tag('li');
                        $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                    }

                    if (!$ch->hidden) {
                        $nch++;
                        $ns = 0;
                        if ($book->numbering == BOOK_NUM_NUMBERS) {
                            $title = "$nch. $title";
                            $titleout = $title;
                        }
                    } else {
                        if ($book->numbering == BOOK_NUM_NUMBERS) {
                            $title = "x. $title";
                        }
                        $titleout = html_writer::tag('span', $title, array('class' => 'dimmed_text'));
                    }
                } else {

                    if ($first) {
                        $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                        $toc .= html_writer::start_tag('ul');
                        $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                    } else {
                        $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                    }

                    if (!$ch->hidden) {
                        $ns++;
                        if ($book->numbering == BOOK_NUM_NUMBERS) {
                            $title = "$nch.$ns. $title";
                            $titleout = $title;
                        }
                    } else {
                        if ($book->numbering == BOOK_NUM_NUMBERS) {
                            if (empty($chapters[$ch->parent]->hidden)) {
                                $title = "$nch.x. $title";
                            } else {
                                $title = "x.x. $title";
                            }
                        }
                        $titleout = html_writer::tag('span', $title, array('class' => 'dimmed_text'));
                    }
                }

                if ($ch->id == $chapter->id) {
                    $toc .= html_writer::tag('strong', $titleout);
                } else {
                    $toc .= html_writer::link(new moodle_url('view.php',
                            array('id' => $cm->id, 'chapterid' => $ch->id)), $titleout, array('title' => $titleunescaped));
                }

                $toc .= html_writer::start_tag('div', array('class' => 'action-list'));
                if ($i != 1) {
                    $toc .= html_writer::link(new moodle_url('move.php',
                        array('id' => $cm->id, 'chapterid' => $ch->id, 'up' => '1', 'sesskey' => $USER->sesskey)),
                        $OUTPUT->pix_icon('t/up', get_string('movechapterup', 'mod_book', $title)),
                        array('title' => get_string('movechapterup', 'mod_book', $titleunescaped)));
                }
                if ($i != count($chapters)) {
                    $toc .= html_writer::link(new moodle_url('move.php',
                            array('id' => $cm->id, 'chapterid' => $ch->id, 'up' => '0', 'sesskey' => $USER->sesskey)),
                            $OUTPUT->pix_icon('t/down', get_string('movechapterdown', 'mod_book', $title)),
                            array('title' => get_string('movechapterdown', 'mod_book', $titleunescaped)));
                }
                $toc .= html_writer::link(new moodle_url('edit.php', array('cmid' => $cm->id, 'id' => $ch->id)),
                        $OUTPUT->pix_icon('t/edit', get_string('editchapter', 'mod_book', $title)),
                        array('title' => get_string('editchapter', 'mod_book', $titleunescaped)));

                $deleteaction = new \confirm_action(get_string('deletechapter', 'mod_book',
                        $titleunescaped));
                $toc .= $OUTPUT->action_icon(
                    new moodle_url('delete.php', [
                        'id'        => $cm->id,
                        'chapterid' => $ch->id,
                        'sesskey'   => sesskey(),
                        'confirm'   => 1,
                    ]),
                    new \pix_icon('t/delete', get_string('deletechapter', 'mod_book', $title)),
                    $deleteaction, ['title' => get_string('deletechapter', 'mod_book', $titleunescaped)]
                );

                if ($ch->hidden) {
                    $toc .= html_writer::link(new moodle_url('show.php',
                            array('id' => $cm->id, 'chapterid' => $ch->id, 'sesskey' => $USER->sesskey)),
                            $OUTPUT->pix_icon('t/show', get_string('showchapter', 'mod_book', $title)),
                            array('title' => get_string('showchapter', 'mod_book', $titleunescaped)));
                } else {
                    $toc .= html_writer::link(new moodle_url('show.php',
                            array('id' => $cm->id, 'chapterid' => $ch->id, 'sesskey' => $USER->sesskey)),
                            $OUTPUT->pix_icon('t/hide', get_string('hidechapter', 'mod_book', $title)),
                            array('title' => get_string('hidechapter', 'mod_book', $titleunescaped)));
                }
                $toc .= html_writer::link(new moodle_url('edit.php',
                        array('cmid' => $cm->id, 'pagenum' => $ch->pagenum, 'subchapter' => $ch->subchapter)),
                        $OUTPUT->pix_icon('add', get_string('addafter', 'mod_book'), 'mod_book'),
                        array('title' => get_string('addafter', 'mod_book')));
                $toc .= html_writer::end_tag('div');

                if (!$ch->subchapter) {
                    $toc .= html_writer::start_tag('ul');
                } else {
                    $toc .= html_writer::end_tag('li');
                }
                $first = 0;
            }

            $toc .= html_writer::end_tag('ul');
            $toc .= html_writer::end_tag('li');
            $toc .= html_writer::end_tag('ul');

        } else { // Editing off. Normal students, teachers view.
            $toc .= html_writer::start_tag('ul');
            foreach ($chapters as $ch) {
                $title = trim(format_string($ch->title, true, array('context' => $context)));
                $titleunescaped = trim(format_string($ch->title, true,
                        array('context' => $context, 'escape' => false)));
                if (!$ch->hidden || ($ch->hidden && $viewhidden)) {
                    if (!$ch->subchapter) {
                        $nch++;
                        $ns = 0;

                        if ($first) {
                            $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                        } else {
                            $toc .= html_writer::end_tag('ul');
                            $toc .= html_writer::end_tag('li');
                            $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                        }

                        if ($book->numbering == BOOK_NUM_NUMBERS) {
                            $title = "$nch. $title";
                        }
                    } else {
                        $ns++;

                        if ($first) {
                            $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                            $toc .= html_writer::start_tag('ul');
                            $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                        } else {
                            $toc .= html_writer::start_tag('li', array('class' => 'clearfix'));
                        }

                        if ($book->numbering == BOOK_NUM_NUMBERS) {
                            $title = "$nch.$ns. $title";
                        }
                    }

                    $cssclass = ($ch->hidden && $viewhidden) ? 'dimmed_text' : '';

                    if ($ch->id == $chapter->id) {
                        $toc .= html_writer::tag('strong', $title, array('class' => $cssclass));
                    } else {
                        $toc .= html_writer::link(new moodle_url('view.php',
                            array('id' => $cm->id, 'chapterid' => $ch->id)),
                            $title, array('title' => s($titleunescaped), 'class' => $cssclass));
                    }

                    if (!$ch->subchapter) {
                        $toc .= html_writer::start_tag('ul');
                    } else {
                        $toc .= html_writer::end_tag('li');
                    }

                    $first = 0;
                }
            }

            $toc .= html_writer::end_tag('ul');
            $toc .= html_writer::end_tag('li');
            $toc .= html_writer::end_tag('ul');

        }

        $toc .= html_writer::end_tag('div');

        $toc = str_replace('<ul></ul>', '', $toc); // Cleanup of invalid structures.

        return $toc;
    }
}
