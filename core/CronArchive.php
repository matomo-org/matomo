<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\ArchiveProcessor\Rules;
use Matomo\Cache\Lazy;
use Piwik\CliMulti\Process;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive\Performance\Logger;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\Model;
use Piwik\Metrics\Formatter;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\CronArchive\SitesToReprocessDistributedList;
use Piwik\CronArchive\SegmentArchivingRequestUrlProvider;
use Piwik\Plugins\CoreAdminHome\API as CoreAdminHomeAPI;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Plugins\UsersManager\UserPreferences;
use Psr\Log\LoggerInterface;

// TODO: modify CLI command options

/**
 * ./console core:archive runs as a cron and is a useful tool for general maintenance,
 * and pre-process reports for a Fast dashboard rendering.
 */
class CronArchive
{
    // the url can be set here before the init, and it will be used instead of --url=
    public static $url = false;

    const TABLES_WITH_INVALIDATED_ARCHIVES = 'CronArchive.getTablesWithInvalidatedArchives';
    const TABLES_WITH_INVALIDATED_ARCHIVES_TTL = 3600;

    // Max parallel requests for a same site's segments
    const MAX_CONCURRENT_API_REQUESTS = 3;

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

    // Name of option used to store starting timestamp
    const OPTION_ARCHIVING_STARTED_TS = "LastFullArchivingStartTime";

    // Show only first N characters from Piwik API output in case of errors
    const TRUNCATE_ERROR_MESSAGE_SUMMARY = 6000;

    // archiving  will be triggered on all websites with traffic in the last $shouldArchiveOnlySitesWithTrafficSince seconds
    private $shouldArchiveOnlySitesWithTrafficSince;

    // By default, we only process the current week/month/year at most once an hour
    private $processPeriodsMaximumEverySeconds;
    private $todayArchiveTimeToLive;
    private $websiteDayHasFinishedSinceLastRun = array();
    private $idSitesInvalidatedOldReports = array();
    private $shouldArchiveOnlySpecificPeriods = array();

    private $allWebsites = array();
    private $segments = array();
    private $requests = 0;
    private $archiveAndRespectTTL = true;

    /**
     * @var Model
     */
    private $model;

    private $lastSuccessRunTimestamp = false;
    private $errors = array();

    private $apiToInvalidateArchivedReport;

    const NO_ERROR = "no error";

    public $testmode = false;

    /**
     * The list of IDs for sites for whom archiving should be initiated. If supplied, only these
     * sites will be archived.
     *
     * @var int[]
     */
    public $shouldArchiveSpecifiedSites = array();

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
     * Given options will be forwarded to the PHP command if the archiver is executed via CLI.
     * @var string
     */
    public $phpCliConfigurationOptions = '';

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
     * The number of concurrent requests to issue per website. Defaults to {@link MAX_CONCURRENT_API_REQUESTS}.
     *
     * Used when archiving a site's segments concurrently.
     *
     * @var int|false
     */
    public $concurrentRequestsPerWebsite = false;

    /**
     * The number of concurrent archivers to run at once max.
     *
     * @var int|false
     */
    public $maxConcurrentArchivers = false;

    /**
     * List of segment strings to force archiving for. If a stored segment is not in this list, it will not
     * be archived.
     *
     * @var string[]
     */
    public $segmentsToForce = array();

    /**
     * @var bool
     */
    public $disableSegmentsArchiving = false;

    /**
     * If enabled, segments will be only archived for yesterday, but not today. If the segment was created recently,
     * then it will still be archived for today and the setting will be ignored for this segment.
     * @var bool
     */
    public $skipSegmentsToday = false;

    private $archivingStartingTime;

    private $formatter;

    /**
     * @var SegmentArchivingRequestUrlProvider
     */
    private $segmentArchivingRequestUrlProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Only used when archiving using HTTP requests.
     *
     * @var string
     */
    private $urlToPiwik = null;

    /**
     * @var ArchiveInvalidator
     */
    private $invalidator;

    /**
     * @var bool
     */
    private $isArchiveProfilingEnabled = false;

    /**
     * @var array
     */
    private $periodIdsToLabels;

