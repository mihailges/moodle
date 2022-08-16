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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/grade/report/lib.php');

/** Taken from OG user report, may be useful later. */
const GRADE_REPORT_USER41_HIDE_HIDDEN = 0;
/** Taken from OG user report, may be useful later. */
const GRADE_REPORT_USER41_HIDE_UNTIL = 1;
/** Taken from OG user report, may be useful later. */
const GRADE_REPORT_USER41_SHOW_HIDDEN = 2;

/** Taken from OG user report, may be useful later. */
const GRADE_REPORT_USER41_VIEW_SELF = 1;
/** Setting the userview preference. */
const GRADE_REPORT_USER41_VIEW_USER = 2;

/**
 * Library for gradereport_user41.
 *
 * @package    gradereport_user41
 * @copyright  2022 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradereport_user41 extends grade_report {

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
    public function process_action($target, $action): void {}

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

        $base = '/grade/report/user41/index.php';

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
        ];
        return $OUTPUT->render_from_template('gradereport_user41/zero_state', $context);
    }

    /**
     * Trigger the grade_report_viewed event
     */
    public function viewed(): void {
        $event = \gradereport_user41\event\grade_report_viewed::create(
            [
                'context' => $this->context,
                'courseid' => $this->courseid,
            ]
        );
        $event->trigger();
    }
}

