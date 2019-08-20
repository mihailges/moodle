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
 * Library of interface functions and constants for module hvp.
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the hvp specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    core_hvp
 * @copyright  2016 Joubel AS <contact@joubel.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

 /**
 * Serves the files from the hvp file areas
 *
 * @package core_hvp
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the newmodule's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 *
 * @return true|false Success
 */
function core_h5p_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {

    switch ($filearea) {
        default:
            return false; // Invalid file area.

        case 'libraries':
            $itemid = 0;
            break;
        case 'cachedassets':
            if ($context->contextlevel != CONTEXT_SYSTEM) {
                return false; // Invalid context.
            }

            // Check permissions.
            if (!has_capability('mod/hvp:getcachedassets', $context)) {
                return false;
            }

            $itemid = 0;
            break;

        case 'content':
            if ($context->contextlevel != CONTEXT_MODULE) {
                return false; // Invalid context.
            }

            // Check permissions.
            if (!has_capability('mod/hvp:view', $context)) {
                return false;
            }

            $itemid = array_shift($args);
            break;

        case 'exports':
            if ($context->contextlevel != CONTEXT_MODULE) {
                return false; // Invalid context.
            }

            // Check permission.
            if (!has_capability('mod/hvp:view', $context)) {
                return false;
            }
            // Note that the getexport permission is checked after loading the content.

            // Get core.
            $h5pinterface = \core_hvp\framework::instance('interface');
            $h5pcore = \core_hvp\framework::instance('core');

            $matches = array();

            // Get content id from filename.
            if (!preg_match('/(\d*).h5p$/', $args[0], $matches)) {
                // Did not find any content ID.
                return false;
            }

            $contentid = $matches[1];
            $content = $h5pinterface->loadContent($contentid);
            $displayoptions = $h5pcore->getDisplayOptionsForView($content['disable'], $context->instanceid);

            // Check permissions.
            if (!$displayoptions['export']) {
                return false;
            }

            $itemid = 0;

            // Change context to course for retrieving file.
            $cm = get_coursemodule_from_id('hvp', $context->instanceid);
            $context = context_course::instance($cm->course);
            break;

        case 'editor':
            $cap = ($context->contextlevel === CONTEXT_COURSE ? 'addinstance' : 'manage');

            // Check permissions.
            if (!has_capability("mod/hvp:$cap", $context)) {
                return false;
            }

            $itemid = 0;
            break;
    }

    $filename = array_pop($args);
    $filepath = (!$args ? '/' : '/' .implode('/', $args) . '/');

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'core_h5p', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // No such file.
    }

    send_stored_file($file, 86400, 0, $forcedownload, $options);

    return true;
}
