<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\AssetManager;

use Piwik\Plugin\Manager;
use Piwik\Plugin;
use Piwik\Theme;

class PluginManagerMock extends Manager
{
    /**
     * @var Plugin[]
     */
    private $plugins = array();

    /**
     * @var Theme
     */
    private $loadedTheme;

    /**
     * @param Plugin[] $plugins
     */
    public function setPlugins($plugins)
    {
        $this->plugins = $plugins;
    }

    public function isPluginLoaded($name)
    {
        $plugin = $this->getLoadedPlugin($name);
        return !empty($plugin);
    }

    public function getLoadedPlugin($name)
    {
        foreach ($this->plugins as $plugin) {
            if ($plugin->getPluginName() == $name) {
                return $plugin;
            }
        }

        return null;
    }

    public function getPluginsLoadedAndActivated()
    {
        return $this->getLoadedPlugins();
    }

    public function getLoadedPluginsName()
    {
        $pluginNames = array();

        foreach($this->plugins as $plugin) {
            $pluginNames[] = $plugin->getPluginName();
        }

        return $pluginNames;
    }

    public function getLoadedPlugins()
    {
        return $this->plugins;
    }

    public function getTheme($themeName)
    {
        return $this->loadedTheme;
    }

    /**
     * @param Theme $loadedTheme
     */
    public function setLoadedTheme($loadedTheme)
    {
        $this->loadedTheme = $loadedTheme;
    }

    public function isPluginBundledWithCore($name)
    {
        return stripos($name, 'NonCore') === false;
    }
}