    /**
     * Constructor.
     *
     * @param string|null $processNewSegmentsFrom When to archive new segments from. See [General] process_new_segments_from
     *                                            for possible values.
     * @param LoggerInterface|null $logger
     */
    public function __construct($processNewSegmentsFrom = null, LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: StaticContainer::get('Psr\Log\LoggerInterface');
        $this->formatter = new Formatter();

        $processNewSegmentsFrom = $processNewSegmentsFrom ?: StaticContainer::get('ini.General.process_new_segments_from');

        $this->segmentArchivingRequestUrlProvider = new SegmentArchivingRequestUrlProvider($processNewSegmentsFrom);

        $this->invalidator = StaticContainer::get('Piwik\Archive\ArchiveInvalidator');

        $this->isArchiveProfilingEnabled = Config::getInstance()->Debug['archiving_profile'] == 1;

        $this->model = StaticContainer::get(Model::class);

        $this->periodIdsToLabels = array_flip(Piwik::$idPeriods);
    }

    private function isMaintenanceModeEnabled()
    {
        return Config::getInstance()->General['maintenance_mode'] == 1;
    }

    /**
     * Initializes and runs the cron archiver.
     */
    public function main()
    {
        if ($this->isMaintenanceModeEnabled()) {
            $this->logger->info("Archiving won't run because maintenance mode is enabled");
            return;
        }

        $self = $this;
        Access::doAsSuperUser(function () use ($self) {
            $self->init();
            $self->run();
            $self->runScheduledTasks();
            $self->end();
        });
    }

    public function init()
    {
        /**
         * This event is triggered during initializing archiving.
         *
         * @param CronArchive $this
         */
        Piwik::postEvent('CronArchive.init.start', array($this));

        SettingsServer::setMaxExecutionTime(0);

        $this->archivingStartingTime = time();

        // Note: the order of methods call matters here.
        $this->initStateFromParameters();

        $this->logInitInfo();
        $this->logArchiveTimeoutInfo();

        // record archiving start time
        Option::set(self::OPTION_ARCHIVING_STARTED_TS, time());

        $this->segments    = $this->initSegmentsToArchive();

        if (!empty($this->shouldArchiveOnlySpecificPeriods)) {
            $this->logger->info("- Will only process the following periods: " . implode(", ", $this->shouldArchiveOnlySpecificPeriods) . " (--force-periods)");
        }

        $this->invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain();

        $allWebsites = APISitesManager::getInstance()->getAllSitesId();
        $websitesIds = $this->initWebsiteIds($allWebsites);
        $this->filterWebsiteIds($websitesIds);
        $this->allWebsites = $websitesIds;

        $this->logForcedSegmentInfo();

        /**
         * This event is triggered after a CronArchive instance is initialized.
         *
         * TODO: look for usages, since we removed a param
         */
        Piwik::postEvent('CronArchive.init.finish', []);
    }

    /**
     * Main function, runs archiving on all websites with new activity
     */
    public function run()
    {
        $timer = new Timer;

        $this->logSection("START");
        $this->logger->info("Starting Matomo reports archiving...");

        $numArchivesFinished = 0;

        if ($this->hasReachedMaxConcurrentArchivers()) {
            $this->logger->info("Reached maximum concurrent archivers allowed ({$this->maxConcurrentArchivers}), aborting run.");
            return;
        }

        $countOfProcesses = $this->getMaxConcurrentApiRequests();

        while (true) {
            if ($this->isMaintenanceModeEnabled()) {
                $this->logger->info("Archiving will stop now because maintenance mode is enabled");
                return;
            }

            if (!Process::isMethodDisabled('getmypid') && !Process::isMethodDisabled('ignore_user_abort')) {
                // see https://github.com/matomo-org/wp-matomo/issues/163
                flush();
            }

            /*
             * TODO:
             * => events to replace:
             *    * CronArchive.archiveSingleSite.start
             *    * CronArchive.archiveSingleSite.finish
             */


            // get archives to process simultaneously
            $archivesToProcess = [];
            $periodToCheckFor = null;
            while (count($archivesToProcess) < $countOfProcesses) {
                $invalidatedArchive = $this->getNextInvalidatedArchive($periodToCheckFor);
                if (empty($invalidatedArchive)) {
                    $this->logger->info("No more invalidated archives found.");
                    break;
                }

                $idArchive = $this->model->startArchive(
                    $invalidatedArchive['idsite'],
                    $invalidatedArchive['date1'],
                    $invalidatedArchive['date2'],
                    $invalidatedArchive['period'],
                    $invalidatedArchive['name']
                );
                if (empty($idArchive)) { // another process started on this archive, pull another one
                    $this->logger->debug("Archive $idArchive invalid, but being handled by another process.");
                    continue;
                }

                $archivesToProcess[] = $invalidatedArchive;
                $periodToCheckFor = $invalidatedArchive['period'];
            }

            if (empty($archivesToProcess)) { // no invalidated archive left, stop
                return;
            }

            $successCount = $this->launchArchivingFor($archivesToProcess);
            $numArchivesFinished += $successCount;
        };

        $this->logger->info("Done archiving!");

        $this->logSection("SUMMARY");
        $this->logger->info("Processed $numArchivesFinished archives.");
        $this->logger->info("Total API requests: {$this->requests}");

        //DONE: done/total, visits, wtoday, wperiods, reqs, time, errors[count]: first eg.
        $this->logger->info("done: " .
            $this->requests . " req, " . round($timer->getTimeMs()) . " ms, " .
            (empty($this->errors)
                ? self::NO_ERROR
                : (count($this->errors) . " errors."))
        );

        $this->logger->info($timer->__toString());
    }

