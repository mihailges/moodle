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
 * Test for Content bank contenttype class.
 *
 * @package    core_contentbank
 * @category   test
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_contentbank;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/contentbank/tests/fixtures/testable_contenttype.php');

use stdClass;
use contenttype_testable\contenttype as testable;
/**
 * Test for Content bank contenttype class.
 *
 * @package    core_contentbank
 * @category   test
 * @copyright  2020 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_contenttype_contenttype_testcase extends \advanced_testcase {

    /**
     * Test create_content() with empty data.
     */
    public function test_create_empty_content() {
        $this->resetAfterTest();

        // Create empty content.
        $record = new stdClass();

        $content = testable::create_content($record);
        $this->assertEquals(testable::COMPONENT, $content->get_content_type());
        $this->assertInstanceOf('\\contenttype_testable\\contenttype', $content);
    }

    /**
     * Test create_content() from 'contenttype' class.
     */
    public function test_create_content_using_contenttype() {
        $this->resetAfterTest();

        // Create empty content.
        $record = new stdClass();

        // This should throw an exception. create_content() should be called using plugins, no using 'base' class.
        $this->expectExceptionMessage("Cannot call abstract method");
        $content = contenttype::create_content($record);
    }

    /**
     * Tests for behaviour of create_content() and getter functions.
     */
    public function test_create_content() {
        $this->resetAfterTest();

        // Create content.
        $record = new stdClass();
        $record->name = 'Test content';
        $record->contenttype = testable::COMPONENT;
        $record->contextid = \context_system::instance()->id;
        $record->configdata = '';

        $content = testable::create_content($record);
        $this->assertEquals($record->name, $content->get_name());
        $this->assertEquals($record->contenttype, $content->get_content_type());
        $this->assertEquals($record->configdata, $content->get_configdata());
    }

    /**
     * Tests for 'configdata' behaviour.
     */
    public function test_configdata_changes() {
        $this->resetAfterTest();

        $configdata = "{img: 'icon.svg'}";

        // Create content.
        $record = new stdClass();
        $record->configdata = $configdata;

        $content = testable::create_content($record);
        $this->assertEquals($configdata, $content->get_configdata());

        $configdata = "{alt: 'Name'}";
        $content->set_configdata($configdata);
        $this->assertEquals($configdata, $content->get_configdata());
    }

    /**
     * Tests can_upload behavior.
     */
    public function test_can_upload() {
        $this->resetAfterTest();

        $systemcontext = \context_system::instance();

        // Admins can upload.
        $this->setAdminUser();
        $this->assertTrue(contenttype::can_upload($systemcontext));

        // Teacher can upload in the course but not at system level.
        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $coursecontext = \context_course::instance($course->id);
        $this->setUser($teacher);
        $this->assertTrue(contenttype::can_upload($coursecontext));
        $this->assertFalse(contenttype::can_upload($systemcontext));

        // Users can't upload.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertFalse(contenttype::can_upload($coursecontext));
        $this->assertFalse(contenttype::can_upload($systemcontext));
    }

    /**
     * Tests for uploaded file.
     */
    public function test_upload_file() {
        $this->resetAfterTest();

        // Create content.
        $record = new stdClass();
        $record->name = 'Test content';
        $record->contenttype = testable::COMPONENT;
        $record->contextid = \context_system::instance()->id;
        $record->configdata = '';
        $content = testable::create_content($record);

        // Create a dummy file.
        $filename = 'content.h5p';
        $dummy = array(
            'contextid' => \context_system::instance()->id,
            'component' => 'contentbank',
            'filearea' => 'public',
            'itemid' => $content->get_id(),
            'filepath' => '/',
            'filename' => $filename
        );
        $fs = get_file_storage();
        $fs->create_file_from_string($dummy, 'dummy content');

        $file = $content->get_file();
        $this->assertInstanceOf(\stored_file::class, $file);
        $this->assertEquals($filename, $file->get_filename());
    }
}
