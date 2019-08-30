<?php
/**
 * Created by PhpStorm.
 * User: mihail
 * Date: 29/08/19
 * Time: 10:35 AM
 */

namespace core_h5p;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once(__DIR__ . '/../autoloader.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/adminlib.php');

class view_assets {

    private $core;
    private $content;
    private $jsrequires;
    private $cssrequires;
    private $testidnumber;

    protected $settings;

    public function __construct($idnumber) {
        global $CFG;
        $this->testidnumber = $idnumber;
        $this->core        = \core_h5p\framework::instance();
        $this->content     = $this->core->loadContent($this->testidnumber);
        $this->jsrequires  = [];
        $this->cssrequires = [];
        $this->settings = $this->get_core_assets(\context_system::instance());
        $context        = \context_system::instance();
        $this->settings['contents'][ 'cid-' . $this->testidnumber ]   = array(
            'library'         => \H5PCore::libraryToString($this->content['library']),
            'jsonContent'     => $this->getfilteredparameters(),
            'fullScreen'      => $this->content['library']['fullscreen'],
            'exportUrl'       => "",
            'embedCode'       => "No Embed Code",
            'resizeCode'      => $this->getresizecode($this->testidnumber),
            'title'           => $this->content['slug'],
            'displayOptions'  => '',
            'url'             => "{$CFG->wwwroot}/lib/classes/output/h5p_embed.php?id={$this->testidnumber}",
            'contentUrl'      => "{$CFG->wwwroot}/pluginfile.php/{$context->id}/core_h5p/content/{$this->testidnumber}",
            'metadata'        => '',
            'contentUserData' => array()
        );

        $this->files = $this->getdependencyfiles();

        $this->generateassets();
    }

    public function getcontent() {
        return $this->content;
    }

    public function get_cache_buster() {
        return '?ver=' . 1;
    }

    public function get_core_assets($context) {
        global $CFG, $PAGE;
        // Get core settings.
        $settings = $this->get_core_settings($context);
        $settings['core'] = [
          'styles' => [],
          'scripts' => []
        ];
        $settings['loadedJs'] = [];
        $settings['loadedCss'] = [];

        // Make sure files are reloaded for each plugin update.
        $cachebuster = $this->get_cache_buster();

        // Use relative URL to support both http and https.
        $liburl = $CFG->wwwroot . '/lib/h5p/';
        $relpath = '/' . preg_replace('/^[^:]+:\/\/[^\/]+\//', '', $liburl);

        // Add core stylesheets.
        foreach (\H5PCore::$styles as $style) {
            $settings['core']['styles'][] = $relpath . $style . $cachebuster;
            //$this->cssrequires[] = new moodle_url($liburl . $style . $cachebuster);
        }
        // Add core JavaScript.
        foreach (\H5PCore::$scripts as $script) {
            $settings['core']['scripts'][] = $relpath . $script . $cachebuster;
            $this->jsrequires[] = new \moodle_url($liburl . $script . $cachebuster);
        }

        return $settings;
    }

    public function get_core_settings($context) {
        global $USER, $CFG;

        $basepath = $CFG->wwwroot . '/';

        // Check permissions and generate ajax paths.
        $ajaxpaths = [];
        $ajaxpaths['setFinished'] = '';
        $ajaxpaths['xAPIResult'] = '';
        $ajaxpaths['contentUserData'] = '';

        $settings = array(
            'baseUrl' => $basepath,
            'url' => "{",
            'urlLibraries' => "",
            'postUserStatistics' => true,
            'ajax' => $ajaxpaths,
            'saveFreq' => false,
            'siteUrl' => $CFG->wwwroot,
            'l10n' => array('H5P' => $this->core->getLocalization()),
            'user' => [],
            'hubIsEnabled' => false,
            'reportingIsEnabled' => true,
            'crossorigin' => null,
            'libraryConfig' => '',
            'pluginCacheBuster' => $this->get_cache_buster(),
            'libraryUrl' => ''
        );

        return $settings;
    }

    public function getfilteredparameters() {
        global $PAGE;

        $safeparameters = $this->core->filterParameters($this->content);

        $decodedparams  = json_decode($safeparameters);
        $safeparameters = json_encode($decodedparams);

        return $safeparameters;
    }

    /**
     * Resizing script for settings
     *
     * @param $embedenabled
     *
     * @return string
     */
    private function getresizecode($embedenabled) {
        global $CFG;

        if ( ! $embedenabled) {
            return '';
        }

        $resizeurl = new \moodle_url($CFG->wwwroot . '/lib/h5p/js/h5p-resizer.js');

        return "<script src=\"{$resizeurl->out()}\" charset=\"UTF-8\"></script>";
    }

    /**
     * Adds js assets to current page
     */
    public function addassetstopage() {
        global $PAGE, $CFG;

        foreach ($this->jsrequires as $script) {
            $PAGE->requires->js($script, true);
        }

        foreach ($this->cssrequires as $css) {
            $PAGE->requires->css($css);
        }

        // Print JavaScript settings to page.
        $PAGE->requires->data_for_js('H5PIntegration', $this->settings, true);
    }

    /**
     * Finds library dependencies of view
     *
     * @return array Files that the view has dependencies to
     */
    private function getdependencyfiles() {
        global $PAGE;

        $preloadeddeps = $this->core->loadContentDependencies($this->testidnumber);
        $files         = $this->core->getDependenciesFiles($preloadeddeps);

        return $files;
    }

    public function generateassets() {
        global $CFG;
        $context = \context_system::instance();
        $h5ppath = "/pluginfile.php/{$context->id}/core_h5p";

        // Schedule JavaScripts for loading through Moodle.
        foreach ($this->files['scripts'] as $script) {
            $url = $script->path . $script->version;

            // Add URL prefix if not external.
            $isexternal = strpos($script->path, '://');
            if ($isexternal === false) {
                $url = $h5ppath . $url;
            }
            $this->settings['loadedJs'][] = $url;
            $this->jsrequires[] = new \moodle_url($isexternal ? $url : $CFG->wwwroot . $url);
        }

        // Schedule stylesheets for loading through Moodle.
        foreach ($this->files['styles'] as $style) {
            $url = $style->path . $style->version;

            // Add URL prefix if not external.
            $isexternal = strpos($style->path, '://');
            if ($isexternal === false) {
                $url = $h5ppath . $url;
            }
            $this->settings['loadedCss'][] = $url;
            $this->cssrequires[] = new \moodle_url($isexternal ? $url : $CFG->wwwroot . $url);
        }
    }

    public function outputview() {
        return "<div class=\"h5p-content\" data-content-id=\"{$this->testidnumber}\"></div>";
    }
}