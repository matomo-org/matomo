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
use Piwik\Log;
use Piwik\Piwik;
use Piwik\Url;
use Piwik\UrlHelper;

/**
 * TODO
 */
class BaseJob extends Job
{
    /**
     * TODO
     *
     * @var AlgorithmOptions
     */
    protected $cronArchiveOptions;

    /**
     * TODO
     */
    public function __construct($idSite, $date, $period, $segment, $token_auth, AlgorithmOptions $options)
    {
        $url = array(
            'module' => 'API',
            'method' => 'API.get',
            'idSite' => $idSite,
            'period' => $period,
            'date' => $date,
            'format' => 'php',
            'token_auth' => $token_auth
        );
        if (!empty($segment)) {
            $url['segment'] = $segment;
        }
        $url = $options->getProcessedUrl($url);

        parent::__construct($url);

        $this->cronArchiveOptions = $options;
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
        $response = @unserialize($textResponse);

        $visits = $visitsLast = null;

        if (!empty($textResponse)
            && $this->checkResponse($context, $textResponse, $this->url)
            && is_array($response)
            && count($response) != 0
        ) {
            $visits = $this->getVisitsLastPeriodFromApiResponse($response);
            $visitsLast = $this->getVisitsFromApiResponse($response);

            $context->getAlgorithmState()->getActiveRequestsSemaphore($idSite)->decrement(); // TODO: this code probably shouldn't be here
        }

        return array($visits, $visitsLast);
    }

    protected function archivingRequestFinished(CronArchive $context, $idSite, $period, $date, $segment, $visits, $visitsLast)
    {
        $this->logArchivedWebsite($context, $idSite, $period, $date, $segment, $visits, $visitsLast); // TODO no timer

        if ($context->getAlgorithmState()->getActiveRequestsSemaphore($idSite)->get() === 0) {
            $processedWebsitesCount = $context->getAlgorithmState()->getProcessedWebsitesSemaphore();
            $processedWebsitesCount->increment();

            $completed = $context->getAlgorithmState()->getShouldProcessAllPeriods();

            /**
             * This event is triggered immediately after the cron archiving process starts archiving data for a single
             * site.
             *
             * @param int $idSite The ID of the site we're archiving data for.
             * @param bool $completed `true` if every period was processed for a site, `false` if due to command line
             *                        arguments, one or more periods is skipped.
             */
            Piwik::postEvent('CronArchive.archiveSingleSite.finish', array($idSite, $completed));

            Log::info("Archived website id = $idSite, "
                //. $requestsWebsite . " API requests, " TODO: necessary to report?
                // TODO: . $timerWebsite->__toString()
                . " [" . $processedWebsitesCount->get() . "/"
                . count($context->getAlgorithmState()->getWebsitesToArchive())
                . " done]");
        }
    }

    protected function makeCronArchiveContext()
    {
        $context = new CronArchive();
        $context->options = $this->cronArchiveOptions;
        return $context;
    }

    protected function handleError(CronArchive $context, $errorMessage)
    {
        $context->getAlgorithmStats()->errors[] = $errorMessage;
        $context->getAlgorithmLogger()->logError($errorMessage);
    }

    private function logArchivedWebsite(CronArchive $context, $idSite, $period, $date, $segment, $visitsInLastPeriods, $visitsToday)
    {
        if (substr($date, 0, 4) === 'last') {
            $visitsInLastPeriods = (int)$visitsInLastPeriods . " visits in last " . $date . " " . $period . "s, ";
            $thisPeriod = $period == "day" ? "today" : "this " . $period;
            $visitsInLastPeriod = (int)$visitsToday . " visits " . $thisPeriod . ", ";
        } else {
            $visitsInLastPeriods = (int)$visitsInLastPeriods . " visits in " . $period . "s included in: $date, ";
            $visitsInLastPeriod = '';
        }

        $context->getAlgorithmLogger()->log("Archived website id = $idSite, period = $period, "
            . $visitsInLastPeriods
            . $visitsInLastPeriod
            . " [segment = $segment]"
        ); // TODO: used to use $timer
    }

    private function getVisitsLastPeriodFromApiResponse($stats)
    {
        if (empty($stats)) {
            return 0;
        }

        $today = end($stats);

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

    private function checkResponse(CronArchive $context, $response, $url)
    {
        if (empty($response)
            || stripos($response, 'error')
        ) {
            return $context->getAlgorithmLogger()->logNetworkError($url, $response);
        }
        return true;
    }
}