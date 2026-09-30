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
 * Focus the first scope checkbox when the "select at least one scope" error is present, and
 * name it with the scopes label and the error.
 *
 * The error is reported against a static label rather than a form control (no single checkbox is
 * "the" invalid one, the set of them is), so mform has nothing of its own to send focus to.
 * Focus goes to the first checkbox instead: a native control announces its name, invalid state
 * and description on focus more consistently across screen readers than a scripted focus on a
 * plain container does, and lands the user on something they can immediately act on. The error
 * itself stays displayed at the top of the scope list, ahead of the checkboxes.
 *
 * @module     core/api/create_token_form
 * @copyright  2026 Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Name and focus the first scope checkbox, if the form was redisplayed with a scopes error.
 *
 * @param {string} checkboxId The id of the first scope checkbox.
 * @param {string} errorId The id of the element carrying the scopes error message.
 */
export const init = (checkboxId, errorId) => {
    const checkbox = document.getElementById(checkboxId);
    const error = document.getElementById(errorId);

    if (!checkbox || !error || !error.textContent.trim().length) {
        // No checkbox, no error container, or the form has nothing to report: nothing to do.
        return;
    }

    // A focused control is announced name first, description last, so for the label and error
    // to be heard before the checkbox they have to be in its accessible name, not its
    // description: the visible "Scopes" label (the static element's label column has no id in
    // mform's markup, so lend it one), then the error, then the checkbox's own description,
    // which moves with them rather than being read twice. Every referenced node stays visible:
    // Firefox (and so NVDA) does not honour the spec exception that lets a hidden node still
    // serve as an ARIA reference source, the way Safari (and so VoiceOver) does, so hiding
    // them would silence the error in NVDA.
    const labelledby = [];
    const fitem = error.closest('.fitem');
    const label = fitem ? fitem.querySelector('.col-form-label') : null;
    if (label) {
        if (!label.id) {
            label.id = `${errorId}_label`;
        }
        labelledby.push(label.id);
    }
    labelledby.push(errorId);
    const owndescription = checkbox.getAttribute('aria-describedby');
    if (owndescription) {
        labelledby.push(owndescription);
        checkbox.removeAttribute('aria-describedby');
    }

    checkbox.setAttribute('aria-labelledby', labelledby.join(' '));
    checkbox.setAttribute('aria-invalid', 'true');
    checkbox.focus();
};
