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
 * Defines the renderer for the book print tool.
 *
 * @package    lessontool_print
 * @copyright  2019 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lessontool_print\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use html_writer;
use context_module;
use moodle_url;

/**
 * The renderer for the lesson print tool.
 *
 * @copyright  2019 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the print lesson page.
     *
     * @param print_lesson_page $page
     * @return string html for the page
     * @throws moodle_exception
     */
    public function render_print_lesson_page(print_lesson_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('lessontool_print/print_lesson', $data);
    }

    public function render_print_branchtable_page($page) {
        $pagecontent = "<h4>" . $page->title . "</h4>";
        $pagecontent .= $page->get_contents();
        $pagetype = 'content';

        return array($pagecontent, $pagetype);
    }

    public function render_print_matching_page($page) {
        $pagecontent = "<h4>" . $page->title . "</h4>";
        $pagecontent .= $page->get_contents();
        $pagetype = 'content';

        return array($pagecontent, $pagetype);
    }

    public function render_print_truefalse_page($page) {
        $pagecontent = "<h4>" . $page->title . "</h4>";
        $pagecontent .= $page->get_contents();
        $pagetype = 'content';

        return array($pagecontent, $pagetype);
    }

    public function render_print_multichoice_page($page) {
        $pagecontent = "<h4>" . $page->title . "</h4>";
        $pagecontent .= $page->get_contents();
        $pagetype = 'multichoice';

        return array($pagecontent, $pagetype);
    }

    function print_lesson_page_output($page) {
        switch ($page->get_typestring()) {
            case 'Content':
                render_print_content_page($page);
                break;
        }
    }
}
