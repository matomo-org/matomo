<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Container\StaticContainer;
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Tracker;
use Exception;
use Psr\Log\LoggerInterface;

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->setResponse(new Response());
        $this->logger = StaticContainer::get(LoggerInterface::class);
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
        $statusCode = 500;
        if ($e instanceof UnexpectedWebsiteFoundException) {
            $statusCode = 400;
        } elseif ($e instanceof InvalidRequestParameterException) {
            $statusCode = 400;
        }

        // if an internal server error, log as a real error, otherwise it's just malformed input
        if ($statusCode == 500) {
            $this->logger->error('Exception: {exception}', [
                'exception' => $e,
            ]);
        } else {
            $this->logger->debug('Exception: {exception}', [
                'exception' => $e,
            ]);
        }

        $this->response->outputException($tracker, $e, $statusCode);
    }

    public function finish(Tracker $tracker, RequestSet $requestSet)
    {
        $this->response->outputResponse($tracker);
        return $this->response->getOutput();
    }

    public function getResponse()
    {
        return $this->response;
    }

}
