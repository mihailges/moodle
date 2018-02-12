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
 * Validate a digital minor.
 *
 * @package     core
 * @category    auth
 * @copyright   2018 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once($CFG->libdir . '/authlib.php');
require_once ('verify_age_location_form.php');
require_once ('verify_age_location_page.php');
require_once ('lib.php');


if (!$authplugin = signup_is_enabled()) {
    print_error('notlocalisederrormessage', 'error', '', 'Sorry, you may not use this page.');
}

if (!is_age_location_verification_enabled()) {
    print_error('notlocalisederrormessage', 'error', '', 'Sorry, you may not use this page.');
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/login/verify_age_location.php'));

if (isloggedin() and !isguestuser()) {
    // Prevent signing up when already logged in.
    echo $OUTPUT->header();
    echo $OUTPUT->box_start();
    $logout = new single_button(new moodle_url($CFG->httpswwwroot . '/login/logout.php',
        array('sesskey' => sesskey(), 'loginpage' => 1)), get_string('logout'), 'post');
    $continue = new single_button(new moodle_url('/'), get_string('cancel'), 'get');
    echo $OUTPUT->confirm(get_string('cannotsignup', 'error', fullname($USER)), $logout, $continue);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit;
}

$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('agelocationverification'));
$PAGE->set_heading($SITE->fullname);

// Handle if minor check has already been done.
//if (\tool_policy\session_helper::minor_session_exists()) {
//    if (!\tool_policy\session_helper::is_valid_policy_session()) { // Policy session is no longer valid.
//        \tool_policy\session_helper::destroy_policy_session();
//    } else { // Policy session is still valid.
//        $is_minor = \tool_policy\session_helper::get_minor_session_status();
//        \tool_policy\validateminor_helper::redirect($is_minor);
//    }
//}

$mform = new verify_age_location_form();
$page = new verify_age_location_page($mform);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/login/index.php'));
} else if ($data = $mform->get_data()) {
    $is_minor = core_login_is_minor($data->age, $data->country);
    $USER->is_minor = $is_minor;
    redirect(new moodle_url('/login/signup.php'));
//    $is_minor = \tool_policy\api::is_minor($data->age, $data->country);
//    \tool_policy\session_helper::create_policy_session();
//    \tool_policy\session_helper::create_minor_session($is_minor);
//    \tool_policy\validateminor_helper::redirect($is_minor);
} else {
    echo $OUTPUT->header();
    if ($page instanceof renderable) {
        // Try and use the renderer from the auth plugin if it exists.
        try {
            $renderer = $PAGE->get_renderer('auth_' . $authplugin->authtype);
        } catch (coding_exception $ce) {
            // Fall back on the general renderer.
            $renderer = $OUTPUT;
        }
        echo $renderer->render($page);
    } else {
        // Fall back for auth plugins not using renderables.
        $mform->display();
    }
    echo $OUTPUT->footer();
}
