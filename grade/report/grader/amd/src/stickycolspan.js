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
 * Javascript module for fixing the position of sticky headers with multiple colspans
 *
 * @module      gradereport_grader/stickycolspan
 * @copyright   2022 Bas Brands <bas@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

const SELECTORS = {
    GRADEPARENT: '.gradeparent',
    STUDENTHEADER: '#studentheader',
    TABLEHEADER: 'th.header',
    BEHAT: 'body.behat-site',
    USERHEADERDROPDOWN: 'tr.userrow th.header .dropdown',
    DROPDOWNMENU: '.dropdown-menu'
};

/**
 * Initialize module
 */
export const init = () => {
    let userHeaderDropdownMenu;
    // The sticky positioning attributed to the user header cells affects the stacking context and makes the dropdowns
    // within these cells appear cut off. To solve this issue we need to detach the dropdown menu element (on show)
    // from the the current context and attach it to the body.
    $(SELECTORS.USERHEADERDROPDOWN).on('show.bs.dropdown', (e) => {
        userHeaderDropdownMenu = e.target.querySelector(SELECTORS.DROPDOWNMENU);
        // Calculate the proper positioning.
        const left = userHeaderDropdownMenu.offsetLeft -  window.scrollLeft;
        const top = userHeaderDropdownMenu.offsetTop - window.scrollLeft;
        userHeaderDropdownMenu.setAttribute('style',
            `position: fixed; display: block; z-index: 999; left: ${left}, top: ${top}`);
        // Move the dropdown menu element to the document's body.
        document.body.append(userHeaderDropdownMenu.parentNode.removeChild(userHeaderDropdownMenu));
    });
    // Return the dropdown menu element to its original context on hide.
    $(SELECTORS.USERHEADERDROPDOWN).on('hide.bs.dropdown', (e) => {
        userHeaderDropdownMenu.style.display = '';
        $(e.target).append(e.target.appendChild(userHeaderDropdownMenu));
    });

    if (!document.querySelector(SELECTORS.BEHAT)) {
        const grader = document.querySelector(SELECTORS.GRADEPARENT);
        const tableHeaders = grader.querySelectorAll(SELECTORS.TABLEHEADER);
        const studentHeader = grader.querySelector(SELECTORS.STUDENTHEADER);
        const leftOffset = getComputedStyle(studentHeader).getPropertyValue('left');
        const rightOffset = getComputedStyle(studentHeader).getPropertyValue('right');

        tableHeaders.forEach((tableHeader) => {
            if (tableHeader.colSpan > 1) {
                const addOffset = (tableHeader.offsetWidth - studentHeader.offsetWidth);
                if (window.right_to_left()) {
                    tableHeader.style.right = 'calc(' + rightOffset + ' - ' + addOffset + 'px )';
                } else {
                    tableHeader.style.left = 'calc(' + leftOffset + ' - ' + addOffset + 'px )';
                }
            }
        });
    }
};
