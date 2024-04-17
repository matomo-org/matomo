<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Db;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_2_9_0_b7 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    private $sequenceTable = 'sequence';

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $migrations = array();
        $migrations = $this->addCreateSequenceTableQuery($migrations);
        $migrations = $this->addArchivingIdMigrationQueries($migrations);

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

    private function addArchivingIdMigrationQueries($sql)
    {
        $tables = ArchiveTableCreator::getTablesArchivesInstalled();

        foreach ($tables as $table) {
            $type = ArchiveTableCreator::getTypeFromTableName($table);

            if ($type === ArchiveTableCreator::NUMERIC_TABLE) {
                $maxId = Db::fetchOne('SELECT MAX(idarchive) FROM ' . $table);

                if (!empty($maxId)) {
                    $maxId = (int) $maxId + 500;
                } else {
                    $maxId = 1;
                }

                $sql[] = $this->migration->db->insert($this->sequenceTable, array('name' => $table, 'value' => $maxId));
            }
        }

        return $sql;
    }

    /**
     * @return string
     */
    private function addCreateSequenceTableQuery($sql)
    {
        $sql[] = $this->migration->db->createTable($this->sequenceTable, array(
           'name' => 'VARCHAR(120) NOT NULL',
           'value' => 'BIGINT(20) UNSIGNED NOT NULL',
        ), $primary = 'name');

        return $sql;
    }
}
