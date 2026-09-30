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
 * of its own to send focus to. The error instead joins the group's accessible name — as a
 * description it would never be read by JAWS, which does not announce aria-describedby on
 * non-widget roles like group — and focus moves to the group holding the label, the error and
 * every checkbox, ahead of the list, so the first Tab reaches the first checkbox like any
 * other.
 *
 * @module     core/api/create_token_form
 * @copyright  2026 Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Name the scopes group, and focus it if a scopes error is present.
 *
 * @param {string} groupId The id of the element grouping the label, notification, error and
 *                          scope checkboxes.
 * @param {string} errorId The id of the element carrying the scopes error message.
 */
export const init = (groupId, errorId) => {
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
    //
    // The name always references the error element as well as the label, error or not: the
    // element renders empty when there is nothing to report, so it contributes nothing to the
    // name then, and when there is an error it is already rendered and visible. Setting the
    // name once here, rather than rewriting it when the error is found, keeps the
    // accessibility tree settled by the time focus moves: a name mutation and a focus event
    // arriving together were announced as the same name twice by NVDA and JAWS.
    let name = errorId;
    const label = group.querySelector('.col-form-label');
    if (label) {
        if (!label.id) {
            label.id = `${groupId}_label`;
        }
        name = `${label.id} ${errorId}`;
    }
    group.setAttribute('aria-labelledby', name);

    if (!error.textContent.trim().length) {
        // The form has nothing to report: the grouping above is all that is needed.
        return;
    }

    // Give the group an explicit description: the info notification, which describes what the
    // scopes are for. Without one, Chromium fabricates a description from the group's leftover
    // text content, and NVDA speaks that after the name, reading the notification and the
    // error a second time. JAWS never hears this description: it does not announce
    // aria-describedby on non-widget roles like group, which is why the error is in the name.
    const note = group.querySelector('.alert');
    if (note) {
        if (!note.id) {
            note.id = `${groupId}_note`;
        }
        group.setAttribute('aria-describedby', note.id);
    }

    group.setAttribute('aria-invalid', 'true');
    group.focus();
};
