<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\DbHelper;
use Piwik\Option;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 2.14.2.
 */
class Updates_2_14_2 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * Removes option entries for columns that are marked as installed but are actually no longer installed due to
     * a bug in previous versions where the option entries were not correctly removed.
     *
     * @param Updater $updater
     * @return array
     */
    public function getMigrations(Updater $updater)
    {
        $visitMigrations = $this->getMigrationsThatRemoveOptionEntriesOfNotActuallyInstalledColumns(VisitDimension::INSTALLER_PREFIX, 'log_visit');
        $actionMigrations = $this->getMigrationsThatRemoveOptionEntriesOfNotActuallyInstalledColumns(ActionDimension::INSTALLER_PREFIX, 'log_link_visit_action');
        $conversionMigrations = $this->getMigrationsThatRemoveOptionEntriesOfNotActuallyInstalledColumns(ConversionDimension::INSTALLER_PREFIX, 'log_conversion');

        $migrations = array();

        foreach ($visitMigrations as $migration) {
            $migrations[] = $migration;
        }

        foreach ($actionMigrations as $migration) {
            $migrations[] = $migration;
        }

        foreach ($conversionMigrations as $migration) {
            $migrations[] = $migration;
        }

        return $migrations;
    }

    private function getMigrationsThatRemoveOptionEntriesOfNotActuallyInstalledColumns($dimensionPrefix, $tableName)
    {
        $componentPrefix = 'version_' . $dimensionPrefix;

        $notActuallyInstalledColumns = self::getNotActuallyInstalledColumnNames($componentPrefix, $tableName);

        $sqls = array();
        foreach ($notActuallyInstalledColumns as $column) {
            $sqls[] = $this->buildRemoveOptionEntrySql($componentPrefix . $column);
        }

        return $sqls;
    }

    private function buildRemoveOptionEntrySql($optionName)
    {
        $tableName = Common::prefixTable('option');

        $sql = sprintf("DELETE FROM `%s` WHERE `option_name` = ?", $tableName);

        return $this->migration->db->boundSql($sql, array($optionName));
    }

    /**
     * @param string $componentPrefix
     * @param string $tableName
     * @return array An array of columns that are marked as installed but are actually removed. There was a bug
     *               where option entries were not correctly removed. eg array('idvist', 'server_time', ...)
     */
    private static function getNotActuallyInstalledColumnNames($componentPrefix, $tableName)
    {
        $installedVisitColumns = self::getMarkedAsInstalledColumns($componentPrefix);
        $existingVisitColumns  = self::getActuallyExistingColumns($tableName);

        return array_diff($installedVisitColumns, $existingVisitColumns);
    }

    /**
     * @param  string $componentPrefix eg 'version_log_visit.'
     * @return array An array of column names that are marked as installed. eg array('idvist', 'server_time', ...)
     */
    private static function getMarkedAsInstalledColumns($componentPrefix)
    {
        $installedVisitColumns = Option::getLike($componentPrefix . '%');
        $installedVisitColumns = array_keys($installedVisitColumns);
        $installedVisitColumns = array_map(function ($entry) use ($componentPrefix) {
            return str_replace($componentPrefix, '', $entry);
        }, $installedVisitColumns);

        return $installedVisitColumns;
    }

    /**
     * @param string $tableName
     * @return array  An array of actually existing column names in the given table. eg array('idvist', 'server_time', ...)
     */
    private static function getActuallyExistingColumns($tableName)
    {
        $tableName = Common::prefixTable($tableName);
        return array_keys(DbHelper::getTableColumns($tableName));
    }

    /**
     * @param Updater $updater
     */
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
