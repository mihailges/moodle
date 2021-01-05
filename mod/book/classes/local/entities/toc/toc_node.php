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
 * Author class.
 *
 * @package    mod_book
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_book\local\entities\toc;

defined('MOODLE_INTERNAL') || die();

/**
 * Chapter class.
 *
 * @copyright  2020 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class toc_node {
    /** @var int $id ID */
    public $id;
    /** @var string $title Picture item id */
    public $title;
    /** @var string $titleunescaped Picture item id */
    public $titleunescaped;
    /** @var string $url Picture item id */
    public $url;
    /** @var bool $ishidden Last name */
    public $ishidden;
    /** @var bool $iscurrent Last name */
    public $iscurrent;
    /** @var bool $isfirst Last name */
    public $isfirst;
    /** @var bool $islast Last name */
    public $islast;
    /** @var string $actionlist Picture item id */
    public $actionlist;
    /** @var bool $hassubchapters First name */
    public $hassubnodes;
    /** @var array $subchapters Last name */
    public $subnodes;

    /**
     * Constructor.
     *
     * @param int $id ID
     * @param int $title Picture item id
     * @param string $ishidden First name
     * @param string $iscurrent Last name
     * @param \stdClass $cm Full name
     */
    public function __construct(
        \stdClass $chapter,
        \stdClass $cm,
        bool $iscurrent,
        bool $isfirst,
        bool $islast,
        bool $iseditmode
    ) {
        $context = \context_module::instance($cm->id);

        $this->id = $chapter->id;
        $this->title = trim(format_string($chapter->title, true, ['context' => $context]));
        $this->titleunescaped = trim(format_string($chapter->title, true, ['context' => $context, 'escape' => false]));
        $this->url = new \moodle_url('view.php', ['id' => $cm->id, 'chapterid' => $chapter->id]);
        $this->ishidden = $chapter->hidden;
        $this->iscurrent = $iscurrent;
        $this->isfirst = $isfirst;
        $this->islast = $islast;
        $this->actionlist = $iseditmode ? $this->node_action_urls($chapter, $cm) : null;
        $this->hassubnodes = false;
        $this->subnodes = [];
    }

    public function add_subnode(self $subchapter) {
        $this->subnodes[] = $subchapter;
        $this->hassubnodes = true;
    }

    protected function node_action_urls($chapter, $cm): array {
        global $USER;

        return [
            'moveupurl' => $this->isfirst ? null : new \moodle_url('move.php', [
                'id' => $cm->id,
                'chapterid' => $chapter->id,
                'up' => '1',
                'sesskey' => $USER->sesskey
            ]),
            'movedownurl' => $this->islast ? null : new \moodle_url('move.php', [
                'id' => $cm->id,
                'chapterid' => $chapter->id,
                'up' => '0',
                'sesskey' => $USER->sesskey
            ]),
            'editurl' => new \moodle_url('edit.php', [
                'cmid' => $cm->id,
                'id' => $chapter->id
            ]),
            'deleteurl' => new \moodle_url('delete.php', [
                'id' => $cm->id,
                'chapterid' => $chapter->id,
                'sesskey' => $USER->sesskey,
                'confirm' => 1
            ]),
            'displaytoggleurl' => new \moodle_url('show.php', [
                'id' => $cm->id,
                'chapterid' => $chapter->id,
                'sesskey' => $USER->sesskey
            ]),
            'addurl' => new \moodle_url('edit.php', [
                'cmid' => $cm->id,
                'pagenum' => $chapter->pagenum,
                'subchapter' => $chapter->subchapter
            ])
        ];
    }
}
