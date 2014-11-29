<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\CronArchive;

use Exception;
use Piwik\CronArchive;
use Piwik\Profiler;

/**
 * Contains all editable options for the CronArchive algorithm. Each option has a corresponding
 * command line option in the core:archive command.
 */
class AlgorithmOptions extends Hooks
{
    // Flag to know when the archive cron is calling the API
    const APPEND_TO_API_REQUEST = '&trigger=archivephp';

    /**
     * true if running CronArchive in automated tests.
     *
     * @var bool
     */
    public $testmode = false;

    /**
     * The list of IDs for sites for whom archiving should be initiated. If not empty, only these
     * sites will be archived.
     *
     * @var int[]
     */
    public $shouldArchiveSpecifiedSites = array();

    /**
     * The list of IDs of sites to ignore when launching archiving. Archiving will not be launched
     * for any site whose ID is in this list (even if the ID is supplied in {@link $shouldArchiveSpecifiedSites}
     * or if {@link $shouldArchiveAllSites} is true).
     *
     * @var int[]
     */
    public $shouldSkipSpecifiedSites = array();

    /**
     * If true, archiving will be launched for every site.
     *
     * @var bool
     */
    public $shouldArchiveAllSites = false;

    /**
     * If true, xhprof will be initiated for the archiving run. Only for development/testing.
     *
     * @var bool
     */
    public $shouldStartProfiler = false;

    /**
     * If HTTP requests are used to initiate archiving, this controls whether invalid SSL certificates should
     * be accepted or not by each request.
     *
     * @var bool
     */
    public $acceptInvalidSSLCertificate = false;

    /**
     * If set to true, scheduled tasks will not be run.
     *
     * @var bool
     */
    public $disableScheduledTasks = false;

    /**
     * The amount of seconds between non-day period archiving. That is, if archiving has been launched within
     * the past [$forceTimeoutPeriod] seconds, Piwik will not initiate archiving for week, month and year periods.
     *
     * @var int|false
     */
    public $forceTimeoutPeriod = false;

    /**
     * If supplied, archiving will be launched for sites that have had visits within the last [$shouldArchiveAllPeriodsSince]
     * seconds. If set to `true`, the value defaults to {@link ARCHIVE_SITES_WITH_TRAFFIC_SINCE}.
     *
     * @var int|bool
     */
    public $shouldArchiveAllPeriodsSince = false;

    /**
     * If supplied, archiving will be launched only for periods that fall within this date range. For example,
     * `"2012-01-01,2012-03-15"` would result in January 2012, February 2012 being archived but not April 2012.
     *
     * @var string|false eg, `"2012-01-01,2012-03-15"`
     */
    public $restrictToDateRange = false;

    /**
     * A list of periods to launch archiving for. By default, day, week, month and year periods
     * are considered. This variable can limit the periods to, for example, week & month only.
     *
     * @var string[] eg, `array("day","week","month","year")`
     */
    public $restrictToPeriods = array();

    /**
     * Forces CronArchive to retrieve data for the last [$dateLastForced] periods when initiating archiving.
     * When archiving weeks, for example, if 10 is supplied, the API will be called w/ last10. This will potentially
     * initiate archiving for the last 10 weeks.
     *
     * @var int|false
     */
    public $dateLastForced = false;

    /**
     * Add extra query parameters to a request URL based on the options set for CronArchive.
     *
     * @param string $url The URL to modify.
     * @return string The modified URL.
     */
    public function getProcessedUrl($url)
    {
        if ($this->shouldStartProfiler) {
            if (is_array($url)) {
                $url['xhprof'] = '2';
            } else {
                $url .= "&xhprof=2";
            }
        }

        if ($this->testmode) {
            if (is_array($url)) {
                $url['testmode'] = '1';
            } else {
                $url .= "&testmode=1";
            }
        }

        if (is_array($url)) {
            $url['trigger'] = 'archivephp';
        } else {
            $url .= self::APPEND_TO_API_REQUEST;
        }

        return $url;
    }

