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

use renderable;
use renderer_base;
use templatable;

/**
 * Tertiary navigation selector renderable.
 *
 * @package     core
 * @category    navigation
 * @copyright   2022 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navigation_selector implements renderable, templatable {

    /** @var string $title The title for the tertiary navigation selector. */
    protected $title;

    /** @var array $items An array of navigation_selector_item objects. */
    protected $items = [];

    /** @var navigation_selector_action_item|null $activeactionitem The active navigation selector action item. */
    protected $activeactionitem = null;

    /**
     * Constructor.
     *
     * @param string $title The title for the tertiary navigation selector.
     */
    public function __construct(?string $title = null) {
        $this->title = $title;
    }

    /**
     * Add an item to the tertiary navigation selector.
     *
     * This method adds the navigation selector item to the tertiary navigation selector and also identifies the
     * selected navigation selector item
     *
     * @param navigation_selector_item $item The navigation selector item.
     * @return navigation_selector
     * @throws \coding_exception If attempting to add more then one active action item to the navigation selector.
     */
    public function add_item(navigation_selector_item $item): navigation_selector {
        $activeactionitem = $item instanceof navigation_selector_group_item ?
            $item->get_active_action_item() : ($item->is_active() ? $item : null);
        // If the navigation selector item is or has an action item labeled as active.
        if ($activeactionitem) {
            // If we already have an action item within the navigation selector that is labeled as active, throw an exception.
            if ($this->activeactionitem) {
                throw new \coding_exception('Cannot add more then one active action item to the tertiary navigation selector.');
            }
            $this->activeactionitem = $activeactionitem;
        }

        $this->items[] = $item;
        return $this;
    }

    /**
     * Return all navigation selector items associated to the tertiary navigation selector.
     *
     * @return array
     */
    public function get_items(): array {
        return $this->items;
    }

    /**
     * Return the active action item associated to the navigation selector (if any).
     *
     * @return navigation_selector_action_item|null The active action item associated to the navigation selector.
     */
    public function get_active_action_item(): ?navigation_selector_action_item {
        return $this->activeactionitem;
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output Renderer to be used to render the tertiary navigation selector.
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $items = array_map(function($item) use ($output) {
            return array_merge($item->export_for_template($output),
                ['isgroup' => $item instanceof navigation_selector_group_item]);
        }, $this->items);

        return [
            'title' => $this->title,
            'activeitemtext' => $this->activeactionitem ? $this->activeactionitem->get_name() : null,
            'items' => $items,
        ];
    }
}
