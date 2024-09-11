<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

class Updates_5_2_0_b2 extends Updates
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
        $migrations = [];

        $config = Config::getInstance();
        $dbConfig = $config->database;

        // only run migration if config is not set
        if (empty($dbConfig['collation'])) {
            try {
                $db = Db::get();
                $userTable = Common::prefixTable('user');
                $userTableStatus = $db->fetchRow('SHOW TABLE STATUS WHERE Name = ?', [$userTable]);
                $connectionCollation = $db->fetchOne('SELECT @@collation_connection');

                // only update config if user table and connection report same collation
                if (
                    !empty($userTableStatus['Collation'])
                    && !empty($connectionCollation)
                    && $userTableStatus['Collation'] === $connectionCollation
                ) {
                    $migrations[] = $this->migration->config->set(
                        'database',
                        'collation',
                        $connectionCollation
                    );
                }
            } catch (\Exception $e) {
                // rely on the system check if detection failed
            }
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
