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

namespace core_admin\route\api\oauth2\server;

use core\oauth2\server\client_manager;
use core\oauth2\server\entity\client_entity;
use core\oauth2\server\repository\scope_repository;
use core\tests\router\route_testcase;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for the OAuth2 server clients API scope declarations.
 *
 * @package    core_admin
 * @copyright  Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(clients::class)]
final class clients_test extends route_testcase {
    /**
     * Managing OAuth2 clients (revoking, reactivating, or deleting) is a sensitive administrative action
     * which requires both the read and write config scopes.
     *
     * @param string $method
     */
    #[DataProvider('required_scopes_provider')]
    public function test_required_scopes(string $method): void {
        $this->assert_route_required_scopes(
            [['core_admin:config:read', 'core_admin:config:write']],
            [clients::class, $method],
        );
    }

    /**
     * Data provider for test_required_scopes.
     *
     * @return array
     */
    public static function required_scopes_provider(): array {
        return [
            'disable_client' => ['disable_client'],
            'reactivate_client' => ['reactivate_client'],
            'delete_client' => ['delete_client'],
            'create_client' => ['create_client'],
            'update_client' => ['update_client'],
        ];
    }

    /**
     * Get the identifier of a scope that exists.
     *
     * @return string
     */
    private function get_scope(): string {
        return array_key_first(\core\di::get(scope_repository::class)->get_all_scopes());
    }

    /**
     * Send a JSON request to the clients API as an admin.
     *
     * @param string $method
     * @param string $path
     * @param array $body
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function request(string $method, string $path, array $body) {
        $this->add_class_routes_to_route_loader(clients::class);
        // The redirect URL in the response refers to these controller routes.
        $this->add_class_routes_to_route_loader(
            \core_admin\route\controller\oauth2\server\client_management::class,
            \core\router\route_loader_interface::ROUTE_GROUP_PAGE,
        );
        $this->setAdminUser();

        return $this->process_api_request($method, $path, body: Utils::streamFor(json_encode($body)));
    }

    /**
     * Create a confidential client using the Authorization Code grant.
     *
     * @return client_entity
     */
    private function create_auth_code_client(): client_entity {
        return \core\di::get(client_manager::class)->create_client(
            'Existing',
            \core\context\system::instance(),
            [client_entity::GRANT_TYPE_AUTHORIZATION_CODE, client_entity::GRANT_TYPE_REFRESH_TOKEN],
            ['https://example.com/one', 'https://example.com/two'],
            'Desc',
            true,
            true,
            [$this->get_scope()],
        );
    }

    /**
     * A public client always gets the authorization code grant and PKCE, whatever else is sent.
     */
    public function test_create_public_client(): void {
        $this->resetAfterTest();

        $response = $this->request('POST', '/oauth2/server/clients', [
            'name' => 'Mobile',
            'clienttype' => 'public',
            'flows' => ['client_credentials'],
            'redirecturis' => ['https://example.com/cb', ' ', 'https://example.com/cb'],
            'scopes' => [$this->get_scope()],
        ]);

        $this->assert_valid_response($response, 201);
        $payload = $this->decode_response($response);
        $this->assertFalse($payload->isconfidential);

        $client = \core\di::get(client_manager::class)->get_client_by_id($payload->id);
        $this->assertFalse($client->isConfidential());
        $this->assertTrue($client->is_pkce_required());
        $this->assertTrue($client->supportsGrantType(client_entity::GRANT_TYPE_AUTHORIZATION_CODE));
        $this->assertFalse($client->supportsGrantType(client_entity::GRANT_TYPE_CLIENT_CREDENTIALS));
        $this->assertEquals(
            ['https://example.com/cb'],
            array_values(\core\di::get(client_manager::class)->get_redirect_uris($payload->id)),
        );
    }

