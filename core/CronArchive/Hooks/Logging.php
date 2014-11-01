<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive\Hooks;

use Piwik\ArchiveProcessor\Rules;
use Piwik\CronArchive\AlgorithmLogger;
use Piwik\CronArchive\AlgorithmOptions;
use Piwik\CronArchive\AlgorithmState;
use Piwik\CronArchive\AlgorithmStatistics;
use Piwik\CronArchive\Hooks;
use Piwik\MetricsFormatter;
use Piwik\Url;
use Piwik\Version;

/**
 * TODO
 */
class Logging extends Hooks
{
    public function onInit(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        $this->startInitSection($logger);

        $logger->logSection("NOTES");

        // Recommend to disable browser archiving when using this script
        if (Rules::isBrowserTriggerEnabled()) {
            $logger->log("- If you execute this script at least once per hour (or more often) in a crontab, you may disable 'Browser trigger archiving' in Piwik UI > Settings > General Settings. ");
            $logger->log("  See the doc at: http://piwik.org/docs/setup-auto-archiving/");
        }
        $logger->log("- Reports for today will be processed at most every " . $state->getTodayArchiveTimeToLive()
            . " seconds. You can change this value in Piwik UI > Settings > General Settings.");
        $logger->log("- Reports for the current week/month/year will be refreshed at most every "
            . $state->getProcessPeriodsMaximumEverySeconds() . " seconds.");

        $lastSuccessRunTimestamp = $state->getLastSuccessRunTimestamp();
        if ($lastSuccessRunTimestamp !== false) {
            $dateLast = time() - $lastSuccessRunTimestamp;
            $logger->log("- Archiving was last executed without error " . MetricsFormatter::getPrettyTimeFromSeconds($dateLast, true, $isHtml = false) . " ago");
        }

        $periodsToProcess = $state->getPeriodsToProcess();
        if (!empty($periodsToProcess)) {
            $logger->log("- Will process the following periods: " . implode(", ", $periodsToProcess) . " (--force-periods)");
        }

        if (count($options->shouldArchiveSpecifiedSites) > 0) {
            $logger->log("- Will process " . count($options->shouldArchiveSpecifiedSites) . " websites (--force-idsites)");
        } else if ($options->shouldArchiveAllSites) {
            $logger->log("- Will process all " . count($state->getAllWebsites()) . " websites");
        } else {
            // log websites with visits since last run
            $shouldArchiveOnlySitesWithTrafficSince = $state->getShouldArchiveOnlySitesWithTrafficSinceLastNSecs();
            $websitesWithVisit = $state->getWebsitesWithVisitsSinceLastRun();

            $prettySeconds = MetricsFormatter::getPrettyTimeFromSeconds($shouldArchiveOnlySitesWithTrafficSince, true, false);
            $logger->log("- Will process " . count($websitesWithVisit) . " websites with new visits since $prettySeconds"
                . " , IDs: [" . implode(", ", $websitesWithVisit) . "]");

            // log websites with invalidated data
            $websitesInvalidatedOldReports = $state->getWebsitesWithInvalidatedArchiveData();
            if (count($websitesInvalidatedOldReports) > 0) {
                $logger->log("- Will process " . count($websitesInvalidatedOldReports)
                    . " other websites because some old data reports have been invalidated (eg. using the Log Import script) "
                    . ", IDs: [" . implode(", ", $websitesInvalidatedOldReports) . "]");
            }

            // log websites with new day in site's timezone
            $websiteDayHasFinishedSinceLastRun = $state->getWebsitesInTimezoneWithNewDay();
            if (count($websiteDayHasFinishedSinceLastRun) > 0) {
                $websiteDayHasFinishedSinceLastRun = array_diff(
                    $websiteDayHasFinishedSinceLastRun,
                    $websitesWithVisit,
                    $state->getWebsitesWithInvalidatedArchiveData()
                );

                $logger->log("- Will process " . count($websiteDayHasFinishedSinceLastRun)
                    . " other websites because the last time they were archived was on a different day (in the website's timezone) "
                    . ", IDs: [" . implode(", ", $websiteDayHasFinishedSinceLastRun) . "]");
            }
        }

        // log segments that will be applied to all sites
        $segments = $state->getSegmentsForAllSites();
        $logger->log("- Will pre-process " . count($segments) . " Segments for each website and each period: " . implode(", ", $segments));

        // TODO: some of these logs duplicate logic in AlgorithmState (mostly conditions). perhaps adding a onPropertyComputed event
        //       would help w/ that.

        // log warnings
        if (!empty($options->forceTimeoutPeriod)
            && $options->forceTimeoutPeriod > $state->getTodayArchiveTimeToLive()
        ) {
            $logger->log("WARNING: Automatically increasing --force-timeout-for-periods from {$options->forceTimeoutPeriod} to "
                . $state->getTodayArchiveTimeToLive()
                . " to match the cache timeout for Today's report specified in Piwik UI > Settings > General Settings");
        }

        if ($options->shouldStartProfiler) {
            $logger->log("XHProf profiling is enabled.");
        }
    }

