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
 * Factory class for instantiating a singleview report screen.
 *
 * @package   gradereport_singleview
 * @copyright 2023 Mihail Geshoski <mihail@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradereport_singleview\local\screen;

use context_course;

defined('MOODLE_INTERNAL') || die;

/**
 * Factory class for instantiating a singleview report screen.
 *
 * @package   gradereport_singleview
 * @copyright 2023 Mihail Geshoski <mihail@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class screen_factory {

    /**
     * Returns the appropriate singleview screen object.
     *
     * @param int $courseid The course ID.
     * @param string|null $screentype The screen type.
     * @param int|null $itemid The item id (user ID or grade item ID).
     * @param int|null $groupid The group ID
     * @return screen The singleview screen object.
     * @throws moodle_exception When the defined screen type is not valid.
     */
    public static function build(int $courseid, ?string $screentype = null, ?int $itemid = null, ?int $groupid = null): screen {
        global $SESSION;

        $context = context_course::instance($courseid);
        // If the screen type is not explicitly defined, try to obtain it through the session variable or default to the
        // user select zero state.
        if (!$screentype) {
            $screentype = isset($SESSION->gradereport_singleview["type-{$context->id}"]) ?
                $SESSION->gradereport_singleview["type-{$context->id}"] : 'user_select';
        }
        // Make sure that the defined screen type is valid.
        if (!in_array($screentype, \gradereport_singleview\report\singleview::valid_screens())) {
            throw new \moodle_exception('notvalid', 'gradereport_singleview', '', $screentype);
        }

        $lastvieweduseritemid = $SESSION->gradereport_singleview["useritem-{$context->id}"] ?? null;
        $lastviewedgradeitemid = $SESSION->gradereport_singleview["gradeitem-{$context->id}"] ?? null;

        switch ($screentype) {
            case 'user_select':
                // If there is a stored user item (last viewed) in a session variable, bypass the user select zero state
                // and display this user item. Also, make sure that the stored last viewed user is part of the current
                // list of gradable users in this course.
                if ($lastvieweduseritemid && array_key_exists($lastvieweduseritemid, get_gradable_users($courseid, $groupid))) {
                    return new user($courseid, $lastvieweduseritemid, $groupid);
                }
                return new user_select($courseid, null, $groupid);

            case 'user':
                $itemid = $itemid ?? $lastvieweduseritemid;
                // If the item id (user id) is not defined or the user id is not part of the list of gradable users,
                // display the user select zero state.
                if (is_null($itemid) || !array_key_exists($itemid, get_gradable_users($courseid, $groupid))) {
                    return new user_select($courseid, null, $groupid);
                }
                return new user($courseid, $itemid, $groupid);

            case 'grade_select':
                // If there is a stored grade item (last viewed) in a session variable, bypass the grade item select
                // zero state and display this grade item.
                if ($lastviewedgradeitemid) {
                    return new grade($courseid, $lastviewedgradeitemid, $groupid);
                }
                return new grade_select($courseid, null, $groupid);

            case 'grade':
                $itemid = $itemid ?? $lastviewedgradeitemid;
                // If the item id (grade item id) is not defined, display the grade item select zero state.
                if (is_null($itemid)) {
                    return new grade_select($courseid, null, $groupid);
                }
                return new grade($courseid, $itemid, $groupid);
        }
    }
}
