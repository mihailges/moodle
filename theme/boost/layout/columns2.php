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
 * A two column layout for the boost theme.
 *
 * @package   theme_boost
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');

// Add-a-block in editing mode.
if (isset($PAGE->theme->addblockposition) && $PAGE->user_is_editing() && $PAGE->user_can_edit_blocks()) {
    $url = new moodle_url($PAGE->url, ['bui_addblock' => '', 'sesskey' => sesskey()]);
    $block = new block_contents;
    $block->title = '';
    $block->content = $OUTPUT->action_link($url, get_string('addblock'), null, ['data-key' => 'addblock',  'class' => 'btn btn-outline-primary border-dashed w-100']);
    $block->footer = '';
    $this->page->blocks->add_fake_block($block, BLOCK_POS_RIGHT);

    $addblockurl = "?{$url->get_query_string(false)}";
    $PAGE->requires->js_call_amd('core/addblockmodal', 'init',
        [$PAGE->pagetype, $PAGE->pagelayout, $addblockurl]);

}

$extraclasses = [];
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;

$buildsecondarynavigation = $PAGE->has_secondary_navigation();
if ($buildsecondarynavigation) {
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs');
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
}

$primary = new core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'primarymoremenu' => $primarymenu['moremenu'],
    'secondarymoremenu' => $secondarynavigation ?: false,
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
];
$nav = $PAGE->flatnav;
$templatecontext['firstcollectionlabel'] = $nav->get_collectionlabel();
echo $OUTPUT->render_from_template('theme_boost/columns2', $templatecontext);
