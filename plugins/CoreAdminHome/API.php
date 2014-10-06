<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Exception;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Period\Week;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Site;
use Piwik\TaskScheduler;

/**
 * @hideExceptForSuperUser
 * @method static \Piwik\Plugins\CoreAdminHome\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Will run all scheduled tasks due to run at this time.
     *
     * @return array
     */
    public function runScheduledTasks()
    {
        Piwik::checkUserHasSuperUserAccess();
        return TaskScheduler::runTasks();
    }

    /*
     * stores the list of websites IDs to re-reprocess in core:archive command
     */
    const OPTION_INVALIDATED_IDSITES = 'InvalidatedOldReports_WebsiteIds';

    /**
     * When tracking data in the past (using Tracking API), this function
     * can be used to invalidate reports for the idSites and dates where new data
     * was added.
     * DEV: If you call this API, the UI should display the data correctly, but will process
     *      in real time, which could be very slow after large data imports.
     *      After calling this function via REST, you can manually force all data
     *      to be reprocessed by visiting the script as the Super User:
     *      http://example.net/piwik/misc/cron/archive.php?token_auth=$SUPER_USER_TOKEN_AUTH_HERE
     * REQUIREMENTS: On large piwik setups, you will need in PHP configuration: max_execution_time = 0
     *    We recommend to use an hourly schedule of the script.
     *    More information: http://piwik.org/setup-auto-archiving/
     *
     * @param string $idSites Comma separated list of idSite that have had data imported for the specified dates
     * @param string $dates Comma separated list of dates to invalidate for all these websites
     * @param string $period If specified (one of day, week, month, year, range) it will only delete archives for this period.
     *                      Note: because week, month, year, range reports aggregate day reports then you need to specifically invalidate day reports to see
     *                      other periods reports processed..
     * @throws Exception
     * @return array
     */
    public function invalidateArchivedReports($idSites, $dates, $period = false)
    {
        $idSites = Site::getIdSitesFromIdSitesString($idSites);

        if (empty($idSites)) {
            throw new Exception("Specify a value for &idSites= as a comma separated list of website IDs, for which your token_auth has 'admin' permission");
        }

        Piwik::checkUserHasAdminAccess($idSites);

        if (!empty($period)) {
            $period = Period\Factory::build($period, Date::today());
        }

        // Ensure the specified dates are valid
        $toInvalidate = $invalidDates = array();

        $dates = explode(',', trim($dates));
        $dates = array_unique($dates);

        foreach ($dates as $theDate) {
            $theDate = trim($theDate);
            try {
                $date = Date::factory($theDate);
            } catch (Exception $e) {
                $invalidDates[] = $theDate;
                continue;
            }
            if ($date->toString() == $theDate) {
                $toInvalidate[] = $date;
            } else {
                $invalidDates[] = $theDate;
            }
        }

        // If using the feature "Delete logs older than N days"...
        $purgeDataSettings = PrivacyManager::getPurgeDataSettings();
        $logsAreDeletedBeforeThisDate = $purgeDataSettings['delete_logs_schedule_lowest_interval'];
        $logsDeleteEnabled = $purgeDataSettings['delete_logs_enable'];
        $minimumDateWithLogs = false;
        if ($logsDeleteEnabled
            && $logsAreDeletedBeforeThisDate
        ) {
            $minimumDateWithLogs = Date::factory('today')->subDay($logsAreDeletedBeforeThisDate);
        }

        // Given the list of dates, process which tables they should be deleted from
        $minDate = false;
        $warningDates = $processedDates = array();
        /* @var $date Date */
        foreach ($toInvalidate as $date) {
            // we should only delete reports for dates that are more recent than N days
            if ($minimumDateWithLogs
                && $date->isEarlier($minimumDateWithLogs)
            ) {
                $warningDates[] = $date->toString();
            } else {
                $processedDates[] = $date->toString();
            }

            $month = $date->toString('Y_m');
            // For a given date, we must invalidate in the monthly archive table
            $datesByMonth[$month][] = $date->toString();

            // But also the year stored in January
            $year = $date->toString('Y_01');
            $datesByMonth[$year][] = $date->toString();

            // but also weeks overlapping several months stored in the month where the week is starting
            /* @var $week Week */
            $week = Period\Factory::build('week', $date);
            $weekAsString = $week->getDateStart()->toString('Y_m');
            $datesByMonth[$weekAsString][] = $date->toString();

            // Keep track of the minimum date for each website
            if ($minDate === false
                || $date->isEarlier($minDate)
            ) {
                $minDate = $date;
            }
        }

        if (empty($minDate)) {
            throw new Exception("Check the 'dates' parameter is a valid date.");
        }

        $invalidateForPeriod = $period ? $period->getId() : false;

        // In each table, invalidate day/week/month/year containing this date
        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();
        foreach ($archiveTables as $table) {
            // Extract Y_m from table name
            $suffix = ArchiveTableCreator::getDateFromTableName($table);
            if (!isset($datesByMonth[$suffix])) {
                continue;
            }
            // Dates which are to be deleted from this table
            $datesToDeleteInTable = $datesByMonth[$suffix];

            // Build one statement to delete all dates from the given table
            $sql = $bind = array();
            $datesToDeleteInTable = array_unique($datesToDeleteInTable);
            foreach ($datesToDeleteInTable as $dateToDelete) {
                $sql[] = '(date1 <= ? AND ? <= date2 AND name LIKE \'done%\')';
                $bind[] = $dateToDelete;
                $bind[] = $dateToDelete;
            }
            $sql = implode(" OR ", $sql);

            $sqlPeriod = "";
            if ($invalidateForPeriod) {
                $sqlPeriod = " AND period = ? ";
                $bind[] = $invalidateForPeriod;
            }

            $query = "UPDATE $table " .
                " SET value = " . ArchiveWriter::DONE_INVALIDATED .
                " WHERE ( $sql ) " .
                " AND idsite IN (" . implode(",", $idSites) . ")" .
                $sqlPeriod;
            Db::query($query, $bind);
        }
        \Piwik\Plugins\SitesManager\API::getInstance()->updateSiteCreatedTime($idSites, $minDate);

        // Force to re-process data for these websites in the next cron core:archive command run
        $invalidatedIdSites = self::getWebsiteIdsToInvalidate();
        $invalidatedIdSites = array_merge($invalidatedIdSites, $idSites);
        $invalidatedIdSites = array_unique($invalidatedIdSites);
        $invalidatedIdSites = array_values($invalidatedIdSites);
        Option::set(self::OPTION_INVALIDATED_IDSITES, serialize($invalidatedIdSites));

        Site::clearCache();

        $output = array();
        // output logs
        if ($warningDates) {
            $output[] = 'Warning: the following Dates have not been invalidated, because they are earlier than your Log Deletion limit: ' .
                implode(", ", $warningDates) .
                "\n The last day with logs is " . $minimumDateWithLogs . ". " .
                "\n Please disable 'Delete old Logs' or set it to a higher deletion threshold (eg. 180 days or 365 years).'.";
        }
        $output[] = "Success. The following dates were invalidated successfully: " .
            implode(", ", $processedDates);
        return $output;
    }

    /**
     * Returns array of idSites to force re-process next time core:archive command runs
     *
     * @ignore
     * @return mixed
     */
    public static function getWebsiteIdsToInvalidate()
    {
        Piwik::checkUserHasSomeAdminAccess();

        Option::clearCachedOption(self::OPTION_INVALIDATED_IDSITES);
        $invalidatedIdSites = Option::get(self::OPTION_INVALIDATED_IDSITES);
        if ($invalidatedIdSites
            && ($invalidatedIdSites = unserialize($invalidatedIdSites))
            && count($invalidatedIdSites)
        ) {
            return $invalidatedIdSites;
        }
        return array();
    }

    /**
     * Return true if plugin is activated, false otherwise
     *
     * @param string $pluginName
     * @return bool
     */
    public function isPluginActivated($pluginName)
    {
        Piwik::checkUserHasSomeViewAccess();
        return \Piwik\Plugin\Manager::getInstance()->isPluginActivated($pluginName);
    }
}
