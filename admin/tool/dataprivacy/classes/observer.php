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
 * Event observers supported by this module.
 *
 * @package    tool_dataprivacy
 * @copyright   2018 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observers supported by this module.
 *
 * @package    tool_dataprivacy
 * @copyright   2018 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_dataprivacy_observer {

    /**
     * Create user data deletion request when the user is deleted.
     *
     * @param \core\event\user_deleted $event
     */
    public static function create_delete_data_request_user_deleted(\core\event\user_deleted $event) {

        $requesttypes = [\tool_dataprivacy\api::DATAREQUEST_TYPE_DELETE];
        $requeststatuses = [\tool_dataprivacy\api::DATAREQUEST_STATUS_DELETED];

        $hasongoingdeleterequests = \tool_dataprivacy\api::has_ongoing_request($event->objectid, $requesttypes[0]);
        $hascompleteddeleterequest = (\tool_dataprivacy\api::get_data_requests_count($event->objectid,
                $requeststatuses, $requesttypes) > 0) ? true : false;

        if (!$hasongoingdeleterequests && !$hascompleteddeleterequest) {
            \tool_dataprivacy\api::create_data_request($event->objectid, $requesttypes[0],
                    get_string('datarequestcreatedupondelete', 'tool_dataprivacy'), true);
        }
    }
}
