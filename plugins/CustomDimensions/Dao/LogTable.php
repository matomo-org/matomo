<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomDimensions\Dao;

use Piwik\Common;
use Piwik\DataAccess\TableMetadata;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Exception;

class LogTable
{
    const DEFAULT_CUSTOM_DIMENSION_COUNT = 5;

    private $scope = null;
    private $table = null;

    public function __construct($scope)
    {
        $this->scope = $scope;
        $this->table = Common::prefixTable($this->getTableNameFromScope($scope));
    }

    private function getTableNameFromScope($scope)
    {
        // actually we should have a class for each scope but don't want to overengineer it for now
        switch ($scope) {
            case CustomDimensions::SCOPE_ACTION:
                return 'log_link_visit_action';
            case CustomDimensions::SCOPE_VISIT:
                return 'log_visit';
            case CustomDimensions::SCOPE_CONVERSION:
                return 'log_conversion';
            default:
                throw new Exception('Unsupported scope ' . $scope);
        }
    }

    /**
     * @see getHighestCustomDimensionIndex()
     * @return int
     */
    public function getNumInstalledIndexes()
    {
        $indexes = $this->getInstalledIndexes();

        return count($indexes);
    }

    public function getInstalledIndexes()
    {
        $columns = $this->getCustomDimensionColumnNames();

        if (empty($columns)) {
            return array();
        }

        $indexes = array_map(function ($column) {
            $onlyNumber = str_replace('custom_dimension_', '', $column);

            if (is_numeric($onlyNumber)) {
                return (int) $onlyNumber;
            }
        }, $columns);

        return array_values(array_unique($indexes));
    }

    private function getCustomDimensionColumnNames()
    {
        $tableMetadataAccess = new TableMetadata();
        $columns = $tableMetadataAccess->getColumns($this->table);

        $dimensionColumns = array_filter($columns, function ($column) {
            return LogTable::isCustomDimensionColumn($column);
        });

        return $dimensionColumns;
    }

    public static function isCustomDimensionColumn($column)
    {
        return (bool) preg_match('/^custom_dimension_(\d+)$/', '' . $column);
    }

    public static function buildCustomDimensionColumnName($indexOrDimension)
    {
        if (is_array($indexOrDimension) && isset($indexOrDimension['index'])) {
            $indexOrDimension = $indexOrDimension['index'];
        }

        $indexOrDimension = (int) $indexOrDimension;

        if ($indexOrDimension >= 1) {
            return 'custom_dimension_' . (int) $indexOrDimension;
        }
    }

    public function removeCustomDimension($index)
    {
        if ($index < 1) {
            return;
        }

        $field = self::buildCustomDimensionColumnName($index);

        $this->dropColumn($field);
    }

    public function addManyCustomDimensions($count, $extraAlter = null)
    {
        if ($count < 0) {
            return;
        }

        $indexes = $this->getInstalledIndexes();

        if (empty($indexes)) {
            $highestIndex = 0;
        } else {
            $highestIndex = max($indexes);
        }

        $total = $highestIndex + $count;

        $queries = array();

        if (isset($extraAlter)) {
            // we make sure to install needed tracker request processor columns first, before installing custom dimensions
            // if something fails custom dimensions can be added later any time
            $queries[] = $extraAlter;
        }

        for ($index = $highestIndex; $index < $total; $index++) {
            $queries[] = $this->getAddColumnQueryToAddCustomDimension($index + 1);
        }

        if (!empty($queries)) {
            $sql = 'ALTER TABLE ' . $this->table . ' ' . implode(', ', $queries) . ';';
            Db::exec($sql);
        }
    }

    private function getAddColumnQueryToAddCustomDimension($index)
    {
        $field = self::buildCustomDimensionColumnName($index);

        return sprintf('ADD COLUMN %s VARCHAR(255) DEFAULT NULL', $field);
    }

    public function install()
    {
        $numDimensionsInstalled = $this->getNumInstalledIndexes();
        $numDimensionsToAdd = self::DEFAULT_CUSTOM_DIMENSION_COUNT - $numDimensionsInstalled;

        $query = null;
        if ($this->scope === CustomDimensions::SCOPE_VISIT && !$this->hasColumn('last_idlink_va')) {
            $query = 'ADD COLUMN last_idlink_va BIGINT UNSIGNED DEFAULT NULL';
        } elseif ($this->scope === CustomDimensions::SCOPE_ACTION && !$this->hasColumn('time_spent')) {
            $query = 'ADD COLUMN time_spent INT UNSIGNED DEFAULT NULL';
        }

        $this->addManyCustomDimensions($numDimensionsToAdd, $query);
    }

    public function uninstall()
    {
        foreach ($this->getInstalledIndexes() as $index) {
            $this->removeCustomDimension($index);
        }

        if ($this->scope === CustomDimensions::SCOPE_VISIT) {
            $this->dropColumn('last_idlink_va');
        } elseif ($this->scope === CustomDimensions::SCOPE_ACTION) {
            $this->dropColumn('time_spent');
        }
    }

    private function hasColumn($field)
    {
        $columns = DbHelper::getTableColumns($this->table);
        return array_key_exists($field, $columns);
    }

    private function dropColumn($field)
    {
        if ($this->hasColumn($field)) {
            $sql = sprintf('ALTER TABLE %s DROP COLUMN %s;', $this->table, $field);
            Db::exec($sql);
        }
    }

}

