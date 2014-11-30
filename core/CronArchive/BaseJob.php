<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive;

use Exception;
use Piwik\CronArchive;
use Piwik\Jobs\Job;
use Piwik\Piwik;
use Piwik\Timer;
use Piwik\Url;

/**
 * Base class for CronArchive jobs.
 *
 * CronArchive jobs just send a request to the API.get API method. This will launch archiving for
 * a site, period & segment.
 *
 * CronArchive jobs should be able to be executed from different machines if necessary.
 */
class BaseJob extends Job
{
    /**
     * The CronArchive options used in this CronArchive run. The options are stored with the Job to reconstruct
     * a CronArchive instance that is equivalent to the original when executing job callbacks. (ie before job
     * starts code & after job finishes code). This allows the jobs to be executed in different processes or
     * on different machines. As long as the configuration for Piwik is the same, the behaviour should be the
     * same.
     *
     * @var AlgorithmOptions
     */
    protected $cronArchiveOptions;

    /**
     * The Timer instance used to time this job's execution.
     *
     * @var Timer
     */
    protected $timer;

    /**
     * The elapsed time it took to run this job. Computed in jobFinished().
     *
     * @var int
     */
    protected $elapsedTime;

    /**
     * Constructor.
     *
     * @param int $idSite
     * @param string $date
     * @param string $period
     * @param string $segment
     * @param AlgorithmOptions $options See {@link $cronArchiveOptions}.
     */
    public function __construct($idSite, $date, $period, $segment, AlgorithmOptions $options)
    {
        $url = array(
            'module' => 'API',
            'method' => 'API.get',
            'idSite' => $idSite,
            'period' => $period,
            'date' => $date,
            'format' => 'php'
        );
        if (!empty($segment)) {
            $url['segment'] = $segment;
        }
        $url = $options->getProcessedUrl($url);

        parent::__construct($url);

        $this->cronArchiveOptions = $options;
    }

    public function jobStarting()
    {
        parent::jobStarting();

        $this->timer = new Timer();
    }

    public function jobFinished($response)
    {
        parent::jobFinished($response);

        $this->elapsedTime = $this->timer->getTimeMs($decimals = 0);
    }

    protected function parseJobUrl()
    {
        $url = $this->url;
        if (empty($url['idSite'])
            || empty($url['date'])
            || empty($url['period'])
        ) {
            throw new Exception("Invalid CronArchive job URL found in job callback: '" . $this->getUrlString() . "'"); // sanity check
        }

        return array($url['idSite'], $url['date'], $url['period'], @$url['segment']);
    }

    protected function parseVisitsApiResponse(CronArchive $context, $textResponse, $idSite)
    {
        $context->getAlgorithmRules()->getActiveRequestsSemaphore($idSite)->decrement();

        $response = @unserialize($textResponse);

        $visits = $visitsLast = null;

        if (!empty($textResponse)
            && $context->checkApiResponse($textResponse, $this->url)
            && is_array($response)
            && count($response) != 0
        ) {
            $visits = $this->getVisitsLastPeriodFromApiResponse($response);
            $visitsLast = $this->getVisitsFromApiResponse($response);

            // if the response is valid, decrement the failed requests semaphore
            $context->getAlgorithmRules()->getFailedRequestsSemaphore($idSite)->decrement();
        }

        return array($visits, $visitsLast);
    }

    protected function archivingRequestFinished(CronArchive $context, $idSite, $visits, $visitsLast)
    {
        $context->executeHook('onArchiveRequestFinished', array($this->url, $visits, $visitsLast, $this->elapsedTime));

        if ($context->getAlgorithmRules()->getActiveRequestsSemaphore($idSite)->get() == 0) {
            $processedWebsitesCount = $context->getAlgorithmRules()->getProcessedWebsitesSemaphore();
            $processedWebsitesCount->increment();

            $completed = $context->getAlgorithmRules()->getShouldProcessAllPeriods();

            /**
             * This event is triggered immediately after the cron archiving process starts archiving data for a single
             * site.
             *
             * @param int $idSite The ID of the site we're archiving data for.
             * @param bool $completed `true` if every period was processed for a site, `false` if due to command line
             *                        arguments, one or more periods is skipped.
             */
            Piwik::postEvent('CronArchive.archiveSingleSite.finish', array($idSite, $completed));

            $context->executeHook('onSiteArchivingFinished', array($idSite));
        }
    }

    protected function makeCronArchiveContext()
    {
        return new CronArchive($this->cronArchiveOptions);
    }

    protected function handleError(CronArchive $context, $errorMessage)
    {
        $context->executeHook('onError', array($errorMessage));
    }

    private function getVisitsLastPeriodFromApiResponse($stats)
    {
        if (empty($stats)) {
            return 0;
        }

        $today = end($stats);

        if (!isset($today['nb_visits'])) {
            return 0;
        }

        return $today['nb_visits'];
    }

    private function getVisitsFromApiResponse($stats)
    {
        if (empty($stats)) {
            return 0;
        }

        $visits = 0;
        foreach($stats as $metrics) {
            if (empty($metrics['nb_visits'])) {
                continue;
            }
            $visits += $metrics['nb_visits'];
        }

        return $visits;
    }
}