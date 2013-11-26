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
namespace Piwik;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\CoreAdminHome\API as APICoreAdminHome;
use Exception;

/**
 * archive.php runs as a cron and is a useful tool for general maintenance,
 * and pre-process reports for a Fast dashboard rendering.
 */
class CronArchive
{
    static public function getUsage()
    {
        return "Usage:
	/path/to/cli/php \"" . @$_SERVER['argv'][0] . "\" --url=http://your-website.org/path/to/piwik/ [arguments]

Arguments:
	--url=[piwik-server-url]
			Mandatory argument. Must be set to the Piwik base URL.
			For example: --url=http://analytics.example.org/ or --url=https://example.org/piwik/
	--force-all-websites
			If specified, the script will trigger archiving on all websites and all past dates.
			You may use --force-all-periods=[seconds] to trigger archiving on those websites that had visits in the last [seconds] seconds.
	--force-all-periods[=seconds]
			Limits archiving to websites with some traffic in the last [seconds] seconds.
			For example --force-all-periods=86400 will archive websites that had visits in the last 24 hours.
			If [seconds] is not specified, all websites will visits in the last ". CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE
            . " seconds (" . round( CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE/86400 ) ." days) will be archived.
	--force-timeout-for-periods=[seconds]
			The current week/ current month/ current year will be processed at most every [seconds].
			If not specified, defaults to ". CronArchive::SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES.".
    --force-date-last-n=M
            This script calls the API with period=lastN. You can force the N in lastN by specifying this value.
	--force-idsites=1,2,n
			Restricts archiving to the specified website IDs, comma separated list.
	--skip-idsites=1,2,n
			If the specified websites IDs were to be archived, skip them instead.
	--disable-scheduled-tasks
	        Skips executing Scheduled tasks (sending scheduled reports, db optimization, etc.).
	--xhprof
			Enables XHProf profiler for this archive.php run. Requires XHPRof (see tests/README.xhprof.md).
	--accept-invalid-ssl-certificate
			It is _NOT_ recommended to use this argument. Instead, you should use a valid SSL certificate!
			It can be useful if you specified --url=https://... or if you are using Piwik with force_ssl=1
	--help
			Displays usage

Notes:
	* It is recommended to run the script with the argument --url=[piwik-server-url] only. Other arguments are not required.
	* This script should be executed every hour via crontab, or as a deamon.
	* You can also run it via http:// by specifying the Super User &token_auth=XYZ as a parameter ('Web Cron'),
	  but it is recommended to run it via command line/CLI instead.
	* If you have any suggestion about this script, please let the team know at hello@piwik.org
	* Enjoy!
";
    }

    // the url can be set here before the init, and it will be used instead of --url=
    static public $url = false;

    // force-timeout-for-periods default (1 hour)
    const SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES = 3600;

    // force-all-periods default (7 days)
    const ARCHIVE_SITES_WITH_TRAFFIC_SINCE = 604800;

    // By default, will process last 52 days and months
    // It will be overwritten by the number of days since last archiving ran until completion.
    const DEFAULT_DATE_LAST = 52;

    // Since weeks are not used in yearly archives, we make sure that all possible weeks are processed
    const DEFAULT_DATE_LAST_WEEKS = 260;

    const DEFAULT_DATE_LAST_YEARS = 7;

    // Flag to know when the archive cron is calling the API
    const APPEND_TO_API_REQUEST = '&trigger=archivephp';

    // Flag used to record timestamp in Option::
    const OPTION_ARCHIVING_FINISHED_TS = "LastCompletedFullArchiving";

    // Show only first N characters from Piwik API output in case of errors
    const TRUNCATE_ERROR_MESSAGE_SUMMARY = 4000 ;

    // archiving  will be triggered on all websites with traffic in the last $shouldArchiveOnlySitesWithTrafficSince seconds
    private $shouldArchiveOnlySitesWithTrafficSince;

    // By default, we only process the current week/month/year at most once an hour
    private $processPeriodsMaximumEverySeconds;
    private $todayArchiveTimeToLive;
    private $websiteDayHasFinishedSinceLastRun = array();
    private $idSitesInvalidatedOldReports = array();
    private $shouldArchiveSpecifiedSites = array();
    private $shouldSkipSpecifiedSites = array();
    private $websites = array();
    private $allWebsites = array();
    private $segments = array();
    private $piwikUrl = false;
    private $token_auth = false;
    private $visits = 0;
    private $requests = 0;
    private $output = '';
    private $archiveAndRespectTTL = true;
    private $shouldArchiveAllSites = false;
    private $shouldStartProfiler = false;
    private $acceptInvalidSSLCertificate = false;
    private $lastSuccessRunTimestamp = false;
    private $errors = array();

    /**
     * Returns the option name of the option that stores the time the archive.php script was last run.
     *
     * @param int $idsite
     * @param string $period
     * @return string
     */
    static public function lastRunKey($idsite, $period)
    {
        return "lastRunArchive" . $period . "_" . $idsite;
    }

