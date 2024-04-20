<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock\Tracker;

use Exception;
use Piwik\Tracker;
use Piwik\Tracker\RequestSet as TrackerRequestSet;

class Handler extends \Piwik\Tracker\Handler
{
    public $isInit = false;
    public $isInitTrackingRequests = false;
    public $isOnStartTrackRequests = false;
    public $isProcessed = false;
    public $isOnAllRequestsTracked = false;
    public $isOnException = false;
    public $isFinished = false;
    public $output = 'My Rendered Content';

    private $doTriggerExceptionInProcess = false;

    public function init(Tracker $tracker, TrackerRequestSet $TrackerRequestSet)
    {
        $this->isInit = true;
    }

    public function enableTriggerExceptionInProcess()
    {
        $this->doTriggerExceptionInProcess = true;
    }

    public function onStartTrackRequests(Tracker $tracker, TrackerRequestSet $TrackerRequestSet)
    {
        $this->isOnStartTrackRequests = true;
    }

    public function process(Tracker $tracker, TrackerRequestSet $TrackerRequestSet)
    {
        if ($this->doTriggerExceptionInProcess) {
            throw new Exception('My Exception During Process');
        }

        $this->isProcessed = true;
    }

    public function onAllRequestsTracked(Tracker $tracker, TrackerRequestSet $TrackerRequestSet)
    {
        $this->isOnAllRequestsTracked = true;
    }

    public function onException(Tracker $tracker, TrackerRequestSet $TrackerRequestSet, Exception $e)
    {
        $this->isOnException = true;
        $this->output = $e->getMessage();
    }

    public function finish(Tracker $tracker, TrackerRequestSet $TrackerRequestSet)
    {
        $this->isFinished = true;

        return $this->output;
    }
}
