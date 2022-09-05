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

namespace gradereport_user41\external;

use coding_exception;
use context_course;
use core_course_external;
use core_user;
use external_description;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;
use invalid_parameter_exception;
use moodle_exception;
use moodle_url;
use restricted_context_exception;
use user_picture;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/externallib.php');

/**
 * External grade report user41 API
 *
 * @package    gradereport_user41
 * @copyright  2022 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user extends core_course_external {
    /**
     * Describes the parameters for get_users_for_course.
     *
     * @return external_function_parameters
     */
    public static function get_users_for_search_widget_parameters(): external_function_parameters {
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
    protected static function get_users_for_search_widget(int $courseid): array {
        global $PAGE, $USER;

        $params = self::validate_parameters(
            self::get_users_for_search_widget_parameters(),
            [
                'courseid' => $courseid,
            ]
        );

        $warnings = [];
        $coursecontext = context_course::instance($params['courseid']);
        parent::validate_context($coursecontext);

        $enrolledusers = get_enrolled_users($coursecontext);

        $users = array_map(function ($user) use ($PAGE, $USER, $params) {
            $user->fullname = fullname($user);
            $url = new moodle_url('/grade/report/user41/index.php', ['id' => $params['courseid'], 'userid' => $user->id]);
            $user->url = $url->out(false);
            $userpicture = new user_picture($user);
            $userpicture->size = 1;
            $user->profileimage = $userpicture->get_url($PAGE)->out(false);
            $sendmessage = new moodle_url('/message/index.php', ['id' => $user->id]);
            $user->sendmessage = $sendmessage->out(false);
            $addcontact = new moodle_url('/message/index.php', [
                'user1' => $USER->id,
                'user2' => $user->id,
                'addcontact' => $user->id,
                'sesskey' => sesskey()
            ]);
            $user->addcontact = $addcontact->out(false);
            $user->currentuser = $USER->id;
            return $user;
        }, $enrolledusers);
        sort($users);

        return [
            'users' => $users,
            'warnings' => $warnings,
        ];
    }

    /**
     * Returns description of what the user search for the widget should return.
     *
     * @return external_single_structure
     */
    public static function get_users_for_search_widget_returns(): external_single_structure {
        return new external_single_structure([
            'users' => new external_multiple_structure(self::user_description()),
            'warnings' => new external_warnings(),
        ]);
    }

    /**
     * Create user return value description.
     *
     * @return external_description
     */
    public static function user_description(): external_description {
        $userfields = array(
            'id'    => new external_value(core_user::get_property_type('id'), 'ID of the user'),
            'currentuser'    => new external_value(core_user::get_property_type('id'), 'ID of the current user'),
            'profileimage' => new external_value(
                PARAM_URL,
                'The location of the users larger image',
                VALUE_OPTIONAL
            ),
            'url' => new external_value(
                PARAM_URL,
                'The link to the user report',
                VALUE_OPTIONAL
            ),
            'sendmessage' => new external_value(
                PARAM_URL,
                'The link to send the user a message',
                VALUE_OPTIONAL
            ),
            'addcontact' => new external_value(
                PARAM_URL,
                'The link that allows the current user to add the respective user for messaging',
                VALUE_OPTIONAL
            ),
            'fullname' => new external_value(PARAM_TEXT, 'The full name of the user', VALUE_OPTIONAL),
            'firstname'   => new external_value(
                core_user::get_property_type('firstname'),
                'The first name(s) of the user',
                VALUE_OPTIONAL),
            'lastname'    => new external_value(
                core_user::get_property_type('lastname'),
                'The family name of the user',
                VALUE_OPTIONAL),
        );
        return new external_single_structure($userfields);
    }
}
