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
 * The modchooser renderable.
 *
 * @package    core_course
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_course\output;
defined('MOODLE_INTERNAL') || die();

use core\output\chooser;
use core\output\chooser_section;
use context_course;
use lang_string;
use moodle_url;
use pix_icon;
use renderer_base;
use stdClass;

/**
 * The modchooser renderable class.
 *
 * @package    core_course
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class modchooser extends chooser {

    /** @var stdClass The course. */
    public $course;

    public $resources = [];

    public $activities = [];

    public $userstarredmodules = [];

    /**
     * Constructor.
     *
     * @param stdClass $course The course.
     * @param stdClass[] $modules The modules.
     */
    public function __construct(stdClass $course, array $modules) {
        $this->course = $course;

        $sections = [];
        $context = context_course::instance($course->id);
        $userstarredmodules = explode(",", get_user_preferences('userstarredmodules'));

         // Activities.
        $activities = array_filter($modules, function($mod) {
            return ($mod->archetype !== MOD_ARCHETYPE_RESOURCE && $mod->archetype !== MOD_ARCHETYPE_SYSTEM);
        });
        if (count($activities)) {
            $sections[] = $this->activities[] = new chooser_section('activities', new lang_string('activities'),
                array_map(function($module) use ($context, $userstarredmodules) {
                    $modchooseritem = new modchooser_item($module, $context);
                    if (in_array($module->name, $userstarredmodules)) {
                        $this->userstarredmodules[] = $modchooseritem;
                    }
                    return $modchooseritem;
                }, $activities)
            );
        }

        $resources = array_filter($modules, function($mod) {
            return ($mod->archetype === MOD_ARCHETYPE_RESOURCE);
        });
        if (count($resources)) {
            $sections[] = $this->resources[] = new chooser_section('resources', new lang_string('resources'),
                array_map(function($module) use ($context, $userstarredmodules) {
                    $modchooseritem = new modchooser_item($module, $context);
                    if (in_array($module->name, $userstarredmodules)) {
                        $this->userstarredmodules[] = $modchooseritem;
                    }
                    return $modchooseritem;
                }, $resources)
            );
        }

        $actionurl = new moodle_url('/course/jumpto.php');
        $title = new lang_string('addresourceoractivity');
        parent::__construct($actionurl, $title, $sections, 'jumplink');

        $this->set_instructions(new lang_string('selectmoduletoviewhelp'));
        $this->add_param('course', $course->id);
    }

    /**
     * Export for template.
     *
     * @param renderer_base  The renderer.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $userstarredmodules = get_user_preferences('userstarredmodules');

        $PAGE->requires->js_call_amd('core_course/modchooser', 'init', [$userstarredmodules]);

        $data = parent::export_for_template($output);
        $data->courseid = $this->course->id;
        $data->activities = [];
        $data->resources = [];
        $userstarredmodules = get_user_preferences('userstarredmodules');
        $data->userstarredmodules = $userstarredmodules;
        foreach ($data->sections as $section) {
            if ($section->id == 'activities') {
                $data->activities[] = $section;
                //$section->starred = true;
            } else if ($section->id == 'resources') {
                $data->resources[] = $section;
            }
            $section->items = array_map(function($item) use ($userstarredmodules) {
                if (in_array($item->id, explode(',', $userstarredmodules))) {
                    $item->starred = true;
                } else {
                    $item->starred = false;
                }
                return $item;
            }, $section->items);
        }

        return $data;
    }

}
