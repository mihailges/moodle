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

namespace core\exception;

use core\router\response\validation_error_response;

/**
 * Tests for the validation exception.
 *
 * @package    core
 * @copyright  2026 Moodle Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(validation_exception::class)]
final class validation_exception_test extends \advanced_testcase {
    public function test_errors(): void {
        $exception = new validation_exception([
            'name' => 'Required',
            'email' => ['Invalid', 'Too long', ''],
            'empty' => [],
            validation_exception::ROOT => ['General problem'],
        ]);

        $this->assertEquals([
            'name' => ['Required'],
            'email' => ['Invalid', 'Too long'],
            validation_exception::ROOT => ['General problem'],
        ], $exception->get_errors());
        $this->assertEquals(['name', 'email'], array_keys($exception->get_field_errors()));
        $this->assertEquals(['General problem'], $exception->get_root_errors());
    }

    public function test_for_field(): void {
        $exception = validation_exception::for_field('name', 'Required');

        $this->assertEquals(['name' => ['Required']], $exception->get_errors());
        $this->assertEquals([], $exception->get_root_errors());
    }

    public function test_get_errors_under(): void {
        $exception = new validation_exception([
            validation_exception::ROOT => ['Invalid'],
            'city' => ['Required'],
        ]);

        $this->assertEquals(
            ['address' => ['Invalid'], 'address.city' => ['Required']],
            $exception->get_errors_under('address'),
        );
    }

    public function test_response_classname(): void {
        $this->assertEquals(
            validation_error_response::class,
            (new validation_exception())->get_response_classname(),
        );
    }
}
