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
 * A type of dialogue used as for choosing options.
 *
 * @module     core_course/chooser_dialogue
 * @package    core
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import * as ModalEvents from 'core/modal_events';
import selectors from 'core_course/local/chooser/selectors';
import * as Templates from 'core/templates';
import {end, arrowLeft, arrowRight, arrowUp, arrowDown, home, tab, enter, space} from 'core/key_codes';
import {debounce} from 'core/utils';

/**
 * Given an event from the main module 'page' navigate to it's help section via a carousel.
 *
 * @method carouselPageTo
 * @param {Event} e Triggering Event
 * @param {Map} mappedModules A map of all of the modules we are working with with K: mod_name V: {Object}
 * @param {Promise} modal Our modal that we are working with
 * @param {jQuery} carousel Our initialized carousel to manipulate
 */
const carouselPageTo = async(e, mappedModules, modal, carousel) => {
    // Get the systems name for the module just clicked.
    const module = e.target.closest(selectors.regions.chooserOption.container);
    const moduleName = module.dataset.modname;
    // Build up the html & js ready to place into the help section.
    const {html, js} = await Templates.renderForPromise('core_course/chooser_help', mappedModules.get(moduleName));
    const help = modal.getBody()[0].querySelector(selectors.regions.help);
    await Templates.replaceNodeContents(help, html, js);
    // Trigger the transition between 'pages'.
    carousel.carousel('next');
    carousel.carousel('pause');

    carousel.on('slid.bs.carousel', () => {
        const helpContent = help.querySelector(selectors.regions.chooserSummary.description);
        helpContent.focus();
    });
};

/**
 * Register chooser related event listeners.
 *
 * @method registerListenerEvents
 * @param {Promise} modal Our modal that we are working with
 * @param {Map} mappedModules A map of all of the modules we are working with with K: mod_name V: {Object}
 */
const registerListenerEvents = (modal, mappedModules) => {
    const searchInput = modal.getBody()[0].querySelector(selectors.actions.search);

    modal.getBody()[0].addEventListener('click', async(e) => {
        const carousel = $(selectors.regions.carousel);
        carousel.carousel();
        carousel.off('keydown.bs.carousel');
        carousel.carousel('pause');
        if (e.target.closest(selectors.actions.optionActions.showSummary)) {
            await carouselPageTo(e, mappedModules, modal, carousel);
        }

        // From the help screen go back to the module overview.
        if (e.target.matches(selectors.actions.closeOption)) {
            // Trigger the transition between 'pages'.
            carousel.carousel('prev');
            carousel.on('slid.bs.carousel', async() => {
                const module = e.target.dataset.modname;
                const allModules = document.querySelector(selectors.regions.modules);
                const caller = allModules.querySelector(`[role="gridcell"][data-modname=${module}]`);
                await caller.focus();
            });
        }
        // The "clear search" button is triggered.
        if (e.target.matches(selectors.actions.clearSearch)) {
            // Clear the entered search query in the search bar and hide the search results container.
            searchInput.value = "";
            searchInput.focus();
            toggleSearchResultsView(modal, mappedModules, searchInput.value);
        }
    });

    // Register event listeners related to the keyboard navigation controls.
    const chooserOptions = modal.getBody()[0].querySelector(selectors.regions.chooserOptions);
    initKeyboardNavigation(modal, mappedModules, chooserOptions);

    // The search input is triggered.
    searchInput.addEventListener('input', debounce(() => {
        // Display the search results.
        toggleSearchResultsView(modal, mappedModules, searchInput.value);
    }, 300));
};

/**
 * Initialise the keyboard navigation controls for the chooser.
 *
 * @method initKeyboardNavigation
 * @param {Promise} modal Our modal that we are working with
 * @param {Map} mappedModules A map of all of the modules we are working with with K: mod_name V: {Object}
 * @param {HTMLElement} chooserOptionsContainer The section that contains the chooser items
 */
const initKeyboardNavigation = (modal, mappedModules, chooserOptionsContainer) => {
    const chooserOptions = chooserOptionsContainer.querySelectorAll(selectors.regions.chooserOption.container);

    Array.from(chooserOptions).forEach((element) => {
        return element.addEventListener('keyup', async(e) => {

            // Check for enter/ space triggers for showing the help.
            if (e.keyCode === enter || e.keyCode === space) {
                if (e.target.matches(selectors.actions.optionActions.showSummary)) {
                    const carousel = $(selectors.regions.carousel);
                    carousel.carousel();
                    carousel.off('keydown.bs.carousel');
                    carousel.carousel('pause');
                    await carouselPageTo(e, mappedModules, modal, carousel);
                }
            }

            // Next.
            if (e.keyCode === arrowRight || e.keyCode === arrowDown) {
                if (!e.target.matches(selectors.actions.optionActions.showSummary)) {
                    const currentOption = e.target.closest(selectors.regions.chooserOption.container);
                    const nextOption = currentOption.nextElementSibling;
                    const firstOption = chooserOptionsContainer.firstElementChild;
                    clickErrorHandler(nextOption, firstOption);
                }
            }

            // Previous.
            if (e.keyCode === arrowLeft || e.keyCode === arrowUp) {
                if (!e.target.matches(selectors.actions.optionActions.showSummary)) {
                    const currentOption = e.target.closest(selectors.regions.chooserOption.container);
                    const previousOption = currentOption.previousElementSibling;
                    const lastOption = chooserOptionsContainer.lastElementChild;
                    clickErrorHandler(previousOption, lastOption);
                }
            }

            if (e.keyCode === home) {
                const firstOption = chooserOptionsContainer.firstElementChild;
                firstOption.focus();
            }

            if (e.keyCode === end) {
                const lastOption = chooserOptionsContainer.lastElementChild;
                lastOption.focus();
            }

            if (e.keyCode === tab) {
                // We want the user to get focus on the close button if they tab through an entire module.
                if (e.target.matches(selectors.regions.chooserOption.container) &&
                        e.target !== chooserOptionsContainer.firstElementChild) {
                    const closeBtn = modal.getModal()[0].querySelector(selectors.actions.hide);
                    closeBtn.focus();
                }
            }
        });
    });
};

