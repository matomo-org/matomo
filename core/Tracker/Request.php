<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Cookie;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Registry;
use Piwik\Tracker;

/**
 * The Request object holding the http parameters for this tracking request. Use getParam() to fetch a named parameter.
 *
 */
class Request
{
    /**
     * @var array
     */
    protected $params;

    protected $isAuthenticated = null;

    protected $tokenAuth;

    const UNKNOWN_RESOLUTION = 'unknown';

    const CUSTOM_TIMESTAMP_DOES_NOT_REQUIRE_TOKENAUTH_WHEN_NEWER_THAN = 14400; // 4 hours

    /**
     * @param $params
     * @param bool|string $tokenAuth
     */
    public function __construct($params, $tokenAuth = false)
    {
        if (!is_array($params)) {
            $params = array();
        }
        $this->params = $params;
        $this->tokenAuth = $tokenAuth;
        $this->timestamp = time();

        // When the 'url' and referrer url parameter are not given, we might be in the 'Simple Image Tracker' mode.
        // The URL can default to the Referrer, which will be in this case
        // the URL of the page containing the Simple Image beacon
        if (empty($this->params['urlref'])
            && empty($this->params['url'])
        ) {
            $url = @$_SERVER['HTTP_REFERER'];
            if (!empty($url)) {
                $this->params['url'] = $url;
            }
        }
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        if (is_null($this->isAuthenticated)) {
            $this->authenticateTrackingApi($this->tokenAuth);
        }

        return $this->isAuthenticated;
    }

    /**
     * This method allows to set custom IP + server time + visitor ID, when using Tracking API.
     * These two attributes can be only set by the Super User (passing token_auth).
     */
    protected function authenticateTrackingApi($tokenAuthFromBulkRequest)
    {
        $shouldAuthenticate = Config::getInstance()->Tracker['tracking_requests_require_authentication'];
        if ($shouldAuthenticate) {
            $tokenAuth = $tokenAuthFromBulkRequest ? $tokenAuthFromBulkRequest : Common::getRequestVar('token_auth', false, 'string', $this->params);
            try {
                $idSite = $this->getIdSite();
                $this->isAuthenticated = $this->authenticateSuperUserOrAdmin($tokenAuth, $idSite);
            } catch (Exception $e) {
                $this->isAuthenticated = false;
            }
            if (!$this->isAuthenticated) {
                return;
            }
            Common::printDebug("token_auth is authenticated!");
        } else {
            $this->isAuthenticated = true;
            Common::printDebug("token_auth authentication not required");
        }
    }

    public static function authenticateSuperUserOrAdmin($tokenAuth, $idSite)
    {
        if (empty($tokenAuth)) {
            return false;
        }

        Piwik::postEvent('Request.initAuthenticationObject');

        /** @var \Piwik\Auth $auth */
        $auth = Registry::get('auth');
        $auth->setTokenAuth($tokenAuth);
        $auth->setLogin(null);
        $access = $auth->authenticate();

        if (!empty($access) && $access->hasSuperUserAccess()) {
            return true;
        }

        // Now checking the list of admin token_auth cached in the Tracker config file
        if (!empty($idSite) && $idSite > 0) {
            $website = Cache::getCacheWebsiteAttributes($idSite);
            if (array_key_exists('admin_token_auth', $website) && in_array($tokenAuth, $website['admin_token_auth'])) {
                return true;
            }
        }
        Common::printDebug("WARNING! token_auth = $tokenAuth is not valid, Super User / Admin was NOT authenticated");

        return false;
    }

    /**
     * @return float|int
     */
    public function getDaysSinceFirstVisit()
    {
        $cookieFirstVisitTimestamp = $this->getParam('_idts');
        if (!$this->isTimestampValid($cookieFirstVisitTimestamp)) {
            $cookieFirstVisitTimestamp = $this->getCurrentTimestamp();
        }

        $daysSinceFirstVisit = round(($this->getCurrentTimestamp() - $cookieFirstVisitTimestamp) / 86400, $precision = 0);
        if ($daysSinceFirstVisit < 0) {
            $daysSinceFirstVisit = 0;
        }

        return $daysSinceFirstVisit;
    }

    /**
     * @return bool|float|int
     */
    public function getDaysSinceLastOrder()
    {
        $daysSinceLastOrder = false;
        $lastOrderTimestamp = $this->getParam('_ects');

        if ($this->isTimestampValid($lastOrderTimestamp)) {
            $daysSinceLastOrder = round(($this->getCurrentTimestamp() - $lastOrderTimestamp) / 86400, $precision = 0);
            if ($daysSinceLastOrder < 0) {
                $daysSinceLastOrder = 0;
            }
        }

        return $daysSinceLastOrder;
    }

