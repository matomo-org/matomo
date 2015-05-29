<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel;

/**
 * Lists the currently activated plugins. Used when setting up Piwik's environment before
 * initializing the DI container.
 *
 * Uses the [Plugins] section in Piwik's INI config to get the activated plugins.
 *
 * Depends on GlobalSettingsProvider being used.
 *
 * TODO: parts of Plugin\Manager edit the plugin list; maybe PluginList implementations should be mutable?
 */
class PluginList
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
     * Returns the list of plugins that should be loaded. Used by the container factory to
     * load plugin specific DI overrides.
     *
     * @return string[]
     */
    public function getActivatedPlugins()
    {
        $section = $this->settings->getSection('Plugins');
        return @$section['Plugins'] ?: array();
    }

    /**
     * Returns the list of plugins that are bundled with Piwik.
     *
     * @return string[]
     */
    public function getPluginsBundledWithPiwik()
    {
        $pathGlobal = $this->settings->getPathGlobal();

        $section = $this->settings->getIniFileChain()->getFrom($pathGlobal, 'Plugins');
        return $section['Plugins'];
    }
}
