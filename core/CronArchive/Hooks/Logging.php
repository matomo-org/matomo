<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive\Hooks;

use Piwik\ArchiveProcessor\Rules;
use Piwik\CronArchive;
use Piwik\CronArchive\AlgorithmLogger;
use Piwik\CronArchive\AlgorithmOptions;
use Piwik\CronArchive\AlgorithmRules;
use Piwik\CronArchive\Hooks;
use Piwik\Metrics;
use Piwik\Url;
use Piwik\Version;

/**
 * CronArchive logging logic.
 */
class Logging extends Hooks
{
    private $formatter;

    public function __construct()
    {
        $this->formatter = new Metrics\Formatter();
    }

    public function onInit(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger)
    {
        /** @var AlgorithmOptions $options */
        $options = $context->getHooks("Piwik\\CronArchive\\AlgorithmOptions");

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
            $logger->log("- Archiving was last executed without error " . $this->formatter->getPrettyTimeFromSeconds($dateLast, true) . " ago");
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

            $prettySeconds = $this->formatter->getPrettyTimeFromSeconds($shouldArchiveOnlySitesWithTrafficSince, true);
            $logger->log("- Will process " . count($websitesWithVisit) . " websites with new visits since $prettySeconds"
                . ", IDs: [" . implode(", ", $websitesWithVisit) . "]");

            // log websites with invalidated data
            $websitesInvalidatedOldReports = $state->getWebsitesWithInvalidatedArchiveData();
            if (count($websitesInvalidatedOldReports) > 0) {
                $logger->log("- Will process " . count($websitesInvalidatedOldReports)
                    . " other websites because some old data reports have been invalidated (eg. using the Log Import script)"
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
                    . " other websites because the last time they were archived was on a different day (in the website's timezone)"
                    . ", IDs: [" . implode(", ", $websiteDayHasFinishedSinceLastRun) . "]");
            }
        }

        // log segments that will be applied to all sites
        $segments = $state->getSegmentsForAllSites();
        $logger->log("- Will pre-process " . count($segments) . " Segments for each website and each period: " . implode(", ", $segments));

