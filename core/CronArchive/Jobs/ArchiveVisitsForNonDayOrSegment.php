<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive\Jobs;

use Piwik\CronArchive;
use Piwik\CronArchive\BaseJob;
use Piwik\Option;

/**
 * TODO
 */
class ArchiveVisitsForNonDayOrSegment extends BaseJob
{
    /**
     * TODO
    // TODO: instead of passing options to distributed callbacks, we should depend on DI container
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

        $failedRequestsCount = $context->getAlgorithmState()->getFailedRequestsSemaphore($idSite);
        if ($failedRequestsCount->get() === 0
            && $context->getAlgorithmState()->getShouldProcessNonDayPeriods() // if any period is skipped, do not mark periods archiving as complete
        ) {
            Option::set(CronArchive::lastRunKey($idSite, "periods"), time());
        }

        $this->archivingRequestFinished($context, $idSite, $visits, $visitsLast);
    }
}