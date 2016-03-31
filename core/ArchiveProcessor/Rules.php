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
use Piwik\DataAccess\ArchiveWriter;
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
     * @return string
     */
    public static function getDoneStringFlagFor(array $idSites, $segment, $periodLabel, $plugin)
    {
        if (!self::shouldProcessReportsAllPlugins($idSites, $segment, $periodLabel)) {
            return self::getDoneFlagArchiveContainsOnePlugin($segment, $plugin);
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

    public static function getDoneFlagArchiveContainsOnePlugin(Segment $segment, $plugin)
    {
        return 'done' . $segment->getHash() . '.' . $plugin ;
    }

    public static function getDoneFlagArchiveContainsAllPlugins(Segment $segment)
    {
        return 'done' . $segment->getHash();
    }

    /**
     * Return done flags used to tell how the archiving process for a specific archive was completed,
     *
     * @param array $plugins
     * @param $segment
     * @return array
     */
    public static function getDoneFlags(array $plugins, Segment $segment)
    {
        $doneFlags = array();
        $doneAllPlugins = self::getDoneFlagArchiveContainsAllPlugins($segment);
        $doneFlags[$doneAllPlugins] = $doneAllPlugins;

        $plugins = array_unique($plugins);
        foreach ($plugins as $plugin) {
            $doneOnePlugin = self::getDoneFlagArchiveContainsOnePlugin($segment, $plugin);
            $doneFlags[$plugin] = $doneOnePlugin;
        }
        return $doneFlags;
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
        return self::getTodayArchiveTimeToLiveDefault();
    }

    public static function getTodayArchiveTimeToLiveDefault()
    {
        return Config::getInstance()->General['time_before_today_archive_considered_outdated'];
    }

    public static function isArchivingDisabledFor(array $idSites, Segment $segment, $periodLabel)
    {
        $generalConfig = Config::getInstance()->General;

        if ($periodLabel == 'range') {
            if (!isset($generalConfig['archiving_range_force_on_browser_request'])
                || $generalConfig['archiving_range_force_on_browser_request'] != false
            ) {
                return false;
            }

            Log::debug("Not forcing archiving for range period.");
            $processOneReportOnly = false;

        } else {
            $processOneReportOnly = !self::shouldProcessReportsAllPlugins($idSites, $segment, $periodLabel);
        }

        $isArchivingEnabled = self::isRequestAuthorizedToArchive() && !self::$archivingDisabledByTests;

        if ($processOneReportOnly)  {
            // When there is a segment, we disable archiving when browser_archiving_disabled_enforce applies
            if (!$segment->isEmpty()
                && !$isArchivingEnabled
                && $generalConfig['browser_archiving_disabled_enforce']
                && !SettingsServer::isArchivePhpTriggered() // Only applies when we are not running core:archive command
            ) {
                Log::debug("Archiving is disabled because of config setting browser_archiving_disabled_enforce=1");
                return true;
            }

            // Always allow processing one report
            return false;
        }

        return !$isArchivingEnabled;
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

        return in_array($segment, $segmentsToProcess)
            || in_array($segment, $segmentsToProcessUrlDecoded);
    }

    /**
     * Returns done flag values allowed to be selected
     *
     * @return string
     */
    public static function getSelectableDoneFlagValues()
    {
        $possibleValues = array(ArchiveWriter::DONE_OK, ArchiveWriter::DONE_OK_TEMPORARY);

        if (!Rules::isRequestAuthorizedToArchive()) {
            //If request is not authorized to archive then fetch also invalidated archives
            $possibleValues[] = ArchiveWriter::DONE_INVALIDATED;
        }

        return $possibleValues;
    }
}