/**
 * Small error handling function to make sure the navigated to object exists
 *
 * @method clickErrorHandler
 * @param {HTMLElement} item What we want to check exists
 * @param {HTMLElement} fallback If we dont match anything fallback the focus
 */
const clickErrorHandler = (item, fallback) => {
    if (item !== null) {
        item.focus();
    } else {
        fallback.focus();
    }
};

/**
 * Render the search results in a defined container
 *
 * @method renderSearchResults
 * @param {Object} modal Our created modal for the section
 * @param {Object} searchResultsData Data containing the module items that satisfy the search criteria
 * @param {HTMLElement} searchResultsContainer The container where the data should be rendered
 */
const renderSearchResults = async(modal, searchResultsData, searchResultsContainer) => {
    const searchResultsNumber = searchResultsData.length;
    const templateData = {
        'searchresultsnumber': searchResultsNumber,
        'searchresults': searchResultsData
    };
    // Build up the html & js ready to place into the help section.
    const {html, js} = await Templates.renderForPromise('core_course/chooser-search-results', templateData);
    await Templates.replaceNodeContents(searchResultsContainer, html, js);
};

/**
 * Toggle (display/hide) the search results depending on the value of the search query
 *
 * @method toggleSearchResultsView
 * @param {Object} modal Our created modal for the section
 * @param {Map} mappedModules A map of all of the modules we are working with with K: mod_name V: {Object}
 * @param {String} searchQuery The search query
 */
const toggleSearchResultsView = async(modal, mappedModules, searchQuery) => {
    const searchActive = searchQuery.length > 0;
    const searchResultsContainer = modal.getBody()[0].querySelector(selectors.regions.searchResults);

    if (searchActive) {
        const searchResultsData = searchModules(mappedModules, searchQuery);
        await renderSearchResults(modal, searchResultsData, searchResultsContainer);
        const searchResultItemsContainer = searchResultsContainer.querySelector(selectors.regions.searchResultItems);
        // Register keyboard events on the created search result items.
        initKeyboardNavigation(modal, mappedModules, searchResultItemsContainer);
    }

    toggleSearchResultsContainer(modal, searchActive);
    toggleClearSearchButton(modal, searchActive);
};

/**
 * Toggle (display/hide) the "clear search" button in the activity chooser search bar
 *
 * @method toggleClearSearchButton
 * @param {Object} modal Our created modal for the section
 * @param {Boolean} active Whether the search mode is activated
 */
const toggleClearSearchButton = (modal, active) => {
    const clearSearchutton = modal.getBody()[0].querySelector(selectors.actions.clearSearch);
    if (active) {
        clearSearchutton.style.display = "block";
    } else {
        clearSearchutton.style.display = "none";
    }
};

/**
 * Toggle (display/hide) the search results container
 *
 * @method toggleSearchResultsContainer
 * @param {Object} modal Our created modal for the section
 * @param {Boolean} active Whether the search mode is activated
 */
const toggleSearchResultsContainer = (modal, active) => {
    const searchResultsContainer = modal.getBody()[0].querySelector(selectors.regions.searchResults);
    const chooserOptionsContainer = modal.getBody()[0].querySelector(selectors.regions.chooserOptions);

    if (active) {
        chooserOptionsContainer.setAttribute('hidden', 'hidden');
        chooserOptionsContainer.classList.remove("d-flex");
        searchResultsContainer.removeAttribute('hidden');
    } else {
        searchResultsContainer.setAttribute('hidden', 'hidden');
        chooserOptionsContainer.removeAttribute('hidden');
        chooserOptionsContainer.classList.add("d-flex");
    }
};

/**
 * Return the list of modules which have a name or description that matches the given search term.
 *
 * @method searchModules
 * @param {Array} modules List of available modules
 * @param {String} searchTerm The search term to match
 * @return {Array}
 */
const searchModules = (modules, searchTerm) => {
    if (searchTerm === '') {
        return modules;
    }

    searchTerm = searchTerm.toLowerCase();

    const searchResults = [];

    modules.forEach((activity) => {
        const activityName = activity.label.toLowerCase();
        const activityDesc = activity.description.toLowerCase();
        if (activityName.includes(searchTerm) || activityDesc.includes(searchTerm)) {
            searchResults.push(activity);
        }
    });

    return searchResults;
};

/**
 * Display the module chooser.
 *
 * @method displayChooser
 * @param {HTMLElement} origin The calling button
 * @param {Object} modal Our created modal for the section
 * @param {Array} sectionModules An array of all of the built module information
 */
export const displayChooser = async(origin, modal, sectionModules) => {

    // Make a map so we can quickly fetch a specific module's object for either rendering or searching.
    const mappedModules = new Map();
    sectionModules.forEach((module) => {
        mappedModules.set(module.modulename, module);
    });

    // Register event listeners.
    await registerListenerEvents(modal, mappedModules);

    // We want to focus on the action select when the dialog is closed.
    modal.getRoot().on(ModalEvents.hidden, () => {
        try {
            // Just in case a user shuts the chooser on the help screen set it back to default.
            const carousel = $(selectors.regions.carousel);
            carousel.carousel();
            carousel.carousel(0);

            origin.focus();
        } catch (e) {
            // eslint-disable-line
        }
    });


    modal.show();
};
