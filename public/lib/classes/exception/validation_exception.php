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

/**
 * Exception indicating that submitted data failed validation.
 *
 * It carries a list of messages per field so that all of the problems can be reported at once.
 * When thrown in a REST API route, it results in a 422 response using the RFC 9457 problem details format.
 *
 * Field names are paths. Nested fields are separated with a dot (for example `address.city`), and the items of a list use
 * their index (for example `tags.0`). Messages that are not related to a specific field use the {@see self::ROOT} key.
 *
 * @package    core
 * @copyright  2026 Moodle Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class validation_exception extends moodle_exception implements response_aware_exception {
    /** @var string The key used for messages which are not related to a specific field. */
    public const ROOT = '';

    /** @var array<string, string[]> The messages for each field. */
    protected array $errors = [];

    /**
     * Constructor.
     *
     * @param array<string, string|string[]> $errors The messages for each field.
     * @param string|null $debuginfo Some detailed information
     */
    public function __construct(array $errors = [], ?string $debuginfo = null) {
        foreach ($errors as $field => $messages) {
            $messages = array_values(array_filter(
                array_map(strval(...), (array) $messages),
                fn(string $message): bool => $message !== '',
            ));
            if ($messages) {
                $this->errors[(string) $field] = $messages;
            }
        }

        parent::__construct('validationerror', 'error', '', null, $debuginfo);
    }

    /**
     * Create an exception for a single field.
     *
     * @param string $field The field name, or {@see self::ROOT}
     * @param string $message
     * @return static
     */
    public static function for_field(string $field, string $message): static {
        return new static([$field => [$message]]);
    }

    /**
     * Get all of the messages, keyed by field.
     *
     * @return array<string, string[]>
     */
    public function get_errors(): array {
        return $this->errors;
    }

    /**
     * Get the messages which relate to a specific field.
     *
     * @return array<string, string[]>
     */
    public function get_field_errors(): array {
        return array_diff_key($this->errors, [self::ROOT => true]);
    }

    /**
     * Get the messages which do not relate to a specific field.
     *
     * @return string[]
     */
    public function get_root_errors(): array {
        return $this->errors[self::ROOT] ?? [];
    }

    /**
     * Get the messages with the field names nested under the specified parent field.
     *
     * @param string $parent
     * @return array<string, string[]>
     */
    public function get_errors_under(string $parent): array {
        $errors = [];
        foreach ($this->errors as $field => $messages) {
            $errors[$field === self::ROOT ? $parent : "{$parent}.{$field}"] = $messages;
        }

        return $errors;
    }

    #[\Override]
    public function get_response_classname(): string {
        return \core\router\response\validation_error_response::class;
    }
}
