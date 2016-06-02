<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;
use Piwik\Updater\Migration;

/**
 * Base class for update scripts.
 *
 * Update scripts perform version updates for Piwik core or individual plugins. They can run
 * SQL queries and/or PHP code to update an environment to a newer version.
 *
 * To create a new update script, create a class that extends `Updates`. Name the class and file
 * after the version, eg, `class Updates_3_0_0` and `3.0.0.php`. Override the {@link getMigrationQueries()}
 * method if you need to run SQL queries. Override the {@link doUpdate()} method to do other types
 * of updating, eg, to activate/deactivate plugins or create files.
 *
 * If you define SQL queries in {@link getMigrationQueries()}, you have to call {@link Updater::executeMigrationQueries()},
 * eg:
 *
 *     public function doUpdate(Updater $updater)
 *     {
 *         $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries());
 *     }
 *
 * @example core/Updates/0.4.2.php
 */
abstract class Updates
{
    /**
     * @deprecated since v2.12.0 use getMigrationQueries() instead. Will be removed in Piwik 4.0.0
     */
    public static function getSql()
    {
        return array();
    }

    /**
     * @deprecated since v2.12.0 use doUpdate() instead. Will be removed in Piwik 4.0.0
     */
    public static function update()
    {
    }

    /**
     * Return migrations to be executed in this update.
     *
     * Migrations should be defined here, instead of in `doUpdate()`, since this method is used to display a preview
     * of which migrations and database queries an update will run. If you execute migrations directly in `doUpdate()`,
     * they won't be displayed to the user.
     *
     * @param Updater $updater
     * @return Migration[]
     * @api
     */
    public function getMigrations(Updater $updater)
    {
        return $this->getMigrationQueries($updater);
    }

    /**
     * Return SQL to be executed in this update.
     *
     * @param Updater $updater
     * @return array
     * @deprecated since Piwik 3.0.0, implement {@link getMigrations()} instead. Will be removed in Piwik 4.0.0
     */
    public function getMigrationQueries(Updater $updater)
    {
        return static::getSql();
    }

    /**
     * Perform the incremental version update.
     *
     * This method should preform all updating logic. If you define migrations in an overridden `getMigrations()`
     * method, you must call {@link Updater::executeMigrations()} here.
     *
     * See {@link \Piwik\Plugins\ExamplePlugin\Updates\Updates_0_0_2} for an example.
     *
     * @param Updater $updater
     * @api
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
     * Enables maintenance mode. Should be used for updates where Piwik will be unavailable
     * for a large amount of time.
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
     * Helper method to disable maintenance mode after large updates.
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
