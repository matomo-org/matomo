<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik;

/**
 * Abstract class for update scripts
 *
 * @example core/Updates/0.4.2.php
 * @package Piwik
 */
abstract class Updates
{
    /**
     * Return SQL to be executed in this update
     *
     * @param string $schema Schema name
     * @return array(
     *              'ALTER .... ' => '1234', // if the query fails, it will be ignored if the error code is 1234
     *              'ALTER .... ' => false,  // if an error occurs, the update will stop and fail
     *                                       // and user will have to manually run the query
     *         )
     */
    static function getSql($schema = 'Myisam')
    {
        return array();
    }

    /**
     * Incremental version update
     */
    static function update()
    {
    }

    /**
     * Tell the updater that this is a major update.
     * Leads to a more visible notice.
     *
     * @return bool
     */
    static function isMajorUpdate()
    {
        return false;
    }

    /**
     * Helper method to enable maintenance mode during large updates
     */
    static function enableMaintenanceMode()
    {
        $config = Config::getInstance();
        $config->init();

        $tracker = $config->Tracker;
        $tracker['record_statistics'] = 0;
        $config->Tracker = $tracker;

        $general = $config->General;
        $general['maintenance_mode'] = 1;
        $config->General = $general;

        $config->forceSave();
    }

    /**
     * Helper method to disable maintenance mode after large updates
     */
    static function disableMaintenanceMode()
    {
        $config = Config::getInstance();
        $config->init();

        $tracker = $config->Tracker;
        $tracker['record_statistics'] = 1;
        $config->Tracker = $tracker;

        $general = $config->General;
        $general['maintenance_mode'] = 0;
        $config->General = $general;

        $config->forceSave();
    }

    public static function deletePluginFromConfigFile($pluginToDelete)
    {
        $config = Config::getInstance();
        $config->init();
        if (isset($config->Plugins['Plugins'])) {
            $plugins = $config->Plugins['Plugins'];
            if (($key = array_search($pluginToDelete, $plugins)) !== false) {
                unset($plugins[$key]);
            }
            $config->Plugins['Plugins'] = $plugins;

            $pluginsInstalled = $config->PluginsInstalled['PluginsInstalled'];
            if (($key = array_search($pluginToDelete, $pluginsInstalled)) !== false) {
                unset($pluginsInstalled[$key]);
            }
            $config->PluginsInstalled = $pluginsInstalled;

            $config->forceSave();
        }
    }
}
