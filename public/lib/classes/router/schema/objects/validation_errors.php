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

use core\router\schema\referenced_object;
use core\router\schema\specification;

/**
 * A schema to describe the validation messages for each field.
 *
 * The keys are field paths and the values are lists of messages.
 *
 * @package    core
 * @copyright  2026 Moodle Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class validation_errors extends type_base implements referenced_object {
    #[\Override]
    public function get_openapi_description(
        specification $api,
        ?string $path = null,
    ): ?\stdClass {
        return (object) [
            'type' => 'object',
            'description' => 'The validation messages for each field, keyed by field path. '
                . 'Nested fields are separated with a dot, and list items use their index.',
            'additionalProperties' => (object) [
                'type' => 'array',
                'items' => (object) ['type' => 'string'],
            ],
        ];
    }

    #[\Override]
    public function validate_data(mixed $data) {
        return $data;
    }
}
