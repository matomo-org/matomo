<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Plugins\Dashboard\Model as DashboardModel;

/**
 * This Update script will update all browser and os archives of UserSettings and DevicesDetection plugin
 *
 * In the future only DevicesDetection will handle browser and os archives, so we try to rename all existing archives
 * of UserSettings plugin to their corresponding archive name in DevicesDetection plugin:
 * - *UserSettings_browser* will now be *DevicesDetection_browserVersions*
 * - *UserSettings_os* will now be *DevicesDetection_osVersions*
 *
 * Unlike DevicesDetection plugin, the UserSettings plugin did not store archives holding the os and browser data without
 * their version number. The "version-less" reports were always generated out of the "version-containing" archives .
 * For big archives (month/year) that ment that some of the data was truncated, due to the datatable entry limit.
 * To avoid that data loss / inaccuracy in the future, DevicesDetection plugin will also store archives without the version.
 * For data archived after DevicesDetection plugin was enabled, those archive already exist. As we are removing the
 * UserSettings reports, there is a fallback in DevicesDetection API to build the report out of the datatable with versions.
 *
 * NOTE: Some archives might not contain "all" data.
 * That might have happened directly after the day DevicesDetection plugin was enabled. For the days before, there were
 * no archives calculated. So week/month/year archives will only contain data for the days, where archives were generated
 * To find a date after which it is safe to use DevicesDetection archives we need to find the first day-archive that
 * contains DevicesDetection data. Day archives will always contain full data, but week/month/year archives may not.
 * So we need to recreate those week/month/year archives.
 */
class Updates_2_10_0_b5 extends Updates
{
    public static $archiveBlobTables;

