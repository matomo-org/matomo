<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Config;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_0_6_3 extends Updates
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
        return array(
            $this->migration->db->changeColumnType('log_visit', 'location_ip', 'INT UNSIGNED NOT NULL'),
            $this->migration->db->changeColumnType('logger_api_call', 'caller_ip', 'INT UNSIGNED')->addErrorCodeToIgnore(Updater\Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS),
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

        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
