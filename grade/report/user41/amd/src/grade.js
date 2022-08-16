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
 * This will eventually become the grade item search widget caller.
 *
 * @module     gradereport_user41/grade
 * @copyright  2022 Mathew May <mathew.solutions>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This is all going to be extended and worked upon in the single view report base issue.
export const searchGradeitems = (sections, searchTerm) => {
    if (searchTerm === '') {
        return sections;
    }
    searchTerm = searchTerm.toLowerCase();
    const searchResults = [];
    sections.forEach((section) => {
        section.modules.forEach((module) => {
            const moduleName = module.name.toLowerCase();
            if (moduleName.includes(searchTerm)) {
                searchResults.push(module);
            }
        });
    });
    return searchResults;
};
