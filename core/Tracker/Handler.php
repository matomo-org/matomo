<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Tracker;
use Exception;
use Piwik\Url;

class Handler
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var ScheduledTasksRunner
     */
    private $tasksRunner;

    public function __construct()
    {
        $this->setResponse(new Response());
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function init(Tracker $tracker, RequestSet $requestSet)
    {
        $this->response->init($tracker);
    }

    public function process(Tracker $tracker, RequestSet $requestSet)
    {
        foreach ($requestSet->getRequests() as $request) {
            $tracker->trackRequest($request);
        }
    }

    public function onStartTrackRequests(Tracker $tracker, RequestSet $requestSet)
    {
    }

    public function onAllRequestsTracked(Tracker $tracker, RequestSet $requestSet)
    {
        $tasks = $this->getScheduledTasksRunner();
        if ($tasks->shouldRun($tracker)) {
            $tasks->runScheduledTasks();
        }
    }

    private function getScheduledTasksRunner()
    {
        if (is_null($this->tasksRunner)) {
            $this->tasksRunner = new ScheduledTasksRunner();
        }

        return $this->tasksRunner;
    }

    /**
     * @internal
     */
    public function setScheduledTasksRunner(ScheduledTasksRunner $runner)
    {
        $this->tasksRunner = $runner;
    }

    public function onException(Tracker $tracker, RequestSet $requestSet, Exception $e)
    {
        Common::printDebug("Exception: " . $e->getMessage());

        $statusCode = 500;
        if ($e instanceof UnexpectedWebsiteFoundException) {
            $statusCode = 400;
        } elseif ($e instanceof InvalidRequestParameterException) {
            $statusCode = 400;
        }

        $this->response->outputException($tracker, $e, $statusCode);
        $this->redirectIfNeeded($requestSet);
    }

    public function finish(Tracker $tracker, RequestSet $requestSet)
    {
        $this->response->outputResponse($tracker);
        $this->redirectIfNeeded($requestSet);
        return $this->response->getOutput();
    }

    public function getResponse()
    {
        return $this->response;
    }

    protected function redirectIfNeeded(RequestSet $requestSet)
    {
        $redirectUrl = $requestSet->shouldPerformRedirectToUrl();

        if (!empty($redirectUrl)) {
            Url::redirectToUrl($redirectUrl);
        }
    }
}
