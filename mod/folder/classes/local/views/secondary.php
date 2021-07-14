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

namespace mod_folder\local\views;

use core\navigation\views\secondary as core_secondary;

/**
 * Custom secondary navigation class
 *
 * A custom construct of secondary navigation for the folder module.
 *
 * @package     mod_folder
 * @copyright   2021 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class secondary extends core_secondary {

    /**
     * Custom module construct for folder.
     */
    protected function load_module_navigation(): void {
        $settingsnav = $this->page->settingsnav;
        $mainnode = $settingsnav->find('modulesettings', self::TYPE_SETTING);
        $nodes = $this->get_default_module_mapping();

        if ($mainnode) {
            $viewurl = new \moodle_url('/mod/folder/view.php', ['id' => $this->page->cm->id]);
            $node = $this->add(get_string('module', 'course'), $viewurl, null, null, 'modulepage');

            $editurl = new \moodle_url('/mod/folder/edit.php', ['id' => $this->page->cm->id]);

            // Set the 'modulepage' secondary navigation node active if the base URL of the current page matches the
            // base URL of the view folder or edit folder page.
            if ($this->page->url->compare($viewurl, URL_MATCH_BASE) ||
                    $this->page->url->compare($editurl, URL_MATCH_BASE)) {
                $node->make_active();
            }

            // Add the initial nodes.
            $nodesordered = $this->get_leaf_nodes($mainnode, $nodes);
            $this->add_ordered_nodes($nodesordered);

            // We have finished inserting the initial structure.
            // Populate the menu with the rest of the nodes available.
            $this->load_remaining_nodes($mainnode, $nodes);
        }
    }
}
