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

namespace mod_wiki\local\views;

use core\navigation\views\secondary as core_secondary;

/**
 * Custom secondary navigation class
 *
 * A custom construct of secondary navigation for the wiki module.
 *
 * @package     mod_wiki
 * @copyright   2021 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class secondary extends core_secondary {

    /**
     * Custom module construct for wiki.
     */
    protected function load_module_navigation(): void {
        $settingsnav = $this->page->settingsnav;
        $mainnode = $settingsnav->find('modulesettings', self::TYPE_SETTING);
        $nodes = $this->get_default_module_mapping();

        if ($mainnode) {
            $viewurl = new \moodle_url('/mod/wiki/view.php', ['id' => $this->page->cm->id]);
            $node = $this->add(get_string('module', 'course'), $viewurl, null, null,
                'modulepage');

            $ismodulepageactive = false;
            // Array containing the base URL's of the pages that are considered to be a part of the 'modulepage'
            // secondary navigation node.
            $pageurls = [
                new \moodle_url('/mod/wiki/view.php'),
                new \moodle_url('/mod/wiki/edit.php'),
                new \moodle_url('/mod/wiki/filesedit.php'),
                new \moodle_url('/mod/wiki/comments.php'),
                new \moodle_url('/mod/wiki/instancecomments.php'),
                new \moodle_url('/mod/wiki/history.php'),
                new \moodle_url('/mod/wiki/viewversion.php'),
                new \moodle_url('/mod/wiki/diff.php'),
                new \moodle_url('/mod/wiki/map.php'),
                new \moodle_url('/mod/wiki/files.php'),
                new \moodle_url('/mod/wiki/admin.php'),
            ];

            foreach($pageurls as $pageurl) {
                if ($this->page->url->compare($pageurl, URL_MATCH_BASE)) {
                    $ismodulepageactive = true;
                    break;
                }
            }
            // If the base URL of the current page matches the base URL of one of the pages that are considered
            // to be a part of the 'modulepage' secondary navigation node, then set the 'modulepage' node to active.
            if ($ismodulepageactive) {
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
