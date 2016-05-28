<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugin\Manager;
use Piwik\Updater;
use Piwik\Updates;

class Updates_2_9_0_b1 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        $sql = array();
        $sql = self::updateBrowserEngine($sql);

        return $sql;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));

        self::updateIPAnonymizationSettings();

        try {
            Manager::getInstance()->activatePlugin('TestRunner');
        } catch (\Exception $e) {
        }
    }

    private static function updateBrowserEngine($sql)
    {
        $sql[sprintf("ALTER TABLE `%s` ADD COLUMN `config_browser_engine` VARCHAR(10) NOT NULL", Common::prefixTable('log_visit'))] = 1060;

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
        $sql[$engineUpdate] = false;

        $archiveBlobTables = Db::get()->fetchCol("SHOW TABLES LIKE '%archive_blob%'");

        // for each blob archive table, rename UserSettings_browserType to DevicesDetection_browserEngines
        foreach ($archiveBlobTables as $table) {

            // try to rename old archives
            $sql[sprintf("UPDATE IGNORE %s SET name='DevicesDetection_browserEngines' WHERE name = 'UserSettings_browserType'", $table)] = false;
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
