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
 * Module that enables selection of insights across different pages and also bulk selection of all existing insights.
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

/**
 * Method that returns the selectors in the given toggle group.
 *
 * @method getSelectors
 * @return {Object}
 */
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
 * Method that returns the insight selection unique ID.
 *
 * @method getUniqueId
 * @param {String} toggleGroup The name of the toggle group
 */
const getUniqueId = (toggleGroup) => {
    const insightSelectionContainer = document.querySelector(getSelectors(toggleGroup).containers.insightSelection);
    return `${insightSelectionContainer.dataset.uniqueid}`;
};

/**
 * Stores the IDs of the selected insights in the local storage.
 *
 * @method setSelectedInsights
 * @param {Array} selectedInsights The array containing the IDs of the selected insights
 * @param {String} toggleGroup The name of the toggle group
 */
export const setSelectedInsights = (selectedInsights, toggleGroup) => {
    LocalStorage.set(getUniqueId(toggleGroup), JSON.stringify(selectedInsights));
};

/**
 * Fetches all selected insights from the local storage and returns an array containing the IDs of the insights.
 *
 * @method getSelectedInsights
 * @param {String} toggleGroup The name of the toggle group
 * @return {Array}
 */
export const getSelectedInsights = (toggleGroup) => {
    const selectedInsights = JSON.parse(LocalStorage.get(getUniqueId(toggleGroup)));
    return selectedInsights ? selectedInsights : [];
};

/**
 * Returns an array containing the IDs of all existing insights.
 *
 * @method getAllInsights
 * @param {String} toggleGroup The name of the toggle group
 * @return {Array}
 */
const getAllInsights = (toggleGroup) => {
    const insightSelectionContainer = document.querySelector(getSelectors(toggleGroup).containers.insightSelection);
    return JSON.parse(insightSelectionContainer.dataset.allinsights);
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
            handleInsightToggle(data);
        }
    });

    document.addEventListener('click', async (e) => {
        // All existing insights are selected.
        if (e.target.matches(getSelectors(toggleGroup).elements.selectAllInsights)) {
            e.preventDefault();
            const allInsights = getAllInsights(toggleGroup);
            // Set all existing insights from a given toggle group as selected.
            setSelectedInsights(allInsights, toggleGroup);
            // Check all insight checkboxes on the page from a given toggle group.
            CheckboxToggleAll.setGroupState(document, toggleGroup, true);

            renderInsightSelectionState(allInsights, toggleGroup);
        }

        // Insight selection is cleared.
        if (e.target.matches(getSelectors(toggleGroup).elements.clearSelectedInsights)) {
            e.preventDefault();
            // Reset all selected insights.
            setSelectedInsights([], toggleGroup);
            // Uncheck all insight checkboxes on the page from a given toggle group.
            CheckboxToggleAll.setGroupState(document, toggleGroup, false);

            renderInsightSelectionState([], toggleGroup);
        }
    });
};

/**
 * Method that handles the change of state of the insight selection based on the data returned by the
 * 'checkboxToggled' event.
 *
 * @method handleInsightToggle
 * @param {Object} data The data returned by the 'checkboxToggled' event
 */
const handleInsightToggle = (data) => {
    // Array containing all currently checked insight (slave) checkbox elements on the page.
    const checkedInsightsOnPage = data.checkedSlaves.toArray();
    // Array containing all insight (slave) checkbox elements on the current page.
    const insightsOnPage = data.slaves.toArray();
    // Get all previously selected insight in the given toggle group.
    let selectedInsights = getSelectedInsights(data.toggleGroupName);

    // Loop through each insight checkbox element on the current page.
    $.each(insightsOnPage, (index, insight) => {
        const insightId = parseInt($(insight).val());
        // Whether the current insight checkbox element is currently selected.
        const isSelected = checkedInsightsOnPage.indexOf(insight) > -1;
        // Whether the current insight checkbox element was previously selected.
        const wasSelected = selectedInsights.length > 0 &&
            selectedInsights.indexOf(insightId) > -1;

        if (isSelected && !wasSelected) { // The insight checkbox element is selected and was not previously.
            // The current insight was selected in the latest change of state, therefore add the current insight
            // to the selected insights.
            selectedInsights.push(insightId);
        } else if (!isSelected && wasSelected) { // The insights checkbox element is not selected and it was previously.
            // The current insights was unselected in the latest change of state, therefore remove the current
            // insight from the selected insights.
            selectedInsights.splice(selectedInsights.indexOf(insightId), 1);
        }
    });

    setSelectedInsights(selectedInsights, data.toggleGroupName);

    renderInsightSelectionState(selectedInsights, data.toggleGroupName);
};