    public function init()
    {
        // Note: the order of methods call matters here.
        $this->displayHelp();
        $this->initPiwikHost();
        $this->initLog();
        $this->initCore();
        $this->initTokenAuth();
        $this->initCheckCli();
        $this->initStateFromParameters();
        Piwik::setUserIsSuperUser(true);

        $this->logInitInfo();
        $this->checkPiwikUrlIsValid();
        $this->logArchiveTimeoutInfo();

        $this->segments = $this->initSegmentsToArchive();
        $this->allWebsites = APISitesManager::getInstance()->getAllSitesId();
        $websitesIds = $this->initWebsiteIds();
        $this->filterWebsiteIds($websitesIds);
        $this->websites = $websitesIds;

        if($this->shouldStartProfiler) {
            \Piwik\Profiler::setupProfilerXHProf($mainRun = true);
            $this->log("XHProf profiling is enabled.");
        }
    }

    /**
     * Main function, runs archiving on all websites with new activity
     */
    public function run()
    {
        $websitesWithVisitsSinceLastRun =
        $skippedPeriodsArchivesWebsite =
        $skippedDayArchivesWebsites =
        $skipped =
        $processed =
        $archivedPeriodsArchivesWebsite = 0;
        $timer = new Timer;

        $this->logSection("START");
        foreach ($this->websites as $idsite) {
            flush();
            $requestsBefore = $this->requests;
            if ($idsite <= 0) {
                continue;
            }

            $skipWebsiteForced = in_array($idsite, $this->shouldSkipSpecifiedSites);
            if($skipWebsiteForced) {
                $this->log("Skipped website id $idsite, found in --skip-idsites ");
                $skipped++;
                continue;
            }

            $timerWebsite = new Timer;

            $lastTimestampWebsiteProcessedPeriods = $lastTimestampWebsiteProcessedDay = false;
            if ($this->archiveAndRespectTTL) {
                $lastTimestampWebsiteProcessedPeriods = Option::get($this->lastRunKey($idsite, "periods"));
                $lastTimestampWebsiteProcessedDay = Option::get($this->lastRunKey($idsite, "day"));
            }

            // For period other than days, we only re-process the reports at most
            // 1) every $processPeriodsMaximumEverySeconds
            $secondsSinceLastExecution = time() - $lastTimestampWebsiteProcessedPeriods;

            // if timeout is more than 10 min, we account for a 5 min processing time, and allow trigger 1 min earlier
            if ($this->processPeriodsMaximumEverySeconds > 10 * 60) {
                $secondsSinceLastExecution += 5 * 60;
            }
            $shouldArchivePeriods = $secondsSinceLastExecution > $this->processPeriodsMaximumEverySeconds;
            if (empty($lastTimestampWebsiteProcessedPeriods)) {
                // 2) OR always if script never executed for this website before
                $shouldArchivePeriods = true;
            }

            // (*) If the website is archived because it is a new day in its timezone
            // We make sure all periods are archived, even if there is 0 visit today
            $dayHasEndedMustReprocess = in_array($idsite, $this->websiteDayHasFinishedSinceLastRun);
            if ($dayHasEndedMustReprocess) {
                $shouldArchivePeriods = true;
            }

            // (*) If there was some old reports invalidated for this website
            // we make sure all these old reports are triggered at least once
            $websiteIsOldDataInvalidate = in_array($idsite, $this->idSitesInvalidatedOldReports);
            if ($websiteIsOldDataInvalidate) {
                $shouldArchivePeriods = true;
            }

            $websiteIdIsForced = in_array($idsite, $this->shouldArchiveSpecifiedSites);
            if($websiteIdIsForced) {
                $shouldArchivePeriods = true;
            }

            // Test if we should process this website at all
            $elapsedSinceLastArchiving = time() - $lastTimestampWebsiteProcessedDay;

            // Skip this day archive if last archive was older than TTL
            $existingArchiveIsValid = ($elapsedSinceLastArchiving < $this->todayArchiveTimeToLive);

            $skipDayArchive = $existingArchiveIsValid;

            // Invalidate old website forces the archiving for this site
            $skipDayArchive = $skipDayArchive && !$websiteIsOldDataInvalidate;

            // Also reprocess when day has ended since last run
            if($dayHasEndedMustReprocess
                && !$existingArchiveIsValid) {
                $skipDayArchive = false;
            }

            if($websiteIdIsForced) {
                $skipDayArchive = false;
            }

             if ($skipDayArchive) {
                $this->log("Skipped website id $idsite, already processed today's report in recent run, "
                    . \Piwik\MetricsFormatter::getPrettyTimeFromSeconds($elapsedSinceLastArchiving, true, $isHtml = false)
                    . " ago, " . $timerWebsite->__toString());
                $skippedDayArchivesWebsites++;
                $skipped++;
                continue;
            }

            // Fake that the request is already done, so that other archive.php
            // running do not grab the same website from the queue
            Option::set($this->lastRunKey($idsite, "day"), time());

            // when some data was purged from this website
            // we make sure we query all previous days/weeks/months
            $processDaysSince = $lastTimestampWebsiteProcessedDay;
            if($websiteIsOldDataInvalidate
                // when --force-all-websites option,
                // also forces to archive last52 days to be safe
                || $this->shouldArchiveAllSites) {
                $processDaysSince = false;
            }

            $url = $this->getVisitsRequestUrl($idsite, "day", $processDaysSince);
            $content = $this->request($url);
            $response = @unserialize($content);

            if (empty($content)
                || !is_array($response)
                || count($response) == 0
            ) {
                // cancel the succesful run flag
                Option::set($this->lastRunKey($idsite, "day"), 0);

                $this->log("WARNING: Empty or invalid response '$content' for website id $idsite, " . $timerWebsite->__toString() . ", skipping");
                $skipped++;
                continue;
            }
            $visitsToday = end($response);
            if(empty($visitsToday)) {
                $visitsToday = 0;
            }
            $this->requests++;
            $processed++;

            // If there is no visit today and we don't need to process this website, we can skip remaining archives
            if ($visitsToday == 0
                && !$shouldArchivePeriods
            ) {
                $this->log("Skipped website id $idsite, no visit today, " . $timerWebsite->__toString());
                $skipped++;
                continue;
            }

            $visitsAllDays = array_sum($response);
            if ($visitsAllDays == 0
                && !$shouldArchivePeriods
                && $this->shouldArchiveAllSites
            ) {
                $this->log("Skipped website id $idsite, no visits in the last " . count($response) . " days, " . $timerWebsite->__toString());
                $skipped++;
                continue;
            }
            $this->visits += $visitsToday;
            $websitesWithVisitsSinceLastRun++;
            $this->archiveVisitsAndSegments($idsite, "day", $lastTimestampWebsiteProcessedDay, $timerWebsite);

            if (!$shouldArchivePeriods) {
                $this->log("Skipped website id $idsite, already processed period reports in recent run, "
                    . \Piwik\MetricsFormatter::getPrettyTimeFromSeconds($elapsedSinceLastArchiving, true, $isHtml = false)
                    . " ago, " . $timerWebsite->__toString());
                $skippedDayArchivesWebsites++;
                $skipped++;
                continue;
            }

            $success = true;
            foreach (array('week', 'month', 'year') as $period) {
                $success = $this->archiveVisitsAndSegments($idsite, $period, $lastTimestampWebsiteProcessedPeriods)
                    && $success;
            }
            // Record succesful run of this website's periods archiving
            if ($success) {
                Option::set($this->lastRunKey($idsite, "periods"), time());

                // Remove this website from the list of websites to be invalidated
                // since it's now just been re-processing the reports, job is done!
                if ($websiteIsOldDataInvalidate) {
                    $this->setSiteIsArchived($idsite);
                }
            }
            $archivedPeriodsArchivesWebsite++;

            $requestsWebsite = $this->requests - $requestsBefore;
            $debug = $this->shouldArchiveAllSites ? ", last days = $visitsAllDays visits" : "";
            Log::info("Archived website id = $idsite, today = $visitsToday visits"
                . $debug . ", $requestsWebsite API requests, "
                . $timerWebsite->__toString()
                . " [" . ($websitesWithVisitsSinceLastRun + $skipped) . "/"
                . count($this->websites)
                . " done]");

        }
        $this->log("Starting Piwik reports archiving...");


        $this->log("Done archiving!");

        $this->logSection("SUMMARY");
        $this->log("Total daily visits archived: " . $this->visits);

        $totalWebsites = count($this->allWebsites);
        $skipped = $totalWebsites - $websitesWithVisitsSinceLastRun;
        $this->log("Archived today's reports for $websitesWithVisitsSinceLastRun websites");
        $this->log("Archived week/month/year for $archivedPeriodsArchivesWebsite websites");
        $this->log("Skipped $skipped websites: no new visit since the last script execution");
        $this->log("Skipped $skippedDayArchivesWebsites websites day archiving: existing daily reports are less than {$this->todayArchiveTimeToLive} seconds old");
        $this->log("Skipped $skippedPeriodsArchivesWebsite websites week/month/year archiving: existing periods reports are less than {$this->processPeriodsMaximumEverySeconds} seconds old");
        $this->log("Total API requests: $this->requests");

        //DONE: done/total, visits, wtoday, wperiods, reqs, time, errors[count]: first eg.
        $percent = count($this->websites) == 0
            ? ""
            : " " . round($processed * 100 / count($this->websites), 0) . "%";
        $this->log("done: " .
            $processed . "/" . count($this->websites) . "" . $percent . ", " .
            $this->visits . " v, $websitesWithVisitsSinceLastRun wtoday, $archivedPeriodsArchivesWebsite wperiods, " .
            $this->requests . " req, " . round($timer->getTimeMs()) . " ms, " .
            (empty($this->errors)
                ? "no error"
                : (count($this->errors) . " errors. eg. '" . reset($this->errors) . "'"))
        );
        $this->log($timer->__toString());

    }

