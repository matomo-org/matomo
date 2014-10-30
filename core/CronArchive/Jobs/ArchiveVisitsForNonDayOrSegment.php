<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive\Jobs;

use Piwik\CronArchive;
use Piwik\CronArchive\AlgorithmOptions;
use Piwik\CronArchive\BaseJob;
use Piwik\Option;

/**
 * TODO
 */
class ArchiveVisitsForNonDayOrSegment extends BaseJob
{
    /**
     * TODO
     */
    public function __construct($idSite, $date, $period, $segment, $token_auth, AlgorithmOptions $options)
    {
        parent::__construct($idSite, $date, $period, $segment, $token_auth, $options);
    }

    /**
     * TODO
     */
    public function jobStarting()
    {
        // empty
    }

    /**
     * TODO
    // TODO: instead of passing options to distributed callbacks, we should depend on DI container
     */
    public function jobFinished($response)
    {
        $context = $this->makeCronArchiveContext();

        list($idSite, $date, $period, $segment) = $this->parseJobUrl();
        list($visits, $visitsLast) = $this->parseVisitsApiResponse($context, $response, $idSite);

        if ($visits === null) {
            $context->handleError("Error unserializing the following response from {$this->url}: " . $response);
            return;
        }

        $failedRequestsCount = $context->getAlgorithmState()->getFailedRequestsSemaphore($idSite);
        $failedRequestsCount->decrement();

        if ($failedRequestsCount->get() === 0
            && $context->getAlgorithmState()->getShouldProcessNonDayPeriods() // if any period is skipped, do not mark as complete
        ) {
            Option::set(CronArchive::lastRunKey($idSite, "periods"), time());

            // TODO: need to double check all metrics are counted correctly
            // for example, this incremented only when success or always?
            $context->getAlgorithmStats()->archivedPeriodsArchivesWebsite++;
        }

        $this->archivingRequestFinished($context, $idSite, $period, $date, $segment, $visits, $visitsLast);
    }
}