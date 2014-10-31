<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive;
use Piwik\Jobs\Job;

/**
 * TODO
 */
abstract class Hooks
{
    /**
     * TODO
     */
    public function onInit(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onInitTrackerTasks(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onStartProcessing(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onEndProcessing(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, AlgorithmStatistics $stats)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onStartRunScheduledTasks(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onEndRunScheduledTasks(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $tasksOutput)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onEnd(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, AlgorithmStatistics $stats)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onApiRequestError(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $url, $errorMessage)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onError(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $errorMessage)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onSkipWebsiteDayArchiving(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite, $reason)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onSkipWebsitePeriodArchiving(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite, $reason)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onArchiveRequestFinished(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $requestParams, $visits, $visitsLast)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onSiteArchivingFinished(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onQueuePeriodAndSegmentArchiving(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onQueueDayArchiving(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onEnqueueJob(AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, Job $job, $idSite)
    {
        // empty
    }
}