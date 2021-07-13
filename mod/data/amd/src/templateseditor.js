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
 * Javascript module to control the template editor.
 *
 * @module      mod_data/templateseditor
 * @package     mod_data
 * @copyright   2021 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString} from 'core/str';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';

/**
 * Template editor constants.
 */
const selectors = {
    toggleTemplateEditor: 'input[name="useeditor"]',
};

/**
 * Register event listeners for the module.
 */
const registerEventListeners = () => {
    const toggleTemplateEditor = document.querySelector(selectors.toggleTemplateEditor);

    toggleTemplateEditor.addEventListener('click', async(event) => {
        event.preventDefault();
        const actionUrl = event.target.getAttribute('data-action');

        if (event.target.checked) {
            const modal = await buildModal();
            await showModal(modal, actionUrl);
        } else {
            window.location.href = actionUrl;
        }
    });
};

/**
 * Create the 'enable template editor' confirmation modal.
 *
 * @method buildModal
 * @return {Promise} The modal promise (modal's body will be rendered later).
 */
const buildModal = async() => {
    return ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: await getString('confirmation', 'admin'),
        body: await getString('enabletemplateeditorcheck', 'mod_data'),
    });
};

/**
 * Show the 'enable template editor' confirmation modal.
 *
 * @method showModal
 * @param {Promise} modal The modal promise.
 * @param {String} actionUrl The URL to redirect to upon confirmation.
 */
const showModal = async(modal, actionUrl) => {
    modal.setSaveButtonText(await getString('yes', 'moodle'));

    modal.getRoot().on(ModalEvents.save, () => {
        window.location.href = actionUrl;
    });

    modal.getRoot().on(ModalEvents.hidden, () => {
        modal.destroy();
    });

    modal.show();
};

/**
 * Initialize module.
 */
export const init = () => {
    registerEventListeners();
};
