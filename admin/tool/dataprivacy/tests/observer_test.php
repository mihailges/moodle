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
 * Tests for the event observer.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer test.
 *
 * @package    tool_dataprivacy
 * @copyright  2018 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_dataprivacy_observer_testcase extends advanced_testcase {

    /**
     * Ensure that a delete data request is created upon user deletion.
     */
    public function test_create_delete_data_request_user_deleted() {
        $this->resetAfterTest();

        $this->setAdminUser();
        // Create another user who is not a DPO.
        $user = $this->getDataGenerator()->create_user();

        $event = $this->trigger_delete_user_event($user);

        \tool_dataprivacy_observer::create_delete_data_request_user_deleted($event);
        // Validate that delete data request has been created.
        $this->assertTrue(\tool_dataprivacy\api::has_ongoing_request($user->id,
                \tool_dataprivacy\api::DATAREQUEST_TYPE_DELETE));
    }

    /**
     * Ensure that a delete data request is not being created upon user deletion
     * if a delete request for that user already exists.
     */
    public function test_create_delete_data_request_user_deleted_request_exists() {
        $this->resetAfterTest();

        $this->setAdminUser();
        // Create another user who is not a DPO.
        $user = $this->getDataGenerator()->create_user();
        // Create a delete data request for $user.
        \tool_dataprivacy\api::create_data_request($user->id,
                \tool_dataprivacy\api::DATAREQUEST_TYPE_DELETE);
        // Validate that delete data request has been created.
        $this->assertTrue(\tool_dataprivacy\api::has_ongoing_request($user->id,
            \tool_dataprivacy\api::DATAREQUEST_TYPE_DELETE));

        $event = $this->trigger_delete_user_event($user);

        \tool_dataprivacy_observer::create_delete_data_request_user_deleted($event);
        // Validate that additional delete data request has not been created.
        $this->assertEquals(1, \tool_dataprivacy\api::get_data_requests_count($user->id, [],
                [\tool_dataprivacy\api::DATAREQUEST_TYPE_DELETE]));
    }

    /**
     * Helper to trigger and capture the delete user event.
     *
     * @param object $user The user object.
     * @return \core\event\user_deleted $event The returned event.
     */
    private function trigger_delete_user_event($user) {

        $sink = $this->redirectEvents();
        delete_user($user);
        $events = $sink->get_events();
        $sink->close();
        $event = reset($events);
        // Validate event data.
        $this->assertInstanceOf('\core\event\user_deleted', $event);

        return $event;
    }
}
