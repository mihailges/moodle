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
 * Utility class for presenting the googledocs repository contents.
 *
 * @package    repository_googledocs
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace repository_googledocs;

/**
 * Base class for presenting the googledocs repository contents.
 *
 * @package    repository_googledocs
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class googledocs_content {

    /** @var rest The rest API object. */
    protected $service;

    /** @var string The current path. */
    protected $path;

    /** @var bool Whether sorting should be applied to the fetched content. */
    protected $sortcontent;

    /**
     * Constructor.
     *
     * @param rest $service The rest API object
     * @param string $path The current path
     * @param bool $sortcontent Whether sorting should be applied to the content
     */
    public function __construct(rest $service, string $path, bool $sortcontent = true) {
        $this->service = $service;
        $this->path = $path;
        $this->sortcontent = $sortcontent;
    }

    /**
     * Generate and return all nodes (files and folders) for the existing content based on the path or search query.
     *
     * @param string $query The search query
     * @param callable $isaccepted The callback function which determines whether a given file should be displayed
     *                             or filtered based on the existing file restrictions
     * @return array[] The array containing the content nodes
     */
    public function get_content_nodes(string $query, callable $isaccepted): array {
        // Create the repository content nodes.
        $contentnodes = array_reduce($this->get_contents($query), function ($carry, $content) use ($isaccepted) {
            $contentnodeobj = helper::get_content_node($content, $this->path);
            $contentnode = $contentnodeobj->create_node();
            // If the node was successfully created and the content type is accepted, add it to the content nodes array.
            if ($contentnode && $isaccepted($contentnode)) {
                $carry[] = $contentnode;
            }
            return $carry;
        }, []);

        // Sort the contents if required.
        if ($this->sortcontent) {
            return $this->get_sorted_content_nodes($contentnodes);
        }

        return $contentnodes;
    }

    /**
     * Build the navigation (breadcrumb) from a given path.
     *
     * @return array Array containing name and path of each navigation node
     */
    public function get_navigation(): array {
        $nav = [];
        $navtrail = '';
        $pathnodes = explode('/', $this->path);

        foreach ($pathnodes as $node) {
            list($id, $name) = helper::explode_node_path($node);
            $name = empty($name) ? $id : $name;
            $nav[] = array(
                'name' => $name,
                'path' => helper::build_node_path($id, $name, $navtrail)
            );
            $tmp = end($nav);
            $navtrail = $tmp['path'];
        }

        return $nav;
    }

    /**
     * Returns the array of grouped and sorted alphabetically content nodes (folders and files).
     *
     * @param array $contents The content nodes array
     * @return array[] The array containing the sorted content nodes
     */
    private function get_sorted_content_nodes(array $contents): array {
        $files = [];
        $folders = [];
        foreach ($contents as $content) {
            // Group the content nodes by type (files and folders). Generate unique array keys for each content node
            // which will be later used by the sorting function. Note: Using the item id along with the name as key of
            // the array because Google Drive allows files and folders with identical names.
            if ($content['source']) { // If the content node has a source attribute, it is a file node.
                $files["{$content['title']}{$content['id']}"] = $content;
            } else {
                $folders["{$content['title']}{$content['id']}"] = $content;
            }
        }
        // Order the results alphabetically by their array keys.
        \core_collator::ksort($files, \core_collator::SORT_STRING);
        \core_collator::ksort($folders, \core_collator::SORT_STRING);

        return array_merge(array_values($folders), array_values($files));
    }

    /**
     * Returns all relevant contents (files and folders) based on the given path or search query.
     *
     * @param string $query The search query
     * @return array The array containing the contents
     */
    abstract protected function get_contents(string $query): array;
}
