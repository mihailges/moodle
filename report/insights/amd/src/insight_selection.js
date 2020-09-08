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

const Actions = {
    selectAll: 'select-all',
    clearAll: 'clear-selection'
};

const getSelectors = (toggleGroup) => {
    return {
        containers: {
            insightSelection: `[data-region="insight-selection"][data-togglegroup="${toggleGroup}"]`,
            get insightSelectionInfoSpan() {
                return `${this.insightSelection} [data-region="insight-selection-info"] span`;
            },
            get insightSelectionActionsList() {
                return `${this.insightSelection} [data-region="insight-selection-actions"] ul`;
            }
        },
        elements: {
            insightSlaveCheckbox: `input[type="checkbox"][data-toggle="slave"][data-togglegroup="${toggleGroup}"]`,
            actionToggle: `[data-toggle="action"][data-action="toggle"][data-togglegroup="${toggleGroup}"]`,
            selectAllInsights: `[data-toggle="action"][data-action="${Actions.selectAll}"][data-togglegroup="${toggleGroup}"]`,
            clearSelectedInsights: `[data-toggle="action"][data-action="${Actions.clearAll}"][data-togglegroup="${toggleGroup}"]`,
        }
    };
};

/**
 * Helper method that returns the local storage key of the stored selected predictions.
 *
 * @method selectedPredictionsStorageKey
 * @param {String} toggleGroup The name of the toggle group
 */
const selectedPredictionsStorageKey = (toggleGroup) => {
    // const insightSelectionContainer = document.querySelector(getSelectors(toggleGroup).containers.insightSelection);
    // return `${insightSelectionContainer.dataset.uniqueid}-${toggleGroup}`;
    return `${document.body.id}-${toggleGroup}`;
};

/**
 * Stores the IDs of the selected predictions in the local storage.
 *
 * @method setSelectedPredictions
 * @param {Array} selectedPredictions The array containing the IDs of the selected predictions
 * @param {String} toggleGroup The name of the toggle group
 */
const setSelectedPredictions = (selectedPredictions, toggleGroup) => {
    LocalStorage.set(selectedPredictionsStorageKey(toggleGroup), JSON.stringify(selectedPredictions));
};

/**
 * Fetches all selected predictions from the local storage and returns an array containing the IDs of the predictions.
 *
 * @method getSelectedPredictions
 * @param {String} toggleGroup The name of the toggle group
 * @return {Array}
 */
export const getSelectedPredictions = (toggleGroup) => {
    const selectedPredictions = JSON.parse(LocalStorage.get(selectedPredictionsStorageKey(toggleGroup)));
    return selectedPredictions ? selectedPredictions : [];
};

/**
 * Returns an array containing the IDs of all existing predictions.
 *
 * @method getAllPredictions
 * @param {String} toggleGroup The name of the toggle group
 * @return {Array}
 */
const getAllPredictions = (toggleGroup) => {
    const insightSelectionContainer = document.querySelector(getSelectors(toggleGroup).containers.insightSelection);
    return JSON.parse(insightSelectionContainer.dataset.allpredictions);
};

/**
 * Register insight selection related event listeners.
 *
 * @method registerListenerEvents
 * @param {String} toggleGroup The name of the toggle group
 */
const registerListenerEvents = (toggleGroup) => {
    // Subscribe to the 'checkboxToggled' event.
    PubSub.subscribe(CheckboxToggleAll.events.checkboxToggled, (data) => {
        // If the captured event is related to the given toggle group, handle the event data.
        if (data.toggleGroupName === toggleGroup) {
            handlePredictionToggle(data);
        }
    });

    document.addEventListener('click', async (e) => {
        // All existing insights are selected.

        if (e.target.matches(getSelectors(toggleGroup).elements.selectAllInsights)) {
            e.preventDefault();
            const allPredictions = getAllPredictions(toggleGroup);
            // Set all existing predictions from a given toggle group as selected.
            setSelectedPredictions(allPredictions, toggleGroup);
            // Check all prediction checkboxes on the page from a given toggle group.
            CheckboxToggleAll.setGroupState(document, toggleGroup, true);

            renderInsightSelectionState(allPredictions, toggleGroup);
        }

        // Insight selection is cleared.
        if (e.target.matches(getSelectors(toggleGroup).elements.clearSelectedInsights)) {
            e.preventDefault();
            // Reset all selected insights.
            setSelectedPredictions([], toggleGroup);
            // Uncheck all prediction checkboxes on the page from a given toggle group.
            CheckboxToggleAll.setGroupState(document, toggleGroup, false);

            renderInsightSelectionState([], toggleGroup);
        }
    });
};

/**
 * Method that handles the change of state of the prediction selection based on the data returned by the
 * 'checkboxToggled' event.
 *
 * @method handlePredictionToggle
 * @param {Object} data The data returned by the 'checkboxToggled' event
 */
