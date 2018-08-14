<?php
/**
 * Create bulk delete data requests.
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$confirm = optional_param('confirm', 0, PARAM_BOOL);

require_login();
admin_externalpage_setup('userbulk');

$context = context_system::instance();
// Make sure the user has the proper capability.
require_capability('tool/dataprivacy:managedatarequests', $context);

$return = $CFG->wwwroot.'/'.$CFG->admin.'/user/user_bulk.php';

if (empty($SESSION->bulk_users)) {
    redirect($return);
}

echo $OUTPUT->header();

if ($confirm and confirm_sesskey()) {
    $notifications = '';
    list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
    $rs = $DB->get_recordset_select('user', "id $in", $params);
    foreach ($rs as $user) {
        if (!is_siteadmin($user) and $USER->id != $user->id and
                \tool_dataprivacy\api::create_data_request($user->id, \tool_dataprivacy\api::DATAREQUEST_TYPE_DELETE)) {
            unset($SESSION->bulk_users[$user->id]);
        } else {
            $notifications .= $OUTPUT->notification(get_string('deletednot', '',
                    fullname($user, true)));
        }
    }
    $rs->close();
    \core\session\manager::gc(); // Remove stale sessions.
    echo $OUTPUT->box_start('generalbox', 'notice');
    if (!empty($notifications)) {
        echo $notifications;
    } else {
        echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
    }
    $continue = new single_button(new moodle_url($return), get_string('continue'), 'post');
    echo $OUTPUT->render($continue);
    echo $OUTPUT->box_end();
} else {
    list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
    $userlist = $DB->get_records_select_menu('user', "id $in", $params, 'fullname',
            'id,'.$DB->sql_fullname().' AS fullname');
    $usernames = implode(', ', $userlist);
    echo $OUTPUT->heading(get_string('confirmation', 'admin'));
    $formcontinue = new single_button(new moodle_url('/admin/tool/dataprivacy/user_bulk_delete_data_request.php',
            array('confirm' => 1)), get_string('yes'));
    $formcancel = new single_button(new moodle_url('/admin/user/user_bulk.php'),
            get_string('no'), 'get');
    echo $OUTPUT->confirm(get_string('confirmbulkdeleterequest', 'tool_dataprivacy', $usernames),
            $formcontinue, $formcancel);
}

echo $OUTPUT->footer();
