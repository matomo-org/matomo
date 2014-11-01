<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\CronArchive;

use Piwik\CronArchive;
use Piwik\Jobs\Job;

/**
 * TODO
 */
abstract class Hooks
{
    /**
     * TODO
     */
    public function onInit(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onInitTrackerTasks(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onStartProcessing(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onEndProcessing(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onStartRunScheduledTasks(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onEndRunScheduledTasks(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $tasksOutput)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onEnd(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onApiRequestError(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $url, $errorMessage)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onError(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $errorMessage)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onSkipWebsiteDayArchiving(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite, $reason)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onSkipWebsitePeriodArchiving(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite, $reason)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onArchiveRequestFinished(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger,
                                             $requestParams, $visits, $visitsLast, $elapsedTime)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onSiteArchivingFinished(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onQueuePeriodAndSegmentArchiving(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onQueueDayArchiving(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite)
    {
        // empty
    }

    /**
     * TODO
     */
    public function onEnqueueJob(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, Job $job, $idSite)
    {
        // empty
    }
}