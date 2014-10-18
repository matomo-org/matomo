<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Jobs;

/**
 * Interface for a job processor.
 *
 * Job processors pull URLs from some location and execute them, either sequentially
 * or with some level of concurrency.
 *
 * All job processors should try to support the same processor being used to process
 * the same list of jobs on multiple machines.
 *
 * Job processors allow callbacks to be executed before one or more jobs is started,
 * and after one or more jobs finishes.
 *
 * Should normally be used with a class implementing {@link \Piwik\Jobs|Queue}.
 *
 * NOTE: This API is not stable. It will be considered stable after Dependency Injection is
 *       implemented in core.
 */
interface Processor
{
    /**
     * Sets the callback to execute before one or more jobs is about to be processed.
     *
     * @param callback $onJobsStartedCallback This callback should have the following signature:
     *
     *                                            function (string[] $urls)
     *
     *                                        Where each URL references an API method that should
     *                                        be executed as a job.
     */
    public function setOnJobsStartingCallback($onJobsStartedCallback);

    /**
     * Sets the callback to execute after one or more jobs finishes.
     *
     * @param callback $onJobsFinishedCallback This callback should have the following signature:
     *
     *                                             function (array[] $responses)
     *
     *                                         Where each element of `$responses` is a pair containing
     *                                         two strings. The first is the job's URL, and the second
     *                                         is the string output.
     */
    public function setOnJobsFinishedCallback($onJobsFinishedCallback);

    /**
     * Start processing jobs.
     *
     * @param bool $finishWhenNoJobs Whether to return from this method when there are nojobs left or
     *                               to continue to keep checking for jobs.
     */
    public function startProcessing($finishWhenNoJobs);

    /**
     * Stop currently processing jobs.
     *
     * TODO: See TODO in CliProcessor, is there an environment where this can actually be used?
     */
    public function stopProcessing();
}