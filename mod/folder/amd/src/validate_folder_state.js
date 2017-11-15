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
 * Folder state validator JS module for the edit folder page.   
 *
 * @module     mod_folder/validate_folder_state
 * @package    mod_folder
 * @copyright  2017 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/notification', 'core/str'],
        function($, Ajax, Notification, Str) {

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
            var folderid = parseInt($('[name=folder_id]').val());
            var revision = parseInt($('[name=revision]').val());
            validateFolderState(folderid, revision);
        });
    };

    /**
     * Validate the folder state.
     *
     * @method validateFolderState
     * @param {int} folderid The folder id.
     * @param {int} revision The revision number of the currently displayed folder.
     * @private
     */
    var validateFolderState = function(folderid, revision) {
        var request = {
            methodname: 'mod_folder_folder_state_changed',
            args: {'folderid': folderid, 'revision': revision}
        };

        Ajax.call([request])[0].done(function(data) {
            if (data.status) {
                // Folder content has changed.
                $.when(Str.get_string('reload'), Str.get_string('cancel'))
                    .done(function(reload, cancel) {
                        Notification.confirm(
                            data.warnings[0].key,
                            data.warnings[0].message,
                            reload,
                            cancel,
                            function() {
                                window.location.reload();
                            }
                        );
                    });
            } else {
                // Folder content hasn't changed, safe to submit the form.
                window.onbeforeunload = null;
                var form = getForm();
                form.submit();
            }
        }).fail(Notification.exception);
    };

    /**
     * Return the folder form.
     *
     * @method getForm
     * @return {DOMElement}
     */
    var getForm = function() {
        return $(SELECTORS.SUBMIT_BUTTON).closest('form');
    };

    return /** @alias module:folder/validate_folder_state */ {
        // Public variables and functions.
        /**
         * Initialise the folder state validator.
         *
         * @method init
         */
        'init': function() {
            init();
        }
    };
});
