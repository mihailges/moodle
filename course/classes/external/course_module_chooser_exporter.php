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
 * Author exporter.
 *
 * @package    mod_forum
 * @copyright  2019 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_course\external;

defined('MOODLE_INTERNAL') || die();

use core\external\exporter;
use message_email\output\email_digest;
use renderer_base;

/**
 * Course module chooser exporter.
 *
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_chooser_exporter extends exporter {

    /** @var string $author The title of the module chooser */
    private $title;
    /** @var array $modules Array containing the available modules */
    private $modules;
    /** @var array $course The course object */
    private $course;

    /**
     * Constructor.
     *
     * @param author_entity $author The author entity to export
     * @param int|null $authorcontextid The context id for the author entity to export (null if the user doesn't have one)
     * @param stdClass[] $authorgroups The list of groups that the author belongs to
     * @param bool $canview Can the requesting user view this author or should it be anonymised?
     * @param array $related The related data for the export.
     */
    public function __construct(
        object $course,
        string $title,
        ?array $modules

    ) {
        $this->title = $title;
        $this->modules = ['dsadas'];
        $this->course = $course;
    }

    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'title' => [
                'type' => PARAM_TEXT,
                'optional' => true,
                'default' => null,
                'null' => NULL_ALLOWED
            ],
            'modules' => [
                'multiple' => true,
                'optional' => true,
                'type' => [
                    'label' => ['type' => PARAM_TEXT],
                    'description' => ['type' => PARAM_TEXT],
                    'urls' => [
                        'add_module' => [
                            'type' => PARAM_URL,
                            'optional' => true,
                            'default' => null,
                            'null' => NULL_ALLOWED
                        ],
                        'module_icon' => [
                            'type' => PARAM_URL,
                            'optional' => true,
                            'default' => null,
                            'null' => NULL_ALLOWED
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(renderer_base $output) {
        $title = $this->title;

        $context = \context_course::instance($this->course->id);

        $options = new \stdClass();
        $options->trusted = false;
        $options->noclean = false;
        $options->smiley = false;
        $options->filter = false;
        $options->para = true;
        $options->newlines = false;
        $options->overflowdiv = false;

        $modulesData = [];
        foreach ($this->modules as $module) {
            //print_r($module); die();
            $customiconurl = null;

            // The property 'name' may contain more than just the module, in which case we need to extract the true module name.
            $modulename = $module->name;
            if ($colon = strpos($modulename, ':')) {
                $modulename = substr($modulename, 0, $colon);
            }
            if (preg_match('/src="([^"]*)"/i', $module->icon, $matches)) {
                // Use the custom icon.
                $customiconurl = str_replace('&amp;', '&', $matches[1]);
            }

            if (isset($module->help)) {
                $description = '';
                if (!empty($module->help)) {
                    list($description) = external_format_text((string) $module->help, FORMAT_MARKDOWN,
                        $context->id, null, null, null, $options);
                }
            } else {
                $description = get_string('nohelpforactivityorresource', 'moodle');
            }

            $icon = new \pix_icon('icon', '', $modulename, ['class' => 'icon']);

            $modulesData[] = [
                'label' => $module->title->out(),
                'description' => $description,
                'urls' => [
                    'add_module' => $module->link->out(false),
                    'module_icon' => $icon->export_for_template($output)
                ]
            ];
        }


       // print_r($modulesData); die();

//        $modulesData = array_map(function($module) use ($context, $options, $output) {
//            $customiconurl = null;
//            print_r($module); die();
//
//            // The property 'name' may contain more than just the module, in which case we need to extract the true module name.
//            $modulename = $module->name;
//            if ($colon = strpos($modulename, ':')) {
//                $modulename = substr($modulename, 0, $colon);
//            }
//            if (preg_match('/src="([^"]*)"/i', $module->icon, $matches)) {
//                // Use the custom icon.
//                $customiconurl = str_replace('&amp;', '&', $matches[1]);
//            }
//
//            if (isset($module->help)) {
//                $description = '';
//                if (!empty($module->help)) {
//                    list($description) = external_format_text((string) $module->help, FORMAT_MARKDOWN,
//                        $context->id, null, null, null, $options);
//                }
//            } else {
//                $description = get_string('nohelpforactivityorresource', 'moodle');
//            }
//
//            $icon = new \pix_icon('icon', '', $modulename, ['class' => 'icon']);
//
//            return [
//                'label' => $module->title,
//                'description' => $description,
//                'urls' => [
//                    'add_module' => $module->link->out(false),
//                    'module_icon' => $icon->export_for_template($output)
//                ]
//            ];
//        }, $this->modules);

        return [
            'title' => $title,
            'modules' => $modulesData
        ];

    }

//    /**
//     * Returns a list of objects that are related.
//     *
//     * @return array
//     */
//    protected static function define_related() {
//        return [
//            'urlfactory' => 'mod_forum\local\factories\url',
//            'context' => 'context'
//        ];
//    }
}
