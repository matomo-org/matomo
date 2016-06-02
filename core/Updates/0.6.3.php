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
use Piwik\Config;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_0_6_3 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('log_visit') . '`
				CHANGE `location_ip` `location_ip` INT UNSIGNED NOT NULL'                   => 1054,
            'ALTER TABLE `' . Common::prefixTable('logger_api_call') . '`
				CHANGE `caller_ip` `caller_ip` INT UNSIGNED'                                => array(1054, 1146),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $config = Config::getInstance();
        $dbInfos = $config->database;
        if (!isset($dbInfos['schema'])) {
            try {
                if (is_writable(Config::getLocalConfigPath())) {
                    $config->database = $dbInfos;
                    $config->forceSave();
                } else {
                    throw new \Exception('mandatory update failed');
                }
            } catch (\Exception $e) {
            }
        }

        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
