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
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

/**
 * Adds the table core:archive records failed archiving jobs in, so they are retried with a delay, and an index to
 * skip the invalidations put on hold because of them.
 */
class Updates_6_1_0_b2 extends Updates
{
    private MigrationFactory $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        // name and report are compared with archive_invalidations, so they need the same collation
        $collations = $this->getInvalidationColumnCollations();

        return [
            $this->migration->db->createTable('archive_failures', [
                'idfailure' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
                'idsite' => 'INTEGER UNSIGNED NOT NULL',
                'name' => 'VARCHAR(255)' . ($collations['name'] ?? '') . ' NOT NULL',
                'period' => 'TINYINT UNSIGNED NOT NULL',
                'date1' => 'DATE NOT NULL',
                'date2' => 'DATE NOT NULL',
                'report' => 'VARCHAR(255)' . ($collations['report'] ?? '') . " NOT NULL DEFAULT ''",
                'archive_hash' => 'CHAR(32) NOT NULL',
                'attempts' => 'SMALLINT UNSIGNED NOT NULL DEFAULT 0',
                'last_error' => 'TEXT NULL',
                'ts_first_failed' => 'DATETIME NOT NULL',
                'ts_last_failed' => 'DATETIME NOT NULL',
                'ts_retry_after' => 'DATETIME NOT NULL',
            ], 'idfailure'),
            $this->migration->db->addUniqueKey(
                'archive_failures',
                ['idsite', 'archive_hash', 'period', 'date1', 'date2'],
                'unique_archive'
            ),
            // lets the archiving queue skip invalidations on hold without reading them
            $this->migration->db->addIndex(
                'archive_invalidations',
                ['idsite', 'status', 'date1', 'period'],
                'index_idsite_status_date1_period'
            ),
        ];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

    /**
     * @return array<string, string> column name => " CHARACTER SET ... COLLATE ..."
     */
    private function getInvalidationColumnCollations(): array
    {
        try {
            $columns = Db::fetchAll(
                "SELECT COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME IN ('name', 'report')",
                [Common::prefixTable('archive_invalidations')]
            );
        } catch (\Exception $e) {
            return [];
        }

        $collations = [];
        foreach ($columns as $column) {
            $charset = $column['CHARACTER_SET_NAME'] ?? '';
            $collation = $column['COLLATION_NAME'] ?? '';

            if (preg_match('/^\w+$/', $charset) && preg_match('/^\w+$/', $collation)) {
                $collations[$column['COLUMN_NAME']] = " CHARACTER SET $charset COLLATE $collation";
            }
        }

        return $collations;
    }
}
