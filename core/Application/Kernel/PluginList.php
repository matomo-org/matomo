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

    /**
     * Plugins bundled with core package, disabled by default
     * @var array
     */
    private $corePluginsDisabledByDefault = array(
        'DBStats',
        'ExampleCommand',
        'ExampleSettingsPlugin',
        'ExampleUI',
        'ExampleVisualization',
        'ExamplePluginTemplate',
        'ExampleTracker',
        'ExampleReport',
        'MobileAppMeasurable',
        'Provider'
    );

    // Themes bundled with core package, disabled by default
    private $coreThemesDisabledByDefault = array(
        'ExampleTheme'
    );

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

    /**
     * Returns the plugins bundled with core package that are disabled by default.
     *
     * @return string[]
     */
    public function getCorePluginsDisabledByDefault()
    {
        return array_merge($this->corePluginsDisabledByDefault, $this->coreThemesDisabledByDefault);
    }

    /**
     * Sorts an array of plugins in the order they should be loaded.
     *
     * @params string[] $plugins
     * @return \string[]
     */
    public function sortPlugins(array $plugins)
    {
        $global = $this->getPluginsBundledWithPiwik();
        if (empty($global)) {
            return $plugins;
        }

        // we need to make sure a possibly disabled plugin will be still loaded before any 3rd party plugin
        $global = array_merge($global, $this->corePluginsDisabledByDefault);

        $global = array_values($global);
        $plugins = array_values($plugins);

        $defaultPluginsLoadedFirst = array_intersect($global, $plugins);

        $otherPluginsToLoadAfterDefaultPlugins = array_diff($plugins, $defaultPluginsLoadedFirst);

        // sort by name to have a predictable order for those extra plugins
        sort($otherPluginsToLoadAfterDefaultPlugins);

        $sorted = array_merge($defaultPluginsLoadedFirst, $otherPluginsToLoadAfterDefaultPlugins);

        return $sorted;
    }
}
