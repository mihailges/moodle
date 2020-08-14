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
 * @module     core_course/repository
 * @package    core_course
 * @copyright  2019 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as PubSub from 'core/pubsub';
import CheckboxToggleAll from 'core/checkbox-toggleall';
import Templates from 'core/templates';

const SELECTORS = {
    SELECT_ALL: '.select-all',
    CLEAR_ALL: '.clear-all'
};

let allSelected = false;

const registerListenerEvents = (root) => {
    PubSub.subscribe(CheckboxToggleAll.events.checkboxToggled, function (data) {
        handleCheckboxToggle(data);
    });
};

const setAllSelected = () => {
    allSelected = true;

    const templateData = {
        total: 1000,
        selectedall: true
    };

    renderSelectAll(templateData);
};

const clearAllSelected = () => {
    allSelected = false;

    const selectedInsightsContainer = document.getElementById('selected-insights');
    selectedInsightsContainer.innerHTML = '';
};

const handleCheckboxToggle = (data) => {
    allSelected = false;

    let templateData = {
        total: 1000,
        selectednumber: data.checkedSlaves.length
    };

    if (data.slaves.length == data.checkedSlaves.length) {
        templateData.selectedallonpage = true;
    } else if (data.slaves.length != data.checkedSlaves.length) {
        templateData.selected = true;
    }

    renderSelectAll(templateData);
};

