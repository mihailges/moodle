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
 * Utility class for browsing of content bank files.
 *
 * @package    core_files
 * @copyright  2008 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace repository_contentbank\contentbankbrowser;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents the system context in the tree navigated by {@link file_browser}.
 *
 * @package    repository_contentbank
 * @copyright  2008 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contentbank_context_course extends contentbank_browser {

    protected $context;

    public function __construct($context) {
        $this->context = $context;
    }

    public function get_parent() {
        return new contentbank_context_coursecat($this->context->get_parent_context());
    }

    public function get_context_folders() {
        return [];
    }
}
