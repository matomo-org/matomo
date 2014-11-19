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
 * Base class for classes that aim to insert logic into the CronArchive algorithm. The methods
 * in these classes are executed at certain parts of the CronArchive algorithm. Hooks are used
 * to provide logging & statistics counting without cluttering the core algorithm code.
 */
abstract class Hooks
{
    /**
     * Executed when initialization is finished.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     */
    public function onInit(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * Executed before scheduled tasks are run during the tracker.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     */
    public function onInitTrackerTasks(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * Executed before job processing starts.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     */
    public function onStartProcessing(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * Executed after all jobs are processed.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     */
    public function onEndProcessing(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * Executed before scheduled tasks are run.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     */
    public function onStartRunScheduledTasks(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * Executed after scheduled tasks are run.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     * @param $tasksOutput
     */
    public function onEndRunScheduledTasks(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $tasksOutput)
    {
        // empty
    }

    /**
     * Executed after the CronArchive algorithm finishes.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     */
    public function onEnd(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger)
    {
        // empty
    }

    /**
     * Executed when an API request results in an error.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     * @param string $url The API request URL.
     * @param string $errorMessage The error message returned from the API.
     */
    public function onApiRequestError(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $url, $errorMessage)
    {
        // empty
    }

    /**
     * Executed after on a non-API related error.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     * @param string $errorMessage The error message.
     */
    public function onError(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $errorMessage)
    {
        // empty
    }

    /**
     * Executed when day archiving for a website is skipped.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     * @param int $idSite The ID of the site archiving was skipped for.
     * @param string $reason The reason for skipping.
     */
    public function onSkipWebsiteDayArchiving(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite, $reason)
    {
        // empty
    }

    /**
     * Executed when period archiving for a website is skipped.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     * @param int $idSite The ID of the site archiving was skipped for.
     * @param string $reason The reason for skipping.
     */
    public function onSkipWebsitePeriodArchiving(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite, $reason)
    {
        // empty
    }

    /**
     * Executed after an archiving API request finishes successfully.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     * @param string[] $requestParams The query parameters in the API request that was made.
     * @param int $visits The number of visits computed by the request.
     * @param int $visitsLast The number of visits in the last N periods (where N is determined by CronArchive logic).
     * @param float $elapsedTime The elapsed time in seconds it took for the archiving request to complete.
     */
    public function onArchiveRequestFinished(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger,
                                             $requestParams, $visits, $visitsLast, $elapsedTime)
    {
        // empty
    }

    /**
     * Executed after all archiving for a site is completed.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     * @param int $idSite The ID of the site whose archiving is finished.
     */
    public function onSiteArchivingFinished(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite)
    {
        // empty
    }

    /**
     * Executed before period & segment archiving requests are queued as jobs to process.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     * @param int $idSite The ID of the site for whom archiving requests were queued..
     */
    public function onQueuePeriodAndSegmentArchiving(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite)
    {
        // empty
    }

    /**
     * Executed before period & segment archiving requests are queued as jobs to process.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     * @param int $idSite The ID of the site for whom archiving requests were queued..
     */
    public function onQueueDayArchiving(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, $idSite)
    {
        // empty
    }

    /**
     * Executed before a job is queued for processing.
     *
     * @param CronArchive $context
     * @param AlgorithmOptions $options
     * @param AlgorithmState $state
     * @param AlgorithmLogger $logger
     * @param Job $job
     * @param int $idSite The ID of the site the Job is for.
     */
    public function onEnqueueJob(CronArchive $context, AlgorithmOptions $options, AlgorithmState $state, AlgorithmLogger $logger, Job $job, $idSite)
    {
        // empty
    }
}