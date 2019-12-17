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
 * A system for displaying notifications to users from the session.
 *
 * Wrapper for the YUI M.core.notification class. Allows us to
 * use the YUI version in AMD code until it is replaced.
 *
 * @module     core/activity_chooser_dialogue
 * @package    core
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.8
 */

import {focusLock} from 'core/local/aria/focuslock';
import {modalFactory} from 'core/modal_factory';
import {modalEvents} from 'core/modal_events';
import {templates} from 'core/templates';

const SELECTORS = {
    CHOOSER_CONTAINER: '[data-region="chooser-container"]',
    CHOOSER_OPTIONS_CONTAINER: '[data-region="chooser-options-container"]',
    CHOOSER_OPTION_CONTAINER: '[data-region="chooser-option-container"]',
    CHOOSER_OPTION_ACTIONS_CONTAINER: '[data-region="chooser-option-actions-container"]',
    CHOOSER_OPTION_INFO_CONTAINER: '[data-region="chooser-option-info-container"]',
    CHOOSER_OPTION_SUMMARY_CONTAINER: '[data-region="chooser-option-summary-container"]',
    CHOOSER_OPTION_SUMMARY_CONTENT_CONTAINER: '[data-region="chooser-option-summary-content-container"]',
    CHOOSER_OPTION_SUMMARY_ACTIONS_CONTAINER: '[data-region="chooser-option-summary-actions-container"]',
    CHOOSER_OPTION_ACTION_GROUP_ELEMENT: '[data-group="chooser-option-action"]',
    CHOOSER_OPTION_ACTIONS: {
        SHOW_CHOOSER_OPTION_SUMMARY: '[data-action="show-option-summary"]',
    },
    ADD_CHOOSER_OPTION: '[data-action="add-chooser-option"]',
    CLOSE_CHOOSER_OPTION_SUMMARY: '[data-action="close-chooser-option-summary"]',
};

const showOptionSummary = optionSummaryElement => {
    var chooserContainerElement = document.querySelector(SELECTORS.CHOOSER_CONTAINER);
    // Get the current scroll position of the chooser container element.
    var topPosition = chooserContainerElement.scrollTop;
    // Get the height of the chooser container element.
    var height = chooserContainerElement.offsetHeight;
    // Disable the scroll of the chooser container element.
    chooserContainerElement.classList.add('noscroll');

    setOptionSummaryPositionAndHeight(optionSummaryElement, topPosition, height);

    var optionSummaryContentElement = optionSummaryElement
        .querySelector(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTENT_CONTAINER);
    // Set the scroll of the type summary content element to top.
    if (optionSummaryContentElement.scrollTop > 0) {
        optionSummaryContentElement.scrollTop = 0;
    }
    // Show the particular summary overlay.
    optionSummaryElement.classList.add('open');
    var cancelOptionSummaryElement = optionSummaryElement.querySelector(SELECTORS.CLOSE_CHOOSER_OPTION_SUMMARY);
    var addOptionElement = optionSummaryElement.querySelector(SELECTORS.ADD_CHOOSER_OPTION);

    focusLock.trapFocus(optionSummaryElement);

    cancelOptionSummaryElement.setAttribute('tabindex', '0');
    addOptionElement.setAttribute('tabindex', '0');
};

const setOptionSummaryPositionAndHeight = (optionSummaryElement, position, height) => {
    var optionSummaryContentElement = optionSummaryElement.querySelector(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTENT_CONTAINER);
    var optionSumarryActionsElement = optionSummaryElement.querySelector(SELECTORS.CHOOSER_OPTION_SUMMARY_ACTIONS_CONTAINER);
    var contentHeight = height - optionSumarryActionsElement.offsetHeight;
    optionSummaryContentElement.style.height = contentHeight + 'px';

    optionSummaryElement.style.top = position + 'px';
    optionSummaryElement.style.height = height + 'px';
};

