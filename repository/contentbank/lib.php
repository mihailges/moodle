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
        $ret = array();
        $ret['dynload'] = true;
        $ret['nosearch'] = true;
        $ret['nologin'] = true;

        if (!empty($encodedpath)) {
            $params = json_decode(base64_decode($encodedpath), true);
            if (is_array($params) && isset($params['contextid'])) {
                $context = context::instance_by_id(clean_param($params['contextid'], PARAM_INT));
            }
        }

        if (empty($context) && !empty($this->context)) {
            $context = $this->context->get_course_context(false);
            // TODO: check for coursecat context.
        }
        if (empty($context)) {
            $context = context_system::instance();
        }

        $manageurl = new moodle_url('/contentbank/index.php', ['contextid' => $context->id]);
        $canuploadcontent = has_capability('moodle/contentbank:upload', $context);
        $ret['manage'] = $canuploadcontent ? $manageurl->out() : '';

        $browser = $this->get_contentbank_browser($context);

        $contextfolders = $browser->get_context_folders();
        $files = $browser->get_contentbank_files();

        $ret['list'] = array_merge($contextfolders, $files);
        $ret['path'] = $browser->get_path_navigation();

        return $ret;
    }

    private function get_contentbank_browser($context) {
         switch ($context->contextlevel) {
            case CONTEXT_SYSTEM:
                return new \repository_contentbank\contentbankbrowser\contentbank_context_system($context);
            case CONTEXT_COURSECAT:
                return new \repository_contentbank\contentbankbrowser\contentbank_context_coursecat($context);
            case CONTEXT_COURSE:
                return new \repository_contentbank\contentbankbrowser\contentbank_context_course($context);
        }
        return;
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