    /**
     * A confidential client using only client credentials needs no redirect URI, and continues to its secrets.
     */
    public function test_create_confidential_client_credentials_client(): void {
        $this->resetAfterTest();

        $response = $this->request('POST', '/oauth2/server/clients', [
            'name' => 'Service',
            'description' => 'Machine',
            'clienttype' => 'confidential',
            'flows' => ['client_credentials'],
            'scopes' => [$this->get_scope()],
        ]);

        $this->assert_valid_response($response, 201);
        $payload = $this->decode_response($response);
        $this->assertTrue($payload->isconfidential);
        $this->assertStringContainsString("/{$payload->id}/secrets", $payload->redirecturl);

        $client = \core\di::get(client_manager::class)->get_client_by_id($payload->id);
        $this->assertTrue($client->supportsGrantType(client_entity::GRANT_TYPE_CLIENT_CREDENTIALS));
        $this->assertFalse($client->supportsGrantType(client_entity::GRANT_TYPE_AUTHORIZATION_CODE));
    }

    /**
     * The rules of the old create form are reported per field, with a 422.
     */
    public function test_create_client_validation(): void {
        $this->resetAfterTest();

        $response = $this->request('POST', '/oauth2/server/clients', [
            'name' => ' ',
            'clienttype' => 'confidential',
            'flows' => [],
            'scopes' => [],
        ]);
        $this->assert_valid_response($response, 422);
        $errors = $this->decode_response($response, true)['errors'];
        $this->assertEqualsCanonicalizing(['name', 'flows', 'scopes'], array_keys($errors));

        $response = $this->request('POST', '/oauth2/server/clients', [
            'name' => 'Needs a URI',
            'clienttype' => 'public',
            'scopes' => [$this->get_scope(), 'not:a:scope'],
        ]);
        $this->assert_valid_response($response, 422);
        $errors = $this->decode_response($response, true)['errors'];
        $this->assertEqualsCanonicalizing(['redirecturis', 'scopes'], array_keys($errors));

        $response = $this->request('POST', '/oauth2/server/clients', [
            'name' => 'Bad URI',
            'clienttype' => 'public',
            'redirecturis' => ['https://example.com/ok', 'http://example.com/not-https#frag'],
            'scopes' => [$this->get_scope()],
        ]);
        $this->assert_valid_response($response, 422);
        $this->assertSame(['redirecturis.1'], array_keys($this->decode_response($response, true)['errors']));
    }

    /**
     * A client is returned with the values the edit form sends back.
     */
    public function test_get_client(): void {
        $this->resetAfterTest();
        $client = $this->create_auth_code_client();

        $response = $this->request('GET', "/oauth2/server/clients/{$client->get_id()}", []);

        $this->assert_valid_response($response);
        $payload = $this->decode_response($response, true);
        $this->assertSame($client->get_id(), $payload['id']);
        $this->assertSame('Desc', $payload['description']);
        $this->assertSame([$this->get_scope()], $payload['scopes']);
        $this->assertNotEmpty($payload['redirecturis']);
        $this->assertSame([], $payload['extensions']);
    }

    /**
     * Only the submitted fields change, and redirect URIs are added and removed to match the submitted list.
     */
    public function test_update_client(): void {
        $this->resetAfterTest();
        $client = $this->create_auth_code_client();

        $response = $this->request('PATCH', "/oauth2/server/clients/{$client->get_id()}", [
            'name' => 'Renamed',
            'redirecturis' => ['https://example.com/two', 'https://example.com/three', ''],
            'ispkcerequired' => false,
        ]);

        $this->assert_valid_response($response);
        $payload = $this->decode_response($response, true);
        $this->assertSame('Renamed', $payload['name']);
        $this->assertSame('Desc', $payload['description']);
        $this->assertFalse($payload['ispkcerequired']);
        $this->assertEqualsCanonicalizing(
            ['https://example.com/two', 'https://example.com/three'],
            $payload['redirecturis'],
        );
        $this->assertSame([$this->get_scope()], $payload['scopes']);
    }

