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
 * Launches the modal dialogue that contains the iframe that sends the Content-Item selection request to an
 * LTI tool provider that supports Content-Item type message.
 *
 * See template: core_ltix/contentitem
 *
 * @module     core_ltix/contentitem
 * @copyright  2024 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      5.0
 */

import Notification from 'core/notification';
import {getString} from 'core/str';
import Templates from 'core/templates';
import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';
import Url from 'core/url';

export default class ContentItem {

    /** @property {int|null} toolID The tool ID. */
    toolID = null;

    /** @property {int|null} contextID The context ID. */
    contextID = null;

    /** @property {string|null} toolInstanceTitle The tool instance title. */
    toolInstanceTitle = null;

    /** @property {string|null} toolInstanceText The tool instance text. */
    toolInstanceText = null;

    /** @property {string|null} toolInstanceText The tool instance text. */
    modal = null;

    className = null;

    /**
     * Initializes the content item selection process.
     *
     * @param {int} toolID The tool ID.
     * @param {int} contextID The context ID.
     * @param {string|null} toolInstanceTitle The tool instance title.
     * @param {string|null} toolInstanceText The tool instance text.
     * @returns {ContentItemSelection}
     */
    static async init(toolID, contextID, toolInstanceTitle = null, toolInstanceText = null) {
        const contentItem = new this(toolID, contextID, toolInstanceTitle, toolInstanceText);
        contentItem.registerEventListeners();
        // Store the class object in the global scope to be accessible later in the content item selection iframe.
        globalThis[contentItem.className] = contentItem;
    }

    /**
     * The class constructor.
     *
     * @param {int} toolID The tool ID.
     * @param {int} contextID The context ID.
     * @param {string|null} toolInstanceTitle The tool instance title.
     * @param {string|null} toolInstanceText The tool instance text.
     * @returns {void}
     */
    constructor(toolID, contextID, toolInstanceTitle = null, toolInstanceText = null) {
        this.toolID = toolID;
        this.contextID = contextID;
        this.toolInstanceTitle = toolInstanceTitle;
        this.toolInstanceText = toolInstanceText;
        this.className = this.constructor.name;
    }

    /**
     * Registers the listener events for the content item selection.
     *
     * @method registerListenerEvents
     * @param {HTMLElement} containerElement The container element for the bulk actions.
     * @returns {void}
     */
    registerEventListeners() {
        document.addEventListener('click', async (e) => {
            if (e.target.closest(this.getContentItemTriggerSelector())) {
                e.preventDefault();
                this.customContentItemTriggerActions();
                this.modal = await this.showModal();
            }
        });
    }

    /**
     * Show the content item selection modal.
     *
     * @method showModal
     * @returns {Promise} The modal promise
     */
    async showModal() {

        const modal = await Modal.create({
            title: await getString('selectcontent', 'lti'),
            body: await this.renderModalBody(),
            large: true,
        });

        // Handle hidden event.
        modal.getRoot().on(ModalEvents.hidden, () => {
            modal.destroy();
            // Fetch notifications.
            Notification.fetchNotifications();
        });

        modal.show();

        return modal;
    }

    /**
     * Renders the content item selection modal body.
     *
     * @method renderModalBody
     * @returns {Promise} The modal body promise
     */
    async renderModalBody() {
        var context = {
            url: Url.relativeUrl('/ltix/contentitem.php'),
            postData: {
                toolid: this.toolID,
                contextid: this.contextID,
                toolinstancetitle: this.toolInstanceTitle,
                toolinstancetext: this.toolInstanceText
            }
        };

        return Templates.render('core_ltix/contentitem', context);
    }

    /**
     * Renders the content item selection modal body.
     *
     * @method renderModalBody
     * @returns {Promise} The modal body promise
     */
    contentItemReturnAction(returnData) {
        if (modal) {
            modal.hide();
        }

        processContentItemReturnData(returnData)
    }

    /**
     * Optional method for defining custom action that will occur right after the trigger action of the content item
     * selection modal.
     *
     * @method processContentItemReturnData
     * @returns {void}
     */
    customContentItemTriggerActions() {
        return;
    }

    /**
     * Defines the selector of the element that triggers the opening of content item selection modal.
     *
     * @method getBulkActionTriggerSelector
     * @returns {string}
     */
    getContentItemTriggerSelector() {
        throw new Error(`getContentItemTriggerSelector() must be implemented in ${this.constructor.name}`);
    }

    /**
     * Defines the custom logic for processing the content item return data.
     *
     * @method processContentItemReturnData
     * @returns {void}
     */
    processContentItemReturnData(returnData) {
        throw new Error(`processContentItemReturnData() must be implemented in ${this.constructor.name}`);
    };
}

