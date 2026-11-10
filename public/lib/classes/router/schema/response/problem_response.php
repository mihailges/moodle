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
namespace core\router\schema\response;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A problem details response, as defined by RFC 9457.
 *
 * @package    core
 * @copyright  2026 Mihail Geshoski <mihailgesoski@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_response extends payload_response {
    /**
     * Create a validation problem response (422) with the messages keyed by field path.
     *
     * @param string $detail A human-readable explanation of this occurrence of the problem
     * @param array<string, string[]> $errors The error messages, keyed by field path
     * @param ServerRequestInterface $request The request
     * @param int $status The HTTP status code
     * @param string $type A URI reference identifying the problem type
     */
    public function __construct(
        string $detail,
        array $errors,
        ServerRequestInterface $request,
        int $status = 422,
        string $type = 'about:blank',
    ) {
        $response = new \GuzzleHttp\Psr7\Response($status);
        parent::__construct(
            payload: [
                'type' => $type,
                'title' => $response->getReasonPhrase(),
                'status' => $status,
                'detail' => $detail,
                'instance' => (string) $request->getUri()->getPath(),
                // An extension member of the problem type: the messages by field path.
                'errors' => (object) $errors,
            ],
            request: $request,
            response: $response,
        );
    }

    #[\Override]
    public function get_response(
        ResponseFactoryInterface $responsefactory,
    ): ?ResponseInterface {
        return parent::get_response($responsefactory)
            ->withHeader('Content-Type', 'application/problem+json; charset=utf-8');
    }
}
