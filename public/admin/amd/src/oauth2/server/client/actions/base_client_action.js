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
 * Base class for OAuth2 client action confirmation modals.
 *
 * @module     core_admin/oauth2/server/client/actions/base_client_action
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Notification from 'core/notification';

/**
 * Base class for OAuth2 client action confirmation modals.
 *
 * @abstract
 */
export default class BaseClientAction {

    /** @property {HTMLElement|null} target The active HTML element that initiated the confirmation routine. */
    target = null;

    /**
     * Initialise event delegation for the module action.
     */
    init() {
        document.addEventListener('click', async(e) => {
            // Resolve the event target via the child's abstract selector string.
            const target = e.target.closest(this.getActionSelector());
            if (!target) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            // Populate the class property for the duration of this action cycle
            this.target = target;

            const isDestructive = this.isDestructive();

            const iconStyleClass = isDestructive ? 'text-danger' : 'text-warning';
            const iconHtml = `<i class="fa fa-exclamation-triangle ${iconStyleClass} me-2" aria-hidden="true"></i>`;
            const title = await this.getTitle();

            const titleHtml = iconHtml + title;
            const body = await this.getBody();
            const buttonText = await this.getConfirmationText();

            const onConfirm = () => {
                this.executeConfirmAction();
            };

            if (isDestructive) {
                Notification.deleteCancel(titleHtml, body, buttonText, onConfirm);
            } else {
                Notification.confirm(titleHtml, body, buttonText, null, onConfirm);
            }
        });
    }

    /**
     * Method to return the CSS selector string to target elements via closest().
     *
     * @returns {string} The CSS selector string to target elements via closest().
     */
    getActionSelector() {
        throw new Error('Method getActionSelector() must be implemented.');
    }

    /**
     * Dictates what happens when the confirmation button inside the modal is clicked.
     *
     * @returns {Promise<void>|void}
     */
    executeConfirmAction() {
        throw new Error('Method executeConfirmAction() must be implemented.');
    }

    /**
     * Method to return the title text for the confirmation modal.
     *
     * @returns {Promise<string>} Resolved title string.
     */
    async getTitleText() {
        throw new Error('Method getTitleText() must be implemented.');
    }

    /**
     * Method to return the body string the confirmation modal.
     *
     * @returns {Promise<string>} Resolved body string.
     */
    async getBody() {
        throw new Error('Method getBody() must be implemented.');
    }

    /**
     * Method to return then confirmation button text for the confirmation modal.
     *
     * @returns {Promise<string>} Resolved button string.
     */
    async getConfirmationButtonText() {
        throw new Error('Method getConfirmationButtonText() must be implemented.');
    }

    /**
     * Method to determine if the confirmation modal should have a destructive styling based on the action.
     *
     * @returns {boolean} True if confirmation should trigger a destructive styling modal.
     */
    isDestructive() {
        throw new Error('Method isDestructive() must be implemented.');
    }
}
