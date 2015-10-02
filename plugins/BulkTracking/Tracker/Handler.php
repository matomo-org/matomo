<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\BulkTracking\Tracker;

use Piwik\Archiver\Request;
use Piwik\AuthResult;
use Piwik\Container\StaticContainer;
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Piwik;
use Piwik\Tracker;
use Piwik\Tracker\RequestSet;
use Piwik\Tracker\TrackerConfig;
use Exception;

class Handler extends Tracker\Handler
{
    private $transactionId = null;

    public function __construct()
    {
        $this->setResponse(new Response());
    }

    public function onStartTrackRequests(Tracker $tracker, RequestSet $requestSet)
    {
        if ($this->isTransactionSupported()) {
            $this->beginTransaction();
        }
    }

    public function onAllRequestsTracked(Tracker $tracker, RequestSet $requestSet)
    {
        $this->commitTransaction();

        // Do not run schedule task if we are importing logs or doing custom tracking (as it could slow down)
    }

    public function process(Tracker $tracker, RequestSet $requestSet)
    {
        $isAuthenticated = $this->isBulkTrackingRequestAuthenticated($requestSet);

        /** @var Response $response */
        $response = $this->getResponse();
        $response->setIsAuthenticated($isAuthenticated);

        $invalidRequests = array();
        foreach ($requestSet->getRequests() as $index => $request) {
            try {
                $tracker->trackRequest($request);
            } catch (UnexpectedWebsiteFoundException $ex) {
                $invalidRequests[] = $index;
            } catch (InvalidRequestParameterException $ex) {
                $invalidRequests[] = $index;
            }
        }

        $response->setInvalidRequests($invalidRequests);
    }

    public function onException(Tracker $tracker, RequestSet $requestSet, Exception $e)
    {
        $this->rollbackTransaction();
        parent::onException($tracker, $requestSet, $e);
    }

    private function beginTransaction()
    {
        if (empty($this->transactionId)) {
            $this->transactionId = $this->getDb()->beginTransaction();
        }
    }

    private function commitTransaction()
    {
        if (!empty($this->transactionId)) {
            $this->getDb()->commit($this->transactionId);
            $this->transactionId = null;
        }
    }

    private function rollbackTransaction()
    {
        if (!empty($this->transactionId)) {
            $this->getDb()->rollback($this->transactionId);
            $this->transactionId = null;
        }
    }

    private function getDb()
    {
        return Tracker::getDatabase();
    }

    /**
     * @return bool
     */
    private function isTransactionSupported()
    {
        return (bool) TrackerConfig::getConfigValue('bulk_requests_use_transaction');
    }

    private function isBulkTrackingRequestAuthenticated(RequestSet $requestSet)
    {
        $tokenAuth = $requestSet->getTokenAuth();
        if (empty($tokenAuth)) {
            return false;
        }

        Piwik::postEvent('Request.initAuthenticationObject');

        /** @var \Piwik\Auth $auth */
        $auth = StaticContainer::get('Piwik\Auth');
        $auth->setTokenAuth($tokenAuth);
        $auth->setLogin(null);
        $auth->setPassword(null);
        $auth->setPasswordHash(null);
        $access = $auth->authenticate();

        return $access->getCode() == AuthResult::SUCCESS_SUPERUSER_AUTH_CODE;
    }
}
