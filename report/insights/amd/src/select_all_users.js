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
 * @module     report_insights/select_all_users
 * @package    report_insights
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as PubSub from 'core/pubsub';
import CheckboxToggleAll from 'core/checkbox-toggleall';
import Templates from 'core/templates';

const SELECTORS = {
    CONTAINERS: {
        SELECTED_USERS_INFO: '[data-region="selected-users-info"]'
    },
    ACTIONS: {
        SELECT_ALL_EXISTING: '[data-action="select-all-existing"]',
        CLEAR_ALL: '[data-action="clear-all-selected"]',
    }
};

export const STATUSES = {
    NONE_SELECTED: 0,
    PARTIALY_SELECTED: 1,
    ALL_ON_PAGE_SELECTED: 2,
    ALL_EXISTING_SELECTED: 3
};

export let allUserIds;

export let selectedStatus = STATUSES.NONE_SELECTED;

const registerListenerEvents = () => {
    PubSub.subscribe(CheckboxToggleAll.events.checkboxToggled, (data) => {
        handleCheckboxToggle(data);
    });

    document.addEventListener('click',(e) => {
        let templateData = {};

        if (e.target.matches(SELECTORS.ACTIONS.SELECT_ALL_EXISTING)) {
            selectedStatus = STATUSES.ALL_EXISTING_SELECTED;
            templateData = {
                total: 1000
            };
            renderNotification(templateData);
        }

        if (e.target.matches(SELECTORS.ACTIONS.CLEAR_ALL)) {
            selectedStatus = STATUSES.NONE_SELECTED;
            renderNotification(templateData);
        }
    });
};

const handleCheckboxToggle = (data) => {
    let templateData = {};

    if (data.checkedSlaves.length == 0) {
        selectedStatus = STATUSES.NONE_SELECTED;
    } else if (data.slaves.length != data.checkedSlaves.length) {
        selectedStatus = STATUSES.PARTIALY_SELECTED;
        templateData = {
            selectednumber: data.checkedSlaves.length,
        };
    } else if (data.slaves.length == data.checkedSlaves.length) {
        selectedStatus = STATUSES.ALL_ON_PAGE_SELECTED;
        templateData = {
            selectednumber: data.checkedSlaves.length,
            total: 1000
        };
    }

    renderNotification(templateData);
};


const renderNotification = async (templateData) => {
    let templateName;

    switch (selectedStatus) {
        case STATUSES.PARTIALY_SELECTED:
            templateName = 'report_insights/insights_partially_selected';
            break;
        case STATUSES.ALL_ON_PAGE_SELECTED:
            templateName = 'report_insights/all_insights_on_page_selected';
            break;
        case STATUSES.ALL_EXISTING_SELECTED:
            templateName = 'report_insights/all_existing_insights_selected';
            break;
        default:
            templateName = '';
    }

    const selectedInsightsContainer = document.querySelector(SELECTORS.CONTAINERS.SELECTED_USERS_INFO);

    if (templateName.length > 0) {
        const {html, js} = await Templates.renderForPromise(templateName, templateData);
        await Templates.replaceNodeContents(selectedInsightsContainer, html, js);
    } else {
        selectedInsightsContainer.innerHTML = '';
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
export const init = () => {
    const selectedUsersInfoContainer = document.querySelector(SELECTORS.CONTAINERS.SELECTED_USERS_INFO);
    allUserIds = selectedUsersInfoContainer.dataset.alluserids;

    registerListenerEvents();
};
