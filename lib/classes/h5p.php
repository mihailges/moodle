<?php
//// This file is part of Moodle - http://moodle.org/
////
//// Moodle is free software: you can redistribute it and/or modify
//// it under the terms of the GNU General Public License as published by
//// the Free Software Foundation, either version 3 of the License, or
//// (at your option) any later version.
////
//// Moodle is distributed in the hope that it will be useful,
//// but WITHOUT ANY WARRANTY; without even the implied warranty of
//// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//// GNU General Public License for more details.
////
//// You should have received a copy of the GNU General Public License
//// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
///**
// * H5P wrapper class.
// *
// * @package    core
// * @copyright  2019 Moodle
// * @author     Bas Brands
// * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
// */
//
//
///**
// * A simple autoloader which makes it easy to load classes when you need them.
// *
// * @param string $class name
// */
//function h5p_autoloader($class) {
//    global $CFG;
//    static $classmap;
//    if (!isset($classmap)) {
//        $classmap = array(
//        // Core.
//        'H5PCore' => 'h5p.classes.php',
//        'H5PFrameworkInterface' => 'h5p.classes.php',
//        'H5PStorage' => 'h5p.classes.php',
//        'H5PDevelopment' => 'h5p-development.class.php',
//        'H5PFileStorage' => 'h5p-file-storage.interface.php',
//        'H5PDefaultStorage' => 'h5p-default-storage.class.php',
//        'H5PMetadata' => 'h5p-metadata.class.php',
//        );
//    }
//
//    if (isset($classmap[$class])) {
//        require_once($CFG->libdir . '/h5p/' . $classmap[$class]);
//    }
//}
//
//spl_autoload_register('h5p_autoloader');
//
//defined('MOODLE_INTERNAL') || die();
//
//class core_h5p {
//
//    private $core;
//    private $content;
//    private $jsrequires;
//    private $cssrequires;
//    private $testidnumber;
//
//    protected $settings;
//
//    public function __construct($idnumber) {
//        global $CFG;
//        $this->testidnumber = $idnumber;
//        $this->core        = core_h5p_framework::instance();
//        $this->content     = $this->core->loadContent($this->testidnumber);
//        $this->jsrequires  = [];
//        $this->cssrequires = [];
//        $this->settings = $this->get_core_assets(\context_system::instance());
//        $context        = \context_system::instance();
//        $this->settings['contents'][ 'cid-' . $this->testidnumber ]   = array(
//            'library'         => \H5PCore::libraryToString($this->content['library']),
//            'jsonContent'     => $this->getfilteredparameters(),
//            'fullScreen'      => $this->content['library']['fullscreen'],
//            'exportUrl'       => "",
//            'embedCode'       => "No Embed Code",
//            'resizeCode'      => $this->getresizecode($this->testidnumber),
//            'title'           => $this->content['slug'],
//            'displayOptions'  => '',
//            'url'             => "{$CFG->wwwroot}/lib/classes/output/h5p_embed.php?id={$this->testidnumber}",
//            'contentUrl'      => "{$CFG->wwwroot}/pluginfile.php/{$context->id}/core_h5p/content/{$this->testidnumber}",
//            'metadata'        => '',
//            'contentUserData' => array()
//        );
//
//        $this->files = $this->getdependencyfiles();
//
//        $this->generateassets();
//    }
//
//    public function getcontent() {
//        return $this->content;
//    }
//
//    public function get_cache_buster() {
//        return '?ver=' . 1;
//    }
//
//    public function get_core_assets($context) {
//        global $CFG, $PAGE;
//        // Get core settings.
//        $settings = $this->get_core_settings($context);
//        $settings['core'] = [
//          'styles' => [],
//          'scripts' => []
//        ];
//        $settings['loadedJs'] = [];
//        $settings['loadedCss'] = [];
//
//        // Make sure files are reloaded for each plugin update.
//        $cachebuster = $this->get_cache_buster();
//
//        // Use relative URL to support both http and https.
//        $liburl = $CFG->wwwroot . '/lib/h5p/';
//        $relpath = '/' . preg_replace('/^[^:]+:\/\/[^\/]+\//', '', $liburl);
//
//        // Add core stylesheets.
//        foreach (\H5PCore::$styles as $style) {
//            $settings['core']['styles'][] = $relpath . $style . $cachebuster;
//            //$this->cssrequires[] = new moodle_url($liburl . $style . $cachebuster);
//        }
//        // Add core JavaScript.
//        foreach (\H5PCore::$scripts as $script) {
//            $settings['core']['scripts'][] = $relpath . $script . $cachebuster;
//            $this->jsrequires[] = new moodle_url($liburl . $script . $cachebuster);
//        }
//
//        return $settings;
//    }
//
//    public function get_core_settings($context) {
//        global $USER, $CFG;
//
//        $basepath = $CFG->wwwroot . '/';
//
//        // Check permissions and generate ajax paths.
//        $ajaxpaths = [];
//        $ajaxpaths['setFinished'] = '';
//        $ajaxpaths['xAPIResult'] = '';
//        $ajaxpaths['contentUserData'] = '';
//
//        $settings = array(
//            'baseUrl' => $basepath,
//            'url' => "{",
//            'urlLibraries' => "",
//            'postUserStatistics' => true,
//            'ajax' => $ajaxpaths,
//            'saveFreq' => false,
//            'siteUrl' => $CFG->wwwroot,
//            'l10n' => array('H5P' => $this->core->getLocalization()),
//            'user' => [],
//            'hubIsEnabled' => false,
//            'reportingIsEnabled' => true,
//            'crossorigin' => null,
//            'libraryConfig' => '',
//            'pluginCacheBuster' => $this->get_cache_buster(),
//            'libraryUrl' => ''
//        );
//
//        return $settings;
//    }
//
//    public function getfilteredparameters() {
//        global $PAGE;
//
//        $safeparameters = $this->core->filterParameters($this->content);
//
//        $decodedparams  = json_decode($safeparameters);
//        $safeparameters = json_encode($decodedparams);
//
//        return $safeparameters;
//    }
//
//    /**
//     * Resizing script for settings
//     *
//     * @param $embedenabled
//     *
//     * @return string
//     */
//    private function getresizecode($embedenabled) {
//        global $CFG;
//
//        if ( ! $embedenabled) {
//            return '';
//        }
//
//        $resizeurl = new \moodle_url($CFG->wwwroot . '/lib/h5p/js/h5p-resizer.js');
//
//        return "<script src=\"{$resizeurl->out()}\" charset=\"UTF-8\"></script>";
//    }
//
//    /**
//     * Adds js assets to current page
//     */
//    public function addassetstopage() {
//        global $PAGE, $CFG;
//
//        foreach ($this->jsrequires as $script) {
//            $PAGE->requires->js($script, true);
//        }
//
//        foreach ($this->cssrequires as $css) {
//            $PAGE->requires->css($css);
//        }
//
//        // Print JavaScript settings to page.
//        $PAGE->requires->data_for_js('H5PIntegration', $this->settings, true);
//    }
//
//    /**
//     * Finds library dependencies of view
//     *
//     * @return array Files that the view has dependencies to
//     */
//    private function getdependencyfiles() {
//        global $PAGE;
//
//        $preloadeddeps = $this->core->loadContentDependencies($this->testidnumber);
//        $files         = $this->core->getDependenciesFiles($preloadeddeps);
//
//        return $files;
//    }
//
//    public function generateassets() {
//        global $CFG;
//        $context = \context_system::instance();
//        $h5ppath = "/pluginfile.php/{$context->id}/core_h5p";
//
//        // Schedule JavaScripts for loading through Moodle.
//        foreach ($this->files['scripts'] as $script) {
//            $url = $script->path . $script->version;
//
//            // Add URL prefix if not external.
//            $isexternal = strpos($script->path, '://');
//            if ($isexternal === false) {
//                $url = $h5ppath . $url;
//            }
//            $this->settings['loadedJs'][] = $url;
//            $this->jsrequires[] = new \moodle_url($isexternal ? $url : $CFG->wwwroot . $url);
//        }
//
//        // Schedule stylesheets for loading through Moodle.
//        foreach ($this->files['styles'] as $style) {
//            $url = $style->path . $style->version;
//
//            // Add URL prefix if not external.
//            $isexternal = strpos($style->path, '://');
//            if ($isexternal === false) {
//                $url = $h5ppath . $url;
//            }
//            $this->settings['loadedCss'][] = $url;
//            $this->cssrequires[] = new \moodle_url($isexternal ? $url : $CFG->wwwroot . $url);
//        }
//    }
//
//    public function outputview() {
//        return "<div class=\"h5p-content\" data-content-id=\"{$this->testidnumber}\"></div>";
//    }
//}
//
//class core_h5p_framework implements \H5PFrameworkInterface {
//    /**
//     * Get type of h5p instance
//     *
//     * @param string $type Type of h5p instance to get
//     * @return \H5PContentValidator|\H5PCore|\H5PStorage|\H5PValidator|\core_h5p\framework|\H5peditor
//     */
//    public static function instance($type = null) {
//        global $CFG;
//        static $interface, $core, $editor, $editorinterface, $editorajaxinterface;
//
//        if (!isset($interface)) {
//            $interface = new core_h5p_framework();
//            $fs = new core_h5p_file_storage();
//            $core = new \H5PCore($interface, $fs, '', '', '');
//        }
//        switch ($type) {
//            case 'interface':
//                return $interface;
//            case 'validator':
//                return new \H5PValidator($interface, $core);
//            case 'storage':
//                return new \H5PStorage($interface, $core);
//            default:
//                return $core;
//        }
//    }
//
//    /**
//    * Load content.
//    *
//    * @param int $id
//    *   Content identifier
//    * @return array
//    *   Associative array containing:
//    *   - contentId: Identifier for the content
//    *   - params: json content as string
//    *   - embedType: csv of embed types
//    *   - title: The contents title
//    *   - language: Language code for the content
//    *   - libraryId: Id for the main library
//    *   - libraryName: The library machine name
//    *   - libraryMajorVersion: The library's majorVersion
//    *   - libraryMinorVersion: The library's minorVersion
//    *   - libraryEmbedTypes: CSV of the main library's embed types
//    *   - libraryFullscreen: 1 if fullscreen is supported. 0 otherwise.
//    */
//    public function loadContent($id) {
//        global $DB;
//
//        $data = $DB->get_record_sql("
//          SELECT
//            hc.id,
//            hc.jsoncontent,
//            hc.embedtype,
//            hl.id AS libraryid,
//            hl.machinename,
//            hl.majorversion,
//            hl.minorversion,
//            hl.fullscreen,
//            hl.semantics
//          FROM {h5p} hc
//          JOIN {h5p_libraries} hl ON hl.id = hc.mainlibraryid
//          WHERE hc.id = ?", array($id)
//        );
//
//        // Return null if not found.
//        if ($data === false) {
//            //return null;
//        }
//
//        // Some databases do not support camelCase, so we need to manually
//        // map the values to the camelCase names used by the H5P core.
//        $content = array(
//            'id' => $data->id,
//            'params' => $data->jsoncontent,
//            'title' => 'h5p-title-' . $data->id,
//            'filtered' => '',
//            'slug' => 'h5p-test-' . $data->id,
//            'embedType' => $data->embedtype,
//            'disable' => 'false',
//            'libraryId' => $data->libraryid,
//            'libraryName' => $data->machinename,
//            'libraryMajorVersion' => $data->majorversion,
//            'libraryMinorVersion' => $data->minorversion,
//            'libraryEmbedTypes' => $data->embedtype,
//            'libraryFullscreen' => $data->fullscreen,
//        );
//
//        $content['metadata'] = ''; // Not sure if required.
//
//        return $content;
//    }
//
//    /**
//    * Load dependencies for the given content of the given type.
//    *
//    * @param int $id
//    *   Content identifier
//    * @param int $type
//    *   Dependency types. Allowed values:
//    *   - editor
//    *   - preloaded
//    *   - dynamic
//    * @return array
//    *   List of associative arrays containing:
//    *   - libraryId: The id of the library if it is an existing library.
//    *   - machineName: The library machineName
//    *   - majorVersion: The library's majorVersion
//    *   - minorVersion: The library's minorVersion
//    *   - patchVersion: The library's patchVersion
//    *   - preloadedJs(optional): comma separated string with js file paths
//    *   - preloadedCss(optional): comma separated sting with css file paths
//    *   - dropCss(optional): csv of machine names
//    */
//    public function loadContentDependencies($id, $type = null) {
//
//        global $DB;
//
//        $query = "SELECT hcl.id AS unidepid
//                       , hl.id
//                       , hl.machinename as machine_name
//                       , hl.majorversion as major_version
//                       , hl.minorversion as minor_version
//                       , hl.patchversion as patch_version
//                       , hl.preloaded_css
//                       , hl.preloaded_js
//                       , hcl.dropcss as drop_css
//                       , hcl.dependencytype as dependency_type
//                   FROM {h5p_contents_libraries} hcl
//                   JOIN {h5p_libraries} hl ON hcl.libraryid = hl.id
//                  WHERE hcl.h5pid = ?";
//        $queryargs = array($id);
//
//        if ($type !== null) {
//            $query .= " AND hcl.dependency_type = ?";
//            $queryargs[] = $type;
//        }
//
//        $query .= " ORDER BY hcl.weight";
//        $data = $DB->get_records_sql($query, $queryargs);
//
//        $dependencies = array();
//        foreach ($data as $dependency) {
//            unset($dependency->unidepid);
//            $dependencies[$dependency->machine_name] = \H5PCore::snakeToCamel($dependency);
//        }
//
//        return $dependencies;
//    }
//
//    public static function has_editor_access($error) {
//    }
//
//    public function getPlatformInfo() {
//    }
//
//    public function t($message, $replacements = array()) {
//        static $translationsmap;
//
//        return $message . print_r($replacements, true);
//    }
//
//    public static function get_language() {
//    }
//
//    public function fetchExternalData($url, $data = null, $blocking = true, $stream = null) {
//    }
//
//    public function setLibraryTutorialUrl($libraryname, $url) {
//    }
//
//    /**
//     * Implements setErrorMessage
//     *
//     * @param string $message translated error message
//     * @param string $code
//     */
//    // @codingStandardsIgnoreLine
//    public function setErrorMessage($message, $code = null) {
//        if ($message !== null) {
//            self::messages('error', $message, $code);
//        }
//    }
//
//    /**
//     * Implements setInfoMessage
//     */
//    // @codingStandardsIgnoreLine
//    public function setInfoMessage($message) {
//        if ($message !== null) {
//            self::messages('info', $message);
//        }
//    }
//
//    /**
//     * Store messages until they can be printed to the current user
//     *
//     * @param string $type Type of messages, e.g. 'info' or 'error'
//     * @param string $newmessage Optional
//     * @param string $code
//     * @return array Array of stored messages
//     */
//    public static function messages($type, $newmessage = null, $code = null) {
//        static $m = 'h5p_messages';
//
//        if ($newmessage === null) {
//            // Return and reset messages.
//            $messages = isset($_SESSION[$m][$type]) ? $_SESSION[$m][$type] : array();
//            unset($_SESSION[$m][$type]);
//            if (empty($_SESSION[$m])) {
//                unset($_SESSION[$m]);
//            }
//            return $messages;
//        }
//
//        // We expect to get out an array of strings when getting info
//        // and an array of objects when getting errors for consistency across platforms.
//        // This implementation should be improved for consistency across the data type returned here.
//        if ($type === 'error') {
//            $_SESSION[$m][$type][] = (object)array(
//                'code' => $code,
//                'message' => $newmessage
//            );
//        } else {
//            $_SESSION[$m][$type][] = $newmessage;
//        }
//    }
//
//    /**
//     * Simple print of given messages.
//     *
//     * @param string $type One of error|info
//     * @param array $messages
//     */
//    // @codingStandardsIgnoreLine
//    public static function printMessages($type, $messages) {
//        global $OUTPUT;
//        foreach ($messages as $message) {
//            $out = $type === 'error' ? $message->message : $message;
//            print $OUTPUT->notification($out, ($type === 'error' ? 'notifyproblem' : 'notifymessage'));
//        }
//    }
//
//    /**
//     * Implements getMessages
//     */
//    // @codingStandardsIgnoreLine
//    public function getMessages($type) {
//        return self::messages($type);
//    }
//
//    public function getH5pPath() {
//    }
//
//    public function getLibraryFileUrl($libraryfoldername, $fileName) {
//    }
//
//    public function getUploadedH5pFolderPath($setpath = null) {
//        static $path;
//
//        if ($setpath !== null) {
//            $path = $setpath;
//        }
//
//        if (!isset($path)) {
//            throw new \coding_exception('Using getUploadedH5pFolderPath() before path is set');
//        }
//
//        return $path;
//    }
//
//    public function getUploadedH5pPath($setpath = null) {
//        static $path;
//
//        if ($setpath !== null) {
//            $path = $setpath;
//        }
//
//        return $path;
//    }
//
//    public function loadLibraries() {
//    }
//
//    public function setUnsupportedLibraries($libraries) {
//        // Not supported.
//    }
//
//    public function getUnsupportedLibraries() {
//        // Not supported.
//    }
//
//    public function getAdminUrl() {
//        // Not supported.
//    }
//
//    public function getLibraryId($machinename, $majorversion = null, $minorversion = null) {
//        global $DB;
//
//        // Look for specific library.
//        $sqlwhere = 'WHERE machinename = ?';
//        $sqlargs = array($machinename);
//
//        if ($majorversion !== null) {
//            // Look for major version.
//            $sqlwhere .= ' AND majorversion = ?';
//            $sqlargs[] = $majorversion;
//            if ($minorversion !== null) {
//                // Look for minor version.
//                $sqlwhere .= ' AND minorversion = ?';
//                $sqlargs[] = $minorversion;
//            }
//        }
//
//        // Get the lastest version which matches the input parameters.
//        $libraries = $DB->get_records_sql("
//                SELECT id
//                  FROM {h5p_libraries}
//          {$sqlwhere}
//              ORDER BY majorversion DESC,
//                       minorversion DESC,
//                       patchversion DESC
//                ", $sqlargs, 0, 1);
//        if ($libraries) {
//            $library = reset($libraries);
//            return $library ? $library->id : false;
//        } else {
//            return false;
//        }
//    }
//
//    public function isPatchedLibrary($library) {
//    }
//
//    public function isInDevMode() {
//        return false; // Not supported (Files in moodle not editable).
//    }
//
//    public function mayUpdateLibraries($allow = false) {
//        static $override;
//
//        return true; // <-- bas always return true for now.
//
//        // Allow overriding the permission check. Needed when installing.
//        // since caps hasn't been set.
//        if ($allow) {
//            $override = true;
//        }
//        if ($override) {
//            return true;
//        }
//
//        // Check permissions.
//        $context = \context_system::instance();
//        if (!has_capability('core/hvp:updatelibraries', $context)) {
//            return false;
//        }
//
//        return true;
//    }
//
//    public function getLibraryUsage($id, $skipcontent = false) {
//    }
//
//    public function getLibraryContentCount() {
//    }
//
//    public function saveLibraryData(&$librarydata, $new = true) {
//        global $DB;
//
//        // Some special properties needs some checking and converting before they can be saved.
//        $preloadedjs = $this->pathsToCsv($librarydata, 'preloadedJs');
//        $preloadedcss = $this->pathsToCsv($librarydata, 'preloadedCss');
//        $droplibrarycss = '';
//
//        if (isset($librarydata['dropLibraryCss'])) {
//            $libs = array();
//            foreach ($librarydata['dropLibraryCss'] as $lib) {
//                $libs[] = $lib['machineName'];
//            }
//            $droplibrarycss = implode(', ', $libs);
//        }
//
//        $embedtypes = '';
//        if (isset($librarydata['embedTypes'])) {
//            $embedtypes = implode(', ', $librarydata['embedTypes']);
//        }
//        if (!isset($librarydata['semantics'])) {
//            $librarydata['semantics'] = '';
//        }
//        if (!isset($librarydata['fullscreen'])) {
//            $librarydata['fullscreen'] = 0;
//        }
//        if (!isset($librarydata['hasIcon'])) {
//            $librarydata['hasIcon'] = 0;
//        }
//        // TODO: Can we move the above code to H5PCore? It's the same for multiple
//        // implementations. Perhaps core can update the data objects before calling
//        // this function?
//        // I think maybe it's best to do this when classes are created for
//        // library, content, etc.
//
//        $library = (object) array(
//            'title' => $librarydata['title'],
//            'machinename' => $librarydata['machineName'],
//            'majorversion' => $librarydata['majorVersion'],
//            'minorversion' => $librarydata['minorVersion'],
//            'semantics' => $librarydata['semantics'],
//            'patchversion' => $librarydata['patchVersion'],
//            'runnable' => $librarydata['runnable'],
//            'fullscreen' => $librarydata['fullscreen'],
//            'embedtypes' => $embedtypes,
//            'preloaded_js' => $preloadedjs,
//            'preloaded_css' => $preloadedcss,
//            'droplibrarycss' => $droplibrarycss
//        );
//
//        if ($new) {
//            // Create new library and keep track of id.
//            $library->id = $DB->insert_record('h5p_libraries', $library);
//            $librarydata['libraryId'] = $library->id;
//        } else {
//            // Update library data.
//            $library->id = $librarydata['libraryId'];
//
//            // Save library data.
//            $DB->update_record('h5p_libraries', (object) $library);
//
//            // Remove old dependencies.
//            $this->deleteLibraryDependencies($librarydata['libraryId']);
//        }
//
//        // Update library translations.
//    }
//
//    private function pathsToCsv($librarydata, $key) {
//        if (isset($librarydata[$key])) {
//            $paths = array();
//            foreach ($librarydata[$key] as $file) {
//                $paths[] = $file['path'];
//            }
//            return implode(', ', $paths);
//        }
//        return '';
//    }
//
//    public function lockDependencyStorage() {
//        // Library development mode not supported.
//    }
//
//    public function unlockDependencyStorage() {
//        // Library development mode not supported.
//    }
//
//    public function deleteLibrary($library) {
//    }
//
//    public function saveLibraryDependencies($libraryid, $dependencies, $dependencytype) {
//    }
//
//    public function updateContent($content, $contentmainid = null) {
//        global $DB;
//
//        $data = array(
//            'jsoncontent' => $content['params'],
//            'embedtype' => 'div',
//            'mainlibraryid' => $content['library']['libraryId'],
//            'timemodified' => time(),
//        );
//
//        if (!isset($content['id'])) {
//            $data['slug'] = '';
//            $data['timecreated'] = $data['timemodified'];
//            $id = $DB->insert_record('h5p', $data);
//        } else {
//            $data['id'] = $content['id'];
//            $DB->update_record('h5p', $data);
//            $id = $data['id'];
//        }
//
//        return $id;
//    }
//
//    public function insertContent($content, $contentmainid = null) {
//        return $this->updateContent($content);
//    }
//
//    public function resetContentUserData($contentid) {
//    }
//
//    public function getWhitelist($islibrary, $defaultcontentwhitelist, $defaultlibrarywhitelist) {
//        return $defaultcontentwhitelist . ($islibrary ? ' ' . $defaultlibrarywhitelist : '');
//    }
//
//    public function copyLibraryUsage($contentid, $copyfromid, $contentmainid = null) {
//    }
//
//    public function loadLibrarySemantics($name, $majorversion, $minorversion) {
//        global $DB;
//
//        $semantics = $DB->get_field_sql(
//            "SELECT semantics
//            FROM {h5p_libraries}
//            WHERE machinename = ?
//            AND majorversion = ?
//            AND minorversion = ?",
//            array($name, $majorversion, $minorversion));
//
//        return ($semantics === false ? null : $semantics);
//    }
//
//    public function alterLibrarySemantics(&$semantics, $name, $majorversion, $minorversion) {
//    }
//
//    public function getOption($name, $default = false) {
//    }
//
//    public function setOption($name, $value) {
//    }
//
//    public function updateContentFields($id, $fields) {
//    }
//
//    public function deleteLibraryDependencies($libraryid) {
//    }
//
//    public function deleteContentData($contentid) {
//    }
//
//    public function deleteLibraryUsage($contentid) {
//    }
//
//    public function saveLibraryUsage($contentid, $librariesinuse) {
//        global $DB;
//
//        $droplibrarycsslist = array();
//        foreach ($librariesinuse as $dependency) {
//            if (!empty($dependency['library']['dropLibraryCss'])) {
//                $droplibrarycsslist = array_merge($droplibrarycsslist, explode(', ', $dependency['library']['dropLibraryCss']));
//            }
//        }
//        // TODO: Consider moving the above code to core. Same for all impl.
//
//        foreach ($librariesinuse as $dependency) {
//            $dropcss = in_array($dependency['library']['machineName'], $droplibrarycsslist) ? 1 : 0;
//            $params = array(
//                'h5pid' => $contentid,
//                'libraryid' => $dependency['library']['libraryId'],
//                'dependencytype' => $dependency['type'],
//                'dropcss' => $dropcss,
//                'weight' => $dependency['weight']
//            );
//
//            $DB->insert_record('h5p_contents_libraries', $params);
//        }
//    }
//
//    public function loadLibrary($machinename, $majorversion, $minorversion) {
//        global $DB;
//
//        $library = $DB->get_record('h5p_libraries', array(
//            'machinename' => $machinename,
//            'majorversion' => $majorversion,
//            'minorversion' => $minorversion
//        ));
//
//        $librarydata = array(
//            'libraryId' => $library->id,
//            'machineName' => $library->machinename,
//            'title' => $library->title,
//            'majorVersion' => $library->majorversion,
//            'minorVersion' => $library->minorversion,
//            'patchVersion' => '',
//            'embedTypes' => '',
//            'preloadedJs' => $library->preloaded_js,
//            'preloadedCss' => $library->preloaded_css,
//            'dropLibraryCss' => $library->droplibrarycss,
//            'semantics' => $library->semantics
//        );
//
//        $dependencies = $DB->get_records_sql(
//                'SELECT hl.id, hl.machinename, hl.majorversion, hl.minorversion, hll.dependencytype
//                   FROM {h5p_library_dependencies} hll
//                   JOIN {h5p_libraries} hl ON hll.requiredlibraryid = hl.id
//                  WHERE hll.libraryid = ?', array($library->id));
//        foreach ($dependencies as $dependency) {
//            $librarydata[$dependency->dependency_type . 'Dependencies'][] = array(
//                'machineName' => $dependency->machine_name,
//                'majorVersion' => $dependency->major_version,
//                'minorVersion' => $dependency->minor_version
//            );
//        }
//
//        return $librarydata;
//    }
//
//    public function clearFilteredParameters($libraryid) {
//    }
//
//    public function getNumNotFiltered() {
//    }
//
//    public function getNumContent($libraryid, $skip = NULL) {
//    }
//
//    public function isContentSlugAvailable($slug) {
//    }
//
//    public function saveCachedAssets($key, $libraries) {
//    }
//
//    public function deleteCachedAssets($libraryid) {
//    }
//
//    public function getLibraryStats($type) {
//    }
//
//    public function getNumAuthors() {
//    }
//
//    public function afterExportCreated($content, $filename) {
//    }
//
//    public function hasPermission($permission, $cmid = null) {
//    }
//
//    private function getajaxcoursecontext() {
//    }
//
//    public function replaceContentTypeCache($contenttypecache) {
//    }
//
//    public function loadAddons() {
//        global $DB;
//        $addons = array();
//
//        // $records = $DB->get_records_sql(
//        //         "SELECT l1.id AS library_id,
//        //                 l1.machinename,
//        //                 l1.majorversion,
//        //                 l1.minorversion,
//        //                 l1.patchversion,
//        //                 l1.preloaded_js,
//        //                 l1.preloaded_css
//        //            FROM {h5p_libraries} l1
//        //       LEFT JOIN {h5p_libraries} l2
//        //              ON l1.machinename = l2.machinename
//        //             AND (l1.majorversion < l2.majorversion
//        //                  OR (l1.majorversion = l2.majorversion
//        //                      AND l1.minorversion < l2.minorversion))
//        //             AND l2.machinename IS NULL");
//
//        // // NOTE: These are treated as library objects but are missing the following properties:
//        // // title, embed_types, drop_library_css, fullscreen, runnable, semantics, has_icon
//
//        // // Extract num from records.
//        // foreach ($records as $addon) {
//        //     $addons[] = \H5PCore::snakeToCamel($addon);
//        // }
//
//        return $addons;
//    }
//
//    public function getLibraryConfig($libraries = null) {
//    }
//
//    public function libraryHasUpgrade($library) {
//    }
//}
//
//class core_h5p_file_storage implements \H5PFileStorage {
//
//    public $contentId;
//
//    public function saveLibrary($library) {
//        // Libraries are stored in a system context.
//        $context = \context_system::instance();
//        $options = array(
//            'contextid' => $context->id,
//            'component' => 'core_h5p',
//            'filearea' => 'libraries',
//            'itemid' => 0,
//            'filepath' => '/' . \H5PCore::libraryToString($library, true) . '/',
//        );
//
//        // Remove any old existing library files.
//        self::deleteFileTree($context->id, $options['filearea'], $options['filepath']);
//
//        // Move library folder.
//        self::readFileTree($library['uploadDirectory'], $options);
//    }
//
//    public function saveContent($source, $content) {
//        // Remove any old content.
//        $this->deleteContent($content);
//        // Contents are stored in a course context.
//        $context = \context_system::instance();
//        $options = array(
//            'contextid' => $context->id,
//            'component' => 'core_h5p',
//            'filearea' => 'content',
//            'itemid' => $content['id'],
//            'filepath' => '/',
//        );
//
//        $this->contentId = $content['id'];
//        // Move content folder.
//        self::readFileTree($source, $options);
//    }
//
//    public function deleteContent($content) {
//    }
//
//    public function cloneContent($id, $newid) {
//    }
//
//    public function getTmpPath() {
//    }
//
//    public function exportContent($id, $target) {
//    }
//
//    public function exportLibrary($library, $target) {
//    }
//
//    public function saveExport($source, $filename) {
//    }
//
//    private function getExportFile($filename) {
//    }
//
//    public function deleteExport($filename) {
//    }
//
//    public function hasExport($filename) {
//    }
//
//    public function cacheAssets(&$files, $key) {
//    }
//
//    public function getCachedAssets($key) {
//    }
//
//    public function deleteCachedAssets($keys) {
//    }
//
//    public function getContent($filepath) {
//    }
//
//    public function saveFile($file, $contentid, $contextid = null) {
//    }
//
//    public function cloneContentFile($file, $fromid, $tocontent) {
//    }
//
//    public function getContentFile($file, $content) {
//    }
//
//    public function removeContentFile($file, $content) {
//    }
//
//    private static function readFileTree($source, $options) {
//        $dir = opendir($source);
//        if ($dir === false) {
//            trigger_error('Unable to open directory ' . $source, E_USER_WARNING);
//            throw new \Exception('unabletocopy');
//        }
//
//        while (false !== ($file = readdir($dir))) {
//            if (($file != '.') && ($file != '..') && $file != '.git' && $file != '.gitignore') {
//                if (is_dir($source . DIRECTORY_SEPARATOR . $file)) {
//                    $suboptions = $options;
//                    $suboptions['filepath'] .= $file . '/';
//                    self::readFileTree($source . '/' . $file, $suboptions);
//                } else {
//                    $record = $options;
//                    $record['filename'] = $file;
//                    $fs = get_file_storage();
//                    $fs->create_file_from_pathname($record, $source . '/' . $file);
//                }
//            }
//        }
//        closedir($dir);
//    }
//
//    private static function exportFileTree($target, $contextid, $filearea, $filepath, $itemid = 0) {
//    }
//
//    private static function deleteFileTree($contextid, $filearea, $filepath, $itemid = 0) {
//    }
//
//    private function getFile($filearea, $itemid, $file) {
//    }
//
//    private function getFilepath($file) {
//    }
//
//    private function getFilename($file) {
//    }
//
//    public static function fileExists($contextid, $filearea, $filepath, $filename) {
//    }
//
//    public function hasWriteAccess() {
//    }
//
//    public function moveContentDirectory($source, $contentid = null) {
//    }
//
//    private static function moveFile($sourcefile, $contextid, $contentid) {
//    }
//
//    private static function moveFileTree($sourcefiletree, $contextid, $contentid) {
//    }
//
//    public function hasPresave($libraryname, $developmentpath = null) {
//    }
//
//    public function getUpgradeScript($machinename, $majorversion, $minorversion) {
//    }
//}