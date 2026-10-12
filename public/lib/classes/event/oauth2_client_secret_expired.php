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

namespace core\event;

/**
 * Fired when an OAuth 2 client secret has naturally expired.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class oauth2_client_secret_expired extends base {
    #[\Override]
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'oauth2_server_client_secrets';
    }

    #[\Override]
    public static function get_name() {
        return get_string('eventoauth2clientsecretexpired');
    }

    #[\Override]
    public function get_description() {
        return "The OAuth 2 secret with ID '{$this->objectid}' has expired for the client with ID '{$this->other['clientid']}'.";
    }

    #[\Override]
    public function get_url() {
        return \core\router\util::get_path_for_callable(
            [\core_admin\route\controller\oauth2\server\client_management::class, 'manage_client_secrets'],
            ['client' => $this->other['clientid']]
        );
    }

    #[\Override]
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['clientid'])) {
            throw new \coding_exception('The key \'clientid\' must be set in $other.');
        }
    }
}
