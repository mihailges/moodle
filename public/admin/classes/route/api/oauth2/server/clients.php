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

use core\param;
use core\router\require_login;
use core\router\route;
use core\router\schema\objects\array_of_strings;
use core\router\schema\objects\array_of_things;
use core\router\schema\objects\scalar_type;
use core\router\schema\objects\schema_object;
use core\router\schema\request_body;
use core\router\schema\response\content\payload_response_type;
use core\router\schema\response\payload_response;
use core\router\schema\response\response;
use core\router\scope\scopeset;
use core_admin\local\oauth2\server\client_editor;
use core_admin\local\oauth2\server\client_validation_exception;
use GuzzleHttp\Psr7\Response as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * REST API routes for OAuth2 server clients.
 *
 * @package    core_admin
 * @copyright  2026 Mihail Gehoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class clients {
    /**
     * Create a client.
     *
     * @param ServerRequestInterface $request The request object
     * @param ResponseInterface $response The response object
     * @return payload_response The created client, or the validation errors
     */
    #[route(
        path: '/oauth2/server/clients',
        method: ['POST'],
        title: 'Create an OAuth 2 client',
        description: 'Create an OAuth 2 client. Public clients always use the authorization code flow.',
        requestbody: new request_body(
            required: true,
            content: new payload_response_type(
                schema: new schema_object(
                    content: [
                        'name' => new scalar_type(param::RAW, required: true),
                        'description' => new scalar_type(param::RAW),
                        'clienttype' => new scalar_type(param::ALPHA, required: true),
                        'flows' => new array_of_strings(keyparamtype: param::INT, valueparamtype: param::ALPHANUMEXT),
                        'redirecturis' => new array_of_strings(keyparamtype: param::INT, valueparamtype: param::RAW),
                        'ispkcerequired' => new scalar_type(param::BOOL),
                        'scopes' => new array_of_strings(keyparamtype: param::INT, valueparamtype: param::RAW),
                        // Data of plugin form extensions, by component. Validated by the plugins' hook callbacks.
                        'extensions' => new array_of_things(),
                    ],
                ),
            ),
        ),
        responses: [
            new response(statuscode: 201, description: 'The client was created'),
            new response(statuscode: 422, description: 'The submitted data is not valid'),
        ],
        requirelogin: new require_login(
            requirelogin: true,
            autologinguest: false,
        ),
    )]
    #[scopeset(
        new \core_admin\route\scope\config\read(),
        new \core_admin\route\scope\config\write(),
    )]
    public function create_client(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): payload_response {
        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        $editor = \core\di::get(client_editor::class);

        try {
            $cliententity = $editor->create($request->getParsedBody() ?? []);
        } catch (client_validation_exception $e) {
            return $this->get_validation_error_response($e, $request);
        }

        // Confidential clients continue to their secrets, public clients to the list.
        $nextroute = $cliententity->isConfidential() ? 'manage_client_secrets' : 'list_clients';
        $nexturl = \core\router\util::get_path_for_callable(
            [\core_admin\route\controller\oauth2\server\client_management::class, $nextroute],
            $cliententity->isConfidential() ? ['client' => $cliententity->get_id()] : [],
        );

        return new payload_response(
            payload: [
                'id' => $cliententity->get_id(),
                'isconfidential' => $cliententity->isConfidential(),
                'redirecturl' => $nexturl->out(false),
            ],
            request: $request,
            response: new HttpResponse(201),
        );
    }

    /**
     * Get a client. The values are the ones the edit form sends back.
     *
     * @param ServerRequestInterface $request The request object
     * @param ResponseInterface $response The response object
     * @param \core\oauth2\server\entity\client_entity $cliententity The client entity
     * @return payload_response The client
     */
    #[route(
        path: '/oauth2/server/clients/{client}',
        method: ['GET'],
        title: 'Get an OAuth 2 client',
        description: 'Get the editable fields of an OAuth 2 client.',
        pathtypes: [
            new \core_admin\route\parameters\oauth2\server\path_client(),
        ],
        responses: [
            new response(statuscode: 200, description: 'The client'),
        ],
        requirelogin: new require_login(
            requirelogin: true,
            autologinguest: false,
        ),
    )]
    #[scopeset(
        new \core_admin\route\scope\config\read(),
    )]
    public function get_client(
        ServerRequestInterface $request,
        ResponseInterface $response,
        \core\oauth2\server\entity\client_entity $cliententity,
    ): payload_response {
        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        return new payload_response(
            payload: $this->get_client_payload($cliententity),
            request: $request,
            response: $response,
        );
    }

    /**
     * The editable fields of a client, with the values plugins add to the form.
     *
     * @param \core\oauth2\server\entity\client_entity $cliententity The client entity
     * @return array
     */
    protected function get_client_payload(\core\oauth2\server\entity\client_entity $cliententity): array {
        $clientmanager = \core\di::get(\core\oauth2\server\client_manager::class);

        $hook = new \core_admin\hook\oauth2_client_form_extend($cliententity);
        \core\di::get(\core\hook\manager::class)->dispatch($hook);

        return [
            'id' => $cliententity->get_id(),
            'name' => $cliententity->getName(),
            'description' => (string) $cliententity->get_description(),
            'ispkcerequired' => $cliententity->is_pkce_required(),
            'scopes' => array_values($cliententity->get_scopes()),
            'redirecturis' => array_values($clientmanager->get_redirect_uris($cliententity->get_id())),
            'extensions' => (object) $hook->get_values(),
        ];
    }

    /**
     * Update a client. Only the fields in the request body are changed.
     *
     * @param ServerRequestInterface $request The request object
     * @param ResponseInterface $response The response object
     * @param \core\oauth2\server\entity\client_entity $cliententity The client entity
     * @return payload_response The updated client, or the validation errors
     */
    #[route(
        path: '/oauth2/server/clients/{client}',
        method: ['PATCH'],
        title: 'Update an OAuth 2 client',
        description: 'Update the given fields of an OAuth 2 client. Client type and flows cannot be changed.',
        pathtypes: [
            new \core_admin\route\parameters\oauth2\server\path_client(),
        ],
        requestbody: new request_body(
            required: true,
            content: new payload_response_type(
                schema: new schema_object(
                    content: [
                        'name' => new scalar_type(param::RAW),
                        'description' => new scalar_type(param::RAW),
                        'redirecturis' => new array_of_strings(keyparamtype: param::INT, valueparamtype: param::RAW),
                        'ispkcerequired' => new scalar_type(param::BOOL),
                        'scopes' => new array_of_strings(keyparamtype: param::INT, valueparamtype: param::RAW),
                        // Data of plugin form extensions, by component. Validated by the plugins' hook callbacks.
                        'extensions' => new array_of_things(),
                    ],
                ),
            ),
        ),
        responses: [
            new response(statuscode: 200, description: 'The client was updated'),
            new response(statuscode: 422, description: 'The submitted data is not valid'),
        ],
        requirelogin: new require_login(
            requirelogin: true,
            autologinguest: false,
        ),
    )]
    #[scopeset(
        new \core_admin\route\scope\config\read(),
        new \core_admin\route\scope\config\write(),
    )]
    public function update_client(
        ServerRequestInterface $request,
        ResponseInterface $response,
        \core\oauth2\server\entity\client_entity $cliententity,
    ): payload_response {
        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        $editor = \core\di::get(client_editor::class);

        try {
            $cliententity = $editor->update($cliententity, $request->getParsedBody() ?? []);
        } catch (client_validation_exception $e) {
            return $this->get_validation_error_response($e, $request);
        }

        return new payload_response(
            payload: $this->get_client_payload($cliententity),
            request: $request,
            response: $response,
        );
    }

    /**
     * Build the 422 response for invalid data, with the messages keyed by field path.
     *
     * @param client_validation_exception $exception
     * @param ServerRequestInterface $request
     * @return payload_response
     */
    protected function get_validation_error_response(
        client_validation_exception $exception,
        ServerRequestInterface $request,
    ): payload_response {
        return new payload_response(
            payload: [
                'message' => $exception->getMessage(),
                'errors' => $exception->errors,
            ],
            request: $request,
            response: new HttpResponse(422),
        );
    }

    /**
     * Disable a client.
     *
     * @param ServerRequestInterface $request The request object
     * @param ResponseInterface $response The response object
     * @param \core\oauth2\server\entity\client_entity $cliententity The client entity
     * @return payload_response The response object with the success status
     */
    #[route(
        path: '/oauth2/server/clients/{client}/disable',
        method: ['POST'],
        pathtypes: [
            new \core_admin\route\parameters\oauth2\server\path_client(),
        ],
        requirelogin: new require_login(
            requirelogin: true,
            autologinguest: false,
        ),
    )]
    #[scopeset(
        new \core_admin\route\scope\config\read(),
        new \core_admin\route\scope\config\write(),
    )]
    public function disable_client(
        ServerRequestInterface $request,
        ResponseInterface $response,
        \core\oauth2\server\entity\client_entity $cliententity,
    ): payload_response {
        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        $manager = \core\di::get(\core\oauth2\server\client_manager::class);
        $manager->disable_client($cliententity->get_id());

        return new payload_response(
            payload: [
                'success' => true,
            ],
            request: $request,
            response: $response,
        );
    }

    /**
     * Reactivate a disabled client.
     *
     * @param ServerRequestInterface $request The request object
     * @param ResponseInterface $response The response object
     * @param \core\oauth2\server\entity\client_entity $cliententity The client entity
     * @return payload_response The response payload indicating success
     */
    #[route(
        path: '/oauth2/server/clients/{client}/reactivate',
        method: ['POST'],
        pathtypes: [
            new \core_admin\route\parameters\oauth2\server\path_client(),
        ],
        requirelogin: new require_login(
            requirelogin: true,
            autologinguest: false,
        ),
    )]
    #[scopeset(
        new \core_admin\route\scope\config\read(),
        new \core_admin\route\scope\config\write(),
    )]
    public function reactivate_client(
        ServerRequestInterface $request,
        ResponseInterface $response,
        \core\oauth2\server\entity\client_entity $cliententity,
    ): payload_response {
        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        $manager = \core\di::get(\core\oauth2\server\client_manager::class);
        $manager->reactivate_client($cliententity->get_id());

        return new payload_response(
            payload: [
                'success' => true,
            ],
            request: $request,
            response: $response,
        );
    }

    /**
     * Delete a client.
     *
     * @param ServerRequestInterface $request The request object
     * @param ResponseInterface $response The response object
     * @param \core\oauth2\server\entity\client_entity $cliententity The client entity
     * @return payload_response The response payload indicating success
     */
    #[route(
        path: '/oauth2/server/clients/{client}/delete',
        method: ['POST'],
        pathtypes: [
            new \core_admin\route\parameters\oauth2\server\path_client(),
        ],
        requirelogin: new require_login(
            requirelogin: true,
            autologinguest: false,
        ),
    )]
    #[scopeset(
        new \core_admin\route\scope\config\read(),
        new \core_admin\route\scope\config\write(),
    )]
    public function delete_client(
        ServerRequestInterface $request,
        ResponseInterface $response,
        \core\oauth2\server\entity\client_entity $cliententity,
    ): payload_response {
        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        $manager = \core\di::get(\core\oauth2\server\client_manager::class);
        $manager->delete_client($cliententity->get_id());

        return new payload_response(
            payload: [
                'success' => true,
            ],
            request: $request,
            response: $response,
        );
    }
}
