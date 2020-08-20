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
        SELECTED_PREDICTIONS_INFO: '[data-region="selected-predictions-info"]'
    },
    ACTIONS: {
        SELECT_ALL_EXISTING: '[data-action="select-all-existing"]',
        CLEAR_ALL: '[data-action="clear-all-selected"]',
    }
};

export let selectedPredictions;

const registerListenerEvents = () => {
    PubSub.subscribe(CheckboxToggleAll.events.checkboxToggled, (data) => {
        handleCheckboxToggle(data);
    });

    document.addEventListener('click',(e) => {
        if (e.target.matches(SELECTORS.ACTIONS.SELECT_ALL_EXISTING)) { // All existing insights are selected.
            const selectedPredictionsInfo = document.querySelector(SELECTORS.CONTAINERS.SELECTED_PREDICTIONS_INFO);
            const allPredictions = JSON.parse(selectedPredictionsInfo.dataset.allpredictionids);
            selectedPredictions[e.target.dataset.togglegroup] = allPredictions;

            const templateData = {
                totalpredictions: allPredictions.length,
                togglegroupname:  e.target.dataset.togglegroup
            };

            renderNotification('report_insights/all_existing_insights_selected', templateData);
        }

        if (e.target.matches(SELECTORS.ACTIONS.CLEAR_ALL)) { // Insight selection is cleared.
            const checkboxToggleGroup = e.target.dataset.togglegroup;
            CheckboxToggleAll.setGroupState(document, checkboxToggleGroup, false);
            selectedPredictions = [];

            clearNotification();
        }
    });
};

const handleCheckboxToggle = (data) => {
    selectedPredictions = [];
    let predictionIds = [];

    if (data.checkedSlaves.length > 0) {
        $.each(data.checkedSlaves, (index, checkedSlave) => {
            const predictionId = $(checkedSlave).val();
            predictionIds.push(predictionId);
        });

        selectedPredictions[data.toggleGroupName] = predictionIds;

        if (data.slaves.length === data.checkedSlaves.length) { // All insights on the page are selected.
            const selectedPredictionsInfo = document.querySelector(SELECTORS.CONTAINERS.SELECTED_PREDICTIONS_INFO);
            const allPredictions = JSON.parse(selectedPredictionsInfo.dataset.allpredictionids);

            const templateData = {
                selectedpredictionscount: data.checkedSlaves.length,
                totalpredictions: allPredictions.length,
                togglegroupname: data.toggleGroupName
            };

            renderNotification('report_insights/all_insights_on_page_selected', templateData);
        } else { // Some insights are selected.
            // Remove and do not display a notification.
            clearNotification();
        }
    } else { // No insights selected.
        // Remove and do not display a notification.
        clearNotification();
    }
};

const renderNotification = async (templateName, templateData) => {
    const selectedInsightsContainer = document.querySelector(SELECTORS.CONTAINERS.SELECTED_PREDICTIONS_INFO);
    const {html, js} = await Templates.renderForPromise(templateName, templateData);
    await Templates.replaceNodeContents(selectedInsightsContainer, html, js);
};

const clearNotification = () => {
    document.querySelector(SELECTORS.CONTAINERS.SELECTED_PREDICTIONS_INFO).innerHTML = '';
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
