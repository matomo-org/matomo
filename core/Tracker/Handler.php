<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker;

use Piwik\Container\StaticContainer;
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Exception\PluginDeactivatedException;
use Piwik\Exception\PluginNotFoundException;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Http\HttpCodeException;
use Piwik\Tracker;
use Exception;
use Piwik\Log\LoggerInterface;
use Throwable;

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
        $statusCode = self::getStatusCodeForException($e);

        // if an internal server error, log as a real error, otherwise it's just malformed input
        try {
            if ($statusCode >= 500) {
                $this->logger->error('Exception: {exception}', [
                    'exception' => $e,
                ]);
            } else {
                $this->logger->debug('Exception: {exception}', [
                    'exception' => $e,
                ]);
            }
        } catch (Throwable) {
            // a failing log writer must not replace the response
        }

        $this->response->outputException($tracker, $e, $statusCode);
    }

    /**
     * @internal
     */
    public static function getStatusCodeForException(Throwable $e): int
    {
        // checked first, as eg NoAccessException is also an InvalidRequestParameterException
        if (
            $e instanceof HttpCodeException
            // these describe the server's plugin setup, eg when tracking code calls an API internally
            && !($e instanceof PluginDeactivatedException)
            && !($e instanceof PluginNotFoundException)
        ) {
            $code = (int) $e->getCode();
            if (in_array($code, [400, 401, 403, 404, 429, 503], true)) {
                return $code;
            }
            if ($code >= 400 && $code < 500) {
                return 400;
            }
        }

        if ($e instanceof UnexpectedWebsiteFoundException || $e instanceof InvalidRequestParameterException) {
            return 400;
        }

        return 500;
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
