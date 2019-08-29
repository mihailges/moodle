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
 * References files that should be automatically loaded
 *
 * @package    core_h5p
 * @copyright  2016 Joubel AS <contact@joubel.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * A simple autoloader which makes it easy to load classes when you need them.
 *
 * @param string $class name
 */
function h5p_autoloader($class) {
    global $CFG;
    static $classmap;
    if (!isset($classmap)) {
        $classmap = array(
        // Core.
        'H5PCore' => '/lib/h5p/h5p.classes.php',
        'H5PFrameworkInterface' => '/lib/h5p/h5p.classes.php',
        'H5PContentValidator' => 'lib/h5p/h5p.classes.php',
        'H5PValidator' => '/lib/h5p/h5p.classes.php',
        'H5PStorage' => '/lib/h5p/h5p.classes.php',
        'H5PExport' => '/lib/h5p/h5p.classes.php',
        'H5PDevelopment' => '/lib/h5p/h5p-development.class.php',
        'H5PFileStorage' => '/lib/h5p/h5p-file-storage.interface.php',
        'H5PDefaultStorage' => '/lib/h5p/h5p-default-storage.class.php',
        'H5PEventBase' => '/lib/h5p/h5p-event-base.class.php',
        'H5PMetadata' => '/lib/h5p/h5p-metadata.class.php',

//        // Editor.
//        'H5peditor' => 'editor/h5peditor.class.php',
//        'H5PEditorAjax' => 'editor/h5peditor-ajax.class.php',
//        'H5PEditorAjaxInterface' => 'editor/h5peditor-ajax.interface.php',
//        'H5peditorFile' => 'editor/h5peditor-file.class.php',
//        'H5peditorStorage' => 'editor/h5peditor-storage.interface.php',

//        // Reporting.
//        'H5PReport' => 'reporting/h5p-report.class.php',
//        'H5PReportXAPIData' => 'reporting/h5p-report-xapi-data.class.php',
//        'ChoiceProcessor' => 'reporting/type-processors/choice-processor.class.php',
//        'CompoundProcessor' => 'reporting/type-processors/compound-processor.class.php',
//        'FillInProcessor' => 'reporting/type-processors/fill-in-processor.class.php',
//        'LongChoiceProcessor' => 'reporting/type-processors/long-choice-processor.class.php',
//        'MatchingProcessor' => 'reporting/type-processors/matching-processor.class.php',
//        'TrueFalseProcessor' => 'reporting/type-processors/true-false-processor.class.php',
//        'IVOpenEndedQuestionProcessor' => 'reporting/type-processors/iv-open-ended-question-processor.class.php',
//        'TypeProcessor' => 'reporting/type-processors/type-processor.class.php',
//        'DocumentationToolProcessor' => 'reporting/type-processors/compound/documentation-tool-processor.class.php',
//        'GoalsPageProcessor' => 'reporting/type-processors/compound/goals-page-processor.class.php',
//        'GoalsAssessmentPageProcessor' => 'reporting/type-processors/compound/goals-assessment-page-processor.class.php',
//        'StandardPageProcessor' => 'reporting/type-processors/compound/standard-page-processor.class.php',

        // Plugin specific classes are loaded by Moodle.
        );
    }

    if (isset($classmap[$class])) {
        require_once($CFG->dirroot . $classmap[$class]);
    }
}
spl_autoload_register('h5p_autoloader');
