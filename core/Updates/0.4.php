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
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_0_4 extends Updates
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
            // 0.4 [1140]
            $this->migration->db->sql('UPDATE `' . Common::prefixTable('log_visit') . '`
                SET location_ip=location_ip+CAST(POW(2,32) AS UNSIGNED) WHERE location_ip < 0'),
            $this->migration->db->changeColumnType('log_visit', 'location_ip', 'BIGINT UNSIGNED NOT NULL'),
            $this->migration->db->sql('UPDATE `' . Common::prefixTable('logger_api_call') . '`
                SET caller_ip=caller_ip+CAST(POW(2,32) AS UNSIGNED) WHERE caller_ip < 0', Updater\Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS),
            $this->migration->db->changeColumnType('logger_api_call', 'caller_ip', 'BIGINT UNSIGNED')->addErrorCodeToIgnore(Updater\Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
