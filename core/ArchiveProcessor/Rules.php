<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ArchiveProcessor;

use Exception;
use Piwik\Config;
use Piwik\Date;
use Piwik\Log;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\Controller;
use Piwik\Segment;
use Piwik\SettingsPiwik;
use Piwik\SettingsServer;
use Piwik\Site;
use Piwik\Tracker\Cache;

/**
 * This class contains Archiving rules/logic which are used when creating and processing Archives.
 *
 */
class Rules
{
    const OPTION_TODAY_ARCHIVE_TTL = 'todayArchiveTimeToLive';

    const OPTION_BROWSER_TRIGGER_ARCHIVING = 'enableBrowserTriggerArchiving';

    const FLAG_TABLE_PURGED = 'lastPurge_';

    /** Flag that will forcefully disable the archiving process (used in tests only) */
    public static $archivingDisabledByTests = false;

    /**
     * Returns the name of the archive field used to tell the status of an archive, (ie,
     * whether the archive was created successfully or not).
     *
     * @param array $idSites
     * @param Segment $segment
     * @param string $periodLabel
     * @param string $plugin
     * @param bool $isSkipAggregationOfSubTables
     * @return string
     */
    public static function getDoneStringFlagFor(array $idSites, $segment, $periodLabel, $plugin, $isSkipAggregationOfSubTables)
    {
        if (!self::shouldProcessReportsAllPlugins($idSites, $segment, $periodLabel)) {
            return self::getDoneFlagArchiveContainsOnePlugin($segment, $plugin, $isSkipAggregationOfSubTables);
        }
        return self::getDoneFlagArchiveContainsAllPlugins($segment);
    }

    public static function shouldProcessReportsAllPlugins(array $idSites, Segment $segment, $periodLabel)
    {
        if ($segment->isEmpty() && $periodLabel != 'range') {
            return true;
        }

        return self::isSegmentPreProcessed($idSites, $segment);
    }

    /**
     * @param $idSites
     * @return array
     */
    private static function getSegmentsToProcess($idSites)
    {
        $knownSegmentsToArchiveAllSites = SettingsPiwik::getKnownSegmentsToArchive();

        $segmentsToProcess = $knownSegmentsToArchiveAllSites;
        foreach ($idSites as $idSite) {
            $segmentForThisWebsite = SettingsPiwik::getKnownSegmentsToArchiveForSite($idSite);
            $segmentsToProcess = array_merge($segmentsToProcess, $segmentForThisWebsite);
        }
        $segmentsToProcess = array_unique($segmentsToProcess);
        return $segmentsToProcess;
    }

    public static function getDoneFlagArchiveContainsOnePlugin(Segment $segment, $plugin, $isSkipAggregationOfSubTables = false)
    {
        $partial = self::isFlagArchivePartial($plugin, $isSkipAggregationOfSubTables);
        return 'done' . $segment->getHash() . '.' . $plugin . $partial ;
    }

    private static function getDoneFlagArchiveContainsAllPlugins(Segment $segment)
    {
        return 'done' . $segment->getHash();
    }

    /**
     * @param $plugin
     * @param $isSkipAggregationOfSubTables
     * @return string
     */
    private static function isFlagArchivePartial($plugin, $isSkipAggregationOfSubTables)
    {
        $partialArchive = '';
        if ($plugin != "VisitsSummary" // VisitsSummary is always called when segmenting and should not have its own .partial archive
            && $isSkipAggregationOfSubTables
        ) {
            $partialArchive = '.partial';
        }
        return $partialArchive;
    }

    /**
     * @param array $plugins
     * @param $segment
     * @return array
     */
    public static function getDoneFlags(array $plugins, Segment $segment, $isSkipAggregationOfSubTables)
    {
        $doneFlags = array();
        $doneAllPlugins = self::getDoneFlagArchiveContainsAllPlugins($segment);
        $doneFlags[$doneAllPlugins] = $doneAllPlugins;

        $plugins = array_unique($plugins);
        foreach ($plugins as $plugin) {
            $doneOnePlugin = self::getDoneFlagArchiveContainsOnePlugin($segment, $plugin, $isSkipAggregationOfSubTables);
            $doneFlags[$plugin] = $doneOnePlugin;
        }
        return $doneFlags;
    }

    /**
     * Given a monthly archive table, will delete all reports that are now outdated,
     * or reports that ended with an error
     *
     * @param \Piwik\Date $date
     * @return int|bool  False, or timestamp indicating which archives to delete
     */
    public static function shouldPurgeOutdatedArchives(Date $date)
    {
        $key = self::FLAG_TABLE_PURGED . "blob_" . $date->toString('Y_m');
        $timestamp = Option::get($key);

        // we shall purge temporary archives after their timeout is finished, plus an extra 6 hours
        // in case archiving is disabled or run once a day, we give it this extra time to run
        // and re-process more recent records...
        $temporaryArchivingTimeout = self::getTodayArchiveTimeToLive();
        $hoursBetweenPurge = 6;
        $purgeEveryNSeconds = max($temporaryArchivingTimeout, $hoursBetweenPurge * 3600);

        // we only delete archives if we are able to process them, otherwise, the browser might process reports
        // when &segment= is specified (or custom date range) and would below, delete temporary archives that the
        // browser is not able to process until next cron run (which could be more than 1 hour away)
        if (self::isRequestAuthorizedToArchive()
            && (!$timestamp
                || $timestamp < time() - $purgeEveryNSeconds)
        ) {
            Option::set($key, time());

            if (self::isBrowserTriggerEnabled()) {
                // If Browser Archiving is enabled, it is likely there are many more temporary archives
                // We delete more often which is safe, since reports are re-processed on demand
                $purgeArchivesOlderThan = Date::factory(time() - 2 * $temporaryArchivingTimeout)->getDateTime();
            } else {
                // If cron core:archive command is building the reports, we should keep all temporary reports from today
                $purgeArchivesOlderThan = Date::factory('yesterday')->getDateTime();
            }
            return $purgeArchivesOlderThan;
        }

        Log::info("Purging temporary archives: skipped.");
        return false;
    }

