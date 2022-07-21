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

/**
 * Renderable class representing a group item for the tertiary navigation selector.
 *
 * @package     core
 * @category    navigation
 * @copyright   2022 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navigation_selector_group_item extends navigation_selector_item {

    /** @var array $actionitems An array of navigation_selector_action_item objects. */
    protected $actionitems = [];

    /** @var navigation_selector_action_item|null $activeactionitem The active navigation selector action item. */
    protected $activeactionitem = null;

    /**
     * Add an action item to the tertiary navigation selector group.
     *
     * @param navigation_selector_action_item $actionitem The action item.
     * @return navigation_selector_group_item
     */
    public function add_action_item(navigation_selector_action_item $actionitem): navigation_selector_group_item {
        // If the action item is labeled as active.
        if ($actionitem->is_active()) {
            // If we already have an action item within the group that is labeled as active, throw an exception.
            if ($this->activeactionitem) {
                throw new \coding_exception(
                    'Cannot add more then one active action item into the tertiary navigation selector group.');
            }
            $this->activeactionitem = $actionitem;
        }
        $this->actionitems[] = $actionitem;
        return $this;
    }

    /**
     * Return all action items associated to the navigation selector group.
     *
     * @return array The array of associated navigation_selector_action_item objects.
     */
    public function get_action_items(): array {
        return $this->actionitems;
    }

    /**
     * Return the active action item associated to the navigation selector group (if any).
     *
     * @return navigation_selector_action_item|null The active action item associated to the navigation selector group.
     */
    public function get_active_action_item(): ?navigation_selector_action_item {
        return $this->activeactionitem;
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output Renderer to be used to render the group items for the tertiary navigation selector.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        $data = parent::export_for_template($output);
        foreach ($this->actionitems as $actionitem) {
            $data['actionitems'][] = $actionitem->export_for_template($output);
        }

        return $data;
    }
}
