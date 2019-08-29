<?php
/**
 * Created by PhpStorm.
 * User: mihail
 * Date: 29/08/19
 * Time: 10:28 AM
 */

namespace core_h5p;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once(__DIR__ . '/../autoloader.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/adminlib.php');

class file_storage implements \H5PFileStorage {

    public $contentId;

    public function saveLibrary($library) {
        // Libraries are stored in a system context.
        $context = \context_system::instance();
        $options = array(
            'contextid' => $context->id,
            'component' => 'core_h5p',
            'filearea' => 'libraries',
            'itemid' => 0,
            'filepath' => '/' . \H5PCore::libraryToString($library, true) . '/',
        );

        // Remove any old existing library files.
        self::deleteFileTree($context->id, $options['filearea'], $options['filepath']);

        // Move library folder.
        self::readFileTree($library['uploadDirectory'], $options);
    }

    public function saveContent($source, $content) {
        // Remove any old content.
        $this->deleteContent($content);
        // Contents are stored in a course context.
        $context = \context_system::instance();
        $options = array(
            'contextid' => $context->id,
            'component' => 'core_h5p',
            'filearea' => 'content',
            'itemid' => $content['id'],
            'filepath' => '/',
        );

        $this->contentId = $content['id'];
        // Move content folder.
        self::readFileTree($source, $options);
    }

    public function deleteContent($content) {
    }

    public function cloneContent($id, $newid) {
    }

    public function getTmpPath() {
    }

    public function exportContent($id, $target) {
    }

    public function exportLibrary($library, $target) {
    }

    public function saveExport($source, $filename) {
    }

    private function getExportFile($filename) {
    }

    public function deleteExport($filename) {
    }

    public function hasExport($filename) {
    }

    public function cacheAssets(&$files, $key) {
    }

    public function getCachedAssets($key) {
    }

    public function deleteCachedAssets($keys) {
    }

    public function getContent($filepath) {
    }

    public function saveFile($file, $contentid, $contextid = null) {
    }

    public function cloneContentFile($file, $fromid, $tocontent) {
    }

    public function getContentFile($file, $content) {
    }

    public function removeContentFile($file, $content) {
    }

    private static function readFileTree($source, $options) {
        $dir = opendir($source);
        if ($dir === false) {
            trigger_error('Unable to open directory ' . $source, E_USER_WARNING);
            throw new \Exception('unabletocopy');
        }

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..') && $file != '.git' && $file != '.gitignore') {
                if (is_dir($source . DIRECTORY_SEPARATOR . $file)) {
                    $suboptions = $options;
                    $suboptions['filepath'] .= $file . '/';
                    self::readFileTree($source . '/' . $file, $suboptions);
                } else {
                    $record = $options;
                    $record['filename'] = $file;
                    $fs = get_file_storage();
                    $fs->create_file_from_pathname($record, $source . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    private static function exportFileTree($target, $contextid, $filearea, $filepath, $itemid = 0) {
    }

    private static function deleteFileTree($contextid, $filearea, $filepath, $itemid = 0) {
    }

    private function getFile($filearea, $itemid, $file) {
    }

    private function getFilepath($file) {
    }

    private function getFilename($file) {
    }

    public static function fileExists($contextid, $filearea, $filepath, $filename) {
    }

    public function hasWriteAccess() {
    }

    public function moveContentDirectory($source, $contentid = null) {
    }

    private static function moveFile($sourcefile, $contextid, $contentid) {
    }

    private static function moveFileTree($sourcefiletree, $contextid, $contentid) {
    }

    public function hasPresave($libraryname, $developmentpath = null) {
    }

    public function getUpgradeScript($machinename, $majorversion, $minorversion) {
    }
}