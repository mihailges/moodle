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
 * Move focus to the "select at least one scope" error, when present, on the personal access
 * token creation form.
 *
 * The error is reported against a static label rather than a form control (no single checkbox is
 * "the" invalid one, the set of them is), so mform has nothing of its own to send focus to.
 * Focus moves to the container holding the label and the error instead, ahead of the list:
 * focusing the first checkbox would start the user past it, its announcement already spent,
 * one Tab away from being skipped entirely, while from the container the first Tab reaches the
 * first checkbox like any other.
 *
 * @module     core/api/create_token_form
 * @copyright  2026 Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Move focus to the error container, if the form was redisplayed with a scopes error.
 *
 * @param {string} containerId The id of the element to focus, which already carries the label,
 *                              info notification and, when present, the error as its own content.
 * @param {string} errorId The id of the element carrying the scopes error message.
 */
export const init = (containerId, errorId) => {
    const container = document.getElementById(containerId);
    const error = document.getElementById(errorId);

    if (!container || !error || !error.textContent.trim().length) {
        // No container, no error container, or the form has nothing to report: nothing to do.
        return;
    }

    // A bare div has no accessible name, so focusing one leaves a screen reader nothing
    // programmatic to announce beyond whatever content it happens to start reading (JAWS reads
    // the "Scopes" label and stops, missing the error entirely). Give the container the same
    // identity a single invalid control would carry: a name pointing at the visible "Scopes"
    // label (the static element's label column has no id in mform's markup, so lend it one),
    // a description pointing at the visible error, and the invalid state. Both referenced
    // nodes stay visible: Firefox (and so NVDA) does not honour the spec exception that lets a
    // hidden node still serve as an aria-describedby source, the way Safari (and so VoiceOver)
    // does, so hiding them would silence the error in NVDA.
    container.setAttribute('role', 'group');

    const label = container.querySelector('.col-form-label');
    if (label) {
        if (!label.id) {
            label.id = `${containerId}_label`;
        }
        container.setAttribute('aria-labelledby', label.id);
    }

    container.setAttribute('aria-describedby', errorId);
    container.setAttribute('aria-invalid', 'true');

    container.focus();
};
