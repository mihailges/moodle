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
 * @module     core_course/local/chooser/dialogue
 * @package    core
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import * as ModalEvents from 'core/modal_events';
import selectors from 'core_course/local/chooser/selectors';
import * as Templates from 'core/templates';
import {end, arrowLeft, arrowRight, home, enter, space} from 'core/key_codes';
import {addIconToContainer} from 'core/loadingicon';
import {debounce} from 'core/utils';

/**
 * Given an event from the main module 'page' navigate to it's help section via a carousel.
 *
 * @method carouselPageTo
 * @param {jQuery} carousel Our initialized carousel to manipulate
 * @param {Object} moduleData Data of the module to carousel to
 */
const carouselPageTo = (carousel, moduleData) => {
    const help = carousel.find(selectors.regions.help)[0];
    help.innerHTML = '';

    addIconToContainer(help)

    // Build up the html & js ready to place into the help section.
    .then(() => {
        return Templates.renderForPromise('core_course/chooser_help', moduleData);
    })
    .then(({html, js}) => Templates.replaceNodeContents(help, html, js))
    .then(() => {
        help.querySelector(selectors.regions.chooserSummary.description).focus();
        return help;
    })
    .catch(Notification.exception);

    // Trigger the transition between 'pages'.
    carousel.carousel('next');
};

/**
 * Register chooser related event listeners.
 *
 * @method registerListenerEvents
 * @param {Promise} modal Our modal that we are working with
 * @param {Map} mappedModules A map of all of the modules we are working with with K: mod_name V: {Object}
 */
const registerListenerEvents = (modal, mappedModules) => {
    const bodyClickListener = e => {
        if (e.target.closest(selectors.actions.optionActions.showSummary)) {
            const carousel = $(modal.getBody()[0].querySelector(selectors.regions.carousel));

            const module = e.target.closest(selectors.regions.chooserOption.container);
            const moduleName = module.dataset.modname;
            const moduleData = mappedModules.get(moduleName);
            carouselPageTo(carousel, moduleData);
        }

        // From the help screen go back to the module overview.
        if (e.target.matches(selectors.actions.closeOption)) {
            const carousel = $(modal.getBody()[0].querySelector(selectors.regions.carousel));

            // Trigger the transition between 'pages'.
            carousel.carousel('prev');
            carousel.on('slid.bs.carousel', () => {
                const allModules = modal.getBody()[0].querySelector(selectors.regions.modules);
                const caller = allModules.querySelector(selectors.regions.getModuleSelector(e.target.dataset.modname));
                caller.focus();
            });
        }

        // The "clear search" button is triggered.
        if (e.target.matches(selectors.actions.clearSearch)) {
            // Clear the entered search query in the search bar and hide the search results container.
            const searchInput = modal.getBody()[0].querySelector(selectors.actions.search);
            searchInput.value = "";
            searchInput.focus();
            toggleSearchResultsView(modal.getBody()[0], mappedModules, searchInput.value);
        }
    };

    modal.getBodyPromise()

    // The return value of getBodyPromise is a jquery object containing the body NodeElement.
    .then(body => body[0])

    // Set up the carousel.
    .then(body => {
        $(body.querySelector(selectors.regions.carousel))
            .carousel({
                interval: false,
                pause: true,
                keyboard: false
            });

        return body;
    })

    // Add the listener for clicks on the body.
    .then(body => {
        body.addEventListener('click', bodyClickListener);
        return body;
    })

    // Add the listener for clicks on the body.
    .then(body => {
        const searchInput = body.querySelector(selectors.actions.search);
        // The search input is triggered.
        searchInput.addEventListener('input', debounce(() => {
            // Display the search results.
            toggleSearchResultsView(body, mappedModules, searchInput.value);
        }, 300));
        return body;
    })

    // Register event listeners related to the keyboard navigation controls.
    .then(body => {
        const chooserOptions = body.querySelector(selectors.regions.chooserOptions);
        initKeyboardNavigation(body, mappedModules, chooserOptions);
        return body;
    })
    .catch();

};

