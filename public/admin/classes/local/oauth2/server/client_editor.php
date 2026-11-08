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

namespace core_admin\local\oauth2\server;

use core\oauth2\server\client_manager;
use core\oauth2\server\entity\client_entity;
use core\oauth2\server\repository\scope_repository;
use core\hook\form\after_form_saved;
use core\hook\form\before_form_validation;

/**
 * Validates and saves OAuth 2 client data submitted by the client create and edit forms.
 *
 * This holds the rules that used to live in the create_client_form and edit_client_form validation() methods,
 * so the REST endpoints used by the React forms enforce the same rules.
 *
 * @package    core_admin
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class client_editor {
    /** @var string The name of the client form, as passed to the form hooks. */
    public const string FORM_NAME = 'core_admin_oauth2_client';

    /** @var string The client type value for a confidential client. */
    public const string TYPE_CONFIDENTIAL = 'confidential';

    /** @var string The client type value for a public client. */
    public const string TYPE_PUBLIC = 'public';

    /**
     * Constructor.
     *
     * @param client_manager $clientmanager
     * @param scope_repository $scoperepository
     */
    public function __construct(
        protected readonly client_manager $clientmanager,
        protected readonly scope_repository $scoperepository,
    ) {
    }

    /**
     * Validate the submitted data and create a client.
     *
     * @param array $data The submitted data:
     *      name, description, clienttype, flows[] (auth_code, client_credentials), redirecturis[], ispkcerequired, scopes[].
     * @return client_entity The created client.
     * @throws client_validation_exception If the data is not valid.
     */
    public function create(array $data): client_entity {
        $errors = [];

        $name = $this->validate_name($data['name'] ?? null, $errors);
        $description = $this->clean_text($data['description'] ?? '');
        $scopes = $this->validate_scopes($data['scopes'] ?? null, $errors);

        $clienttype = $data['clienttype'] ?? null;
        if (!in_array($clienttype, [self::TYPE_CONFIDENTIAL, self::TYPE_PUBLIC], true)) {
            $errors['clienttype'][] = get_string('invaliddata', 'error');
        }
        $ispublic = $clienttype === self::TYPE_PUBLIC;

        $flows = is_array($data['flows'] ?? null) ? $data['flows'] : [];
        $hasauthcode = in_array('auth_code', $flows, true);
        $hasclientcreds = in_array('client_credentials', $flows, true);

        // Confidential clients must select at least one primary flow.
        if (!$ispublic && $clienttype !== null && !$hasauthcode && !$hasclientcreds) {
            $errors['flows'][] = get_string('oauth2server_clientmustselectprimaryflow', 'admin');
        }

        // A redirect URI is required for Public clients and for Authorization Code.
        $redirecturis = $this->validate_redirect_uris($data['redirecturis'] ?? [], $ispublic || $hasauthcode, $errors);

        $errors = array_merge_recursive($errors, $this->dispatch_validation($data));

        if ($errors) {
            throw new client_validation_exception($errors);
        }

        // Public clients always use Authorization Code. Client Credentials is not available to them.
        $granttypes = [];
        if ($ispublic || $hasauthcode) {
            $granttypes = [
                client_entity::GRANT_TYPE_AUTHORIZATION_CODE,
                client_entity::GRANT_TYPE_REFRESH_TOKEN,
            ];
        }
        if (!$ispublic && $hasclientcreds) {
            $granttypes[] = client_entity::GRANT_TYPE_CLIENT_CREDENTIALS;
        }

        global $DB;
        $transaction = $DB->start_delegated_transaction();

        $cliententity = $this->clientmanager->create_client(
            $name,
            \core\context\system::instance(),
            $granttypes,
            $ispublic || $hasauthcode ? $redirecturis : [],
            $description,
            !$ispublic,
            $ispublic || !empty($data['ispkcerequired']),
            $scopes,
        );
        \core\di::get(\core\hook\manager::class)->dispatch(
            new after_form_saved(self::FORM_NAME, $cliententity->get_id(), $data),
        );

        $transaction->allow_commit();

        return $cliententity;
    }

    /**
     * Validate the submitted changes and apply them to a client.
     *
     * Only the fields present in $data are changed. Redirect URIs only apply to clients using the Authorization Code
     * grant, and the PKCE setting only to confidential clients using it, so those are ignored for other clients.
     *
     * @param client_entity $cliententity The client to update.
     * @param array $data The changes: name, description, redirecturis[], ispkcerequired, scopes[].
     * @return client_entity The updated client.
     * @throws client_validation_exception If the data is not valid.
     */
    public function update(client_entity $cliententity, array $data): client_entity {
        global $DB;

        $errors = [];
        $updates = [];

        if (array_key_exists('name', $data)) {
            $updates['name'] = $this->validate_name($data['name'], $errors);
        }
        if (array_key_exists('description', $data)) {
            $updates['description'] = $this->clean_text($data['description']);
        }
        if (array_key_exists('scopes', $data)) {
            $updates['scopes'] = $this->validate_scopes($data['scopes'], $errors);
        }

        $hasauthcode = $cliententity->supportsGrantType(client_entity::GRANT_TYPE_AUTHORIZATION_CODE);

        if (
            array_key_exists('ispkcerequired', $data)
            && $cliententity->isConfidential()
            && $hasauthcode
        ) {
            $updates['ispkcerequired'] = empty($data['ispkcerequired']) ? 0 : 1;
        }

        $redirecturis = null;
        if (array_key_exists('redirecturis', $data) && $hasauthcode) {
            $redirecturis = $this->validate_redirect_uris($data['redirecturis'], true, $errors);
        }

        $errors = array_merge_recursive($errors, $this->dispatch_validation($data));

        if ($errors) {
            throw new client_validation_exception($errors);
        }

        $transaction = $DB->start_delegated_transaction();

        $this->clientmanager->update_client($cliententity->get_id(), $updates);

        if ($redirecturis !== null) {
            $existing = $this->clientmanager->get_redirect_uris($cliententity->get_id());

            foreach (array_diff($existing, $redirecturis) as $uri) {
                $this->clientmanager->remove_redirect_uri($cliententity->get_id(), $uri);
            }
            foreach (array_diff($redirecturis, $existing) as $uri) {
                $this->clientmanager->add_redirect_uri($cliententity->get_id(), $uri);
            }
        }

        $updated = $this->clientmanager->get_client_by_id($cliententity->get_id());
        \core\di::get(\core\hook\manager::class)->dispatch(
            new after_form_saved(self::FORM_NAME, $updated->get_id(), $data),
        );

        $transaction->allow_commit();

        return $updated;
    }

    /**
     * Let plugins validate the submitted data.
     *
     * @param array $data The submitted data.
     * @return array<string, string[]> The errors the plugins reported, keyed by field path.
     */
    protected function dispatch_validation(array $data): array {
        $hook = new before_form_validation(self::FORM_NAME, $data);
        \core\di::get(\core\hook\manager::class)->dispatch($hook);
        return $hook->get_errors();
    }

    /**
     * Clean free text the same way the mforms did (PARAM_TEXT).
     *
     * @param mixed $value
     * @return string
     */
    protected function clean_text(mixed $value): string {
        return is_scalar($value) ? clean_param((string) $value, PARAM_TEXT) : '';
    }

    /**
     * Validate the client name, which is required.
     *
     * @param mixed $value
     * @param array $errors The errors, appended to.
     * @return string
     */
    protected function validate_name(mixed $value, array &$errors): string {
        $name = trim($this->clean_text($value));

        if ($name === '') {
            $errors['name'][] = get_string('required');
        }

        return $name;
    }

    /**
     * Validate the selected scopes: at least one, all of them known.
     *
     * @param mixed $value
     * @param array $errors The errors, appended to.
     * @return string[]
     */
    protected function validate_scopes(mixed $value, array &$errors): array {
        $scopes = is_array($value) ? array_values(array_unique(array_filter($value, 'is_string'))) : [];

        if (!$scopes) {
            $errors['scopes'][] = get_string('oauth2serverclientnoscopes', 'error');
            return [];
        }

        $known = array_keys($this->scoperepository->get_all_scopes());
        if (array_diff($scopes, $known)) {
            $errors['scopes'][] = get_string('invaliddata', 'error');
        }

        return $scopes;
    }

    /**
     * Validate the redirect URIs. Blank entries are dropped, as the mforms did.
     *
     * @param mixed $value
     * @param bool $required Whether at least one URI is needed.
     * @param array $errors The errors, appended to. Format errors are keyed by the URI's position in the submitted list.
     * @return string[] The trimmed, non-blank URIs.
     */
    protected function validate_redirect_uris(mixed $value, bool $required, array &$errors): array {
        $submitted = is_array($value) ? $value : [$value];
        $uris = [];

        foreach (array_values($submitted) as $index => $uri) {
            $uri = is_scalar($uri) ? trim((string) $uri) : '';
            if ($uri === '') {
                continue;
            }

            try {
                $this->clientmanager->validate_redirect_uri_format($uri);
                $uris[] = $uri;
            } catch (\moodle_exception) {
                $errors["redirecturis.{$index}"][] = get_string('oauth2server_clientcallbackurisinvalid', 'admin');
            }
        }

        if ($required && !$uris && !preg_grep('/^redirecturis\./', array_keys($errors))) {
            $errors['redirecturis'][] = get_string('oauth2server_clientcallbackurirequired', 'admin');
        }

        return array_values(array_unique($uris));
    }
}
