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
 * Class containing data for the bulk delete data requests.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_dataprivacy\output;
defined('MOODLE_INTERNAL') || die();

use coding_exception;
use moodle_exception;
use moodle_url;
use renderable;
use renderer_base;
use single_select;
use stdClass;
use templatable;
use tool_dataprivacy\data_request;
use tool_dataprivacy\local\helper;

/**
 * Class containing data for the bulk delete data requests.
 *
 * @copyright  2018 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulk_delete_data_request_page implements renderable, templatable {


    protected $confirm;

    protected $notifications;


    /**
     * Construct this renderable.
     *
     */
    public function __construct($confirm) {
        global $DB,

        if ($confirm) {
            $notifications = '';
            list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
            $rs = $DB->get_recordset_select('user', "id $in", $params);
            foreach ($rs as $user) {
                if ($this->condition($user)) {
                    unset($SESSION->bulk_users[$user->id]);
                } else {

                    $notifications .= $OUTPUT->notification(get_string('deletednot', '',
                        fullname($user, true)));
                }
            }
            $rs->close();
            \core\session\manager::gc(); // Remove stale sessions.
            echo $OUTPUT->box_start('generalbox', 'notice');
            if (!empty($notifications)) {
                echo $notifications;
            } else {
                echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
            }
            $continue = new single_button(new moodle_url($return), get_string('continue'), 'post');
            echo $OUTPUT->render($continue);
            echo $OUTPUT->box_end();
        }


    }

    public function condition($user) {
        global $USER;

        return (!is_siteadmin($user) and $USER->id != $user->id and
            \tool_dataprivacy\api::create_data_request($user->id, \tool_dataprivacy\api::DATAREQUEST_TYPE_DELETE));
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();


        return $data;
    }
}
