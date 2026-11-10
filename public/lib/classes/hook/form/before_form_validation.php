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

namespace core\hook\form;

/**
 * Allows plugins to validate the data submitted by a React form, before it is saved.
 *
 * Listeners should ignore forms they do not know by checking the form name. The payload is not cleaned:
 * validate and clean it before use. Errors are keyed by field path, as the React form maps them onto its fields.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\core\attribute\label('Allows plugins to validate the data submitted by a React form before it is saved')]
#[\core\attribute\tags('forms')]
final class before_form_validation {
    /** @var array<string, string[]> The errors, keyed by field path. */
    private array $errors = [];

    /**
     * Constructor.
     *
     * @param string $formname The name of the form, in the form "<component>_<form>".
     * @param array $payload The submitted data. For updates this is only the data the request contained.
     */
    public function __construct(
        /** @var string The name of the form. */
        public readonly string $formname,
        /** @var array The submitted data. */
        public readonly array $payload,
    ) {
    }

    /**
     * Report an invalid field.
     *
     * @param string $fieldpath The path of the field in the payload, e.g. "extensions.local_example.limit".
     * @param string $message The message to show on the field.
     */
    public function add_error(string $fieldpath, string $message): void {
        $this->errors[$fieldpath][] = $message;
    }

    /**
     * The errors reported, as messages keyed by field path.
     *
     * @return array<string, string[]>
     */
    public function get_errors(): array {
        return $this->errors;
    }
}
