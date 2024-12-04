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
 * Class that defines content item selection process for creating an LTI external tool activity.
 *
 * @module     mod_lti/activitycontentitem
 * @copyright  2024 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ContentItem from 'core_ltix/contentitem';
import FormField from 'mod_lti/form-field';
import Templates from 'core/templates';


export default class ActivityContentItem extends ContentItem {

    /**
     * Array of form fields for LTI tool configuration.
     */
    ltiFormFields = [
        new FormField('name', FormField.TYPES.TEXT, false, ''),
        new FormField('introeditor', FormField.TYPES.EDITOR, false, ''),
        new FormField('toolurl', FormField.TYPES.TEXT, true, ''),
        new FormField('securetoolurl', FormField.TYPES.TEXT, true, ''),
        new FormField('instructorchoiceacceptgrades', FormField.TYPES.CHECKBOX, true, true),
        new FormField('instructorchoicesendname', FormField.TYPES.CHECKBOX, true, true),
        new FormField('instructorchoicesendemailaddr', FormField.TYPES.CHECKBOX, true, true),
        new FormField('instructorcustomparameters', FormField.TYPES.TEXT, true, ''),
        new FormField('icon', FormField.TYPES.TEXT, true, ''),
        new FormField('secureicon', FormField.TYPES.TEXT, true, ''),
        new FormField('launchcontainer', FormField.TYPES.SELECT, true, 0),
        new FormField('grade_modgrade_point', FormField.TYPES.TEXT, false, ''),
        new FormField('lineitemresourceid', FormField.TYPES.TEXT, true, ''),
        new FormField('lineitemtag', FormField.TYPES.TEXT, true, ''),
        new FormField('lineitemsubreviewurl', FormField.TYPES.TEXT, true, ''),
        new FormField('lineitemsubreviewparams', FormField.TYPES.TEXT, true, '')
    ];

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
        super(toolID, contextID, toolInstanceTitle, toolInstanceText);
    }

    /**
     * Defines the selector of the element that triggers the opening of content item selection modal.
     *
     * @method getBulkActionTriggerSelector
     * @returns {string}
     */
    getContentItemTriggerSelector() {
        return '[name="selectcontent"]';
    }

    /**
     * Optional method for defining custom action that will occur right after the trigger action of the content item
     * selection modal.
     *
     * @method processContentItemReturnData
     * @returns {void}
     */
    customContentItemTriggerActions() {
        this.toolInstanceTitle = document.querySelector('#id_name').value.trim();
        this.toolInstanceText = document.querySelector('#id_introeditor').value.trim();
    }


    /**
     * Window function that can be called from mod_lti/contentitem_return to close the dialogue and process the return data.
     * If the return data contains more than one item, the form will not be populated with item data
     * but rather hidden, and the item data will be added to a single input field used to create multiple
     * instances in one request.
     *
     * @param {object} returnData The fetched configuration data from the Content-Item selection dialogue.
     */
    processContentItemReturnData(returnData) {
        var index;
        if (returnData.multiple) {
            for (index in this.ltiFormFields) {
                // Name is required, so putting a placeholder as it will not be used
                // in multi-items add.
                this.ltiFormFields[index].setFieldValue(this.ltiFormFields[index].name === 'name' ? 'item' : null);
            }
            var variants = [];
            returnData.multiple.forEach(function(v) {
                variants.push(this.configToVariant(v));
            });
            this.showMultipleSummaryAndHideForm(returnData.multiple);
            const submitAndCourse = document.querySelector('#id_submitbutton2');
            submitAndCourse.onclick = (e) => {
                e.preventDefault();
                submitAndCourse.disabled = true;
                const fd = new FormData(document.querySelector('#region-main-box form'));
                const postVariant = (promise, variant) => {
                    Object.entries(variant).forEach((entry) => fd.set(entry[0], entry[1]));
                    const body = new URLSearchParams(fd);
                    const doPost = () => fetch(document.location.pathname, {method: 'post', body});
                    return promise.then(doPost).catch(doPost);
                };
                const backToCourse = () => {
                    document.querySelector("#id_cancel").click();
                };
                variants.reduce(postVariant, Promise.resolve()).then(backToCourse).catch(backToCourse);
            };
        } else {
            // Populate LTI configuration fields from return data.
            for (index in this.ltiFormFields) {
                var field = this.ltiFormFields[index];
                var value = null;
                if (typeof returnData[field.name] !== 'undefined') {
                    value = returnData[field.name];
                }
                field.setFieldValue(value);
            }
            field.setFieldValue(value);

            // Update the UI element which signifies content has been selected.
            document.querySelector("#id_selectcontentindicator").innerHTML = returnData.selectcontentindicator;

            // The state of the grade checkbox has already been set but that
            // hasn't fired the click/change event required by formslib to show/hide the dependent grade fields.
            // Fire it now.
            const allowGrades = document.querySelector('#id_instructorchoiceacceptgrades');
            let allowGradesChangeEvent = new Event('change');
            allowGrades.dispatchEvent(allowGradesChangeEvent);

            // If the tool is set to accept grades, make sure "Point" is selected.
            if (allowGrades.checked) {
                const gradeType = document.querySelector('#id_grade_modgrade_type');
                gradeType.value = "point";
                let gradeTypeChangeEvent = new Event('change');
                gradeType.dispatchEvent(gradeTypeChangeEvent);
            }
        }
    }

    /**
     * Hide the element, including aria and tab index.
     * @param {HTMLElement} e the element to be hidden.
     */
    hideElement(e) {
        e.setAttribute('hidden', 'true');
        e.setAttribute('aria-hidden', 'true');
        e.setAttribute('tab-index', '-1');
    }

    /**
     * Show the element, including aria and tab index (set to 1).
     * @param {HTMLElement} e the element to be shown.
     */
    showElement(e) {
        e.removeAttribute('hidden');
        e.setAttribute('aria-hidden', 'false');
        e.setAttribute('tab-index', '1');
    }

    /**
     * When more than one item needs to be added, the UI is simplified
     * to just list the items to be added. Form is hidden and the only
     * options is (save and return to course) or cancel.
     * This function injects the summary to the form page, and hides
     * the unneeded elements.
     *
     * @param {Object[]} items items to be added to the course.
     */
    async showMultipleSummaryAndHideForm(items) {
        const form = document.querySelector('#region-main-box form');
        const toolArea = form.querySelector('[data-attribute="dynamic-import"]');
        const buttonGroup = form.querySelector('#fgroup_id_buttonar');
        const submitAndLaunch = form.querySelector('#id_submitbutton');
        Array.from(form.children).forEach(this.hideElement);
        this.hideElement(submitAndLaunch);
        const {html, js} = await Templates.renderForPromise('mod_lti/tool_deeplinking_results',
            {items: items});

        await Templates.replaceNodeContents(toolArea, html, js);
        this.showElement(toolArea);
        this.showElement(buttonGroup);
    }

    /**
     * Transforms config values aimed at populating the lti mod form to JSON variant
     * which are used to insert more than one activity modules in one submit
     * by applying variation to the submitted form.
     * See /course/modedit.php.
     *
     * @private
     * @param {Object} config transforms a config to an actual form data to be posted.
     * @return {Object} variant that will be used to modify form values on submit.
     */
    configToVariant(config) {
        const variant = {};
        ['name', 'toolurl', 'securetoolurl', 'instructorcustomparameters', 'icon', 'secureicon',
            'launchcontainer', 'lineitemresourceid', 'lineitemtag', 'lineitemsubreviewurl',
            'lineitemsubreviewparams'].forEach(
            function(name) {
                variant[name] = config[name] || '';
            }
        );
        variant['introeditor[text]'] = config.introeditor ? config.introeditor.text : '';
        variant['introeditor[format]'] = config.introeditor ? config.introeditor.format : '';
        if (config.instructorchoiceacceptgrades === 1) {
            variant.instructorchoiceacceptgrades = '1';
            variant['grade[modgrade_point]'] = config.grade_modgrade_point || '100';
        } else {
            variant.instructorchoiceacceptgrades = '0';
        }
        return variant;
    }
}
