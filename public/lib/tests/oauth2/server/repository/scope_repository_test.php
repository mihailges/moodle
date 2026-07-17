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

use core\oauth2\server\entity\client_entity;
use core\tests\fake_plugins_test_trait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use core\oauth2\server\entity\auth_code_entity;

/**
 * Tests for {@see scope_repository}.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(scope_repository::class)]
final class scope_repository_test extends \advanced_testcase {
    use fake_plugins_test_trait;

    /**
     * Test getScopeEntityByIdentifier with various scope identifiers.
     *
     * @param string $identifier The identifier to look up.
     * @param bool $expectedfound True if a scope entity is expected to be returned.
     */
    #[DataProvider('get_scope_entity_provider')]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function test_get_scope_entity_by_identifier(string $identifier, bool $expectedfound): void {
        $this->resetAfterTest();

        // Inject the 'fake' plugin type that contains the 'oauth2scope' test plugin that registers the
        // 'test_scope' scope.
        $this->add_full_mocked_plugintype(
            plugintype: 'fake',
            path: 'public/lib/tests/fixtures/fakeplugins/fake',
        );

        $repository = new scope_repository();
        $scope = $repository->getScopeEntityByIdentifier($identifier);

        if ($expectedfound) {
            $this->assertInstanceOf(ScopeEntityInterface::class, $scope);
            $this->assertSame($identifier, $scope->getIdentifier());
        } else {
            $this->assertNull($scope);
        }
    }

    /**
     * Data provider for getScopeEntityByIdentifier.
     *
     * @return array
     */
    public static function get_scope_entity_provider(): array {
        return [
            'existing scope' => [
                'test_scope',
                true,
            ],
            'non-existing scope' => [
                'non_existent',
                false,
            ],
        ];
    }

    /**
     * Test finalizeScopes with different user and database states.
     *
     * @param bool $hasuseridentifier Whether the user identifier is provided.
     * @param array $requestedscopes The identifiers of requested scopes.
     * @param string|null $grantedscopestring The space-separated scopes granted in DB, or null if no record.
     * @param array $expectedscopeidentifiers The identifiers of finalized scopes expected.
     */
    #[DataProvider('finalize_scopes_provider')]
    public function test_finalize_scopes(
        bool $hasuseridentifier,
        array $requestedscopes,
        string $granttype,
        ?string $authcodeid,
        ?string $sessiongrantedscopestring,
        ?string $globalgrantedscopestring,
        array $expectedscopeidentifiers
    ): void {
        global $DB;

        $this->resetAfterTest();

        $clientidentifier = 'client_123';

        // Register a client.
        $DB->insert_record('oauth2_server_clients', (object)[
            'clientidentifier' => $clientidentifier,
            'name' => 'Test client',
            'ownercontext' => \context_system::instance()->id,
            'status' => client_entity::STATUS_ACTIVE,
            'isconfidential' => 1,
            'timecreated' => time(),
        ]);

        $client = $this->createMock(ClientEntityInterface::class);
        $client->method('getIdentifier')->willReturn($clientidentifier);

        $useridentifier = null;

        if ($hasuseridentifier) {
            $user = $this->getDataGenerator()->create_user();
            $useridentifier = (string) $user->id;

            $DB->insert_record(
                'oauth2_server_client_granted_scopes',
                (object)[
                    'clientidentifier' => $clientidentifier,
                    'userid' => $user->id,
                    'scope' => $globalgrantedscopestring ?? '',
                    'timecreated' => time(),
                ],
            );

            $DB->insert_record(
                'oauth2_server_client_auth_codes',
                (object) [
                    'identifier' => 'authcode-123',
                    'userid' => $user->id,
                    'clientidentifier' => $clientidentifier,
                    'redirecturi' => 'https://example.test/callback',
                    'scopes' => $sessiongrantedscopestring ?? '',
                    'expirytime' => time() + 600,
                    'revoked' => auth_code_entity::REVOKED_NO,
                    'timecreated' => time(),
                ],
            );
        }

        // Mock scope entities.
        $scopes = [];
        foreach ($requestedscopes as $name) {
            $scope = $this->createMock(ScopeEntityInterface::class);
            $scope->method('getIdentifier')->willReturn($name);
            $scopes[] = $scope;
        }

        $repository = new scope_repository();
        $finalized = $repository->finalizeScopes($scopes, $granttype, $client, $useridentifier, $authcodeid);

        $finalizedidentifiers = array_map(function($s) {
            return $s->getIdentifier();
        }, $finalized);

        // Normalize array keys to compare values.
        $this->assertEquals(array_values($expectedscopeidentifiers), array_values($finalizedidentifiers));
    }

    /**
     * Data provider for finalizeScopes.
     *
     * @return array
     */
    public static function finalize_scopes_provider(): array {
        return [
            'no user identifier (null)' => [
                false,
                ['profile', 'email'],
                'refresh_token',
                null,
                null,
                null,
                ['profile', 'email'],
            ],
            'no granted scopes during authorization code grant' => [
                true,
                ['profile', 'email'],
                'authorization_code',
                'authcode-123',
                null,
                null,
                [],
            ],
            'session granted scopes only during authorization code grant' => [
                true,
                ['profile', 'email'],
                'authorization_code',
                'authcode-123',
                'profile email',
                null,
                ['profile', 'email'],
            ],
            'global granted scopes only during authorization code grant' => [
                true,
                ['profile', 'email'],
                'authorization_code',
                'authcode-123',
                null,
                'profile',
                [],
            ],
            'session and global granted scopes during authorization code grant' => [
                true,
                ['profile', 'email'],
                'authorization_code',
                'authcode-123',
                'email',
                'profile email',
                ['email'],
            ],
            'no granted scopes during refresh token grant' => [
                true,
                ['profile', 'email'],
                'refresh_token',
                null,
                null,
                null,
                [],
            ],
            'session granted scopes only during refresh token grant' => [
                true,
                ['profile', 'email'],
                'refresh_token',
                null,
                'profile email',
                null,
                [],
            ],
            'global granted scopes only during refresh token grant' => [
                true,
                ['profile', 'email'],
                'refresh_token',
                null,
                null,
                'profile',
                ['profile'],
            ],
            'session and global granted scopes during refresh token grant' => [
                true,
                ['profile', 'email'],
                'refresh_token',
                null,
                'email',
                'profile email',
                ['profile', 'email'],
            ],
        ];
    }

    /**
     * Test get_scope_map retrieve values from cache, or populates cache via the scope discovery mechanism.
     *
     * @param bool $scopemapcached Whether to pre-populate cache before calling get_scope_map()
     * @param string $expectedscopename The expected scope name
     * @param string $expectedscopeclass The expected scope class name
     */
    #[DataProvider('get_scope_map_provider')]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function test_get_scope_map(bool $scopemapcached, string $expectedscopename, string $expectedscopeclass): void {
        $this->resetAfterTest();

        // If testing the cached behavior, pre-populate the cache with a defined scope map. Otherwise, add a mock plugin
        // that registers a scope to be discovered.
        if ($scopemapcached) {
            $cache = \cache::make('core', 'oauth2_server');
            $cache->set('scope_map', [
                'cached_scope' => 'cached_scope_class_name',
            ]);
        } else {
            // Inject the 'fake' plugin type that contains the 'oauth2scope' test plugin that registers the
            // 'test_scope' scope.
            $this->add_full_mocked_plugintype(
                plugintype: 'fake',
                path: 'public/lib/tests/fixtures/fakeplugins/fake',
            );
        }

        $scopemap = scope_repository::get_scope_map();

        $this->assertArrayHasKey($expectedscopename, $scopemap);
        $this->assertSame($expectedscopeclass, $scopemap[$expectedscopename]);
    }

    /**
     * Data provider for test_get_scope_map.
     *
     * @return array
     */
    public static function get_scope_map_provider(): array {
        return [
            'unpopulated cache' => [
                false,
                'test_scope',
                'fake_oauth2scope\route\scope\test_scope',
            ],
            'populated cache' => [
                true,
                'cached_scope',
                'cached_scope_class_name',
            ],
        ];
    }
}
