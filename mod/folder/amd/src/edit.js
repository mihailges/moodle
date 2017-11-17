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
 * Edit folder JS module for the edit folder page.   
 *
 * @module     mod_folder/edit
 * @package    mod_folder
 * @copyright  2017 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery','core/validate_files_state'],
        function($, FilesStateValidator) {

    /**
     * Selectors.
     *
     * @access private
     * @type {{SUBMIT_BUTTON: string}}
     */
    var SELECTORS = {
        SUBMIT_BUTTON: '#id_submitbutton'
    };

    /**
     * Init function.
     *
     * @method init
     * @private
     */
    var init = function() {
        $(SELECTORS.SUBMIT_BUTTON).on('click', function(e) {
            e.preventDefault();
            form = $(this).closest('form');
            // Compare and validate the current folder files state and the state in the database.
            // Helps to prevent a race condition occurance.
            FilesStateValidator.validateFilesState(form);
        });
    };

    return /** @alias module:folder/edit */ {
        // Public variables and functions.
        /**
         * Initialise the edit folder module.
         *
         * @method init
         */
        'init': function() {
            init();
        }
    };
});