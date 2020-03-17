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
 * This plugin is used to access the content bank files
 *
 * @since Moodle 3.8
 * @package    repository_contentbank
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * repository_contentbank class is used to browse the content bank files
 *
 * @since     Moodle 3.8
 * @package   repository_contentbank
 * @copyright 2020 Mihail Geshoski <mihail@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_contentbank extends repository {

    /**
     * Get file listing.
     *
     * @param string $encodedpath
     * @param string $page
     * @return array
     */
    public function get_listing($encodedpath = '', $page = '') {
        global $DB;

        $ret = array();
        $ret['dynload'] = true;
        $ret['nosearch'] = true;
        $ret['nologin'] = true;
        $manageurl = new moodle_url('/contentbank/index.php');
        $canuploadcontent = has_capability('repository/contentbank:upload', context_system::instance());
        $ret['manage'] = $canuploadcontent ? $manageurl : '';

        $contents = $DB->get_records('contentbank_content');
        $list = $this->generate_contentbank_repository_file_listing($contents);

        $ret['list'] = array_filter($list, array($this, 'filter'));
        return $ret;
    }

    /**
     * Generate the file listing.
     *
     * @param array $contents The repository files array
     * @return array
     */
    private function generate_contentbank_repository_file_listing($contents) {
        global $OUTPUT;

        return array_reduce($contents, function($list, $content) use ($OUTPUT) {
            $plugin = core_plugin_manager::instance()->get_plugin_info($content->contenttype);
            if ($plugin && $plugin->is_enabled()) {
                if (class_exists($managerclass = "\\$content->contenttype\\plugin")) {
                    $contentmanager = new $managerclass($content);

                    $file = $contentmanager->get_file();
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

                    $list[] =  $node;
                }
            }
            return $list;
        }, []);
    }

    /**
     * Does this repository used to browse moodle files?
     *
     * @return boolean
     */
    public function has_moodle_files() {
        return true;
    }

    /**
     * User cannot use the external link to dropbox
     *
     * @return int
     */
    public function supported_returntypes() {
        return FILE_INTERNAL | FILE_REFERENCE;
    }

    /**
     * Is this repository accessing private data?
     *
     * @return bool
     */
    public function contains_private_data() {
        return false;
    }
}