//
// import jquery from 'core/ajax';
// import ModalCancel from "core/modal_cancel";
// import Templates from 'core/templates';
// import {getString} from 'core/str';
// import GradebookEditTreeBulkMove from "../../../grade/amd/src/bulkactions/edit/tree/move";
// import ModalSaveCancel from "../../../lib/amd/src/modal_save_cancel";
// import Url from "../../../lib/amd/src/url";
//
// define(
//     [
//         'jquery',
//         'core/notification',
//         'core/str',
//         'core/templates',
//         'mod_lti/form-field',
//         'core/modal',
//         'core/modal_events'
//     ],
//     function($, notification, str, templates, FormField, Modal, ModalEvents) {
//         var dialogue;
//         var doneCallback;
//         var contentItem = {
//             /**
//              * Init function.
//              *
//              * @param {string} url The URL for the content item selection.
//              * @param {object} postData The data to be sent for the content item selection request.
//              * @param {Function} cb The callback to run once the content item has been processed.
//              */
//             init: function(url, postData, cb) {
//                 doneCallback = cb;
//                 var context = {
//                     url: url,
//                     postData: postData
//                 };
//                 var bodyPromise = templates.render('mod_lti/contentitem', context);
//
//                 if (dialogue) {
//                     // Set dialogue body.
//                     dialogue.setBody(bodyPromise);
//                     // Display the dialogue.
//                     dialogue.show();
//                     return;
//                 }
//
//                 str.get_string('selectcontent', 'lti').then(function(title) {
//                     return Modal.create({
//                         title: title,
//                         body: bodyPromise,
//                         large: true,
//                         show: true,
//                     });
//                 }).then(function(modal) {
//                     dialogue = modal;
//                     // On hide handler.
//                     modal.getRoot().on(ModalEvents.hidden, function() {
//                         // Empty modal contents when it's hidden.
//                         modal.setBody('');
//
//                         // Fetch notifications.
//                         notification.fetchNotifications();
//                     });
//                     return;
//                 }).catch(notification.exception);
//             }
//         };
//
//         /**
//          * Array of form fields for LTI tool configuration.
//          */
//         var ltiFormFields = [
//             new FormField('name', FormField.TYPES.TEXT, false, ''),
//             new FormField('introeditor', FormField.TYPES.EDITOR, false, ''),
//             new FormField('toolurl', FormField.TYPES.TEXT, true, ''),
//             new FormField('securetoolurl', FormField.TYPES.TEXT, true, ''),
//             new FormField('instructorchoiceacceptgrades', FormField.TYPES.CHECKBOX, true, true),
//             new FormField('instructorchoicesendname', FormField.TYPES.CHECKBOX, true, true),
//             new FormField('instructorchoicesendemailaddr', FormField.TYPES.CHECKBOX, true, true),
//             new FormField('instructorcustomparameters', FormField.TYPES.TEXT, true, ''),
//             new FormField('icon', FormField.TYPES.TEXT, true, ''),
//             new FormField('secureicon', FormField.TYPES.TEXT, true, ''),
//             new FormField('launchcontainer', FormField.TYPES.SELECT, true, 0),
//             new FormField('grade_modgrade_point', FormField.TYPES.TEXT, false, ''),
//             new FormField('lineitemresourceid', FormField.TYPES.TEXT, true, ''),
//             new FormField('lineitemtag', FormField.TYPES.TEXT, true, ''),
//             new FormField('lineitemsubreviewurl', FormField.TYPES.TEXT, true, ''),
//             new FormField('lineitemsubreviewparams', FormField.TYPES.TEXT, true, '')
//         ];
//
//         /**
//          * Hide the element, including aria and tab index.
//          * @param {HTMLElement} e the element to be hidden.
//          */
//         const hideElement = (e) => {
//             e.setAttribute('hidden', 'true');
//             e.setAttribute('aria-hidden', 'true');
//             e.setAttribute('tab-index', '-1');
//         };
//
//         /**
//          * Show the element, including aria and tab index (set to 1).
//          * @param {HTMLElement} e the element to be shown.
//          */
//         const showElement = (e) => {
//             e.removeAttribute('hidden');
//             e.setAttribute('aria-hidden', 'false');
//             e.setAttribute('tab-index', '1');
//         };
//
//         /**
//          * When more than one item needs to be added, the UI is simplified
//          * to just list the items to be added. Form is hidden and the only
//          * options is (save and return to course) or cancel.
//          * This function injects the summary to the form page, and hides
//          * the unneeded elements.
//          * @param {Object[]} items items to be added to the course.
//          */
//         const showMultipleSummaryAndHideForm = async function(items) {
//             const form = document.querySelector('#region-main-box form');
//             const toolArea = form.querySelector('[data-attribute="dynamic-import"]');
//             const buttonGroup = form.querySelector('#fgroup_id_buttonar');
//             const submitAndLaunch = form.querySelector('#id_submitbutton');
//             Array.from(form.children).forEach(hideElement);
//             hideElement(submitAndLaunch);
//             const {html, js} = await templates.renderForPromise('mod_lti/tool_deeplinking_results',
//                 {items: items});
//
//             await templates.replaceNodeContents(toolArea, html, js);
//             showElement(toolArea);
//             showElement(buttonGroup);
//         };
//
//         /**
//          * Transforms config values aimed at populating the lti mod form to JSON variant
//          * which are used to insert more than one activity modules in one submit
//          * by applying variation to the submitted form.
//          * See /course/modedit.php.
//          * @private
//          * @param {Object} config transforms a config to an actual form data to be posted.
//          * @return {Object} variant that will be used to modify form values on submit.
//          */
//         var configToVariant = (config) => {
//             const variant = {};
//             ['name', 'toolurl', 'securetoolurl', 'instructorcustomparameters', 'icon', 'secureicon',
//                 'launchcontainer', 'lineitemresourceid', 'lineitemtag', 'lineitemsubreviewurl',
//                 'lineitemsubreviewparams'].forEach(
//                 function(name) {
//                     variant[name] = config[name] || '';
//                 }
//             );
//             variant['introeditor[text]'] = config.introeditor ? config.introeditor.text : '';
//             variant['introeditor[format]'] = config.introeditor ? config.introeditor.format : '';
//             if (config.instructorchoiceacceptgrades === 1) {
//                 variant.instructorchoiceacceptgrades = '1';
//                 variant['grade[modgrade_point]'] = config.grade_modgrade_point || '100';
//             } else {
//                 variant.instructorchoiceacceptgrades = '0';
//             }
//             return variant;
//         };
//
//         /**
//          * Window function that can be called from mod_lti/contentitem_return to close the dialogue and process the return data.
//          * If the return data contains more than one item, the form will not be populated with item data
//          * but rather hidden, and the item data will be added to a single input field used to create multiple
//          * instances in one request.
//          *
//          * @param {object} returnData The fetched configuration data from the Content-Item selection dialogue.
//          */
//         window.processContentItemReturnData = function(returnData) {
//             if (dialogue) {
//                 dialogue.hide();
//             }
//             var index;
//             if (returnData.multiple) {
//                 for (index in ltiFormFields) {
//                     // Name is required, so putting a placeholder as it will not be used
//                     // in multi-items add.
//                     ltiFormFields[index].setFieldValue(ltiFormFields[index].name === 'name' ? 'item' : null);
//                 }
//                 var variants = [];
//                 returnData.multiple.forEach(function(v) {
//                     variants.push(configToVariant(v));
//                 });
//                 showMultipleSummaryAndHideForm(returnData.multiple);
//                 const submitAndCourse = document.querySelector('#id_submitbutton2');
//                 submitAndCourse.onclick = (e) => {
//                     e.preventDefault();
//                     submitAndCourse.disabled = true;
//                     const fd = new FormData(document.querySelector('#region-main-box form'));
//                     const postVariant = (promise, variant) => {
//                         Object.entries(variant).forEach((entry) => fd.set(entry[0], entry[1]));
//                         const body = new URLSearchParams(fd);
//                         const doPost = () => fetch(document.location.pathname, {method: 'post', body});
//                         return promise.then(doPost).catch(doPost);
//                     };
//                     const backToCourse = () => {
//                         document.querySelector("#id_cancel").click();
//                     };
//                     variants.reduce(postVariant, Promise.resolve()).then(backToCourse).catch(backToCourse);
//                 };
//             } else {
//                 // Populate LTI configuration fields from return data.
//                 for (index in ltiFormFields) {
//                     var field = ltiFormFields[index];
//                     var value = null;
//                     if (typeof returnData[field.name] !== 'undefined') {
//                         value = returnData[field.name];
//                     }
//                     field.setFieldValue(value);
//                 }
//                 field.setFieldValue(value);
//
//                 // Update the UI element which signifies content has been selected.
//                 document.querySelector("#id_selectcontentindicator").innerHTML = returnData.selectcontentindicator;
//             }
//
//             if (doneCallback) {
//                 doneCallback(returnData);
//             }
//         };
//
//         return contentItem;
//     }
// );
