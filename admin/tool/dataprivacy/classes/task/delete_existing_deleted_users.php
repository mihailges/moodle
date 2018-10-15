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
 * Scheduled task to create delete data request for pre-existing deleted users.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_dataprivacy\task;

use core\task\scheduled_task;
use tool_dataprivacy\api;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/dataprivacy/lib.php');

/**
 * Scheduled task to create delete data request for pre-existing deleted users.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_existing_deleted_users extends scheduled_task {

    /**
     * Returns the task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('deleteexistingdeleteduserstask', 'tool_dataprivacy');
    }

    /**
     * Run the task to delete expired data request files and update request statuses.
     *
     */
    public function execute() {
        global $DB;

        $statusids = [
            api::DATAREQUEST_STATUS_CANCELLED,
            api::DATAREQUEST_STATUS_REJECTED,
            api::DATAREQUEST_STATUS_EXPIRED
        ];

        list($sql, $params) = $DB->get_in_or_equal($statusids);

        $sql = "SELECT DISTINCT(u.id)
                  FROM {user} u
             LEFT JOIN {tool_dataprivacy_request} r
                       ON u.id = r.userid
                 WHERE u.deleted = ?
                       AND (r.id IS NULL
                           OR r.type != ?
                           OR (r.type = ? AND r.status $sql)
                       )";

        $params = array_merge(
            [
                1,
                api::DATAREQUEST_TYPE_DELETE,
                api::DATAREQUEST_TYPE_DELETE
            ],
            $params
        );

        $deletedusers = $DB->get_records_sql($sql, $params);
        $createdrequests = 0;

        foreach ($deletedusers as $user) {
            $hasongoingdeleterequests = api::has_ongoing_request($user->id,
                    api::DATAREQUEST_TYPE_DELETE);
            $hascompleteddeleterequest = (api::get_data_requests_count($user->id,
                    [api::DATAREQUEST_STATUS_DELETED],
                    [api::DATAREQUEST_TYPE_DELETE]) > 0) ? true : false;

            if (!$hasongoingdeleterequests && !$hascompleteddeleterequest) {
                api::create_data_request($user->id, api::DATAREQUEST_TYPE_DELETE,
                        get_string('datarequestcreatedfromscheduledtask', 'tool_dataprivacy'));
                $createdrequests++;
            }
        }

        if ($createdrequests > 0) {
            mtrace($createdrequests . ' delete data request(s) created for existing deleted users');
        }
    }
}