    public function onInitTrackerTasks(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        $this->startInitSection($logger);
    }

    public function onStartProcessing(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        $logger->logSection("START");
        $logger->log("Starting Piwik reports archiving...");
    }

    public function onQueuePeriodAndSegmentArchiving(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite)
    {
        $segmentsForSite = $state->getSegmentsForSingleSite($idSite);
        if (!empty($segmentsForSite)) {
            $logger->log("Will pre-process the following " . count($segmentsForSite) . " Segments for this website (id = $idSite): ["
                . implode(", ", $segmentsForSite) . "]");
        }
    }

    public function onEnd(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, AlgorithmStatistics $stats)
    {
        if (empty($stats->errors)) {
            return;
        }

        $logger->logSection("SUMMARY OF ERRORS");
        foreach ($stats->errors as $error) {
            // do not logError since errors are already in stderr
            $logger->log("Error: " . $error);
        }

        $logger->logFatalError(count($stats->errors) . " total errors during this script execution, please investigate and try and fix these errors.");
    }

    public function onEndProcessing(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, AlgorithmStatistics $stats)
    {
        $stats->logSummary($logger, $state);
    }

    public function onStartRunScheduledTasks(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        $logger->logSection("SCHEDULED TASKS");

        if ($options->disableScheduledTasks) {
            $logger->log("Scheduled tasks are disabled with --disable-scheduled-tasks");
            return;
        }

        $logger->log("Starting Scheduled tasks... ");
    }

    public function onEndRunScheduledTasks(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $tasksOutput)
    {
        if ($tasksOutput == \Piwik\DataTable\Renderer\Csv::NO_DATA_AVAILABLE) {
            $tasksOutput = " No task to run";
        }

        $logger->log($tasksOutput);
        $logger->log("done");
        $logger->logSection("");
    }

    public function onApiRequestError(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $url, $errorMessage)
    {
        if (is_array($url)) {
            $url = Url::getQueryStringFromParameters($url);
        }

        $message = "Got invalid response from API request: $url. ";
        if (empty($errorMessage)) {
            $message .= "The response was empty. This usually means a server error. This solution to this error is generally to increase "
                      . "the value of 'memory_limit' in your php.ini file. Please check your Web server Error Log file for more details.";
        } else {
            $message .= "Response was '$errorMessage'";
        }

        $this->onError($options, $state, $logger, $message);
    }

    public function onError(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $errorMessage)
    {
        $logger->logError($errorMessage);
    }

    public function onSkipWebsiteDayArchiving(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite, $reason)
    {
        $logger->log("Skipped day archiving for website id $idSite, $reason");
    }

    public function onSkipWebsitePeriodArchiving(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite, $reason)
    {
        $logger->log("Skipped period archiving for website id $idSite, $reason");
    }

    public function onArchiveRequestFinished(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $requestParams,
                                             $visitsInThisPeriod, $visitsInLastPeriods)
    {
        $idSite = @$requestParams['idSite'] ?: '';
        $date = @$requestParams['date'] ?: '';
        $period = @$requestParams['period'] ?: '';
        $segment = @$requestParams['segment'] ?: '';

        if (substr($date, 0, 4) === 'last') {
            $visitsInLastPeriods = (int)$visitsInLastPeriods . " visits in last " . $date . " " . $period . "s, ";
            $thisPeriod = $period == "day" ? "today" : "this " . $period;
            $visitsInLastPeriod = (int)$visitsInThisPeriod . " visits " . $thisPeriod . ", ";
        } else {
            $visitsInLastPeriods = (int)$visitsInLastPeriods . " visits in " . $period . "s included in: $date, ";
            $visitsInLastPeriod = '';
        }

        // TODO: used to use $timer
        $logger->log("Archived website id = $idSite, period = $period, " . $visitsInLastPeriods . $visitsInLastPeriod . " [segment = $segment]");
    }

    public function onSiteArchivingFinished(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite)
    {
        $logger->log("Archived website id = $idSite, "
            // TODO: . $timerWebsite->__toString()
            . " [" . $state->getProcessedWebsitesSemaphore()->get() . "/"
            . count($state->getWebsitesToArchive())
            . " done]");
    }

    private function startInitSection(AlgorithmLogger $logger)
    {
        $logger->logSection("INIT");
        $logger->log("Running Piwik " . Version::VERSION . " as Super User");
    }
}