    /**
     * @return float|int
     */
    public function getDaysSinceLastVisit()
    {
        $daysSinceLastVisit = 0;
        $lastVisitTimestamp = $this->getParam('_viewts');

        if ($this->isTimestampValid($lastVisitTimestamp)) {
            $daysSinceLastVisit = round(($this->getCurrentTimestamp() - $lastVisitTimestamp) / 86400, $precision = 0);
            if ($daysSinceLastVisit < 0) {
                $daysSinceLastVisit = 0;
            }
        }

        return $daysSinceLastVisit;
    }

    /**
     * @return int|mixed
     */
    public function getVisitCount()
    {
        $visitCount = $this->getParam('_idvc');
        if ($visitCount < 1) {
            $visitCount = 1;
        }
        return $visitCount;
    }

    /**
     * Returns the language the visitor is viewing.
     *
     * @return string browser language code, eg. "en-gb,en;q=0.5"
     */
    public function getBrowserLanguage()
    {
        return Common::getRequestVar('lang', Common::getBrowserLanguage(), 'string', $this->params);
    }

    /**
     * @return string
     */
    public function getLocalTime()
    {
        $localTimes = array(
            'h' => (string)Common::getRequestVar('h', $this->getCurrentDate("H"), 'int', $this->params),
            'i' => (string)Common::getRequestVar('m', $this->getCurrentDate("i"), 'int', $this->params),
            's' => (string)Common::getRequestVar('s', $this->getCurrentDate("s"), 'int', $this->params)
        );
        foreach ($localTimes as $k => $time) {
            if (strlen($time) == 1) {
                $localTimes[$k] = '0' . $time;
            }
        }
        $localTime = $localTimes['h'] . ':' . $localTimes['i'] . ':' . $localTimes['s'];
        return $localTime;
    }

    /**
     * Returns the current date in the "Y-m-d" PHP format
     *
     * @param string $format
     * @return string
     */
    protected function getCurrentDate($format = "Y-m-d")
    {
        return date($format, $this->getCurrentTimestamp());
    }

    public function getGoalRevenue($defaultGoalRevenue)
    {
        return Common::getRequestVar('revenue', $defaultGoalRevenue, 'float', $this->params);
    }

