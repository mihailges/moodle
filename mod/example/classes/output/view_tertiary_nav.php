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

namespace mod_example\output;

use moodle_url;
use templatable;
use renderable;

/**
 * Renderable class for the tertiary navigation elements in the view page.
 *
 * @package    mod_example
 * @copyright  2022 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_tertiary_nav implements templatable, renderable {

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output The renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        global $PAGE;

        $id = $PAGE->url->get_param('id');
        $subpage = $PAGE->url->get_param('subpage');

        // Generate the URL select element for the tertiary navigation.
        $subpage1 = new moodle_url('/mod/example/view.php', ['id' => $id, 'subpage' => 1]);
        $subpage2 = new moodle_url('/mod/example/view.php', ['id' => $id, 'subpage' => 2]);
        $urlselectelements = [
            $subpage1->out(false) => 'Subpage 1',
            $subpage2->out(false) => 'Subpage 2',
        ];
        $selected = $subpage == 1 ? $subpage1->out(false) : $subpage2->out(false);
        $urlselect = new \url_select($urlselectelements, $selected, null, 'viewactionselect');

        $data = [
            'urlselect' => $urlselect->export_for_template($output),
        ];

        if ($subpage == 1) {
            // Generate the primary action button for the tertiary navigation.
            $primaryaction = new moodle_url('/mod/example/action1.php');
            $primaryactionbutton = new \single_button($primaryaction, 'Action 1', 'get', true);
            $data['action1'] = $primaryactionbutton->export_for_template($output);

            // Generate the secondary action button for the tertiary navigation.
            $secondaryaction = new moodle_url('/mod/example/action2.php');
            $secondaryactionbutton = new \single_button($secondaryaction, 'Action 2', 'get', false);
            $data['action2'] = $secondaryactionbutton->export_for_template($output);
        }

        return $data;
    }
}
