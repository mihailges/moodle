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
 * Renderer for the grade user report
 *
 * @package   gradereport_user
 * @copyright 2010 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_message\helper;
use core_message\api;

/**
 * Custom renderer for the user grade report
 *
 * To get an instance of this use the following code:
 * $renderer = $PAGE->get_renderer('gradereport_user');
 *
 * @copyright 2010 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradereport_user_renderer extends plugin_renderer_base {

    /**
     * Small rendering function that helps with outputting the relevant user selector.
     *
     * @param string $report
     * @param stdClass $course
     * @param int $userid
     * @param null|int $groupid
     * @param bool $includeall
     * @return string The raw HTML to render.
     * @throws coding_exception
     */
    public function graded_users_selector(string $report, stdClass $course, int $userid, ?int $groupid, bool $includeall): string {

        $select = grade_get_graded_users_select($report, $course, $userid, $groupid, $includeall);
        $output = html_writer::tag('div', $this->output->render($select), ['id' => 'graded_users_selector']);
        $output .= html_writer::tag('p', '', ['style' => 'page-break-after: always;']);

        return $output;
    }

    /**
     * Creates and renders the single select box for the user view.
     *
     * @param int $userid The selected userid
     * @param int $userview The current view user setting constant
     * @return string
     */
    public function view_user_selector(int $userid, int $userview): string {
        global $USER;
        $url = $this->page->url;
        if ($userid != $USER->id) {
            $url->param('userid', $userid);
        }

        $options = [
            GRADE_REPORT_USER_VIEW_USER => get_string('otheruser', 'gradereport_user'),
            GRADE_REPORT_USER_VIEW_SELF => get_string('myself', 'gradereport_user')
        ];
        $select = new single_select($url, 'userview', $options, $userview, null);

        $select->label = get_string('viewas', 'gradereport_user');

        $output = html_writer::tag('div', $this->output->render($select), ['class' => 'view_users_selector']);

        return $output;
    }

    /**
     * Renders the user selector trigger element.
     *
     * @param object $course The course object.
     * @param int|null $userid The user ID.
     * @param int|null $groupid The group ID.
     * @return string The raw HTML to render.
     * @throws coding_exception
     */
    public function users_selector(object $course, ?int $userid = null, ?int $groupid = null): string {
        global $PAGE;

        $data = [
            'courseid' => $course->id,
            'groupid' => $groupid ?? 0,
        ];

        $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
        $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
        $showonlyactiveenrol = $showonlyactiveenrol ||
            !has_capability('moodle/course:viewsuspendedusers', context_course::instance($course->id));

        if (!is_null($userid)) {
            if ($userid) {
                $user = core_user::get_user($userid);
                $data['selectedoption'] = [
                    'image' => $this->user_picture($user, array('size' => 40, 'link' => false)),
                    'text' => fullname($user),
                    'additionaltext' => $user->email,
                ];
            } else {
                // Get the total number of users.
                $gui = new graded_users_iterator($course, null, $groupid);
                $gui->require_active_enrolment($showonlyactiveenrol);
                $gui->init();
                $totalusersnum = 0;
                while ($userdata = $gui->next_user()) {
                    $totalusersnum++;
                }
                $gui->close();

                $data['selectedoption'] = [
                    'text' =>  get_string('allusers', 'gradereport_user', $totalusersnum),
                ];
            }
        }

        $PAGE->requires->js_call_amd('gradereport_user/user', 'init');
        return $this->render_from_template('gradereport_user/user_selector', $data);
    }

    /**
     * Renders the group selector trigger element.
     *
     * @param object $course The course object.
     * @return string|null The raw HTML to render.
     */
    public function group_selector(object $course): ?string {
        global $USER, $PAGE;

        // Make sure that group mode is enabled.
        if (!$groupmode = $course->groupmode) {
            return null;
        }

        $label = $groupmode == VISIBLEGROUPS ? get_string('selectgroupsvisible') :
            get_string('selectgroupsseparate');

        $data = [
            'label' => $label,
            'courseid' => $course->id,
        ];

        $context = context_course::instance($course->id);

        if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $context)) {
            $allowedgroups = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
        } else {
            $allowedgroups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
        }

        $activegroup = groups_get_course_group($course, true, $allowedgroups);

        if ($activegroup) {
            $group = groups_get_group($activegroup);
            $data['selectedgroup'] = $group->name;
        } else if ($activegroup === 0) {
            $data['selectedgroup'] = get_string('allparticipants');
        }

        $PAGE->requires->js_call_amd('gradereport_user/group', 'init');
        return $this->render_from_template('gradereport_user/group_selector', $data);
    }

    /**
     * Creates and renders 'view report as' selector element.
     *
     * @param int $userid The selected userid
     * @param int $userview The current view user setting constant
     * @param int $courseid The course ID.
     * @return string|null The raw HTML to render.
     */
    public function view_mode_selector(int $userid, int $userview, int $courseid): ?string {
        global $USER;

        $viewasotheruser = new moodle_url('/grade/report/user/index.php', ['id' => $courseid,
            'userview' => GRADE_REPORT_USER_VIEW_USER]);
        $viewasmyself = new moodle_url('/grade/report/user/index.php', ['id' => $courseid,
            'userview' => GRADE_REPORT_USER_VIEW_SELF]);

        if ($userid != $USER->id) {
            $viewasotheruser->param('userid', $userid);
            $viewasmyself->param('userid', $userid);
        }

        $selectoroptions = [
            $viewasotheruser->out(false) => get_string('otheruser', 'gradereport_user'),
            $viewasmyself->out(false) => get_string('myself', 'gradereport_user')
        ];

        $selectoractiveurl = $userview === GRADE_REPORT_USER_VIEW_USER ? $viewasotheruser : $viewasmyself;

        $viewasselect = new \core\output\select_menu('viewas', $selectoroptions, $selectoractiveurl->out(false));
        $viewasselect->set_label(get_string('viewas', 'gradereport_user'));

        return $this->render_from_template('gradereport_user/view_user_selector',
            $viewasselect->export_for_template($this));
    }

    /**
     * Creates and renders the previous/next user navigation for the user report view.
     *
     * @param graded_users_iterator $gui Objects that is used to iterate over a list of gradable users in the course.
     * @param int $userid The ID of the user which report is currently selected.
     * @param int $courseid The course ID.
     * @return string The raw HTML to render.
     */
    public function user_navigation(graded_users_iterator $gui, int $userid, int $courseid): string {

        while ($userdata = $gui->next_user()) {
            $users[$userdata->user->id] = $userdata->user;
        }
        $gui->close();

        $keysArray = array_keys($users);
        $keyNumber = array_search($userid, $keysArray);

        $navigationdata = [];

        // If the current user is not the first one in the list, find and render the previous user.
        if ($keyNumber !== 0) {
            $previoususer = $users[$keysArray[$keyNumber - 1]];
            $navigationdata['previoususer'] = [
                'name' => fullname($previoususer),
                'url' => (new moodle_url('/grade/report/user/index.php', ['id' => $courseid, 'userid' => $previoususer->id]))
                    ->out(false)
            ];
        }
        // If the current user is not the last one in the list, find and render the last user.
        if ($keyNumber < count($users) - 1) {
            $nextuser = $users[$keysArray[$keyNumber + 1]];
            $navigationdata['nextuser'] = [
                'name' => fullname($nextuser),
                'url' => (new moodle_url('/grade/report/user/index.php', ['id' => $courseid, 'userid' => $nextuser->id]))
                    ->out(false)
            ];
        }

        return $this->render_from_template('gradereport_user/user_navigation', $navigationdata);
    }

    /**
     * Creates and renders a heading for the user report.
     *
     * @param stdClass $user The user object.
     * @param int $courseid The course ID.
     * @param bool $showbuttons Whether to display buttons (message, add to contacts) within the heading.
     * @return string The raw HTML to render.
     */
    public function user_report_heading(stdClass $user, int $courseid, $showbuttons = true) {
        global $USER;

        $headingdata = [
            'userprofileurl' => (new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $courseid]))->out(false),
            'name' => fullname($user),
            'image' => $this->user_picture($user, ['size' => 50, 'link' => false])
        ];

        if ($showbuttons) {
            // Generate the data for the 'message' button.
            $messagelinkattributes = array_map(function($name, $value) {
                return ['name' => $name, 'value' => $value];
            }, array_keys(helper::messageuser_link_params($user->id)), helper::messageuser_link_params($user->id));
            $messagelinkattributes[] = ['name' => 'class', 'value' => 'btn px-0'];

            $headingdata['buttons'][] = [
                'title' => get_string('message', 'message'),
                'url' => (new moodle_url('/message/index.php', ['id' => $user->id]))->out(false),
                'icon' => ['name' => 't/message', 'component' => 'core'],
                'linkattributes' => $messagelinkattributes
            ];
            // Include js for messaging.
            helper::messageuser_requirejs();

            if ($USER->id != $user->id) {
                // Generate the data for the 'contact' button.
                $iscontact = api::is_contact($USER->id, $user->id);
                $contacttitle = $iscontact ? 'removefromyourcontacts' : 'addtoyourcontacts';
                $contacturlaction = $iscontact ? 'removecontact' : 'addcontact';
                $contacticon = $iscontact ? 't/removecontact' : 't/addcontact';

                $togglelinkparams = helper::togglecontact_link_params($user, $iscontact, false);
                $togglecontactlinkattributes = array_map(function($name, $value) {
                    if ($name === 'class') {
                        $value .= ' btn px-0';
                    }
                    return ['name' => $name, 'value' => $value];
                }, array_keys($togglelinkparams), $togglelinkparams);

                $headingdata['buttons'][] = [
                    'title' => get_string($contacttitle, 'message'),
                    'url' => (new moodle_url('/message/index.php', ['user1' => $USER->id, 'user2' => $user->id,
                        $contacturlaction => $user->id, 'sesskey' => sesskey()]))->out(false),
                    'icon' => ['name' => $contacticon, 'component' => 'core'],
                    'linkattributes' => $togglecontactlinkattributes
                ];
                // Include js for contact toggle.
                helper::togglecontact_requirejs();
            }
        }

        return $this->render_from_template('gradereport_user/user_report_heading', $headingdata);
    }
}
