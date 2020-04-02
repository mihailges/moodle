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
 * Test for H5P content bank plugin.
 *
 * @package    contenttype_h5p
 * @category   test
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Test for H5P content bank plugin.
 *
 * @package    contenttype_h5p
 * @category   test
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contenttype_h5p_contenttype_plugin_testcase extends advanced_testcase {

    /**
     * Tests can_upload behavior.
     */
    public function test_can_upload() {
        $this->resetAfterTest();

        $systemcontext = \context_system::instance();

        // Admins can upload.
        $this->setAdminUser();
        $this->assertTrue(contenttype_h5p\contenttype::can_upload($systemcontext));

        // Teacher can upload in the course but not at system level.
        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $coursecontext = \context_course::instance($course->id);
        $this->setUser($teacher);
        $this->assertTrue(contenttype_h5p\contenttype::can_upload($coursecontext));
        $this->assertFalse(contenttype_h5p\contenttype::can_upload($systemcontext));

        // Users can't upload.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertFalse(contenttype_h5p\contenttype::can_upload($coursecontext));
        $this->assertFalse(contenttype_h5p\contenttype::can_upload($systemcontext));
    }
}