    public function getMigrationQueries(Updater $updater)
    {
        $sqls = array('# ATTENTION: This update script will execute some more SQL queries than that below as it is necessary to rebuilt some archives #' => false);

        // update scheduled reports to use new plugin
        $reportsToReplace = array(
            'UserSettings_getBrowserVersion' => 'DevicesDetection_getBrowserVersions',
            'UserSettings_getBrowser' => 'DevicesDetection_getBrowsers',
            'UserSettings_getOSFamily' => 'DevicesDetection_getOsFamilies',
            'UserSettings_getOS' => 'DevicesDetection_getOsVersions',
            'UserSettings_getMobileVsDesktop' => 'DevicesDetection_getType',
            'UserSettings_getBrowserType' => 'DevicesDetection_getBrowserEngines',
            'UserSettings_getWideScreen' => 'UserSettings_getScreenType',
        );

        foreach ($reportsToReplace as $old => $new) {
            $sqls["UPDATE " . Common::prefixTable('report') . " SET reports = REPLACE(reports, '".$old."', '".$new."')"] = false;
        }

        // update dashboard to use new widgets
        $oldWidgets = array(
            array('module' => 'UserSettings', 'action' => 'getBrowserVersion', 'params' => array()),
            array('module' => 'UserSettings', 'action' => 'getBrowser', 'params' => array()),
            array('module' => 'UserSettings', 'action' => 'getOSFamily', 'params' => array()),
            array('module' => 'UserSettings', 'action' => 'getOS', 'params' => array()),
            array('module' => 'UserSettings', 'action' => 'getMobileVsDesktop', 'params' => array()),
            array('module' => 'UserSettings', 'action' => 'getBrowserType', 'params' => array()),
            array('module' => 'UserSettings', 'action' => 'getWideScreen', 'params' => array()),
        );

        $newWidgets = array(
            array('module' => 'DevicesDetection', 'action' => 'getBrowserVersions', 'params' => array()),
            array('module' => 'DevicesDetection', 'action' => 'getBrowsers', 'params' => array()),
            array('module' => 'DevicesDetection', 'action' => 'getOsFamilies', 'params' => array()),
            array('module' => 'DevicesDetection', 'action' => 'getOsVersions', 'params' => array()),
            array('module' => 'DevicesDetection', 'action' => 'getType', 'params' => array()),
            array('module' => 'DevicesDetection', 'action' => 'getBrowserEngines', 'params' => array()),
            array('module' => 'UserSettings', 'action' => 'getScreenType', 'params' => array()),
        );

        $allDashboards = Db::get()->fetchAll(sprintf("SELECT * FROM %s", Common::prefixTable('user_dashboard')));

        foreach ($allDashboards as $dashboard) {
            $dashboardLayout = json_decode($dashboard['layout']);

            $dashboardLayout = DashboardModel::replaceDashboardWidgets($dashboardLayout, $oldWidgets, $newWidgets);

            $newLayout = json_encode($dashboardLayout);
            if ($newLayout != $dashboard['layout']) {
                $sqls["UPDATE " . Common::prefixTable('user_dashboard') . " SET layout = '".addslashes($newLayout)."' WHERE iddashboard = ".$dashboard['iddashboard']] = false;
            }
        }

        return $sqls;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));

        // DeviceDetection upgrade in beta1 timed out on demo #6750
        $archiveBlobTables = self::getAllArchiveBlobTables();

        foreach ($archiveBlobTables as $table) {
            self::updateBrowserArchives($table);
            self::updateOsArchives($table);
        }
    }

    /**
     * Returns all available archive blob tables
     *
     * @return array
     */
    public static function getAllArchiveBlobTables()
    {
        if (empty(self::$archiveBlobTables)) {
            $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();

            self::$archiveBlobTables = array_filter($archiveTables, function ($name) {
                return ArchiveTableCreator::getTypeFromTableName($name) == ArchiveTableCreator::BLOB_TABLE;
            });

            // sort tables so we have them in order of their date
            rsort(self::$archiveBlobTables);
        }

        return (array) self::$archiveBlobTables;
    }

    /**
     * Find the first day on which DevicesDetection archives were generated
     *
     * @return int    Timestamp
     */
    public static function getFirstDayOfArchivedDeviceDetectorData()
    {
        static $deviceDetectionBlobAvailableDate;

        if (empty($deviceDetectionBlobAvailableDate)) {
            $archiveBlobTables = self::getAllArchiveBlobTables();

            $deviceDetectionBlobAvailableDate = null;
            foreach ($archiveBlobTables as $table) {

                // Look for all day archives and try to find that with the lowest date
                $deviceDetectionBlobAvailableDate = Db::get()->fetchOne(sprintf("SELECT date1 FROM %s WHERE name = 'DevicesDetection_browserVersions' AND period = 1 ORDER BY date1 ASC LIMIT 1", $table));

                if (!empty($deviceDetectionBlobAvailableDate)) {
                    break;
                }
            }

            $deviceDetectionBlobAvailableDate = strtotime($deviceDetectionBlobAvailableDate);
        }

        return $deviceDetectionBlobAvailableDate;
    }

    /**
     * Updates all browser archives to new structure
     * @param string $table
     * @throws \Exception
     */
    public static function updateBrowserArchives($table)
    {
        // rename old UserSettings archives where no DeviceDetection archives exists
        Db::exec(sprintf("UPDATE IGNORE %s SET name='DevicesDetection_browserVersions' WHERE name = 'UserSettings_browser'", $table));

        /*
         * check dates of remaining (non-day) archives with calculated safe date
         * archives before or within that week/month/year of that date will be replaced
         */
        $oldBrowserBlobs = Db::get()->fetchAll(sprintf("SELECT * FROM %s WHERE name = 'UserSettings_browser' AND `period` > 1", $table));
        foreach ($oldBrowserBlobs as $blob) {

            // if start date of blob is before calculated date us old usersettings archive instead of already existing DevicesDetection archive
            if (strtotime($blob['date1']) < self::getFirstDayOfArchivedDeviceDetectorData()) {
                Db::get()->query(sprintf("DELETE FROM %s WHERE idarchive = ? AND name = ?", $table), array($blob['idarchive'], 'DevicesDetection_browserVersions'));
                Db::get()->query(sprintf("UPDATE %s SET name = ? WHERE idarchive = ? AND name = ?", $table), array('DevicesDetection_browserVersions', $blob['idarchive'], 'UserSettings_browser'));
            }
        }
    }

    public static function updateOsArchives($table)
    {
        Db::exec(sprintf("UPDATE IGNORE %s SET name='DevicesDetection_osVersions' WHERE name = 'UserSettings_os'", $table));

        /*
         * check dates of remaining (non-day) archives with calculated safe date
         * archives before or within that week/month/year of that date will be replaced
         */
        $oldOsBlobs = Db::get()->fetchAll(sprintf("SELECT * FROM %s WHERE name = 'UserSettings_os' AND `period` > 1", $table));
        foreach ($oldOsBlobs as $blob) {

            // if start date of blob is before calculated date us old usersettings archive instead of already existing DevicesDetection archive
            if (strtotime($blob['date1']) < self::getFirstDayOfArchivedDeviceDetectorData()) {
                Db::get()->query(sprintf("DELETE FROM %s WHERE idarchive = ? AND name = ?", $table), array($blob['idarchive'], 'DevicesDetection_osVersions'));
                Db::get()->query(sprintf("UPDATE %s SET name = ? WHERE idarchive = ? AND name = ?", $table), array('DevicesDetection_osVersions', $blob['idarchive'], 'UserSettings_os'));
            }
        }
    }
}
