<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel\PluginList;

use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Application\Kernel\PluginList;

/**
 * Default implementation of the PluginList interface. Uses the [Plugins] section
 * in Piwik's INI config to get the activated plugins.
 *
 * Depends on GlobalSettingsProvider being used.
 */
class IniPluginList implements PluginList
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
        return @$section['Plugins'] ?: array();
    }

    /**
     * @return string[]
     */
    public function getPluginsBundledWithPiwik()
    {
        $pathGlobal = $this->settings->getPathGlobal();

        $section = $this->settings->getIniFileChain()->getFrom($pathGlobal, 'Plugins');
        return $section['Plugins'];
    }
}