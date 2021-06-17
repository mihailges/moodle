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
 * Output the HTML elements for tertiary nav for this activity.
 *
 * @package   mod_quiz
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_question\output;;

use moodle_url;
use renderer_base;
use templatable;
use renderable;
use url_select;

/**
 * Rendered HTML elements for tertiary nav for Question bank.
 *
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @package core_question\output
 */
class qbank_actionbar implements templatable, renderable {
    /** @var int */
    private $cmid;

    /** @var moodle_url */
    private $currenturl;

    /**
     * qbank_actionbar constructor.
     *
     * @param int $cmid
     * @param moodle_url $currenturl
     */
    public function __construct(int $cmid, moodle_url $currenturl) {
        $this->cmid = $cmid;
        $this->currenturl = $currenturl;
    }

    /**
     * Provides the data for the template.
     *
     * @param renderer_base $output
     * @return array data for the template
     */
    public function export_for_template(renderer_base $output)
    {
        $questionslink = new moodle_url('/question/edit.php', ['cmid' => $this->cmid]);
        $categorylink = new moodle_url('/question/category.php', ['cmid' => $this->cmid]);
        $importlink = new moodle_url('/question/import.php', ['cmid' => $this->cmid]);
        $exportlink = new moodle_url('/question/export.php', ['cmid' => $this->cmid]);

        $menu = [
            $questionslink->out(false) => get_string('questions', 'question'),
            $categorylink->out(false) => get_string('categories', 'question'),
            $importlink->out(false) => get_string('import', 'question'),
            $exportlink->out(false) => get_string('export', 'question')
        ];

        $urlselect = new url_select($menu, $this->currenturl, null, 'questionbankaction');

        $data = [
            'questionbankselect' => $urlselect->export_for_template($output),
        ];
        return $data;
    }

    /**
     * Rendered HTML elements for tertiary nav in the Qestion bank.
     *
     * @return string rendered HTML for tertiary nav in the Question bank
     */
    public function get_qbank_action() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('core_question', 'bank');
        return $renderer->qbank_action_menu($this);
    }
}