    /**
     * End of the script
     */
    public function end()
    {
        // How to test the error handling code?
        // - Generate some hits since last archive.php run
        // - Start the script, in the middle, shutdown apache, then restore
        // Some errors should be logged and script should successfully finish and then report the errors and trigger a PHP error
        if (!empty($this->errors)) {
            $this->logSection("SUMMARY OF ERRORS");

            foreach ($this->errors as $error) {
                $this->log("Error: " . $error);
            }
            $summary = count($this->errors) . " total errors during this script execution, please investigate and try and fix these errors";
            $this->log($summary);

            $summary .= '. First error was: ' . reset($this->errors);
            $this->logFatalError($summary);
        } else {
            // No error -> Logs the successful script execution until completion
            Option::set(self::OPTION_ARCHIVING_FINISHED_TS, time());
        }
    }

    public function logFatalError($m, $backtrace = true)
    {
        $this->logError($m);
        $fe = fopen('php://stderr', 'w');
        fwrite($fe, "Error in the last Piwik archive.php run: \n" . $m . "\n"
            . ($backtrace ? "\n\n Here is the full errors output:\n\n" . $this->output : '')
        );
        exit(1);
    }

    public function runScheduledTasks()
    {
        $this->logSection("SCHEDULED TASKS");
        if($this->isParameterSet('--disable-scheduled-tasks')) {
            $this->log("Scheduled tasks are disabled with --disable-scheduled-tasks");
            return;
        }
        $this->log("Starting Scheduled tasks... ");

        $tasksOutput = $this->request("?module=API&method=CoreAdminHome.runScheduledTasks&format=csv&convertToUnicode=0&token_auth=" . $this->token_auth);
        if ($tasksOutput == \Piwik\DataTable\Renderer\Csv::NO_DATA_AVAILABLE) {
            $tasksOutput = " No task to run";
        }
        $this->log($tasksOutput);
        $this->log("done");
        $this->logSection("");
    }

