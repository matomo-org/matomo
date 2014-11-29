<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive\Jobs;

use Piwik\CronArchive;
use Piwik\CronArchive\AlgorithmRules;
use Piwik\CronArchive\BaseJob;
use Piwik\Option;

/**
 * Job that handles archiving for a non-day period or segment. Will mark serialization for periods
 * for a site as done and execute appropriate CronArchive hooks.
 *
 * TODO: instead of passing options to distributed callbacks, we should depend on DI container
 */
class ArchiveVisitsForNonDayOrSegment extends BaseJob
{
    /**
     * Executes after the job finishes.
     */
    public function jobFinished($response)
    {
        parent::jobFinished($response);

        $context = $this->makeCronArchiveContext();

        list($idSite, $date, $period, $segment) = $this->parseJobUrl();
        list($visits, $visitsLast) = $this->parseVisitsApiResponse($context, $response, $idSite);

        if ($visits === null) {
            $this->handleError($context, "Error unserializing the following response from {$this->url}: " . $response);
            return;
        }

        $failedRequestsCount = $context->getAlgorithmRules()->getFailedRequestsSemaphore($idSite);
        if ($failedRequestsCount->get() === 0
            && $context->getAlgorithmRules()->getShouldProcessNonDayPeriods() // if any period is skipped, do not mark periods archiving as complete
        ) {
            Option::set(AlgorithmRules::lastRunKey($idSite, "periods"), time());
        }

        $this->archivingRequestFinished($context, $idSite, $visits, $visitsLast);
    }
}