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

namespace gradereport_singleview\report;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/grade/report/lib.php');
use grade_report;
use moodle_url;

/**
 * The report for grade items.
 *
 * @package    gradereport_singleview
 * @copyright  2022 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class singleview_grade extends grade_report {

    /**
     * Return the list of valid screens, used to validate the input.
     *
     * @return array List of screens.
     */
    public static function valid_screens(): array {
        // This is a list of all the known classes representing a screen in this plugin.
        return ['user'];
    }

    /**
     * Process data from a form submission. Delegated to the current screen.
     *
     * @param array $data The data from the form
     * @return null|array List of warnings
     */
    public function process_data($data): ?array {
        if (has_capability('moodle/grade:edit', $this->context)) {
            return $this->screen->process($data);
        }
        return null;
    }

    /**
     * Unused - abstract function declared in the parent class.
     *
     * @param string $target
     * @param string $action
     */
    public function process_action($target, $action): void {
    }

    /**
     * Constructor for this report. Creates the appropriate screen class based on itemtype.
     *
     * @param int $courseid The course id.
     * @param object $gpr grade plugin return tracking object
     * @param context_course $context
     * @param string $itemtype Should be user, select or grade
     * @param int $itemid The id of the user or grade item
     */
    public function __construct($courseid, $gpr, $context, $itemtype, $itemid) {
        parent::__construct($courseid, $gpr, $context);

        $base = '/grade/report/singleview/index.php';

        $idparams = ['id' => $courseid];

        $this->baseurl = new moodle_url($base, $idparams);

        $this->pbarurl = new moodle_url($base, $idparams + [
                'item' => $itemtype,
                'itemid' => $itemid
            ]);
    }

    /**
     * Build the html for the screen.
     * @return string HTML to display
     */
    public function output(): string {
        global $OUTPUT, $COURSE;
        $context = [
            'courseid' => $COURSE->id,
            'imglink' => new \moodle_url('/pix/f/clip-353 1.png'),
            'userpage' => new \moodle_url('/grade/report/singleview/user.php', ['id' => $COURSE->id]),
            'userpageactive' => false,
            'gradepagepage' => new \moodle_url('/grade/report/singleview/grade.php', ['id' => $COURSE->id]),
            'gradepagepageactive' => true,
        ];
        return $OUTPUT->render_from_template('gradereport_singleview/zero_state_grade', $context);
    }

    /**
     * Trigger the grade_report_viewed event
     */
    public function viewed(): void {
        global $USER;
        $event = \gradereport_singleview\event\grade_report_viewed::create(
            [
                'context' => $this->context,
                'courseid' => $this->courseid,
                'relateduserid' => $USER->id,
            ]
        );
        $event->trigger();
    }
}
