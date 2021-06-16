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
 * Output the activityActionbar for this activity.
 *
 * @package   mod_forum
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_forum\output;

use renderable;
use renderer_base;
use templatable;
use moodle_url;
use help_icon;
use mod_forum\local\entities\forum as forum_entity;

/**
 * Render elements search forum, add new discussion button and subscribe all
 * to the page action.
 *
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @package mod_forum\output
 */
class activity_actionbar implements renderable, templatable {
    /**
     * @var forum_entity $forum
     */
    private $forum;

    /**
     * @var \stdClass $course
     */
    private $course;

    /**
     * @var mixed $groupid
     */
    private $groupid;

    /**
     * @var string $search
     */
    private $search;


    public function __construct(forum_entity $forum, \stdClass $course, ?int $groupid, string $search) {
        $this->forum = $forum;
        $this->course = $course;
        $this->groupid = $groupid;
        $this->search = $search;
        $this->actionurl = new moodle_url('/mod/forum/search.php');
        $this->helpicon = new help_icon('search', 'core');
    }

    /**
     * Render the new discussion button.
     *
     * @return string
     */
    private function get_new_discussion_topic_button() {
        global $USER;
        $renderfactory = \mod_forum\local\container::get_renderer_factory();
        $discussionrenderer = $renderfactory->get_blog_discussion_list_renderer($this->forum);
        return $discussionrenderer->render_new_discussion($USER, $this->groupid);
    }

    /**
     * Data for the template.
     *
     * @param renderer_base $output
     * @return array data for the template
     */
    public function export_for_template(renderer_base $output)
    {
        $hiddenfields = [
            (object) ['name' => 'id', 'value' => $this->course->id],
        ];
        $data = [
            'action' => $this->actionurl->out(false),
            'hiddenfields' => $hiddenfields,
            'query' => $this->search,
            'helpicon' => $this->helpicon->export_for_template($output),
            'inputname' => 'search',
            'searchstring' => get_string('searchforums', 'mod_forum'),
            'newdiscussionbtn' => $this->get_new_discussion_topic_button(),
            'forcedsubscription' => new moodle_url('/mod/forum/subscribe.php', ['id' => $this->course->id, 'mode' => 1, 'sesskey' => sesskey()]),
        ];
        return $data;
    }
}
