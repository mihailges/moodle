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

namespace core\route\api;

use core\hook\form\after_form_loaded;
use core\hook\form\after_form_saved;
use core\hook\form\before_form_validation;
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
use GuzzleHttp\Psr7\Response as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Dummy endpoint of the React forms test page (/test.php), a form that is both loaded and saved at one URL.
 * It validates the data, keeps it in a config setting, and lets plugins validate, save and provide their
 * extension data through the form hooks.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_form {
    /** @var string The name of the form, as passed to the form hooks. */
    public const string FORM_NAME = 'core_test_form';

    /** @var string The config setting where the form's own data is kept, as it is a single record. */
    private const CONFIG_NAME = 'testformdata';

    /**
     * Load the test form.
     *
     * @param ServerRequestInterface $request The request object
     * @param ResponseInterface $response The response object
     * @return payload_response The values plugins provide for their extension fields
     */
    #[route(
        path: '/test/form',
        method: ['GET'],
        title: 'Load the React forms test form',
        description: 'Returns the saved record of the React forms test page, with the values plugins provide for their extensions.',
        responses: [
            new response(statuscode: 200, description: 'The form record'),
        ],
        requirelogin: new require_login(
            requirelogin: true,
            autologinguest: false,
        ),
    )]
    #[\core\router\scope\scopeset(
        new \core_admin\route\scope\config\read(),
    )]
    public function load(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): payload_response {
        require_capability('moodle/site:config', \core\context\system::instance());

        $hook = new after_form_loaded(self::FORM_NAME);
        \core\di::get(\core\hook\manager::class)->dispatch($hook);

        $saved = json_decode(get_config('core', self::CONFIG_NAME) ?: '', true);

        return new payload_response(
            payload: (is_array($saved) ? $saved : []) + ['extensions' => (object) $hook->get_values()],
            request: $request,
            response: $response,
        );
    }

    /**
     * Save the test form.
     *
     * @param ServerRequestInterface $request The request object
     * @param ResponseInterface $response The response object
     * @return payload_response The saved data, or the validation errors
     */
    #[route(
        path: '/test/form',
        method: ['POST'],
        title: 'Save the React forms test form',
        description: 'A dummy endpoint for the React forms test page. It keeps the record in a config setting and lets plugins extend the form.',
        requestbody: new request_body(
            required: true,
            content: new payload_response_type(
                schema: new schema_object(
                    content: [
                        'title' => new scalar_type(param::RAW, required: true),
                        'description' => new scalar_type(param::RAW),
                        'priority' => new scalar_type(param::ALPHA),
                        'notify' => new scalar_type(param::BOOL),
                        'notifyEmail' => new scalar_type(param::RAW),
                        'tags' => new array_of_strings(keyparamtype: param::INT, valueparamtype: param::ALPHANUMEXT),
                        'emails' => new array_of_strings(keyparamtype: param::INT, valueparamtype: param::RAW),
                        'rating' => new scalar_type(param::INT),
                        'color' => new scalar_type(param::RAW),
                        'internalNote' => new scalar_type(param::RAW),
                        'externalId' => new scalar_type(param::RAW),
                        'slug' => new scalar_type(param::RAW),
                        'website' => new scalar_type(param::RAW),
                        // Data of plugin form extensions, by component. Validated by the plugins' hook callbacks.
                        'extensions' => new array_of_things(),
                    ],
                ),
            ),
        ),
        responses: [
            new response(statuscode: 201, description: 'The form was saved'),
            new response(statuscode: 422, description: 'The submitted data is not valid'),
        ],
        requirelogin: new require_login(
            requirelogin: true,
            autologinguest: false,
        ),
    )]
    #[\core\router\scope\scopeset(
        new \core_admin\route\scope\config\read(),
        new \core_admin\route\scope\config\write(),
    )]
    public function save(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): payload_response {
        require_capability('moodle/site:config', \core\context\system::instance());

        $data = $request->getParsedBody() ?? [];
        $errors = $this->validate($data);

        $hook = new before_form_validation(self::FORM_NAME, $data);
        \core\di::get(\core\hook\manager::class)->dispatch($hook);
        $errors = array_merge_recursive($errors, $hook->get_errors());

        if ($errors) {
            return new payload_response(
                payload: ['message' => 'The submitted data is not valid', 'errors' => $errors],
                request: $request,
                response: new HttpResponse(422),
            );
        }

        // The form is a single record, kept in a config setting. Plugins save their own data in the hook.
        $own = $data;
        unset($own['extensions']);
        set_config(self::CONFIG_NAME, json_encode($own));
        $id = time();
        \core\di::get(\core\hook\manager::class)->dispatch(new after_form_saved(self::FORM_NAME, $id, $data));

        return new payload_response(
            payload: ['id' => $id] + $data,
            request: $request,
            response: new HttpResponse(201),
        );
    }

    /**
     * Validate the fields of the form itself.
     *
     * @param array $data The submitted data.
     * @return array<string, string[]> The errors, keyed by field path.
     */
    protected function validate(array $data): array {
        $errors = [];

        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            $errors['title'][] = 'The title is required';
        } else if (\core_text::strlen($title) > 50) {
            $errors['title'][] = 'The title must be at most 50 characters long';
        }

        if (!empty($data['notify']) && !validate_email((string) ($data['notifyEmail'] ?? ''))) {
            $errors['notifyEmail'][] = 'Enter a valid email to notify';
        }

        $rating = (int) ($data['rating'] ?? 0);
        if ($rating < 1 || $rating > 5) {
            $errors['rating'][] = 'The rating must be between 1 and 5';
        }

        if (!preg_match('/^#[0-9a-f]{6}$/i', (string) ($data['color'] ?? ''))) {
            $errors['color'][] = 'The colour must be a hex value like #3366ff';
        }

        foreach (['externalId', 'slug'] as $field) {
            if (!preg_match('/^[a-z0-9-]+$/', (string) ($data[$field] ?? ''))) {
                $errors[$field][] = 'Use lowercase letters, numbers and dashes';
            }
        }

        foreach (array_values($data['emails'] ?? []) as $i => $email) {
            if ($email !== '' && !validate_email((string) $email)) {
                $errors["emails.{$i}"][] = 'This is not a valid email address';
            }
        }

        return $errors;
    }
}
