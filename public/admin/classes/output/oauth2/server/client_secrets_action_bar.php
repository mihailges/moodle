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

namespace core_admin\output\oauth2\server;

use core\oauth2\server\entity\client_entity;
use core_admin\route\controller\oauth2\server\client_management;

/**
 * Renderable class for the action bar elements in the client secrets page.
 *
 * @package    core_admin
 * @copyright  2026 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class client_secrets_action_bar extends edit_client_action_bar {
    /**
     * The class constructor.
     *
     * @param client_entity $cliententity The client entity object.
     * @param \core\url $activeurl The active URL for the current page.
     * @param bool $cancreatesecret Whether the client can create a new secret.
     */
    public function __construct(
        client_entity $cliententity,
        \core\url $activeurl,
        protected bool $cancreatesecret,
    ) {
        parent::__construct($cliententity, $activeurl);
    }

    /**
     * Returns the template for the action bar.
     *
     * @return string
     */
    public function get_template(): string {
        return 'core_admin/oauth2/server/client_secrets_action_bar';
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        $data = parent::export_for_template($output);
        $data['id'] = $this->cliententity->get_id();
        $data['cancreatesecret'] = $this->cancreatesecret;

        return $data;
    }
}
