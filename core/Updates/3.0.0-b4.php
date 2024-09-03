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
use Piwik\Plugins\Installation\ServerFilesGenerator;
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
        $updater->executeMigrations(__FILE__, $this->getUserPasswordMigrations([]));

        ServerFilesGenerator::createFilesForSecurity();
    }

    /**
     * Returns database migrations for this update.
     * @param Migration[] $queries
     * @return Migration[]
     */
    private function getUserDatabaseMigrations($queries)
    {
        $queries[] = $this->migration->db->changeColumn($this->userTable, 'password', 'password', 'VARCHAR(255) NOT NULL');

        return $queries;
    }

    /**
     * Returns migrations to hash existing password with bcrypt.
     * @param Migration[] $queries
     * @return Migration[]
     */
    private function getUserPasswordMigrations($queries)
    {
        $db        = Db::get();
        $userTable = Common::prefixTable($this->userTable);

        $users = $db->fetchAll(
            'SELECT `login`, `password` FROM `' . $userTable . '` WHERE LENGTH(`password`) = 32'
        );

        foreach ($users as $user) {
            $queries[] = $this->migration->db->boundSql(
                'UPDATE `' . $userTable . '`'
                . ' SET `password` = ?'
                . ' WHERE `login` = ?',
                [
                    password_hash($user['password'], PASSWORD_BCRYPT),
                    $user['login'],
                ]
            );
        }

        return $queries;
    }
}
