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
 * Table of contents renderer.
 *
 * @package    mod_book
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_book\local\renderers;

defined('MOODLE_INTERNAL') || die();

use mod_book\local\renderers\renderer;
use mod_book\local\builders\builder;

/**
 * Table of contents renderer class.
 *
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class renderer_base implements renderer {

    /** @var \stdClass $builder Builder for building the table of contents data */
    protected $builder;

    /**
     * Constructor.
     *
     * @param builder $builder The book chapters
     */
    public function __construct(builder $builder) {
        $this->builder = $builder;
    }

    /**
     * Render the given posts for the forums and discussions.
     *
     * @return string
     */
    public function render(): string {
        global $PAGE;

        $renderer = $PAGE->get_renderer('mod_book');

        return $renderer->render_from_template(
            $this->template,
            $this->builder->build()
        );
    }
}
