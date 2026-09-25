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

use PHPUnit\Framework\Attributes\CoversClass;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use core\oauth2\server\client_manager;
use core\oauth2\server\entity\client_entity;
use core\oauth2\server\entity\auth_code_entity;

/**
 * Tests for {@see auth_code_repository}.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(auth_code_repository::class)]
final class auth_code_repository_test extends \advanced_testcase {
    /**
     * Test getting a new auth code.
     */
    public function test_get_new_auth_code(): void {
        $repository = \core\di::get(auth_code_repository::class);
        $code = $repository->getNewAuthCode();

        $this->assertInstanceOf(auth_code_entity::class, $code);
    }

    /**
     * Test persisting a new auth code.
     *
     * Also verifies that the issuing client's lastaccessed timestamp is updated at the exact
     * moment the code is persisted, since that is the definitive point at which the client's
     * access is known - tracking it any earlier (e.g. when authorize() is merely requested)
     * would let anyone bump it just by loading the authorize endpoint, without ever
     * authenticating.
     */
    public function test_persist_new_auth_code(): void {
        global $DB;

        $this->resetAfterTest();

        $clock = $this->mock_clock_with_frozen();

        $repository = \core\di::get(auth_code_repository::class);

        $client = $this->create_client_entity();

        $scope = $this->createMock(ScopeEntityInterface::class);
        $scope->method('getIdentifier')->willReturn('profile');

        $code = new auth_code_entity();
        $code->setIdentifier('code-id');
        $code->setClient($client);
        $code->setUserIdentifier('123');
        $code->addScope($scope);
        $code->setRedirectUri('https://example.test/callback');
        $code->setExpiryDateTime(new \DateTimeImmutable('+10 minutes'));

        $repository->persistNewAuthCode($code);

        $record = $DB->get_record('oauth2_server_client_auth_codes', ['identifier' => 'code-id']);
        $this->assertNotEmpty($record);
        $this->assertSame('code-id', $record->identifier);
        $this->assertEquals(123, $record->userid);
        $this->assertEquals($client->getIdentifier(), $record->clientidentifier);
        $this->assertSame('https://example.test/callback', $record->redirecturi);
        $this->assertSame('profile', $record->scopes);
        $this->assertEquals(0, $record->revoked);

        $clientrecord = $DB->get_record('oauth2_server_clients', ['id' => $client->get_id()]);
        $this->assertEquals($clock->time(), $clientrecord->lastaccessed);

        // Duplicate identifier.
        $this->expectException(\dml_write_exception::class);
        $repository->persistNewAuthCode($code);
    }

    /**
     * Test revoking auth code.
     */
    public function test_revoke_auth_code(): void {
        global $DB;

        $this->resetAfterTest();

        $repository = \core\di::get(auth_code_repository::class);

        $clientid = $DB->insert_record('oauth2_server_clients', [
            'name' => 'Test client',
            'clientidentifier' => 'client-id',
            'ownercontext' => \context_system::instance()->id,
            'status' => client_entity::STATUS_ACTIVE,
            'isconfidential' => 1,
            'timecreated' => time(),
        ]);

        $DB->insert_record('oauth2_server_client_auth_codes', [
            'identifier' => 'code-id-to-revoke',
            'userid' => 123,
            'clientidentifier' => $clientid,
            'redirecturi' => 'https://example.test/callback',
            'scopes' => 'profile',
            'expirytime' => time() + 600,
            'revoked' => auth_code_entity::REVOKED_NO,
            'timecreated' => time(),
        ]);

        $this->assertFalse($repository->isAuthCodeRevoked('code-id-to-revoke'));

        $repository->revokeAuthCode('code-id-to-revoke');

        $this->assertTrue($repository->isAuthCodeRevoked('code-id-to-revoke'));
    }

    /**
     * Test check if auth code is revoked.
     */
    public function test_is_auth_code_revoked(): void {
        $repository = \core\di::get(auth_code_repository::class);

        $this->expectException(\dml_missing_record_exception::class);
        $repository->isAuthCodeRevoked('non-existent-code');
    }

    /**
     * Test that ownership of an authorisation code is resolved against the issuing client.
     *
     * @return void
     */
    public function test_is_owned_by_client(): void {
        global $DB;

        $this->resetAfterTest();

        $DB->insert_record('oauth2_server_client_auth_codes', (object) [
            'identifier' => 'code-1',
            'userid' => 123,
            'clientidentifier' => 'client-a',
            'redirecturi' => 'https://example.com/callback',
            'scopes' => 'profile',
            'expirytime' => time() + MINSECS,
            'revoked' => auth_code_entity::REVOKED_NO,
            'timecreated' => time(),
        ]);

        $repository = \core\di::get(auth_code_repository::class);

        $this->assertTrue($repository->is_owned_by_client('code-1', 'client-a'));
        $this->assertFalse($repository->is_owned_by_client('code-1', 'client-b'));
        $this->assertFalse($repository->is_owned_by_client('no-such-code', 'client-a'));
    }

    /**
     * Create and persist a real client entity to attach to an auth code fixture.
     *
     * Built via a real database row (rather than a bare new client_entity() with only its
     * identifier set), since {@see client_entity::get_id()} must not be accessed before
     * initialisation, and persisting an auth code now needs the client's real id in order to
     * track its lastaccessed timestamp.
     *
     * @return client_entity
     */
    private function create_client_entity(): client_entity {
        return \core\di::get(client_manager::class)->create_client(
            name: 'Example client',
            ownercontext: \core\context\system::instance(),
            granttypes: [],
        );
    }
}
