<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

/**
 * Abstract class for update scripts
 *
 * @example core/Updates/0.4.2.php
 */
abstract class Updates
{
    /**
     * @deprecated since v2.12.0 use getMigrationQueries() instead
     */
    public static function getSql()
    {
        return array();
    }

    /**
     * @deprecated since v2.12.0 use doUpdate() instead
     */
    public static function update()
    {
    }

    /**
     * Return SQL to be executed in this update
     *
     * @return array(
     *              'ALTER .... ' => '1234', // if the query fails, it will be ignored if the error code is 1234
     *              'ALTER .... ' => false,  // if an error occurs, the update will stop and fail
     *                                       // and user will have to manually run the query
     *         )
     */
    public function getMigrationQueries(Updater $updater)
    {
        return static::getSql();
    }

    /**
     * Incremental version update
     */
    public function doUpdate(Updater $updater)
    {
        static::update();
    }

    /**
     * Tell the updater that this is a major update.
     * Leads to a more visible notice.
     *
     * NOTE to release manager: Remember to mention in the Changelog
     * that this update contains major DB upgrades and will take some time!
     *
     * @return bool
     */
    public static function isMajorUpdate()
    {
        return false;
    }

    /**
     * Helper method to enable maintenance mode during large updates
     */
    public static function enableMaintenanceMode()
    {
        $config = Config::getInstance();

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
    public static function disableMaintenanceMode()
    {
        $config = Config::getInstance();

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
            $config->PluginsInstalled = array('PluginsInstalled' => $pluginsInstalled);

            $config->forceSave();
        }
    }
}
