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
 * Class used to represent a file node in the googledocs repository.
 *
 * @package    repository_googledocs
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace repository_googledocs\node;

/**
 * Represents a file node in the googledocs repository.
 *
 * @package    repository_googledocs
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class file_node implements node {

    /** @var string The title of the file node. */
    private $title;

    /** @var string The source for the file node. */
    private $source;

    /** @var string The timestamp representing the last modified date. */
    private $modified;

    /** @var string The size of the file. */
    private $size;

    /** @var bool The thumbnail of the file. */
    private $thumbnail;

    /**
     * Constructor.
     *
     * @param \context_system $title The current context
     */
    public function __construct(string $title, string $source, string $modified, string $size, string $thumbnail) {
        $this->title = $title;
        $this->source = $source;
        $this->modified = $modified;
        $this->size = $size;
        $this->thumbnail = $thumbnail;
    }

    /**
     * Create the file node.
     *
     * @return array
     */
    public function create_node(): array {

        return [
            'title' => $this->title,
            'source' => $this->source,
            'date' => $this->modified,
            'size' => $this->size,
            'thumbnail' => $this->thumbnail,
            'thumbnail_height' => 64,
            'thumbnail_width' => 64,
        ];
    }
}
