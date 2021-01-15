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

/**
 * Date form field class.
 *
 * @package    core_form
 * @category   test
 * @copyright  2013 David Monllaó
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__  . '/behat_form_group.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;
use Behat\Mink\Element\NodeElement as NodeElement;

/**
 * Date form field.
 *
 * This class will be refactored in case we are interested in
 * creating more complex formats to fill date and date-time fields.
 *
 * @package    core_form
 * @category   test
 * @copyright  2013 David Monllaó
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_form_date_selector extends behat_form_group {

    /**
     * Sets the value to a date field.
     *
     * @param string $value The value to be assigned to the date selector field. The string value must be either
     *                      parsable into a UNIX timestamp or equal to 'disabled' (if disabling the date selector).
     * @return void
     * @throws ExpectationException If the value is invalid.
     */
    public function set_value($value) {

        $dateformfield = $this->field;

        if ($value === 'disabled') {
            // Disable the given date selector field.
            $this->set_field_availability($dateformfield, false);
        } else if (is_numeric($value)) { // The value is numeric (unix timestamp).
            // If the given date selector field is optional, make sure it is enabled before setting the values.
            $this->set_field_availability($dateformfield, true);

            // Assign the mapped values to each form element in the date selector field.
            foreach ($this->field_values_mapper($value) as $index => $value) {
                // Find the given form element in the date selector field.
                $this->field = $dateformfield->find('css', "*[name$='[{$index}]']");
                // Set the value to the form element. Delegate this responsibility to the parent class which will
                // detect the type of the element and properly set the value.
                parent::set_value($value);
            }
        } else { // Invalid value.
            // Get the name of the field.
            $fieldname = $dateformfield->find('css', 'legend')->getHtml();
            throw new ExpectationException("Invalid value for '{$fieldname}'", $this->session);
        }
    }

    /**
     * Maps the date field identifiers with the values to be assigned to them.
     *
     * @param int $timestamp The UNIX timestamp
     * @return array
     */
    protected function field_values_mapper(int $timestamp): array {
        return [
            'day' => date('j', $timestamp),
            'month' => date('n', $timestamp),
            'year' => date('Y', $timestamp)
        ];
    }

    /**
     * Sets the availability (enabled/disabled) of the date form field.
     *
     * @param NodeElement $field The date form field element
     * @param bool $available Whether the date form field element is enabled or disabled
     */
    private function set_field_availability(NodeElement $field, bool $available) {
        if ($this->field = $field->find('css', '*[name$="[enabled]"]')) {
            parent::set_value($available);
        }
    }
}
