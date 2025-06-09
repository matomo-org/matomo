<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Columns;

use Piwik\Common;
use Piwik\Db\Schema;
use Piwik\DbHelper;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Manager;
use Piwik\Updater as PiwikUpdater;
use Piwik\Filesystem;
use Piwik\Cache as PiwikCache;
use Piwik\Updater\Migration;

/**
 * Class that handles dimension updates
 */
class Updater extends \Piwik\Updates
{
    private static $cacheId = 'AllDimensionModifyTime';

    /**
     * @var VisitDimension[]
     */
    public $visitDimensions;

    /**
     * @var ActionDimension[]
     */
    private $actionDimensions;

    /**
     * @var ConversionDimension[]
     */
    private $conversionDimensions;

    /**
     * @param VisitDimension[]|null $visitDimensions
     * @param ActionDimension[]|null $actionDimensions
     * @param ConversionDimension[]|null $conversionDimensions
     */
    public function __construct(?array $visitDimensions = null, ?array $actionDimensions = null, ?array $conversionDimensions = null)
    {
        $this->visitDimensions = $visitDimensions;
        $this->actionDimensions = $actionDimensions;
        $this->conversionDimensions = $conversionDimensions;
    }

    /**
     * @param PiwikUpdater $updater
     * @return Migration[]
     * @api
     */
    public function getMigrations(PiwikUpdater $updater)
    {
        return $this->getMigrationQueries($updater);
    }

    /**
     * @param PiwikUpdater $updater
     * @return Migration\Db[]
     */
    public function getMigrationQueries(PiwikUpdater $updater)
    {
        $sqls = array();

        $changingColumns = $this->getUpdates($updater);
        $errorCodes = array(
            Migration\Db\Sql::ERROR_CODE_COLUMN_NOT_EXISTS,
            Migration\Db\Sql::ERROR_CODE_DUPLICATE_COLUMN
        );

        foreach ($changingColumns as $table => $columns) {
            if (empty($columns) || !is_array($columns)) {
                continue;
            }

            if (Schema::getInstance()->supportsComplexColumnUpdates()) {
                $sql = "ALTER TABLE `" . Common::prefixTable($table) . "` " . implode(', ', $columns);
                $sqls[] = new Migration\Db\Sql($sql, $errorCodes);
            } else {
                foreach ($columns as $column) {
                    $sql = "ALTER TABLE `" . Common::prefixTable($table) . "` " . $column;
                    $sqls[] = new Migration\Db\Sql($sql, $errorCodes);
                }
            }
        }

        return $sqls;
    }

