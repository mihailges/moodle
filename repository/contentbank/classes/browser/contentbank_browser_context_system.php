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
 * Utility class for browsing of content bank files in the system context.
 *
 * @package    repository_contentbank
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace repository_contentbank\browser;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents the content bank browser in the system context.
 *
 * @package    repository_contentbank
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contentbank_browser_context_system extends contentbank_browser {

    /**
     * Constructor.
     *
     * @param \context_system $context The current context
     */
    public function __construct(\context_system $context) {
        $this->context = $context;
    }

    /**
     * Get the content bank browser class of the parent context. Currently used to generate the navigation path.
     *
     * @return contentbank_browser|null The content bank browser of the parent context
     */
    protected function get_parent(): ?contentbank_browser {
        return null;
    }

    /**
     * Get the all relevant children contexts.
     *
     * @return array The array containing the relevant children contexts
     */
    protected function get_children_contexts(): array {
        // Get all course categories and return their contexts.
        $coursecategories = \core_course_category::get_all();
        return array_map(function($coursecategory) {
            return \context_coursecat::instance($coursecategory->id);
        }, $coursecategories);
    }

   /**
     * The required condition to enable the user to view/access the content bank content in this context.
     *
     * @return bool Whether the user can view/access the content bank content in the context
     */
    public function can_view_contentbank_content(): bool {
        // The content bank repository should enable managers to share the content created in system context level
        // all over the site. Therefore, by default, the content from the system context level should be available
        // to every authenticated user.
        return has_capability('repository/contentbank:browse', $this->context);
    }
}
