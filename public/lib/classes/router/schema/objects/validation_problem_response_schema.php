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

namespace core\router\schema\objects;

use core\router\schema\referenced_object;

/**
 * The RFC 9457 problem details for a validation failure.
 *
 * This adds an `errors` extension member, which holds the validation messages for each field.
 *
 * @package    core
 * @copyright  2026 Moodle Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class validation_problem_response_schema extends problem_response_schema implements
    referenced_object
{
    #[\Override]
    protected static function get_content(): array {
        return array_merge(
            parent::get_content(),
            [
                'errors' => new validation_errors(),
            ],
        );
    }
}
