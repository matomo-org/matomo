<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel\PluginList;

use Piwik\Application\Kernel\GlobalSettingsProvider;

/**
 * TODO
 */
class IniPluginList implements \Piwik\Application\Kernel\PluginList
{
    /**
     * @var GlobalSettingsProvider
     */
    private $settings;

    public function __construct(GlobalSettingsProvider $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return string[]
     */
    public function getActivatedPlugins()
    {
        $section = $this->settings->getSection('Plugins');
        return $section['Plugins'];
    }

    /**
     * @return string[]
     */
    public function getInstalledPlugins()
    {
        $section = $this->settings->getSection('PluginsInstalled');
        return $section['PluginsInstalled'];
    }
}