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

use moodle_url;

/**
 * Renderable class representing an action item for the tertiary navigation selector.
 *
 * @package     core
 * @category    navigation
 * @copyright   2022 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navigation_selector_action_item extends navigation_selector_item {

    /** @var moodle_url $action The action (url) attached to the item. */
    protected $action;

    /** @var bool $active Whether the navigation selector action item is labeled as active. */
    protected $active;

    /** @var array $attributes An array containing additional HTML attributes. */
    protected $attributes;

    /**
     * Constructor.
     *
     * @param string $name The name (title) of the navigation selector action item.
     * @param moodle_url $action The action (url) attached to the item.
     * @param bool $active Whether the navigation selector action item is labeled as active.
     * @param array $attributes Array containing additional HTML attributes. e.g. ['target' => '_blank', 'id' => 'uid'].
     */
    public function __construct(string $name, moodle_url $action, bool $active = false, array $attributes = []) {
        parent::__construct($name);
        $this->action = $action;
        $this->active = $active;
        $this->attributes = $attributes;
    }

    /**
     * Set whether the action item is active (selected) is not.
     *
     * @param bool $active Whether the action item is active or not.
     */
    public function set_active(bool $active = false) {
        $this->active = $active;
    }

    /**
     * Check whether the action item is labeled as active (selected).
     *
     * @return bool
     */
    public function is_active(): bool {
        return $this->active;
    }

    /**
     * Whether the action item is active (selected).
     *
     * @return moodle_url
     */
    public function get_action(): moodle_url {
        return $this->action;
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output Renderer to be used to render the action items for the tertiary navigation selector.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        $attributes = [];
        foreach ($this->attributes as $key => $value) {
            $attributes[] = ['name' => $key, 'value' => $value];
        }

        return array_merge(
            parent::export_for_template($output),
            [
                'action' => $this->action->out(false),
                'isactive' => $this->active,
                'attributes' => $attributes,
            ]
        );
    }
}