    private function getNextInvalidatedArchive($periodToGet)
    {
        $tables = $this->getTablesWithInvalidatedArchives();

        foreach ($tables as $table) {
            $nextArchive = $this->model->getNextInvalidatedArchive($table, $periodToGet, $this->allWebsites);
            if (!empty($nextArchive)) {
                return $nextArchive;
            }

            $this->removeTableThatHasNoInvalidatedArchives($table);
        }

        return null;
    }

    private function getTablesWithInvalidatedArchives()
    {
        $cacheKey = self::TABLES_WITH_INVALIDATED_ARCHIVES;

        /** @var Lazy $cache */
        $cache = Cache::getLazyCache();
        $result = $cache->fetch($cacheKey);
        $result = @json_decode($result);

        if (empty($result)) {
            $this->invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain();

            // make sure tables are reloaded
            ArchiveTableCreator::$tablesAlreadyInstalled = null;
            DbHelper::getTablesInstalled(true);

            $result = $this->model->getTablesWithInvalidatedArchives();
            $cache->save($cacheKey, json_encode($result), $lifeTime = self::TABLES_WITH_INVALIDATED_ARCHIVES_TTL);
        }
        return $result;
    }

    private function removeTableThatHasNoInvalidatedArchives($table)
    {
        $cacheKey = self::TABLES_WITH_INVALIDATED_ARCHIVES;

        // TODO: there is a slight chance of a race condition here between processes. need to make sure it's ok.

        /** @var Lazy $cache */
        $cache = Cache::getLazyCache();
        $cachedTables = $cache->fetch($cacheKey);
        $cachedTables = @json_decode($cachedTables);
        if (empty($cachedTables)) {
            return;
        }

        $index = array_search($table, $cachedTables);
        unset($cachedTables[$index]);

        $cache->save($cacheKey, json_encode($cachedTables), $lifeTime = self::TABLES_WITH_INVALIDATED_ARCHIVES_TTL);
    }

    private function launchArchivingFor($archives)
    {
        $urls = [];
        $archivesBeingQueried = [];
        foreach ($archives as $index => $archive) {
            $url = $this->generateUrlToArchiveFromArchiveInfo($archive);
            if (empty($url)) {
                // can happen if, for example, a segment was deleted after an archive was invalidated
                // in this case, we can just delete the archive entirely.
                $date = Date::factory($archive['date1']);
                $this->model->deleteArchiveIds(ArchiveTableCreator::getNumericTable($date), ArchiveTableCreator::getBlobTable($date), [$archive['idarchive']]);
                continue;
            }

            $urls[] = $url;
            $archivesBeingQueried[$index] = $archive;
        }

        $cliMulti = $this->makeCliMulti();
        $cliMulti->timeRequests();

        $responses = $cliMulti->request($urls);
        $timers = $cliMulti->getTimers();

        $successCount = 0;

        foreach ($urls as $index => $url) {
            $content = array_key_exists($index, $responses) ? $responses[$index] : null;
            $this->checkResponse($content, $url);

            $stats = Common::safe_unserialize($content); // TODO: I wonder if we can use json here instead of 'original' format? would be safer
            if (!is_array($stats)) {
                $this->logError("Error unserializing the following response from $url: " . $content);
                continue;
            }

            $visitsForPeriod = $this->getVisitsFromApiResponse($stats);

            $this->logArchiveJobFinished($url, $timers[$index], $visitsForPeriod);

            // remove old archive (could also do this in archivewriter, but it's a bit simpler here)
            // TODO: do it in archive writer instead?
            $idArchive = $archivesBeingQueried[$index]['idarchive'];
            $this->model->deleteArchiveIds(ArchiveTableCreator::getNumericTable($date), ArchiveTableCreator::getBlobTable($date), [$idArchive]);

            ++$successCount;
        }

        $this->requests += count($urls);

        return $successCount;
    }

