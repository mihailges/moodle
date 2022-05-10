<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants.
 *
 * @package     mod_example
 * @copyright   2022 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function example_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_example into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_example_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function example_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('example', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_example in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_example_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function example_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('example', $moduleinstance);
}

/**
 * Removes an instance of the mod_example from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function example_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('example', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('example', array('id' => $id));

    return true;
}

/**
 * Extends the settings navigation with the mod_example settings.
 *
 * This function is called when the context for the page is a mod_example module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@see settings_navigation}
 * @param navigation_node $examplenode {@see navigation_node}
 */
function example_extend_settings_navigation($settingsnav, $examplenode = null) {
    $coursemoduleid = $settingsnav->get_page()->cm->id;

    // Add some navigation nodes to the settings navigation.
//    $page1 = $examplenode->add('Page 1', new moodle_url('/mod/example/page1.php', ['id' => $coursemoduleid]),
//        navigation_node::TYPE_SETTING, '', 'page1');
//    $page2 = $examplenode->add('Page 2', new moodle_url('/mod/example/page2.php', ['id' => $coursemoduleid]),
//        navigation_node::TYPE_SETTING, '', 'page2');
//    $page3 = $examplenode->add('Page 3', new moodle_url('/mod/example/page3.php', ['id' => $coursemoduleid]),
//        navigation_node::TYPE_SETTING, '', 'page3');

    // Add some child navigation nodes to the Page 1 node.
//    $page1->add('Subpage 1', new moodle_url('/mod/example/subpage1.php'),
//        navigation_node::TYPE_SETTING, '', 'subpage1');
//    $page1->add('Subpage 2', new moodle_url('/mod/example/subpage2.php'),
//        navigation_node::TYPE_SETTING, '', 'subpage2');

    // Force the 'Page 3' node into the 'More' menu.
//    $page3->set_force_into_more_menu(true);

    // Do not show 'Page 2' node in the secondary navigation.
//    $page2->set_show_in_secondary_navigation(false);
}