    public function getParam($name)
    {
        static $supportedParams = array(
            // Name => array( defaultValue, type )
            '_refts'       => array(0, 'int'),
            '_ref'         => array('', 'string'),
            '_rcn'         => array('', 'string'),
            '_rck'         => array('', 'string'),
            '_idts'        => array(0, 'int'),
            '_viewts'      => array(0, 'int'),
            '_ects'        => array(0, 'int'),
            '_idvc'        => array(1, 'int'),
            'url'          => array('', 'string'),
            'urlref'       => array('', 'string'),
            'res'          => array(self::UNKNOWN_RESOLUTION, 'string'),
            'idgoal'       => array(-1, 'int'),

            // other
            'bots'         => array(0, 'int'),
            'dp'           => array(0, 'int'),
            'rec'          => array(false, 'int'),
            'new_visit'    => array(0, 'int'),

            // Ecommerce
            'ec_id'        => array(false, 'string'),
            'ec_st'        => array(false, 'float'),
            'ec_tx'        => array(false, 'float'),
            'ec_sh'        => array(false, 'float'),
            'ec_dt'        => array(false, 'float'),
            'ec_items'     => array('', 'json'),

            // Events
            'e_c'          => array(false, 'string'),
            'e_a'          => array(false, 'string'),
            'e_n'          => array(false, 'string'),
            'e_v'          => array(false, 'float'),

            // some visitor attributes can be overwritten
            'cip'          => array(false, 'string'),
            'cdt'          => array(false, 'string'),
            'cid'          => array(false, 'string'),
            'uid'          => array(false, 'string'),

            // Actions / pages
            'cs'           => array(false, 'string'),
            'download'     => array('', 'string'),
            'link'         => array('', 'string'),
            'action_name'  => array('', 'string'),
            'search'       => array('', 'string'),
            'search_cat'   => array(false, 'string'),
            'search_count' => array(-1, 'int'),
            'gt_ms'        => array(-1, 'int'),

            // Content
            'c_p'          => array('', 'string'),
            'c_n'          => array('', 'string'),
            'c_t'          => array('', 'string'),
            'c_i'          => array('', 'string'),
        );

        if (!isset($supportedParams[$name])) {
            throw new Exception("Requested parameter $name is not a known Tracking API Parameter.");
        }

        $paramDefaultValue = $supportedParams[$name][0];
        $paramType = $supportedParams[$name][1];

        $value = Common::getRequestVar($name, $paramDefaultValue, $paramType, $this->params);

        return $value;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getCurrentTimestamp()
    {
        $cdt = $this->getCustomTimestamp();
        if(!empty($cdt)) {
            return $cdt;
        }
        return $this->timestamp;
    }

    protected function getCustomTimestamp()
    {
        $cdt = $this->getParam('cdt');
        if (empty($cdt)) {
            return false;
        }
        if (!is_numeric($cdt)) {
            $cdt = strtotime($cdt);
        }
        if (!$this->isTimestampValid($cdt, $this->timestamp)) {
            Common::printDebug(sprintf("Datetime %s is not valid", date("Y-m-d H:i:m", $cdt)));
            return false;
        }

        // If timestamp in the past, token_auth is required
        $timeFromNow = $this->timestamp - $cdt;
        $isTimestampRecent = $timeFromNow < self::CUSTOM_TIMESTAMP_DOES_NOT_REQUIRE_TOKENAUTH_WHEN_NEWER_THAN;
        if (!$isTimestampRecent) {
            if(!$this->isAuthenticated()) {
                Common::printDebug(sprintf("Custom timestamp is %s seconds old, requires &token_auth...", $timeFromNow));
                Common::printDebug("WARN: Tracker API 'cdt' was used with invalid token_auth");
                return false;
            }
        }
        return $cdt;
    }

    /**
     * Returns true if the timestamp is valid ie. timestamp is sometime in the last 10 years and is not in the future.
     *
     * @param $time int Timestamp to test
     * @param $now int Current timestamp
     * @return bool
     */
    protected function isTimestampValid($time, $now = null)
    {
        if(empty($now)) {
            $now = $this->getCurrentTimestamp();
        }
        return $time <= $now
            && $time > $now - 10 * 365 * 86400;
    }

    public function getIdSite()
    {
        $idSite = Common::getRequestVar('idsite', 0, 'int', $this->params);

        /**
         * Triggered when obtaining the ID of the site we are tracking a visit for.
         *
         * This event can be used to change the site ID so data is tracked for a different
         * website.
         *
         * @param int &$idSite Initialized to the value of the **idsite** query parameter. If a
         *                     subscriber sets this variable, the value it uses must be greater
         *                     than 0.
         * @param array $params The entire array of request parameters in the current tracking
         *                      request.
         */
        Piwik::postEvent('Tracker.Request.getIdSite', array(&$idSite, $this->params));

        if ($idSite <= 0) {
            throw new Exception('Invalid idSite: \'' . $idSite . '\'');
        }

        return $idSite;
    }

    public function getUserAgent()
    {
        $default = @$_SERVER['HTTP_USER_AGENT'];
        return Common::getRequestVar('ua', is_null($default) ? false : $default, 'string', $this->params);
    }

    public function getCustomVariables($scope)
    {
        if ($scope == 'visit') {
            $parameter = '_cvar';
        } else {
            $parameter = 'cvar';
        }

        $customVar = Common::unsanitizeInputValues(Common::getRequestVar($parameter, '', 'json', $this->params));

        if (!is_array($customVar)) {
            return array();
        }

        $customVariables = array();
        $maxCustomVars = CustomVariables::getMaxCustomVariables();
        foreach ($customVar as $id => $keyValue) {
            $id = (int)$id;
            if ($id < 1
                || $id > $maxCustomVars
                || count($keyValue) != 2
                || (!is_string($keyValue[0]) && !is_numeric($keyValue[0]))
            ) {
                Common::printDebug("Invalid custom variables detected (id=$id)");
                continue;
            }

            if (strlen($keyValue[1]) == 0) {
                $keyValue[1] = "";
            }
            // We keep in the URL when Custom Variable have empty names
            // and values, as it means they can be deleted server side

            $key   = self::truncateCustomVariable($keyValue[0]);
            $value = self::truncateCustomVariable($keyValue[1]);
            $customVariables['custom_var_k' . $id] = $key;
            $customVariables['custom_var_v' . $id] = $value;
        }

        return $customVariables;
    }

    public static function truncateCustomVariable($input)
    {
        return substr(trim($input), 0, CustomVariables::getMaxLengthCustomVariables());
    }

    protected function shouldUseThirdPartyCookie()
    {
        return (bool)Config::getInstance()->Tracker['use_third_party_id_cookie'];
    }

    /**
     * Update the cookie information.
     */
    public function setThirdPartyCookie($idVisitor)
    {
        if (!$this->shouldUseThirdPartyCookie()) {
            return;
        }

        Common::printDebug("We manage the cookie...");

        $cookie = $this->makeThirdPartyCookie();
        // idcookie has been generated in handleNewVisit or we simply propagate the old value
        $cookie->set(0, bin2hex($idVisitor));
        $cookie->save();
    }

    protected function makeThirdPartyCookie()
    {
        $cookie = new Cookie(
            $this->getCookieName(),
            $this->getCookieExpire(),
            $this->getCookiePath());
        Common::printDebug($cookie);
        return $cookie;
    }

    protected function getCookieName()
    {
        return Config::getInstance()->Tracker['cookie_name'];
    }

    protected function getCookieExpire()
    {
        return $this->getCurrentTimestamp() + Config::getInstance()->Tracker['cookie_expire'];
    }

    protected function getCookiePath()
    {
        return Config::getInstance()->Tracker['cookie_path'];
    }

    /**
     * Returns the ID from  the request in this order:
     * return from a given User ID,
     * or from a Tracking API forced Visitor ID,
     * or from a Visitor ID from 3rd party (optional) cookies,
     * or from a given Visitor Id from 1st party?
     *
     * @throws Exception
     */
    public function getVisitorId()
    {
        $found = false;

        // If User ID is set it takes precedence
        $userId = $this->getForcedUserId();
        if ($userId) {
            $userIdHashed = $this->getUserIdHashed($userId);
            $idVisitor = $this->truncateIdAsVisitorId($userIdHashed);
            Common::printDebug("Request will be recorded for this user_id = " . $userId . " (idvisitor = $idVisitor)");
            $found = true;
        }

        // Was a Visitor ID "forced" (@see Tracking API setVisitorId()) for this request?
        if (!$found) {
            $idVisitor = $this->getForcedVisitorId();
            if (!empty($idVisitor)) {
                if (strlen($idVisitor) != Tracker::LENGTH_HEX_ID_STRING) {
                    throw new Exception("Visitor ID (cid) $idVisitor must be " . Tracker::LENGTH_HEX_ID_STRING . " characters long");
                }
                Common::printDebug("Request will be recorded for this idvisitor = " . $idVisitor);
                $found = true;
            }
        }

        // - If set to use 3rd party cookies for Visit ID, read the cookie
        if (!$found) {
            // - By default, reads the first party cookie ID
            $useThirdPartyCookie = $this->shouldUseThirdPartyCookie();
            if ($useThirdPartyCookie) {
                $cookie = $this->makeThirdPartyCookie();
                $idVisitor = $cookie->get(0);
                if ($idVisitor !== false
                    && strlen($idVisitor) == Tracker::LENGTH_HEX_ID_STRING
                ) {
                    $found = true;
                }
            }
        }

        // If a third party cookie was not found, we default to the first party cookie
        if (!$found) {
            $idVisitor = Common::getRequestVar('_id', '', 'string', $this->params);
            $found = strlen($idVisitor) >= Tracker::LENGTH_HEX_ID_STRING;
        }

        if ($found) {
            $truncated = $this->truncateIdAsVisitorId($idVisitor);
            $binVisitorId = @Common::hex2bin($truncated);
            if (!empty($binVisitorId)) {
                return $binVisitorId;
            }
        }

        return false;
    }

    public function getIp()
    {
        $ipString = $this->getIpString();
        $ip = IP::P2N($ipString);
        return $ip;
    }

    public function getForcedUserId()
    {
        $userId = $this->getParam('uid');
        if (strlen($userId) > 0) {
            return $userId;
        }

        return false;
    }

    public function getForcedVisitorId()
    {
        return $this->getParam('cid');
    }

    public function getPlugins()
    {
        static $pluginsInOrder = array('fla', 'java', 'dir', 'qt', 'realp', 'pdf', 'wma', 'gears', 'ag', 'cookie');
        $plugins = array();
        foreach ($pluginsInOrder as $param) {
            $plugins[] = Common::getRequestVar($param, 0, 'int', $this->params);
        }
        return $plugins;
    }

    public function getParamsCount()
    {
        return count($this->params);
    }

    const GENERATION_TIME_MS_MAXIMUM = 3600000; // 1 hour

    public function getPageGenerationTime()
    {
        $generationTime = $this->getParam('gt_ms');
        if ($generationTime > 0
            && $generationTime < self::GENERATION_TIME_MS_MAXIMUM
        ) {
            return (int)$generationTime;
        }

        return false;
    }

    /**
     * @param $idVisitor
     * @return string
     */
    private function truncateIdAsVisitorId($idVisitor)
    {
        return substr($idVisitor, 0, Tracker::LENGTH_HEX_ID_STRING);
    }

    /**
     * Matches implementation of PiwikTracker::getUserIdHashed
     *
     * @param $userId
     * @return string
     */
    public function getUserIdHashed($userId)
    {
        return substr( sha1( $userId ), 0, 16);
    }

    /**
     * @return mixed|string
     * @throws Exception
     */
    private function getIpString()
    {
        $cip = $this->getParam('cip');

        if(empty($cip)) {
            return IP::getIpFromHeader();
        }

        if(!$this->isAuthenticated()) {
            Common::printDebug("WARN: Tracker API 'cip' was used with invalid token_auth");
            return IP::getIpFromHeader();
        }
        return $cip;
    }
}