const registerListenerEvents = () => {
    // Show the chooser option summary.
    document.querySelectorAll(SELECTORS.CHOOSER_OPTION_ACTIONS.SHOW_CHOOSER_OPTION_SUMMARY)
        .forEach(function(showOptionSummaryElement) {
            showOptionSummaryElement.addEventListener('click', function(e) {
                var optionSummaryElement = e.target.closest(SELECTORS.CHOOSER_OPTION_CONTAINER)
                    .querySelector(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTAINER);
                showOptionSummary(optionSummaryElement);
            });
        });

    // Close the chooser option summary.
    document.querySelectorAll(SELECTORS.CLOSE_CHOOSER_OPTION_SUMMARY)
        .forEach(function(closeOptionSummaryElement) {
            closeOptionSummaryElement.addEventListener('click', function(e) {
                var optionSummaryElement = e.target.closest(SELECTORS.CHOOSER_OPTION_CONTAINER)
                    .querySelector(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTAINER);
                optionSummaryElement.classList.remove('open');
                document.querySelector(SELECTORS.CHOOSER_CONTAINER).classList.remove('noscroll');
            });
        });

    var chooserOptions = document.querySelectorAll(SELECTORS.CHOOSER_OPTION_CONTAINER);
    chooserOptions.forEach(function(chooserOptionElement, index, chooserOptions) {
        chooserOptionElement.addEventListener('keydown', function(e, index, chooserOptions) {
            var totalChooserOptions = chooserOptions.length;

            // Right key or down key.
            if (e.keyCode === 39 || e.keyCode === 40) {
                var nextIndex = index + 1;
                if (typeof chooserOptions[nextIndex] === 'undefined') {
                    return;
                }
                chooserOptions[nextIndex].focus();
            }

            // Left key.
            if (e.keyCode === 37 || e.keyCode === 38) {
                var prevIndex = index - 1;
                if (typeof chooserOptions[prevIndex] === 'undefined') {
                    return;
                }
                chooserOptions[prevIndex].focus();
            }

            // End key.
            if (e.keyCode === 35) {
                var lastChooserOptionIndex = totalChooserOptions - 1;
                if (index === lastChooserOptionIndex) {
                    return;
                }
                chooserOptions[lastChooserOptionIndex].focus();
            }

            // Home key.
            if (e.keyCode === 36) {
                var firstChooserOptionIndex = 0;
                if (index === firstChooserOptionIndex) {
                    return;
                }
                chooserOptions[firstChooserOptionIndex].focus();
            }
        });
    });

    var chooserOptionActionElements = document.querySelectorAll(SELECTORS.CHOOSER_OPTION_ACTION_GROUP_ELEMENT);
    chooserOptionActionElements.forEach(function(chooserOptionActionElement, index) {
        chooserOptionActionElement.addEventListener('keydown', function (e) {
            if (e.keyCode === 13) {
                if (this.getAttribute('data-action') === 'show-option-summary') {
                    var chooserOptionSummary = this.closest(SELECTORS.CHOOSER_OPTION_CONTAINER)
                        .querySelector(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTAINER);

                    showOptionSummary(chooserOptionSummary);
                }
            }
        });
    });
};

export const displayChooser = (e, data) => {
    return templates.render('core_course/modchooser', data).then(html => {
            return modalFactory.create({
                type: modalFactory.types.DEFAULT,
                body: html,
                title: data.title,
                large: true
            }).then(modal => {
                modal.getRoot().on(modalEvents.shown, () => {
                    disableFocusChooserOptionSummary();
                    registerListenerEvents();
                });

                // We want to focus on the action select when the dialog is closed.
                modal.getRoot().on(modalEvents.hidden, () => {
                    e.target.closest('.chooser-link').focus();
                    modal.getRoot().remove();
                });

                modal.show();
            });
        });
};

const disableFocusChooserOptionSummary = () => {
    var chooserOptionSummaryElements = document.querySelectorAll(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTAINER);
    chooserOptionSummaryElements.forEach(function(chooserOptionSummaryElement, index) {
        var focusableElements = chooserOptionSummaryElement.querySelectorAll('a, button');
        focusableElements.forEach(function(focusableElement, index) {
            focusableElement.setAttribute('tabindex', -1);
        });
    });
};

