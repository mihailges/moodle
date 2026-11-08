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

namespace core_admin\hook;

use core\oauth2\server\entity\client_entity;

/**
 * Allows plugins to extend the OAuth 2 client create and edit forms.
 *
 * A plugin adds a JavaScript module that registers its form components in the form slots
 * (see registerFormExtension in @moodle/lms/core/forms), and can supply the initial values of its fields.
 * The fields must be named "extensions.<component>.<field>" so that the values are submitted with the form,
 * and are handed to the \core\hook\form\before_form_validation and \core\hook\form\after_form_saved hooks,
 * which are dispatched with the form name core_admin_oauth2_client.
 *
 * @package    core_admin
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\core\attribute\label('Allows plugins to extend the OAuth 2 client create and edit forms')]
#[\core\attribute\tags('oauth2')]
final class oauth2_client_form_extend {
    /** @var string[] The ES modules to load with the form, by specifier. */
    private array $modules = [];

    /** @var array The initial values of the extension fields, by component. */
    private array $values = [];

    /**
     * Constructor.
     *
     * @param client_entity|null $client The client being edited, or null when creating one.
     */
    public function __construct(
        /** @var client_entity|null The client being edited, or null when creating one. */
        public readonly ?client_entity $client = null,
    ) {
    }

    /**
     * Load an ES module with the form. It should register its form extensions when imported.
     *
     * @param string $specifier The module specifier, in the form "@moodle/lms/<component>/<path>".
     */
    public function add_module(string $specifier): void {
        if (!preg_match('~^@moodle/lms/[a-z][a-z0-9_]*(/[A-Za-z0-9_.-]+)+$~', $specifier) || str_contains($specifier, '..')) {
            throw new \coding_exception("Invalid form extension module '{$specifier}'");
        }
        if (!in_array($specifier, $this->modules, true)) {
            $this->modules[] = $specifier;
        }
    }

    /**
     * Set the initial values of a plugin's extension fields, available as "extensions.<component>.<field>".
     *
     * @param string $component The plugin's frankenstyle component name.
     * @param array $values The field values, which must be JSON serialisable.
     */
    public function set_values(string $component, array $values): void {
        $this->values[$component] = $values;
    }

    /**
     * The modules to load.
     *
     * @return string[]
     */
    public function get_modules(): array {
        return $this->modules;
    }

    /**
     * The initial extension values, by component.
     *
     * @return array
     */
    public function get_values(): array {
        return $this->values;
    }
}
