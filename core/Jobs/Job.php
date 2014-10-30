<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Jobs;
use Piwik\Url;

/**
 * Description of a job that should be processed by a job processor.
 *
 * Job instances store a URL that points to the logic to execute when processed by a job
 * processor. Additionally, they can contain logic to execute before and after the job
 * is processed.
 *
 * If you want to queue a job and don't care about executing logic before or after the job,
 * you can simply create a new instance of Job w/ the URL to execute, eg:
 *
 *     $job = new Job("?module=API&method=MyPlugin.myApiMethod&myParam=1");
 *     $queue->enqueue($job);
 *
 * If you want to execute code before or after a job, you must create a new class that
 * derives from Job and implement the jobStarting & jobFinished methods:
 *
 *     class MyJob extends Job
 *     {
 *         public function __construct()
 *         {
 *             parent::__construct("?module=API&method=MyPlugin.myApiMethod&myParam=1");
 *         }
 *
 *         public function jobStarting()
 *         {
 *             // ...
 *         }
 *
 *         public function jobFinished($response)
 *         {
 *             // ...
 *         }
 *    }
 *
 * Jobs are serialized completely when added to a Job queue. This means you should not
 * store large objects w/ lots of dependencies in a Job instance. This may change in the
 * future when Dependency Injection is added.
 */
class Job
{
    /**
     * The URL that the Job processor should execute.
     *
     * @var string[]
     */
    public $url;

    /**
     * Constructor.
     *
     * @param string[] $url An array of query parameters.
     */
    public function __construct($url = null)
    {
        $this->url = $url;
    }

    /**
     * The method that is executed before a job starts.
     */
    public function jobStarting()
    {
        // empty
    }

    /**
     * The method that is executed after a job finishes.
     *
     * @param string $response The string response that the job's URL returned.
     */
    public function jobFinished($response)
    {
        // empty
    }

    /**
     * Returns the URL as a string instead of an array of query parameter values.
     *
     * @return string
     */
    public function getUrlString()
    {
        return '?' . Url::getQueryStringFromParameters($this->url); // TODO: getQueryStringFromParameters doesn't urlencode. maybe a problem.
    }
}