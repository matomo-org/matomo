<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\BulkTracking\Tracker;

use Exception;
use Piwik\Common;
use Piwik\Tracker;

class Response extends Tracker\Response
{
    /**
     * @var int[]
     */
    private $invalidRequests = array();

    /**
     * @var bool
     */
    private $isAuthenticated = false;

    /**
     * Echos an error message & other information, then exits.
     *
     * @param Tracker $tracker
     * @param Exception $e
     * @param int  $statusCode eg 500
     */
    public function outputException(Tracker $tracker, Exception $e, $statusCode)
    {
        Common::sendResponseCode($statusCode);

        $this->logExceptionToErrorLog($e);

        $result = $this->formatException($tracker, $e);

        echo json_encode($result);
    }

    public function outputResponse(Tracker $tracker)
    {
        if ($this->hasAlreadyPrintedOutput()) {
            return;
        }

        $result = $this->formatResponse($tracker);

        echo json_encode($result);
    }

    public function getOutput()
    {
        Common::sendHeader('Content-Type: application/json');

        return parent::getOutput();
    }

    private function formatException(Tracker $tracker, Exception $e)
    {
        // when doing bulk tracking we return JSON so the caller will know how many succeeded
        $result = array(
            'status'  => 'error',
            'tracked' => $tracker->getCountOfLoggedRequests(),
            'invalid' => count($this->invalidRequests),
        );

        $this->addInvalidIndicesIfAuthenticated($result);

        // send error when in debug mode
        if ($tracker->isDebugModeEnabled()) {
            $result['message'] = $this->getMessageFromException($e);
        }

        return $result;
    }

    private function formatResponse(Tracker $tracker)
    {
        $result = array(
            'status' => 'success',
            'tracked' => $tracker->getCountOfLoggedRequests(),
            'invalid' => count($this->invalidRequests),
        );

        $this->addInvalidIndicesIfAuthenticated($result);

        return $result;
    }

    public function setInvalidRequests($invalidRequests)
    {
        $this->invalidRequests = $invalidRequests;
    }

    public function setIsAuthenticated($isAuthenticated)
    {
        $this->isAuthenticated = $isAuthenticated;
    }

    private function addInvalidIndicesIfAuthenticated(&$result)
    {
        if ($this->isAuthenticated) {
            $result['invalid_indices'] = $this->invalidRequests;
        }
    }
}
