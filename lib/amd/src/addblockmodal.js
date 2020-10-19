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
 * Show an add block modal instead of doing it on a separate page.
 *
 * @module     core/addblockmodal
 * @class      addblockmodal
 * @package    core
 * @copyright  2016 Damyon Wiese <damyon@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalFactory from 'core/modal_factory';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
import Ajax from 'core/ajax';

const SELECTORS = {
    ADD_BLOCK: '[data-key="addblock"]'
};

/**
 * Register related event listeners.
 *
 * @method registerListenerEvents
 * @param {Number} pageContextId The context ID of the page
 * @param {String} pageType The type of the page
 * @param {String} pageLayout The layout of the page
 * @param {String} addBlockUrl The add block URL
 */
const registerListenerEvents = (pageContextId, pageType, pageLayout, addBlockUrl) => {
    document.addEventListener('click', async(e) => {

        if (e.target.closest(SELECTORS.ADD_BLOCK)) {
            e.preventDefault();
            // We want to instantly display the 'add block' modal while loading the body content. Once the blocks data
            // has been fetched, the body will be populated with the content.
            let bodyPromiseResolver;
            const bodyPromise = new Promise(resolve => {
                bodyPromiseResolver = resolve;
            });

            // Build and instantly display the 'add block' modal.
            buildAddBlockModal(bodyPromise);

            // Fetch all addable blocks in the given page.
            const blocks = await getAddableBlocks(pageContextId, pageType, pageLayout).catch(async(e) => {
                // If the promise is rejected, display the captured error in the modal.
                bodyPromiseResolver(await Templates.render('core/notification_error', {'message': e.message}));
            });

            if (!blocks) {
                return;
            }
            // If the blocks data has been successfully fetched, render the content within the modal's body.
            bodyPromiseResolver(await Templates.render(
                'core/add_block_body', {'blocks': blocks, 'url': addBlockUrl}));
        }
    });
};

/**
 * Method that creates and instantly displays the 'add block' modal.
 *
 * @method buildAddBlockModal
 * @param {Promise} bodyPromise
 * @return {Object} The displayed modal (modal's body will be rendered later).
 */
const buildAddBlockModal = (bodyPromise) => {
    return ModalFactory.create({
        type: ModalFactory.types.CANCEL,
        title: getString('addblock'),
        body: bodyPromise
    }).then(modal => {
        modal.show();
        return modal;
    });
};

/**
 * Method that fetches all addable blocks in a given page.
 *
 * @method getAddableBlocks
 * @param {Number} pageContextId The context ID of the page
 * @param {String} pageType The type of the page
 * @param {String} pageLayout The layout of the page
 * @return {Promise}
 */
const getAddableBlocks = async(pageContextId, pageType, pageLayout) => {
    const request = {
        methodname: 'core_block_get_page_addable_blocks',
        args: {
            pagecontextid: pageContextId,
            pagetype: pageType,
            pagelayout: pageLayout
        },
    };

    return Ajax.call([request])[0];
};

/**
 * Set up the actions.
 *
 * @method init
 * @param {Number} pageContextId The context ID of the page
 * @param {String} pageType The type of the page
 * @param {String} pageLayout The layout of the page
 * @param {String} addBlockUrl The add block URL
 */
export const init = (pageContextId, pageType, pageLayout, addBlockUrl) => {
    registerListenerEvents(pageContextId, pageType, pageLayout, addBlockUrl);
};
