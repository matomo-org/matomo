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
 * Job classes define a static **execute** method that does something unspecified. Job
 * processors will execute this method when processing jobs. Additionally, they can contain
 * logic to execute before and after the job is processed.
 *
 * If you want to queue a job and don't care about executing logic before or after the job,
 * you can simply create a new instance of {@link Piwik\Jobs\UrlJob} w/ the URL to execute, eg:
 *
 *     $job = new UrlJob("?module=API&method=MyPlugin.myApiMethod&myParam=1");
 *     $queue->enqueue($job);
 *
 * If you want to execute code before or after a job, or execute code that is not accessible via
 * a Piwik URL, you must create a new class that derives from Job and implement the required
 * methods:
 *
 *     class MyJob extends Job
 *     {
 *         private $myData;
 *
 *         public function __construct($myData)
 *         {
 *             $this->myData = $myData;
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
 *
 *         public function getJobData()
 *         {
 *             return array($this->myData);
 *         }
 *
 *         public static function execute($myData)
 *         {
 *             // ...
 *         }
 *    }
 *
 * **NOTE: This api is not stable.**
 *
 * TODO: do not use serialize/unserialize.
 */
class Job
{
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
     * Returns data that should be passed to the derived class' static 'execute' method.
     *
     * @return array
     */
    public function getJobData()
    {
        return array();
    }

    /**
     * Returns the URL as a string instead of an array of query parameter values.
     *
     * @return string
     */
    public function getUrlString()
    {
        $jobClass = get_class($this);
        $jobData = json_encode($this->getJobData());

        $params = array(
            'module' => 'CoreAdminHome',
            'method' => 'executeJob',
            'jobClassName' => urlencode($jobClass),
            'jobData' => urlencode($jobData),
            'format' => 'json'
        );

        return '?' . Url::getQueryStringFromParameters($params);
    }
}