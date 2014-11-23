<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive\Hooks;

use Piwik\Concurrency\Semaphore;
use Piwik\CronArchive;
use Piwik\CronArchive\AlgorithmLogger;
use Piwik\CronArchive\AlgorithmOptions;
use Piwik\CronArchive\AlgorithmRules;
use Piwik\CronArchive\Hooks;
use Piwik\MetricsFormatter;

/**
 * CronArchive statistics calculating logic.
 */
class Statistics extends Hooks
{
    const SEMAPHORE_PREFIX = 'CronArchive.';

    /**
     * Semaphore that holds the number of websites with visits today. When day archiving finishes,
     * the value is incremented if the visit count for the day is > 0.
     *
     * @var Semaphore
     */
    public $countOfWebsitesWithVisitsToday;

    /**
     * Semaphore that holds the number of days skipped because existing archive data is still valid.
     *
     * When day archiving is skipped and the reason string starts with 'was archived', the value is
     * incremented.
     *
     * @var Semaphore
     */
    public $dayArchivingsSkippedBecauseArchivesStillValid;

    /**
     * Semaphore that holds the number of periods skipped because existing archive data is still valid.
     *
     * When period archiving is skipped and the reason string starts with 'was archived', the value is
     * incremented.
     *
     * @var Semaphore
     */
    public $periodArchivingsSkippedBecauseArchivesStillValid;

    /**
     * Semaphore that holds the total number of visits today.
     *
     * When day archiving is finished for the 'today' date, the number of visits is added to this value.
     *
     * @var Semaphore
     */
    public $totalNumberOfVisitsToday;

    /**
     * Semaphore that holds the number of websites successfully processed.
     *
     * When archiving for a site is completed, this value is incremented.
     *
     * @var Semaphore
     */
    public $countOfWebsitesSuccessfullyProcessed;

    /**
     * Semaphore that contains the number of websites for whom period archiving finished.
     *
     * When period archiving for a site is completed, this value is incremented.
     *
     * @var Semaphore
     */
    public $countOfWebsitesWhosePeriodsWereArchived;

    /**
     * Semaphore that contains the total number of archiving API requests made. After every API request
     * is finished, this value is incremented.
     *
     * @var Semaphore
     */
    public $totalArchivingApiRequestsMade;

    /**
     * Semaphore that contains the total number of errors that occurred during this CronArchive run.
     *
     * @var Semaphore
     */
    public $errors;

    /**
     * Semaphores that contains the elapsed time for the archiving of each site. When an archiving request
     * finishes, the elapsed time for the request is added to the semaphore for the idSite in the request.
     *
     * @var Semaphore[]
     */
    public $elapsedArchivingTimePerSite = array();

    /**
     * The start time of archiving.
     *
     * @var int
     */
    public $cronArchiveStartTime;

    /**
     * Constructor.
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
        $this->errors = $this->makeSemaphore('errors');
    }

    /** TODO: deal w/ following concurrency issue
     * - cron:archive started
     * - while cron:archive running, another cron:archive started
     * - stats for first cron:archive overwritten & merged w/ second
     *
     * there needs to be a run ID for cronarchive. store in options.
     */

    public function onInit(CronArchive $context, AlgorithmOptions $options, AlgorithmRules $state, AlgorithmLogger $logger)
    {
        // reset counters & lists, in case they exist in the DB
        $this->countOfWebsitesWithVisitsToday->set(0);
        $this->dayArchivingsSkippedBecauseArchivesStillValid->set(0);
        $this->periodArchivingsSkippedBecauseArchivesStillValid->set(0);
        $this->totalNumberOfVisitsToday->set(0);
        $this->countOfWebsitesSuccessfullyProcessed->set(0);
        $this->countOfWebsitesWhosePeriodsWereArchived->set(0);
        $this->totalArchivingApiRequestsMade->set(0);
        $this->errors->set(0);
        $this->cronArchiveStartTime = time();

        // create and reset site specific semaphores
        foreach ($state->getWebsitesToArchive() as $idSite) {
            $semaphore = $this->makeSemaphore('elapsedArchivingTimePerSite', $idSite);
            $semaphore->set(0);
            $this->elapsedArchivingTimePerSite[$idSite] = $semaphore;
        }
    }

    public function onApiRequestError(CronArchive $context, AlgorithmOptions $options, AlgorithmRules $state, AlgorithmLogger $logger,
                                      $url, $errorMessage)
    {
        $this->errors->increment();
    }

    public function onError(CronArchive $context, AlgorithmOptions $options, AlgorithmRules $state, AlgorithmLogger $logger, $errorMessage)
    {
        $this->errors->increment();
    }

    public function onSkipWebsiteDayArchiving(CronArchive $context, AlgorithmOptions $options, AlgorithmRules $state, AlgorithmLogger $logger,
                                              $idSite, $reason)
    {
        // if reason for skipping was data is still valid, increment $dayArchivingsSkippedBecauseArchivesStillValid
        if (strpos($reason, 'was archived') === 0) {
            $this->dayArchivingsSkippedBecauseArchivesStillValid->increment();
        }
    }

    public function onSkipWebsitePeriodArchiving(CronArchive $context, AlgorithmOptions $options, AlgorithmRules $state, AlgorithmLogger $logger,
                                                 $idSite, $reason)
    {
        // if reason for skipping was data is still valid, increment $periodArchivingsSkippedBecauseArchivesStillValid
        if (strpos($reason, 'was archived') === 0) {
            $this->periodArchivingsSkippedBecauseArchivesStillValid->increment();
        }
    }

    public function onArchiveRequestFinished(CronArchive $context, AlgorithmOptions $options, AlgorithmRules $state, AlgorithmLogger $logger,
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

    public function onSiteArchivingFinished(CronArchive $context, AlgorithmOptions $options, AlgorithmRules $state, AlgorithmLogger $logger, $idSite)
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

    private function getPrimitiveName($name, $idSite)
    {
        $primitiveName = self::SEMAPHORE_PREFIX . $name;
        if ($idSite !== null) {
            $primitiveName .= '.' . $idSite;
        }
        return $primitiveName;
    }
}