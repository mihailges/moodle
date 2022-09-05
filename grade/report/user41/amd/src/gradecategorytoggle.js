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
 * Javascript module for toggling the visibility of the grade categories in the user report.
 *
 * @module      gradereport_user41/gradecategorytoggle
 * @copyright   2022 Mihail Geshoski <mihail@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const SELECTORS = {
    CATEGORY_TOGGLE: '.toggle-category',
    USER_REPORT_TABLE: '.user-grade'
};

/**
 * Register related event listeners.
 *
 * @method registerListenerEvents
 */
const registerListenerEvents = () => {
    const userReport = document.querySelector(SELECTORS.USER_REPORT_TABLE);

    document.addEventListener('click', e => {
        const toggle = e.target.closest(SELECTORS.CATEGORY_TOGGLE);

        if (toggle) {
            const target = toggle.getAttribute('data-target');
            const isExpanded = toggle.getAttribute('aria-expanded');

            toggle.setAttribute('aria-expanded', isExpanded == "true" ? "false" : "true");
            const rowDisplay = isExpanded == "true" ? "none" : "table-row";

            userReport.querySelectorAll(target).forEach((row) => {
                row.style.display = rowDisplay;
            });
        }
    });
};

/**
 * Initialize module.
 */
export const init = () => {
    registerListenerEvents();
};
