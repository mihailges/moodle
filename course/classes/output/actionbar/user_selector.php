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

namespace core_course\output\actionbar;

use core\output\comboboxsearch;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Renderable class for the user selector element in the action bar.
 *
 * @package    core_course
 * @copyright  2024 Ilya Tregubov <ilyatregubov@proton.me>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_selector implements renderable, templatable {

    /**
     * @var string $usersearch The content that the current user is looking for.
     */
    protected string $usersearch = '';

    /**
     * @var int $userid The ID of the user that the current user is looking for.
     */
    protected ?int $userid = null;

    /**
     * @var int $groupid Currently selected group.
     */
    protected ?int $groupid = null;

    /**
     * @var moodle_url $resetlink Reset search URL.
     */
    protected moodle_url $resetlink;

    /**
     * @var int $instanceid Module instance id.
     */
    protected ?int $instanceid = null;

    /**
     * @var stdClass The course object.
     */
    protected $course;

    /**
     * The class constructor.
     *
     * @param stdClass $course The course object.
     */
    public function __construct(
        stdClass $course,
        moodle_url $resetlink = null,
        ?int $userid = null,
        ?int $groupid = null,
        $usersearch = '',
        ?int $instanceid = null
    ) {
        $this->course = $course;
        $this->userid = $userid;
        $this->usersearch = $usersearch;
        $this->instanceid = $instanceid;

        $this->groupid = $groupid;
        $this->resetlink  = $resetlink;

        if (isset($this->userid) && $this->userid) {
            $user = \core_user::get_user($this->userid);
            $this->usersearch = fullname($user);
        }
    }

    /**
     * Export the data for the mustache template.
     *
     * @param renderer_base $output The renderer that will be used to render the output.
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $OUTPUT;

        $searchinput = $OUTPUT->render_from_template('core_user/comboboxsearch/user_selector', [
            'currentvalue' => $this->usersearch,
            'courseid' => $this->course->id,
            'instance' => $this->instanceid ?? rand(),
            'resetlink' => $this->resetlink->out(false),
            'group' => $this->groupid ?? 0,
            'name' => 'usersearch',
            'value' => json_encode([
                'userid' => $this->userid,
                'search' => $this->usersearch,
            ]),
        ]);
        $searchdropdown = new comboboxsearch(
            true,
            $searchinput,
            null,
            'user-search d-flex',
            null,
            'usersearchdropdown overflow-auto',
            null,
            false,
        );
        return $searchdropdown->export_for_template($output);
    }

    /**
     * Returns the template for the group selector.
     *
     * @return string
     */
    public function get_template(): string {
        return 'core/comboboxsearch';
    }
}
