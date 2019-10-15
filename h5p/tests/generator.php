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
 * Base class for unit tests for mod_assign.
 *
 * @package    core_h5p
 * @category   phpunit
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Generator helper trait.
 *
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait core_h5p_test_generator {

    /**
     * Populate H5P database tables with relevant data to simulate the process of adding H5P content.
     *
     * @param bool $createlibraryfiles Whether should create library relater files on the filesystem
     * @return object An object representing the added H5P records
     */
    protected function generate_h5p_data(bool $createlibraryfiles = false) : stdClass {
        // Create libraries.
        $mainlib = $this->create_library_record('MainLibrary', 'Main Lib', 1, 0);
        $lib1 = $this->create_library_record('Library1', 'Lib1', 2, 0);
        $lib2 = $this->create_library_record('Library2', 'Lib2', 2, 1);
        $lib3 = $this->create_library_record('Library3', 'Lib3', 3, 2);
        $lib4 = $this->create_library_record('Library4', 'Lib4', 1, 1);
        $lib5 = $this->create_library_record('Library5', 'Lib5', 1, 3);

        if ($createlibraryfiles) {
            // Get a temp path.
            $filestorage = new \core_h5p\file_storage();
            $temppath = $filestorage->getTmpPath();
            // Create library files for MainLibrary.
            $basedirectorymain = $temppath . DIRECTORY_SEPARATOR . $mainlib->machinename . '-' .
                $mainlib->majorversion . '.' . $mainlib->minorversion;
            $this->create_library_files($basedirectorymain, $mainlib->id, $mainlib->machinename,
                $mainlib->majorversion, $mainlib->minorversion);
            // Create library files for Library1.
            $basedirectory1 = $temppath . DIRECTORY_SEPARATOR . $lib1->machinename . '-' .
                $lib1->majorversion . '.' . $lib1->minorversion;
            $this->create_library_files($basedirectory1, $lib1->id, $lib1->machinename,
                $lib1->majorversion, $lib1->minorversion);
            // Create library files for Library2.
            $basedirectory2 = $temppath . DIRECTORY_SEPARATOR . $lib2->machinename . '-' .
                $lib2->majorversion . '.' . $lib2->minorversion;
            $this->create_library_files($basedirectory2, $lib2->id, $lib2->machinename,
                $lib2->majorversion, $lib2->minorversion);
            // Create library files for Library3.
            $basedirectory3 = $temppath . DIRECTORY_SEPARATOR . $lib3->machinename . '-' .
                $lib3->majorversion . '.' . $lib3->minorversion;
            $this->create_library_files($basedirectory3, $lib3->id, $lib3->machinename,
                $lib3->majorversion, $lib3->minorversion);
            // Create library files for Library4.
            $basedirectory4 = $temppath . DIRECTORY_SEPARATOR . $lib4->machinename . '-' .
                $lib4->majorversion . '.' . $lib4->minorversion;
            $this->create_library_files($basedirectory4, $lib4->id, $lib4->machinename,
                $lib4->majorversion, $lib4->minorversion);
            // Create library files for Library5.
            $basedirectory5 = $temppath . DIRECTORY_SEPARATOR . $lib5->machinename . '-' .
                $lib5->majorversion . '.' . $lib5->minorversion;
            $this->create_library_files($basedirectory5, $lib5->id, $lib5->machinename,
                $lib5->majorversion, $lib5->minorversion);
        }

        // Create h5p content.
        $h5p = $this->create_h5p_record($mainlib->id);
        // Create h5p content library dependencies.
        $this->create_contents_libraries_record($h5p, $mainlib->id);
        $this->create_contents_libraries_record($h5p, $lib1->id);
        $this->create_contents_libraries_record($h5p, $lib2->id);
        $this->create_contents_libraries_record($h5p, $lib3->id);
        $this->create_contents_libraries_record($h5p, $lib4->id);
        // Create library dependencies for $mainlib.
        $this->create_library_dependency_record($mainlib->id, $lib1->id);
        $this->create_library_dependency_record($mainlib->id, $lib2->id);
        $this->create_library_dependency_record($mainlib->id, $lib3->id);
        // Create library dependencies for $lib1.
        $this->create_library_dependency_record($lib1->id, $lib2->id);
        $this->create_library_dependency_record($lib1->id, $lib3->id);
        $this->create_library_dependency_record($lib1->id, $lib4->id);
        // Create library dependencies for $lib3.
        $this->create_library_dependency_record($lib3->id, $lib5->id);

        return (object) [
            'h5pcontent' => (object) array(
                'h5pid' => $h5p,
                'contentdependencies' => array($mainlib, $lib1, $lib2, $lib3, $lib4)
            ),
            'mainlib' => (object) array(
                'data' => $mainlib,
                'dependencies' => array($lib1, $lib2, $lib3)
            ),
            'lib1' => (object) array(
                'data' => $lib1,
                'dependencies' => array($lib2, $lib3, $lib4)
            ),
            'lib2' => (object) array(
                'data' => $lib2,
                'dependencies' => array()
            ),
            'lib3' => (object) array(
                'data' => $lib3,
                'dependencies' => array($lib5)
            ),
            'lib4' => (object) array(
                'data' => $lib4,
                'dependencies' => array()
            ),
            'lib5' => (object) array(
                'data' => $lib5,
                'dependencies' => array()
            ),
            'libtemppath' => $temppath ?? ''
        ];
    }

    /**
     * Create a record in the h5p_libraries database table.
     *
     * @param string $machinename The library machine name
     * @param string $title The library's name
     * @param int $majorversion The library's major version
     * @param int $minorversion The library's minor version
     * @param int $patchversion The library's patch version
     * @param string $semantics Json describing the content structure for the library
     * @param string $addto The plugin configuration data
     * @return stdClass An object representing the added library record
     */
    protected function create_library_record(string $machinename, string $title, int $majorversion = 1,
            int $minorversion = 0, int $patchversion = 1, string $semantics = '', string $addto = null) : stdClass {
        global $DB;

        $content = array(
            'machinename' => $machinename,
            'title' => $title,
            'majorversion' => $majorversion,
            'minorversion' => $minorversion,
            'patchversion' => $patchversion,
            'runnable' => 1,
            'fullscreen' => 1,
            'embedtypes' => 'iframe',
            'preloadedjs' => 'js/example.js',
            'preloadedcss' => 'css/example.css',
            'droplibrarycss' => '',
            'semantics' => $semantics,
            'addto' => $addto
        );

        $libraryid = $DB->insert_record('h5p_libraries', $content);

        return $DB->get_record('h5p_libraries', ['id' => $libraryid]);
    }

    /**
     * Create the necessary files and return an array structure for a library.
     *
     * @param  string $uploaddirectory base directory for the library
     * @param  int    $libraryid       The library ID
     * @param  string $machinename     Name for this library
     * @param  int    $majorversion    Major version (any number will do)
     * @param  int    $minorversion    Minor version (any number will do)
     */
    protected function create_library_files(string $uploaddirectory, int $libraryid, string $machinename,
            int $majorversion, int $minorversion) {
        global $CFG;

        // Create library directories.
        mkdir($uploaddirectory, $CFG->directorypermissions, true);
        mkdir($uploaddirectory . DIRECTORY_SEPARATOR . 'scripts', $CFG->directorypermissions, true);
        mkdir($uploaddirectory . DIRECTORY_SEPARATOR . 'styles', $CFG->directorypermissions, true);

        // Create library.json file.
        $jsonfile = $uploaddirectory . DIRECTORY_SEPARATOR . 'library.json';
        $handle = fopen($jsonfile, 'w+');
        fwrite($handle, 'test data');
        fclose($handle);
        // Create library js file.
        $jsfile = $uploaddirectory . DIRECTORY_SEPARATOR . 'scripts/testlib.min.js';
        $handle = fopen($jsfile, 'w+');
        fwrite($handle, 'test data');
        fclose($handle);
        // Create library css file.
        $cssfile = $uploaddirectory . DIRECTORY_SEPARATOR . 'styles/testlib.min.css';
        $handle = fopen($cssfile, 'w+');
        fwrite($handle, 'test data');
        fclose($handle);

        $lib = [
            'title' => 'Test lib',
            'description' => 'Test library description',
            'majorVersion' => $majorversion,
            'minorVersion' => $minorversion,
            'patchVersion' => 2,
            'machineName' => $machinename,
            'embedTypes' => 'iframe',
            'preloadedJs' => [
                [
                    'path' => 'scripts' . DIRECTORY_SEPARATOR . 'testlib.min.js'
                ]
            ],
            'preloadedCss' => [
                [
                    'path' => 'styles' . DIRECTORY_SEPARATOR . 'testlib.min.css'
                ]
            ],
            'uploadDirectory' => $uploaddirectory,
            'libraryId' => $libraryid
        ];

        $filestorage = new \core_h5p\file_storage();
        $filestorage->saveLibrary($lib);
    }

    /**
     * Create a record in the h5p database table.
     *
     * @param int $mainlibid The ID of the content's main library
     * @param string $jsoncontent The content in json format
     * @param string $filtered The filtered content parameters
     * @return int The ID of the added record
     */
    protected function create_h5p_record(int $mainlibid, string $jsoncontent = null, string $filtered = null) : int {
        global $DB;

        if (!$jsoncontent) {
            $jsoncontent = json_encode(
                array(
                    'text' => '<p>Dummy text<\/p>\n',
                    'questions' => '<p>Test question<\/p>\n'
                )
            );
        }

        if (!$filtered) {
            $filtered = json_encode(
                array(
                    'text' => 'Dummy text',
                    'questions' => 'Test question'
                )
            );
        }

        return $DB->insert_record(
            'h5p',
            array(
                'jsoncontent' => $jsoncontent,
                'displayoptions' => 8,
                'mainlibraryid' => $mainlibid,
                'timecreated' => time(),
                'timemodified' => time(),
                'filtered' => $filtered,
                'pathnamehash' => sha1('pathname'),
                'contenthash' => sha1('content')
            )
        );
    }

    /**
     * Create a record in the h5p_contents_libraries database table.
     *
     * @param string $h5pid The ID of the H5P content
     * @param int $libid The ID of the library
     * @param string $dependencytype The dependency type
     * @return int The ID of the added record
     */
    protected function create_contents_libraries_record(string $h5pid, int $libid,
            string $dependencytype = 'preloaded') : int {
        global $DB;

        return $DB->insert_record(
            'h5p_contents_libraries',
            array(
                'h5pid' => $h5pid,
                'libraryid' => $libid,
                'dependencytype' => $dependencytype,
                'dropcss' => 0,
                'weight' => 1
            )
        );
    }

    /**
     * Create a record in the h5p_library_dependencies database table.
     *
     * @param int $libid The ID of the library
     * @param int $requiredlibid The ID of the required library
     * @param string $dependencytype The dependency type
     * @return int The ID of the added record
     */
    protected function create_library_dependency_record(int $libid, int $requiredlibid,
            string $dependencytype = 'preloaded') : int {
        global $DB;

        return $DB->insert_record(
            'h5p_library_dependencies',
            array(
                'libraryid' => $libid,
                'requiredlibraryid' => $requiredlibid,
                'dependencytype' => $dependencytype
            )
        );
    }
}
