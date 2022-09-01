<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel;

use Piwik\Plugin\MetadataLoader;

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
        'ExamplePlugin',
        'ExampleCommand',
        'ExampleSettingsPlugin',
        'ExampleUI',
        'ExampleVisualization',
        'ExamplePluginTemplate',
        'ExampleTracker',
        'ExampleLogTables',
        'ExampleReport',
        'ExampleAPI',
        'ExampleVue',
        'MobileAppMeasurable',
        'TagManager'
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
        $plugins = @$section['Plugins'] ?: array();

        return $plugins;
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
     * Sorts an array of plugins in the order they should be loaded. We cannot use DI here as DI is not initialized
     * at this stage.
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
        natcasesort($otherPluginsToLoadAfterDefaultPlugins);

        $sorted = array_merge($defaultPluginsLoadedFirst, $otherPluginsToLoadAfterDefaultPlugins);

        return $sorted;
    }

    /**
     * Sorts an array of plugins in the order they should be saved in config.ini.php. This basically influences
     * the order of the plugin config.php and which config will be loaded first. We want to make sure to require the
     * config or a required plugin first before loading the plugin that requires it.
     *
     * We do not sort using this logic on each request since it is much slower than `sortPlugins()`. The order
     * of plugins in config.ini.php is only important for the ContainerFactory. During a regular request it is otherwise
     * fine to load the plugins in the order of `sortPlugins()` since we will make sure that required plugins will be
     * loaded first in plugin manager.
     *
     * @param string[] $plugins
     * @param array[] $pluginJsonCache  For internal testing only
     * @return \string[]
     */
    public function sortPluginsAndRespectDependencies(array $plugins, $pluginJsonCache = array())
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

        // we still want to sort alphabetically by default
        natcasesort($otherPluginsToLoadAfterDefaultPlugins);

        $sorted = array();
        foreach ($otherPluginsToLoadAfterDefaultPlugins as $pluginName) {
            $sorted = $this->sortRequiredPlugin($pluginName, $pluginJsonCache, $otherPluginsToLoadAfterDefaultPlugins, $sorted);
        }

        $sorted = array_merge($defaultPluginsLoadedFirst, $sorted);

        return $sorted;
    }

    private function sortRequiredPlugin($pluginName, &$pluginJsonCache, $toBeSorted, $sorted)
    {
        if (!isset($pluginJsonCache[$pluginName])) {
            $loader = new MetadataLoader($pluginName);
            $pluginJsonCache[$pluginName] = $loader->loadPluginInfoJson();
        }

        if (!empty($pluginJsonCache[$pluginName]['require'])) {
            $dependencies = $pluginJsonCache[$pluginName]['require'];
            foreach ($dependencies as $possiblePluginName => $key) {
                if (in_array($possiblePluginName, $toBeSorted, true) && !in_array($possiblePluginName, $sorted, true)) {
                    $sorted = $this->sortRequiredPlugin($possiblePluginName, $pluginJsonCache, $toBeSorted, $sorted);
                }
            }
        }

        if (!in_array($pluginName, $sorted, true)) {
            $sorted[] = $pluginName;
        }

        return $sorted;
    }
}
