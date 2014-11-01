<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive;

/**
 * TODO
 *
 * TODO: make all stats calculating concurrent
 *
 * TODO: revise all statistics; from old code some stats do not make sense. at least they don't based
 *       on how they're calculated.
 *       related: TODO: need to log time of archiving for websites (in summary)
 *
 * Useful stats:
 *    -> # of websites with visits today
 *       * on day archiving finished, ++value if visits > 0
 *    -> days skipped because archives still valid
 *       * on skip if reason starts w/ 'was archived' ++value
 *    -> periods skipped because archives still valid
 *       * on skip period archiving w/ reason starts w/ 'was archived' ++value
 *    -> elapsed time of each request
 *       * on start job, set current time to value
 *       * on finish job, get start time & calculate elapsed]
 *    -> total number of visits today
 *       * on day archiving finished, value += visits
 *    -> errors
 *       * on any error (network or otherwise), push to distributed list (new class AtomicList in core/Concurrency)
 *    -> websites successfully processed
 *       * on site archiving finished ++value
 */
class AlgorithmStatistics
{
    /**
     * TODO
     *
     * @var int
     */
    public $websitesWithVisitsSinceLastRun = 0;

    /**
     * TODO
     *
     * @var int
     */
    public $skippedPeriodsArchivesWebsite = 0;

    /**
     * TODO
     *
     * @var int
     */
    public $skippedDayArchivesWebsites = 0;

    /**
     * TODO
     *
     * @var int
     */
    public $skipped = 0;

    /**
     * TODO
     *
     * @var int
     */
    public $processed = 0;

    /**
     * TODO
     *
     * @var int
     */
    public $archivedPeriodsArchivesWebsite = 0;

    /**
     * TODO
     *
     * @var int
     */
    public $visitsToday = 0;

    /**
     * TODO (docs + logic to calculate)
     *
     * @var int
     */
    public $apiRequestsMade = 0;

    /**
     * TODO
     *
     * @var string[]
     */
    public $errors = array();

    /**
     * TODO
     */
    public function logSummary(AlgorithmLogger $algorithmLogger, AlgorithmState $algorithmState)
    {
        $websites = $algorithmState->getWebsitesToArchive();

        $algorithmLogger->log("Done archiving!");

        $algorithmLogger->logSection("SUMMARY");
        $algorithmLogger->log("Total visits for today across archived websites: " . $this->visitsToday);

        $totalWebsites = count($websites);
        $this->skipped = $totalWebsites - $this->websitesWithVisitsSinceLastRun; // TODO: why does this get overwritten?
        $algorithmLogger->log("Archived today's reports for {$this->websitesWithVisitsSinceLastRun} websites");
        $algorithmLogger->log("Archived week/month/year for {$this->archivedPeriodsArchivesWebsite} websites");
        $algorithmLogger->log("Skipped {$this->skipped} websites: no new visit since the last script execution");
        $algorithmLogger->log("Skipped {$this->skippedDayArchivesWebsites} websites day archiving: existing daily reports are less than {$algorithmState->getTodayArchiveTimeToLive()} seconds old");
        $algorithmLogger->log("Skipped {$this->skippedPeriodsArchivesWebsite} websites week/month/year archiving: existing periods reports are less than {$algorithmState->getProcessPeriodsMaximumEverySeconds()} seconds old");
        $algorithmLogger->log("Total API requests: {$this->apiRequestsMade}");

        //DONE: done/total, visits, wtoday, wperiods, reqs, time, errors[count]: first eg.
        $percent = count($websites) == 0
            ? ""
            : " " . round($this->processed * 100 / count($websites), 0) . "%";
        $algorithmLogger->log("done: " .
            $this->processed . "/" . count($websites) . "" . $percent . ", " .
            $this->visitsToday . " vtoday, {$this->websitesWithVisitsSinceLastRun} wtoday, {$this->archivedPeriodsArchivesWebsite} wperiods, " .
            $this->apiRequestsMade . " req, " /* TODO . round($timer->getTimeMs()) */ . " ms, " .
            (empty($this->errors)
                ? "no error"
                : (count($this->errors) . " errors."))
        );
        // TODO: $algorithmLogger->log($timer->__toString());
    }
}