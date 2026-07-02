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

use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use core\oauth2\server\entity\auth_code_entity;

/**
 * OAuth2 server auth code repository.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_code_repository implements AuthCodeRepositoryInterface {

    /**
     * Get a new auth code.
     *
     * @return AuthCodeEntityInterface The auth code entity.
     */
    public function getNewAuthCode(): AuthCodeEntityInterface {
        return new auth_code_entity();
    }

    /**
     * Persist a new auth code.
     *
     * @param AuthCodeEntityInterface $authcodeentity The auth code entity.
     * @return void
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authcodeentity): void {
        global $DB;

        $scopes = array_map(function($scope) {
            return $scope->getIdentifier();
        }, $authcodeentity->getScopes());

        $record = new \stdClass();
        $record->identifier = $authcodeentity->getIdentifier();
        $record->userid = (int) $authcodeentity->getUserIdentifier();
        $record->clientidentifier = $authcodeentity->getClient()->getIdentifier();
        $record->redirecturi = $authcodeentity->getRedirectUri();
        $record->scopes = implode(' ', $scopes);
        $record->expirytime = $authcodeentity->getExpiryDateTime()->getTimestamp();
        $record->revoked = auth_code_entity::REVOKED_NO;

        $DB->insert_record('oauth2_server_client_auth_codes', $record);
    }

    /**
     * Revoke an auth code.
     *
     * @param string $codeid The auth code identifier.
     * @return void
     */
    public function revokeAuthCode(string $codeid): void {
        global $DB;

        $DB->set_field(
            'oauth2_server_client_auth_codes',
            'revoked',
            auth_code_entity::REVOKED_YES,
            ['identifier' => $codeid],
        );
    }

    /**
     * Check if an auth code is revoked.
     *
     * @param string $codeid The auth code identifier.
     * @return bool True if revoked, false otherwise.
     */
    public function isAuthCodeRevoked(string $codeid): bool {
        global $DB;

        $revoked = $DB->get_field(
            'oauth2_server_client_auth_codes',
            'revoked',
            ['identifier' => $codeid],
            MUST_EXIST
        );

        return (int) $revoked === auth_code_entity::REVOKED_YES;
    }
}
