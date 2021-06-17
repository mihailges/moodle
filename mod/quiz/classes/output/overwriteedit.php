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
 * Output the elements for the action area of edit page in this activity.
 *
 * @package   mod_quiz
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_quiz\output;

use renderable;
use renderer_base;
use templatable;
use moodle_url;

/**
 * Render the tertiary elements for the edit page
 *
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @package mod_quiz\output
 */
class overwriteedit implements templatable, renderable {

    /** @var int */
    private $cmid;

    /**
     * overwriteedit constructor.
     *
     * @param int $cmid
     */
    public function __construct(int $cmid) {
        $this->cmid = $cmid;
    }

    /**
     * Provides the data for the template.
     *
     * @param renderer_base $output
     * @return array data for the template
     */
    public function export_for_template(renderer_base $output)
    {
        $data = [
            'back' => new moodle_url('/mod/quiz/view.php', ['id' => $this->cmid]),
            'backclass' => 'col-xs-6',
            'previewlink' => new moodle_url('/mod/quiz/startattempt.php', ['cmid' => $this->cmid, 'sesskey' => sesskey()]),
            'prevclass' => 'col-sm-6',
        ];
        return $data;
    }

    /**
     * Rendered HTML for the action area in edit page.
     *
     * @return string rendered HTML for the edit page
     */
    public function get_preview_response() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('mod_quiz');
        return $renderer->overwrite_edit_action($this);
    }
}

