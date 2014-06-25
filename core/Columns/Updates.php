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
use Piwik\Db;
use Piwik\Updater;

/**
 * Class that handles dimension updates
 */
class Updates extends \Piwik\Updates
{
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
            $sqls["ALTER TABLE `" . $table . "` " . implode(', ', $columns)] = false;
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
                    Updater::handleQueryError($e, $sql, false, __FILE__);
                }
            }
        }
    }

    private static function getUpdates()
    {
        $visitColumns      = DbHelper::getTableColumns(Common::prefixTable('log_visit'));
        $actionColumns     = DbHelper::getTableColumns(Common::prefixTable('log_link_visit_action'));
        $conversionColumns = DbHelper::getTableColumns(Common::prefixTable('log_conversion'));

        $changingColumns = array();

        foreach (VisitDimension::getAllDimensions() as $dimension) {
            $columns = $dimension->install($visitColumns, $conversionColumns);
            if (!empty($columns)) {
                foreach ($columns as $table => $col) {
                    if (empty($changingColumns[$table])) {
                        $changingColumns[$table] = $col;
                    } else {
                        $changingColumns[$table] = array_merge($changingColumns[$table], $col);
                    }
                }
            }
        }

        foreach (ActionDimension::getAllDimensions() as $dimension) {
            $columns = $dimension->install($actionColumns);
            if (!empty($columns)) {
                foreach ($columns as $table => $col) {
                    if (empty($changingColumns[$table])) {
                        $changingColumns[$table] = $col;
                    } else {
                        $changingColumns[$table] = array_merge($changingColumns[$table], $col);
                    }
                }
            }
        }

        return $changingColumns;
    }

    public static function hasUpdates()
    {
        $changingColumns = self::getUpdates();

        foreach ($changingColumns as $table => $columns) {
            if (!empty($columns)) {
                return true;
            }
        }

        return false;
    }

}
