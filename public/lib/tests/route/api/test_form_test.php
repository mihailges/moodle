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

use core\hook\form\after_form_saved;
use core\hook\form\before_form_validation;
use core\tests\router\route_testcase;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the dummy React forms test endpoint.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(test_form::class)]
final class test_form_test extends route_testcase {
    /** @var array A valid submission. */
    private const VALID = ['title' => 'Hello', 'rating' => 4, 'color' => '#aabbcc', 'emails' => ['a@example.com'],
        'externalId' => 'ext-1', 'slug' => 'hello'];

    /**
     * Send the form to the endpoint as an admin.
     *
     * @param array $body
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function save(array $body) {
        $this->add_class_routes_to_route_loader(test_form::class);
        $this->setAdminUser();
        return $this->process_api_request('POST', '/test/form', body: Utils::streamFor(json_encode($body)));
    }

    /**
     * The form's own fields are validated.
     */
    public function test_core_validation(): void {
        $this->resetAfterTest();

        $response = $this->save(['title' => '', 'rating' => 9, 'color' => 'red', 'emails' => ['nope']]);

        $this->assert_valid_response($response, 422);
        $errors = array_keys((array) $this->decode_response($response)->errors);
        foreach (['title', 'rating', 'color', 'emails.0', 'externalId', 'slug'] as $field) {
            $this->assertContains($field, $errors);
        }
    }

    /**
     * Plugins can add validation errors and are told about the saved form.
     */
    public function test_hooks(): void {
        $this->resetAfterTest();
        $saved = [];
        $manager = \core\di::get(\core\hook\manager::class);
        $manager->phpunit_redirect_hook(
            before_form_validation::class,
            fn (before_form_validation $hook) => $hook->add_error('extensions.local_x.f', 'Bad'),
        );
        $manager->phpunit_redirect_hook(
            after_form_saved::class,
            function (after_form_saved $hook) use (&$saved) {
                $saved[] = $hook->formname;
            },
        );

        $response = $this->save(self::VALID + ['extensions' => ['local_x' => ['f' => 'v']]]);
        $this->assert_valid_response($response, 422);
        $this->assertSame(['Bad'], $this->decode_response($response)->errors->{'extensions.local_x.f'});
        $this->assertSame([], $saved);

        $manager->phpunit_redirect_hook(before_form_validation::class, fn () => null);
        $response = $this->save(self::VALID);
        $this->assert_valid_response($response, 201);
        $this->assertSame(['core_test_form'], $saved);
    }
}