/**
 * Initialise the keyboard navigation controls for the chooser.
 *
 * @method initKeyboardNavigation
 * @param {NodeElement} body Our modal that we are working with
 * @param {Map} mappedModules A map of all of the modules we are working with with K: mod_name V: {Object}
 * @param {HTMLElement} chooserOptionsContainer The section that contains the chooser items
 */
const initKeyboardNavigation = (body, mappedModules, chooserOptionsContainer) => {

    const chooserOptions = chooserOptionsContainer.querySelectorAll(selectors.regions.chooserOption.container);

    Array.from(chooserOptions).forEach((element) => {
        return element.addEventListener('keyup', (e) => {

            // Check for enter/ space triggers for showing the help.
            if (e.keyCode === enter || e.keyCode === space) {
                if (e.target.matches(selectors.actions.optionActions.showSummary)) {
                    const module = e.target.closest(selectors.regions.chooserOption.container);
                    const moduleName = module.dataset.modname;
                    const moduleData = mappedModules.get(moduleName);
                    const carousel = $(body.querySelector(selectors.regions.carousel));
                    carousel.carousel({
                        interval: false,
                        pause: true,
                        keyboard: false
                    });
                    carouselPageTo(carousel, moduleData);
                }
            }

            // Next.
            if (e.keyCode === arrowRight) {
                const currentOption = e.target.closest(selectors.regions.chooserOption.container);
                const nextOption = currentOption.nextElementSibling;
                const firstOption = chooserOptionsContainer.firstElementChild;
                const toFocusOption = clickErrorHandler(nextOption, firstOption);
                focusChooserOption(toFocusOption, currentOption);
            }

            // Previous.
            if (e.keyCode === arrowLeft) {
                const currentOption = e.target.closest(selectors.regions.chooserOption.container);
                const previousOption = currentOption.previousElementSibling;
                const lastOption = chooserOptionsContainer.lastElementChild;
                const toFocusOption = clickErrorHandler(previousOption, lastOption);
                focusChooserOption(toFocusOption, currentOption);
            }

            if (e.keyCode === home) {
                const currentOption = e.target.closest(selectors.regions.chooserOption.container);
                const firstOption = chooserOptionsContainer.firstElementChild;
                focusChooserOption(firstOption, currentOption);
            }

            if (e.keyCode === end) {
                const currentOption = e.target.closest(selectors.regions.chooserOption.container);
                const lastOption = chooserOptionsContainer.lastElementChild;
                focusChooserOption(lastOption, currentOption);
            }
        });
    });
};

/**
 * Focus on a chooser option element and remove the previous chooser element from the focus order
 *
 * @method focusChooserOption
 * @param {HTMLElement} currentChooserOption The current chooser option element that we want to focus
 * @param {HTMLElement} previousChooserOption The previous focused option element
 */
const focusChooserOption = (currentChooserOption, previousChooserOption = false) => {
    if (previousChooserOption !== false) {
        toggleFocusableChoserOption(previousChooserOption, false);
    }

    toggleFocusableChoserOption(currentChooserOption, true);
    currentChooserOption.focus();
};

/**
 * Add or remove a chooser option from the focus order.
 *
 * @method focusChooserOption
 * @param {HTMLElement} chooserOption The chooser option element which should be added or removed from the focus order
 * @param {Boolean} isFocusable Whether the chooser element is focusable or not
 */
const toggleFocusableChoserOption = (chooserOption, isFocusable) => {
    const chooserOptionLink = chooserOption.querySelector(selectors.actions.addChooser);
    const chooserOptionHelp = chooserOption.querySelector(selectors.actions.optionActions.showSummary);

    if (isFocusable) {
        // Set tabindex to 0 to add current chooser option element to the focus order.
        chooserOption.tabIndex = 0;
        chooserOptionLink.tabIndex = 0;
        chooserOptionHelp.tabIndex = 0;
    } else {
        // Set tabindex to -1 to remove the previous chooser option element from the focus order.
        chooserOption.tabIndex = -1;
        chooserOptionLink.tabIndex = -1;
        chooserOptionHelp.tabIndex = -1;
    }
};

