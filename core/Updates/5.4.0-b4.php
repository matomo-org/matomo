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
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 5.4.0-b4
 */
class Updates_5_4_0_b4 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    /**
     * @var string
     */
    private $userTable;

    /**
     * @var string
     */
    private $optionTable;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
        $this->userTable = Common::prefixTable('user');
        $this->optionTable = Common::prefixTable('option');
    }

    /**
     * @param Updater $updater
     *
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        return [
            $this->migration->db->addColumns('user', ['ts_last_seen' => 'TIMESTAMP null DEFAULT null']),
            $this->migration->db->addColumns('user', ['ts_inactivity_notified' => 'TIMESTAMP null DEFAULT null']),
            $this->migration->db->sql($this->migrateLastLoginFromOptionsTableSql()),
            $this->migration->db->sql($this->removeLegacyValuesFromOptionsTableSql()),
        ];
    }

    private function migrateLastLoginFromOptionsTableSql(): string
    {
        return sprintf(
            "UPDATE `%s` u JOIN (
                SELECT 
                    SUBSTRING_INDEX(o.option_name, 'UsersManager.lastSeen.', -1) AS login,
                    o.option_value AS last_seen
                FROM `%s` o
                WHERE o.option_name LIKE 'UsersManager.lastSeen.%%'
            ) AS userData
            ON u.login = userData.login
            SET u.ts_last_seen = CONVERT_TZ(FROM_UNIXTIME(userData.last_seen), 'SYSTEM', '+00:00')",
            $this->userTable,
            $this->optionTable
        );
    }

    private function removeLegacyValuesFromOptionsTableSql(): string
    {
        return sprintf("DELETE FROM `%s` WHERE option_name LIKE 'UsersManager.lastSeen.%%'", $this->optionTable);
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
