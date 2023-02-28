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

namespace core_grades\external;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Unit tests for the core_grades\external\get_feedback webservice.
 *
 * @package    core_grades
 * @category   external
 * @copyright  2023 Kevin Percy <kevin.percy@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 4.2
 */
class get_feedback_test extends \externallib_advanced_testcase {

    /**
     * Test get_feedback.
     *
     * @return void
     */
    public function test_get_feedback() {
        $this->resetAfterTest(true);
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $gradeitem = $this->getDataGenerator()->create_grade_item(['courseid' => $course->id]);

        $gradegradedata = [
            'itemid' => $gradeitem->id,
            'userid' => $user->id,
            'feedback' => 'Test feedback',
        ];

        $this->getDataGenerator()->create_grade_grade($gradegradedata);
        $this->setAdminUser();

        // Test that correct data is returned for a valid request.
        $feedback = get_feedback::execute($course->id, $user->id, $gradeitem->id);

        $this->assertEquals('Test feedback', $feedback['feedbacktext']);
        $this->assertEquals($gradeitem->itemname, $feedback['title']);
        $this->assertEquals(fullname($user), $feedback['fullname']);

        // Test that empty data is returned if feedback isn't set.
        $userwithoutfeedback = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($userwithoutfeedback->id, $course->id);

        $gradegradedata = [
            'itemid' => $gradeitem->id,
            'userid' => $userwithoutfeedback->id,
        ];

        $this->getDataGenerator()->create_grade_grade($gradegradedata);
        $emptyfeedback = get_feedback::execute($course->id, $userwithoutfeedback->id, $gradeitem->id);

        $this->assertEquals('', $emptyfeedback['feedbacktext']);
        $this->assertEquals($gradeitem->itemname, $emptyfeedback['title']);
        $this->assertEquals(fullname($userwithoutfeedback), $emptyfeedback['fullname']);

        // Test that exception is thrown if the Course ID and Item ID mismatch.
        $invalidcourse = $this->getDataGenerator()->create_course();
        $this->expectException(\invalid_parameter_exception::class);
        $this->expectExceptionMessage('Course ID and item ID mismatch');

        get_feedback::execute($invalidcourse->id, $user->id, $gradeitem->id);

        // Test that exception is thrown if enrolled user doesn't have permission to view feedback.
        $this->setUser($user);
        $this->expectException(\required_capability_exception::class);

        get_feedback::execute($course->id, $user->id, $gradeitem->id);

        // Test that exception is thrown for guest user.
        $this->setGuestUser();
        $this->expectException(\require_login_exception::class);

        get_feedback::execute($course->id, $user->id, $gradeitem->id);
    }
}
