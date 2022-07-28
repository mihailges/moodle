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

namespace core_grades\output;

use core\navigation\output\tertiary\navigation_selector;
use core\navigation\output\tertiary\navigation_selector_action_item;
use core\navigation\output\tertiary\navigation_selector_group_item;
use moodle_url;

/**
 * Renderable class for the general action bar in the gradebook pages.
 *
 * This class is responsible for rendering the general navigation select menu in the gradebook pages.
 *
 * @package    core_grades
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class general_action_bar extends action_bar {

    /** @var moodle_url $activeurl The URL that should be set as active in the URL selector element. */
    protected $activeurl;

    /**
     * The type of the current gradebook page (report, settings, import, export, scales, outcomes, letters).
     *
     * @var string $activetype
     */
    protected $activetype;

    /** @var string $activeplugin The plugin of the current gradebook page (grader, fullview, ...). */
    protected $activeplugin;

    /**
     * The class constructor.
     *
     * @param \context $context The context object.
     * @param moodle_url $activeurl The URL that should be set as active in the URL selector element.
     * @param string $activetype The type of the current gradebook page (report, settings, import, export, scales,
     *                           outcomes, letters).
     * @param string $activeplugin The plugin of the current gradebook page (grader, fullview, ...).
     */
    public function __construct(\context $context, moodle_url $activeurl, string $activetype, string $activeplugin) {
        parent::__construct($context);
        $this->activeurl = $activeurl;
        $this->activetype = $activetype;
        $this->activeplugin = $activeplugin;
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        $navigationselector = $this->get_action_selector();

        if (is_null($navigationselector)) {
            return [];
        }

        return [
            'generalnavselector' => $navigationselector->export_for_template($output),
        ];
    }

    /**
     * Returns the template for the action bar.
     *
     * @return string
     */
    public function get_template(): string {
        return 'core_grades/general_action_bar';
    }

    /**
     * Returns the tertiary navigation selector object.
     *
     * @return \navigation_selector|null The tertiary navigation selector object.
     */
    private function get_action_selector(): ?navigation_selector {
        if ($this->context->contextlevel !== CONTEXT_COURSE) {
            return null;
        }
        $courseid = $this->context->instanceid;
        $plugininfo = grade_get_plugin_info($courseid, $this->activetype, $this->activeplugin);

        $tertiarynavselector = new navigation_selector(get_string('gradebooktertiarynavigation', 'grades'));

        $viewgroup = new navigation_selector_group_item(get_string('view'));
        $setupgroup = new navigation_selector_group_item(get_string('setup', 'grades'));
        $moregroup = new navigation_selector_group_item(get_string('moremenu'));

        foreach ($plugininfo as $plugintype => $plugins) {
            // Skip if the plugintype value is 'strings'. This particular item only returns an array of strings
            // which we do not need.
            if ($plugintype == 'strings') {
                continue;
            }

            // If $plugins is actually the definition of a child-less parent link.
            if (!empty($plugins->id)) {
                $string = $plugins->string;
                if (!empty($plugininfo[$this->activetype]->parent)) {
                    $string = $plugininfo[$this->activetype]->parent->string;
                }
                $tertiarynavselector->add_item(new navigation_selector_action_item($string, $plugins->link,
                    $this->activeurl == $plugins->link));
                continue;
            }

            foreach ($plugins as $key => $plugin) {
                // Depending on the plugin type, include the plugin to the appropriate item group for the tertiary
                // navigation selector.
                switch ($plugintype) {
                    case 'report':
                        $reportitem = new navigation_selector_action_item($plugin->string, $plugin->link,
                            $this->activeurl == $plugin->link);
                        $viewgroup->add_action_item($reportitem);
                        break;
                    case 'settings':
                        $setupitem = new navigation_selector_action_item($plugin->string, $plugin->link,
                            $this->activeurl == $plugin->link);
                        $setupgroup->add_action_item($setupitem);
                        break;
                    case 'scale':
                        // We only need the link to the 'view scales' page, otherwise skip and continue to the next
                        // plugin.
                        if ($key !== 'view') {
                            continue 2;
                        }
                        $moreitem = new navigation_selector_action_item(get_string('scales'), $plugin->link,
                            $this->activeurl == $plugin->link);
                        $moregroup->add_action_item($moreitem);
                        break;
                    case 'outcome':
                        // We only need the link to the 'outcomes used in course' page, otherwise skip and continue to
                        // the next plugin.
                        if ($key !== 'course') {
                            continue 2;
                        }
                        $moreitem = new navigation_selector_action_item(get_string('outcomes', 'grades'),
                            $plugin->link, $this->activeurl == $plugin->link);
                        $moregroup->add_action_item($moreitem);
                        break;
                    case 'letter':
                        // We only need the link to the 'view grade letters' page, otherwise skip and continue to the
                        // next plugin.
                        if ($key !== 'view') {
                            continue 2;
                        }
                        $moreitem = new navigation_selector_action_item(get_string('gradeletters', 'grades'),
                            $plugin->link, $this->activeurl == $plugin->link);
                        $moregroup->add_action_item($moreitem);
                        break;
                    case 'import':
                        $link = new moodle_url('/grade/import/index.php', ['id' => $courseid]);
                        $moreitem = new navigation_selector_action_item(get_string('import', 'grades'),
                            $link, $this->activeurl == $link);
                        // If the link to the grade import options is already added to the group, skip and continue to
                        // the next plugin.
                        if (in_array($moreitem, $moregroup->get_action_items())) {
                            continue 2;
                        }
                        $moregroup->add_action_item($moreitem);
                        break;
                    case 'export':
                        $link = new moodle_url('/grade/export/index.php', ['id' => $courseid]);
                        $moreitem = new navigation_selector_action_item(get_string('export', 'grades'),
                            $link, $this->activeurl == $link);
                        // If the link to the grade export options is already added to the group, skip and continue to
                        // the next plugin.
                        if (in_array($moreitem, $moregroup->get_action_items())) {
                            continue 2;
                        }
                        $moregroup->add_action_item($moreitem);
                        break;
                }
            }
        }

        if (!empty($viewgroup->get_action_items())) {
            $tertiarynavselector->add_item($viewgroup);
        }

        if (!empty($setupgroup->get_action_items())) {
            $tertiarynavselector->add_item($setupgroup);
        }

        if (!empty($moregroup->get_action_items())) {
            $tertiarynavselector->add_item($moregroup);
        }

        return $tertiarynavselector;
    }
}
