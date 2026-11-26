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

namespace core\router\response;

use core\exception\validation_exception;
use core\router\schema\specification;
use core\tests\router\route_testcase;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * Tests for the validation error response.
 *
 * @package    core
 * @copyright  2026 Moodle Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(validation_error_response::class)]
final class validation_error_response_test extends route_testcase {
    public function test_status_code(): void {
        $this->assertEquals(422, validation_error_response::get_exception_status_code());
    }

    public function test_get_response(): void {
        $exception = new validation_exception([
            'name' => ['Required'],
            'address.city' => ['Too long', 'Invalid'],
        ]);
        $request = new ServerRequest('POST', '/example');

        $payload = validation_error_response::get_response($request, $exception);
        $response = $payload->get_response($this->get_router()->get_response_factory());

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('application/problem+json', $response->getHeaderLine('Content-Type'));

        $body = json_decode((string) $response->getBody());
        $this->assertEquals('about:blank', $body->type);
        $this->assertEquals('Unprocessable Entity', $body->title);
        $this->assertEquals(422, $body->status);
        $this->assertEquals('/example', $body->instance);
        $this->assertEquals($exception->getMessage(), $body->detail);
        $this->assertEquals(['Required'], $body->errors->name);
        $this->assertEquals(['Too long', 'Invalid'], $body->errors->{'address.city'});

        // The deprecated members are not part of new responses.
        $this->assertObjectNotHasProperty('message', $body);
        $this->assertObjectNotHasProperty('errorcode', $body);
    }

    public function test_root_errors_become_the_detail(): void {
        $exception = validation_exception::for_field(validation_exception::ROOT, 'The body is broken');
        $request = new ServerRequest('POST', '/example');

        $payload = validation_error_response::get_response($request, $exception);
        $body = json_decode((string) $payload->get_response($this->get_router()->get_response_factory())->getBody());

        $this->assertEquals('The body is broken', $body->detail);
        $this->assertEquals((object) [], $body->errors);
    }

    public function test_openapi_description(): void {
        $api = new specification();
        $openapi = (new validation_error_response())->get_openapi_description($api);

        $this->assertIsString($openapi->description);
        $this->assertObjectHasProperty('application/problem+json', $openapi->content);

        $this->assertObjectHasProperty('$ref', $openapi->content->{'application/problem+json'}->schema);
    }
}
