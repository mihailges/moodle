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
 * Exported post builder class.
 *
 * @package    mod_book
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_book\local\builders\toc;

defined('MOODLE_INTERNAL') || die();

use mod_book\local\builders\builder as builder;
use mod_book\local\entities\toc\toc_node as toc_node;

/**
 * Exported builder class.
 *
 * This class is an implementation of the builder pattern (loosely). It is responsible
 * for taking a set of related forums, discussions, and posts and generate the exported
 * version of the posts.
 *
 * It encapsulates the complexity involved with exporting posts. All of the relevant
 * additional resources will be loaded by this class in order to ensure the exporting
 * process can happen.
 *
 * See this doc for more information on the builder pattern:
 * https://designpatternsphp.readthedocs.io/en/latest/Creational/Builder/README.html
 *
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class toc_numbers_builder implements builder {

    /** @var array $chapters ID */
    public $chapters;
    /** @var \stdClass $cm Picture item id */
    public $cm;
    /** @var bool $edit Last name */
    public $edit;
    /** @var int|null $currentchapterid Picture item id */
    public $currentchapterid;

    /**
     * Constructor.
     *
     * @param array $chapters Core renderer
     * @param stdClass $chapter Legacy data mapper factory
     * @param stdClass $book Exporter factory
     * @param stdClass $cm Vault factory
     * @param bool $edit Rating manager
     */
    public function __construct(array $chapters, \stdClass $cm, bool $edit, ?int $currentchapterid = null) {
        $this->chapters = $chapters;
        $this->cm = $cm;
        $this->edit = $edit;
        $this->currentchapterid = $currentchapterid;
    }

    /**
     * Build the exported posts for a given set of forums, discussions, and posts.
     *
     * This will typically be used for a list of posts in the same discussion/forum however
     * it does support exporting any arbitrary list of posts as long as the caller also provides
     * a unique list of all discussions for the list of posts and all forums for the list of discussions.
     *
     * Increasing the number of different forums being processed will increase the processing time
     * due to processing multiple contexts (for things like capabilities, files, etc). The code attempts
     * to load the additional resources as efficiently as possible but there is no way around some of
     * the additional overhead.
     *
     * Note: Some posts will be removed as part of the build process according to capabilities.
     * A one-to-one mapping should not be expected.
     *
     * @return stdClass List of exported posts in the same order as the $posts array.
     */
    public function build() {
        $data = new \stdClass();
        $data->editmode = $this->edit;

        $context = \context_module::instance($this->cm->id);
        $viewhidden = has_capability('mod/book:viewhiddenchapters', $context);
        $chapters = array_values($this->chapters);
        $chaptersdata = [];

        $chapternum = 0;
        $subchapternum = 0;

        foreach ($chapters as $chapter) {
            if ($chapter->hidden && !$viewhidden) {
                continue;
            }

            $iscurrent = $chapter->id === $this->currentchapterid;
            $isfirst = $chapter === $chapters[0];
            $islast = $chapter === $chapters[count($chapters) - 1];

            $node = new toc_node($chapter, $this->cm, $iscurrent, $isfirst, $islast, $this->edit);
            if ($chapter->subchapter) { // Subchapter. Add it as a subnode of the last chapter node.
                $lastchapternode = $chaptersdata[count($chaptersdata) - 1];
                if (!$chapter->hidden) {
                    $subchapternum++;
                    $node->subnodenumber = $subchapternum;
                } else {
                    $node->subnodenumber = null;
                }

                $lastchapternode->add_subnode($node);

            } else { // Chapter.
                if (!$chapter->hidden) {
                    $subchapternum = 0;
                    $chapternum++;
                    $node->nodenumber = $chapternum;
                } else {
                    $node->nodenumber = null;
                }
                $chaptersdata[] = $node;
            }
        }
        $data->chaptersdata = $chaptersdata;

        return $data;
    }
}
