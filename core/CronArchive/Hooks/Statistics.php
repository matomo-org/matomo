<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive\Hooks;

use Piwik\Concurrency\AtomicList;
use Piwik\Concurrency\Semaphore;
use Piwik\CronArchive;
use Piwik\CronArchive\AlgorithmLogger;
use Piwik\CronArchive\AlgorithmOptions;
use Piwik\CronArchive\AlgorithmState;
use Piwik\CronArchive\Hooks;
use Piwik\MetricsFormatter;

/**
 * CronArchive statistics calculating logic.
 */
class Statistics extends Hooks
{
    const SEMAPHORE_PREFIX = 'CronArchive.';

    /**
     * TODO
     *    -> # of websites with visits today
     *       * on day archiving finished, ++value if visits > 0
     *
     * @var Semaphore
     */
    public $countOfWebsitesWithVisitsToday;

    /**
     * TODO
     *    -> days skipped because archives still valid
     *       * on skip if reason starts w/ 'was archived' ++value
     *
     * @var Semaphore
     */
    public $dayArchivingsSkippedBecauseArchivesStillValid;

    /**
     * TODO
     *    -> periods skipped because archives still valid
     *       * on skip period archiving w/ reason starts w/ 'was archived' ++value
     *
     * @var Semaphore
     */
    public $periodArchivingsSkippedBecauseArchivesStillValid;

    /**
     * TODO
     *    -> total number of visits today
     *       * on day archiving finished, value += visits
     *
     * @var Semaphore
     */
    public $totalNumberOfVisitsToday;

    /**
     * TODO
     *    -> websites successfully processed
     *       * on site archiving finished ++value
     *
     * @var Semaphore
     */
    public $countOfWebsitesSuccessfullyProcessed;

    /**
     * TODO
     *
     * @var Semaphore
     */
    public $countOfWebsitesWhosePeriodsWereArchived;

    /**
     * TODO
     *
     * @var Semaphore
     */
    public $totalArchivingApiRequestsMade;

    /**
     * TODO
     *    -> errors
     *       * on any error (network or otherwise), push to distributed list (new class AtomicList in core/Concurrency)
     *
     * @var AtomicList
     */
    public $errors;

    /**
     * TODO
     *    -> total time spent archiving for website
     *       * on site request finished, add elapsed time to semaphore
     *
     * @var Semaphore[]
     */
    public $elapsedArchivingTimePerSite = array();

    /**
     * TODO
     *
     * @var int
     */
    public $cronArchiveStartTime;

    /**
     * TODO
     */
    public function __construct()
    {
        $this->countOfWebsitesWithVisitsToday = $this->makeSemaphore('countOfWebsitesWithVisitsToday');
        $this->dayArchivingsSkippedBecauseArchivesStillValid = $this->makeSemaphore('dayArchivingsSkippedBecauseArchivesStillValid');
        $this->periodArchivingsSkippedBecauseArchivesStillValid = $this->makeSemaphore('$periodArchivingsSkippedBecauseArchivesStillValid');
        $this->totalNumberOfVisitsToday = $this->makeSemaphore('totalNumberOfVisitsToday');
        $this->countOfWebsitesSuccessfullyProcessed = $this->makeSemaphore('countOfWebsitesSuccessfullyProcessed');
        $this->countOfWebsitesWhosePeriodsWereArchived = $this->makeSemaphore('countOfWebsitesWhosePeriodsWereArchived');
        $this->totalArchivingApiRequestsMade = $this->makeSemaphore('totalArchivingApiRequestsMade');
        $this->errors = $this->makeAtomicLlist('errors');
    }

    /** TODO: deal w/ following concurrency issue
     * - cron:archive started
     * - while cron:archive running, another cron:archive started
     * - stats for first cron:archive overwritten & merged w/ second
     *
     * there needs to be a run ID for cronarchive. store in options.
     */

