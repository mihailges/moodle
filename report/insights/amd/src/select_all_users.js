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
 *
 * @module     report_insights/select_all_users
 * @package    report_insights
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import * as PubSub from 'core/pubsub';
import CheckboxToggleAll from 'core/checkbox-toggleall';
import Templates from 'core/templates';

const SELECTORS = {
    CONTAINERS: {
        SELECTED_PREDICTIONS_INFO: '[data-region="selected-predictions-info"]',
        PREDICTIONS_TABLE: '.insights-list',
        PREDICTIONS_TABLE_ROW: 'tr[data-prediction-id]',
        PREDICTIONS_INPUT_CHECKBOX: '.insight-checkbox-cell input[type="checkbox"]'
    },
    ACTIONS: {
        SELECT_ALL_EXISTING: '[data-action="select-all-existing"]',
        CLEAR_ALL: '[data-action="clear-all-selected"]',
    }
};

export const NONESELECTED = 0;
export const PARTIALLYSELECTED = 1;
export const ALLONPAGESELECTED = 2;
export const ALLEXISTINGSELECTED = 3;

export let selectedPredictions;
export let selectedStatus = NONESELECTED;

const registerListenerEvents = () => {
    PubSub.subscribe(CheckboxToggleAll.events.checkboxToggled, (data) => {
        handleCheckboxToggle(data);
    });

    document.addEventListener('click',(e) => {
        let templateData = {};

        if (e.target.matches(SELECTORS.ACTIONS.SELECT_ALL_EXISTING)) {
            const selectedPredictionsInfo = document.querySelector(SELECTORS.CONTAINERS.SELECTED_PREDICTIONS_INFO);
            selectedPredictions = JSON.parse(selectedPredictionsInfo.dataset.allpredictionids);
            selectedStatus = ALLEXISTINGSELECTED;

            templateData = {
                total: 1000
            };
            renderNotification(templateData);
        }

        if (e.target.matches(SELECTORS.ACTIONS.CLEAR_ALL)) {
            selectedPredictions = [];
            selectedStatus = NONESELECTED;
            renderNotification(templateData);
        }
    });
};

const handleCheckboxToggle = (data) => {
    let templateData = {};
    selectedPredictions = [];

    if (data.checkedSlaves.length == 0) {
        selectedStatus = NONESELECTED;
    } else {
        $.each(data.checkedSlaves, (index, checkedSlave) => {
            const predictionId = $(checkedSlave).closest(SELECTORS.CONTAINERS.PREDICTIONS_TABLE_ROW).data('prediction-id');
            selectedPredictions.push(predictionId);
        });

        if (data.slaves.length == data.checkedSlaves.length) {
            selectedStatus = ALLONPAGESELECTED;
            templateData = {
                selectednumber: data.checkedSlaves.length,
                total: 1000
            };
        } else {
            selectedStatus = PARTIALLYSELECTED;
            templateData = {
                selectednumber: data.checkedSlaves.length,
            };
        }
    }

    renderNotification(templateData);
};


const renderNotification = async (templateData) => {
    let templateName;

    switch (selectedStatus) {
        case PARTIALLYSELECTED:
            templateName = 'report_insights/insights_partially_selected';
            break;
        case ALLONPAGESELECTED:
            templateName = 'report_insights/all_insights_on_page_selected';
            break;
        case ALLEXISTINGSELECTED:
            templateName = 'report_insights/all_existing_insights_selected';
            break;
        default:
            templateName = '';
    }

    const selectedInsightsContainer = document.querySelector(SELECTORS.CONTAINERS.SELECTED_PREDICTIONS_INFO);

    if (templateName.length > 0) {
        const {html, js} = await Templates.renderForPromise(templateName, templateData);
        await Templates.replaceNodeContents(selectedInsightsContainer, html, js);
    } else {
        selectedInsightsContainer.innerHTML = '';
        // TODO: Uncheck checkboxes after 'Clear all selected' action.
        // const insightsTable = document.querySelector(SELECTORS.CONTAINERS.PREDICTIONS_TABLE);
        // const checkboxToggleGroup = document.querySelectorAll(SELECTORS.CONTAINERS.PREDICTIONS_INPUT_CHECKBOX)[0].dataset.togglegroup;
        // console.log(insightsTable);
        // console.log(checkboxToggleGroup);
        // CheckboxToggleAll.setGroupState(insightsTable, checkboxToggleGroup, false);
    }
};

/**
 * Fetch all the information on modules we'll need in the activity chooser.
 *
 * @method init
 * @param {Number} courseid What course to fetch the data for
 * @param {Number} sectionid What section to fetch the data for
 * @return {object} jQuery promise
 */
export const init = () => {
    registerListenerEvents();
};
