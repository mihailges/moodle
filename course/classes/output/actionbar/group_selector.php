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
use stdClass;

/**
 * Renderable class for the group selector element in the action bar.
 *
 * @package    core_course
 * @copyright  2024 Shamim Rezaie <shamim@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group_selector extends comboboxsearch {

    /**
     * @var stdClass The course object.
     */
    protected $course;

    /**
     * The class constructor.
     *
     * @param stdClass $course The course object.
     */
    public function __construct(stdClass $course) {
        $this->course = $course;

        parent::__construct(false, $this->group_selector_output(), $this->searchbody_output(), 'group-search',
            'groupsearchwidget', 'groupsearchdropdown overflow-auto', null, true, $this->get_label(), 'group',
            $this->get_active_group());
    }

    private function group_selector_output() {
        global $OUTPUT;

        $context = \context_course::instance($this->course->id);
        $activegroup = $this->get_active_group();

        $buttondata = [
            'label' => $this->get_label(),
            'group' => $activegroup,
        ];

        if ($activegroup) {
            $group = groups_get_group($activegroup);
            $buttondata['selectedgroup'] = format_string($group->name, true, ['context' => $context]);
        } else if ($activegroup === 0) {
            $buttondata['selectedgroup'] = get_string('allparticipants');
        }

        return $OUTPUT->render_from_template('core_group/comboboxsearch/group_selector', $buttondata);
    }

    private function searchbody_output() {
        global $OUTPUT;

        return $OUTPUT->render_from_template('core_group/comboboxsearch/searchbody', [
            'courseid' => $this->course->id,
            'currentvalue' => optional_param('groupsearchvalue', '', PARAM_NOTAGS),
            'instance' => rand(),
        ]);
    }

    private function get_label() {
        return $this->course->groupmode === VISIBLEGROUPS ? get_string('selectgroupsvisible') :
            get_string('selectgroupsseparate');
    }

    private function get_active_group() {
        global $USER;

        $context = \context_course::instance($this->course->id);
        $userid = $this->course->groupmode == VISIBLEGROUPS || has_capability('moodle/site:accessallgroups', $context) ?
            0 : $USER->id;
        $allowedgroups = groups_get_all_groups($this->course->id, $userid, $this->course->defaultgroupingid);

        return groups_get_course_group($this->course, true, $allowedgroups);
    }
}
