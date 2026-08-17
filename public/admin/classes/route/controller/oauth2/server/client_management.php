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

use core\oauth2\server\entity\client_entity;
use core\oauth2\server\form\client as client_form;
use core_admin\reportbuilder\local\systemreports\oauth2_server_clients;
use core_admin\reportbuilder\local\systemreports\oauth2_server_client_secrets;
use Psr\Http\Message\ResponseInterface;

/**
 * Class client_management.
 *
 * @package    core_admin
 * @copyright  2026 Mihail Gehoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\core\router\route(
    title: 'OAuth2 Client Management',
    path: '/oauth2server/clients',
)]
class client_management
{
    /**
     * Constructor.
     *
     * Executes automatically prior to invoking any route method in this class.
     */
    public function __construct()
    {
        $composer = \core\di::get(\core\composer::class);
        // The 'league/oauth2-server' composer package needs to be installed to manage OAuth 2 clients.
        if (!$composer->get_package_status('league/oauth2-server')->installed) {
            throw new \coding_exception("Required Composer package 'league/oauth2-server' is missing. " .
                "Please run 'composer install' in the Moodle root directory to install the required dependencies.");
        }
    }

    /**
     * List the OAuth2 clients.
     *
     * @param ResponseInterface $response The response object
     * @return ResponseInterface The response object with the rendered client list
     */
    #[\core\router\route(
        path: '',
    )]
    public function list_clients(
        ResponseInterface $response,
    ): ResponseInterface {
        global $OUTPUT, $PAGE;

        $this->setup_admin_page();

        has_capability('moodle/site:manageoauth2clients', \context_system::instance());

        $PAGE->requires->js_call_amd('core_admin/oauth2/server/client/actions/client_revoke', 'init');

        $response->getBody()->write($OUTPUT->header());

        // Render the action bar.
        $actionbar = $OUTPUT->render_from_template(
            'admin/oauth2/server/client_management_actionbar',
            ['createclientlink' => \core\router\util::get_path_for_callable([self::class, 'create_client'])],
        );

        $response->getBody()->write($actionbar);

        // Render the OAuth2 server clients table.
        $report = \core_reportbuilder\system_report_factory::create(
            oauth2_server_clients::class,
            \core\context\system::instance(),
        );

        $response->getBody()->write($report->output());
        $response->getBody()->write($OUTPUT->footer());

        return $response;
    }

    #[\core\router\route(
        path: '/create',
        method: ['GET', 'POST'],
    )]
    public function create_client(
        ResponseInterface $response,
    ): ResponseInterface {
        global $OUTPUT, $PAGE;

        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        $this->setup_admin_page(get_string('oauth2server_clientcreate', 'admin'));
        $PAGE->set_pagetype('admin-oauth2server-client-create');

        $mform = new \core_admin\form\oauth2\server\create_client_form();

        // Handle form cancellation.
        if ($mform->is_cancelled()) {
            redirect(\core\router\util::get_path_for_callable([self::class, 'list_clients']));
        }

        // Process the form data.
        if ($data = $mform->get_data()) {
            // Sanitize the redirect URIs by trimming whitespace and removing empty entries.
            $redirecturis = array_values(array_filter(array_map('trim', $data->redirecturi ?? [])));

            $ispublicclient = (int) $data->clienttype === client_entity::TYPE_PUBLIC;

            // Set the grant types based on the selected Primary flows.
            $granttypes = [];
            // If Authorization Code flow is selected or the client type is Public, add both Authorization Code and
            // Refresh Token grant types.
            if ($ispublicclient || !empty($data->flow_auth_code)) {
                $granttypes = ['authorization_code', 'refresh_token'];
            }
            // If Client Credentials flow is selected, add the Client Credentials grant type.
            if (!empty($data->flow_client_credentials)) {
                $granttypes[] = 'client_credentials';
            }

            $clientmanager = \core\di::get(\core\oauth2\server\client_manager::class);
            $cliententity = $clientmanager->create_client(
                $data->name,
                \core\context\system::instance(),
                $granttypes,
                $redirecturis,
                $data->description,
                (int) $data->clienttype === client_entity::TYPE_CONFIDENTIAL,
                $ispublicclient || !empty($data->enablepkce),
            );

            \core\notification::success(
                get_string('oauth2server_clientcreated', 'admin', $cliententity->getName())
            );

            redirect(\core\router\util::get_path_for_callable([self::class, 'list_clients']));
        }

        $response->getBody()->write($OUTPUT->header());
        $response->getBody()->write($OUTPUT->heading(get_string('oauth2server_clientcreate', 'admin')));
        $response->getBody()->write($mform->render());
        $response->getBody()->write($OUTPUT->footer());

        return $response;
    }

    #[\core\router\route(
        path: '/{client}/edit',
        pathtypes: [
            new \core_admin\route\parameters\oauth2\server\path_client(),
        ],
        method: ['GET', 'POST'],
    )]
    public function edit_client(
        ResponseInterface $response,
        \core\oauth2\server\entity\client_entity $cliententity,
    ): ResponseInterface {
        global $OUTPUT, $PAGE;

        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        $this->setup_admin_page(get_string('oauth2server_clientcreate', 'admin'));
        $PAGE->set_pagetype('admin-oauth2server-client-edit');

        $mform = new \core_admin\form\oauth2\server\edit_client_form(null, ['cliententity' => $cliententity]);

        // Process the form data.
        if ($data = $mform->get_data()) {
            $clientmanager = \core\di::get(\core\oauth2\server\client_manager::class);
            $clientmanager->update_client(
                $cliententity->get_id(),
                [
                    'name' => $data->name,
                    'description' => $data->description,
                ],
            );

            // Sanitize the redirect URIs by trimming whitespace and removing empty entries.
            $redirecturis = array_values(array_filter(array_map('trim', $data->redirecturi ?? [])));
            // Fetch the current records from the database.
            $existingredirecturis = $clientmanager->get_redirect_uris($cliententity->get_id());

            // Find URIs that are in db, but missing from form and delete them.
            $redirecturistodelete = array_diff($existingredirecturis, $redirecturis);
            foreach ($redirecturistodelete as $redirecturi) {
                $clientmanager->remove_redirect_uri($cliententity->get_id(), $redirecturi);
            }

            // Find URIs that are in the form, but missing from the database and add them.
            $redirecturistoadd = array_diff($redirecturis, $existingredirecturis);
            foreach ($redirecturistoadd as $redirecturi) {
                $clientmanager->add_redirect_uri($cliententity->get_id(), $redirecturi);
            }
        }

        $actionbar = new \core_admin\output\oauth2\server\edit_client_action_bar(
            $cliententity,
            \core\router\util::get_path_for_callable([self::class, __FUNCTION__], ['client' => $cliententity->get_id()]),
        );
        $actionbarhtml = $OUTPUT->render_from_template(
            $actionbar->get_template(),
            $actionbar->export_for_template($OUTPUT),
        );

        $response->getBody()->write($OUTPUT->header());
        $response->getBody()->write($actionbarhtml);
        $response->getBody()->write($mform->render());

        $response->getBody()->write($OUTPUT->footer());

        return $response;
    }

    #[\core\router\route(
        path: '/{client}/secrets',
        pathtypes: [
            new \core_admin\route\parameters\oauth2\server\path_client(),
        ],
        method: ['GET'],
    )]
    public function client_secrets(
        ResponseInterface $response,
        \core\oauth2\server\entity\client_entity $cliententity,
    ): ResponseInterface {
        global $OUTPUT, $PAGE;

        require_capability('moodle/site:manageoauth2clients', \core\context\system::instance());

        $this->setup_admin_page(get_string('oauth2server_clientcreate', 'admin'));
        $PAGE->set_pagetype('admin-oauth2server-client-edit');

        $response->getBody()->write($OUTPUT->header());

        $clientmanager = \core\di::get(\core\oauth2\server\client_manager::class);
        $clientsecrets = $clientmanager->get_secrets($cliententity->get_id());
        $cancreatesecret = count($clientsecrets) < $clientmanager::MAX_ACTIVE_SECRETS;

        $actionbar = new \core_admin\output\oauth2\server\client_secrets_action_bar(
            $cliententity,
            \core\router\util::get_path_for_callable([self::class, __FUNCTION__], ['client' => $cliententity->get_id()]),
            $cancreatesecret,
        );
        $actionbarhtml = $OUTPUT->render_from_template(
            $actionbar->get_template(),
            $actionbar->export_for_template($OUTPUT),
        );

        $response->getBody()->write($actionbarhtml);

        $PAGE->requires->js_call_amd(
            'core_admin/oauth2/server/client/client_secrets',
            'init',
            [$clientmanager::MAX_ACTIVE_SECRETS]
        );

        // Render the OAuth2 client secrets table.
        $report = \core_reportbuilder\system_report_factory::create(
            oauth2_server_client_secrets::class,
            \core\context\system::instance(),
            parameters: [
                'clientidentifier' => $cliententity->getIdentifier(),
            ]
        );
        $response->getBody()->write($report->output());

        $secreatlimitreachedalert = $OUTPUT->render_from_template(
            'core_admin/oauth2/server/client_secrets_limit_reached_alert',
            ['maxsecretsnumber' => $clientmanager::MAX_ACTIVE_SECRETS],
        );

        $alertcontainer = \html_writer::div(
            !$cancreatesecret ? $secreatlimitreachedalert : '',
            'mt-5',
            ['id' => 'client-secrets-alert-container'],
        );

        // Render the alert container.
        $response->getBody()->write($alertcontainer);

        $response->getBody()->write($OUTPUT->footer());

        return $response;
    }

    /**
     * Helper method to set up the admin page.
     *
     * @param string|null $title The title of the page. If not set, defaults to 'OAuth 2 clients'
     *                           ('oauth2server_clients', 'admin').
     */
    private function setup_admin_page(?string $title = null): void
    {
        global $CFG, $PAGE;

        require_once("{$CFG->libdir}/adminlib.php");

        admin_externalpage_setup('oauth2serverclients');
        $PAGE->set_url(new \moodle_url('/admin/oauth2server/clients'));
        $PAGE->set_context(\core\context\system::instance());
        $PAGE->set_pagelayout('admin');
        if ($title !== null) {
            $PAGE->set_title($title);
        } else {
            $PAGE->set_title(get_string('oauth2server_clients', 'admin'));
        }
    }
}
