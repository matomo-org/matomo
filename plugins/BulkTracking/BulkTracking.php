<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\BulkTracking;
use Piwik\Plugins\BulkTracking\Tracker\Handler;
use Piwik\Plugins\BulkTracking\Tracker\Requests;
use Piwik\Tracker\RequestSet;

class BulkTracking extends \Piwik\Plugin
{
    /**
     * @var Requests
     */
    private $requests;

    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Tracker.newHandler' => 'setHandlerIfBulkRequest',
            'Tracker.initRequestSet' => 'initRequestSet',
        );
    }

    public function setRequests(Requests $requests)
    {
        $this->requests = $requests;
    }

    public function initRequestSet(RequestSet $requestSet)
    {
        if ($this->isUsingBulkRequest()) {

            $bulk = $this->buildBulkRequests();

            list($requests, $token) = $bulk->initRequestsAndTokenAuth($bulk->getRawBulkRequest());

            if ($bulk->requiresAuthentication()) {
                $bulk->authenticateRequests($requests);
            }

            if (!$requestSet->getTokenAuth()) {
                $requestSet->setTokenAuth($token);
            }

            $requestSet->setRequests($requests);
        }
    }

    public function setHandlerIfBulkRequest(&$handler)
    {
        if (!is_null($handler)) {
            return;
        }

        if ($this->isUsingBulkRequest()) {
            $handler = new Handler();
        }
    }

    private function isUsingBulkRequest()
    {
        $requests = $this->buildBulkRequests();
        $rawData  = $requests->getRawBulkRequest();

        return $requests->isUsingBulkRequest($rawData);
    }

    private function buildBulkRequests()
    {
        if (!is_null($this->requests)) {
            return $this->requests;
        }

        return new Requests();
    }
}
