<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel;

/**
 * TODO
 */
interface PluginList
{
    /**
     * @return string[]
     */
    public function getActivatedPlugins();

    /**
     * @return string[]
     */
    public function getInstalledPlugins(); // TODO: should this be here? or is it better to store in the DB?
}