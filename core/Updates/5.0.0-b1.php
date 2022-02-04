<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Db;
use Piwik\Common;
use Piwik\Updater;
use Piwik\Updater\Migration\Db as DbAlias;
use Piwik\Updater\Migration\Factory;
use Piwik\Updates as PiwikUpdates;

/**
 * Update for version 5.0.0-b1
 */
class Updates_5_0_0_b1 extends PiwikUpdates
{
    /**
     * @var Factory
     */
    private $migration;
    private $tableName;
    private $indexName;

    public function __construct(Factory $factory)
    {
        $this->migration = $factory;

        $this->tableName = Common::prefixTable('log_visit');
        $this->indexName = 'index_idsite_idvisitor';
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

    public function getMigrations(Updater $updater)
    {
        if ($this->requiresUpdatedLogVisitTableIndex()) {
            return $this->getLogVisitTableMigrations();
        }

        return [];
    }

    private function getLogVisitTableMigrations()
    {
        $migrations = [];

        $migrations[] = $this->migration->db->dropIndex('log_visit', $this->indexName);

        // Using the custom `sql` method instead of the `addIndex` method as it doesn't support DESC collation
        $migrations[] = $this->migration->db->sql(
            "ALTER TABLE `{$this->tableName}` ADD INDEX `{$this->indexName}` (`idsite`, `idvisitor`, `visit_last_action_time` DESC)",
            [DbAlias::ERROR_CODE_DUPLICATE_KEY, DbAlias::ERROR_CODE_KEY_COLUMN_NOT_EXISTS]
        );

        return $migrations;
    }

    private function requiresUpdatedLogVisitTableIndex()
    {
        $sql = "SHOW INDEX FROM `{$this->tableName}` WHERE Key_name = '{$this->indexName}'";

        $result = Db::fetchAll($sql);

        if (empty($result)) {
            // No index present - should be added
            return true;
        }

        // Check that the $result contains all the required column names. This is required as there was a previous index
        // with the same name that only consisted of two columns. We want to check this index is built with all three.
        // $diff will be empty if all three columns are found, meaning that the index already exists.
        $diff = array_diff(['idsite', 'idvisitor', 'visit_last_action_time'], array_column($result, 'Column_name'));

        if (!$diff) {
            return false;
        }

        return true;
    }
}