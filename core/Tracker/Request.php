<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Exception;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Cookie;
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Http;
use Piwik\IP;
use Matomo\Network\IPUtils;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\ProxyHttp;
use Piwik\Segment\SegmentExpression;
use Piwik\Tracker;
use Piwik\Cache as PiwikCache;

/**
 * The Request object holding the http parameters for this tracking request. Use getParam() to fetch a named parameter.
 *
 */
class Request
{
    private $cdtCache;
    private $idSiteCache;
    private $paramsCache = array();

    /**
     * @var array
     */
    protected $params;
    protected $rawParams;

    protected $isAuthenticated = null;
    private $isEmptyRequest = false;

    protected $tokenAuth;

    protected $timestamp;

    /**
     * Stores plugin specific tracking request metadata. RequestProcessors can store
     * whatever they want in this array, and other RequestProcessors can modify these
     * values to change tracker behavior.
     *
     * @var string[][]
     */
    private $requestMetadata = array();

    const UNKNOWN_RESOLUTION = 'unknown';

    private $customTimestampDoesNotRequireTokenauthWhenNewerThan;

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
        $this->rawParams = $params;
        $this->tokenAuth = $tokenAuth;
        $this->timestamp = time();
        $this->isEmptyRequest = empty($params);


        // When the 'url' and referrer url parameter are not given, we might be in the 'Simple Image Tracker' mode.
        // The URL can default to the Referrer, which will be in this case
        // the URL of the page containing the Simple Image beacon
        if (empty($this->params['urlref'])
            && empty($this->params['url'])
            && array_key_exists('HTTP_REFERER', $_SERVER)
        ) {
            $url = $_SERVER['HTTP_REFERER'];
            if (!empty($url)) {
                $this->params['url'] = $url;
            }
        }

        // check for 4byte utf8 characters in all tracking params and replace them with � if not support by database
        $this->params = $this->replaceUnsupportedUtf8Chars($this->params);

