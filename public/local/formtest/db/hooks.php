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

/**
 * Hook callbacks.
 *
 * @package    local_formtest
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core\hook\form\extend_form::class,
        'callback' => [\local_formtest\hook_callbacks::class, 'extend_form'],
    ],
    [
        'hook' => \core\hook\form\before_form_validation::class,
        'callback' => [\local_formtest\hook_callbacks::class, 'validate_form'],
    ],
    [
        'hook' => \core\hook\form\after_form_saved::class,
        'callback' => [\local_formtest\hook_callbacks::class, 'form_saved'],
    ],
];
