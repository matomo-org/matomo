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
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Db;
use Piwik\Updater as PiwikUpdater;
use Piwik\Cache\PersistentCache;
use Piwik\Filesystem;

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
            $updates         = self::getUpdatesForDimension($dimension, 'log_visit.', $visitColumns, $conversionColumns);
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

    /**
     * @param ActionDimension|ConversionDimension|VisitDimension $dimension
     * @param string $componentPrefix
     * @param array $existingColumnsInDb
     * @param array $conversionColumns
     * @return array
     */
    private static function getUpdatesForDimension($dimension, $componentPrefix, $existingColumnsInDb, $conversionColumns = array())
    {
        $column = $dimension->getColumnName();
        $componentName = $componentPrefix . $column;

        if (!self::hasComponentNewVersion($componentName)) {
            return array();
        }

        if (array_key_exists($column, $existingColumnsInDb)) {
            if ($dimension instanceof VisitDimension) {
                $sqlUpdates = $dimension->update($conversionColumns);
            } else {
                $sqlUpdates = $dimension->update();
            }
        } else {
            $sqlUpdates = $dimension->install();
        }

        return $sqlUpdates;
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

    public static function getAllVersions()
    {
        // to avoid having to load all dimensions on each request we check if there were any changes on the file system
        // can easily save > 100ms for each request
        $cachedTimes  = self::getCachedDimensionFileChanges();
        $currentTimes = self::getCurrentDimensionFileChanges();
        $diff         = array_diff_assoc($currentTimes, $cachedTimes);

        if (empty($diff)) {
            return array();
        }

        $versions = array();

        $visitColumns      = DbHelper::getTableColumns(Common::prefixTable('log_visit'));
        $actionColumns     = DbHelper::getTableColumns(Common::prefixTable('log_link_visit_action'));
        $conversionColumns = DbHelper::getTableColumns(Common::prefixTable('log_conversion'));

        foreach (VisitDimension::getAllDimensions() as $dimension) {
            $versions = self::mixinVersions($dimension, 'log_visit.', $visitColumns, $versions);
        }

        foreach (ActionDimension::getAllDimensions() as $dimension) {
            $versions = self::mixinVersions($dimension, 'log_link_visit_action.', $actionColumns, $versions);
        }

        foreach (ConversionDimension::getAllDimensions() as $dimension) {
            $versions = self::mixinVersions($dimension, 'log_conversion.', $conversionColumns, $versions);
        }

        return $versions;
    }

    /**
     * @param ActionDimension|ConversionDimension|VisitDimension $dimension
     * @param string $componentPrefix
     * @param array $columns
     * @param array $versions
     * @return array The modified versions array
     */
    private static function mixinVersions($dimension, $componentPrefix, $columns, $versions)
    {
        $columnName = $dimension->getColumnName();

        if (!$columnName || !$dimension->hasColumnType()) {
            return $versions;
        }

        $component = $componentPrefix . $columnName;
        $version   = $dimension->getVersion();

        if (array_key_exists($columnName, $columns)
            && false === PiwikUpdater::getCurrentRecordedComponentVersion($component)
            && self::wasDimensionMovedFromCoreToPlugin($component, $version)) {
            PiwikUpdater::recordComponentSuccessfullyUpdated($component, $version);
            return $versions;
        }

        $versions[$component] = $version;

        return $versions;
    }

    public static function isDimensionComponent($name)
    {
        return 0 === strpos($name, 'log_visit.')
            || 0 === strpos($name, 'log_conversion.')
            || 0 === strpos($name, 'log_conversion_item.')
            || 0 === strpos($name, 'log_link_visit_action.');
    }

    public static function wasDimensionMovedFromCoreToPlugin($name, $version)
    {
        $dimensions = array (
            'log_visit.config_resolution' => 'VARCHAR(9) NOT NULL',
            'log_visit.config_device_brand' => 'VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL',
            'log_visit.config_device_model' => 'VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL',
            'log_visit.config_windowsmedia' => 'TINYINT(1) NOT NULL',
            'log_visit.config_silverlight' => 'TINYINT(1) NOT NULL',
            'log_visit.config_java' => 'TINYINT(1) NOT NULL',
            'log_visit.config_gears' => 'TINYINT(1) NOT NULL',
            'log_visit.config_pdf' => 'TINYINT(1) NOT NULL',
            'log_visit.config_quicktime' => 'TINYINT(1) NOT NULL',
            'log_visit.config_realplayer' => 'TINYINT(1) NOT NULL',
            'log_visit.config_device_type' => 'TINYINT( 100 ) NULL DEFAULT NULL',
            'log_visit.visitor_localtime' => 'TIME NOT NULL',
            'log_visit.location_region' => 'char(2) DEFAULT NULL1',
            'log_visit.visitor_days_since_last' => 'SMALLINT(5) UNSIGNED NOT NULL',
            'log_visit.location_longitude' => 'float(10, 6) DEFAULT NULL1',
            'log_visit.visit_total_events' => 'SMALLINT(5) UNSIGNED NOT NULL',
            'log_visit.config_os_version' => 'VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL',
            'log_visit.location_city' => 'varchar(255) DEFAULT NULL1',
            'log_visit.location_country' => 'CHAR(3) NOT NULL1',
            'log_visit.location_latitude' => 'float(10, 6) DEFAULT NULL1',
            'log_visit.config_flash' => 'TINYINT(1) NOT NULL',
            'log_visit.config_director' => 'TINYINT(1) NOT NULL',
            'log_visit.visit_total_time' => 'SMALLINT(5) UNSIGNED NOT NULL',
            'log_visit.visitor_count_visits' => 'SMALLINT(5) UNSIGNED NOT NULL1',
            'log_visit.visit_entry_idaction_name' => 'INTEGER(11) UNSIGNED NOT NULL',
            'log_visit.visit_entry_idaction_url' => 'INTEGER(11) UNSIGNED NOT NULL',
            'log_visit.visitor_returning' => 'TINYINT(1) NOT NULL1',
            'log_visit.visitor_days_since_order' => 'SMALLINT(5) UNSIGNED NOT NULL1',
            'log_visit.visit_goal_buyer' => 'TINYINT(1) NOT NULL',
            'log_visit.visit_first_action_time' => 'DATETIME NOT NULL',
            'log_visit.visit_goal_converted' => 'TINYINT(1) NOT NULL',
            'log_visit.visitor_days_since_first' => 'SMALLINT(5) UNSIGNED NOT NULL1',
            'log_visit.visit_exit_idaction_name' => 'INTEGER(11) UNSIGNED NOT NULL',
            'log_visit.visit_exit_idaction_url' => 'INTEGER(11) UNSIGNED NULL DEFAULT 0',
            'log_visit.config_browser_version' => 'VARCHAR(20) NOT NULL',
            'log_visit.config_browser_name' => 'VARCHAR(10) NOT NULL',
            'log_visit.location_browser_lang' => 'VARCHAR(20) NOT NULL',
            'log_visit.config_os' => 'CHAR(3) NOT NULL',
            'log_visit.config_cookie' => 'TINYINT(1) NOT NULL',
            'log_visit.referer_url' => 'TEXT NOT NULL',
            'log_visit.visit_total_searches' => 'SMALLINT(5) UNSIGNED NOT NULL',
            'log_visit.visit_total_actions' => 'SMALLINT(5) UNSIGNED NOT NULL',
            'log_visit.referer_keyword' => 'VARCHAR(255) NULL1',
            'log_visit.referer_name' => 'VARCHAR(70) NULL1',
            'log_visit.referer_type' => 'TINYINT(1) UNSIGNED NULL1',
            'log_visit.user_id' => 'VARCHAR(200) NULL',
            'log_link_visit_action.idaction_name' => 'INTEGER(10) UNSIGNED',
            'log_link_visit_action.idaction_url' => 'INTEGER(10) UNSIGNED DEFAULT NULL',
            'log_link_visit_action.server_time' => 'DATETIME NOT NULL',
            'log_link_visit_action.time_spent_ref_action' => 'INTEGER(10) UNSIGNED NOT NULL',
            'log_link_visit_action.idaction_event_action' => 'INTEGER(10) UNSIGNED DEFAULT NULL',
            'log_link_visit_action.idaction_event_category' => 'INTEGER(10) UNSIGNED DEFAULT NULL',
            'log_conversion.revenue_discount' => 'float default NULL',
            'log_conversion.revenue' => 'float default NULL',
            'log_conversion.revenue_shipping' => 'float default NULL',
            'log_conversion.revenue_subtotal' => 'float default NULL',
            'log_conversion.revenue_tax' => 'float default NULL',
        );

        if (!array_key_exists($name, $dimensions)) {
            return false;
        }

        return strtolower($dimensions[$name]) === strtolower($version);
    }

    public static function onNoUpdateAvailable($versionsThatWereChecked)
    {
        if (!empty($versionsThatWereChecked)) {
            // invalidate cache only if there were actually file changes before, otherwise we write the cache on each
            // request. There were versions checked only if there was a file change but no update, meaning we can
            // set the cache and declare this state as "no update available".
            self::cacheCurrentDimensionFileChanges();
        }
    }

    private static function getCurrentDimensionFileChanges()
    {
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH . '/plugins/*/Columns', '*.php');

        $times = array();
        foreach ($files as $file) {
            $times[$file] = filemtime($file);
        }

        return $times;
    }

    private static function cacheCurrentDimensionFileChanges()
    {
        $changes = self::getCurrentDimensionFileChanges();
        $persistentCache = new PersistentCache('AllDimensionModifyTime');
        $persistentCache->set($changes);
    }

    private static function getCachedDimensionFileChanges()
    {
        $persistentCache = new PersistentCache('AllDimensionModifyTime');
        if ($persistentCache->has()) {
            return $persistentCache->get();
        }

        return array();
    }
}
