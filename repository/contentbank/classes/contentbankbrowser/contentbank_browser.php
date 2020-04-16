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
abstract class contentbank_browser {

    abstract public function get_parent();

    abstract public function get_context_folders();

    protected function create_context_folder_node($name, $path) {
        global $OUTPUT;

        return array(
            'title' => $name,
            'datemodified' => '',
            'datecreated' => '',
            'path' => $path,
            'thumbnail' => $OUTPUT->image_url(file_folder_icon(90))->out(false),
            'children' => array()
        );
    }

    public function get_contentbank_files() {
        global $DB;

        $contents = $DB->get_records('contentbank_content', ['contextid' => $this->context->id]);

        return array_reduce($contents, function($list, $content) {
            $plugin = \core_plugin_manager::instance()->get_plugin_info($content->contenttype);
            if ($plugin && $plugin->is_enabled()) {
                if (class_exists($managerclass = "\\$content->contenttype\\contenttype")) {
                    if (has_capability('moodle/contentbank:access', $this->context)) {

                        $contentmanager = new $managerclass($content);
                        $file = $contentmanager->get_file();
                        if ($file) {
                            $list[] = $this->create_contentbank_file_node($file);
                        }
                    }
                }
            }
            return $list;
        }, []);
    }

    private function create_contentbank_file_node($file) {
        global $OUTPUT;

        $params = array(
            'contextid' => $file->get_contextid(),
            'component' => $file->get_component(),
            'filearea'  => $file->get_filearea(),
            'itemid'    => $file->get_itemid(),
            'filepath'  => $file->get_filepath(),
            'filename'  => $file->get_filename()
        );

        $encodedpath = base64_encode(json_encode($params));

        $node = array(
            'title' => $file->get_filename(),
            'size' => $file->get_filesize(),
            'datemodified' => $file->get_timemodified(),
            'datecreated' => $file->get_timecreated(),
            'author' => $file->get_author(),
            'license' => $file->get_license(),
            'isref' => $file->is_external_file(),
            'source'=> $encodedpath,
            'icon' => $OUTPUT->image_url(file_file_icon($file, 24))->out(false),
            'thumbnail' => $OUTPUT->image_url(file_file_icon($file, 90))->out(false)
        );

        if ($file->get_status() == 666) {
            $node['originalmissing'] = true;
        }

        return $node;
    }

    public function get_path_navigation() {
        $path = array($this->get_node_path());
        $parent = $this->get_parent();
        while ($parent !== null) {
            $parentfolder = $parent->get_node_path();
            array_unshift($path, $parentfolder);
            $parent = $parent->get_parent();
        }
        return $path;
    }

    private function get_node_path() {
        return array(
            'path' => base64_encode(json_encode(['contextid' => $this->context->id])),
            'name' => $this->context->get_context_name(false)
        );
    }
}