    private function generateUrlToArchiveFromArchiveInfo($archive)
    {
        $period = $this->periodIdsToLabels[$archive['period']];

        if ($period == 'range') {
            $date = $archive['date1'] . ',' . $archive['date2'];
        } else {
            $date = $archive['date1'];
        }

        $idSite = $archive['idsite'];

        // TODO: what about plugin specific archives? what if one gets invalidated?
        $segment = $this->findSegmentForArchive($archive, $idSite);
        if (!empty($segment)) {
            $date = $this->segmentArchivingRequestUrlProvider->getUrlParameterDateString($idSite, $period, $date, $segment);
        }

        return $this->getVisitsRequestUrl($idSite, $period, $date, $segment);
    }

    private function findSegmentForArchive($archive, $idSite)
    {
        $flag = explode('.', $archive['value'])[0];
        if ($flag == 'done') {
            return '';
        }

        $hash = substr($flag, 5);
        return $this->segmentArchivingRequestUrlProvider->findSegmentForHash($hash, $idSite);
    }

    private function logArchiveJobFinished($url, $timer, $visits)
    {
        $params = UrlHelper::getArrayFromQueryString($url);
        $visits = (int) $visits;

        $this->logger->info("Archived website id {$params['idSite']}, period = {$params['period']}, date = "
            . "{$params['date']}, segment = {$params['segment']}. $visits visits found. $timer");
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * End of the script
     */
    public function end()
    {
        /**
         * This event is triggered after archiving.
         *
         * @param CronArchive $this
         */
        Piwik::postEvent('CronArchive.end', array($this));

        if (empty($this->errors)) {
            // No error -> Logs the successful script execution until completion
            Option::set(self::OPTION_ARCHIVING_FINISHED_TS, time());
            return;
        }

        $this->logSection("SUMMARY OF ERRORS");
        foreach ($this->errors as $error) {
            // do not logError since errors are already in stderr
            $this->logger->info("Error: " . $error);
        }

        $summary = count($this->errors) . " total errors during this script execution, please investigate and try and fix these errors.";
        $this->logFatalError($summary);
    }

    public function logFatalError($m)
    {
        $this->logError($m);

        throw new Exception($m);
    }

    // TODO: make sure this workflow still works: invalidate segment archive, run core:archive

    public function runScheduledTasks()
    {
        $this->logSection("SCHEDULED TASKS");

        if ($this->disableScheduledTasks) {
            $this->logger->info("Scheduled tasks are disabled with --disable-scheduled-tasks");
            return;
        }

        // TODO: this is a HACK to get the purgeOutdatedArchives task to work when run below. without
        //       it, the task will not run because we no longer run the tasks through CliMulti.
        //       harder to implement alternatives include:
        //       - moving CronArchive logic to DI and setting a flag in the class when the whole process
        //         runs
        //       - setting a new DI environment for core:archive which CoreAdminHome can use to conditionally
        //         enable/disable the task
        $_GET['trigger'] = 'archivephp';
        CoreAdminHomeAPI::getInstance()->runScheduledTasks();

        $this->logSection("");
    }
    // TODO: check if lastRunKey() is used somewhere else

    /**
     * Returns base URL to process reports for the $idSite on a given $period
     *
     * @param string $idSite
     * @param string $period
     * @param string $date
     * @param bool|false $segment
     * @return string
     */
    private function getVisitsRequestUrl($idSite, $period, $date, $segment = false)
    {
        $request = "?module=API&method=API.get&idSite=$idSite&period=$period&date=" . $date . "&format=php";
        if ($segment) {
            $request .= '&segment=' . urlencode($segment);
        }
        return $request;
    }

    private function initSegmentsToArchive()
    {
        $segments = \Piwik\SettingsPiwik::getKnownSegmentsToArchive();

        if (empty($segments)) {
            return array();
        }

        $this->logger->info("- Will pre-process " . count($segments) . " Segments for each website and each period: " . implode(", ", $segments));
        return $segments;
    }

    // TODO: is the isCommandAlreadyRunning() optimization still needed? since we mark an archive as DONE_IN_PROGRESS, I don't think it is. think about it anyway
    // TODO: make sure we are still invalidating archives before running core:archive. or maybe before refreshing the list of tables? that might be better I guess.
    // TODO: need test to make sure segment archives are invalidated as well. and are able to be invalidated.

    /**
     * Logs a section in the output
     *
     * @param string $title
     */
    private function logSection($title = "")
    {
        $this->logger->info("---------------------------");
        if (!empty($title)) {
            $this->logger->info($title);
        }
    }

    public function logError($m)
    {
        if (!defined('PIWIK_ARCHIVE_NO_TRUNCATE')) {
            $m = substr($m, 0, self::TRUNCATE_ERROR_MESSAGE_SUMMARY);
            $m = str_replace(array("\n", "\t"), " ", $m);
        }
        $this->errors[] = $m;
        $this->logger->error($m);
    }

    private function logNetworkError($url, $response)
    {
        $message = "Got invalid response from API request: $url. ";
        if (empty($response)) {
            $message .= "The response was empty. This usually means a server error. A solution to this error is generally to increase the value of 'memory_limit' in your php.ini file. ";

            if($this->makeCliMulti()->supportsAsync()) {
                $message .= " For more information and the error message please check in your PHP CLI error log file. As this core:archive command triggers PHP processes over the CLI, you can find where PHP CLI logs are stored by running this command: php -i | grep error_log";
            } else {
                $message .= " For more information and the error message please check your web server's error Log file. As this core:archive command triggers PHP processes over HTTP, you can find the error message in your Matomo's web server error logs. ";
            }
        } else {
            $message .= "Response was '$response'";
        }

        $this->logError($message);
        return false;
    }

    private function checkResponse($response, $url)
    {
        if (empty($response)
            || stripos($response, 'error') !== false
        ) {
            return $this->logNetworkError($url, $response);
        }
        return true;
    }

    /**
     * Initializes the various parameters to the script, based on input parameters.
     *
     */
    private function initStateFromParameters()
    {
        $this->todayArchiveTimeToLive = Rules::getTodayArchiveTimeToLive();
        $this->processPeriodsMaximumEverySeconds = $this->getDelayBetweenPeriodsArchives();
        $this->lastSuccessRunTimestamp = $this->getLastSuccessRunTimestamp();
        $this->shouldArchiveOnlySitesWithTrafficSince = $this->isShouldArchiveAllSitesWithTrafficSince();
        $this->shouldArchiveOnlySpecificPeriods = $this->getPeriodsToProcess();

        if ($this->shouldArchiveOnlySitesWithTrafficSince !== false) {
            // force-all-periods is set here
            $this->archiveAndRespectTTL = false;
        }
    }

    public function filterWebsiteIds(&$websiteIds)
    {
        // Keep only the websites that do exist
        $websiteIds = array_intersect($websiteIds, $this->allWebsites);

        /**
         * Triggered by the **core:archive** console command so plugins can modify the priority of
         * websites that the archiving process will be launched for.
         *
         * Plugins can use this hook to add websites to archive, remove websites to archive, or change
         * the order in which websites will be archived.
         *
         * @param array $websiteIds The list of website IDs to launch the archiving process for.
         */
        Piwik::postEvent('CronArchive.filterWebsiteIds', array(&$websiteIds));
    }

    /**
     * @internal
     * @param $api
     */
    public function setApiToInvalidateArchivedReport($api)
    {
        $this->apiToInvalidateArchivedReport = $api;
    }

    private function getApiToInvalidateArchivedReport()
    {
        if ($this->apiToInvalidateArchivedReport) {
            return $this->apiToInvalidateArchivedReport;
        }

        return CoreAdminHomeAPI::getInstance();
    }

    public function invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain()
    {
        $sitesPerDays = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        foreach ($sitesPerDays as $date => $siteIds) {
            //Concurrent transaction logic will end up with duplicates set.  Adding array_unique to the siteIds.
            $listSiteIds = implode(',', array_unique($siteIds ));

            try {
                $this->logger->info('- Will invalidate archived reports for ' . $date . ' for following websites ids: ' . $listSiteIds);
                $this->getApiToInvalidateArchivedReport()->invalidateArchivedReports($siteIds, $date);
            } catch (Exception $e) {
                $this->logger->info('Failed to invalidate archived reports: ' . $e->getMessage());
            }
        }
    }

    /**
     *  Returns the list of sites to loop over and archive.
     *  @return array
     */
    private function initWebsiteIds($allWebsites)
    {
        if (count($this->shouldArchiveSpecifiedSites) > 0) {
            $this->logger->info("- Will process " . count($this->shouldArchiveSpecifiedSites) . " websites (--force-idsites)");

            return $this->shouldArchiveSpecifiedSites;
        }

        $this->findWebsiteIdsInTimezoneWithNewDay($this->allWebsites);
        $this->findInvalidatedSitesToReprocess();

        if ($this->shouldArchiveAllSites) {
            $this->logger->info("- Will process all " . count($this->allWebsites) . " websites");
        }

        return $allWebsites;
    }

    private function updateIdSitesInvalidatedOldReports()
    {
        $store = new SitesToReprocessDistributedList();
        $this->idSitesInvalidatedOldReports = $store->getAll();
    }

    /**
     * Return All websites that had reports in the past which were invalidated recently
     * (see API CoreAdminHome.invalidateArchivedReports)
     * eg. when using Python log import script
     *
     * @return array
     */
    private function findInvalidatedSitesToReprocess()
    {
        $this->updateIdSitesInvalidatedOldReports();

        if (count($this->idSitesInvalidatedOldReports) > 0) {
            $ids = ", IDs: " . implode(", ", $this->idSitesInvalidatedOldReports);
            $this->logger->info("- Will process " . count($this->idSitesInvalidatedOldReports)
                . " other websites because some old data reports have been invalidated (eg. using the Log Import script or the InvalidateReports plugin) "
                . $ids);
        }

        return $this->idSitesInvalidatedOldReports;
    }

    // TODO: we need to still respect minimum process time for archives (in Rules.php) when selecting invalidated archives to re-archive.

    /**
     * Returns the list of timezones where the specified timestamp in that timezone
     * is on a different day than today in that timezone.
     *
     * @return array
     */
    private function getTimezonesHavingNewDaySinceLastRun()
    {
        $timestamp = $this->lastSuccessRunTimestamp;
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
    private function findWebsiteIdsInTimezoneWithNewDay($websiteIds)
    {
        $timezones = $this->getTimezonesHavingNewDaySinceLastRun();
        $websiteDayHasFinishedSinceLastRun = APISitesManager::getInstance()->getSitesIdFromTimezones($timezones);
        $websiteDayHasFinishedSinceLastRun = array_intersect($websiteDayHasFinishedSinceLastRun, $websiteIds);
        $this->websiteDayHasFinishedSinceLastRun = $websiteDayHasFinishedSinceLastRun;

        if (count($websiteDayHasFinishedSinceLastRun) > 0) {
            $ids = !empty($websiteDayHasFinishedSinceLastRun) ? ", IDs: " . implode(", ", $websiteDayHasFinishedSinceLastRun) : "";
            $this->logger->info("- Will process " . count($websiteDayHasFinishedSinceLastRun)
                . " other websites because the last time they were archived was on a different day (in the website's timezone) "
                . $ids);
        }

        return $websiteDayHasFinishedSinceLastRun;
    }

    private function logInitInfo()
    {
        $this->logSection("INIT");
        $this->logger->info("Running Matomo " . Version::VERSION . " as Super User");
    }

    private function logArchiveTimeoutInfo()
    {
        $this->logSection("NOTES");

        // Recommend to disable browser archiving when using this script
        if (Rules::isBrowserTriggerEnabled()) {
            $this->logger->info("- If you execute this script at least once per hour (or more often) in a crontab, you may disable 'Browser trigger archiving' in Matomo UI > Settings > General Settings.");
            $this->logger->info("  See the doc at: https://matomo.org/docs/setup-auto-archiving/");
        }

        $cliMulti = new CliMulti();
        $supportsAsync = $cliMulti->supportsAsync();
        $this->logger->info("- " . ($supportsAsync ? 'Async process archiving supported, using CliMulti.' : 'Async process archiving not supported, using curl requests.'));

        $this->logger->info("- Reports for today will be processed at most every " . $this->todayArchiveTimeToLive
            . " seconds. You can change this value in Matomo UI > Settings > General Settings.");

        $this->logger->info("- Reports for the current week/month/year will be requested at most every "
            . $this->processPeriodsMaximumEverySeconds . " seconds.");

        foreach (array('week', 'month', 'year', 'range') as $period) {
            $ttl = Rules::getPeriodArchiveTimeToLiveDefault($period);

            if (!empty($ttl) && $ttl !== $this->todayArchiveTimeToLive) {
                $this->logger->info("- Reports for the current $period will be processed at most every " . $ttl
                    . " seconds. You can change this value in config/config.ini.php by editing 'time_before_" . $period . "_archive_considered_outdated' in the '[General]' section.");
            }
        }

        // Try and not request older data we know is already archived
        if ($this->lastSuccessRunTimestamp !== false) {
            $dateLast = time() - $this->lastSuccessRunTimestamp;
            $this->logger->info("- Archiving was last executed without error "
                . $this->formatter->getPrettyTimeFromSeconds($dateLast, true) . " ago");
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
        if (empty($this->forceTimeoutPeriod)) {
            return self::SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES;
        }

        // Ensure the cache for periods is at least as high as cache for today
        if ($this->forceTimeoutPeriod > $this->todayArchiveTimeToLive) {
            return $this->forceTimeoutPeriod;
        }

        $this->logger->info("WARNING: Automatically increasing --force-timeout-for-periods from {$this->forceTimeoutPeriod} to "
            . $this->todayArchiveTimeToLive
            . " to match the cache timeout for Today's report specified in Matomo UI > Settings > General Settings");

        return $this->todayArchiveTimeToLive;
    }

    private function isShouldArchiveAllSitesWithTrafficSince()
    {
        if (empty($this->shouldArchiveAllPeriodsSince)) {
            return false;
        }

        if (is_numeric($this->shouldArchiveAllPeriodsSince)
            && $this->shouldArchiveAllPeriodsSince > 1
        ) {
            return (int)$this->shouldArchiveAllPeriodsSince;
        }

        return true;
    }

    private function getVisitsFromApiResponse($stats)
    {
        if (empty($stats['nb_visits'])) {
            return 0;
        }

        return (int) $stats['nb_visits'];
    }

    /**
     * @return array
     */
    private function getPeriodsToProcess()
    {
        $this->restrictToPeriods = array_intersect($this->restrictToPeriods, $this->getDefaultPeriodsToProcess());
        $this->restrictToPeriods = array_intersect($this->restrictToPeriods, PeriodFactory::getPeriodsEnabledForAPI());

        return $this->restrictToPeriods;
    }

    /**
     * @return array
     */
    private function getDefaultPeriodsToProcess()
    {
        return array('day', 'week', 'month', 'year', 'range');
    }

    /**
     * @return int
     */
    private function getMaxConcurrentApiRequests()
    {
        if (false !== $this->concurrentRequestsPerWebsite) {
            return $this->concurrentRequestsPerWebsite;
        }

        return self::MAX_CONCURRENT_API_REQUESTS;
    }

    /**
     * @return false|string
     */
    private function getLastSuccessRunTimestamp()
    {
        $timestamp = Option::get(self::OPTION_ARCHIVING_FINISHED_TS);
        return $this->sanitiseTimestamp($timestamp);
    }

    private function sanitiseTimestamp($timestamp)
    {
        $now = time();
        return ($timestamp < $now) ? $timestamp : $now;
    }

    /**
     * @param $idSite
     * @return array of date strings
     */
    private function getCustomDateRangeToPreProcess($idSite) // TODO: [General] archiving_custom_ranges needs to be handled still.
    {
        static $cache = null;
        if (is_null($cache)) {
            $cache = $this->loadCustomDateRangeToPreProcess();
        }

        if (empty($cache[$idSite])) {
            $cache[$idSite] = array();
        }

        $customRanges = array_filter(Config::getInstance()->General['archiving_custom_ranges']);

        if (!empty($customRanges)) {
            $cache[$idSite] = array_merge($cache[$idSite], $customRanges);
        }

        $dates = array_unique($cache[$idSite]);
        return $dates;
    }

    /**
     * @return array
     */
    private function loadCustomDateRangeToPreProcess()
    {
        $customDateRangesToProcessForSites = array();

        // For all users who have selected this website to load by default,
        // we load the default period/date that will be loaded for this user
        // and make sure it's pre-archived
        $allUsersPreferences = APIUsersManager::getInstance()->getAllUsersPreferences(array(
            APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE,
            APIUsersManager::PREFERENCE_DEFAULT_REPORT
        ));

        foreach ($allUsersPreferences as $userLogin => $userPreferences) {
            if (!isset($userPreferences[APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE])) {
                continue;
            }

            $defaultDate = $userPreferences[APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE];
            $preference = new UserPreferences();
            $period = $preference->getDefaultPeriod($defaultDate);
            if ($period != 'range') {
                continue;
            }

            if (isset($userPreferences[APIUsersManager::PREFERENCE_DEFAULT_REPORT])
                && is_numeric($userPreferences[APIUsersManager::PREFERENCE_DEFAULT_REPORT])) {
                // If user selected one particular website ID
                $idSites = array($userPreferences[APIUsersManager::PREFERENCE_DEFAULT_REPORT]);
            } else {
                // If user selected "All websites" or some other random value, we pre-process all websites that they have access to
                $idSites = APISitesManager::getInstance()->getSitesIdWithAtLeastViewAccess($userLogin);
            }

            foreach ($idSites as $idSite) {
                $customDateRangesToProcessForSites[$idSite][] = $defaultDate;
            }
        }

        return $customDateRangesToProcessForSites;
    }

    /**
     * @param $url
     * @return string
     */
    private function makeRequestUrl($url) // TODO: should this still be used? where?
    {
        $url = $url . self::APPEND_TO_API_REQUEST;

        if ($this->shouldStartProfiler) {
            $url .= "&xhprof=2";
        }

        if ($this->testmode) {
            $url .= "&testmode=1";
        }

        /**
         * @ignore
         */
        Piwik::postEvent('CronArchive.alterArchivingRequestUrl', [&$url]);

        return $url;
    }

    protected function wasSegmentChangedRecently($definition, $allSegments)
    {
        foreach ($allSegments as $segment) {
            if ($segment['definition'] === $definition) {
                $twentyFourHoursAgo = Date::now()->subHour(24);
                $segmentDate = $segment['ts_created'];
                if (!empty($segment['ts_last_edit'])) {
                    $segmentDate = $segment['ts_last_edit'];
                }
                return Date::factory($segmentDate)->isLater($twentyFourHoursAgo);
            }
        }

        return false;
    }

    private function logForcedSegmentInfo()
    {
        if (empty($this->segmentsToForce)) {
            return;
        }

        $this->logger->info("- Limiting segment archiving to following segments:");
        foreach ($this->segmentsToForce as $segmentDefinition) {
            $this->logger->info("  * " . $segmentDefinition);
        }
    }

    /**
     * @return CliMulti
     */
    private function makeCliMulti()
    {
        /** @var CliMulti $cliMulti */
        $cliMulti = StaticContainer::getContainer()->make('Piwik\CliMulti');
        $cliMulti->setUrlToPiwik($this->urlToPiwik);
        $cliMulti->setPhpCliConfigurationOptions($this->phpCliConfigurationOptions);
        $cliMulti->setAcceptInvalidSSLCertificate($this->acceptInvalidSSLCertificate);
        $cliMulti->setConcurrentProcessesLimit($this->getMaxConcurrentApiRequests());
        $cliMulti->runAsSuperUser();
        $cliMulti->onProcessFinish(function ($pid) {
            $this->printPerformanceStatsForProcess($pid);
        });
        return $cliMulti;
    }

    public function setUrlToPiwik($url)
    {
        $this->urlToPiwik = $url;
    }

    private function printPerformanceStatsForProcess($childPid)
    {
        if (!$this->isArchiveProfilingEnabled) {
            return;
        }

        $data = Logger::getMeasurementsFor(getmypid(), $childPid);
        if (empty($data)) {
            return;
        }

        $message = "";
        foreach ($data as $request => $measurements) {
            $message .= "PERFORMANCE FOR " . $request . "\n  ";
            $message .= implode("\n  ", $measurements) . "\n";
        }
        $this->logger->info($message);
    }

    private function hasReachedMaxConcurrentArchivers()
    {
        $cliMulti = $this->makeCliMulti();
        if ($this->maxConcurrentArchivers && $cliMulti->supportsAsync()) {
            $numRunning = 0;
            $processes = Process::getListOfRunningProcesses();
            $instanceId = SettingsPiwik::getPiwikInstanceId();

            foreach ($processes as $process) {
                if (strpos($process, 'console core:archive') !== false &&
                    (!$instanceId
                        || strpos($process, '--matomo-domain=' . $instanceId) !== false
                        || strpos($process, '--matomo-domain="' . $instanceId . '"') !== false
                        || strpos($process, '--matomo-domain=\'' . $instanceId . "'") !== false
                        || strpos($process, '--piwik-domain=' . $instanceId) !== false
                        || strpos($process, '--piwik-domain="' . $instanceId . '"') !== false
                        || strpos($process, '--piwik-domain=\'' . $instanceId . "'") !== false)) {
                    $numRunning++;
                }
            }
            if ($this->maxConcurrentArchivers < $numRunning) {
                $this->logger->info(sprintf("Archiving will stop now because %s archivers are already running and max %s are supposed to run at once.", $numRunning, $this->maxConcurrentArchivers));
                return true;
            } else {
                $this->logger->info(sprintf("%s out of %s archivers running currently", $numRunning, $this->maxConcurrentArchivers));
            }
        }
        return false;
    }
}
