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
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
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
            $collation = $this->detectCollationForMigration();

            if (null !== $collation) {
                $migrations[] = $this->migration->config->set(
                    'database',
                    'collation',
                    $collation
                );
            }
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

    private function detectCollationForMigration(): ?string
    {
        try {
            $db = Db::get();

            $metadataProvider = StaticContainer::get('Piwik\Plugins\DBStats\MySQLMetadataProvider');
            $userTableStatus = $metadataProvider->getTableStatus('user');
            if (empty($userTableStatus['Collation'] ?? null)) {
                // if there is no user table, or no collation for it, abort detection
                // this table should always exist and something must be wrong in this case
                return null;
            }

            $userTableCollation = $userTableStatus['Collation'];
            $connectionCollation = $db->fetchOne('SELECT @@collation_connection');

            if ($userTableCollation === $connectionCollation) {
                // if the connection is matching the user table
                // we should be safe to assume we have already found a config value
                return $userTableCollation;
            }

            $archiveTable = ArchiveTableCreator::getLatestArchiveTableInstalled(ArchiveTableCreator::NUMERIC_TABLE);
            if (null === $archiveTable) {
                return null;
            }

            $archiveTableStatus = $metadataProvider->getTableStatus(Common::unprefixTable($archiveTable));

            if (
                !empty($archiveTableStatus['Collation'])
                && $archiveTableStatus['Collation'] === $userTableCollation
            ) {
                // the most recent numeric archive table is matching the collation
                // of the users table, should be a good config value to choose
                return $userTableCollation;
            }
        } catch (\Exception $e) {
            // rely on the system check if detection failed
        }

        return null;
    }
}