    /**
     * Returns true if the specified site should be skipped based on the CronArchive options set. Returns
     * false if otherwise.
     *
     * @param int $idSite
     * @return bool
     */
    public function shouldSkipWebsite($idSite)
    {
        return in_array($idSite, $this->shouldSkipSpecifiedSites);
    }

    public function onInit(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger)
    {
        if ($this->shouldStartProfiler) {
            Profiler::setupProfilerXHProf($mainRun = true);

            $logger->log("XHProf profiling is enabled.");
        }
    }

    public function onRulePropertyComputed(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger, $getterName, $idSite, &$value)
    {
        switch ($getterName) {
            case 'getIsWebsiteArchivingForced':
                $this->setIsWebsiteArchivingForced($idSite, $value);
                break;
            case 'getProcessPeriodsMaximumEverySeconds':
                $this->applyForcePeriodTimeout($state, $logger, $value);
                break;
            case 'getShouldArchivePeriodsOnlyForSitesWithTrafficSinceLastNSecs':
                $this->applyShouldArchiveAllPeriodsSince($value);
                break;
            case 'getPeriodsToProcess':
                $this->applyRestrictToPeriods($value);
                break;
            case 'getForcedWebsitesToArchive':
                $this->setForcedWebsitesToArchive($state, $value);
                break;
            case 'getForcedDateRangeToProcess':
                $this->setForcedDateRangeToProcess($value);
                break;
            case 'getLastTimestampWebsiteProcessedDay':
                $this->unsetLastTimestampWebsiteProcessedDayIfForceAllWebsites($value);
                break;
        }
    }

    private function setIsWebsiteArchivingForced($idSite, &$value)
    {
        if (empty($this->shouldArchiveSpecifiedSites)) {
            return;
        }

        $value = in_array($idSite, $this->shouldArchiveSpecifiedSites);
    }

    private function applyForcePeriodTimeout(AlgorithmRules $rules, AlgorithmLogger $logger, &$value)
    {
        if (empty($this->forceTimeoutPeriod)) {
            return;
        }

        // Ensure the cache for periods is at least as high as cache for today
        if ($rules->getTodayArchiveTimeToLive() > $this->forceTimeoutPeriod) {
            $value = $rules->getTodayArchiveTimeToLive();

            $logger->log("WARNING: Automatically increasing --force-timeout-for-periods from {$this->forceTimeoutPeriod} to "
                . $rules->getTodayArchiveTimeToLive()
                . " to match the cache timeout for Today's report specified in Piwik UI > Settings > General Settings");
        } else {
            $value = $this->forceTimeoutPeriod;
        }
    }

    private function applyShouldArchiveAllPeriodsSince(&$value)
    {
        if (empty($this->shouldArchiveAllPeriodsSince)) {
            return;
        }

        if (is_numeric($this->shouldArchiveAllPeriodsSince)
            && $this->shouldArchiveAllPeriodsSince > 1
        ) {
            $value = (int)$this->shouldArchiveAllPeriodsSince;
        } else {
            $value = true;
        }
    }

    private function applyRestrictToPeriods(&$value)
    {
        if (empty($this->restrictToPeriods)) {
            return;
        }

        $value = array_intersect($this->restrictToPeriods, $value);
    }

    private function setForcedWebsitesToArchive(AlgorithmRules $rules, &$value)
    {
        if (count($this->shouldArchiveSpecifiedSites) > 0) {
            $value = $this->shouldArchiveSpecifiedSites;
        } else if ($this->shouldArchiveAllSites) {
            $value = $rules->getAllWebsites();
        }
    }

    private function setForcedDateRangeToProcess(&$value)
    {
        if (empty($this->restrictToDateRange)) {
            return;
        }

        if (strpos($this->restrictToDateRange, ',') === false) {
            throw new Exception("--force-date-range expects a date range ie. YYYY-MM-DD,YYYY-MM-DD");
        }

        $value = $this->restrictToDateRange;
    }

    private function unsetLastTimestampWebsiteProcessedDayIfForceAllWebsites(&$value)
    {
        // when --force-all-websites option,
        // also forces to archive last52 days to be safe
        if ($this->shouldArchiveAllSites) {
            $value = false;
        }
    }
}