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
 * Give the scopes their group context on the personal access token creation form, and focus
 * the first scope checkbox when the "select at least one scope" error is present.
 *
 * The scopes render as separate form rows with nothing programmatic tying them to the "Scopes"
 * label, so tabbing through the list would announce each checkbox without ever saying what the
 * list is (SC 1.3.1). The group names them from the visible label: the ARIA equivalent of a
 * fieldset and legend, without a fieldset's layout or its repeated-legend verbosity in some
 * screen readers.
 *
 * The validation error, when present, is reported against that static label rather than a form
 * control (no single checkbox is "the" invalid one, the set of them is), so mform has nothing
 * of its own to send focus to. Scripted focus on a content-rich container is announced
 * inconsistently across screen readers: JAWS reads the name but never the description of a
 * group, and NVDA speaks the name and description and then the container's content at the
 * browse-mode caret as well, reading the error twice. A native control has neither problem, so
 * focus goes to the first checkbox, named with the error, while the group around it is
 * announced as its context: label first, then the error, then the checkbox itself.
 *
 * @module     core/api/create_token_form
 * @copyright  2026 Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Name the scopes group, and focus the first scope checkbox if a scopes error is present.
 *
 * @param {string} groupId The id of the element grouping the label, notification, error and
 *                          scope checkboxes.
 * @param {string} errorId The id of the element carrying the scopes error message.
 * @param {string} checkboxId The id of the first scope checkbox.
 */
export const init = (groupId, errorId, checkboxId) => {
    const group = document.getElementById(groupId);
    const error = document.getElementById(errorId);

    if (!group || !error) {
        return;
    }

    // The static element's label column has no id in mform's markup, so lend it one: the group
    // is named from it, so entering the list announces "Scopes" like a fieldset legend would.
    // Referenced nodes stay visible throughout: Firefox (and so NVDA) does not honour the spec
    // exception that lets a hidden node still serve as an ARIA reference source, the way
    // Safari (and so VoiceOver) does, so hiding them would silence the references in NVDA.
    const label = group.querySelector('.col-form-label');
    if (label) {
        if (!label.id) {
            label.id = `${groupId}_label`;
        }
        group.setAttribute('aria-labelledby', label.id);
    }

    if (!error.textContent.trim().length) {
        // The form has nothing to report: the grouping above is all that is needed.
        return;
    }

    // Name the first checkbox with the label, then the error, then its own description (which
    // moves with it rather than being read twice). The label is repeated from the group
    // because VoiceOver does not announce a group's name when focus is scripted straight into
    // it, so without it here VO users would never hear "Scopes" on a failed submit; other
    // screen readers announce the group as context and may say the label twice, the lesser
    // cost. The error is part of the accessible name, not a description, so it is announced
    // ahead of the checkbox whatever the screen reader.
    const checkbox = document.getElementById(checkboxId);
    if (!checkbox) {
        return;
    }

    const names = [];
    if (label) {
        names.push(label.id);
    }
    names.push(errorId);
    const owndescription = checkbox.getAttribute('aria-describedby');
    if (owndescription) {
        names.push(owndescription);
        checkbox.removeAttribute('aria-describedby');
    }

    checkbox.setAttribute('aria-labelledby', names.join(' '));
    checkbox.setAttribute('aria-invalid', 'true');
    checkbox.focus();
};
