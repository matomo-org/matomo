<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_0_6_rc1 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        $defaultTimezone = 'UTC';
        $defaultCurrency = 'USD';
        return array(
            'ALTER TABLE ' . Common::prefixTable('user') . ' CHANGE date_registered date_registered TIMESTAMP NULL'                                                                => 1054,
            'ALTER TABLE ' . Common::prefixTable('site') . ' CHANGE ts_created ts_created TIMESTAMP NULL'                                                                          => 1054,
            'ALTER TABLE ' . Common::prefixTable('site') . ' ADD `timezone` VARCHAR( 50 ) NOT NULL AFTER `ts_created` ;'                                                           => 1060,
            'UPDATE ' . Common::prefixTable('site') . ' SET `timezone` = "' . $defaultTimezone . '";'                                                                              => 1060,
            'ALTER TABLE ' . Common::prefixTable('site') . ' ADD currency CHAR( 3 ) NOT NULL AFTER `timezone` ;'                                                                   => 1060,
            'UPDATE ' . Common::prefixTable('site') . ' SET `currency` = "' . $defaultCurrency . '";'                                                                              => 1060,
            'ALTER TABLE ' . Common::prefixTable('site') . ' ADD `excluded_ips` TEXT NOT NULL AFTER `currency` ;'                                                                  => 1060,
            'ALTER TABLE ' . Common::prefixTable('site') . ' ADD excluded_parameters VARCHAR( 255 ) NOT NULL AFTER `excluded_ips` ;'                                               => 1060,
            'ALTER TABLE ' . Common::prefixTable('log_visit') . ' ADD INDEX `index_idsite_datetime_config`  ( `idsite` , `visit_last_action_time`  , `config_md5config` ( 8 ) ) ;' => array(1061, 1072),
            'ALTER TABLE ' . Common::prefixTable('log_visit') . ' ADD INDEX index_idsite_idvisit (idsite, idvisit) ;'                                                              => array(1061, 1072),
            'ALTER TABLE ' . Common::prefixTable('log_conversion') . ' DROP INDEX index_idsite_date'                                                                               => 1091,
            'ALTER TABLE ' . Common::prefixTable('log_conversion') . ' DROP visit_server_date;'                                                                                    => 1091,
            'ALTER TABLE ' . Common::prefixTable('log_conversion') . ' ADD INDEX index_idsite_datetime ( `idsite` , `server_time` )'                                               => array(1072, 1061),
        );
    }

    public function doUpdate(Updater $updater)
    {
        // first we disable the plugins and keep an array of warnings messages
        $pluginsToDisableMessage = array(
            'SearchEnginePosition' => "SearchEnginePosition plugin was disabled, because it is not compatible with the new Piwik 0.6. \n You can download the latest version of the plugin, compatible with Piwik 0.6.\n<a target='_blank' href='?module=Proxy&action=redirect&url=https://github.com/piwik/piwik/issues/502'>Click here.</a>",
            'GeoIP'                => "GeoIP plugin was disabled, because it is not compatible with the new Piwik 0.6. \nYou can download the latest version of the plugin, compatible with Piwik 0.6.\n<a target='_blank' href='?module=Proxy&action=redirect&url=https://github.com/piwik/piwik/issues/45'>Click here.</a>"
        );
        $disabledPlugins = array();
        foreach ($pluginsToDisableMessage as $pluginToDisable => $warningMessage) {
            if (\Piwik\Plugin\Manager::getInstance()->isPluginActivated($pluginToDisable)) {
                \Piwik\Plugin\Manager::getInstance()->deactivatePlugin($pluginToDisable);
                $disabledPlugins[] = $warningMessage;
            }
        }

        // Run the SQL
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));

        // Outputs warning message, pointing users to the plugin download page
        if (!empty($disabledPlugins)) {
            throw new \Exception("The following plugins were disabled during the upgrade:"
                . "<ul><li>" .
                implode('</li><li>', $disabledPlugins) .
                "</li></ul>");
        }
    }
}
