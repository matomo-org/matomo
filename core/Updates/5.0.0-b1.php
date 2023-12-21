<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Db;
use Piwik\Common;
use Piwik\DbHelper;
use Piwik\SettingsPiwik;
use Piwik\Updater;
use Piwik\Updater\Migration\Db as DbAlias;
use Piwik\Updater\Migration\Factory;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Custom as CustomMigration;
use Piwik\Plugins\Goals\Commands\CalculateConversionPages;

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
    private $newIndexName;

    public function __construct(Factory $factory)
    {
        $this->migration = $factory;

        $this->tableName = Common::prefixTable('log_visit');
        $this->indexName = 'index_idsite_idvisitor';
        $this->newIndexName = 'index_idsite_idvisitor_time';
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

    public function getMigrations(Updater $updater)
    {
        $migrations = $this->getUpdateArchiveIndexMigrations();

        $migrations[] = $this->migration->db->addColumns('user_token_auth', ['post_only' => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'"]);
        $migrations[] = $this->migration->db->addColumns('log_conversion', ['pageviews_before' => "SMALLINT UNSIGNED DEFAULT NULL"]);

        $instanceId = SettingsPiwik::getPiwikInstanceId();
        if (strpos($instanceId, '.matomo.cloud') === false && strpos($instanceId, '.innocraft.cloud') === false) {
            $commandString = './console core:calculate-conversion-pages --dates=yesterday,today';
            $populatePagesBefore = new CustomMigration([CalculateConversionPages::class, 'calculateYesterdayAndToday'], $commandString);
            $migrations[] = $populatePagesBefore;
        }

        return $this->appendLogVisitTableMigrations($migrations);
    }

    private function getUpdateArchiveIndexMigrations()
    {
        $migrations = [];

        $tables = ArchiveTableCreator::getTablesArchivesInstalled('numeric');
        foreach ($tables as $table) {
            $migrations[] = $this->migration->db->sql(sprintf('DELETE FROM `%s` WHERE ts_archived is null', $table));

            $hasPrefix = strpos($table, 'archive') !== 0;
            if ($hasPrefix) {
                $table = Common::unprefixTable($table);
            }
            $migrations[] = $this->migration->db->dropIndex($table, 'index_idsite_dates_period');
            $migrations[] = $this->migration->db->addIndex($table, ['idsite', 'date1', 'date2', 'period', 'name(6)'], 'index_idsite_dates_period');
        }

        return $migrations;
    }

    private function appendLogVisitTableMigrations($migrations)
    {
        if ($this->hasNewIndex()) {
            // correct index already exists, so don't perform anything
            return $migrations;
        }

        if ($this->hasCorrectlySetOldIndex()) {
            // already existing index has the correct fields. Try renaming, but ignore syntax error thrown if rename command does not exist
            $migrations[] = $this->migration->db->sql(
                "ALTER TABLE `{$this->tableName}` RENAME INDEX `{$this->indexName}` TO `{$this->newIndexName}`",
                [DbAlias::ERROR_CODE_SYNTAX_ERROR]);
        }

        // create the new index if it does not yet exist and drop the old one
        $migrations[] = $this->migration->db->sql(
            "ALTER TABLE `{$this->tableName}` ADD INDEX `{$this->newIndexName}` (`idsite`, `idvisitor`, `visit_last_action_time` DESC)",
            [DbAlias::ERROR_CODE_DUPLICATE_KEY, DbAlias::ERROR_CODE_KEY_COLUMN_NOT_EXISTS]
        );
        $migrations[] = $this->migration->db->dropIndex('log_visit', $this->indexName);

        return $migrations;
    }

    private function hasCorrectlySetOldIndex(): bool
    {
        $sql = "SHOW INDEX FROM `{$this->tableName}` WHERE Key_name = '{$this->indexName}'";

        $result = Db::fetchAll($sql);

        if (empty($result)) {
            // No index present
            return false;
        }

        // Check that the $result contains all the required column names. This is required as there was a previous index
        // with the same name that only consisted of two columns. We want to check this index is built with all three.
        // $diff will be empty if all three columns are found, meaning that the index already exists.
        $diff = array_diff(['idsite', 'idvisitor', 'visit_last_action_time'], array_column($result, 'Column_name'));

        if (!$diff) {
            return true;
        }

        return false;
    }

    private function hasNewIndex(): bool
    {
        return DbHelper::tableHasIndex($this->tableName, $this->newIndexName);
    }
}
