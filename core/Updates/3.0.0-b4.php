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
use Piwik\Db;
use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

class Updates_3_0_0_b4 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    /**
     * @var string
     */
    private $userTable = 'user';

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * Here you can define one or multiple SQL statements that should be executed during the update.
     * @param Updater $updater
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        $migrations = [];
        $migrations = $this->getUserDatabaseMigrations($migrations);

        return $migrations;
    }


    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

      /**
     * @param Migration[] $queries
     * @return Migration[]
     */
    private function getUserDatabaseMigrations($queries)
    {
        $queries[] = $this->migration->db->changeColumn($this->userTable, 'password', 'password', 'VARCHAR(255) NOT NULL');

        return $queries;
    }
}
