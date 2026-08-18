<?php
namespace core_admin\route\api\oauth2\server;

use core\router\route;
use core\router\schema\response\payload_response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * REST API routes for OAuth2 server clients.
 */
class clients {
    /**
     * Constructor.
     *
     * Executes automatically prior to invoking any API endpoint in this class.
     */
    public function __construct() {

    }

    /**
     * Revoke a client.
     *
     * @param ServerRequestInterface $request The request object
     * @param ResponseInterface $response The response object
     * @param \core\oauth2\server\entity\client_entity $cliententity The client entity
     * @return payload_response The response object with the success status
     */
    #[route(
        path: '/oauth2/server/clients/{client}/revoke',
        method: ['POST'],
        pathtypes: [
            new \core_admin\route\parameters\oauth2\server\path_client(),
        ],
    )]
    public function revoke_client(
        ServerRequestInterface $request,
        ResponseInterface $response,
        \core\oauth2\server\entity\client_entity $cliententity,
    ): payload_response {
        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        $manager = \core\di::get(\core\oauth2\server\client_manager::class);
        $manager->revoke_client($cliententity->get_id());

        return new payload_response(
            payload: [
                'success' => true,
            ],
            request: $request,
            response: $response,
        );
    }

    /**
     * Reactivate a revoked client.
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
