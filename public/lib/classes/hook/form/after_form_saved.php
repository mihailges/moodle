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

namespace core\hook\form;

/**
 * Allows plugins to act on the data submitted by a React form, once it was saved.
 *
 * It is dispatched inside the transaction that saves the record, so an exception rolls the save back.
 * Listeners should ignore forms they do not know by checking the form name.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\core\attribute\label('Allows plugins to act on the data submitted by a React form once it was saved')]
#[\core\attribute\tags('forms')]
final class after_form_saved {
    /**
     * Constructor.
     *
     * @param string $formname The name of the form, in the form "<component>_<form>".
     * @param int $id The id of the record that was created or updated.
     * @param array $payload The submitted data. For updates this is only the data the request contained.
     */
    public function __construct(
        /** @var string The name of the form. */
        public readonly string $formname,
        /** @var int The id of the record that was created or updated. */
        public readonly int $id,
        /** @var array The submitted data. */
        public readonly array $payload,
    ) {
    }
}
