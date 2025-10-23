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
 * Handle sending a user to a tool provider to initiate a content-item selection.
 *
 * @package    core_ltix
 * @copyright  2024 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_ltix\local\placement\placements_manager;
use core_ltix\local\lticore\message\request\builder\builder_factory;

require_once('../config.php');
require_once($CFG->dirroot . '/mod/lti/lib.php');
require_once($CFG->dirroot . '/mod/lti/locallib.php');

$id = required_param('id', PARAM_INT);
$contextid = required_param('contextid', PARAM_INT);
$placementtype = required_param('placementtype', PARAM_RAW);
$title = optional_param('title', '', PARAM_TEXT);
$text = optional_param('text', '', PARAM_RAW);

$context = \context_helper::instance_by_id($contextid);
$course = $DB->get_record('course', ['id' => $context->get_course_context()->instanceid], '*', MUST_EXIST);

// Confirm that the user is logged in.
if ($context instanceof context_course) {
    require_login($course);
} else if ($context instanceof context_module) {
    $cm = get_coursemodule_from_id('', $context->instanceid, 0, false, MUST_EXIST);
    require_login($course, true, $cm, true, true);
} else {
    require_login();
}

// Confirm any capability restrictions that implementors may have.
$placementinstance = placements_manager::get_instance()->get_deeplinking_placement_instance($placementtype);
$placementinstance->content_item_selection_capabilities($context);

// TODO: Expand the expected context beyond just course.
// Currently, the expected context is always course due to the lack flexibility of the methods that are used for constructing
// the login or the content item selection request. This should be improved once these calls are replaced by the builder API.

$config = \core_ltix\helper::get_type_type_config($id);
$messagetype = $config->lti_ltiversion === \core_ltix\constants::LTI_VERSION_1P3 ?
    'LtiDeepLinkingRequest' : 'ContentItemSelectionRequest';

// Set the return URL. We send the launch container along to help us avoid frames-within-frames when the user returns.
$returnurlparams = [
    'contextid' => $contextid,
    'id' => $id,
    'sesskey' => sesskey()
];
$returnurl = new \moodle_url('/ltix/contentitem_return.php', $returnurlparams);

// Set a unique launch ID session variable to store parameters used during the LTI 1.1 ContentItemSelection process.
// A similar approach is followed for LTI 1.3 ContentItemSelection, but the session variable is set while building
// the login request.
$launchid = "ltilaunch_$messagetype" . rand();
$SESSION->$launchid = "{$context->get_course_context()->instanceid},{$config->typeid},,{$messagetype},0," .
    base64_encode($title) . ',' . base64_encode($text) . ",{$placementtype}";

// Prepare launch configuration for the builder factory.
$launchconfig = (object)[
    'toolconfig'    => $config,
    'context'       => $context,
    'user'          => $USER,
    'messagetype'   => $messagetype,
    'returnurl'     => $returnurl->out(false),
    'launchid'      => $launchid,
    'placementtype' => $placementtype,
];

// Use the builder factory to create the appropriate request builder.
$requestbuilderfactory = new builder_factory();

// Build the deep linking request (or LTI 1.1 content item selection).
$builder = $requestbuilderfactory->get_request_builder($launchconfig);
$message = $builder->build_message();

// Get the launch HTML.
echo $message->to_html_form();
