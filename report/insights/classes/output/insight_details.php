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
 * Insight details view page.
 *
 * @package    report_insights
 * @copyright  2020 Mihail Geshoski {@link http://www.davidmonllao.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_insights\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Insight details view page.
 *
 * @package    report_insights
 * @copyright  2020 Mihail Geshoski {@link http://www.davidmonllao.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class insight_details implements \renderable, \templatable {

    /**
     * Constructor.
     *
     * @param \core_analytics\prediction $prediction
     * @param \core_analytics\model $model
     * @param bool $includedetailsaction
     * @param \context $context
     */
    public function __construct(\core_analytics\prediction $prediction, \core_analytics\model $model,
            $includedetailsaction = false, \context $context) {
        $this->prediction = $prediction;
        $this->model = $model;
        $this->includedetailsaction = $includedetailsaction;
        $this->context = $context;
    }

    /**
     * Exports the data.
     *
     * @param \renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output) {
        $predictiondata = $this->prediction->get_prediction_data();
        $target = $this->model->get_target();

        $data = new \stdClass();
        $data->modelid = $this->model->get_id();
        $data->contextid = $this->context->id;

        $targetname = $target->get_name();
        $data->insightname = format_string($targetname);

        $targetinfostr = $targetname->get_identifier() . 'info';
        if (get_string_manager()->string_exists($targetinfostr, $targetname->get_component())) {
            $data->insightdescription = get_string($targetinfostr, $targetname->get_component());
        }

        $data->showpredictionheading = true;
        if (!$target->is_linear()) {
            $nclasses = count($target::get_classes());
            $nignoredclasses = count($target->ignored_predicted_classes());
            if ($nclasses - $nignoredclasses <= 1) {
                // Hide the prediction heading if there is only 1 class displayed. Otherwise it is redundant with the insight name.
                $data->showpredictionheading = false;
            }
        }

        // Get the details.
        $data->timecreated = userdate($predictiondata->timecreated);
        $data->timerange = '';

        if (!empty($predictiondata->timestart) && !empty($predictiondata->timeend)) {
            $timerange = new \stdClass();
            $timerange->timestart = userdate($predictiondata->timestart);
            $timerange->timeend = userdate($predictiondata->timeend);
            $data->timerange = get_string('timerangewithdata', 'report_insights', $timerange);
        }

        // Prediction info.
        $predictedvalue = $predictiondata->prediction;

        $data->predictiondisplayvalue = $target->get_display_value($predictedvalue);
        list($data->style, $data->outcomeicon) = insight::get_calculation_display($target,
            floatval($predictedvalue), $output);

        // Calculated indicators values.
        $data->calculations = array();
        $calculations = $this->prediction->get_calculations();
        foreach ($calculations as $calculation) {
            // Hook for indicators with extra features that should not be displayed (e.g. discrete indicators).
            if (!$calculation->indicator->should_be_displayed($calculation->value, $calculation->subtype)) {
                continue;
            }

            if ($calculation->value === null) {
                // We don't show values that could not be calculated.
                continue;
            }

            $obj = new \stdClass();
            $obj->name = call_user_func(array($calculation->indicator, 'get_name'));
            $obj->displayvalue = $calculation->indicator->get_display_value($calculation->value, $calculation->subtype);
            list($obj->style, $obj->outcomeicon) = insight::get_calculation_display($calculation->indicator,
                floatval($calculation->value), $output, $calculation->subtype);

            $identifier = $calculation->indicator->get_name()->get_identifier() . 'def';
            $component = $calculation->indicator->get_name()->get_component();
            if (get_string_manager()->string_exists($identifier, $component)) {
                $obj->outcomehelp = (new \help_icon($identifier, $component))->export_for_template($output);
            }
            $data->calculations[] = $obj;
        }

        if (empty($data->calculations)) {
            $data->nocalculations = (object)array(
                'message' => get_string('nodetailsavailable', 'report_insights'),
                'closebutton' => false
            );
        }

        $data->bulkactions = actions_exporter::add_bulk_actions($target, $output, [$this->prediction], $this->context);

        $insightrenderable = new \report_insights\output\insight($this->prediction, $this->model, true,
            $this->context, true);
        $data->insight[] = $insightrenderable->export_for_template($output);

        // We need it to automatically enable the bulk action buttons in report/insights/prediction.php.
        $togglegroup = "insight-bulk-action-{$predictedvalue}";
        $toggleall = new \core\output\checkbox_toggleall($togglegroup, true, [
            'id' => 'id-toggle-all-' . $predictedvalue,
            'name' => 'toggle-all-' . $predictedvalue,
            'classes' => 'hidden',
            'label' => get_string('selectall'),
            'labelclasses' => 'sr-only',
            'checked' => false
        ]);
        $data->hiddencheckboxtoggleall = $output->render($toggleall);

        $uniqieid = md5($data->contextid . $data->modelid . $predictiondata->id . $togglegroup);
        $insightselection = new \report_insights\output\insight_selection(
            $uniqieid, $togglegroup, [$predictiondata->id], false);
        $data->insightselection = $output->render($insightselection);

        return $data;
    }

    /**
     * Model getter.
     *
     * @return \core_analytics\model
     */
    public function get_model(): \core_analytics\model {
        return $this->model;
    }
}
