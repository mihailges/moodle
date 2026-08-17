<?php
namespace core_admin\route\api\oauth2\server;

use core\exception\moodle_exception;
use core\param;
use core\router\route;
use core\router\schema\parameters\path_parameter;
use core\router\schema\response\payload_response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * REST API routes for OAuth2 secrets.
 */
class client_secrets {
    /**
     * Create a new client secret.
     *
     */
    #[route(
        path: '/oauth2/server/clients/{client}/secrets/create',
        method: ['POST'],
        pathtypes: [
            new \core_admin\route\parameters\oauth2\server\path_client(),
        ],
    )]
    public function create_secret(
        ServerRequestInterface $request,
        ResponseInterface $response,
        \core\oauth2\server\entity\client_entity $cliententity,
    ): payload_response {

        $context = \core\context\system::instance();
        // require_capability('moodle/site:config', $context);

        $manager = \core\di::get(\core\oauth2\server\client_manager::class);
        $secret = $manager->create_secret($cliententity->get_id());

        // 4. Return JSON response payload
        return new payload_response(
            payload: [
                'secret' => $secret,
            ],
            request: $request,
            response: $response,
        );
    }

    /**
     * Get client secrets.
     *
     */
    #[route(
        path: '/oauth2/server/clients/{client}/secrets',
        method: ['GET'],
        pathtypes: [
            new \core_admin\route\parameters\oauth2\server\path_client(),
        ],
        queryparams: [
            new \core\router\schema\parameters\query_parameter(
                name: 'includeinactive',
                type: \core\param::BOOL,
                description: 'Whether to include inactive secrets',
                default: false,
            ),
        ]
    )]
    public function get_client_secrets(
        ServerRequestInterface $request,
        ResponseInterface $response,
        \core\oauth2\server\entity\client_entity $cliententity,
    ): payload_response {

        $context = \core\context\system::instance();
        // require_capability('moodle/site:config', $context);

        $manager = \core\di::get(\core\oauth2\server\client_manager::class);
        $secrets = $manager->get_secrets($cliententity->get_id(), $request->getQueryParams()['includeinactive'] ?? false);

        // 4. Return JSON response payload
        return new payload_response(
            payload: [
                'secrets' => $secrets,
            ],
            request: $request,
            response: $response,
        );
    }
}