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
 * @package    repository_contentbank
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace repository_contentbank\browser;

defined('MOODLE_INTERNAL') || die();

/**
 * Base class for the content bank browsers.
 *
 * @package    repository_contentbank
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class contentbank_browser {

    /** @var \context The current context. */
    protected $context;

    /**
     * Get the content bank browser class of the parent context. Currently used to generate the navigation path.
     *
     * @return contentbank_browser|null The content bank browser of the parent context
     */
    abstract protected function get_parent(): ?self;

    /**
     * Get the all relevant children contexts.
     *
     * @return array The array containing the relevant children contexts
     */
    abstract protected function get_children_contexts(): array;

    /**
     * The required condition to enable the user to view/access the content bank content in this context.
     *
     * @return bool Whether the user can view/access the content bank content in the context
     */
    abstract public function can_view_contentbank_content(): bool;

    /**
     * Get all content nodes in the current context which can be viewed/accessed by the user.
     *
     * @return array The array containing all nodes which can be viewed/accessed by the user in the current context
     */
    public function get_content() {
        return array_merge($this->get_context_folders(), $this->get_contentbank_files());
    }

    /**
     * Generate folder nodes for the relevant child contexts.
     *
     * @return array The array containing the context folder nodes
     */
    protected function get_context_folders(): array {
        // Return only course contexts which can be accessed by the user and have content bank files within the context.
        $children = $this->get_children_contexts();
        return array_reduce($children, function ($list, $child) {
            if ($this->has_accessible_content_in_context_tree($child)) {
                $name = $child->get_context_name(false);
                $path = base64_encode(json_encode(['contextid' => $child->id]));
                $list[] = \repository_contentbank\helper::create_context_folder_node($name, $path);
            }
            return $list;
        }, []);
    }

    /**
     * Generate file nodes for the content bank files in the current context which can be accessed/viewed by the user.
     *
     * @return array The array containing the content bank file nodes
     */
    protected function get_contentbank_files(): array {
        $cb = new \core_contentbank\contentbank();
        // Return all content bank files in the current context.
        $contents = $cb->search_contents(null, $this->context->id);
        return array_reduce($contents, function($list, $content) {
            if ($this->can_view_contentbank_content() &&
                    $file = $content->get_file()) {
                $list[] = \repository_contentbank\helper::create_contentbank_file_node($file);
            }
            return $list;
        }, []);
    }

    /**
     * Generate the full navigation to the current node.
     *
     * @return array The array containing the path to each node in the navigation
     */
    public function get_navigation(): array {
        // Get the current navigation node.
        $currentnavigationnode = \repository_contentbank\helper::create_navigation_node($this->context);
        $navigationnodes = array($currentnavigationnode);
        // Get the parent content bank browser.
        $parent = $this->get_parent();
        // Prepend parent navigation node in the navigation nodes array until there is an existing parent.
        while ($parent !== null) {
            $parentnavigationnode = \repository_contentbank\helper::create_navigation_node($parent->context);
            array_unshift($navigationnodes, $parentnavigationnode);
            $parent = $parent->get_parent();
        }
        return $navigationnodes;
    }

    /**
     * Determine whether the user has an access to an existing content in the context three starting from a given context.
     *
     * @param \context $context The context
     * @return bool Whether the user has an access to an existing content in the context three
     */
    protected function has_accessible_content_in_context_tree(\context $context): bool {
        // Return TRUE if there is an existing content in the given context and the user has a capability
        // to view the content.
        $browser = \repository_contentbank\helper::get_contentbank_browser($context);
        if ($this->can_view_contentbank_content() && !empty($browser->get_contentbank_files())) {
            return true;
        }
        // If not, get the children contexts and recursively check each child context until the previous
        // condition is satisfied or there is no more children contexts left.
        $children = $browser->get_children_contexts();
        foreach ($children as $child) {
            if (!$this->has_accessible_content_in_context_tree($child)) {
                continue;
            }
            return true;
        }
        return false;
    }
}
