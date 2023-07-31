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

import GradebookBulkActions from "core_grades/bulkactions/bulk_actions";
import GradebookEditTreeBulkMove from "core_grades/bulkactions/edit/tree/move";

/**
 * Class for the bulk actions in the gradebook setup page.
 *
 * @module     core_grades/bulkactions/edit/tree/bulk_actions
 * @copyright  2023 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export default class GradebookEditTreeBulkActions extends GradebookBulkActions {

    /**
     * Returns the instance of the class.
     *
     * @param {int} courseID
     * @returns {GradebookEditTreeBulkActions}
     */
    static init(courseID) {
        return new this(courseID);
    }

    /**
     * The class constructor.
     *
     * @param {int} courseID The course ID.
     * @returns {void}
     */
    constructor(courseID) {
        super(courseID);
    }

    /**
     * Returns the array of the relevant bulk action objects for the gradebook setup page.
     *
     * @method getBulkActions
     * @returns {Array}
     */
    getBulkActions() {
        return [
            new GradebookEditTreeBulkMove(this.courseID)
        ];
    }

    /**
     * Returns the selector of a checkbox used to select items for bulk actions in the gradebook setup page.
     *
     * @method getBulkItemCheckboxSelector
     * @returns {string}
     */
    getBulkItemCheckboxSelector() {
        return 'input[type="checkbox"].itemselect';
    }
}
