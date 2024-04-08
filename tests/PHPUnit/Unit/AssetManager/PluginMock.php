<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\AssetManager;

use Piwik\Plugin;

class PluginMock extends Plugin
{
    /**
     * @var string[]
     */
    private $jsFiles = array();

    /**
     * @var string[]
     */
    private $stylesheetFiles = array();

    /**
     * @var string
     */
    private $jsCustomization = '';

    /**
     * @var string
     */
    private $cssCustomization = '';

    /**
     * @var boolean
     */
    private $isTheme = false;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->pluginName = $name;
    }

    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.filterMergedJavaScripts' => 'filterMergedJavaScriptsHook',
            'AssetManager.filterMergedStylesheets' => 'filterMergedStylesheetsHook',
        );
    }

    /**
     * @param string[] $jsFiles
     */
    public function getJsFiles(&$jsFiles)
    {
        $jsFiles = array_merge($jsFiles, $this->jsFiles);
    }

    /**
     * @param string[] $stylesheetFiles
     */
    public function getStylesheetFiles(&$stylesheetFiles)
    {
        $stylesheetFiles = array_merge($stylesheetFiles, $this->stylesheetFiles);
    }

    /**
     * @param string $mergedContent
     */
    public function filterMergedJavaScriptsHook(&$mergedContent)
    {
        $mergedContent .= $this->jsCustomization;
    }

    /**
     * @param string $mergedContent
     */
    public function filterMergedStylesheetsHook(&$mergedContent)
    {
        $mergedContent .= $this->cssCustomization;
    }

    /**
     * @param string $cssCustomization
     */
    public function setCssCustomization($cssCustomization)
    {
        $this->cssCustomization = $cssCustomization;
    }

    /**
     * @param string $jsCustomization
     */
    public function setJsCustomization($jsCustomization)
    {
        $this->jsCustomization = $jsCustomization;
    }

    /**
     * @param string[] $jsFiles
     */
    public function setJsFiles($jsFiles)
    {
        $this->jsFiles = $jsFiles;
    }

    /**
     * @param string[] $stylesheetFiles
     */
    public function setStylesheetFiles($stylesheetFiles)
    {
        $this->stylesheetFiles = $stylesheetFiles;
    }

    /**
     * @param boolean $isTheme
     */
    public function setIsTheme($isTheme)
    {
        $this->isTheme = $isTheme;
    }

    public function isTheme()
    {
        return $this->isTheme;
    }
}
