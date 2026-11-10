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

use core\hook\form\after_form_saved;
use core\hook\form\before_form_validation;
use core\hook\form\extend_form;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the form extension hook callbacks.
 *
 * @package    local_formtest
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(hook_callbacks::class)]
final class hook_callbacks_test extends \advanced_testcase {
    /**
     * The module and default values are only added to the test form.
     */
    public function test_extend_form(): void {
        $hook = new extend_form('core_test_form');
        hook_callbacks::extend_form($hook);
        $this->assertSame(['@moodle/lms/local_formtest/extension'], $hook->get_modules());
        $this->assertSame(5, $hook->get_values()['local_formtest']['limit']);

        $other = new extend_form('core_other');
        hook_callbacks::extend_form($other);
        $this->assertSame([], $other->get_modules());
    }

    /**
     * Invalid extension data is reported by field path.
     */
    public function test_validate_form(): void {
        $hook = new before_form_validation('core_test_form', [
            'extensions' => ['local_formtest' => ['reference' => 'bad', 'limit' => 50]],
        ]);
        hook_callbacks::validate_form($hook);
        $this->assertEqualsCanonicalizing(
            ['extensions.local_formtest.reference', 'extensions.local_formtest.limit'],
            array_keys($hook->get_errors()),
        );

        $valid = new before_form_validation('core_test_form', [
            'extensions' => ['local_formtest' => ['reference' => 'ABC-123', 'limit' => 3]],
        ]);
        hook_callbacks::validate_form($valid);
        $this->assertSame([], $valid->get_errors());
    }

    /**
     * The plugin's data is saved with the form.
     */
    public function test_form_saved(): void {
        $this->resetAfterTest();
        hook_callbacks::form_saved(new after_form_saved('core_test_form', 7, [
            'extensions' => ['local_formtest' => ['reference' => 'ABC-123', 'limit' => 3, 'sendcopy' => true]],
        ]));
        $saved = json_decode(get_config('local_formtest', 'lastsubmission'), true);
        $this->assertSame(['formid' => 7, 'reference' => 'ABC-123', 'limit' => 3, 'sendcopy' => true], $saved);
    }
}
