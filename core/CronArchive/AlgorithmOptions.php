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

/**
 * Contains all editable options for the CronArchive algorithm. Each option has a corresponding
 * command line option in the core:archive command.
 */
class AlgorithmOptions
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
     * The list of IDs for sites for whom archiving should be initiated. If supplied, only these
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
            $url .= "&xhprof=2";
        }
        if ($this->testmode) {
            $url .= "&testmode=1";
        }
        $url .= self::APPEND_TO_API_REQUEST;
        return $url;
    }

    /**
     * Returns the date range to restrict archiving to.
     *
     * @return false|string false if there is no restriction, or a date range if there is.
     * @throws Exception if the date range is non-empty and has no comma.
     */
    public function getDateRangeToProcess()
    {
        if (empty($this->restrictToDateRange)) {
            return false;
        }

        if (strpos($this->restrictToDateRange, ',') === false) {
            throw new Exception("--force-date-range expects a date range ie. YYYY-MM-DD,YYYY-MM-DD");
        }

        return $this->restrictToDateRange;
    }
}