    /**
     * Invalid changes are reported with a 422 and nothing is saved.
     */
    public function test_update_client_validation(): void {
        $this->resetAfterTest();
        $client = $this->create_auth_code_client();

        $response = $this->request('PATCH', "/oauth2/server/clients/{$client->get_id()}", [
            'name' => 'Renamed',
            'redirecturis' => ['', ''],
            'scopes' => [],
        ]);

        $this->assert_valid_response($response, 422);
        $this->assertEqualsCanonicalizing(
            ['redirecturis', 'scopes'],
            array_keys($this->decode_response($response, true)['errors']),
        );
        $this->assertSame(
            'Existing',
            \core\di::get(client_manager::class)->get_client_by_id($client->get_id())->getName(),
        );
    }

    /**
     * Redirect URIs and PKCE do not apply to a client without the authorization code grant, so they are ignored.
     */
    public function test_update_client_ignores_fields_that_do_not_apply(): void {
        $this->resetAfterTest();
        $manager = \core\di::get(client_manager::class);
        $client = $manager->create_client(
            'Service',
            \core\context\system::instance(),
            [client_entity::GRANT_TYPE_CLIENT_CREDENTIALS],
            [],
            '',
            true,
            false,
            [$this->get_scope()],
        );

        $response = $this->request('PATCH', "/oauth2/server/clients/{$client->get_id()}", [
            'redirecturis' => ['https://example.com/cb'],
            'ispkcerequired' => true,
        ]);

        $this->assert_valid_response($response);
        $this->assertSame([], $this->decode_response($response, true)['redirecturis']);
        $this->assertFalse($manager->get_client_by_id($client->get_id())->is_pkce_required());
    }

    /**
     * A listener of before_form_validation can reject the submitted data, which the endpoint returns as a 422.
     */
    public function test_before_form_validation_hook_errors_are_returned(): void {
        $this->resetAfterTest();

        $seen = [];
        $this->redirectHook(\core\hook\form\before_form_validation::class, function ($hook) use (&$seen) {
            $seen[] = $hook->formname;
            $limit = $hook->payload['extensions']['local_example']['limit'] ?? 0;
            if ($limit > 10) {
                $hook->add_error('extensions.local_example.limit', 'Too high');
            }
        });
        $client = $this->create_auth_code_client();

        $create = [
            'name' => 'Service',
            'clienttype' => 'confidential',
            'flows' => ['client_credentials'],
            'scopes' => [$this->get_scope()],
            'extensions' => ['local_example' => ['limit' => 50]],
        ];
        $response = $this->request('POST', '/oauth2/server/clients', $create);
        $this->assert_valid_response($response, 422);
        $this->assertEquals(
            ['Too high'],
            $this->decode_response($response, true)['errors']['extensions.local_example.limit'],
        );

        $response = $this->request('PATCH', "/oauth2/server/clients/{$client->get_id()}", [
            'name' => 'Renamed',
            'extensions' => ['local_example' => ['limit' => 50]],
        ]);
        $this->assert_valid_response($response, 422);
        $this->assertEquals('Existing', \core\di::get(client_manager::class)->get_client_by_id($client->get_id())->getName());

        $this->assertSame(['core_admin_oauth2_client', 'core_admin_oauth2_client'], $seen);
    }

    /**
     * A listener of after_form_saved is told the id of the created or updated client and the submitted data.
     */
    public function test_after_form_saved_hook_receives_the_client_id(): void {
        $this->resetAfterTest();

        $saved = [];
        $this->redirectHook(\core\hook\form\after_form_saved::class, function ($hook) use (&$saved) {
            $saved[] = [$hook->formname, $hook->id, $hook->payload['extensions'] ?? null];
        });

        $response = $this->request('POST', '/oauth2/server/clients', [
            'name' => 'Service',
            'clienttype' => 'confidential',
            'flows' => ['client_credentials'],
            'scopes' => [$this->get_scope()],
            'extensions' => ['local_example' => ['limit' => 5]],
        ]);
        $this->assert_valid_response($response, 201);
        $id = $this->decode_response($response)->id;
        $this->assertSame([['core_admin_oauth2_client', $id, ['local_example' => ['limit' => 5]]]], $saved);

        $saved = [];
        $this->assert_valid_response($this->request('PATCH', "/oauth2/server/clients/{$id}", ['name' => 'Renamed']), 200);
        $this->assertSame([['core_admin_oauth2_client', $id, null]], $saved);
    }
}
