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
 * Allows plugins to add their own data to the record a React form loads for editing.
 *
 * The endpoint that loads the record dispatches it, and returns what plugins set under "extensions.<component>",
 * so the fields of a plugin's form extension (see {@see extend_form}) start from it.
 * Listeners should ignore forms they do not know by checking the form name.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\core\attribute\label('Allows plugins to add their data to the record a React form loads')]
#[\core\attribute\tags('forms')]
final class after_form_loaded {
    /** @var array The values of the extension fields, by component. */
    private array $values = [];

    /**
     * Constructor.
     *
     * @param string $formname The name of the form, in the form "<component>_<form>".
     * @param int|null $id The id of the record being loaded, if the form has one.
     */
    public function __construct(
        /** @var string The name of the form. */
        public readonly string $formname,
        /** @var int|null The id of the record being loaded. */
        public readonly ?int $id = null,
    ) {
    }

    /**
     * Set the values of a plugin's extension fields, available as "extensions.<component>.<field>".
     *
     * @param string $component The plugin's frankenstyle component name.
     * @param array $values The field values, which must be JSON serialisable.
     */
    public function set_values(string $component, array $values): void {
        $this->values[$component] = $values;
    }

    /**
     * The extension values, by component.
     *
     * @return array
     */
    public function get_values(): array {
        return $this->values;
    }
}
