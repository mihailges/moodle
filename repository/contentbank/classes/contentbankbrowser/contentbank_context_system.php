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
class contentbank_context_system extends contentbank_browser {

    protected $context;

    public function __construct($context) {
        $this->context = $context;
    }

    public function get_parent() {
        return;
    }

    public function get_context_folders() {
        return array_reduce($this->context->get_child_contexts(), function($list, $childcontext) {
            if ($childcontext instanceof \context_coursecat) {
                $name = $childcontext->get_context_name(false);
                $path = base64_encode(json_encode(['contextid' => $childcontext->id]));
                $list[] = $this->create_context_folder_node($name, $path);
            }
            return $list;
        }, []);
    }

    public function has_children_with_contentbank_files($childcontext) {
        global $DB;

        $hascontentbankcontent = !empty($DB->get_record('contentbank_content', ['contextid' => $childcontext->id]));

        if ($hascontentbankcontent) {
            return true;
        }

    }
}
