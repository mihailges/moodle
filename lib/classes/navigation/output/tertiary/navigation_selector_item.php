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

namespace core\navigation\output\tertiary;

use templatable;
use renderable;

/**
 * Base renderable class for tertiary navigation selector items.
 *
 * @package     core
 * @category    navigation
 * @copyright   2022 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class navigation_selector_item implements templatable, renderable {

    /** @var string $name The name (title) of the navigation selector item. */
    protected $name;

    /**
     * Constructor.
     *
     * @param string $name The name (title) of the navigation selector item.
     */
    public function __construct(string $name) {
        $this->name = $name;
    }

    /**
     * Returns the name (title) of the navigation selector item.
     *
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        return [
            'name' => $this->name,
        ];
    }
}