const renderSelectAll= async (templateData) => {
    const {html, js} = await Templates.renderForPromise('report_insights/selected_insights', templateData);
    const selectedInsightsContainer = document.getElementById('selected-insights');
    await Templates.replaceNodeContents(selectedInsightsContainer, html, js);

    const selectAll = document.querySelector(SELECTORS.SELECT_ALL);
    if (selectAll) {
        selectAll.addEventListener('click', (e) => {
            console.log('dasdasdas');
            e.preventDefault();
            setAllSelected();
        });
    }

    const clearAll = document.querySelector(SELECTORS.CLEAR_ALL);
    if (clearAll) {
        clearAll.addEventListener('click', (e) => {
            e.preventDefault();
            clearAllSelected();
        });
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
export const init = (root, allpredictsionids) => {
    console.log(allpredictsionids);
    registerListenerEvents(root);
};


//
//
// import * as Templates from "../../../../lib/amd/src/templates";
//
// /**
//  * This module manages prediction actions that require AJAX requests.
//  *
//  * @module report_insights/select_all_users
//  */
// define(['jquery',
//         'core/str',
//         'core/ajax',
//         'core/notification',
//         'core/url',
//         'core/modal_factory',
//         'core/modal_events',
//         'core/pubsub',
//         'core/checkbox-toggleall',
//         'core/templates'
//     ],
//     function($,
//              Str,
//              Ajax,
//              Notification,
//              Url,
//              ModalFactory,
//              ModalEvents,
//              PubSub,
//              CheckboxToggleAll,
//              Templates
//     ) {
//
//         var SELECTORS = {
//             SELECT_ALL: '.select-all',
//             CLEAR_ALL: '.clear-all'
//         };
//
//         var registerEventListeners = function(root) {
//             PubSub.subscribe(CheckboxToggleAll.events.checkboxToggled, function (data) {
//                 handleCheckboxToggle(data);
//             });
//
//
//         };
//
//         var allCheckboxesChecked = false;
//         var selectAllUsers = false;
//
//         var handleCheckboxToggle = function(data) {
//             let templateData = {
//                 totalinsights: 1000
//             };
//
//             if (data.slaves.length == data.checkedSlaves.length) {
//                 templateData.selectedallinsightsonpage = true;
//                 //document.getElementsByClassName('selected-insights')[0].innerHTML = "All " + data.checkedSlaves.length + " insights on this page are selected.";
//
//                 //alert("All checked");
//             } else if (data.slaves.length != data.checkedSlaves.length) {
//                 templateData.selectedinsights = true;
//                 //document.getElementsByClassName('selected-insights')[0].innerHTML = "Selected " + data.checkedSlaves.length + " insights";
//                // alert("Not All checked");
//             }
//
//             const {html, js} = await Templates.renderForPromise('core_course/local/activitychooser/favourites',
//                 {favourites: builtFaves});
//
//             Templates.render('report_insights/selected_insights', templateData).
//         };
//
//         return {
//
//             /**
//              * Attach on click handlers for bulk actions.
//              *
//              * @param {String} rootNode
//              * @access public
//              */
//             init: function(root) {
//
//                 registerEventListeners(root);
//
//                 //     /**
//                 //      * Executes the provided action.
//                 //      *
//                 //      * @param  {Array}  predictionIds
//                 //      * @param  {Array}  predictionContainers
//                 //      * @param  {String} actionName
//                 //      * @return {Promise}
//                 //      */
//                 //     var executeAction = function(predictionIds, predictionContainers, actionName) {
//                 //
//                 //         return Ajax.call([
//                 //             {
//                 //                 methodname: 'report_insights_action_executed',
//                 //                 args: {
//                 //                     predictionids: predictionIds,
//                 //                     actionname: actionName
//                 //                 }
//                 //             }
//                 //         ])[0].then(function() {
//                 //             // Remove the selected elements from the list.
//                 //
//                 //             var tableNode = false;
//                 //             predictionContainers.forEach(function(el) {
//                 //                 if (tableNode === false) {
//                 //                     tableNode = el.closest('table');
//                 //                 }
//                 //                 el.remove();
//                 //             });
//                 //
//                 //             if (tableNode.find('tbody > tr').length === 0) {
//                 //                 let params = {
//                 //                     contextid: tableNode.closest('div.insight-container').data('context-id'),
//                 //                     modelid: tableNode.closest('div.insight-container').data('model-id')
//                 //                 };
//                 //                 window.location.assign(Url.relativeUrl("report/insights/insights.php", params, false));
//                 //             }
//                 //             return;
//                 //         }).catch(Notification.exception);
//                 //     };
//                 //
//                 //     $(rootNode + ' [data-bulk-actionname]').on('click', function(e) {
//                 //         e.preventDefault();
//                 //         var action = $(e.currentTarget);
//                 //         var actionName = action.data('bulk-actionname');
//                 //         var actionVisibleName = action.text().trim();
//                 //
//                 //         var predictionIds = [];
//                 //         var predictionContainers = [];
//                 //
//                 //         $('.insights-list input[data-togglegroup^="insight-bulk-action-"][data-toggle="slave"]:checked').each(function() {
//                 //             var container = $(this).closest('tr[data-prediction-id]');
//                 //             predictionContainers.push(container);
//                 //             predictionIds.push(container.data('prediction-id'));
//                 //         });
//                 //
//                 //         if (predictionIds.length === 0) {
//                 //             // No items selected message.
//                 //             return this;
//                 //         }
//                 //
//                 //         var strings = [];
//                 //         Str.get_strings([{
//                 //                 key: 'confirmbulkaction',
//                 //                 component: 'report_insights',
//                 //                 param: {
//                 //                     action: actionVisibleName,
//                 //                     nitems: predictionIds.length
//                 //                 }
//                 //             }, {
//                 //                 key: 'confirm',
//                 //                 component: 'moodle'
//                 //             }]
//                 //         ).then(function(strs) {
//                 //             strings = strs;
//                 //             return ModalFactory.create({
//                 //                 type: ModalFactory.types.SAVE_CANCEL,
//                 //                 title: actionVisibleName,
//                 //                 body: strings[0],
//                 //             });
//                 //         }).then(function(modal) {
//                 //             modal.setSaveButtonText(strings[1]);
//                 //             modal.show();
//                 //             modal.getRoot().on(ModalEvents.save, function() {
//                 //                 // The action is now confirmed, sending an action for it.
//                 //                 return executeAction(predictionIds, predictionContainers, actionName);
//                 //             });
//                 //
//                 //             return modal;
//                 //         }).catch(Notification.exception);
//                 //
//                 //         return this;
//                 //     });
//                 // },
//             }
//         };
//     });
