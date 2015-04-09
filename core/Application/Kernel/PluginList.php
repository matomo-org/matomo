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
 * The default implementation is the IniPluginList class.
 *
 * TODO: parts of Plugin\Manager edit the plugin list; maybe PluginList implementations should be mutable?
 */
interface PluginList
{
    /**
     * Returns the list of plugins that should be loaded. Used by the container factory to
     * load plugin specific DI overrides.
     *
     * @return string[]
     */
    public function getActivatedPlugins();

    /**
     * Returns the list of plugins that are bundled with Piwik.
     *
     * @return string[]
     */
    public function getPluginsBundledWithPiwik();
}