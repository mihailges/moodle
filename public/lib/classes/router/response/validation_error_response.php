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

namespace core\router\response;

use core\exception\validation_exception;
use core\router\schema\objects\validation_problem_response_schema;
use core\router\schema\objects\schema_object;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A standard response for submitted data which failed validation.
 *
 * The body is an RFC 9457 problem details object with an `errors` extension member, which lists the messages for each field.
 *
 * @package    core
 * @copyright  2026 Moodle Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class validation_error_response extends exception_response {
    #[\Override]
    public static function get_exception_status_code(): int {
        return 422;
    }

    #[\Override]
    protected static function get_response_schema(): schema_object {
        return new validation_problem_response_schema();
    }

    #[\Override]
    protected static function get_response_payload(
        \Exception $exception,
        ServerRequestInterface $request,
    ): array {
        $data = parent::get_response_payload($exception, $request);

        // The deprecated members are not part of new responses.
        unset($data['message'], $data['errorcode']);

        if ($exception instanceof validation_exception) {
            // Messages which do not belong to a field describe the problem as a whole.
            $rooterrors = $exception->get_root_errors();
            if ($rooterrors) {
                $data['detail'] = implode(' ', $rooterrors);
            }
            $data['errors'] = (object) $exception->get_field_errors();
        } else {
            $data['errors'] = (object) [];
        }

        return $data;
    }
}
