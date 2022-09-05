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

namespace gradereport_user41\output;

use moodle_url;

/**
 * Renderable class for the action bar elements in the user report page.
 *
 * @package    gradereport_user41
 * @copyright  2022 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action_bar extends \core_grades\output\action_bar {

    /** @var object $user The user object. */
    protected $user;

    /**
     * The class constructor.
     *
     * @param object $context The context object.
     * @param string $user The user object.
     */
    public function __construct($context, $user = null) {
        parent::__construct($context);
        $this->user = $user;
    }

    /**
     * Returns the template for the action bar.
     *
     * @return string
     */
    public function get_template(): string {
        return 'gradereport_user41/action_bar';
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        $data = [];

        // If in the course context, we should display the general navigation selector in gradebook.
        $courseid = $this->context->instanceid;
        // Get the data used to output the general navigation selector.
        $generalnavselector = new \core_grades\output\general_action_bar($this->context,
            new moodle_url('/grade/report/user41/index.php', ['id' => $courseid]), 'gradereport', 'user41');
        $data = $generalnavselector->export_for_template($output);

        $data['user'] = [
            'avatar' => $output->user_picture($this->user, array('size' => 40, 'link' => false)),
            'name' => fullname($this->user),
            'email' => $this->user->email
        ];

        return $data;
    }
}
