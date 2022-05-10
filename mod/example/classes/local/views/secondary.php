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

namespace mod_example\local\views;

/**
 * Custom secondary navigation class.
 *
 * @package     mod_example
 * @category    navigation
 * @copyright   2022 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class secondary extends \core\navigation\views\secondary {

    /**
     * Define the keys of the module secondary nav nodes that should be forced into the "more" menu by default.
     *
     * @return array
     */
    protected function get_default_module_more_menu_nodes(): array {
        $moremenunodes = parent::get_default_module_more_menu_nodes();

        // Remove the 'Filter' and 'Permissions' nodes from the "more" dropdown menu.
//        $nodestoremove = ['filtermanage', 'roleoverride'];
//        foreach ($nodestoremove as $nodekey) {
//            if (($key = array_search($nodekey, $moremenunodes)) !== false) {
//                unset($moremenunodes[$key]);
//            }
//        }

        return $moremenunodes;
    }

    /**
     * Defines the default structure for the secondary nav in a module context.
     *
     * In a module context, we are curating nodes from the settingsnav object.
     * The following mapping construct specifies the type of the node, the key
     * and in what order we want the node - defined as per the mockups.
     *
     * @return array
     */
    protected function get_default_module_mapping(): array {
        return [
            self::TYPE_SETTING => [
                'modedit' => 1,
                'page1' => 2,
                'page2' => 3,
                'page3' => 4,
                'filtermanage' => 5,
                'roleoverride' => 6,
                'rolecheck' => 6.1,
                'roleassign' => 6.2,
            ],
        ];

        return $basenodes;
    }
}
