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

use core\exception\coding_exception;
use core\exception\invalid_parameter_exception;
use core\exception\validation_exception;
use core\router\schema\specification;

/**
 * A schema to describe an array of things. These could be any type, including other schema definitions.
 *
 * See https://spec.openapis.org/oas/v3.1.0#model-with-map-dictionary-properties for relevant documentation.
 *
 * @package    core
 * @copyright  2023 Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class schema_object extends type_base {
    /**
     * An array of things.
     *
     * @param type_base[] $content The child content
     * @param mixed[] ...$extra
     * @throws coding_exception
     */
    public function __construct(
        /** @var type_base[] The child content */
        protected array $content,

        ...$extra,
    ) {
        foreach ($content as $child) {
            if (!$child instanceof type_base) {
                throw new coding_exception('Content must be an array of type_base objects');
            }
        }

        parent::__construct(...$extra);
    }

    /**
     * Whether this schema object has this key as a type.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool {
        return isset($this->content[$key]);
    }

    /**
     * Get the schema object for this key.
     *
     * @param string $key
     * @return type_base
     */
    public function get(string $key): type_base {
        return $this->content[$key];
    }

    #[\Override]
    public function validate_data(mixed $data) {
        if (!is_array($data)) {
            throw validation_exception::for_field(validation_exception::ROOT, get_string('validationinvalid', 'error'));
        }

        // All of the problems are collected so that they can be reported together.
        $errors = [];
        $validated = [];

        foreach ($data as $key => $value) {
            $key = (string) $key;
            if (!$this->has($key)) {
                $errors[$key][] = get_string('validationunknownfield', 'error');
                continue;
            }

            try {
                $validated[$key] = $this->content[$key]->validate_data($value);
            } catch (validation_exception $e) {
                foreach ($e->get_errors_under($key) as $path => $messages) {
                    $errors[$path] = array_merge($errors[$path] ?? [], $messages);
                }
            } catch (invalid_parameter_exception) {
                $errors[$key][] = get_string('validationinvalid', 'error');
            }
        }

        foreach ($this->content as $key => $child) {
            if (array_key_exists($key, $data)) {
                continue;
            }

            if ($child->is_required()) {
                $errors[$key][] = get_string('validationrequired', 'error');
            }
        }

        if ($errors) {
            throw new validation_exception($errors);
        }

        return $validated;
    }

    #[\Override]
    public function get_openapi_description(
        specification $api,
        ?string $path = null,
    ): ?\stdClass {
        $data = (object) [
            'type' => 'object',
            'properties' => (object) [],
        ];

        foreach ($this->content as $name => $content) {
            $data->properties->{$name} = $content->get_openapi_schema(
                $api,
            );

            if ($content->is_required()) {
                $data->required[] = $name;
            }
        }

        return $data;
    }
}
