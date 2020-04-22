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
 * Utility class for browsing of content bank files in the course category context.
 *
 * @package    repository_contentbank
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace repository_contentbank\browser;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents the content bank browser in the course category context.
 *
 * @package    repository_contentbank
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contentbank_browser_context_coursecat extends contentbank_browser {

    /**
     * Constructor.
     *
     * @param \context_coursecat $context The current context
     */
    public function __construct(\context_coursecat $context) {
        $this->context = $context;
    }

    /**
     * Get the content bank browser class of the parent context. Currently used to generate the navigation path.
     *
     * @return contentbank_browser|null The content bank browser of the parent context
     */
    public function get_parent(): ?contentbank_browser {
        $parentcontext = $this->context->get_parent_context();
        if ($parentcontext instanceof \context_system) {
            return new contentbank_browser_context_system($parentcontext);
        }
        return null;
    }

    /**
     * Get the all relevant children contexts.
     *
     * @return array The array containing the relevant children contexts
     */
    protected function get_children_contexts(): array {
        // Get all course category related courses and return their contexts.
        $courses = \core_course_category::get($this->context->instanceid)->get_courses();
        return array_map(function($course) {
            return \context_course::instance($course->id);
        }, $courses);
    }

    /**
     * The required condition to enable the user to view/access the content bank content in this context.
     *
     * @return bool Whether the user can view/access the content bank content in the context
     */
    public function can_view_contentbank_content(): bool {
        // The content bank repository should enable managers to share the content created in course category context
        // level all over the course category. Therefore, the content from the course category context level should
        // be available to every authenticated user.
        return has_capability('repository/contentbank:browse', $this->context);
    }
}
