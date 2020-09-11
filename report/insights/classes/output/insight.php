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
 * Single insight view page.
 *
 * @package    report_insights
 * @copyright  2017 David Monllao {@link http://www.davidmonllao.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_insights\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Single insight view page.
 *
 * @package    report_insights
 * @copyright  2017 David Monllao {@link http://www.davidmonllao.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class insight implements \renderable, \templatable {

    /**
     * @var \core_analytics\model
     */
    protected $model;

    /**
     * @var \core_analytics\prediction
     */
    protected $prediction;

    /**
     * @var bool
     */
    protected $includedetailsaction = false;

    /**
     * @var \context
     */
    protected $context;

    /**
     * @var bool
     */
    protected $hasbulkactions = false;

    /**
     * Constructor.
     *
     * @param \core_analytics\prediction $prediction
     * @param \core_analytics\model $model
     * @param bool $includedetailsaction
     * @param \context $context
     * @param bool $hasbulkactions
     */
    public function __construct(\core_analytics\prediction $prediction, \core_analytics\model $model, $includedetailsaction = false,
            \context $context, bool $hasbulkactions = false) {

        $this->prediction = $prediction;
        $this->model = $model;
        $this->includedetailsaction = $includedetailsaction;
        $this->context = $context;
        $this->hasbulkactions = $hasbulkactions;
    }

    /**
     * Exports the data.
     *
     * @param \renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output) {
        // Get the prediction data.
        $predictiondata = $this->prediction->get_prediction_data();

        $target = $this->model->get_target();

        $data = new \stdClass();
        $data->predictionid = $predictiondata->id;

        // Sample info (determined by the analyser).
        list($data->sampledescription, $samplerenderable) = $this->model->prediction_sample_description($this->prediction);

        // Sampleimage is a renderable we should pass it to HTML.
        if ($samplerenderable) {
            $data->sampleimage = $output->render($samplerenderable);
        }

        // Prediction info.
        $predictedvalue = $predictiondata->prediction;

        $data->actions = actions_exporter::add_prediction_actions($target, $output, $this->prediction,
            $this->includedetailsaction);

        $toggle = new \core\output\checkbox_toggleall( "insight-bulk-action-{$predictedvalue}", false, [
            'id' => 'id-select-' . $predictiondata->id,
            'name' => 'select-' . $predictiondata->id,
            'value' => $predictiondata->id,
            'label' => get_string('selectprediction', 'report_insights', $data->sampledescription),
            'labelclasses' => 'accesshide',
        ]);
        $data->toggleslave = $output->render($toggle);
        $data->hasbulkactions = $this->hasbulkactions;

        return $data;
    }

    /**
     * Returns display info for the calculated value outcome.
     *
     * @param \core_analytics\calculable $calculable
     * @param float $value
     * @param \renderer_base $output
     * @param string|false $subtype
     * @return array The style as 'success', 'info', 'warning' or 'danger' and pix_icon
     */
    public static function get_calculation_display(\core_analytics\calculable $calculable, $value, $output, $subtype = false) {
        $outcome = $calculable->get_calculation_outcome($value, $subtype);
        switch ($outcome) {
            case \core_analytics\calculable::OUTCOME_NEUTRAL:
                $style = '';
                $text = get_string('outcomeneutral', 'report_insights');
                $icon = 't/check';
                break;
            case \core_analytics\calculable::OUTCOME_VERY_POSITIVE:
                $style = 'success';
                $text = get_string('outcomeverypositive', 'report_insights');
                $icon = 't/approve';
                break;
            case \core_analytics\calculable::OUTCOME_OK:
                $style = 'info';
                $text = get_string('outcomeok', 'report_insights');
                $icon = 't/check';
                break;
            case \core_analytics\calculable::OUTCOME_NEGATIVE:
                $style = 'warning';
                $text = get_string('outcomenegative', 'report_insights');
                $icon = 'i/warning';
                break;
            case \core_analytics\calculable::OUTCOME_VERY_NEGATIVE:
                $style = 'danger';
                $text = get_string('outcomeverynegative', 'report_insights');
                $icon = 'i/warning';
                break;
            default:
                throw new \coding_exception('The outcome returned by ' . get_class($calculable) . '::get_calculation_outcome is ' .
                    'not one of the accepted values. Please use \core_analytics\calculable::OUTCOME_VERY_POSITIVE, ' .
                    '\core_analytics\calculable::OUTCOME_OK, \core_analytics\calculable::OUTCOME_NEGATIVE, ' .
                    '\core_analytics\calculable::OUTCOME_VERY_NEGATIVE or \core_analytics\calculable::OUTCOME_NEUTRAL');
        }
        $icon = new \pix_icon($icon, $text);
        return array($style, $icon->export_for_template($output));
    }
}
