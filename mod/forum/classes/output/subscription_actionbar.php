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
 * Output the activityActionbar for this activity.
 *
 * @package   mod_forum
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_forum\output;

use moodle_url;
use renderer_base;
use url_select;
use renderable;
use templatable;

/**
 * Renders the subscribers page.
 *
 * @copyright 2021 Sujith Haridasan <sujith@moodle.com>
 * @package mod_forum\output
 */
class subscription_actionbar implements renderable, templatable {
    /** @var int course id */
    private $id;

    /** @var moodle_url */
    private $currenturl;

    /** @var int 1 for manage subscribers else view subscribers */
    private $edit;

    /** @var false|string  */
    private $sesskey;

    /** @var null  */
    private $viewmanageselect;

    /** @var null  */
    private $subscribeoptionselect;

    /**
     * subscription_actionbar constructor.
     *
     * @param int $id
     * @param int $edit
     * @param moodle_url $currenturl
     */
    public function __construct(int $id, int $edit, moodle_url $currenturl) {
        $this->id = $id;
        $this->currenturl = $currenturl;
        $this->edit = $edit;
        $this->sesskey = sesskey();
        $this->viewmanageselect = null;
        $this->subscribeoptionselect = null;
    }

    /**
     * Create url select menu for subscription option
     *
     * @return url_select
     */
    private function create_subscription_menu() {
        $optionallink = new moodle_url('/mod/forum/subscribe.php', ['id' => $this->id, 'mode' => 0, 'sesskey' => $this->sesskey]);
        $forcedlink = new moodle_url('/mod/forum/subscribe.php', ['id' => $this->id, 'mode' => 1, 'sesskey' => $this->sesskey]);
        $autolink = new moodle_url('/mod/forum/subscribe.php', ['id' => $this->id, 'mode' => 2, 'sesskey' => $this->sesskey]);
        $disabledlink = new moodle_url('/mod/forum/subscribe.php', ['id' => $this->id, 'mode' => 3, 'sesskey' => $this->sesskey]);

        $menu = [
            $optionallink->out(false) => get_string('subscriptionoptional', 'forum'),
            $forcedlink->out(false) => get_string('subscriptionforced', 'forum'),
            $autolink->out(false) => get_string('subscriptionauto', 'forum'),
            $disabledlink->out(false) => get_string('subscriptiondisabled', 'forum'),
        ];

        $urlselect = new url_select($menu, $this->currenturl, null, 'selectsubscriptionoptions');
        $urlselect->class .= ' float-right';
        return $urlselect;
    }

    /**
     * Create view and manage subscribers select menu.
     *
     * @return url_select
     */
    private function create_view_manage_menu() {
        $viewlink = new moodle_url('/mod/forum/subscribers.php', ['id' => $this->id, 'edit' => 'off']);
        $managelink = new moodle_url('/mod/forum/subscribers.php', ['id' => $this->id, 'edit' => 'on']);

        $menu = [
            $viewlink->out(false) => get_string('forum:viewsubscribers', 'forum'),
            $managelink->out(false) => get_string('managesubscriptionson', 'forum'),
        ];

        if ($this->edit === 1) {
            $this->currenturl = $managelink;
        } else {
            $this->currenturl = $viewlink;
        }

        $urlselect = new url_select($menu, $this->currenturl->out(false), null, 'selectviewandmanagesubscribers');
        return $urlselect;
    }

    /**
     * Data for the template.
     *
     * @param renderer_base $output
     * @return array data for template
     */
    public function export_for_template(renderer_base $output)
    {
        $data = [
            'subscriptionoptions' => $this->subscribeoptionselect->export_for_template($output),
            'viewandmanageselect' => $this->viewmanageselect->export_for_template($output),
        ];
        return $data;
    }

    /**
     * Get the HTML elements rendered for the subscribers page.
     *
     * @return string
     */
    public function get_response_result() {
        global $PAGE;
        $this->subscribeoptionselect = $this->create_subscription_menu();
        $this->viewmanageselect = $this->create_view_manage_menu();
        $renderer = $PAGE->get_renderer('mod_forum');
        return $renderer->subscription_actionbar($this);
    }
}
