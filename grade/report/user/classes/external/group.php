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

namespace gradereport_user\external;

use coding_exception;
use context_course;
use core_user;
use external_api;
use external_description;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use invalid_parameter_exception;
use moodle_exception;
use restricted_context_exception;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/externallib.php');

/**
 * External group report API implementation
 *
 * @package    gradereport_user
 * @copyright  2022 Mathew May <mathew.solutions>
 * @category   external
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group extends external_api {
    /**
     * Describes the parameters for get_users_for_course.
     *
     * @return external_function_parameters
     */
    public static function get_groups_for_search_widget_parameters(): external_function_parameters {
        return new external_function_parameters (
            [
                'courseid' => new external_value(PARAM_INT, 'Course Id', VALUE_REQUIRED)
            ]
        );
    }

    /**
     * Given a course ID find the enrolled users within and map some fields to the returned array of user objects.
     *
     * @param int $courseid
     * @return array Users and warnings to pass back to the calling widget.
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    protected static function get_groups_for_search_widget(int $courseid): array {
        global $DB, $PAGE, $USER, $COURSE;

        $params = self::validate_parameters(
            self::get_groups_for_search_widget_parameters(),
            [
                'courseid' => $courseid
            ]
        );

        $warnings = [];
        $context = context_course::instance($params['courseid']);
        parent::validate_context($context);

        $course = $DB->get_record('course', ['id' => $params['courseid']]);
        // Initialise the grade tracking object.
        if ($groupmode = $course->groupmode) {
            $aag = has_capability('moodle/site:accessallgroups', $context);

            $usergroups = array();
            if ($groupmode == VISIBLEGROUPS or $aag) {
                $allowedgroups = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
                // Get user's own groups and put to the top.
                $usergroups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
            } else {
                $allowedgroups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
            }

            $groupsmenu = array_merge($allowedgroups, $usergroups);
            if (!$allowedgroups or $groupmode == VISIBLEGROUPS or $aag) {
                array_unshift($groupsmenu, (object) [
                    'id' => 0,
                    'name' => get_string('allparticipants'),
                ]);
            }

            $mappedgroups = array_map(function($group) use ($COURSE) {
                $url = new \moodle_url('/grade/report/user/index.php', [
                    'id' => $COURSE->id,
                    'group' => $group->id
                ]);
                return (object) [
                    'name' => $group->name,
                    'url' => $url->out(false),
                ];
            }, $groupsmenu);
        }

        return [
            'groups' => $mappedgroups,
            'warnings' => $warnings,
        ];
    }

    /**
     * Returns description of what the group search for the widget should return.
     *
     * @return external_single_structure
     */
    public static function get_groups_for_search_widget_returns(): external_single_structure {
        return new external_single_structure([
            'groups' => new external_multiple_structure(self::group_description()),
            'warnings' => new external_warnings(),
        ]);
    }

    /**
     * Create group return value description.
     *
     * @return external_description
     */
    public static function group_description(): external_description {
        $groupfields = [
            'url' => new external_value(PARAM_URL, 'The link to reloop upon the zero state', VALUE_REQUIRED),
            'name' => new external_value(PARAM_TEXT, 'The full name of the group', VALUE_REQUIRED),
        ];
        return new external_single_structure($groupfields);
    }
}
