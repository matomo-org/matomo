<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updater\Migration;

/**
 */
class Updates_0_6_rc1 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $defaultTimezone = 'UTC';
        $defaultCurrency = 'USD';

        return array(
            $this->migration->db->changeColumnType('user', 'date_registered', 'TIMESTAMP NULL'),
            $this->migration->db->changeColumnType('site', 'ts_created', 'TIMESTAMP NULL'),
            $this->migration->db->addColumn('site', 'timezone', 'VARCHAR( 50 ) NOT NULL', 'ts_created'),
            $this->migration->db->sql('UPDATE ' . Common::prefixTable('site') . ' SET `timezone` = "' . $defaultTimezone . '";', Migration\Db::ERROR_CODE_DUPLICATE_COLUMN),
            $this->migration->db->addColumn('site', 'currency', 'CHAR( 3 ) NOT NULL', 'timezone'),
            $this->migration->db->sql('UPDATE ' . Common::prefixTable('site') . ' SET `currency` = "' . $defaultCurrency . '";', Migration\Db::ERROR_CODE_DUPLICATE_COLUMN),
            $this->migration->db->addColumn('site', 'excluded_ips', 'TEXT NOT NULL', 'currency'),
            $this->migration->db->addColumn('site', 'excluded_parameters', 'VARCHAR( 255 ) NOT NULL', 'excluded_ips'),
            $this->migration->db->addIndex('log_visit', array('idsite', 'visit_last_action_time', 'config_md5config(8)'), 'index_idsite_datetime_config'),
            $this->migration->db->addIndex('log_visit', array('idsite', 'idvisit')),
            $this->migration->db->dropIndex('log_conversion', 'index_idsite_date'),
            $this->migration->db->dropColumn('log_conversion', 'visit_server_date'),
            $this->migration->db->addIndex('log_conversion', array('idsite', 'server_time'), 'index_idsite_datetime'),
        );
    }

    public function doUpdate(Updater $updater)
    {
        // first we disable the plugins and keep an array of warnings messages
        $pluginsToDisableMessage = array(
            'SearchEnginePosition' => "SearchEnginePosition plugin was disabled, because it is not compatible with the new Piwik 0.6. \n You can download the latest version of the plugin, compatible with Piwik 0.6.\n<a target='_blank' rel='noopener' href='https://github.com/matomo-org/matomo/issues/502'>Click here.</a>",
            'GeoIP'                => "GeoIP plugin was disabled, because it is not compatible with the new Piwik 0.6. \nYou can download the latest version of the plugin, compatible with Piwik 0.6.\n<a target='_blank' rel='noopener' href='https://github.com/matomo-org/matomo/issues/45'>Click here.</a>"
        );
        $disabledPlugins = array();
        foreach ($pluginsToDisableMessage as $pluginToDisable => $warningMessage) {
            if (\Piwik\Plugin\Manager::getInstance()->isPluginActivated($pluginToDisable)) {
                \Piwik\Plugin\Manager::getInstance()->deactivatePlugin($pluginToDisable);
                $disabledPlugins[] = $warningMessage;
            }
        }

        // Run the SQL
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        // Outputs warning message, pointing users to the plugin download page
        if (!empty($disabledPlugins)) {
            throw new \Exception("The following plugins were disabled during the upgrade:"
                . "<ul><li>" .
                implode('</li><li>', $disabledPlugins) .
                "</li></ul>");
        }
    }
}
