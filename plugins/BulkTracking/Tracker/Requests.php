<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\BulkTracking\Tracker;

use Exception;
use Piwik\Common;
use Piwik\Tracker\Request;
use Piwik\Tracker\TrackerConfig;

class Requests
{

    public function requiresAuthentication()
    {
        $requiresAuth = TrackerConfig::getConfigValue('bulk_requests_require_authentication');

        return !empty($requiresAuth);
    }

    /**
     * @param Request[] $requests
     * @throws Exception
     */
    public function authenticateRequests($requests)
    {
        foreach ($requests as $request) {
            $this->checkTokenAuthNotEmpty($request->getTokenAuth());

            if (!$request->isAuthenticated()) {
                $msg = sprintf("token_auth specified does not have Admin permission for idsite=%s", $request->getIdSite());
                throw new Exception($msg);
            }
        }
    }

    private function checkTokenAuthNotEmpty($token)
    {
        if (empty($token)) {
            throw new Exception("token_auth must be specified when using Bulk Tracking Import. "
                . " See <a href='https://developer.piwik.org/api-reference/tracking-api'>Tracking Doc</a>");
        }
    }

    /**
     * @return string
     */
    public function getRawBulkRequest()
    {
        return file_get_contents("php://input");
    }

    public function isUsingBulkRequest($rawData)
    {
        if (!empty($rawData)) {
            return strpos($rawData, '"requests"') || strpos($rawData, "'requests'");
        }

        return false;
    }

    public function getRequestsArrayFromBulkRequest($rawData)
    {
        $rawData = trim($rawData);
        $rawData = Common::sanitizeLineBreaks($rawData);

        // POST data can be array of string URLs or array of arrays w/ visit info
        $jsonData = json_decode($rawData, $assoc = true);

        $tokenAuth = Common::getRequestVar('token_auth', false, 'string', $jsonData);

        $requests = array();
        if (isset($jsonData['requests'])) {
            $requests = $jsonData['requests'];
        }

        return array($requests, $tokenAuth);
    }

    public function shouldSendResponse($rawData): bool
    {
        $rawData = trim($rawData);
        $rawData = Common::sanitizeLineBreaks($rawData);

        // POST data can be array of string URLs or array of arrays w/ visit info
        $jsonData = json_decode($rawData, $assoc = true);

        return !!Common::getRequestVar('send_image', true, 'string', $jsonData);
    }

    public function initRequestsAndTokenAuth($rawData)
    {
        list($requests, $tokenAuth) = $this->getRequestsArrayFromBulkRequest($rawData);

        $validRequests = array();

        if (!empty($requests)) {

            foreach ($requests as $index => $request) {
                // if a string is sent, we assume its a URL and try to parse it
                if (is_string($request)) {
                    $params = array();

                    $url = @parse_url($request);
                    if (!empty($url['query'])) {
                        @parse_str($url['query'], $params);
                        $validRequests[] = new Request($params, $tokenAuth);
                    }
                } else {
                    $validRequests[] = new Request($request, $tokenAuth);
                }
            }
        }

        return array($validRequests, $tokenAuth);
    }
}
