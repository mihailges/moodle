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
        $composer = \core\di::get(\core\composer::class);
        // The 'league/oauth2-server' composer package needs to be installed to manage OAuth 2 clients.
        if (!$composer->get_package_status('league/oauth2-server')->installed) {
            throw new \coding_exception("Required Composer package 'league/oauth2-server' is missing. " .
                "Please run 'composer install' in the Moodle root directory to install the required dependencies.");
        }
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
}