        // log segments that will be applied to individual sites
        foreach ($state->getWebsitesToArchive() as $idSite) {
            $segmentsForSite = $state->getSegmentsForSingleSite($idSite);
            if (!empty($segmentsForSite)) {
                $logger->log("- Will pre-process the following " . count($segmentsForSite) . " Segments for this website (id = $idSite): ["
                    . implode(", ", $segmentsForSite) . "]");
            }
        }
    }

    public function onInitTrackerTasks(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger)
    {
        $this->startInitSection($logger);
    }

    public function onStartProcessing(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger)
    {
        $logger->logSection("START");
        $logger->log("Starting Piwik reports archiving...");
    }

    public function onEnd(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger)
    {
        /** @var Statistics $stats */
        $stats = $context->getHooks("Piwik\\CronArchive\\Hooks\\Statistics");

        $errorCount = $stats->errors->get();
        if ($errorCount <= 0) {
            return;
        }

        $logger->logFatalError("$errorCount total errors during this script execution, please investigate and try and fix these errors. See CronArchive and job server logs for more information.");
    }

    public function onEndProcessing(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger)
    {
        $logger->log("Done archiving!");

        /** @var Statistics $stats */
        $stats = $context->getHooks("Piwik\\CronArchive\\Hooks\\Statistics");
        if (empty($stats)) {
            return;
        }

        $websites = $state->getWebsitesToArchive();
        $processedCount = $stats->countOfWebsitesSuccessfullyProcessed->get();
        $visitsToday = $stats->totalNumberOfVisitsToday->get();
        $apiRequestsMade = $stats->totalArchivingApiRequestsMade->get();
        $countOfWebsitesWhoseDaysWereArchived = $stats->countOfWebsitesWhoseDaysWereArchived->get();
        $countOfWebsitesWhosePeriodsWereArchived = $stats->countOfWebsitesWhosePeriodsWereArchived->get();

        $logger->logSection("SUMMARY");
        $logger->log("Total visits for today across archived websites: " . $visitsToday);

        $logger->log("Archived today's reports for $countOfWebsitesWhoseDaysWereArchived websites");
        $logger->log("Archived week/month/year for $countOfWebsitesWhosePeriodsWereArchived websites");
        $logger->log("Skipped {$stats->dayArchivingsSkippedBecauseArchivesStillValid->get()} websites day archiving");
        $logger->log("Skipped {$stats->periodArchivingsSkippedBecauseArchivesStillValid->get()} websites week/month/year archiving");
        $logger->log("Total API requests: $apiRequestsMade");

        //DONE: done/total, visits, wtoday, wperiods, reqs, time, errors[count]: first eg.
        $percent = count($websites) == 0
            ? ""
            : " " . round($processedCount * 100 / count($websites), 0) . "%";

        /** @var Statistics $stats */
        $stats = $context->getHooks("Piwik\\CronArchive\\Hooks\\Statistics");
        $errorCount = $stats->errors->get();

        $logger->log("done: " .
            $processedCount . "/" . count($websites) . "" . $percent . ", " .
            $visitsToday . " vtoday, $countOfWebsitesWhoseDaysWereArchived wtoday, $countOfWebsitesWhosePeriodsWereArchived wperiods, " .
            $apiRequestsMade . " req, " . $stats->getTotalCronArchiveTimePretty() . ", " .
            ($errorCount <= 0 ? "no error" : ($errorCount . " errors."))
        );
    }

    public function onStartRunScheduledTasks(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger)
    {
        /** @var AlgorithmOptions $options */
        $options = $context->getHooks("Piwik\\CronArchive\\AlgorithmOptions");

        $logger->logSection("SCHEDULED TASKS");

        if ($options->disableScheduledTasks) {
            $logger->log("Scheduled tasks are disabled with --disable-scheduled-tasks");
            return;
        }

        $logger->log("Starting Scheduled tasks... ");
    }

    public function onEndRunScheduledTasks(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger, $tasksOutput)
    {
        if ($tasksOutput == \Piwik\DataTable\Renderer\Csv::NO_DATA_AVAILABLE) {
            $tasksOutput = " No task to run";
        }

        $logger->log($tasksOutput);
        $logger->log("done");
        $logger->logSection("");
    }

    public function onApiRequestError(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger, $url, $errorMessage)
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

        $this->onError($context, $state, $logger, $message);
    }

    public function onError(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger, $errorMessage)
    {
        $logger->logError($errorMessage);
    }

    public function onSkipWebsiteDayArchiving(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger, $idSite, $reason)
    {
        $logger->log("Skipped day archiving for website id $idSite, $reason");
    }

    public function onSkipWebsitePeriodArchiving(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger, $idSite, $reason)
    {
        $logger->log("Skipped period archiving for website id $idSite, $reason");
    }

    public function onArchiveRequestFinished(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger, $requestParams, $visits, $visitsLast, $elapsedTime)
    {
        $idSite = @$requestParams['idSite'] ?: '';
        $date = @$requestParams['date'] ?: '';
        $period = @$requestParams['period'] ?: '';
        $segment = @$requestParams['segment'] ?: '';

        if (substr($date, 0, 4) === 'last') {
            $visitsLast = (int)$visitsLast . " visits in last " . $date . " " . $period . "s, ";
            $thisPeriod = $period == "day" ? "today" : "this " . $period;
            $visitsInLastPeriod = (int)$visits . " visits " . $thisPeriod;
        } else {
            $visitsLast = (int)$visitsLast . " visits in " . $period . "s included in: $date";
            $visitsInLastPeriod = '';
        }

        if (!empty($segment)) {
            $segmentsDesc = "with segment, ";
            $segmentsDescSuffix = " [segment = $segment]";
        } else {
            $segmentsDesc = "";
            $segmentsDescSuffix = "";
        }

        $elapsedTime = $this->formatter->getPrettyTimeFromSeconds($elapsedTime / 1000.0, true, false);
        $logger->log("Archived website id = $idSite, period = $period, $segmentsDesc" . $visitsLast . $visitsInLastPeriod . " in $elapsedTime$segmentsDescSuffix");
    }

    public function onSiteArchivingFinished(CronArchive $context, AlgorithmRules $state, AlgorithmLogger $logger, $idSite)
    {
        $elapsedTime = "";

        /** @var Statistics $stats */
        $stats = $context->getHooks("Piwik\\CronArchive\\Hooks\\Statistics");
        if (!empty($stats)
            && !empty($stats->elapsedArchivingTimePerSite[$idSite])
        ) {
            $elapsedTimeMs = $stats->elapsedArchivingTimePerSite[$idSite]->get();
            $elapsedTime = " in " . $this->formatter->getPrettyTimeFromSeconds($elapsedTimeMs / 1000.0, true, false);
        }

        $logger->log("Archived website id = $idSite$elapsedTime"
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