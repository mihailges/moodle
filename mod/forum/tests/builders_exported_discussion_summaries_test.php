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
 * The exported_posts builder tests.
 *
 * @package    mod_forum
 * @copyright  2019 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/generator_trait.php');

/**
 * The exported_posts builder tests.
 *
 * @package    mod_forum
 * @copyright  2019 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class builders_exported_discussion_summaries_testcase extends advanced_testcase {
    // Make use of the test generator trait.
    use mod_forum_tests_generator_trait;

    /** @var \mod_forum\local\builders\exported_posts */
    private $builder;

    private $user1;
    private $user2;
    private $course;
    private $forum;
    private $discussion1;
    private $discussion2;
    private $post1;
    private $post2;
    private $post3;
    private $post4;

    /**
     * Set up function for tests.
     */
    public function setUp() {
        // We must clear the subscription caches. This has to be done both before each test, and after in case of other
        // tests using these functions.
        \mod_forum\subscriptions::reset_forum_cache();

        $builderfactory = \mod_forum\local\container::get_builder_factory();
        $this->builder = $builderfactory->get_exported_discussion_summaries_builder();

        $datagenerator = $this->getDataGenerator();
        $this->user1 = $datagenerator->create_user();
        $this->user2 = $datagenerator->create_user();

        $this->course = $datagenerator->create_course();
        $forum = $datagenerator->create_module('forum', ['course' => $this->course->id]);

        list($this->discussion1, $this->post1) = $this->helper_post_to_forum($forum, $this->user1);
        list($this->discussion2, $this->post2) = $this->helper_post_to_forum($forum, $this->user2);
        $this->post3 = $this->helper_reply_to_post($this->post1, $this->user1);
        $this->post4 = $this->helper_reply_to_post($this->post1, $this->user2);

        $entityfactory = \mod_forum\local\container::get_entity_factory();
//        global $DB;
//        $this->course = $DB->get_record('course', ['id' => $forum->course]);
        $coursemodule = get_coursemodule_from_instance('forum', $forum->id);
        $context = context_module::instance($coursemodule->id);
        $this->forum = $entityfactory->get_forum_from_stdclass($forum, $context, $coursemodule, $this->course);
    }

    /**
     * Tear down function for tests.
     */
    public function tearDown() {
        // We must clear the subscription caches. This has to be done both before each test, and after in case of other
        // tests using these functions.
        \mod_forum\subscriptions::reset_forum_cache();
    }

    private function get_discussions($forum, $user, $sortorder) {
        $vaultfactory = mod_forum\local\container::get_vault_factory();
        $discussionvault = $vaultfactory->get_discussions_in_forum_vault();
        $managerfactory = mod_forum\local\container::get_manager_factory();
        $capabilitymanager = $managerfactory->get_capability_manager($forum);
        return $discussionvault->get_from_forum_id(
            $forum->get_id(),
            $capabilitymanager->can_view_hidden_posts($user),
            $user->id,
            $sortorder,
            100,
            0);
    }

    /**
     * Test the build function returns the exported posts in the order that the posts are
     * given.
     */
    public function test_build_returns_discussions() {
        $this->resetAfterTest();

        $discussions = $this->get_discussions($this->forum, $this->user1, 1);

        $exportedposts = $this->builder->build($this->user1, $this->forum, $discussions);

        $exporteddicussionids = array_map(function ($summary) {
            return $summary->discussion->id;
        }, $exportedposts['summaries']);

        $this->assertEquals([$this->discussion1->id, $this->discussion2->id], $exporteddicussionids,
                '', 0.0, 10, true);
    }

    public function test_build_returns_discussion_replies() {
        $this->resetAfterTest();

        $discussions = $this->get_discussions($this->forum, $this->user1, 1);

        $exportedposts = $this->builder->build($this->user1, $this->forum, $discussions);

        $exporteddicussionreplies = array_reduce($exportedposts['summaries'], function ($result, $summary) {
            $result[$summary->discussion->id] = $summary->replies;
            return $result;
        });

        $this->assertEquals(2, $exporteddicussionreplies[$this->discussion1->id]);
        $this->assertEquals(0, $exporteddicussionreplies[$this->discussion2->id]);
    }

    // TODO: check firstpostauthor seems like its not working.
    public function test_build_returns_discussion_firstpostauthor() {
        $this->resetAfterTest();

        $discussions = $this->get_discussions($this->forum, $this->user1, 1);

        $exportedposts = $this->builder->build($this->user1, $this->forum, $discussions);

        $exporteddicussionfirstpostauthorids = array_reduce($exportedposts['summaries'], function ($result, $summary) {
            $result[$summary->discussion->id] = $summary->firstpostauthor;
            return $result;
        });

        $this->assertEquals($this->user2->id, $exporteddicussionfirstpostauthorids[$this->discussion1->id]);
        $this->assertEquals($this->user2->id, $exporteddicussionfirstpostauthorids[$this->discussion2->id]);
    }

    // TODO: check latestpostauthor seems like its not working.
    public function test_build_returns_discussion_latestpostauthor() {
        $this->resetAfterTest();

        $discussions = $this->get_discussions($this->forum, $this->user1, 1);

        $exportedposts = $this->builder->build($this->user1, $this->forum, $discussions);

        $exporteddicussionfirstpostauthorids = array_reduce($exportedposts['summaries'], function ($result, $summary) {
            $result[$summary->discussion->id] = $summary->latestpostauthor->id;
            return $result;
        });

        $this->assertEquals($this->user2->id, $exporteddicussionfirstpostauthorids[$this->discussion1->id]);
        $this->assertEquals($this->user2->id, $exporteddicussionfirstpostauthorids[$this->discussion2->id]);
    }

    // TODO: check latestpostauthor seems like its not working.
    public function test_build_returns_discussion_latest_post_id() {
        $this->resetAfterTest();

        $discussions = $this->get_discussions($this->forum, $this->user1, 1);

        $exportedposts = $this->builder->build($this->user1, $this->forum, $discussions);

        $exporteddicussionfirstpostauthorids = array_reduce($exportedposts['summaries'], function ($result, $summary) {
            $result[$summary->discussion->id] = $summary->latestpostid;
            return $result;
        });

        $this->assertEquals($this->post4->id, $exporteddicussionfirstpostauthorids[$this->discussion1->id]);
        $this->assertEquals($this->post2->id, $exporteddicussionfirstpostauthorids[$this->discussion2->id]);
    }
}
