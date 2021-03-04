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
 * Class used to represent a folder node in the googledocs repository.
 *
 * @package    repository_googledocs
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace repository_googledocs\node;

use repository_googledocs\helper;

/**
 * Represents a folder node in the googledocs repository.
 *
 * @package    repository_googledocs
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class folder_node implements node {

    /** @var string The ID of the folder node. */
    private $id;

    /** @var string The title of the folder node. */
    private $title;

    /** @var bool The timestamp representing the last modified date. */
    private $modified;

    /** @var string The path of the folder node. */
    private $path;

    /**
     * Constructor.
     *
     * @param \stdClass $content The folder object
     * @param string $path The path of the folder node
     */
    public function __construct(\stdClass $content, string $path) {
        $this->id = $content->id;
        $this->title = $content->name;
        $this->modified = $content->modifiedTime ? strtotime($content->modifiedTime) : '';
        $this->path = $path;
    }

    /**
     * Create the folder node.
     *
     * @return array|null The node array or null if the node could not be created
     */
    public function create_node(): ?array {
        global $OUTPUT;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'path' => helper::build_node_path($this->id, $this->title, $this->path),
            'date' => $this->modified ? strtotime($this->modified) : '',
            'thumbnail' => $OUTPUT->image_url(file_folder_icon(64))->out(false),
            'thumbnail_height' => 64,
            'thumbnail_width' => 64,
            'children' => []
        ];
    }
}
