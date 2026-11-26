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

namespace core\router\schema\objects;

use core\exception\validation_exception;
use core\param;
use core\router\schema\referenced_object;
use core\router\schema\specification;
use core\tests\router\route_testcase;

/**
 * Tests for the schema_object.
 *
 * @package    core
 * @copyright  2023 Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\core\router\schema\objects\schema_object::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\core\router\schema\openapi_base::class)]
final class schema_object_test extends route_testcase {
    public function test_referenced_object(): void {
        $object = new class (
            content: [
                'example' => new schema_object(content: []),
            ],
        ) extends schema_object implements referenced_object {
        };

        $schema = $object->get_openapi_description(new specification());
        $this->assertObjectNotHasProperty('$ref', $schema);
        $this->assertObjectHasProperty('type', $schema);
        $this->assertObjectHasProperty('properties', $schema);
        $this->assertObjectHasProperty('example', $schema->properties);

        $reference = $object->get_openapi_schema(new specification());
        $this->assertObjectNotHasProperty('type', $reference);
        $this->assertObjectHasProperty('$ref', $reference);
    }

    public function test_basics(): void {
        $object = new schema_object(
            content: [
                'example' => new schema_object(content: []),
            ],
        );

        $this->assertFalse($object->has('missing'));
        $this->assertTrue($object->has('example'));

        $this->assertInstanceOf(schema_object::class, $object->get('example'));
    }

    public function test_invalid_content(): void {
        $this->expectException(\coding_exception::class);

        new schema_object(content: ['invalid']);
    }

    public function test_validation(): void {
        $object = new schema_object(
            content: [
                'example' => new schema_object(content: [
                    'somekey' => new schema_object(content: []),
                ]),
            ],
        );

        // Nothing in, nothing out.
        $this->assertEmpty($object->validate_data([]));

        // Valid data is included.
        $data = $object->validate_data(['example' => []]);
        $this->assertArrayHasKey('example', $data);
        $this->assertIsArray($data['example']);

        // Applied to nested values.
        $data = $object->validate_data(['example' => ['somekey' => []]]);
        $this->assertArrayHasKey('example', $data);
        $this->assertIsArray($data['example']);
        $this->assertArrayHasKey('somekey', $data['example']);
    }

    public function test_validation_collects_all_errors(): void {
        $object = new schema_object(
            content: [
                'name' => new scalar_type(type: param::TEXT, required: true, maxlength: 3),
                'age' => new scalar_type(type: param::INT),
                'status' => new scalar_type(type: param::ALPHA, enum: ['open', 'closed']),
                'address' => new schema_object(content: [
                    'city' => new scalar_type(type: param::TEXT, required: true),
                ]),
                'tags' => new array_of_things(param::ALPHA),
                'missing' => new scalar_type(type: param::TEXT, required: true),
            ],
        );

        try {
            $object->validate_data([
                'name' => 'toolong',
                'age' => 'abc',
                'status' => 'other',
                'address' => ['city' => null, 'zip' => '1'],
                'tags' => ['ok', '123'],
                'surplus' => 'x',
            ]);
            $this->fail('Expected a validation_exception');
        } catch (validation_exception $e) {
            $errors = $e->get_errors();
            $this->assertEqualsCanonicalizing(
                ['name', 'age', 'status', 'address.city', 'address.zip', 'tags.1', 'surplus', 'missing'],
                array_keys($errors),
            );
            $this->assertEquals([get_string('validationmaxlength', 'error', 3)], $errors['name']);
            $this->assertEquals([get_string('validationinvalid', 'error')], $errors['age']);
            $this->assertEquals([get_string('validationrequired', 'error')], $errors['address.city']);
            $this->assertEquals([get_string('validationrequired', 'error')], $errors['missing']);
            $this->assertEquals([get_string('validationunknownfield', 'error')], $errors['surplus']);
            $this->assertEquals([get_string('validationunknownfield', 'error')], $errors['address.zip']);
        }
    }

    public function test_validation_required_key(): void {
        $object = new schema_object(
            content: [
                'name' => new scalar_type(type: param::TEXT, required: true),
                'note' => new scalar_type(type: param::TEXT),
            ],
        );

        // Omitted optional keys are left out, not filled in.
        $this->assertEquals(['name' => 'a'], $object->validate_data(['name' => 'a']));

        $this->expectException(validation_exception::class);
        $object->validate_data([]);
    }

    public function test_validation_rejects_non_array(): void {
        $object = new schema_object(content: []);

        $this->expectException(validation_exception::class);
        $object->validate_data('string');
    }
}