        $this->customTimestampDoesNotRequireTokenauthWhenNewerThan = (int) TrackerConfig::getConfigValue('tracking_requests_require_authentication_when_custom_timestamp_newer_than',
            $this->getIdSiteIfExists());
    }

    protected function replaceUnsupportedUtf8Chars($value, $key=false)
    {
        $dbSettings   = new \Piwik\Db\Settings();
        $charset      = $dbSettings->getUsedCharset();

        if ('utf8mb4' === $charset) {
            return $value; // no need to replace anything if utf8mb4 is supported
        }

        if (is_string($value) && preg_match('/[\x{10000}-\x{10FFFF}]/u', $value)) {
            Common::printDebug("Unsupported character detected in $key. Replacing with \xEF\xBF\xBD");
            return preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $value);
        }

        if (is_array($value)) {
            array_walk_recursive ($value, function(&$value, $key){
                $value = $this->replaceUnsupportedUtf8Chars($value, $key);
            });
        }

        return $value;
    }

    /**
     * Get the params that were originally passed to the instance. These params do not contain any params that were added
     * within this object.
     * @return array
     */
    public function getRawParams()
    {
        return $this->rawParams;
    }

    public function getTokenAuth()
    {
        return $this->tokenAuth;
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
    protected function authenticateTrackingApi($tokenAuth)
    {
        $shouldAuthenticate = TrackerConfig::getConfigValue('tracking_requests_require_authentication', $this->getIdSiteIfExists());

        if ($shouldAuthenticate) {
            try {
                $idSite = $this->getIdSite();
            } catch (Exception $e) {
                Common::printDebug("failed to authenticate: invalid idSite");
                $this->isAuthenticated = false;
                return;
            }

            if (empty($tokenAuth)) {
                $tokenAuth = Common::getRequestVar('token_auth', false, 'string', $this->params);
            }

            $cache = PiwikCache::getTransientCache();
            $cacheKey = 'tracker_request_authentication_' . $idSite . '_' . $tokenAuth;

            if ($cache->contains($cacheKey)) {
                Common::printDebug("token_auth is authenticated in cache!");
                $this->isAuthenticated = $cache->fetch($cacheKey);
                return;
            }

            try {
                $this->isAuthenticated = self::authenticateSuperUserOrAdminOrWrite($tokenAuth, $idSite);
                $cache->save($cacheKey, $this->isAuthenticated);
            } catch (Exception $e) {
                Common::printDebug("could not authenticate, caught exception: " . $e->getMessage());

                $this->isAuthenticated = false;
            }

            if ($this->isAuthenticated) {
                Common::printDebug("token_auth is authenticated!");
            } else {
                StaticContainer::get('Piwik\Tracker\Failures')->logFailure(Failures::FAILURE_ID_NOT_AUTHENTICATED, $this);
            }
        } else {
            $this->isAuthenticated = true;
            Common::printDebug("token_auth authentication not required");
        }
    }

    public static function authenticateSuperUserOrAdminOrWrite($tokenAuth, $idSite)
    {
        if (empty($tokenAuth)) {
            return false;
        }

        // Now checking the list of admin token_auth cached in the Tracker config file
        if (!empty($idSite) && $idSite > 0) {
            $website = Cache::getCacheWebsiteAttributes($idSite);
            $userModel = new \Piwik\Plugins\UsersManager\Model();
            $tokenAuthHashed = $userModel->hashTokenAuth($tokenAuth);
            $hashedToken = UsersManager::hashTrackingToken((string) $tokenAuthHashed, $idSite);

            if (array_key_exists('tracking_token_auth', $website)
                && in_array($hashedToken, $website['tracking_token_auth'], true)) {
                return true;
            }
        }

        Piwik::postEvent('Request.initAuthenticationObject');

        /** @var \Piwik\Auth $auth */
        $auth = StaticContainer::get('Piwik\Auth');
        $auth->setTokenAuth($tokenAuth);
        $auth->setLogin(null);
        $auth->setPassword(null);
        $auth->setPasswordHash(null);
        $access = $auth->authenticate();

        if (!empty($access) && $access->hasSuperUserAccess()) {
            return true;
        }

        Common::printDebug("WARNING! token_auth = $tokenAuth is not valid, Super User / Admin / Write was NOT authenticated");

        /**
         * @ignore
         * @internal
         */
        Piwik::postEvent('Tracker.Request.authenticate.failed');

        return false;
    }

    public function isRequestExcluded()
    {
        $excludedRequests = TrackerConfig::getConfigValue('exclude_requests', $this->getIdSiteIfExists());

        if (!empty($excludedRequests)) {
            $excludedRequests = explode(',', $excludedRequests);
            $pattern = '/^(.+?)('.SegmentExpression::MATCH_EQUAL.'|'
                .SegmentExpression::MATCH_NOT_EQUAL.'|'
                .SegmentExpression::MATCH_CONTAINS.'|'
                .SegmentExpression::MATCH_DOES_NOT_CONTAIN.'|'
                .preg_quote(SegmentExpression::MATCH_STARTS_WITH).'|'
                .preg_quote(SegmentExpression::MATCH_ENDS_WITH)
                .'){1}(.*)/';
            foreach ($excludedRequests as $excludedRequest) {
                $match = preg_match($pattern, $excludedRequest, $matches);

                if (!empty($match)) {
                    $leftMember = $matches[1];
                    $operation = $matches[2];
                    if (!isset($matches[3])) {
                        $valueRightMember = '';
                    } else {
                        $valueRightMember = urldecode($matches[3]);
                    }
                    $actual = Common::getRequestVar($leftMember, '', 'string', $this->params);
                    $actual = mb_strtolower($actual);
                    $valueRightMember = mb_strtolower($valueRightMember);
                    switch ($operation) {
                        case SegmentExpression::MATCH_EQUAL:
                            if ($actual === $valueRightMember) {
                                return true;
                            }
                            break;
                        case SegmentExpression::MATCH_NOT_EQUAL:
                            if ($actual !== $valueRightMember) {
                                return true;
                            }
                            break;
                        case SegmentExpression::MATCH_CONTAINS:
                            if (stripos($actual, $valueRightMember) !== false) {
                                return true;
                            }
                            break;
                        case SegmentExpression::MATCH_DOES_NOT_CONTAIN:
                            if (stripos($actual, $valueRightMember) === false) {
                                return true;
                            }
                            break;
                        case SegmentExpression::MATCH_STARTS_WITH:
                            if (stripos($actual, $valueRightMember) === 0) {
                                return true;
                            }
                            break;
                        case SegmentExpression::MATCH_ENDS_WITH:
                            if (Common::stringEndsWith($actual, $valueRightMember)) {
                                return true;
                            }
                            break;
                    }
                }
            }
        }

        return false;
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
        if($localTimes['h'] < 0 || $localTimes['h'] > 23) {
            $localTimes['h'] = 0;
        }
        if($localTimes['i'] < 0 || $localTimes['i'] > 59) {
            $localTimes['i'] = 0;
        }
        if($localTimes['s'] < 0 || $localTimes['s'] > 59) {
            $localTimes['s'] = 0;
        }
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
            'url'          => array('', 'string'),
            'urlref'       => array('', 'string'),
            'res'          => array(self::UNKNOWN_RESOLUTION, 'string'),
            'idgoal'       => array(-1, 'int'),
            'ping'         => array(0, 'int'),

            // other
            'bots'         => array(0, 'int'),
            'dp'           => array(0, 'int'),
            'rec'          => array(0, 'int'),
            'new_visit'    => array(0, 'int'),

            // Ecommerce
            'ec_id'        => array('', 'string'),
            'ec_st'        => array(false, 'float'),
            'ec_tx'        => array(false, 'float'),
            'ec_sh'        => array(false, 'float'),
            'ec_dt'        => array(false, 'float'),
            'ec_items'     => array('', 'json'),

            // ecommerce product/category view
            '_pkc'          => array('', 'string'),
            '_pks'          => array('', 'string'),
            '_pkn'          => array('', 'string'),
            '_pkp'          => array(false, 'float'),

            // Events
            'e_c'          => array('', 'string'),
            'e_a'          => array('', 'string'),
            'e_n'          => array('', 'string'),
            'e_v'          => array(false, 'float'),

            // some visitor attributes can be overwritten
            'cip'          => array('', 'string'),
            'cdt'          => array('', 'string'),
            'cdo'          => array('', 'int'),
            'cid'          => array('', 'string'),
            'uid'          => array('', 'string'),

            // Actions / pages
            'cs'           => array('', 'string'),
            'download'     => array('', 'string'),
            'link'         => array('', 'string'),
            'action_name'  => array('', 'string'),
            'search'       => array('', 'string'),
            'search_cat'   => array('', 'string'),
            'pv_id'        => array('', 'string'),
            'search_count' => array(-1, 'int'),
            'pf_net'       => array(-1, 'int'),
            'pf_srv'       => array(-1, 'int'),
            'pf_tfr'       => array(-1, 'int'),
            'pf_dm1'       => array(-1, 'int'),
            'pf_dm2'       => array(-1, 'int'),
            'pf_onl'       => array(-1, 'int'),

            // Content
            'c_p'          => array('', 'string'),
            'c_n'          => array('', 'string'),
            'c_t'          => array('', 'string'),
            'c_i'          => array('', 'string'),

            // custom action request. Recommended when a plugin declares its own action handler/requestprocessor
            // refs https://github.com/matomo-org/matomo/issues/16569
            'ca'          => array(0, 'int'),
        );

        if (isset($this->paramsCache[$name])) {
            return $this->paramsCache[$name];
        }

        if (!isset($supportedParams[$name])) {
            throw new Exception("Requested parameter $name is not a known Tracking API Parameter.");
        }

        $paramDefaultValue = $supportedParams[$name][0];
        $paramType = $supportedParams[$name][1];

        if ($this->hasParam($name)) {
            $this->paramsCache[$name] = $this->replaceUnsupportedUtf8Chars(Common::getRequestVar($name, $paramDefaultValue, $paramType, $this->params), $name);
        } else {
            $this->paramsCache[$name] = $paramDefaultValue;
        }

        return $this->paramsCache[$name];
    }

    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        unset($this->paramsCache[$name]);

        if ($name === 'cdt') {
            $this->cdtCache = null;
        }
    }

    public function hasParam($name)
    {
        return isset($this->params[$name]);
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getCurrentTimestamp()
    {
        if (!isset($this->cdtCache)) {
            $this->cdtCache = $this->getCustomTimestamp();
        }

        if (!empty($this->cdtCache)) {
            return $this->cdtCache;
        }

        return $this->timestamp;
    }

    public function setCurrentTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    protected function getCustomTimestamp()
    {
        if (!$this->hasParam('cdt') && !$this->hasParam('cdo')) {
            return false;
        }

        $cdt = $this->getParam('cdt');
        $cdo = $this->getParam('cdo');

        if (empty($cdt) && $cdo) {
            $cdt = $this->timestamp;
        }

        if (empty($cdt)) {
            return false;
        }

        if (!is_numeric($cdt)) {
            $cdt = strtotime($cdt);
        }

        if (!empty($cdo)) {
            $cdt = $cdt - abs($cdo);
        }

        if (!$this->isTimestampValid($cdt, $this->timestamp)) {
            Common::printDebug(sprintf("Datetime %s is not valid", date("Y-m-d H:i:m", $cdt)));
            return false;
        }

        // If timestamp in the past, token_auth is required
        $timeFromNow = $this->timestamp - $cdt;
        $isTimestampRecent = $timeFromNow < $this->customTimestampDoesNotRequireTokenauthWhenNewerThan;

        if (!$isTimestampRecent) {
            if (!$this->isAuthenticated()) {
                $message = sprintf("Custom timestamp is %s seconds old, requires &token_auth...", $timeFromNow);
                Common::printDebug($message);
                Common::printDebug("WARN: Tracker API 'cdt' was used with invalid token_auth");
                throw new InvalidRequestParameterException($message);
            }
        }

        $cache = Tracker\Cache::getCacheGeneral();
        if (!empty($cache['delete_logs_enable']) && !empty($cache['delete_logs_older_than'])) {
            $scheduleInterval = $cache['delete_logs_schedule_lowest_interval'];
            $maxLogAge = $cache['delete_logs_older_than'];
            $logEntryCutoff = time() - (($maxLogAge + $scheduleInterval) * 60*60*24);
            if ($cdt < $logEntryCutoff) {
                $message = "Custom timestamp is older than the configured 'deleted old raw data' value of $maxLogAge days";
                Common::printDebug($message);
                throw new InvalidRequestParameterException($message);
            }
        }

        return (int) $cdt;
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
        if (empty($now)) {
            $now = $this->getCurrentTimestamp();
        }

        return $time <= $now
            && $time > $now - 20 * 365 * 86400;
    }

    /**
     * @internal
     * @ignore
     */
    public function getIdSiteUnverified()
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
        return $idSite;
    }

    public function getIdSiteIfExists()
    {
        try {
            return $this->getIdSite();
        } catch (UnexpectedWebsiteFoundException $ex) {
            return null;
        }
    }

    public function getIdSite()
    {
        if (isset($this->idSiteCache)) {
            return $this->idSiteCache;
        }

        $idSite = $this->getIdSiteUnverified();

        if ($idSite <= 0) {
            throw new UnexpectedWebsiteFoundException('Invalid idSite: \'' . $idSite . '\'');
        }

        // check site actually exists, should throw UnexpectedWebsiteFoundException directly
        $site = Cache::getCacheWebsiteAttributes($idSite);

        if (empty($site)) {
            // fallback just in case exception wasn't thrown...
            throw new UnexpectedWebsiteFoundException('Invalid idSite: \'' . $idSite . '\'');
        }

        $this->idSiteCache = $idSite;

        return $idSite;
    }

    public function getUserAgent()
    {
        $default = false;

        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $default = $_SERVER['HTTP_USER_AGENT'];
        }

        return Common::getRequestVar('ua', $default, 'string', $this->params);
    }

    public function getClientHints(): array
    {
        // use headers as default if no data was send with the tracking request
        $default = Http::getClientHintsFromServerVariables();

        $clientHints = Common::getRequestVar('uadata', $default, 'json', $this->params);

        return is_array($clientHints) ? $clientHints : [];
    }

    public function shouldUseThirdPartyCookie()
    {
        return TrackerConfig::getConfigValue('use_third_party_id_cookie', $this->getIdSiteIfExists());
    }

    public function getThirdPartyCookieVisitorId()
    {
        $cookie = $this->makeThirdPartyCookieUID();
        $idVisitor = $cookie->get(0);
        if ($idVisitor !== false
            && strlen($idVisitor) == Tracker::LENGTH_HEX_ID_STRING
        ) {
            return $idVisitor;
        }
        return null;
    }

    /**
     * Update the cookie information.
     */
    public function setThirdPartyCookie($idVisitor)
    {
        if (!$this->shouldUseThirdPartyCookie()) {
            return;
        }

        if (\Piwik\Tracker\IgnoreCookie::isIgnoreCookieFound()) {
            return;
        }

        $cookie = $this->makeThirdPartyCookieUID();
        $idVisitor = bin2hex($idVisitor);
        $cookie->set(0, $idVisitor);
        if (ProxyHttp::isHttps()) {
            $cookie->setSecure(true);
            $cookie->save('None');
        } else {
            $cookie->save('Lax');
        }

        Common::printDebug(sprintf("We set the visitor ID to %s in the 3rd party cookie...", $idVisitor));
    }

    protected function makeThirdPartyCookieUID()
    {
        $cookie = new Cookie(
            $this->getCookieName(),
            $this->getCookieExpire(),
            $this->getCookiePath());

        $domain = $this->getCookieDomain();
        if (!empty($domain)) {
            $cookie->setDomain($domain);
        }

        Common::printDebug($cookie);

        return $cookie;
    }

    protected function getCookieName()
    {
        return TrackerConfig::getConfigValue('cookie_name', $this->getIdSiteIfExists());
    }

    protected function getCookieExpire()
    {
        return $this->getCurrentTimestamp() + TrackerConfig::getConfigValue('cookie_expire', $this->getIdSiteIfExists());
    }

    protected function getCookiePath()
    {
        return TrackerConfig::getConfigValue('cookie_path', $this->getIdSiteIfExists());
    }

    protected function getCookieDomain()
    {
        return TrackerConfig::getConfigValue('cookie_domain', $this->getIdSiteIfExists());
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

        if (TrackerConfig::getConfigValue('enable_userid_overwrites_visitorid', $this->getIdSiteIfExists())) {
            // If User ID is set it takes precedence
            $userId = $this->getForcedUserId();
            if ($userId) {
                $userIdHashed = $this->getUserIdHashed($userId);
                $idVisitor = $this->truncateIdAsVisitorId($userIdHashed);
                Common::printDebug("Request will be recorded for this user_id = " . $userId . " (idvisitor = $idVisitor)");
                $found = true;
            }
        }

        // Was a Visitor ID "forced" (@see Tracking API setVisitorId()) for this request?
        if (!$found) {
            $idVisitor = $this->getForcedVisitorId();
            if (!empty($idVisitor)) {
                if (strlen($idVisitor) != Tracker::LENGTH_HEX_ID_STRING) {
                    throw new InvalidRequestParameterException("Visitor ID (cid) $idVisitor must be " . Tracker::LENGTH_HEX_ID_STRING . " characters long");
                }
                Common::printDebug("Request will be recorded for this idvisitor = " . $idVisitor);
                $found = true;
            }
        }

        $privacyConfig = new \Piwik\Plugins\PrivacyManager\Config();

        // Only check for cookie values if cookieless tracking is NOT forced
        if (!$privacyConfig->forceCookielessTracking) {
            // - If set to use 3rd party cookies for Visit ID, read the cookie
            if (!$found) {
                $useThirdPartyCookie = $this->shouldUseThirdPartyCookie();
                if ($useThirdPartyCookie) {
                    $idVisitor = $this->getThirdPartyCookieVisitorId();
                    if (!empty($idVisitor)) {
                        $found = true;
                    }
                }
            }

            // If a third party cookie was not found, we default to the first party cookie
            if (!$found) {
                $idVisitor = Common::getRequestVar('_id', '', 'string', $this->params);
                $found     = strlen($idVisitor) >= Tracker::LENGTH_HEX_ID_STRING;
            }
        }

        if ($found) {
            return $this->getVisitorIdAsBinary($idVisitor);
        }

        return false;
    }

    /**
     * When creating a third party cookie, we want to ensure that the original value set in this 3rd party cookie
     * sticks and is not overwritten later.
     */
    public function getVisitorIdForThirdPartyCookie()
    {
        $found = false;

        // For 3rd party cookies, priority is on re-using the existing 3rd party cookie value
        if (!$found) {
            $useThirdPartyCookie = $this->shouldUseThirdPartyCookie();
            if ($useThirdPartyCookie) {
                $idVisitor = $this->getThirdPartyCookieVisitorId();
                if(!empty($idVisitor)) {
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
            return $this->getVisitorIdAsBinary($idVisitor);
        }

        return false;
    }


    public function getIp()
    {
        return IPUtils::stringToBinaryIP($this->getIpString());
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
        static $pluginsInOrder = array('fla', 'java', 'qt', 'realp', 'pdf', 'wma', 'ag', 'cookie');
        $plugins = array();
        foreach ($pluginsInOrder as $param) {
            $plugins[] = Common::getRequestVar($param, 0, 'int', $this->params);
        }
        return $plugins;
    }

    public function isEmptyRequest()
    {
        return $this->isEmptyRequest;
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
     * Matches implementation of MatomoTracker::getUserIdHashed
     *
     * @param $userId
     * @return string
     */
    public function getUserIdHashed($userId)
    {
        return substr(sha1($userId), 0, 16);
    }

    /**
     * @return mixed|string
     * @throws Exception
     */
    public function getIpString()
    {
        $cip = $this->getParam('cip');

        if (empty($cip)) {
            return IP::getIpFromHeader();
        }

        if (!$this->isAuthenticated()) {
            Common::printDebug("WARN: Tracker API 'cip' was used with invalid token_auth");
            throw new InvalidRequestParameterException("Tracker API 'cip' was used, requires valid token_auth");
        }

        return $cip;
    }

    /**
     * Set a request metadata value.
     *
     * @param string $pluginName eg, `'Actions'`, `'Goals'`, `'YourPlugin'`
     * @param string $key
     * @param mixed $value
     */
    public function setMetadata($pluginName, $key, $value)
    {
        $this->requestMetadata[$pluginName][$key] = $value;
    }

    /**
     * Get a request metadata value. Returns `null` if none exists.
     *
     * @param string $pluginName eg, `'Actions'`, `'Goals'`, `'YourPlugin'`
     * @param string $key
     * @return mixed
     */
    public function getMetadata($pluginName, $key)
    {
        return isset($this->requestMetadata[$pluginName][$key]) ? $this->requestMetadata[$pluginName][$key] : null;
    }

    /**
     * @param $idVisitor
     * @return bool|string
     */
    private function getVisitorIdAsBinary($idVisitor)
    {
        $truncated = $this->truncateIdAsVisitorId($idVisitor);
        $binVisitorId = @Common::hex2bin($truncated);
        if (!empty($binVisitorId)) {
            return $binVisitorId;
        }
        return false;
    }

}
