<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Db;
use Piwik\Option;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_2_9_0_b1 extends Updates
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
        $migrations = array();
        $migrations = $this->updateBrowserEngine($migrations);
        $migrations[] = $this->migration->plugin->activate('TestRunner');

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        self::updateIPAnonymizationSettings();
    }

    private function updateBrowserEngine($sql)
    {
        $sql[] = $this->migration->db->addColumn('log_visit', 'config_browser_engine', 'VARCHAR(10) NOT NULL');

        $browserEngineMatch = array(
            'Trident' => array('IE'),
            'Gecko' => array('NS', 'PX', 'FF', 'FB', 'CA', 'GA', 'KM', 'MO', 'SM', 'CO', 'FE', 'KP', 'KZ', 'TB'),
            'KHTML' => array('KO'),
            'WebKit' => array('SF', 'CH', 'OW', 'AR', 'EP', 'FL', 'WO', 'AB', 'IR', 'CS', 'FD', 'HA', 'MI', 'GE', 'DF', 'BB', 'BP', 'TI', 'CF', 'RK', 'B2', 'NF'),
            'Presto' => array('OP'),
        );

        // Update visits, fill in now missing engine
        $engineUpdate = "''";
        $ifFragment = "IF (`config_browser_name` IN ('%s'), '%s', %s)";

        foreach ($browserEngineMatch as $engine => $browsers) {
            $engineUpdate = sprintf($ifFragment, implode("','", $browsers), $engine, $engineUpdate);
        }

        $engineUpdate = sprintf("UPDATE %s SET `config_browser_engine` = %s", Common::prefixTable('log_visit'), $engineUpdate);
        $sql[] = $this->migration->db->sql($engineUpdate);

        $archiveBlobTables = Db::get()->fetchCol("SHOW TABLES LIKE '%archive_blob%'");

        // for each blob archive table, rename UserSettings_browserType to DevicesDetection_browserEngines
        foreach ($archiveBlobTables as $table) {
            // try to rename old archives
            $query = sprintf("UPDATE IGNORE %s SET name='DevicesDetection_browserEngines' WHERE name = 'UserSettings_browserType'", $table);
            $sql[] = $this->migration->db->sql($query);
        }

        return $sql;
    }

    private static function updateIPAnonymizationSettings()
    {
        $optionName = 'PrivacyManager.ipAnonymizerEnabled';

        $value = Option::get($optionName);

        if ($value !== false) {
            // If the config is defined, nothing to do
            return;
        }

        // We disable IP anonymization if it wasn't configured (because by default it has gone from disabled to enabled)
        Option::set($optionName, '0');
    }
}