    /**
     * Checks the config file is found.
     *
     * @param $piwikUrl
     * @throws Exception
     */
    protected function initConfigObject($piwikUrl)
    {
        // HOST is required for the Config object
        $parsed = parse_url($piwikUrl);
        Url::setHost($parsed['host']);

        Config::getInstance()->clear();

        try {
            Config::getInstance()->checkLocalConfigFound();
        } catch (Exception $e) {
            throw new Exception("The configuration file for Piwik could not be found. " .
                "Please check that config/config.ini.php is readable by the user " .
                get_current_user());
        }
    }

    /**
     * Returns base URL to process reports for the $idsite on a given $period
     */
    private function getVisitsRequestUrl($idsite, $period, $lastTimestampWebsiteProcessed = false)
    {
        $dateLastMax = self::DEFAULT_DATE_LAST;
        if($period=='year') {
            $dateLastMax = self::DEFAULT_DATE_LAST_YEARS;
        } elseif($period == 'week') {
            $dateLastMax = self::DEFAULT_DATE_LAST_WEEKS;
        }
        if (empty($lastTimestampWebsiteProcessed)) {
            $lastTimestampWebsiteProcessed = strtotime( \Piwik\Site::getCreationDateFor($idsite) );
        }

        // Enforcing last2 at minimum to work around timing issues and ensure we make most archives available
        $dateLast = floor((time() - $lastTimestampWebsiteProcessed) / 86400) + 2;
        if ($dateLast > $dateLastMax) {
            $dateLast = $dateLastMax;
        }

        $dateLastForced = $this->isParameterSet('--force-date-last-n', true);
        if(!empty($dateLastForced)){
            $dateLast = $dateLastForced;
        }

        return "?module=API&method=VisitsSummary.getVisits&idSite=$idsite&period=$period&date=last" . $dateLast . "&format=php&token_auth=" . $this->token_auth;
    }

    private function initSegmentsToArchive()
    {
        $segments = APICoreAdminHome::getInstance()->getKnownSegmentsToArchive();
        if (empty($segments)) {
            return array();
        }
        $this->log("- Will pre-process " . count($segments) . " Segments for each website and each period: " . implode(", ", $segments));
        return $segments;
    }

    private function getSegmentsForSite($idsite)
    {
        $segmentsAllSites = $this->segments;
        $segmentsThisSite = \Piwik\SettingsPiwik::getKnownSegmentsToArchiveForSite($idsite);
        if (!empty($segmentsThisSite)) {
            $this->log("Will pre-process the following " . count($segmentsThisSite) . " Segments for this website (id = $idsite): " . implode(", ", $segmentsThisSite));
        }
        $segments = array_unique(array_merge($segmentsAllSites, $segmentsThisSite));
        return $segments;
    }

