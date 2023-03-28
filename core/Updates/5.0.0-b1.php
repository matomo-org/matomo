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
use Piwik\Date;
use Piwik\Common;
use Piwik\Plugins\Goals\PagesBeforeCalculator;
use Piwik\SettingsPiwik;
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

        $this->populatePagesBefore();
    }

    public function getMigrations(Updater $updater)
    {
        $migrations = $this->getUpdateArchiveIndexMigrations();

        $migrations[] = $this->migration->db->addColumns('user_token_auth', ['post_only' => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'"]);

        $migrations[] = $this->getPagesBeforeAddColumn();

        if ($this->requiresUpdatedLogVisitTableIndex()) {
            return $this->getLogVisitTableMigrations($migrations);
        }

        return $migrations;
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

    private function getLogVisitTableMigrations($migrations)
    {
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

    /**
     * Add new 'pages before column' to log conversions
     *
     * @return DbAlias\AddColumns
     */
    private function getPagesBeforeAddColumn()
    {
        return $this->migration->db->addColumns('log_conversion', ['pageviews_before' => "SMALLINT UNSIGNED DEFAULT NULL"]);
    }

    private function populatePagesBefore(): void
    {
        // Abort if host is *.matomo.cloud
        $piwikUrl = SettingsPiwik::getPiwikUrl();
        if (strpos($piwikUrl, '.matomo.cloud') !== false) {
            return;
        }

        // Abort if there are more than 10,000 conversions across all sites and goals in the last 48hrs
        $startDate = Date::factory('yesterday');
        $endDate = Date::factory('today')->getEndOfDay();

        $sql = 'SELECT COUNT(*) FROM ' . Common::prefixTable('log_conversion') . ' WHERE server_time <= ? AND server_time >= ?';

        $result = Db::fetchOne($sql, [$startDate, $endDate]);
        if ($result > 10000) {
            return;
        }

        // Calculate all conversions for the last 48hrs
        $pagesBeforeCalculator = new PagesBeforeCalculator();
        $pagesBeforeCalculator->calculateFor($startDate, $endDate);

    }
}
