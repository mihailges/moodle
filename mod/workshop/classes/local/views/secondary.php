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
 * Custom secondary navigation for the workshop activity.
 *
 * This would help the activity tab to be activated when
 * confirmation dialog is asked to user in switchphase.php.
 *
 * @package   mod_workshop
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_workshop\local\views;

use core\navigation\views\secondary as core_view_secondary;

/**
 * This class helps to highlight the node in the secondary navigation.
 *
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @package mod_workshop
 */
class secondary extends core_view_secondary {
    /**
     * Get the module's secondary navigation. This is based on settings_nav and would include plugin nodes added via
     * '_extend_settings_navigation'.
     * It populates the tree based on the nav mockup
     *
     * If nodes change, we will have to explicitly call the callback again.
     *
     * @return void
     */
    protected function load_module_navigation(): void {
        $settingsnav = $this->page->settingsnav;
        $mainnode = $settingsnav->find('modulesettings', self::TYPE_SETTING);
        $nodes = $this->get_default_module_mapping();

        if ($mainnode) {
            $url = new \moodle_url('/mod/' . $this->page->activityname . '/view.php', ['id' => $this->page->cm->id]);
            $setactive = $url->compare($this->page->url, URL_MATCH_BASE);
            $node = $this->add(get_string('module', 'course'), $url, null, null, 'modulepage');
            if (!$setactive) {
                // An additional check to see if the url is switchphase.
                // If $PAGE->url is switchphase, then activity tab needs to be active.
                $switchphaseurl = new \moodle_url('/mod/' . $this->page->activityname . '/switchphase.php',
                        ['id' => $this->page->cm->id]);
                $setactive = $switchphaseurl->compare($this->page->url, URL_MATCH_BASE);
            }
            if ($setactive) {
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
