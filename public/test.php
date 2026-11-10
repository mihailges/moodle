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
 * Test page of the React forms system: a dummy form that plugins can extend.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/config.php');

require_login();
$context = \core\context\system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url('/test.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title('React forms test');
$PAGE->set_heading('React forms test');

// Plugins add their modules and the initial values of their fields to the form.
$extend = new \core\hook\form\extend_form(\core\route\api\test_form::FORM_NAME);
\core\di::get(\core\hook\manager::class)->dispatch($extend);

echo $OUTPUT->header();
echo html_writer::react_component('@moodle/lms/core/forms/test/testForm', [
    'extensionModules' => $extend->get_modules(),
]);
echo $OUTPUT->footer();
