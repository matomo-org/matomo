<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\AssetManager;

use Piwik\Plugin;
use Piwik\Theme;

class ThemeMock extends Theme
{
    /**
     * @var string[]
     */
    private $jsFiles = array();

    /**
     * @var string
     */
    private $stylesheet;

    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @param Plugin $plugin
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }

    public function getStylesheet()
    {
        return $this->stylesheet;
    }

    public function getJavaScriptFiles()
    {
        return $this->jsFiles;
    }

    /**
     * @param string[] $jsFiles
     */
    public function setJsFiles($jsFiles)
    {
        $this->jsFiles = $jsFiles;
    }

    /**
     * @param string $stylesheet
     */
    public function setStylesheet($stylesheet)
    {
        $this->stylesheet = $stylesheet;
    }

    /**
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    public function getThemeName()
    {
        return $this->plugin->getPluginName();
    }
}
