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

use core\exception\invalid_parameter_exception;
use core\exception\validation_exception;
use core\param;
use core\router\schema\specification;

/**
 * A scalar type.
 *
 * @package    core
 * @copyright  2023 Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class scalar_type extends type_base {
    /**
     * Instantiate a new Scalar Type
     *
     * @param param $type The Moodle PARAM_ type
     * @param bool $required Whether the value is required or not
     * @param int|null $maxlength The maximum length of a string value
     * @param array|null $enum The values which are allowed
     * @param mixed[] ...$extra
     */
    public function __construct(
        /** @var param The type of the parameter content */
        protected param $type,
        /** @var bool Whether the value is required or not */
        protected bool $required = false,
        /** @var int|null The maximum length of a string value */
        protected ?int $maxlength = null,
        /** @var array|null The values which are allowed */
        protected ?array $enum = null,
        ...$extra,
    ) {
        parent::__construct(...$extra);
    }

    #[\Override]
    public function get_openapi_description(
        specification $api,
        ?string $path = null,
    ): ?\stdClass {
        $data = $this->get_schema_from_type($this->type);

        if ($this->maxlength !== null) {
            $data->maxLength = $this->maxlength;
        }

        if ($this->enum !== null) {
            $data->enum = array_values($this->enum);
        }

        return $data;
    }

    #[\Override]
    public function is_required(): bool {
        return $this->required;
    }

    #[\Override]
    public function validate_data(mixed $data) {
        if ($data === null && $this->required) {
            throw validation_exception::for_field(validation_exception::ROOT, get_string('validationrequired', 'error'));
        }

        try {
            $value = $this->type->validate_param(
                param: $data,
                allownull: NULL_ALLOWED,
            );
        } catch (invalid_parameter_exception) {
            throw validation_exception::for_field(validation_exception::ROOT, get_string('validationinvalid', 'error'));
        }

        if ($value === null) {
            return null;
        }

        if ($this->maxlength !== null && mb_strlen((string) $value) > $this->maxlength) {
            throw validation_exception::for_field(
                validation_exception::ROOT,
                get_string('validationmaxlength', 'error', $this->maxlength),
            );
        }

        if ($this->enum !== null && !in_array((string) $value, array_map(strval(...), $this->enum), true)) {
            throw validation_exception::for_field(
                validation_exception::ROOT,
                get_string('validationenum', 'error', implode(', ', $this->enum)),
            );
        }

        return $value;
    }
}
