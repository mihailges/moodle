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

namespace core_admin\route\controller\oauth2\server;

use core\router\require_login;
use core\oauth2\server\entity\client_entity;
use core_admin\reportbuilder\local\systemreports\oauth2_server_client_secrets;
use core_admin\reportbuilder\local\systemreports\oauth2_server_clients;
use Psr\Http\Message\ResponseInterface;

/**
 * Class client_management.
 *
 * @package    core_admin
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\core\router\route(
    title: 'OAuth2 Client Management',
    path: '/oauth2/server/clients',
)]
class client_management {
    /**
     * List the OAuth2 clients.
     *
     * @param ResponseInterface $response The response object
     * @return ResponseInterface The response object with the rendered client list
     */
    #[\core\router\route(
        path: '',
        requirelogin: new require_login(
            requirelogin: true,
            autologinguest: false,
        ),
    )]
    public function list_clients(
        ResponseInterface $response,
    ): ResponseInterface {
        global $OUTPUT, $PAGE;

        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        $this->setup_admin_page(null, \core\router\util::get_path_for_callable([self::class, 'list_clients']));

        $PAGE->requires->js_call_amd('core_admin/oauth2/server/client/actions/client_disable', 'init');
        $PAGE->requires->js_call_amd('core_admin/oauth2/server/client/actions/client_reactivate', 'init');
        $PAGE->requires->js_call_amd('core_admin/oauth2/server/client/actions/client_delete', 'init');

        $response->getBody()->write($OUTPUT->header());
        $response->getBody()->write(
            $OUTPUT->heading(
                get_string('oauth2server_clients', 'admin'),
                2,
                'fw-bold fs-3',
            ),
        );

        // Render the OAuth2 server clients table.
        $report = \core_reportbuilder\system_report_factory::create(
            oauth2_server_clients::class,
            \core\context\system::instance(),
        );

        $response->getBody()->write($report->output());
        $response->getBody()->write($OUTPUT->footer());

        return $response;
    }

    /**
     * Create client route.
     *
     * @param ResponseInterface $response The response object
     * @return ResponseInterface The response object
     */
    #[\core\router\route(
        path: '/create',
        method: ['GET'],
        requirelogin: new require_login(
            requirelogin: true,
            autologinguest: false,
        ),
    )]
    public function create_client(
        ResponseInterface $response,
    ): ResponseInterface {
        global $OUTPUT, $PAGE;

        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        $this->setup_admin_page(
            get_string('oauth2server_clientcreate', 'admin'),
            \core\router\util::get_path_for_callable([self::class, 'create_client']),
        );

        $PAGE->set_pagetype('admin-oauth2server-client-create');

        $response->getBody()->write($OUTPUT->header());
        $response->getBody()->write(
            $OUTPUT->heading(
                get_string('oauth2server_clientcreate', 'admin'),
                2,
                'fw-bold fs-3 mb-5',
            ),
        );
        // The form submits to the create client API endpoint.
        $response->getBody()->write(\html_writer::react_component('@moodle/lms/core_admin/oauth2/clientForm', [
            'mode' => 'create',
            'scopes' => $this->get_scope_options(),
            'listUrl' => \core\router\util::get_path_for_callable([self::class, 'list_clients'])->out(false),
        ]));
        $response->getBody()->write($OUTPUT->footer());

        return $response;
    }

    /**
     * Edit client route.
     *
     * @param ResponseInterface $response The response object
     * @param \core\oauth2\server\entity\client_entity $cliententity The client entity
     * @return ResponseInterface The response object
     */
    #[\core\router\route(
        path: '/{client}/edit',
        pathtypes: [
            new \core_admin\route\parameters\oauth2\server\path_client(),
        ],
        method: ['GET'],
        requirelogin: new require_login(
            requirelogin: true,
            autologinguest: false,
        ),
    )]
    public function edit_client(
        ResponseInterface $response,
        \core\oauth2\server\entity\client_entity $cliententity,
    ): ResponseInterface {
        global $OUTPUT, $PAGE;

        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        $this->setup_admin_page(
            get_string('oauth2server_clientedit', 'admin'),
            \core\router\util::get_path_for_callable([self::class, 'edit_client'], ['client' => $cliententity->get_id()]),
        );

        $PAGE->set_pagetype('admin-oauth2server-client-edit');

        $clientmanager = \core\di::get(\core\oauth2\server\client_manager::class);

        $response->getBody()->write($OUTPUT->header());

        $isclientactive = $cliententity->get_status() === client_entity::STATUS_ACTIVE;

        $templatedata = [
            'title' => $cliententity->getName(),
            'clientidentifier' => $cliententity->getIdentifier(),
            'isactive' => $isclientactive,
            'isconfidential' => $cliententity->isConfidential(),
            'isauthcodesupported' => in_array(
                client_entity::GRANT_TYPE_AUTHORIZATION_CODE,
                $cliententity->get_grant_types(),
                true,
            ),
            'isclientcredentialssupported' => in_array(
                client_entity::GRANT_TYPE_CLIENT_CREDENTIALS,
                $cliententity->get_grant_types(),
                true,
            ),
            'backurl' => \core\router\util::get_path_for_callable([self::class, 'list_clients'])->out(),
            'editclientform' => \html_writer::react_component('@moodle/lms/core_admin/oauth2/clientForm', [
                'mode' => 'update',
                'clientId' => $cliententity->get_id(),
                'scopes' => $this->get_scope_options(),
                'listUrl' => \core\router\util::get_path_for_callable([self::class, 'list_clients'])->out(false),
            ]),
        ];

        if ($cliententity->isConfidential()) {
            $clientactivesecrets = $clientmanager->get_secrets($cliententity->get_id());
            $templatedata['activesecretscount'] = $isclientactive ? count($clientactivesecrets) : 0;
            $templatedata['managesecretsurl'] = \core\router\util::get_path_for_callable(
                [self::class, 'manage_client_secrets'],
                ['client' => $cliententity->get_id()],
            )->out();
        }

        $editclienthtml = $OUTPUT->render_from_template(
            'core_admin/oauth2/server/edit_client',
            $templatedata,
        );

        // Render the page content.
        $response->getBody()->write($editclienthtml);
        $response->getBody()->write($OUTPUT->footer());

        return $response;
    }

    /**
     * Manage client secrets route.
     *
     * @param ResponseInterface $response The response object
     * @param \core\oauth2\server\entity\client_entity $cliententity The client entity
     * @return ResponseInterface The response object
     */
    #[\core\router\route(
        path: '/{client}/secrets',
        pathtypes: [
            new \core_admin\route\parameters\oauth2\server\path_client(),
        ],
        method: ['GET'],
        requirelogin: new require_login(
            requirelogin: true,
            autologinguest: false,
        ),
    )]
    public function manage_client_secrets(
        ResponseInterface $response,
        \core\oauth2\server\entity\client_entity $cliententity,
    ): ResponseInterface {
        global $OUTPUT, $PAGE;

        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        if (!$cliententity->isConfidential()) {
            throw new \moodle_exception('oauth2server_secretsnotavailablepublicclient', 'admin');
        }

        $this->setup_admin_page(
            get_string('oauth2server_managesecrets', 'admin'),
            \core\router\util::get_path_for_callable(
                [self::class, 'manage_client_secrets'],
                ['client' => $cliententity->get_id()],
            ),
        );

        $response->getBody()->write($OUTPUT->header());

        $clientmanager = \core\di::get(\core\oauth2\server\client_manager::class);
        $isclientactive = $cliententity->get_status() === client_entity::STATUS_ACTIVE;
        $clientactivesecrets = $clientmanager->get_secrets($cliententity->get_id());
        // Secrets can be created if the total number of currently active secrets is not exceeding the defined limit.
        $cancreatesecret = count($clientactivesecrets) < $clientmanager::MAX_ACTIVE_SECRETS;

        // Generate the OAuth2 client secrets table.
        $report = \core_reportbuilder\system_report_factory::create(
            oauth2_server_client_secrets::class,
            \core\context\system::instance(),
            parameters: [
                'clientidentifier' => $cliententity->getIdentifier(),
            ]
        );

        $managesecretshtml = $OUTPUT->render_from_template(
            'core_admin/oauth2/server/manage_client_secrets',
            [
                'id' => $cliententity->get_id(),
                'title' => $cliententity->getName(),
                'clientidentifier' => $cliententity->getIdentifier(),
                'isclientactive' => $isclientactive,
                'backurl' => \core\router\util::get_path_for_callable([self::class, 'list_clients'])->out(),
                'clientsecretstable' => $report->output(),
                'cancreatesecret' => $cancreatesecret,
                'maxsecretsnumber' => $clientmanager::MAX_ACTIVE_SECRETS,
            ],
        );

        // Render the page content.
        $response->getBody()->write($managesecretshtml);

        $PAGE->requires->js_call_amd(
            'core_admin/oauth2/server/client/client_secrets',
            'init',
            [
                $clientmanager::MAX_ACTIVE_SECRETS,
                $isclientactive,
            ],
        );

        $PAGE->requires->js_call_amd('core_admin/oauth2/server/client/actions/client_secret_revoke', 'init');

        $response->getBody()->write($OUTPUT->footer());

        return $response;
    }

    /**
     * Helper method to set up the admin page.
     *
     * @param string|null $title The title of the page. If not set, defaults to 'OAuth 2 clients'
     *                           ('oauth2server_clients', 'admin').
     * @param \moodle_url|null $url The URL of the page.
     */
    private function setup_admin_page(?string $title = null, ?\moodle_url $url = null): void {
        global $CFG, $PAGE;

        require_once("{$CFG->libdir}/adminlib.php");

        admin_externalpage_setup('oauth2serverclients', '', null, $url ?? '');
        $PAGE->set_context(\core\context\system::instance());
        $PAGE->set_pagelayout('admin');
        if ($title !== null) {
            $PAGE->set_title($title);
        } else {
            $PAGE->set_title(get_string('oauth2server_clients', 'admin'));
        }
    }

    /**
     * The scopes a client can be granted, as options for the React forms.
     *
     * @return array[] List of value, label and description.
     */
    private function get_scope_options(): array {
        $options = [];
        foreach (\core\di::get(\core\oauth2\server\repository\scope_repository::class)->get_all_scopes() as $identifier => $scope) {
            $options[] = [
                'value' => $identifier,
                'label' => $scope::get_summary() . ' - ' . $identifier,
                'description' => $scope::get_description(),
            ];
        }
        return $options;
    }
}