    public function onInit(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // reset counters & lists, in case they exist in the DB
        $this->countOfWebsitesWithVisitsToday->set(0);
        $this->dayArchivingsSkippedBecauseArchivesStillValid->set(0);
        $this->periodArchivingsSkippedBecauseArchivesStillValid->set(0);
        $this->totalNumberOfVisitsToday->set(0);
        $this->countOfWebsitesSuccessfullyProcessed->set(0);
        $this->countOfWebsitesWhosePeriodsWereArchived->set(0);
        $this->totalArchivingApiRequestsMade->set(0);
        $this->errors->clear();
        $this->cronArchiveStartTime = time();

        // create and reset site specific semaphores
        foreach ($state->getWebsitesToArchive() as $idSite) {
            $semaphore = $this->makeSemaphore('elapsedArchivingTimePerSite', $idSite);
            $semaphore->set(0);
            $this->elapsedArchivingTimePerSite[$idSite] = $semaphore;
        }
    }

    public function onApiRequestError(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger,
                                      $url, $errorMessage)
    {
        // push error to list for summary of errors
        $this->errors->push(array("API error: $errorMessage [for $url]"));
    }

    public function onError(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $errorMessage)
    {
        // push error to list for summary of errors
        $this->errors->push(array($errorMessage));
    }

    public function onSkipWebsiteDayArchiving(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger,
                                              $idSite, $reason)
    {
        // if reason for skipping was data is still valid, increment $dayArchivingsSkippedBecauseArchivesStillValid
        if (strpos($reason, 'was archived') === 0) {
            $this->dayArchivingsSkippedBecauseArchivesStillValid->increment();
        }
    }

    public function onSkipWebsitePeriodArchiving(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger,
                                                 $idSite, $reason)
    {
        // if reason for skipping was data is still valid, increment $periodArchivingsSkippedBecauseArchivesStillValid
        if (strpos($reason, 'was archived') === 0) {
            $this->periodArchivingsSkippedBecauseArchivesStillValid->increment();
        }
    }

    public function onArchiveRequestFinished(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger,
                                             $requestParams, $visits, $visitsLast, $elapsedTime)
    {
        $idSite = @$requestParams['idSite'];
        $period = @$requestParams['period'];

        // if this request was archiving for days and there are visits for today, increment countOfWebsitesWithVisitsToday
        // and add todays visits to the totalNumberOfVisitsToday stat
        if ($period == 'day'
            && $visits > 0
        ) {
            $this->countOfWebsitesWithVisitsToday->increment();
            $this->totalNumberOfVisitsToday->advance($visits);
        }

        // if this request was archiving for a non-day period, increment countOfWebsitesWhosePeriodsWereArchived
        if ($period != 'day') {
            $this->countOfWebsitesWhosePeriodsWereArchived->increment();
        }

        // add elapsed time to site archiving total
        $semaphore = @$this->elapsedArchivingTimePerSite[$idSite];
        if (!empty($semaphore)) {
            $semaphore->advance($elapsedTime);
        }

        // increment total number of API requests semaphore
        $this->totalArchivingApiRequestsMade->increment();
    }

    public function onSiteArchivingFinished(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite)
    {
        // increment $countOfWebsitesSuccessfullyProcessed since site has been successfully processed
        $this->countOfWebsitesSuccessfullyProcessed->increment();
    }

    public function getTotalCronArchiveTimePretty()
    {
        $elapsed = time() - $this->cronArchiveStartTime;
        return MetricsFormatter::getPrettyTimeFromSeconds($elapsed, true, false);
    }

    private function makeSemaphore($name, $idSite = null)
    {
        return new Semaphore($this->getPrimitiveName($name, $idSite));
    }

    private function makeAtomicLlist($name, $idSite = null)
    {
        return new AtomicList($this->getPrimitiveName($name, $idSite));
    }

    private function getPrimitiveName($name, $idSite)
    {
        $primitiveName = self::SEMAPHORE_PREFIX . $name;
        if ($idSite !== null) {
            $primitiveName .= '.' . $idSite;
        }
        return $primitiveName;
    }
}