    /**
     * Will trigger API requests for the specified Website $idsite,
     * for the specified $period, for all segments that are pre-processed for this website.
     * Requests are triggered using cURL multi handle
     *
     * @param $idsite int
     * @param $period
     * @param $lastTimestampWebsiteProcessed
     * @param Timer $timerWebsite
     * @return bool True on success, false if some request failed
     */
    private function archiveVisitsAndSegments($idsite, $period, $lastTimestampWebsiteProcessed, Timer $timerWebsite = null)
    {
        $timer = new Timer;
        $aCurl = array();
        $mh = false;
        $url = $this->piwikUrl;
        $url .= $this->getVisitsRequestUrl($idsite, $period, $lastTimestampWebsiteProcessed);
        $url .= self::APPEND_TO_API_REQUEST;

        // already processed above for "day"
        if ($period != "day") {
            $ch = $this->getNewCurlHandle($url);
            $this->addCurlHandleToMulti($mh, $ch);
            $aCurl[$url] = $ch;
            $this->requests++;
        }
        $urlNoSegment = $url;
        foreach ($this->getSegmentsForSite($idsite) as $segment) {
            $segmentUrl = $url . '&segment=' . urlencode($segment);
            $ch = $this->getNewCurlHandle($segmentUrl);
            $this->addCurlHandleToMulti($mh, $ch);
            $aCurl[$segmentUrl] = $ch;
            $this->requests++;
        }

        $success = true;
        $visitsAllDaysInPeriod = false;

        if (!empty($aCurl)) {
            $running = null;
            do {
                usleep(1000);
                curl_multi_exec($mh, $running);
            } while ($running > 0);

            foreach ($aCurl as $url => $ch) {
                $content = curl_multi_getcontent($ch);
                $successResponse = $this->checkResponse($content, $url);
                $success = $successResponse && $success;
                if ($url == $urlNoSegment
                    && $successResponse
                ) {
                    $stats = @unserialize($content);
                    if (!is_array($stats)) {
                        $this->logError("Error unserializing the following response from $url: " . $content);
                    }
                    $visitsAllDaysInPeriod = @array_sum($stats);
                }
            }

            foreach ($aCurl as $ch) {
                curl_multi_remove_handle($mh, $ch);
            }
            curl_multi_close($mh);
        }

        $this->log("Archived website id = $idsite, period = $period, "
            . ($period != "day" ? (int)$visitsAllDaysInPeriod . " visits, " : "")
            . (!empty($timerWebsite) ? $timerWebsite->__toString() : $timer->__toString()));
        return $success;
    }

    private function addCurlHandleToMulti(&$mh, $ch)
    {
        if (!$mh) {
            $mh = curl_multi_init();
        }
        curl_multi_add_handle($mh, $ch);
    }

