<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * This class contains Archiving rules/logic which are used in several places
 */
class Piwik_ArchiveProcessor_Rules
{
    const OPTION_TODAY_ARCHIVE_TTL = 'todayArchiveTimeToLive';

    const OPTION_BROWSER_TRIGGER_ARCHIVING = 'enableBrowserTriggerArchiving';

    const FLAG_TABLE_PURGED = 'lastPurge_';

    /** Old Archives purge can be disabled (used in tests only) */
    static public $purgeDisabledByTests = true;

    /** Flag that will forcefully disable the archiving process (used in tests only) */
    public static $archivingDisabledByTests = false;

    /**
     * Returns the name of the archive field used to tell the status of an archive, (ie,
     * whether the archive was created successfully or not).
     *
     * @param Piwik_Segment $segment
     * @param string $periodLabel
     * @param string $plugin
     * @return string
     */
    // FIXMEA: this is called all over the place, not right
    public static function getDoneStringFlagFor($segment, $periodLabel, $plugin)
    {
        if (!self::shouldProcessReportsAllPlugins($segment, $periodLabel)) {
            return self::getDoneFlagArchiveContainsOnePlugin($segment, $plugin);
        }
        return self::getDoneFlagArchiveContainsAllPlugins($segment);
    }

    public static function shouldProcessReportsAllPlugins(Piwik_Segment $segment, $periodLabel)
    {
        if ($segment->isEmpty() && $periodLabel != 'range') {
            return true;
        }

        $segmentsToProcess = Piwik::getKnownSegmentsToArchive();
        if (!empty($segmentsToProcess)) {
            // If the requested segment is one of the segments to pre-process
            // we ensure that any call to the API will trigger archiving of all reports for this segment
            $segment = $segment->getString();
            if (in_array($segment, $segmentsToProcess)) {
                return true;
            }
        }
        return false;
    }

    private static function getDoneFlagArchiveContainsOnePlugin(Piwik_Segment $segment, $plugin)
    {
        return 'done' . $segment->getHash() . '.' . $plugin;
    }

    private static function getDoneFlagArchiveContainsAllPlugins(Piwik_Segment $segment)
    {
        return 'done' . $segment->getHash();
    }

    /**
     * @param array $plugins
     * @param $segment
     * @return array
     */
    public static function getDoneFlags(array $plugins, $segment)
    {
        $doneFlags = array();
        $doneAllPlugins = self::getDoneFlagArchiveContainsAllPlugins($segment);
        $doneFlags[$doneAllPlugins] = $doneAllPlugins;
        foreach ($plugins as $plugin) {
            $doneOnePlugin = self::getDoneFlagArchiveContainsOnePlugin($segment, $plugin);
            $doneFlags[$plugin] = $doneOnePlugin;
        }
        return $doneFlags;
    }

    /**
     * Given a monthly archive table, will delete all reports that are now outdated,
     * or reports that ended with an error
     *
     * @return int False, or timestamp indicating which archives to delete
     */
    public static function shouldPurgeOutdatedArchives(Piwik_Date $date)
    {
        if (self::$purgeDisabledByTests) {
            return false;
        }
        $key = self::FLAG_TABLE_PURGED . "blob_" . $date->toString('Y_m');
        $timestamp = Piwik_GetOption($key);

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
            Piwik_SetOption($key, time());

            if (self::isBrowserTriggerEnabled()) {
                // If Browser Archiving is enabled, it is likely there are many more temporary archives
                // We delete more often which is safe, since reports are re-processed on demand
                $purgeArchivesOlderThan = Piwik_Date::factory(time() - 2 * $temporaryArchivingTimeout)->getDateTime();
            } else {
                // If archive.php via Cron is building the reports, we should keep all temporary reports from today
                $purgeArchivesOlderThan = Piwik_Date::factory('today')->getDateTime();
            }
            return $purgeArchivesOlderThan;
        }

        Piwik::log("Purging temporary archives: skipped.");
        return false;
    }

    public static function getMinTimeProcessedForTemporaryArchive(Piwik_Date $dateStart, Piwik_Period $period, Piwik_Segment $segment, Piwik_Site $site)
    {
        $now = time();
        $minimumArchiveTime = $now - Piwik_ArchiveProcessor_Rules::getTodayArchiveTimeToLive();

        $isArchivingDisabled = Piwik_ArchiveProcessor_Rules::isArchivingDisabledFor($segment, $period->getLabel());
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
                $minimumArchiveTime = Piwik_Date::factory(Piwik_Date::factory('now', $timezone)->getDateStartUTC())->setTimezone($timezone)->getTimestamp();
            }
        }
        return $minimumArchiveTime;
    }

    public static function setTodayArchiveTimeToLive($timeToLiveSeconds)
    {
        $timeToLiveSeconds = (int)$timeToLiveSeconds;
        if ($timeToLiveSeconds <= 0) {
            throw new Exception(Piwik_TranslateException('General_ExceptionInvalidArchiveTimeToLive'));
        }
        Piwik_SetOption(self::OPTION_TODAY_ARCHIVE_TTL, $timeToLiveSeconds, $autoLoad = true);
    }

    public static function getTodayArchiveTimeToLive()
    {
        $timeToLive = Piwik_GetOption(self::OPTION_TODAY_ARCHIVE_TTL);
        if ($timeToLive !== false) {
            return $timeToLive;
        }
        return Piwik_Config::getInstance()->General['time_before_today_archive_considered_outdated'];
    }

    public static function isArchivingDisabledFor(Piwik_Segment $segment, $periodLabel)
    {
        if ($periodLabel == 'range') {
            return false;
        }
        $processOneReportOnly = !self::shouldProcessReportsAllPlugins($segment, $periodLabel);
        $isArchivingDisabled = !self::isRequestAuthorizedToArchive();

        if ($processOneReportOnly) {
            // When there is a segment, archiving is not necessary allowed
            // If browser archiving is allowed, then archiving is enabled
            // if browser archiving is not allowed, then archiving is disabled
            if (!$segment->isEmpty()
                && $isArchivingDisabled
                && Piwik_Config::getInstance()->General['browser_archiving_disabled_enforce']
            ) {
                Piwik::log("Archiving is disabled because of config setting browser_archiving_disabled_enforce=1");
                return true;
            }
            return false;
        }
        return $isArchivingDisabled;
    }

    protected static function isRequestAuthorizedToArchive()
    {
        return !self::$archivingDisabledByTests &&
            (Piwik_ArchiveProcessor_Rules::isBrowserTriggerEnabled()
                || Piwik_Common::isPhpCliMode()
                || (Piwik::isUserIsSuperUser()
                    && Piwik_Common::isArchivePhpTriggered()));
    }

    public static function isBrowserTriggerEnabled()
    {
        $browserArchivingEnabled = Piwik_GetOption(self::OPTION_BROWSER_TRIGGER_ARCHIVING);
        if ($browserArchivingEnabled !== false) {
            return (bool)$browserArchivingEnabled;
        }
        return (bool)Piwik_Config::getInstance()->General['enable_browser_archiving_triggering'];
    }

    public static function setBrowserTriggerArchiving($enabled)
    {
        if (!is_bool($enabled)) {
            throw new Exception('Browser trigger archiving must be set to true or false.');
        }
        Piwik_SetOption(self::OPTION_BROWSER_TRIGGER_ARCHIVING, (int)$enabled, $autoLoad = true);
        Piwik_Tracker_Cache::clearCacheGeneral();
    }
}