    public function doUpdate(PiwikUpdater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrationQueries($updater));
    }

    private function getVisitDimensions()
    {
        // see eg https://github.com/piwik/piwik/issues/8399 we fetch them only on demand to improve performance
        if (!isset($this->visitDimensions)) {
            $this->visitDimensions = VisitDimension::getAllDimensions();
        }

        return $this->visitDimensions;
    }

    private function getActionDimensions()
    {
        // see eg https://github.com/piwik/piwik/issues/8399 we fetch them only on demand to improve performance
        if (!isset($this->actionDimensions)) {
            $this->actionDimensions = ActionDimension::getAllDimensions();
        }

        return $this->actionDimensions;
    }

    private function getConversionDimensions()
    {
        // see eg https://github.com/piwik/piwik/issues/8399 we fetch them only on demand to improve performance
        if (!isset($this->conversionDimensions)) {
            $this->conversionDimensions = ConversionDimension::getAllDimensions();
        }

        return $this->conversionDimensions;
    }

    private function getUpdates(PiwikUpdater $updater)
    {
        $visitColumns      = DbHelper::getTableColumns(Common::prefixTable('log_visit'));
        $actionColumns     = DbHelper::getTableColumns(Common::prefixTable('log_link_visit_action'));
        $conversionColumns = DbHelper::getTableColumns(Common::prefixTable('log_conversion'));

        $allUpdatesToRun = array();

        foreach ($this->getVisitDimensions() as $dimension) {
            $updates         = $this->getUpdatesForDimension($updater, $dimension, 'log_visit.', $visitColumns);
            $allUpdatesToRun = $this->mixinUpdates($allUpdatesToRun, $updates);
        }

        foreach ($this->getActionDimensions() as $dimension) {
            $updates         = $this->getUpdatesForDimension($updater, $dimension, 'log_link_visit_action.', $actionColumns);
            $allUpdatesToRun = $this->mixinUpdates($allUpdatesToRun, $updates);
        }

        foreach ($this->getConversionDimensions() as $dimension) {
            $updates         = $this->getUpdatesForDimension($updater, $dimension, 'log_conversion.', $conversionColumns);
            $allUpdatesToRun = $this->mixinUpdates($allUpdatesToRun, $updates);
        }

        return $allUpdatesToRun;
    }

    /**
     * @param ActionDimension|ConversionDimension|VisitDimension $dimension
     * @param string $componentPrefix
     * @return array
     */
    private function getUpdatesForDimension(PiwikUpdater $updater, $dimension, $componentPrefix, $existingColumnsInDb)
    {
        $column = $dimension->getColumnName();
        $componentName = $componentPrefix . $column;

        if (!$updater->hasNewVersion($componentName)) {
            return array();
        }

        if (array_key_exists($column, $existingColumnsInDb)) {
            $sqlUpdates = $dimension->update();
        } else {
            $sqlUpdates = $dimension->install();
        }

        return $sqlUpdates;
    }

    private function mixinUpdates($allUpdatesToRun, $updatesFromDimension)
    {
        if (!empty($updatesFromDimension)) {
            foreach ($updatesFromDimension as $table => $col) {
                if (empty($allUpdatesToRun[$table])) {
                    $allUpdatesToRun[$table] = $col;
                } else {
                    $allUpdatesToRun[$table] = array_merge($allUpdatesToRun[$table], $col);
                }
            }
        }

        return $allUpdatesToRun;
    }

    public function getAllVersions(PiwikUpdater $updater)
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

        foreach ($this->getVisitDimensions() as $dimension) {
            $versions = $this->mixinVersions($updater, $dimension, VisitDimension::INSTALLER_PREFIX, $visitColumns, $versions);
        }

        foreach ($this->getActionDimensions() as $dimension) {
            $versions = $this->mixinVersions($updater, $dimension, ActionDimension::INSTALLER_PREFIX, $actionColumns, $versions);
        }

        foreach ($this->getConversionDimensions() as $dimension) {
            $versions = $this->mixinVersions($updater, $dimension, ConversionDimension::INSTALLER_PREFIX, $conversionColumns, $versions);
        }

        return $versions;
    }

    /**
     * @param PiwikUpdater $updater
     * @param Dimension $dimension
     * @param string $componentPrefix
     * @param array $columns
     * @param array $versions
     * @return array The modified versions array
     */
    private function mixinVersions(PiwikUpdater $updater, $dimension, $componentPrefix, $columns, $versions)
    {
        $columnName = $dimension->getColumnName();

        // dimensions w/o columns do not need DB updates
        if (!$columnName || !$dimension->hasColumnType()) {
            return $versions;
        }

        $component = $componentPrefix . $columnName;
        $version   = $dimension->getVersion();

        // if the column exists in the table, but has no associated version, and was one of the core columns
        // that was moved when the dimension refactor took place, then:
        // - set the installed version in the DB to the current code version
        // - and do not check for updates since we just set the version to the latest
        if (
            array_key_exists($columnName, $columns)
            && false === $updater->getCurrentComponentVersion($component)
            && self::wasDimensionMovedFromCoreToPlugin($component, $version)
        ) {
            $updater->markComponentSuccessfullyUpdated($component, $version);
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
        // maps names of core dimension columns that were part of the original dimension refactor with their
        // initial "version" strings. The '1' that is sometimes appended to the end of the string (sometimes seen as
        // NULL1) is from individual dimension "versioning" logic (eg, see VisitDimension::getVersion())
        $initialCoreDimensionVersions = array(
            'log_visit.config_resolution' => 'VARCHAR(9) NOT NULL',
            'log_visit.config_device_brand' => 'VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL',
            'log_visit.config_device_model' => 'VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL',
            'log_visit.config_windowsmedia' => 'TINYINT(1) NOT NULL',
            'log_visit.config_silverlight' => 'TINYINT(1) NOT NULL',
            'log_visit.config_java' => 'TINYINT(1) NOT NULL',
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
            'log_visit.visit_total_time' => 'SMALLINT(5) UNSIGNED NOT NULL',
            'log_visit.visitor_count_visits' => 'SMALLINT(5) UNSIGNED NOT NULL1',
            'log_visit.visit_entry_idaction_name' => 'INTEGER(11) UNSIGNED NOT NULL',
            'log_visit.visit_entry_idaction_url' => 'INTEGER(11) UNSIGNED NOT NULL',
            'log_visit.visitor_returning' => 'TINYINT(1) NOT NULL1',
            'log_visit.visitor_days_since_order' => 'INT(11) UNSIGNED NOT NULL1',
            'log_visit.visit_goal_buyer' => 'TINYINT(1) NOT NULL',
            'log_visit.visit_first_action_time' => 'DATETIME NOT NULL',
            'log_visit.visit_goal_converted' => 'TINYINT(1) NOT NULL',
            'log_visit.visitor_days_since_first' => 'SMALLINT(5) UNSIGNED NOT NULL1',
            'log_visit.visit_exit_idaction_name' => 'INTEGER(11) UNSIGNED NOT NULL',
            'log_visit.visit_exit_idaction_url' => 'INTEGER(11) UNSIGNED NULL DEFAULT 0',
            'log_visit.config_browser_version' => 'VARCHAR(20) NOT NULL',
            'log_visit.config_browser_name' => 'VARCHAR(10) NOT NULL',
            'log_visit.config_browser_engine' => 'VARCHAR(10) NOT NULL',
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
        );

        if (!array_key_exists($name, $initialCoreDimensionVersions)) {
            return false;
        }

        return strtolower($initialCoreDimensionVersions[$name]) === strtolower($version);
    }

    public function onNoUpdateAvailable($versionsThatWereChecked)
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
        $times = array();
        foreach (Manager::getPluginsDirectories() as $pluginsDir) {
            $files = Filesystem::globr($pluginsDir . '*/Columns', '*.php');

            foreach ($files as $file) {
                $times[$file] = filemtime($file);
            }
        }

        return $times;
    }

    private static function cacheCurrentDimensionFileChanges()
    {
        $changes = self::getCurrentDimensionFileChanges();

        $cache = self::buildCache();
        $cache->save(self::$cacheId, $changes);
    }

    private static function buildCache()
    {
        return PiwikCache::getEagerCache();
    }

    private static function getCachedDimensionFileChanges()
    {
        $cache = self::buildCache();

        if ($cache->contains(self::$cacheId)) {
            return $cache->fetch(self::$cacheId);
        }

        return array();
    }
}
