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
 * @module     report_insights/insight_selection
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

const ACTIONS = {
    SELECT_ALL: 'select-all',
    CLEAR_ALL: 'clear-selection'
};

const getDataSelector = (name, value) => {
    return `[data-${name}="${value}"]`;
};

const getPredictionCheckboxSelector = (toggleGroup) => {
    return `input[type="checkbox"]${getDataSelector('toggle', 'slave')}${getDataSelector('togglegroup', toggleGroup)}`;
};

const getInsightSelectionContainerSelector = (toggleGroup) => {
    return `${getDataSelector('region', 'insight-selection')}${getDataSelector('togglegroup', toggleGroup)}`;
};

const getActionElementSelector = (action, toggleGroup) => {
    return `${getDataSelector('action', action)}${getDataSelector('togglegroup', toggleGroup)}`;
};

export const getSelectedPredictions = () => {
    const selectedPredictions = JSON.parse(LocalStorage.get('selectedPredictions'));
    return selectedPredictions ? selectedPredictions : [];
};

export const setSelectedPredictions = (selectedPredictions) => {
    LocalStorage.set('selectedPredictions', JSON.stringify(selectedPredictions));
};

const getAllPredictions = (toggleGroup) => {
    const insightSelectionContainer = document.querySelector(getInsightSelectionContainerSelector(toggleGroup));
    return JSON.parse(insightSelectionContainer.dataset.allpredictions);
};

/**
 * Register related event listeners.
 *
 * @method registerListenerEvents
 */
const registerListenerEvents = (toggleGroup) => {
    PubSub.subscribe(CheckboxToggleAll.events.checkboxToggled, (data) => {
        if (data.toggleGroupName === toggleGroup) {
            handlePredictionToggle(data);
        }
    });

    document.addEventListener('click', async (e) => {

        // All existing insights are selected.
        if (e.target.matches(getActionElementSelector(ACTIONS.SELECT_ALL, toggleGroup))) {
            const allPredictions = getAllPredictions(toggleGroup);

            setSelectedPredictions(allPredictions);
            CheckboxToggleAll.setGroupState(document, toggleGroup, true);

            renderNotification(allPredictions, toggleGroup);
        }

        // Insight selection is cleared.
        if (e.target.matches(getActionElementSelector(ACTIONS.CLEAR_ALL, toggleGroup))) {
            setSelectedPredictions([]);
            CheckboxToggleAll.setGroupState(document, toggleGroup, false);

            renderNotification([], toggleGroup);
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

    renderNotification(selectedPredictions, data.toggleGroupName);
};

/**
 * Render notification.
 *
 * @method renderNotification
 * @param {Object} notificationData The object with the required data for the notification template.
 */
const renderNotification = async (selectedPredictions, toggleGroup) => {
    const notificationData = await getTemplateData(selectedPredictions, toggleGroup);
    const {html, js} = await Templates.renderForPromise('report_insights/insights_selected', notificationData);
    await Templates.replaceNodeContents(getInsightSelectionContainerSelector(toggleGroup), html, js);
};

/**
 * Remove notification.
 *
 * @method clearNotification
 */
const clearNotification = (toggleGroup) => {
    [...document.querySelectorAll(getInsightSelectionContainerSelector(toggleGroup))].map(container => container.innerHTML = '');
};

const togglePredictionsOnLoad = (toggleGroup) => {
    const selectedPredictions = getSelectedPredictions();
    const predictionCheckboxElements = document.querySelectorAll(getPredictionCheckboxSelector(toggleGroup));
    let selectedPredictionsOnPage = [];

    predictionCheckboxElements.forEach(predictionCheckboxElement => {
        const predictionId = parseInt(predictionCheckboxElement.value);
        if (selectedPredictions.indexOf(predictionId) > -1) {
            selectedPredictionsOnPage.push(predictionCheckboxElement);
        }
    });

    if (predictionCheckboxElements.length === selectedPredictionsOnPage.length) {
        CheckboxToggleAll.setGroupState(document, toggleGroup, true);
    } else {
        selectedPredictionsOnPage.map(selectedPrediction => selectedPrediction.checked = true);
    }

    renderNotification(selectedPredictions, toggleGroup);
};

const getTemplateData = async (selectedPredictions, toggleGroup) => {
    let actions = [];
    const allPredictions = getAllPredictions(toggleGroup);

    if (selectedPredictions.length > 0) {
        actions.push({
            action: ACTIONS.CLEAR_ALL,
            togglegroup: toggleGroup,
            text: await getString('clearselection', 'report_insights')
        });

        if (selectedPredictions.length !== allPredictions.length) {
            actions.unshift({
                action: ACTIONS.SELECT_ALL,
                togglegroup: toggleGroup,
                text: await getString('selectallinsights', 'report_insights', allPredictions.length)
            });
        }
    } else {
        actions.push({
            action: ACTIONS.SELECT_ALL,
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
export const init = (toggleGroup) => {
    registerListenerEvents(toggleGroup);
    togglePredictionsOnLoad(toggleGroup);
};
