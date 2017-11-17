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
 * Contains an class for validating the state of the folder files.
 *
 * @package   core
 * @copyright 2017 Mihail Geshoski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Mihail Geshoski <mihail@moodle.com>
 */

namespace core\filesstatevalidator\folder;

use core\filesstatevalidator\files_state_validator_base;
use core\filesstatevalidator\files_state_validator_interface;

defined('MOODLE_INTERNAL') || exit();

/**
 * Folder files state validator.
 *
 * This class is intended as a specific validator for the state of the folder module files.
 * 
 *
 * Curl security helpers are currently used by the 'curl' wrapper class in lib/filelib.php.
 *
 * @package   core
 * @copyright 2017 Mihail Geshoski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Mihail Geshoski <mihail@moodle.com>
 */
class folder_files_state_validator extends files_state_validator_base implements files_state_validator_interface {

    protected $folderid;
    protected $revision;

    public function __construct($folderid, $revision) {
        $this->folderid = $folderid;
        $this->revision = $revision;
    }

    /**
     * Check whether the input url should be blocked or not.
     *
     * @return array .
     */
    public function validate_files_state() {
        global $DB, $CFG;

        // Request and permission validation.
        $folder = $DB->get_record('folder', array('id' => $this->folderid), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($folder, 'folder');

        $context = \context_module::instance($cm->id);
        \external_api::validate_context($context);

        require_capability('mod/folder:view', $context);

        return $this->validate_revision($this->revision, $folder->revision);
    }

    public function return_warning_message() {
        return (object)[
                'key' => 'foldercontentchanged',
                'message' => get_string('foldercontentchanged', 'mod_folder')
            ];
    }
}