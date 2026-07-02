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

namespace core\oauth2\server\repository;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use core\oauth2\server\entity\client_entity;

/**
 * OAuth2 server client repository.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class client_repository implements ClientRepositoryInterface {

    /**
     * Get a client entity.
     *
     * @param string $clientIdentifier The client's unique identifier.
     * @return ClientEntityInterface|null The client entity or null if not found.
     */
    public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface {
        global $DB;

        $clientrecord = $DB->get_record('oauth2_server_clients', ['clientidentifier' => $clientIdentifier]);

        if (!$clientrecord) {
            return null;
        }

        // Fetch redirect URIs.
        $urirecords = $DB->get_records('oauth2_server_client_redirect_uris', ['clientidentifier' => $clientrecord->id]);

        return client_entity::create_from_record($clientrecord, $urirecords);
    }

    /**
     * Validate a client's secret.
     *
     * @param string $clientidentifier The client's unique identifier.
     * @param string|null $clientsecret The client's secret (if confidential).
     * @param string|null $granttype The grant type used.
     * @return bool True if valid, false otherwise.
     */
    public function validateClient(string $clientidentifier, ?string $clientsecret, ?string $granttype): bool {
        global $DB;

        $cliententity = $this->getClientEntity($clientidentifier);

        if ($cliententity === null) {
            return false;
        }

        if (!$cliententity->supportsGrantType($granttype)) {
            return false;
        }

        if ($clientsecret === null || $clientsecret === '') {
            return false;
        }

        // Fetch all active, non-revoked secrets for this client.
        $secrets = $DB->get_records('oauth2_server_client_secrets', [
            'clientidentifier' => $clientidentifier,
            'revoked' => client_entity::STATUS_ACTIVE,
        ]);

        foreach ($secrets as $secret) {
            if (password_verify($clientsecret, $secret->secret)) {
                return true;
            }
        }

        return false;
    }
}
