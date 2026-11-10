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

namespace core_admin\local\oauth2\server;

/**
 * Thrown when submitted OAuth 2 client data is invalid.
 *
 * @package    core_admin
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class client_validation_exception extends \moodle_exception {
    /**
     * Constructor.
     *
     * @param array<string, string[]> $errors Messages per field path, e.g. ['redirecturis.1' => ['Invalid URL']].
     */
    public function __construct(
        /** @var array<string, string[]> Messages per field path. */
        public readonly array $errors,
    ) {
        parent::__construct('invaliddata', 'error');
    }
}
