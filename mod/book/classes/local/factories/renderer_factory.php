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
 * Renderer factory.
 *
 * @package    mod_book
 * @copyright  2020 Mihail Geshoski <mihailn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_book\local\factories;

defined('MOODLE_INTERNAL') || die();

use mod_book\local\builders\navigation\navigation_builder as navigation_builder;
use mod_book\local\builders\toc\toc_none_builder as toc_none_builder;
use mod_book\local\builders\toc\toc_bullets_builder as toc_bullets_builder;
use mod_book\local\builders\toc\toc_indented_builder as toc_indented_builder;
use mod_book\local\builders\toc\toc_numbers_builder as toc_numbers_builder;
use mod_book\local\renderers\renderer as renderer;
use mod_book\local\renderers\navigation\navigation_text_renderer as navigation_text_renderer;
use mod_book\local\renderers\navigation\navigation_image_renderer as navigation_image_renderer;
use mod_book\local\renderers\toc\toc_none_renderer as toc_none_renderer;
use mod_book\local\renderers\toc\toc_numbers_renderer as toc_numbers_renderer;
use mod_book\local\renderers\toc\toc_bullets_renderer as toc_bullets_renderer;
use mod_book\local\renderers\toc\toc_indented_renderer as toc_indented_renderer;

/**
 * Renderer factory.
 *
 * See:
 * https://designpatternsphp.readthedocs.io/en/latest/Creational/SimpleFactory/README.html
 *
 * @copyright  2020 Mihail Geshoski <mihailn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer_factory {

    /**
     * Return the relevant table of contents renderer.
     *
     * @param stdClass $book The book object
     * @return toc_renderer
     */
    public static function get_toc_renderer(string $chapterformatting, array $chapters, \stdClass $cm, bool $edit,
            ?int $currentchapterid = null): renderer {

        switch ($chapterformatting) {
            case BOOK_NUM_NONE:
                return new toc_none_renderer(new toc_none_builder($chapters, $cm, $edit, $currentchapterid));
            case BOOK_NUM_NUMBERS:
                return new toc_numbers_renderer(new toc_numbers_builder($chapters, $cm, $edit, $currentchapterid));
            case BOOK_NUM_BULLETS:
                return new toc_bullets_renderer(new toc_bullets_builder($chapters, $cm, $edit, $currentchapterid));
            case BOOK_NUM_INDENTED:
                return new toc_indented_renderer(new toc_indented_builder($chapters, $cm, $edit, $currentchapterid));
            default:
                throw new moodle_exception('Unknown chapter formatting');
        }
    }

    /**
     * Return the relevant book navigation renderer.
     *
     * @param stdClass $book The book object
     * @return renderer|null
     */
    public static function get_navigation_renderer(\stdClass $course, \stdClass $book, \stdClass $cm,
            \stdClass $currentchapter, bool $edit): ?renderer {

        switch ($book->navstyle) {
            case BOOK_LINK_TOCONLY:
                return null;
            case BOOK_LINK_IMAGE:
                return new navigation_image_renderer(new navigation_builder($course, $book, $cm, $currentchapter, $edit));
            case BOOK_LINK_TEXT:
                return new navigation_text_renderer(new navigation_builder($course, $book, $cm, $currentchapter, $edit));
            default:
                throw new coding_exception('Unknown navigation style');
        }
    }
}
