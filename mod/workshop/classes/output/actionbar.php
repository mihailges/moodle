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
 * Output the actionbar for workshop activity.
 *
 * @package   mod_workshop
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_workshop\output;

use moodle_url;
use url_select;

/**
 * Output the rendered elements for the tertiary nav for page action.
 *
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @package mod_workshop\output
 */
class actionbar {
    /**
     * The course id.
     *
     * @var int $cmid
     */
    private $cmid;

    /**
     * The current url.
     *
     * @var moodle_url $currenturl
     */
    private $currenturl;

    /**
     * actionbar constructor.
     * @param int $cmid
     * @param moodle_url $currenturl
     */
    public function __construct(int $cmid, moodle_url $currenturl) {
        $this->cmid = $cmid;
        $this->currenturl = $currenturl;
    }

    /**
     * Creates the select menu for allocation page.
     *
     * @return url_select
     */
    private function create_select_menu() {
        $manualallocationlink = new moodle_url('/mod/workshop/allocation.php', ['cmid' => $this->cmid, 'method' => 'manual']);
        $randomallocationlink = new moodle_url('/mod/workshop/allocation.php', ['cmid' => $this->cmid, 'method' => 'random']);
        $scheduleallocationlink = new moodle_url('/mod/workshop/allocation.php', ['cmid' => $this->cmid, 'method' => 'scheduled']);

        $menu = [
            $manualallocationlink->out(false) => get_string('pluginname', 'workshopallocation_manual'),
            $randomallocationlink->out(false) => get_string('pluginname', 'workshopallocation_random'),
            $scheduleallocationlink->out(false) => get_string('pluginname', 'workshopallocation_scheduled'),
        ];

        $urlselect = new url_select($menu, $this->currenturl->out(false), null, 'allocationsetting');
        return $urlselect;
    }

    /**
     * Rendered HTML for the allocation action.
     *
     * @return string
     */
    public function get_allocation_menu() {
        global $OUTPUT;

        $urlselect = $this->create_select_menu();
        return $OUTPUT->render($urlselect);
    }
}