    public static function getMinTimeProcessedForTemporaryArchive(
        Date $dateStart, \Piwik\Period $period, Segment $segment, Site $site)
    {
        $now = time();
        $minimumArchiveTime = $now - Rules::getTodayArchiveTimeToLive();

        $idSites = array($site->getId());
        $isArchivingDisabled = Rules::isArchivingDisabledFor($idSites, $segment, $period->getLabel());
        if ($isArchivingDisabled) {
            if ($period->getNumberOfSubperiods() == 0
                && $dateStart->getTimestamp() <= $now
            ) {
                // Today: accept any recent enough archive
                $minimumArchiveTime = false;
            } else {
                // This week, this month, this year:
                // accept any archive that was processed today after 00:00:01 this morning
                $timezone = $site->getTimezone();
                $minimumArchiveTime = Date::factory(Date::factory('now', $timezone)->getDateStartUTC())->setTimezone($timezone)->getTimestamp();
            }
        }
        return $minimumArchiveTime;
    }

    public static function setTodayArchiveTimeToLive($timeToLiveSeconds)
    {
        $timeToLiveSeconds = (int)$timeToLiveSeconds;
        if ($timeToLiveSeconds <= 0) {
            throw new Exception(Piwik::translate('General_ExceptionInvalidArchiveTimeToLive'));
        }
        Option::set(self::OPTION_TODAY_ARCHIVE_TTL, $timeToLiveSeconds, $autoLoad = true);
    }

    public static function getTodayArchiveTimeToLive()
    {
        $uiSettingIsEnabled = Controller::isGeneralSettingsAdminEnabled();

        if ($uiSettingIsEnabled) {
            $timeToLive = Option::get(self::OPTION_TODAY_ARCHIVE_TTL);
            if ($timeToLive !== false) {
                return $timeToLive;
            }
        }
        return Config::getInstance()->General['time_before_today_archive_considered_outdated'];
    }

    public static function isArchivingDisabledFor(array $idSites, Segment $segment, $periodLabel)
    {
        if ($periodLabel == 'range') {
            return false;
        }
        $processOneReportOnly = !self::shouldProcessReportsAllPlugins($idSites, $segment, $periodLabel);
        $isArchivingDisabled = !self::isRequestAuthorizedToArchive() || self::$archivingDisabledByTests;

        if ($processOneReportOnly) {

            // When there is a segment, we disable archiving when browser_archiving_disabled_enforce applies
            if (!$segment->isEmpty()
                && $isArchivingDisabled
                && Config::getInstance()->General['browser_archiving_disabled_enforce']
                && !SettingsServer::isArchivePhpTriggered() // Only applies when we are not running core:archive command
            ) {
                Log::debug("Archiving is disabled because of config setting browser_archiving_disabled_enforce=1");
                return true;
            }

            // Always allow processing one report
            return false;
        }
        return $isArchivingDisabled;
    }

    public static function isRequestAuthorizedToArchive()
    {
        return Rules::isBrowserTriggerEnabled() || SettingsServer::isArchivePhpTriggered();
    }

    public static function isBrowserTriggerEnabled()
    {
        $uiSettingIsEnabled = Controller::isGeneralSettingsAdminEnabled();

        if ($uiSettingIsEnabled) {
            $browserArchivingEnabled = Option::get(self::OPTION_BROWSER_TRIGGER_ARCHIVING);
            if ($browserArchivingEnabled !== false) {
                return (bool)$browserArchivingEnabled;
            }
        }
        return (bool)Config::getInstance()->General['enable_browser_archiving_triggering'];
    }

    public static function setBrowserTriggerArchiving($enabled)
    {
        if (!is_bool($enabled)) {
            throw new Exception('Browser trigger archiving must be set to true or false.');
        }
        Option::set(self::OPTION_BROWSER_TRIGGER_ARCHIVING, (int)$enabled, $autoLoad = true);
        Cache::clearCacheGeneral();
    }

    /**
     * Returns true if the archiving process should skip the calculation of unique visitors
     * across several sites. The `[General] enable_processing_unique_visitors_multiple_sites`
     * INI config option controls the value of this variable.
     *
     * @return bool
     */
    public static function shouldSkipUniqueVisitorsCalculationForMultipleSites()
    {
        return Config::getInstance()->General['enable_processing_unique_visitors_multiple_sites'] != 1;
    }

    /**
     * @param array $idSites
     * @param Segment $segment
     * @return bool
     */
    protected static function isSegmentPreProcessed(array $idSites, Segment $segment)
    {
        $segmentsToProcess = self::getSegmentsToProcess($idSites);

        if (empty($segmentsToProcess)) {
            return false;
        }
        // If the requested segment is one of the segments to pre-process
        // we ensure that any call to the API will trigger archiving of all reports for this segment
        $segment = $segment->getString();

        // Turns out the getString() above returns the URL decoded segment string
        $segmentsToProcessUrlDecoded = array_map('urldecode', $segmentsToProcess);

        if (in_array($segment, $segmentsToProcess)
            || in_array($segment, $segmentsToProcessUrlDecoded)
        ) {
            return true;
        }
        return false;
    }
}
