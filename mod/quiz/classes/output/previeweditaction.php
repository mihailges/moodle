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
 * Output the preview and edit action area for this activity.
 *
 * @package   mod_quiz
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_quiz\output;

use moodle_url;
use renderer_base;
use templatable;
use renderable;

/**
 * Render view action with preview and edit buttons
 *
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @package mod_quiz\output
 */
class previeweditaction implements templatable, renderable {
    /** @var int */
    private $cmid;

    /** @var bool */
    private $canedit;

    /**
     * previeweditaction constructor.
     *
     * @param int $cmid
     */
    public function __construct(int $cmid, bool $canedit) {
        $this->cmid = $cmid;
        $this->canedit = $canedit;
    }

    /**
     * Provide data for the template
     *
     * @param renderer_base $output
     * @return array data for template
     */
    public function export_for_template(renderer_base $output)
    {
        $data = [
            'previewlink' => new moodle_url('/mod/quiz/startattempt.php', ['cmid' => $this->cmid, 'sesskey' => sesskey()]),
            'prevclass' => 'col-xs-6',
        ];
        if ($this->canedit) {
            $data['editlink'] = new moodle_url('/mod/quiz/edit.php', ['cmid' => $this->cmid]);
            $data['editclass'] = 'col-sm-6';
        }
        return $data;
    }

    /**
     * Get the preview and edit quiz buttons rendered for action area.
     *
     * @return string
     */
    public function get_preview_edit_action() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('mod_quiz');
        return $renderer->preview_edit_action($this);
    }
}