/**
 * Small error handling function to make sure the navigated to object exists
 *
 * @method clickErrorHandler
 * @param {HTMLElement} item What we want to check exists
 * @param {HTMLElement} fallback If we dont match anything fallback the focus
 * @return {String}
 */
const clickErrorHandler = (item, fallback) => {
    if (item !== null) {
        return item;
    } else {
        return fallback;
    }
};

/**
 * Render the search results in a defined container
 *
 * @method renderSearchResults
 * @param {HTMLElement} searchResultsContainer The container where the data should be rendered
 * @param {Object} searchResultsData Data containing the module items that satisfy the search criteria
 */
const renderSearchResults = async(searchResultsContainer, searchResultsData) => {
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
 * @param {NodeElement} modalBody The body of the created modal for the section
 * @param {Map} mappedModules A map of all of the modules we are working with with K: mod_name V: {Object}
 * @param {String} searchQuery The search query
 */
const toggleSearchResultsView = async(modalBody, mappedModules, searchQuery) => {
    const searchActive = searchQuery.length > 0;
    const searchResultsContainer = modalBody.querySelector(selectors.regions.searchResults);

    if (searchActive) {
        const searchResultsData = searchModules(mappedModules, searchQuery);
        await renderSearchResults(searchResultsContainer, searchResultsData);
        const searchResultItemsContainer = searchResultsContainer.querySelector(selectors.regions.searchResultItems);
        const firstSearchResultItem = searchResultItemsContainer.querySelector(selectors.regions.chooserOption.container);
        if (firstSearchResultItem) {
            // Set the first result item to be focusable.
            toggleFocusableChoserOption(firstSearchResultItem, true);
            // Register keyboard events on the created search result items.
            initKeyboardNavigation(modalBody, mappedModules, searchResultItemsContainer);
        }
    }

    toggleSearchResultsContainer(modalBody, searchActive);
    toggleClearSearchButton(modalBody, searchActive);
};

/**
 * Toggle (display/hide) the "clear search" button in the activity chooser search bar
 *
 * @method toggleClearSearchButton
 * @param {NodeElement} modalBody The body of the created modal for the section
 * @param {Boolean} active Whether the search mode is activated
 */
const toggleClearSearchButton = (modalBody, active) => {
    const clearSearchutton = modalBody.querySelector(selectors.actions.clearSearch);
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
 * @param {NodeElement} modalBody The body of the created modal for the section
 * @param {Boolean} active Whether the search mode is activated
 */
const toggleSearchResultsContainer = (modalBody, active) => {
    const searchResultsContainer = modalBody.querySelector(selectors.regions.searchResults);
    const chooserOptionsContainer = modalBody.querySelector(selectors.regions.chooserOptions);

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
export const displayChooser = (origin, modal, sectionModules) => {

    // Make a map so we can quickly fetch a specific module's object for either rendering or searching.
    const mappedModules = new Map();
    sectionModules.forEach((module) => {
        mappedModules.set(module.modulename, module);
    });

    // Register event listeners.
    registerListenerEvents(modal, mappedModules);

    // We want to focus on the action select when the dialog is closed.
    modal.getRoot().on(ModalEvents.hidden, () => {
        modal.destroy();
    });

    // We want to focus on the first chooser option element as soon as the modal is opened.
    modal.getRoot().on(ModalEvents.shown, () => {
        modal.getModal()[0].tabIndex = -1;

        modal.getBodyPromise()
        .then(body => {
            const firstChooserOption = body[0].querySelector(selectors.regions.chooserOption.container);
            focusChooserOption(firstChooserOption);

            return;
        })
        .catch(Notification.exception);
    });

    modal.show();
};
