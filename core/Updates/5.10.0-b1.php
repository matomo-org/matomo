<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\DataAccess\ArchiveBlobColumnType;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

/**
 * Sets the `[database] archive_blob_tables_may_contain_mediumblob` config flag on installs
 * whose archive_blob tables still use a MEDIUMBLOB `value` column.
 *
 * Fresh installs (all tables are LONGBLOB) receive no migration so the fast-path short-circuit
 * in {@see \Piwik\ArchiveProcessor\ArchiveBlobRowCap} costs nothing at runtime.
 */
class Updates_5_10_0_b1 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater): array
    {
        $mediumBlobTables = ArchiveBlobColumnType::getMediumBlobArchiveTables();
        if (empty($mediumBlobTables)) {
            return [];
        }

        return [
            $this->migration->config->set('database', 'archive_blob_tables_may_contain_mediumblob', '1'),
        ];
    }

    public function doUpdate(Updater $updater): void
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