/**
 * Method that renders the current insight selection state.
 *
 * @method renderInsightSelectionState
 * @param {Array} selectedInsights The array containing the IDs of the selected insights
 * @param {String} toggleGroup The name of the toggle group
 */
const renderInsightSelectionState = (selectedInsights, toggleGroup) => {
    renderInsightSelectionInfo(selectedInsights, toggleGroup);
    // If the insight selection actions element is present, render the proper actions list.
    if (document.querySelector(getSelectors(toggleGroup).containers.insightSelectionActionsList)) {
        renderInsightSelectionActionsList(selectedInsights, toggleGroup);
    }
};

/**
 * Method that renders the proper actions list depending on the selected insights.
 *
 * @method renderInsightSelectionActionsList
 * @param {Array} selectedInsights The array containing the IDs of the selected insights
 * @param {String} toggleGroup The name of the toggle group
 */
const renderInsightSelectionActionsList = async (selectedInsights, toggleGroup) => {
    let actions = [];
    // Get the IDs of all existing insights.
    const allInsights = getAllInsights(toggleGroup);

    if (selectedInsights.length > 0) { // There is at least one selected insight.
        // Display the 'Clear all' option in the actions list.
        actions.push({
            action: Actions.clearAll,
            togglegroup: toggleGroup,
            text: await getString('clearselection', 'report_insights')
        });

        if (selectedInsights.length !== allInsights.length) { // There are still some unselected insights.
            // Display the 'Select all' option in the actions list.
            actions.unshift({
                action: Actions.selectAll,
                togglegroup: toggleGroup,
                text: await getString('selectall', 'core', allInsights.length)
            });
        }
    } else { // There aren't any selected insights.
        // Display the 'Select all' option in the actions list.
        actions.push({
            action: Actions.selectAll,
            togglegroup: toggleGroup,
            text: await getString('selectall', 'core', allInsights.length)
        });
    }

    const {html, js} = await Templates.renderForPromise('report_insights/insight_selection_actions_list',
        {actions: actions});
    await Templates.replaceNodeContents(getSelectors(toggleGroup).containers.insightSelectionActionsList, html, js);
};

/**
 * Method that renders the insight selection info.
 *
 * @method renderInsightSelectionInfo
 * @param {Array} selectedInsights The array containing the IDs of the selected insights
 * @param {String} toggleGroup The name of the toggle group
 */
const renderInsightSelectionInfo = (selectedInsights, toggleGroup) => {
    const allInsights = getAllInsights(toggleGroup);
    const insightSelectionInfoSpanElements = document.querySelectorAll(
        getSelectors(toggleGroup).containers.insightSelectionInfoSpan);
    const strData = {
        nselected: selectedInsights.length,
        ntotal: allInsights.length
    };

    insightSelectionInfoSpanElements.forEach(async (insightSelectionInfoSpanElement) => {
        insightSelectionInfoSpanElement.innerHTML = await getString('insightsselected', 'report_insights', strData);
    });
};

/**
 * Method that pre-sets the state of the insight selection elements on page init.
 *
 * @method setStateOnInit
 * @param {String} toggleGroup The name of the toggle group
 */
const setStateOnInit = (toggleGroup) => {
    // Get all selected insights in the given toggle group.
    const selectedInsights = getSelectedInsights(toggleGroup);
    // Get all insight (slave) checkbox elements on the current page.
    const insightCheckboxElements = document.querySelectorAll(getSelectors(toggleGroup).elements.insightSlaveCheckbox);

    // If at least one insight has been selected in the given toggle group, enable every existing action element
    // related to that toggle group.
    if (selectedInsights.length > 0) {
        const actionToggleElements = document.querySelectorAll(getSelectors(toggleGroup).elements.actionToggle);
        actionToggleElements.forEach(actionToggleElement => {
            actionToggleElement.removeAttribute('disabled');
        });
    }

    // Loop through each insight (slave) checkbox element from the current page.
    insightCheckboxElements.forEach(insightCheckboxElement => {
        const insightId = parseInt(insightCheckboxElement.value);
        if (selectedInsights.indexOf(insightId) > -1) {
            insightCheckboxElement.checked = true;
        }
    });

    renderInsightSelectionState(selectedInsights, toggleGroup);
};

/**
 * Set up the actions.
 *
 * @param {String} toggleGroup The name of the toggle group
 * @method init
 */
export const init = (toggleGroup) => {
    setStateOnInit(toggleGroup);
    registerListenerEvents(toggleGroup);
};
