<?php
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

namespace core\api\form;

use core\api\token_manager;
use core\output\html_writer;
use core\output\notification;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form to create a personal access token, whose fields are fixed once it exists.
 *
 * @package    core
 * @copyright  Meirza Arson <meirza.arson@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_token extends \moodleform {
    /**
     * Prefix for the per-scope checkbox elements.
     *
     * @var string
     */
    protected const string SCOPE_ELEMENT_PREFIX = 'scope_';

    /**
     * Name of the element that labels the scope list and carries its error.
     *
     * @var string
     */
    protected const string SCOPE_LABEL = 'scopeslabel';

    /**
     * Id of the group wrapping the scopes label and checkboxes.
     *
     * @var string
     */
    protected const string SCOPE_GROUP = 'id_scopesgroup';

    /**
     * Map the available scopes to form element names, keyed by element name.
     *
     * @return string[] Scope identifiers keyed by element name.
     */
    protected function get_scope_elements(): array {
        /** @var token_manager $manager */
        $manager = $this->_customdata['manager'];
        $elements = [];

        // Colons are not usable in element names, so each becomes an underscore. Deriving the
        // name from the identifier rather than the position keeps it stable as scopes change.
        foreach (array_keys($manager->get_available_scopes()) as $identifier) {
            $elements[self::SCOPE_ELEMENT_PREFIX . str_replace(':', '_', $identifier)] = $identifier;
        }

        return $elements;
    }

    /**
     * Define the form.
     */
    protected function definition(): void {
        global $OUTPUT, $PAGE;

        $mform = $this->_form;
        /** @var token_manager $manager */
        $manager = $this->_customdata['manager'];

        $mform->addElement('text', 'name', get_string('pat_name'), ['maxlength' => 255]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('textarea', 'description', get_string('pat_description'), ['rows' => 3]);
        $mform->setType('description', PARAM_TEXT);

        // The offered periods are the manager's to state; the form only lists them.
        $mform->addElement('select', 'expirypreset', get_string('pat_expiry'), $manager->get_expiry_choices());
        $mform->setDefault('expirypreset', token_manager::DEFAULT_EXPIRY_PRESET);

        $scopes = $manager->get_available_scopes();

        // The checkboxes stay each their own form row: a group would lay them out inline, and a
        // description too wide for the rest of the line would drop beneath its own checkbox
        // rather than the one it belongs to.
        //
        // The label, info notification, all the checkboxes and (once the form is redisplayed
        // after a failed submit) the "no scope ticked" error all render inside this div, which
        // is otherwise invisible to layout. The group ties the checkboxes to the "Scopes" label
        // programmatically: separate form rows would otherwise announce each checkbox without
        // ever saying what the list is (SC 1.3.1). The JS module below names it from the
        // visible label, so entering the list announces "Scopes" like a fieldset legend would,
        // without a fieldset's layout or its repeated-legend verbosity in some screen readers.
        // Focus never lands on the group itself: scripted focus on a content-rich container is
        // announced inconsistently across screen readers (JAWS reads the name but not the
        // description of a group; NVDA speaks the name and description and then the container's
        // content at the browse-mode caret as well), so on a failed submit focus goes to the
        // first checkbox instead, and the group name is announced as its context.
        $mform->addElement('html', html_writer::start_div('', [
            'id' => self::SCOPE_GROUP,
            'role' => 'group',
        ]));

        // The hint carries the label, so "Scopes" sits in the label column like every other
        // field on this form and level with something to read. Not on the first checkbox: an
        // advcheckbox carrying a label renders its text in a described-by span rather than as
        // the label itself, which leaves a trailing line box and makes that row taller.
        // Marked required by hand: the requirement is that any one of the boxes is ticked, which
        // is not something a rule on a single element expresses.
        //
        // A plain notification() call renders as an assertive alert that a core script re-fires
        // on every page load regardless of whether the scopes error is present, which would
        // compete with, and duplicate, this container's own focus announcement on a failed
        // submit. set_announce(false) keeps the info box exactly as it looks, without that
        // competing announcement.
        $infonotification = new notification(get_string('pat_scopesinfo'), notification::NOTIFY_INFO, false);
        $infonotification->set_announce(false);

        $mform->addElement(
            'static',
            self::SCOPE_LABEL,
            get_string('pat_scopes') . ' ' . $OUTPUT->pix_icon('req', get_string('requiredelement', 'form')),
            // What the scopes are for, rather than that one is required: the marker beside the
            // label says that already, and so does the error when none is ticked.
            $OUTPUT->render($infonotification),
        );

        $scopeelements = $this->get_scope_elements();

        // A checkbox per scope, each its own form row: a form group would lay them out inline,
        // and a description too wide for the rest of the line drops beneath its own checkbox.
        foreach ($scopeelements as $elementname => $identifier) {
            $scope = $scopes[$identifier];
            $mform->addElement(
                'advcheckbox',
                $elementname,
                '',
                html_writer::div(
                    $scope::get_summary() . ' - ' .
                        html_writer::tag('code', $identifier, ['class' => 'fw-normal text-muted']),
                    'fw-bold',
                ) .
                    html_writer::div($scope::get_description(), 'text-muted small'),
            );
            $mform->setType($elementname, PARAM_BOOL);
        }

        $mform->addElement('html', html_writer::end_div());

        // On a failed submit with no scope ticked, name the first checkbox with the error and
        // move focus to it; the group name is announced as its context, ahead of it. A no-op
        // unless the error is present in the rendered markup: the error itself is only known
        // once the form is validated, which happens after this method has already run. Naming
        // the group from the scopes label, on the other hand, happens on every load, error or
        // not, so tabbing into the list announces what the list is (SC 1.3.1).
        $PAGE->requires->js_call_amd(
            'core/api/create_token_form',
            'init',
            [self::SCOPE_GROUP, 'id_error_' . self::SCOPE_LABEL, 'id_' . array_key_first($scopeelements)],
        );

        $this->add_action_buttons(true, get_string('pat_create'));
    }

    /**
     * Validate the submitted data.
     *
     * @param array $data The submitted data.
     * @param array $files The submitted files.
     * @return array Errors keyed by element name.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if (empty($this->get_submitted_scopes($data))) {
            // Reported against the label, which is the row that names the list.
            $errors[self::SCOPE_LABEL] = get_string('apitokennoscopes', 'error');
        }

        return $errors;
    }

    /**
     * Resolve the chosen period to an expiry timestamp.
     *
     * @param \stdClass $data The submitted data.
     * @return int The expiry timestamp.
     */
    public function get_expiry_time(\stdClass $data): int {
        /** @var token_manager $manager */
        $manager = $this->_customdata['manager'];

        // Resolved now rather than when the form was rendered, so a form left open overnight still
        // yields the number of days the user actually chose.
        return $manager->get_expiry_presets()[(int) $data->expirypreset];
    }

    /**
     * Resolve the checked scope elements back to scope identifiers.
     *
     * @param array $data The submitted data.
     * @return string[] The identifiers of the checked scopes.
     */
    public function get_submitted_scopes(array $data): array {
        $selected = [];

        foreach ($this->get_scope_elements() as $elementname => $identifier) {
            if (!empty($data[$elementname])) {
                $selected[] = $identifier;
            }
        }

        return $selected;
    }
}
