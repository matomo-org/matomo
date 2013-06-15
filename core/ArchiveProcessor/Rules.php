<?php
/**
 * The class that rules the ArchiveProcessor
 */

class Piwik_ArchiveProcessor_Rules
{

    const OPTION_TODAY_ARCHIVE_TTL = 'todayArchiveTimeToLive';
    const OPTION_BROWSER_TRIGGER_ARCHIVING = 'enableBrowserTriggerArchiving';
    static public function isBrowserTriggerEnabled()
    {
        $browserArchivingEnabled = Piwik_GetOption(self::OPTION_BROWSER_TRIGGER_ARCHIVING);
        if ($browserArchivingEnabled !== false) {
            return (bool)$browserArchivingEnabled;
        }
        return (bool)Piwik_Config::getInstance()->General['enable_browser_archiving_triggering'];
    }
    public static function getTodayArchiveTimeToLive()
    {
        $timeToLive = Piwik_GetOption(self::OPTION_TODAY_ARCHIVE_TTL);
        if ($timeToLive !== false) {
            return $timeToLive;
        }
        return Piwik_Config::getInstance()->General['time_before_today_archive_considered_outdated'];
    }

    public static function setBrowserTriggerArchiving($enabled)
    {
        if (!is_bool($enabled)) {
            throw new Exception('Browser trigger archiving must be set to true or false.');
        }
        Piwik_SetOption(self::OPTION_BROWSER_TRIGGER_ARCHIVING, (int)$enabled, $autoload = true);
        Piwik_Tracker_Cache::clearCacheGeneral();
    }

    // Old Archives purge can be disabled (used in tests only)
    static public $purgeDisabledByTests = true;

    // Flag that will forcefully disable the archiving process (used in tests only)
    public static $archivingDisabledByTests = false;

    const FLAG_TABLE_PURGED = 'lastPurge_';

    protected static function isRequestAuthorizedToArchive()
    {
        return !self::$archivingDisabledByTests &&
            (Piwik_ArchiveProcessor_Rules::isBrowserTriggerEnabled()
                || Piwik_Common::isPhpCliMode()
                || (Piwik::isUserIsSuperUser()
                    && Piwik_Common::isArchivePhpTriggered()));
    }

    static public function shouldProcessReportsAllPlugins(Piwik_Segment $segment, $periodLabel)
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

    public static function getDoneFlagArchiveContainsAllPlugins($segment)
    {
        return 'done' . $segment->getHash();
    }

    public static function getDoneFlagArchiveContainsOnePlugin($segment, $plugin)
    {
        return 'done' . $segment->getHash() . '.' . $plugin;
    }

    /**
     * Given a monthly archive table, will delete all reports that are now outdated,
     * or reports that ended with an error
     */
    static public function doPurgeOutdatedArchives($numericTable)
    {
        if (self::$purgeDisabledByTests) {
            return;
        }
        $blobTable = str_replace("numeric", "blob", $numericTable);
        $key = self::FLAG_TABLE_PURGED . $blobTable;
        $timestamp = Piwik_GetOption($key);

        // we shall purge temporary archives after their timeout is finished, plus an extra 6 hours
        // in case archiving is disabled or run once a day, we give it this extra time to run
        // and re-process more recent records...
        // TODO: Instead of hardcoding 6 we should put the actual number of hours between 2 archiving runs
        $temporaryArchivingTimeout = self::getTodayArchiveTimeToLive();
        $purgeEveryNSeconds = max($temporaryArchivingTimeout, 6 * 3600);

        // we only delete archives if we are able to process them, otherwise, the browser might process reports
        // when &segment= is specified (or custom date range) and would below, delete temporary archives that the
        // browser is not able to process until next cron run (which could be more than 1 hour away)
        if (self::isRequestAuthorizedToArchive()
            && (!$timestamp
                || $timestamp < time() - $purgeEveryNSeconds)
        ) {
            Piwik_SetOption($key, time());

            // If Browser Archiving is enabled, it is likely there are many more temporary archives
            // We delete more often which is safe, since reports are re-processed on demand
            if (self::isBrowserTriggerEnabled()) {
                $purgeArchivesOlderThan = Piwik_Date::factory(time() - 2 * $temporaryArchivingTimeout)->getDateTime();
            } // If archive.php via Cron is building the reports, we should keep all temporary reports from today
            else {
                $purgeArchivesOlderThan = Piwik_Date::factory('today')->getDateTime();
            }
            Piwik_DataAccess_Archiver::purgeOutdatedArchives($numericTable, $blobTable, $purgeArchivesOlderThan);

            // these tables will be OPTIMIZEd daily in a scheduled task, to claim lost space
        } else {
            Piwik::log("Purging temporary archives: skipped.");
        }
    }


    public static function setTodayArchiveTimeToLive($timeToLiveSeconds)
    {
        $timeToLiveSeconds = (int)$timeToLiveSeconds;
        if ($timeToLiveSeconds <= 0) {
            throw new Exception(Piwik_TranslateException('General_ExceptionInvalidArchiveTimeToLive'));
        }
        Piwik_SetOption(self::OPTION_TODAY_ARCHIVE_TTL, $timeToLiveSeconds, $autoload = true);
    }
}