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
 * @module     report_insights/insights
 * @package    report_insights
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import * as PubSub from 'core/pubsub';
import CheckboxToggleAll from 'core/checkbox-toggleall';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
import LocalStorage from 'core/localstorage';

const SELECTORS = {
    CONTAINERS: {
        SELECTED_PREDICTIONS_INFO: '[data-region="selected-predictions-info"]',
        PREDICTION_CHECKBOX_ELEMENT: 'input[type="checkbox"][data-toggle="slave"][data-togglegroup="insight-bulk-action-1.00"]'
    },
    ACTIONS: {
        SELECT_ALL_EXISTING: '[data-action="select-all-existing"]',
        CLEAR_ALL: '[data-action="clear-all-selected"]',
    }
};

const toggleGroup = 'insight-bulk-action-1.00';

export const getSelectedPredictions = () => {
    const selectedPredictions = JSON.parse(LocalStorage.get('selectedPredictions'));
    return selectedPredictions ? selectedPredictions : [];
};

export const setSelectedPredictions = (selectedPredictions) => {
    LocalStorage.set('selectedPredictions', JSON.stringify(selectedPredictions));
};

const getAllPredictions = () => {
    const selectedPredictionsInfo = document.querySelector(SELECTORS.CONTAINERS.SELECTED_PREDICTIONS_INFO);
    return JSON.parse(selectedPredictionsInfo.dataset.allpredictionids);
};

/**
 * Register related event listeners.
 *
 * @method registerListenerEvents
 */
const registerListenerEvents = () => {
    PubSub.subscribe(CheckboxToggleAll.events.checkboxToggled, (data) => {
        if (data.toggleGroupName === toggleGroup) {
            handlePredictionToggle(data);
        }
    });

    document.addEventListener('click', async (e) => {

        // All existing insights are selected.
        if (e.target.matches(SELECTORS.ACTIONS.SELECT_ALL_EXISTING)) {
            const allPredictions = getAllPredictions();

            setSelectedPredictions(allPredictions);
            CheckboxToggleAll.setGroupState(document, toggleGroup, true);

            renderNotification(allPredictions);
        }

        // Insight selection is cleared.
        if (e.target.matches(SELECTORS.ACTIONS.CLEAR_ALL)) {
            setSelectedPredictions([]);
            CheckboxToggleAll.setGroupState(document, toggleGroup, false);

            renderNotification([]);
        }
    });
};

/**
 * Method that handles the selection/deselection of predictions based on the 'checkboxToggled' event and
 * displays a notification depending on the number of selected insights.
 *
 * @method handlePredictionToggle
 * @param {Object} data The data returned by the 'checkboxToggled' event.
 */
const handlePredictionToggle = async (data) => {
    const checkedSlaves = data.checkedSlaves.toArray();
    const slaves = data.slaves.toArray();
    let selectedPredictions = getSelectedPredictions();

    $.each(slaves, (index, slave) => {
        const predictionId = parseInt($(slave).val());
        const isSelected = checkedSlaves.indexOf(slave) > -1;
        const wasSelected = selectedPredictions.length > 0 &&
            selectedPredictions.indexOf(predictionId) > -1;

        if (isSelected && !wasSelected) {
            selectedPredictions.push(predictionId);
        } else if (!isSelected && wasSelected) {
            selectedPredictions.splice(selectedPredictions.indexOf(predictionId), 1);
        }
    });

    setSelectedPredictions(selectedPredictions);

    renderNotification(selectedPredictions);
};

/**
 * Render notification.
 *
 * @method renderNotification
 * @param {Object} notificationData The object with the required data for the notification template.
 */
const renderNotification = async (selectedPredictions) => {
    const notificationData = await getTemplateData(selectedPredictions);
    console.log(notificationData);
    const {html, js} = await Templates.renderForPromise('report_insights/insights_selected', notificationData);
    await Templates.replaceNodeContents(SELECTORS.CONTAINERS.SELECTED_PREDICTIONS_INFO, html, js);
};

/**
 * Remove notification.
 *
 * @method clearNotification
 */
const clearNotification = () => {
    [...document.querySelectorAll(SELECTORS.CONTAINERS.SELECTED_PREDICTIONS_INFO)].map(container => container.innerHTML = '');
};

const togglePredictionsOnLoad = () => {
    const selectedPredictions = getSelectedPredictions();
    const predictionCheckboxElements = document.querySelectorAll(SELECTORS.CONTAINERS.PREDICTION_CHECKBOX_ELEMENT);

    predictionCheckboxElements.forEach(predictionCheckboxElement => {
        const predictionId = parseInt(predictionCheckboxElement.value);
        if (selectedPredictions.indexOf(predictionId) > -1) {
            predictionCheckboxElement.checked = true;
            predictionCheckboxElement.dispatchEvent(new Event('change'));
        }
    });

    renderNotification(selectedPredictions);
};

const getTemplateData = async (selectedPredictions) => {
    let actions = [];
    const allPredictions = getAllPredictions();

    if (selectedPredictions.length > 0) {
        actions.push({
            action: 'clear-all-selected',
            togglegroup: toggleGroup,
            text: await getString('clearselection', 'report_insights')
        });

        if (selectedPredictions.length !== allPredictions.length) {
            actions.unshift({
                action: 'select-all-existing',
                togglegroup: toggleGroup,
                text: await getString('selectallinsights', 'report_insights', allPredictions.length)
            });
        }
    } else {
        actions.push({
            action: 'select-all-existing',
            togglegroup: toggleGroup,
            text: await getString('selectallinsights', 'report_insights', allPredictions.length)
        });
    }

    const templateData = {
        insightsselected: await getString('insightsselected', 'report_insights', selectedPredictions.length),
        actions: actions
    };

    return templateData;
};

/**
 * Set up the actions.
 *
 * @method init
 */
export const init = () => {
    registerListenerEvents();
    togglePredictionsOnLoad();
};
