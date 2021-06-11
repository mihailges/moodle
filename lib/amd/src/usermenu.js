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
 * Initializes and handles events in the user menu.
 *
 * @module     core/usermenu
 * @package    core
 * @copyright  2021 Moodle
 * @author     Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

/**
 * User menu constants.
 */
const Selectors = {
    userMenu: '.usermenu',
    userMenuSubmenuBtn: '.usermenu .submenu-btn',
};

/**
 * Register event listeners.
 */
const registerEventListeners = () => {
    // Handle the 'click' event on the submenu button element in the user menu.
    const userMenu = document.querySelector(Selectors.userMenu);
    userMenu.querySelectorAll(Selectors.userMenuSubmenuBtn).forEach(element => {
        element.addEventListener('click', (e) => {
            // By default the user menu dropdown element closes on a click event. This behaviour is not desirable
            // as we need to be able to expand or collapse the contents of a given submenu within the user menu.
            // Therefore, we need to prevent the propagation of this event and then manually expand or collapse
            // the content of the submenu.
            e.stopPropagation();
            // Toggle the aria-expanded attribute.
            const expand = e.target.getAttribute('aria-expanded') === 'false' ? 'true' : 'false';
            e.target.setAttribute('aria-expanded', expand);
            // Show or hide the submenu content.
            e.target.nextElementSibling.classList.toggle('show');
        });
    });

    // Reset the state and collapse any expanded submenu elements when the user menu dropdown is closed.
    $(Selectors.userMenu).on('hide.bs.dropdown', function() {
        $(this)[0].querySelectorAll(Selectors.userMenuSubmenuBtn).forEach((element) => {
            if (element.getAttribute('aria-expanded') === 'true') {
                // Toggle the aria-expanded attribute.
                element.setAttribute('aria-expanded', 'false');
                // Show or hide the group elements.
                element.nextElementSibling.classList.remove('show');
            }
        });
    });
};

/**
 * Initialize the user menu.
 */
const init = () => {
    registerEventListeners();
};

export default {
    init: init,
};
