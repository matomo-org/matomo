<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns;
use Piwik\Common;
use Piwik\DbHelper;
use Piwik\Plugin\ActionDimension;
use Piwik\Plugin\VisitDimension;
use Piwik\Plugin\Dimension\Conversion as ConversionDimension;
use Piwik\Db;
use Piwik\Updater as PiwikUpdater;

/**
 * Class that handles dimension updates
 */
class Updater extends \Piwik\Updates
{
    /**
     * @var Updater
     */
    private static $updater;

    /**
     * Return SQL to be executed in this update
     *
     * @return array(
     *              'ALTER .... ' => '1234', // if the query fails, it will be ignored if the error code is 1234
     *              'ALTER .... ' => false,  // if an error occurs, the update will stop and fail
     *                                       // and user will have to manually run the query
     *         )
     */
    public static function getSql()
    {
        $sqls = array();

        $changingColumns = self::getUpdates();

        foreach ($changingColumns as $table => $columns) {
            if (empty($columns) || !is_array($columns)) {
                continue;
            }

            $sqls["ALTER TABLE `" . Common::prefixTable($table) . "` " . implode(', ', $columns)] = false;
        }

        return $sqls;
    }

    /**
     * Incremental version update
     */
    public static function update()
    {
        foreach (self::getSql() as $sql => $errorCode) {
            try {
                Db::exec($sql);
            } catch (\Exception $e) {
                if (!Db::get()->isErrNo($e, '1091') && !Db::get()->isErrNo($e, '1060')) {
                    PiwikUpdater::handleQueryError($e, $sql, false, __FILE__);
                }
            }
        }
    }

    public static function setUpdater($updater)
    {
        self::$updater = $updater;
    }

    private static function hasComponentNewVersion($component)
    {
        return empty(self::$updater) || self::$updater->hasNewVersion($component);
    }

    private static function getUpdates()
    {
        $visitColumns      = DbHelper::getTableColumns(Common::prefixTable('log_visit'));
        $actionColumns     = DbHelper::getTableColumns(Common::prefixTable('log_link_visit_action'));
        $conversionColumns = DbHelper::getTableColumns(Common::prefixTable('log_conversion'));

        $changingColumns = array();

        foreach (VisitDimension::getAllDimensions() as $dimension) {
            $column = $dimension->getColumnName();

            if (!self::hasComponentNewVersion('log_visit.' . $column)) {
                continue;
            }

            if (array_key_exists($column, $visitColumns)) {
                $updates = $dimension->update($visitColumns, $conversionColumns);
            } else {
                $updates = $dimension->install();
            }

            $changingColumns = self::mixinUpdates($changingColumns, $updates);
        }

        foreach (ActionDimension::getAllDimensions() as $dimension) {
            $updates         = self::getUpdatesForDimension($dimension, 'log_link_visit_action.', $actionColumns);
            $changingColumns = self::mixinUpdates($changingColumns, $updates);
        }

        foreach (ConversionDimension::getAllDimensions() as $dimension) {
            $updates         = self::getUpdatesForDimension($dimension, 'log_conversion.', $conversionColumns);
            $changingColumns = self::mixinUpdates($changingColumns, $updates);
        }

        return $changingColumns;
    }

    private static function mixinUpdates($changingColumns, $updatesFromDimension)
    {
        if (!empty($updatesFromDimension)) {
            foreach ($updatesFromDimension as $table => $col) {
                if (empty($changingColumns[$table])) {
                    $changingColumns[$table] = $col;
                } else {
                    $changingColumns[$table] = array_merge($changingColumns[$table], $col);
                }
            }
        }

        return $changingColumns;
    }

    /**
     * @param ActionDimension|ConversionDimension $dimension
     * @param string $componentPrefix
     * @param array $existingColumnsInDb
     * @return array
     */
    private static function getUpdatesForDimension($dimension, $componentPrefix, $existingColumnsInDb)
    {
        $column = $dimension->getColumnName();

        if (!self::hasComponentNewVersion($componentPrefix . $column)) {
            return array();
        }

        if (array_key_exists($column, $existingColumnsInDb)) {
            $sqlUpdates = $dimension->update($existingColumnsInDb);
        } else {
            $sqlUpdates = $dimension->install();
        }

        return $sqlUpdates;
    }
}
