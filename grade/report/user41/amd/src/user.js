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
 * A small modal to search users or grade items within the gradebook.
 *
 * @module     gradereport_user41/user
 * @copyright  2022 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Templates from 'core/templates';
import * as ModalFactory from 'core/modal_factory';
import {get_string as getString} from 'core/str';
import Pending from 'core/pending';
import CustomEvents from "core/custom_interaction_events";
import {debounce} from 'core/utils';
import ajax from 'core/ajax';
import * as ModalEvents from 'core/modal_events';

export const init = () => {
    const pendingPromise = new Pending();
    registerListenerEvents();
    pendingPromise.resolve();
};

const registerListenerEvents = () => {
    const events = [
        'click',
        CustomEvents.events.activate,
        CustomEvents.events.keyboardActivate
    ];
    CustomEvents.define(document, events);

    // We want to show the modal instantly but loading whilst waiting for our data.
    let bodyPromiseResolver;
    const bodyPromise = new Promise(resolve => {
        bodyPromiseResolver = resolve;
    });
    bodyPromiseResolver(Templates.render(
        'gradereport_user41/searchwidget',
        []
    ));

    // Display module chooser event listeners.
    events.forEach((event) => {
        document.addEventListener(event, async(e) => {
            const button1 = e.target.closest('.mattbutton');
            if (e.target.closest('.mattbutton')) {
                const courseID = button1.dataset.courseid;
                const searchType = button1.dataset.searchtype;
                e.preventDefault();
                const modal = buildModal(bodyPromise);

                // Now we have a modal we should start fetching data.
                // If an error occurs while fetching the data, display the error within the modal.
                let data = [];
                let userSearch = true;
                switch (searchType) {
                    case 'user': {
                        data = await userFetch(courseID).catch(async(e) => {
                            const errorTemplateData = {
                                'errormessage': e.message
                            };
                            bodyPromiseResolver(
                                await Templates.render('core_course/local/activitychooser/error', errorTemplateData)
                            );
                        });
                        //searchItemRender = curriedfunction applying a variable
                        break;
                    }
                    case 'gradeitems': {
                        data = await gradeitemFetch(courseID).catch(async(e) => {
                            const errorTemplateData = {
                                'errormessage': e.message
                            };
                            bodyPromiseResolver(
                                await Templates.render('core_course/local/activitychooser/error', errorTemplateData)
                            );
                        });
                        window.console.log(data);
                        userSearch = false;
                        break;
                    }
                    default: {
                        const errorTemplateData = {
                            'errormessage': `Invalid calling type. Please choose between User or GradeItems.
                            Default message as follows: ${e.message}`
                        };
                        bodyPromiseResolver(
                            await Templates.render('core_course/local/activitychooser/error', errorTemplateData)
                        );
                        break;
                    }
                }

                // Early return if there is no module data.
                if (data === []) {
                    return;
                }

                modal.then(modal => {
                    // We want to destroy this when the dialog is closed.
                    modal.getRoot().on(ModalEvents.hidden, () => {
                        modal.destroy();
                    });
                    // Once the body of the modal has been resolved, add more features.
                    modal.getBodyPromise()
                        // The return value of getBodyPromise is a jquery object containing the body NodeElement.
                        .then(body => body[0])
                        .then(body => {
                            const foo = document.querySelector('.reportdatasearch');
                            const searchInput = foo.querySelector('input[data-action="search"]');
                            const searchResultsContainer = foo.querySelector('[data-region="search-results-container"]');
                            renderSearchResults(searchResultsContainer, data, userSearch);
                            // The search input is triggered.
                            searchInput.addEventListener('input', debounce(() => {
                                // Display the search results.
                                const searchResultsData = debounceCallee(searchInput.value, data, userSearch);
                                renderSearchResults(searchResultsContainer, searchResultsData, userSearch);
                            }, 300));
                            return body;
                        }).catch();
                }).catch();
            }
        });
    });
};

const buildModal = (bodyPromise) => {
    return ModalFactory.create({
        type: ModalFactory.types.DEFAULT,
        title: getString('pluginname', 'gradereport_user41'),
        body: bodyPromise,
        small: true,
        scrollable: false,
        templateContext: {
            classes: 'reportdatasearch modal-sm'
        }
    }).then(modal => {
        modal.show();
        return modal;
    });
};

const userFetch = (courseid) => {
    const request = {
        methodname: 'core_enrol_get_enrolled_users',
        args: {
            courseid: courseid,
        },
    };
    return ajax.call([request])[0];
};

const gradeitemFetch = (courseid) => {
    const request = {
        methodname: 'core_course_get_contents',
        args: {
            courseid: courseid,
        },
    };
    return ajax.call([request])[0];
};

const debounceCallee = (searchValue, data, userSearch) => {
    if (searchValue.length > 0) { // Search query is present.
        // Swap here based on User or gradeitem search.
        if (userSearch) {
            return searchUsers(data, searchValue);
        } else if (!userSearch) {
            return searchGradeitems(data, searchValue);
        }
    } else {
        return data;
    }
};

const searchUsers = (users, searchTerm) => {
    if (searchTerm === '') {
        return users;
    }
    searchTerm = searchTerm.toLowerCase();
    const searchResults = [];
    users.forEach((user) => {
        const userName = user.fullname.toLowerCase();
        if (userName.includes(searchTerm)) {
            searchResults.push(user);
        }
    });
    return searchResults;
};

const searchGradeitems = (sections, searchTerm) => {
    // TODO: Fix this.
    if (searchTerm === '') {
        return sections;
    }
    searchTerm = searchTerm.toLowerCase();
    const searchResults = [];
    sections.forEach((section) => {
        section.modules.forEach((module) => {
            const moduleName = module.name.toLowerCase();
            if (moduleName.includes(searchTerm)) {
                searchResults.push(module);
            }
        });
    });
    return searchResults;
};

const renderSearchResults = async(searchResultsContainer, searchResultsData, userSearch) => {
    const templateData = {
        'searchresults': searchResultsData,
        'usertemplate': !!userSearch,
        'gradetemplate': !userSearch,
    };
    // Build up the html & js ready to place into the help section.
    const {html, js} = await Templates.renderForPromise('gradereport_user41/searchresults', templateData);
    await Templates.replaceNodeContents(searchResultsContainer, html, js);
};