    private function getNewCurlHandle($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($this->acceptInvalidSSLCertificate) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, Http::getUserAgent());
        Http::configCurlCertificate($ch);
        return $ch;
    }

    /**
     * Logs a section in the output
     */
    private function logSection($title = "")
    {
        $this->log("---------------------------");
        if(!empty($title)) {
            $this->log($title);
        }
    }

    private function log($m)
    {
        $this->output .= $m . "\n";
        try {
            Log::info($m);
        } catch(Exception $e) {
            print($m . "\n");
        }
    }

    /**
     * Issues a request to $url
     */
    private function request($url)
    {
        $url = $this->piwikUrl . $url . self::APPEND_TO_API_REQUEST;

        if($this->shouldStartProfiler) {
            $url .= "&xhprof=2";
        }

        //$this->log($url);
        try {
            $response = Http::sendHttpRequestBy('curl', $url, $timeout = 300, $userAgent = null, $destinationPath = null, $file = null, $followDepth = 0, $acceptLanguage = false, $acceptInvalidSSLCertificate = $this->acceptInvalidSSLCertificate);
        } catch (Exception $e) {
            return $this->logNetworkError($url, $e->getMessage());
        }
        if ($this->checkResponse($response, $url)) {
            return $response;
        }
        return false;
    }

    private function checkResponse($response, $url)
    {
        if (empty($response)
            || stripos($response, 'error')
        ) {
            return $this->logNetworkError($url, $response);
        }
        return true;
    }

    private function logError($m)
    {
        if (!defined('PIWIK_ARCHIVE_NO_TRUNCATE')) {
            $m = substr($m, 0, self::TRUNCATE_ERROR_MESSAGE_SUMMARY);
        }

        $this->errors[] = $m;
        $this->log("ERROR: $m");
    }

    private function logNetworkError($url, $response)
    {
        $message = "Got invalid response from API request: $url. ";
        if (empty($response)) {
            $message .= "The response was empty. This usually means a server error. This solution to this error is generally to increase the value of 'memory_limit' in your php.ini file. Please check your Web server Error Log file for more details.";
        } else {
            $message .= "Response was '$response'";
        }
        $this->logError($message);
        return false;
    }

    /**
     * Displays script usage
     */
    private function usage()
    {
        echo self::getUsage();
    }

    /**
     * Configures Piwik\Log so messages are written in output
     */
    private function initLog()
    {
        $config = Config::getInstance();
        $config->log['log_only_when_debug_parameter'] = 0;
        $config->log[\Piwik\Log::LOG_WRITERS_CONFIG_OPTION] = array("screen");
        $config->log[\Piwik\Log::LOG_LEVEL_CONFIG_OPTION] = 'VERBOSE';

        if (!function_exists("curl_multi_init")) {
            $this->log("ERROR: this script requires curl extension php_curl enabled in your CLI php.ini");
            $this->usage();
            exit;
        }
    }

    /**
     * Script does run on http:// ONLY if the SU token is specified
     */
    private function initCheckCli()
    {
        if (Common::isPhpCliMode()) {
            return;
        }
        $token_auth = Common::getRequestVar('token_auth', '', 'string');
        if ($token_auth != $this->token_auth
            || strlen($token_auth) != 32
        ) {
            die('<b>You must specify the Super User token_auth as a parameter to this script, eg. <code>?token_auth=XYZ</code> if you wish to run this script through the browser. </b><br>
                However it is recommended to run it <a href="http://piwik.org/docs/setup-auto-archiving/">via cron in the command line</a>, since it can take a long time to run.<br/>
                In a shell, execute for example the following to trigger archiving on the local Piwik server:<br/>
                <code>$ /path/to/php /path/to/piwik/misc/cron/archive.php --url=http://your-website.org/path/to/piwik/</code>');
        }
    }

    /**
     * Init Piwik, connect DB, create log & config objects, etc.
     */
    private function initCore()
    {
        try {
            FrontController::getInstance()->init();
        } catch (Exception $e) {
            echo "ERROR: During Piwik init, Message: " . $e->getMessage();
            //echo $e->getTraceAsString();
            exit(1);
        }
    }

    private function displayHelp()
    {
        $displayHelp = $this->isParameterSet('help') || $this->isParameterSet('h');

        if ($displayHelp) {
            $this->usage();
            exit;
        }
    }

    /**
     * Initializes the various parameters to the script, based on input parameters.
     *
     */
    private function initStateFromParameters()
    {
        $this->todayArchiveTimeToLive = Rules::getTodayArchiveTimeToLive();
        $this->acceptInvalidSSLCertificate = $this->isParameterSet("accept-invalid-ssl-certificate");
        $this->processPeriodsMaximumEverySeconds = $this->getDelayBetweenPeriodsArchives();
        $this->shouldArchiveAllSites = (bool) $this->isParameterSet("force-all-websites");
        $this->shouldStartProfiler = (bool) $this->isParameterSet("xhprof");
        $restrictToIdSites = $this->isParameterSet("force-idsites", true);
        $skipIdSites = $this->isParameterSet("skip-idsites", true);
        $this->shouldArchiveSpecifiedSites = \Piwik\Site::getIdSitesFromIdSitesString($restrictToIdSites);
        $this->shouldSkipSpecifiedSites = \Piwik\Site::getIdSitesFromIdSitesString($skipIdSites);
        $this->lastSuccessRunTimestamp = Option::get(self::OPTION_ARCHIVING_FINISHED_TS);
        $this->shouldArchiveOnlySitesWithTrafficSince = $this->isShouldArchiveAllSitesWithTrafficSince();

        if($this->shouldArchiveOnlySitesWithTrafficSince === false) {
            // force-all-periods is not set here
            if (empty($this->lastSuccessRunTimestamp)) {
                // First time we run the script
                $this->shouldArchiveOnlySitesWithTrafficSince = self::ARCHIVE_SITES_WITH_TRAFFIC_SINCE;
            } else {
                // there was a previous successful run
                $this->shouldArchiveOnlySitesWithTrafficSince = time() - $this->lastSuccessRunTimestamp;
            }
        }  else {
            // force-all-periods is set here
            $this->archiveAndRespectTTL = false;

            if($this->shouldArchiveOnlySitesWithTrafficSince === true) {
                // force-all-periods without value
                $this->shouldArchiveOnlySitesWithTrafficSince = self::ARCHIVE_SITES_WITH_TRAFFIC_SINCE;
            }
        }
    }

    private function filterWebsiteIds(&$websiteIds)
    {
        // Keep only the websites that do exist
        $websiteIds = array_intersect($websiteIds, $this->allWebsites);

        /**
         * When the cron to run archive.php is executed, it fetches the list of website IDs to process.
         * Use this hook to add, remove, or change the order of websites IDs to pre-archive.
         */
        Piwik::postEvent('CronArchive.filterWebsiteIds', array(&$websiteIds));
    }

    /**
     *  Returns the list of sites to loop over and archive.
     *  @return array
     */
    private function initWebsiteIds()
    {
        if(count($this->shouldArchiveSpecifiedSites) > 0) {
            $this->log("- Will process " . count($this->shouldArchiveSpecifiedSites) . " websites (--force-idsites)");

            return $this->shouldArchiveSpecifiedSites;
        }
        if ($this->shouldArchiveAllSites) {
            $this->log("- Will process all " . count($this->allWebsites) . " websites");
            return $this->allWebsites;
        }

        $websiteIds = array_merge(
            $this->addWebsiteIdsWithVisitsSinceLastRun(),
            $this->addWebsiteIdsToReprocess()
        );
        $websiteIds = array_merge($websiteIds, $this->addWebsiteIdsInTimezoneWithNewDay($websiteIds));
        return array_unique($websiteIds);
    }

    private function initTokenAuth()
    {
        $login = Config::getInstance()->superuser['login'];
        $md5Password = Config::getInstance()->superuser['password'];
        $this->token_auth = md5($login . $md5Password);
        $this->login = $login;
    }

    private function initPiwikHost()
    {
        // If archive.php run as a web cron, we use the current hostname
        if (!Common::isPhpCliMode()) {
            // example.org/piwik/misc/cron/
            $piwikUrl = Common::sanitizeInputValue(Url::getCurrentUrlWithoutFileName());
            // example.org/piwik/
            $piwikUrl = $piwikUrl . "../../";
        } else {
            // If archive.php run as CLI/shell we require the piwik url to be set
            $piwikUrl = $this->isParameterSet("url", true);
            if (!$piwikUrl
                || !\Piwik\UrlHelper::isLookLikeUrl($piwikUrl)
            ) {
                $this->logFatalError("archive.php expects the argument --url to be set to your Piwik URL, for example: --url=http://example.org/piwik/ "
                    . "\n--help for more information", $backtrace = false);
            }
            // ensure there is a trailing slash
            if ($piwikUrl[strlen($piwikUrl) - 1] != '/') {
                $piwikUrl .= '/';
            }
        }

        $this->initConfigObject($piwikUrl);

        if (Config::getInstance()->General['force_ssl'] == 1) {
            $piwikUrl = str_replace('http://', 'https://', $piwikUrl);
        }
        $this->piwikUrl = $piwikUrl . "index.php";
    }

    /**
     * Returns if the requested parameter is defined in the command line arguments.
     * If $valuePossible is true, then a value is possibly set for this parameter,
     * ie. --force-timeout-for-periods=3600 would return 3600
     *
     * @param $parameter
     * @param bool $valuePossible
     * @return true or the value (int,string) if set, false otherwise
     */
    private function isParameterSet($parameter, $valuePossible = false)
    {
        if (!Common::isPhpCliMode()) {
            return false;
        }
        if($parameter == 'url' && self::$url) {
            return self::$url;
        }
        $parameters = array(
            "--$parameter",
            "-$parameter",
            $parameter
        );
        foreach ($parameters as $parameter) {
            foreach ($_SERVER['argv'] as $arg) {
                if (strpos($arg, $parameter) === 0) {
                    if ($valuePossible) {
                        $parameterFound = $arg;
                        if (($posEqual = strpos($parameterFound, '=')) !== false) {
                            $return = substr($parameterFound, $posEqual + 1);
                            if ($return !== false) {
                                return $return;
                            }
                        }
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Return All websites that had reports in the past which were invalidated recently
     * (see API CoreAdminHome.invalidateArchivedReports)
     * eg. when using Python log import script
     *
     * @return array
     */
    private function addWebsiteIdsToReprocess()
    {
        $this->idSitesInvalidatedOldReports = APICoreAdminHome::getWebsiteIdsToInvalidate();

        if (count($this->idSitesInvalidatedOldReports) > 0) {
            $ids = ", IDs: " . implode(", ", $this->idSitesInvalidatedOldReports);
            $this->log("- Will process " . count($this->idSitesInvalidatedOldReports)
                . " other websites because some old data reports have been invalidated (eg. using the Log Import script) "
                . $ids);
        }
        return $this->idSitesInvalidatedOldReports;
    }

    /**
     * Returns all sites that had visits since specified time
     *
     * @return string
     */
    private function addWebsiteIdsWithVisitsSinceLastRun()
    {
        $sitesIdWithVisits = APISitesManager::getInstance()->getSitesIdWithVisits(time() - $this->shouldArchiveOnlySitesWithTrafficSince);
        $websiteIds = !empty($sitesIdWithVisits) ? ", IDs: " . implode(", ", $sitesIdWithVisits) : "";
        $prettySeconds = \Piwik\MetricsFormatter::getPrettyTimeFromSeconds( $this->shouldArchiveOnlySitesWithTrafficSince, true, false);
        $this->log("- Will process " . count($sitesIdWithVisits) . " websites with new visits since "
            . $prettySeconds
            . " "
            . $websiteIds);
        return $sitesIdWithVisits;
    }

    /**
     * Returns the list of timezones where the specified timestamp in that timezone
     * is on a different day than today in that timezone.
     *
     * @return array
     */
    private function getTimezonesHavingNewDay()
    {
        $timestamp = time() - $this->shouldArchiveOnlySitesWithTrafficSince;
        $uniqueTimezones = APISitesManager::getInstance()->getUniqueSiteTimezones();
        $timezoneToProcess = array();
        foreach ($uniqueTimezones as &$timezone) {
            $processedDateInTz = Date::factory((int)$timestamp, $timezone);
            $currentDateInTz = Date::factory('now', $timezone);

            if ($processedDateInTz->toString() != $currentDateInTz->toString()) {
                $timezoneToProcess[] = $timezone;
            }
        }
        return $timezoneToProcess;
    }

    /**
     * Returns the list of websites in which timezones today is a new day
     * (compared to the last time archiving was executed)
     *
     * @param $websiteIds
     * @return array Website IDs
     */
    private function addWebsiteIdsInTimezoneWithNewDay($websiteIds)
    {
        $timezones = $this->getTimezonesHavingNewDay();
        $websiteDayHasFinishedSinceLastRun = APISitesManager::getInstance()->getSitesIdFromTimezones($timezones);
        $websiteDayHasFinishedSinceLastRun = array_diff($websiteDayHasFinishedSinceLastRun, $websiteIds);
        $this->websiteDayHasFinishedSinceLastRun = $websiteDayHasFinishedSinceLastRun;
        if (count($websiteDayHasFinishedSinceLastRun) > 0) {
            $ids = !empty($websiteDayHasFinishedSinceLastRun) ? ", IDs: " . implode(", ", $websiteDayHasFinishedSinceLastRun) : "";
            $this->log("- Will process " . count($websiteDayHasFinishedSinceLastRun)
                . " other websites because the last time they were archived was on a different day (in the website's timezone) "
                . $ids);
        }
        return $websiteDayHasFinishedSinceLastRun;
    }

    /**
     *  Test that the specified piwik URL is a valid Piwik endpoint.
     */
    private function checkPiwikUrlIsValid()
    {
        $response = $this->request("?module=API&method=API.getDefaultMetricTranslations&format=original&serialize=1");
        $responseUnserialized = @unserialize($response);
        if ($response === false
            || !is_array($responseUnserialized)
        ) {
            $this->logFatalError("The Piwik URL {$this->piwikUrl} does not seem to be pointing to a Piwik server. Response was '$response'.");
        }
    }

    private function logInitInfo()
    {
        $this->logSection("INIT");
        $this->log("Querying Piwik API at: {$this->piwikUrl}");
        $this->log("Running Piwik " . Version::VERSION . " as Super User: " . $this->login);
    }

    private function logArchiveTimeoutInfo()
    {
        $this->logSection("NOTES");

        // Recommend to disable browser archiving when using this script
        if (Rules::isBrowserTriggerEnabled()) {
            $this->log("- If you execute this script at least once per hour (or more often) in a crontab, you may disable 'Browser trigger archiving' in Piwik UI > Settings > General Settings. ");
            $this->log("  See the doc at: http://piwik.org/docs/setup-auto-archiving/");
        }
        $this->log("- Reports for today will be processed at most every " . $this->todayArchiveTimeToLive
            . " seconds. You can change this value in Piwik UI > Settings > General Settings.");
        $this->log("- Reports for the current week/month/year will be refreshed at most every "
            . $this->processPeriodsMaximumEverySeconds . " seconds.");

        // Try and not request older data we know is already archived
        if ($this->lastSuccessRunTimestamp !== false) {
            $dateLast = time() - $this->lastSuccessRunTimestamp;
            $this->log("- Archiving was last executed without error " . \Piwik\MetricsFormatter::getPrettyTimeFromSeconds($dateLast, true, $isHtml = false) . " ago");
        }
    }

    /**
     * Returns the delay in seconds, that should be enforced, between calling archiving for Periods Archives.
     * It can be set by --force-timeout-for-periods=X
     *
     * @return int
     */
    private function getDelayBetweenPeriodsArchives()
    {
        $forceTimeoutPeriod = $this->isParameterSet("force-timeout-for-periods", $valuePossible = true);
        if (empty($forceTimeoutPeriod) || $forceTimeoutPeriod === true) {
            return self::SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES;
        }

        // Ensure the cache for periods is at least as high as cache for today
        if ($forceTimeoutPeriod > $this->todayArchiveTimeToLive) {
            return $forceTimeoutPeriod;
        }

        $this->log("WARNING: Automatically increasing --force-timeout-for-periods from $forceTimeoutPeriod to "
            . $this->todayArchiveTimeToLive
            . " to match the cache timeout for Today's report specified in Piwik UI > Settings > General Settings");
        return $this->todayArchiveTimeToLive;
    }

    private function isShouldArchiveAllSitesWithTrafficSince()
    {
        $shouldArchiveAllPeriodsSince = $this->isParameterSet("force-all-periods", $valuePossible = true);
        if(empty($shouldArchiveAllPeriodsSince)) {
            return false;
        }
        if ( is_numeric($shouldArchiveAllPeriodsSince)
            && $shouldArchiveAllPeriodsSince > 1
        ) {
            return (int)$shouldArchiveAllPeriodsSince;
        }
        return true;
    }

    /**
     * @param $idsite
     */
    protected function setSiteIsArchived($idsite)
    {
        $websiteIdsInvalidated = APICoreAdminHome::getWebsiteIdsToInvalidate();
        if (count($websiteIdsInvalidated)) {
            $found = array_search($idsite, $websiteIdsInvalidated);
            if ($found !== false) {
                unset($websiteIdsInvalidated[$found]);
//								$this->log("Websites left to invalidate: " . implode(", ", $websiteIdsInvalidated));
                Option::set(APICoreAdminHome::OPTION_INVALIDATED_IDSITES, serialize($websiteIdsInvalidated));
            }
        }
    }
}