const handlePredictionToggle = (data) => {
    // Array containing all currently checked prediction (slave) checkbox elements on the page.
    const checkedPredictionsOnPage = data.checkedSlaves.toArray();
    // Array containing all prediction (slave) checkbox elements on the current page.
    const predictionsOnPage = data.slaves.toArray();
    // Get all previously selected predictions in the given toggle group.
    let selectedPredictions = getSelectedPredictions(data.toggleGroupName);

    // Loop through each prediction checkbox element on the current page.
    $.each(predictionsOnPage, (index, prediction) => {
        const predictionId = parseInt($(prediction).val());
        // Whether the current prediction checkbox element is currently selected.
        const isSelected = checkedPredictionsOnPage.indexOf(prediction) > -1;
        // Whether the current prediction checkbox element was previously selected.
        const wasSelected = selectedPredictions.length > 0 &&
            selectedPredictions.indexOf(predictionId) > -1;

        if (isSelected && !wasSelected) { // The prediction checkbox element is selected and was not previously.
            // The current prediction was selected in the latest change of state, therefore add the current prediction
            // to the selected predictions.
            selectedPredictions.push(predictionId);
        } else if (!isSelected && wasSelected) { // The prediction checkbox element is not selected and it was previously.
            // The current predictions was unselected in the latest change of state, therefore remove the current
            // prediction from the selected predictions.
            selectedPredictions.splice(selectedPredictions.indexOf(predictionId), 1);
        }
    });

    setSelectedPredictions(selectedPredictions, data.toggleGroupName);

    renderInsightSelectionState(selectedPredictions, data.toggleGroupName);
};

/**
 * Render notification.
 *
 * @method renderNotification
 * @param {Object} notificationData The object with the required data for the notification template.
 */
const renderInsightSelectionState = (selectedPredictions, toggleGroup) => {
    renderInsightSelectionActionsList(selectedPredictions, toggleGroup);
    renderInsightSelectionInfo(selectedPredictions, toggleGroup);
};

/**
 * Helper method that returns the proper template data depending on the selected predictions.
 *
 * @method getTemplateData
 * @param {Array} selectedPredictions The array containing the IDs of the selected predictions
 * @param {String} toggleGroup The name of the toggle group
 */
const renderInsightSelectionActionsList = async (selectedPredictions, toggleGroup) => {
    let actions = [];
    // Get the IDs of all existing predictions.
    const allPredictions = getAllPredictions(toggleGroup);

    if (selectedPredictions.length > 0) { // There is at least one selected prediction.
        // Display the 'Clear all' option in the actions list.
        actions.push({
            action: Actions.clearAll,
            togglegroup: toggleGroup,
            text: await getString('clearselection', 'report_insights')
        });

        if (selectedPredictions.length !== allPredictions.length) { // There are still some unselected predictions.
            // Display the 'Select all' option in the actions list.
            actions.unshift({
                action: Actions.selectAll,
                togglegroup: toggleGroup,
                text: await getString('selectall', 'core', allPredictions.length)
            });
        }
    } else { // There aren't any selected predictions.
        // Display the 'Select all' option in the actions list.
        actions.push({
            action: Actions.selectAll,
            togglegroup: toggleGroup,
            text: await getString('selectall', 'core', allPredictions.length)
        });
    }

    const {html, js} = await Templates.renderForPromise('report_insights/insight_selection_actions_list',
        {actions: actions});
    await Templates.replaceNodeContents(getSelectors(toggleGroup).containers.insightSelectionActionsList, html, js);
};

const renderInsightSelectionInfo = (selectedPredictions, toggleGroup) => {
    const allPredictions = getAllPredictions(toggleGroup);
    const insightSelectionInfoSpanElements = document.querySelectorAll(
        getSelectors(toggleGroup).containers.insightSelectionInfoSpan);
    const strData = {
        nselected: selectedPredictions.length,
        ntotal: allPredictions.length
    };

    insightSelectionInfoSpanElements.forEach(async (insightSelectionInfoSpanElement) => {
        insightSelectionInfoSpanElement.innerHTML = await getString('insightsselected', 'report_insights', strData);
    });
};

/**
 * Method that pre-sets the state of the prediction selection elements on page init.
 *
 * @method setStateOnInit
 * @param {String} toggleGroup The name of the toggle group
 */
const setStateOnInit = (toggleGroup) => {
    // Get all selected predictions in the given toggle group.
    const selectedPredictions = getSelectedPredictions(toggleGroup);
    // Get all prediction (slave) checkbox elements on the current page.
    const predictionCheckboxElements = document.querySelectorAll(getSelectors(toggleGroup).elements.insightSlaveCheckbox);
    let selectedPredictionsOnPage = [];

    // If at least one prediction has been selected in the given toggle group, enable every existing action element
    // related to that toggle group.
    if (selectedPredictions.length > 0) {
        const actionToggleElements = document.querySelectorAll(getSelectors(toggleGroup).elements.actionToggle);
        actionToggleElements.forEach(actionToggleElement => {
            actionToggleElement.removeAttribute('disabled');
        });
    }

    // Loop through each prediction (slave) checkbox element from the current page.
    predictionCheckboxElements.forEach(predictionCheckboxElement => {
        const predictionId = parseInt(predictionCheckboxElement.value);
        if (selectedPredictions.indexOf(predictionId) > -1) {
            selectedPredictionsOnPage.push(predictionCheckboxElement);
        }
    });

    if (predictionCheckboxElements.length === selectedPredictionsOnPage.length) {
        CheckboxToggleAll.setGroupState(document, toggleGroup, true);
    } else {
        selectedPredictionsOnPage.map(selectedPrediction => {
            selectedPrediction.checked = true;
        });
    }

    renderInsightSelectionState(selectedPredictions, toggleGroup);
};

/**
 * Set up the actions.
 *
 * @method init
 */
export const init = (toggleGroup) => {
    setStateOnInit(toggleGroup);
    registerListenerEvents(toggleGroup);
};
