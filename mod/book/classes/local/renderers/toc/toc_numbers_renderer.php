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

namespace mod_book\local\renderers\toc;

defined('MOODLE_INTERNAL') || die();

use mod_book\local\builders\toc\toc_numbers_builder as toc_numbers_builder;
use mod_book\local\renderers\renderer_base as renderer_base;

/**
 * Table of contents renderer class.
 *
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class toc_numbers_renderer extends renderer_base {

    /** @var string $template The template to render */
    protected $template = 'mod_book/book_table_of_contents_numbered';

    /**
     * Constructor.
     *
     * @param toc_numbers_builder $builder The book chapters
     */
    public function __construct(toc_numbers_builder $builder) {
        parent::__construct($builder);
    }
}
