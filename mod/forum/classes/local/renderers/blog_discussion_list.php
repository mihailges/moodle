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
 * Discussion list renderer.
 *
 * @package    mod_forum
 * @copyright  2019 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_forum\local\renderers;

defined('MOODLE_INTERNAL') || die();

use mod_forum\local\entities\forum as forum_entity;
use mod_forum\local\factories\legacy_data_mapper as legacy_data_mapper_factory;
use mod_forum\local\factories\exporter as exporter_factory;
use mod_forum\local\factories\vault as vault_factory;
use mod_forum\local\managers\capability as capability_manager;
use mod_forum\local\managers\url as url_manager;
use mod_forum\local\vaults\discussion_list as discussion_list_vault;
use renderer_base;
use stdClass;
use core\output\notification;
use mod_forum\local\factories\builder as builder_factory;

require_once($CFG->dirroot . '/mod/forum/lib.php');

/**
 * The discussion list renderer.
 *
 * @package    mod_forum
 * @copyright  2019 Andrew Nicols <andrew@nicols.co.uk>
 */
class blog_discussion_list extends discussion_list {
    /** @var forum_entity The forum being rendered */
    private $forum;

    /** @var stdClass The DB record for the forum being rendered */
    private $forumrecord;

    /** @var renderer_base The renderer used to render the view */
    private $renderer;

    /** @var legacy_data_mapper_factory $legacydatamapperfactory Legacy data mapper factory */
    private $legacydatamapperfactory;

    /** @var exporter_factory $exporterfactory Exporter factory */
    private $exporterfactory;

    /** @var vault_factory $vaultfactory Vault factory */
    private $vaultfactory;

    /** @var capability_manager $capabilitymanager Capability manager */
    private $capabilitymanager;

    /** @var url_manager $urlmanager URL manager */
    private $urlmanager;

    /** @var array $notifications List of notification HTML */
    private $notifications;

    private $builderfactory;

    /** @var callable $postprocessfortemplate Function to process exported posts before template rendering */
    private $postprocessfortemplate;

    /**
     * Constructor for a new discussion list renderer.
     *
     * @param   forum_entity        $forum The forum entity to be rendered
     * @param   renderer_base       $renderer The renderer used to render the view
     * @param   legacy_data_mapper_factory $legacy_data_mapper_factory The factory used to fetch a legacy record
     * @param   exporter_factory    $exporterfactory The factory used to fetch exporter instances
     * @param   vault_factory       $vaultfactory The factory used to fetch the vault instances
     * @param   capability_manager  $capabilitymanager The managed used to check capabilities on the forum
     * @param   notification[]      $notifications A list of any notifications to be displayed within the page
     */
    public function __construct(
        forum_entity $forum,
        renderer_base $renderer,
        legacy_data_mapper_factory $legacydatamapperfactory,
        exporter_factory $exporterfactory,
        vault_factory $vaultfactory,
        builder_factory $builderfactory,
        capability_manager $capabilitymanager,
        url_manager $urlmanager,
        array $notifications = [],
        callable $postprocessfortemplate = null
    ) {
        $this->forum = $forum;
        $this->renderer = $renderer;
        $this->legacydatamapperfactory = $legacydatamapperfactory;
        $this->exporterfactory = $exporterfactory;
        $this->vaultfactory = $vaultfactory;
        $this->builderfactory = $builderfactory;
        $this->capabilitymanager = $capabilitymanager;
        $this->urlmanager = $urlmanager;
        $this->notifications = $notifications;
        $this->postprocessfortemplate = $postprocessfortemplate;

        $forumdatamapper = $this->legacydatamapperfactory->get_forum_data_mapper();
        $this->forumrecord = $forumdatamapper->to_legacy_object($forum);
    }

    /**
     * Render for the specified user.
     *
     * @param   stdClass    $user The user to render for
     * @param   int         $groupid The group to render
     * @param   int         $sortorder The sort order to use when selecting the discussions in the list
     * @param   int         $pageno The zero-indexed page number to use
     * @param   int         $pagesize The number of discussions to show on the page
     * @return  string      The rendered content for display
     */
    public function render(stdClass $user, \cm_info $cm, ?int $groupid, ?int $sortorder, ?int $pageno, ?int $pagesize) : string {
        global $PAGE;

        $capabilitymanager = $this->capabilitymanager;
        $forum = $this->forum;

        $pagesize = $this->get_page_size($pagesize);
        $pageno = $this->get_page_number($pageno);

        $groupids = $this->get_groups_from_groupid($user, $groupid);
        $forumexporter = $this->exporterfactory->get_forum_exporter(
            $user,
            $this->forum,
            $groupid
        );

        // Count all forum discussion posts.
        $alldiscussionscount = $this->get_count_all_discussions($user, $groupids);

        // Get all forum discussions posts.
        $discussions = $this->get_discussions($user, $groupids, $sortorder, $pageno, $pagesize);

        $exportedpostsbuilder = $this->builderfactory->get_exported_posts_builder();
        $discussionentries = [];
        $postentries = [];

        foreach($discussions as $discussion) {
            $discussionentries[] = $discussion->get_discussion();
            $postentries[] = $discussion->get_first_post();
        }

        $exportedposts['posts'] = $exportedpostsbuilder->build(
            $user,
            [$forum],
            $discussionentries,
            $postentries
        );

        $exportedposts['state']['hasdiscussions'] = $exportedposts['posts'] ? true : false;

        // TODO: triggers error if the specified page number does not return any results.
        // if (!$discussions) {
        //     return;
        // }

        $forumview = array_merge(
            [
                'notifications' => $this->get_notifications($user, $groupid),
                'forum' => (array) $forumexporter->export($this->renderer),
                'groupchangemenu' => groups_print_activity_menu($cm, $this->urlmanager->get_forum_view_url_from_forum($forum), true),
                'pagination' => $this->renderer->render(new \paging_bar($alldiscussionscount, $pageno, $pagesize, $PAGE->url, 'p')),
            ],
            $exportedposts
        );

        return $this->renderer->render_from_template('mod_forum/blog_discussion_list', $forumview);
    }

}
