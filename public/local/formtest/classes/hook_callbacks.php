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

namespace local_formtest;

use core\hook\form\after_form_loaded;
use core\hook\form\after_form_saved;
use core\hook\form\before_form_validation;
use core\hook\form\extend_form;

/**
 * Extends the core React forms test form (/test.php) with the plugin's own fields.
 *
 * @package    local_formtest
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /** @var string The form being extended. */
    private const FORM = 'core_test_form';

    /** @var int The highest allowed limit. */
    public const MAX_LIMIT = 10;

    /**
     * Load the extension module.
     *
     * @param extend_form $hook
     */
    public static function extend_form(extend_form $hook): void {
        if ($hook->formname !== self::FORM) {
            return;
        }
        $hook->add_module('@moodle/lms/local_formtest/extension');
    }

    /**
     * Provide the last saved values of the plugin's fields. Fields without a saved value use their own default.
     *
     * @param after_form_loaded $hook
     */
    public static function form_loaded(after_form_loaded $hook): void {
        if ($hook->formname !== self::FORM) {
            return;
        }
        $last = json_decode(get_config('local_formtest', 'lastsubmission') ?: '', true);
        if (is_array($last)) {
            $hook->set_values('local_formtest', array_intersect_key($last, array_flip(['reference', 'limit', 'sendcopy'])));
        }
    }

    /**
     * Validate the plugin's fields.
     *
     * @param before_form_validation $hook
     */
    public static function validate_form(before_form_validation $hook): void {
        if ($hook->formname !== self::FORM) {
            return;
        }
        $data = $hook->payload['extensions']['local_formtest'] ?? [];

        $reference = (string) ($data['reference'] ?? '');
        if (!preg_match('/^[A-Z]{3}-\d{3}$/', $reference)) {
            $hook->add_error('extensions.local_formtest.reference', get_string('badreference', 'local_formtest'));
        }
        if ((int) ($data['limit'] ?? 0) > self::MAX_LIMIT) {
            $hook->add_error(
                'extensions.local_formtest.limit',
                get_string('limittoohigh', 'local_formtest', self::MAX_LIMIT),
            );
        }
    }

    /**
     * Save the plugin's data. A real plugin would write to its own table.
     *
     * @param after_form_saved $hook
     */
    public static function form_saved(after_form_saved $hook): void {
        if ($hook->formname !== self::FORM) {
            return;
        }
        $data = $hook->payload['extensions']['local_formtest'] ?? [];
        set_config('lastsubmission', json_encode([
            'formid' => $hook->id,
            'reference' => (string) ($data['reference'] ?? ''),
            'limit' => (int) ($data['limit'] ?? 0),
            'sendcopy' => !empty($data['sendcopy']),
        ]), 'local_formtest');
    }
}