//
// define(
//     [
//         'jquery',
//         'core/activity_chooser_events',
//         'core/local/aria/focuslock',
//         'core/modal_factory',
//         'core/modal_events',
//         'core/templates'
//     ],
//     function(
//         $,
//         ActivityChooserEvents,
//         FocusLock,
//         ModalFactory,
//         ModalEvents,
//         Templates
//     ) {
//
//     /**
//      * Cancel any listen events in the listenevents queue
//      *
//      * Several locations add event handlers which should only be called before the form is submitted. This provides
//      * a way of cancelling those events.
//      *
//      * @method cancel_listenevents
//      */
//     var cancelListenevents = function() {
//         // Detach all listen events to prevent duplicate triggers
//         var thisevent;
//         while (listenevents.length) {
//             thisevent = listenevents.shift();
//             $(thisevent).off();
//         }
//     };
//
//     var hide = function() {
//         // Cancel all listen events
//         cancelListenevents();
//         container.off();
//         panel.hide();
//     };
//
//     var SELECTORS = {
//         CHOOSER_CONTAINER: '[data-region="chooser-container"]',
//         CHOOSER_OPTIONS_CONTAINER: '[data-region="chooser-options-container"]',
//         CHOOSER_OPTION_CONTAINER: '[data-region="chooser-option-container"]',
//         CHOOSER_OPTION_ACTIONS_CONTAINER: '[data-region="chooser-option-actions-container"]',
//         CHOOSER_OPTION_INFO_CONTAINER: '[data-region="chooser-option-info-container"]',
//         CHOOSER_OPTION_SUMMARY_CONTAINER: '[data-region="chooser-option-summary-container"]',
//         CHOOSER_OPTION_SUMMARY_CONTENT_CONTAINER: '[data-region="chooser-option-summary-content-container"]',
//         CHOOSER_OPTION_SUMMARY_ACTIONS_CONTAINER: '[data-region="chooser-option-summary-actions-container"]',
//         CHOOSER_OPTION_ACTION_GROUP_ELEMENT: '[data-group="chooser-option-action"]',
//         CHOOSER_OPTION_ACTIONS: {
//             SHOW_CHOOSER_OPTION_SUMMARY: '[data-action="show-option-summary"]',
//         },
//         ADD_CHOOSER_OPTION: '[data-action="add-chooser-option"]',
//         CLOSE_CHOOSER_OPTION_SUMMARY: '[data-action="close-chooser-option-summary"]',
//     };
//
//     var registerListenerEvents = function() {
//
//         // Show the chooser option summary.
//         $(SELECTORS.CHOOSER_OPTION_ACTIONS.SHOW_CHOOSER_OPTION_SUMMARY).on('click', function(e) {
//             var optionSummaryElement = $(e.target).closest(SELECTORS.CHOOSER_OPTION_CONTAINER)
//                 .find(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTAINER);
//             showOptionSummary(optionSummaryElement);
//         });
//
//         // Close the chooser option summary.
//         $(SELECTORS.CLOSE_CHOOSER_OPTION_SUMMARY).on('click', function(e) {
//             var optionSummaryElement = $(e.target).closest(SELECTORS.CHOOSER_OPTION_CONTAINER)
//                 .find(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTAINER);
//             optionSummaryElement.removeClass('open');
//             $(SELECTORS.CHOOSER_CONTAINER).removeClass('noscroll');
//         });
//
//         $(SELECTORS.CHOOSER_OPTION_CONTAINER).on('keydown', function(e) {
//             var index = $(this).index();
//             var chooserOptions = $(SELECTORS.CHOOSER_OPTIONS_CONTAINER).find(SELECTORS.CHOOSER_OPTION_CONTAINER);
//             var totalChooserOptions = chooserOptions.length;
//
//             // Right key or down key.
//             if (e.keyCode === 39 || e.keyCode === 40) {
//                 var nextIndex = index + 1;
//                 if (typeof chooserOptions[nextIndex] === 'undefined') {
//                     return;
//                 }
//                 $(chooserOptions[nextIndex]).focus();
//             }
//
//             // Left key.
//             if (e.keyCode === 37 || e.keyCode === 38) {
//                 var prevIndex = index - 1;
//                 if (typeof chooserOptions[prevIndex] === 'undefined') {
//                     return;
//                 }
//                 $(chooserOptions[prevIndex]).focus();
//             }
//
//             // End key.
//             if (e.keyCode === 35) {
//                 var lastChooserOptionIndex = totalChooserOptions - 1;
//                 if (index === lastChooserOptionIndex) {
//                     return;
//                 }
//                 $(chooserOptions[lastChooserOptionIndex]).focus();
//             }
//
//             // Home key.
//             if (e.keyCode === 36) {
//                 var firstChooserOptionIndex = 0;
//                 if (index === firstChooserOptionIndex) {
//                     return;
//                 }
//                 $(chooserOptions[firstChooserOptionIndex]).focus();
//             }
//         });
//
//         $(SELECTORS.CHOOSER_OPTION_ACTION_GROUP_ELEMENT).on('keydown', function(e) {
//             if (e.keyCode === 13) {
//                 if ($(this).data('action') === 'show-option-summary') {
//                     var chooserOptionSummary = $(this).closest(SELECTORS.CHOOSER_OPTION_CONTAINER)
//                         .find(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTAINER);
//                     showOptionSummary(chooserOptionSummary);
//                 }
//             }
//         });
//     };
//
//     var showOptionSummary = function(optionSummaryElement) {
//         // Get the current scroll position of the chooser container element.
//         var topPosition = $(SELECTORS.CHOOSER_CONTAINER).scrollTop();
//         // Get the height of the chooser container element.
//         var height = $(SELECTORS.CHOOSER_CONTAINER).outerHeight();
//         // Disable the scroll of the chooser container element.
//         $(SELECTORS.CHOOSER_CONTAINER).addClass('noscroll');
//
//         setOptionSummaryPositionAndHeight(optionSummaryElement, topPosition, height);
//
//         var optionSummaryContentElement = optionSummaryElement.find(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTENT_CONTAINER);
//         // Set the scroll of the type summary content element to top.
//         if (optionSummaryContentElement.scrollTop() > 0) {
//             optionSummaryContentElement.scrollTop(0);
//         }
//         // Show the particular summary overlay.
//         optionSummaryElement.addClass('open');
//         var cancelAction = optionSummaryElement.find(SELECTORS.CLOSE_CHOOSER_OPTION_SUMMARY);
//         var addAction = optionSummaryElement.find(SELECTORS.ADD_CHOOSER_OPTION);
//
//         FocusLock.trapFocus($(optionSummaryElement));
//
//         $(cancelAction).attr('tabindex', 0);
//         $(addAction).attr('tabindex', 0);
//     };
//
//     var setOptionSummaryPositionAndHeight = function(optionSummaryElement, position, height) {
//         var optionSummaryContentElement = optionSummaryElement.find(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTENT_CONTAINER);
//         var optionSumarryActionsElement = optionSummaryElement.find(SELECTORS.CHOOSER_OPTION_SUMMARY_ACTIONS_CONTAINER);
//         var contentHeight = height - optionSumarryActionsElement.outerHeight();
//         optionSummaryContentElement.css({'height' : contentHeight + 'px'});
//
//         optionSummaryElement.css({'top' : position + 'px', 'height' : height + 'px'});
//     };
//
//     /**
//       * Display the module chooser.
//       *
//       * @method display_chooser
//       * @param {EventFacade} e Triggering Event
//       */
//     var displayChooser = function(e, data) {
//         return Templates.render('core_course/modchooser', data)
//             .then(function(html) {
//                 return ModalFactory.create({
//                     type: ModalFactory.types.DEFAULT,
//                     body: html,
//                     title: data.title,
//                     large: true
//                 }).then(function(modal) {
//                     modal.getRoot().on(ModalEvents.shown, function() {
//                         var optionSummaryContentAnchors = $(SELECTORS.CHOOSER_OPTION_SUMMARY_CONTENT_CONTAINER).find('a');
//                         optionSummaryContentAnchors.each(function(key, anchor) {
//                             $(anchor).attr('tabindex', -1);
//                         });
//                         registerListenerEvents();
//                     }.bind(this));
//
//                     // We want to focus on the action select when the dialog is closed.
//                     modal.getRoot().on(ModalEvents.hidden, function() {
//                         $(e.target.closest('.chooser-link')).focus();
//                         modal.getRoot().remove();
//                     }.bind(this));
//
//                     modal.show();
//                 });
//             });
//     };
//
//     return /** @alias module:core/activity_chooser_dialogue */{
//         /**
//          * Add a notification to the page.
//          *
//          * Note: This does not cause the notification to be added to the session.
//          *
//          * @method addNotification
//          * @param {Object}  notification                The notification to add.
//          * @param {string}  notification.message        The body of the notification
//          * @param {string}  notification.type           The type of notification to add (error, warning, info, success).
//          * @param {Boolean} notification.closebutton    Whether to show the close button.
//          * @param {Boolean} notification.announce       Whether to announce to screen readers.
//          */
//         displayChooser: displayChooser
//     };
// });
