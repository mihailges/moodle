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
 * Insight selection view.
 *
 * @package    report_insights
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_insights\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Insight selection view.
 *
 * @package    report_insights
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class insight_selection implements \renderable, \templatable {

    /**
     * @var string
     */
    protected $uniqueid;

    /**
     * @var string
     */
    protected $togglegroup;

    /**
     * @var array
     */
    protected $allpredictionids;

    /**
     * @var bool
     */
    protected $displayactionselement;

    /**
     * Constructor.
     *
     * @param string $uniqueid
     * @param string $togglegroup
     * @param array $allpredictionids
     * @param bool $displayactionselement
     */
    public function __construct(string $uniqueid, string $togglegroup, array $allpredictionids,
            bool $displayactionselement = true) {
        $this->uniqueid = $uniqueid;
        $this->togglegroup = $togglegroup;
        $this->allpredictionids = $allpredictionids;
        $this->displayactionselement = $displayactionselement;
    }

    /**
     * Exports the data.
     *
     * @param \renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE;

        // Get the prediction data.
        $data = new \stdClass();
        $data->uniqueid = $this->uniqueid;
        $data->togglegroup = $this->togglegroup;
        $data->allinsights = json_encode($this->allpredictionids);
        $data->displayactionselement = $this->displayactionselement;

        $PAGE->requires->js_call_amd('report_insights/insight_selection', 'init', [$this->togglegroup]);

        return $data;
    }
}
