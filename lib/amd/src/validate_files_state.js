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
 * Files state validator JS module.   
 *
 * @module     core/validate_files_state
 * @package    mod_folder
 * @copyright  2017 Mihail Geshoski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/notification', 'core/str'],
        function($, Ajax, Notification, Str) {

     /**
     * Valid and required parameters.
     *
     * @access private
     * @type {array}
     */
    var REQUIRED_PARAMETERS = [
        'component',
        'componentid',
        'revision'
    ];

    /**
     * Validate the files state.
     *
     * @method validateFolderState
     * @param {DOMElement} form
     * @private
     */
    var validateFilesState = function(form) {
        var params = returnParams(form);
        
        if (params === undefined) {
            Notification.alert('Error', 'Missing parameters');
            return;
        }

        var request = {
            methodname: 'core_get_files_state',
            args: params
        };

        Ajax.call([request])[0].done(function(data) {
            if (data.status) {
                // Files content has changed.
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
                // Files content hasn't changed, safe to submit the form.
                window.onbeforeunload = null;
                form.submit();
            }
        }).fail(Notification.exception);
    };

    /**
     * Return the required form values.
     *
     * @method returnParams
     * @param {DOMElement} form
     * @private
     */
    var returnParams = function(form) {
        var paramObj = {};
        $.each(form.serializeArray(), function(e, v) {
            if (REQUIRED_PARAMETERS.indexOf(v.name) > -1) {
                paramObj[v.name] = v.value;
            }
        });

        if (paramObj.lenght == REQUIRED_PARAMETERS.lenght) {
            return paramObj;
        }   
    }

    return /** @alias module:folder/validate_folder_state */ {
        // Public variables and functions.
         /**
         * Validate the files state.
         *
         * @method validateFilesState
         * @param {DOMElement} form
         */
        'validateFilesState': function(form) {
            validateFilesState(form);
        }
    };
});
