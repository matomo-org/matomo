<?php
/**
 * Piwik - Open source web analytics
 * 
 * Client to record visits, page views, Goals, in a Piwik server.
 * This is a PHP Version of the piwik.js standard Tracking API.
 * For more information, see http://piwik.org/docs/tracking-api/
 * 
 * This class requires: 
 *  - json extension (json_decode, json_encode) 
 *  - CURL or STREAM extensions (to issue the request to Piwik)
 *  
 * @license released under BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version $Id$
 * @link http://piwik.org/docs/tracking-api/
 *
 * @category Piwik
 * @package PiwikTracker
 */

/**
 * @package PiwikTracker
 */
class PiwikTracker
{
	/**
	 * Piwik base URL, for example http://example.org/piwik/
	 * Must be set before using the class by calling 
	 *  PiwikTracker::$URL = 'http://yourwebsite.org/piwik/';
	 * 
	 * @var string
	 */
	static public $URL = '';
	
	/**
	 * API Version
	 * 
	 * @ignore
	 * @var int
	 */
	const VERSION = 1;
	
	/**
	 * @ignore
	 */
	public $DEBUG_APPEND_URL = '';
	
	/**
	 * Visitor ID length
	 * 
	 * @ignore
	 */
	const LENGTH_VISITOR_ID = 16;
	
	/**
	 * Builds a PiwikTracker object, used to track visits, pages and Goal conversions 
	 * for a specific website, by using the Piwik Tracking API.
	 * 
	 * @param int $idSite Id site to be tracked
	 * @param string $apiUrl "http://example.org/piwik/" or "http://piwik.example.org/"
	 * 						 If set, will overwrite PiwikTracker::$URL
	 */
    function __construct( $idSite, $apiUrl = false )
    {
    	$this->cookieSupport = true;
    	
    	$this->userAgent = false;
    	$this->localHour = false;
    	$this->localMinute = false;
    	$this->localSecond = false;
    	$this->hasCookies = false;
    	$this->plugins = false;
    	$this->visitorCustomVar = false;
    	$this->customData = false;
    	$this->forcedDatetime = false;
    	$this->token_auth = false;
    	$this->attributionInfo = false;

    	$this->requestCookie = '';
    	$this->idSite = $idSite;
    	$this->urlReferrer = @$_SERVER['HTTP_REFERER'];
    	$this->pageUrl = self::getCurrentUrl();
    	$this->ip = @$_SERVER['REMOTE_ADDR'];
    	$this->acceptLanguage = @$_SERVER['HTTP_ACCEPT_LANGUAGE'];
    	$this->userAgent = @$_SERVER['HTTP_USER_AGENT'];
    	if(!empty($apiUrl)) {
    		self::$URL = $apiUrl;
    	}
    	$this->visitorId = substr(md5(uniqid(rand(), true)), 0, self::LENGTH_VISITOR_ID);
    }
    
    /**
     * Sets the current URL being tracked
     * 
     * @param string Raw URL (not URL encoded)
     */
	public function setUrl( $url )
    {
    	$this->pageUrl = $url;
    }

    /**
     * Sets the URL referrer used to track Referrers details for new visits.
     * 
     * @param string Raw URL (not URL encoded)
     */
    public function setUrlReferrer( $url )
    {
    	$this->urlReferrer = $url;
    }
    
    /**
     * @deprecated 
     * @ignore
     */
    public function setUrlReferer( $url )
    {
    	$this->setUrlReferrer($url);
    }
    
    /**
     * Sets the attribution information to the visit, so that subsequent Goal conversions are 
     * properly attributed to the right Referrer URL, timestamp, Campaign Name & Keyword.
     * 
     * This must be a JSON encoded string that would typically be fetched from the JS API: 
     * piwikTracker.getAttributionInfo() and that you have JSON encoded via JSON2.stringify() 
     * 
     * @param string $jsonEncoded JSON encoded array containing Attribution info
     * @see function getAttributionInfo() in http://dev.piwik.org/trac/browser/trunk/js/piwik.js 
     */
    public function setAttributionInfo( $jsonEncoded )
    {
    	$decoded = json_decode($jsonEncoded, $assoc = true);
    	if(!is_array($decoded)) 
    	{
    		throw new Exception("setAttributionInfo() is expecting a JSON encoded string, $jsonEncoded given");
    	}
    	$this->attributionInfo = $decoded;
    }

    /**
     * Sets Visit Custom Variable.
     * See http://piwik.org/docs/custom-variables/
     * 
     * @param int Custom variable slot ID from 1-5
     * @param string Custom variable name
     * @param string Custom variable value
     */
    public function setCustomVariable($id, $name, $value)
    {
        $this->visitorCustomVar[$id] = array($name, $value);
    }
    
    /**
     * Returns the currently assigned Custom Variable stored in a first party cookie.
     * 
     * This function will only work if the user is initiating the current request, and his cookies
     * can be read by PHP from the $_COOKIE array.
     * 
     * @return array An array with this format: array( 0 => CustomVariableName, 1 => CustomVariableValue )
     * @see Piwik.js getCustomVariable()
     */
    public function getCustomVariable($id)
    {
    	$customVariablesCookie = 'cvar.'.$this->idSite.'.';
    	$cookie = $this->getCookieMatchingName($customVariablesCookie);
    	if(!$cookie)
    	{
    		return false;
    	}
    	$id = (int)$id;
    	$cookieDecoded = json_decode($cookie, $assoc = true);
    	if(!is_array($cookieDecoded)
    		|| !isset($cookieDecoded[$id])
    		|| !is_array($cookieDecoded[$id])
    		|| count($cookieDecoded[$id]) != 2)
    	{
    		return false;
    	}
    	return $cookieDecoded[$id];
    }
    
    /**
     * Sets the Browser language. Used to guess visitor countries when GeoIP is not enabled
     * 
     * @param string For example "fr-fr"
     */
    public function setBrowserLanguage( $acceptLanguage )
    {
    	$this->acceptLanguage = $acceptLanguage;
    }

    /**
     * Sets the user agent, used to detect OS and browser.
     * If this function is not called, the User Agent will default to the current user agent.
     *  
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
    	$this->userAgent = $userAgent;
    }
    

    /**
     * Tracks a page view
     * 
     * @param string $documentTitle Page title as it will appear in the Actions > Page titles report
     * @return string Response
     */
    public function doTrackPageView( $documentTitle )
    {
    	$url = $this->getUrlTrackPageView($documentTitle);
    	return $this->sendRequest($url);
    } 
    
    /**
     * Records a Goal conversion
     * 
     * @param int $idGoal Id Goal to record a conversion
     * @param int $revenue Revenue for this conversion
     * @return string Response
     */
    public function doTrackGoal($idGoal, $revenue = false)
    {
    	$url = $this->getUrlTrackGoal($idGoal, $revenue);
    	return $this->sendRequest($url);
    }
    
    /**
     * Tracks a download or outlink
     * 
     * @param string $actionUrl URL of the download or outlink
     * @param string $actionType Type of the action: 'download' or 'link'
     * @return string Response
     */
    public function doTrackAction($actionUrl, $actionType)
    {
        // Referrer could be udpated to be the current URL temporarily (to mimic JS behavior)
    	$url = $this->getUrlTrackAction($actionUrl, $actionType);
    	return $this->sendRequest($url); 
    }
    
    /**
     * @see doTrackPageView()
     * @param string $documentTitle Page view name as it will appear in Piwik reports
     * @return string URL to piwik.php with all parameters set to track the pageview
     */
    public function getUrlTrackPageView( $documentTitle = false )
    {
    	$url = $this->getRequest( $this->idSite );
    	if(!empty($documentTitle)) {
    		$url .= '&action_name=' . urlencode($documentTitle);
    	}
    	return $url;
    }
    
    /**
     * @see doTrackGoal()
     * @param string $actionUrl URL of the download or outlink
     * @param string $actionType Type of the action: 'download' or 'link'
     * @return string URL to piwik.php with all parameters set to track the goal conversion
     */
    public function getUrlTrackGoal($idGoal, $revenue = false)
    {
    	$url = $this->getRequest( $this->idSite );
		$url .= '&idgoal=' . $idGoal;
    	if(!empty($revenue)) {
    		$url .= '&revenue=' . $revenue;
    	}
    	return $url;
    }
        
    /**
     * @see doTrackAction()
     * @param string $actionUrl URL of the download or outlink
     * @param string $actionType Type of the action: 'download' or 'link'
     * @return string URL to piwik.php with all parameters set to track an action
     */
    public function getUrlTrackAction($actionUrl, $actionType)
    {
    	$url = $this->getRequest( $this->idSite );
		$url .= '&'.$actionType.'=' . $actionUrl .
				'&redirect=0';
		
    	return $url;
    }

    /**
     * Overrides server date and time for the tracking requests. 
     * By default Piwik will track requests for the "current datetime" but this function allows you 
     * to track visits in the past. All times are in UTC.
     * 
     * Allowed only for Super User, must be used along with setTokenAuth()
     * @see setTokenAuth()
     * @param string Date with the format 'Y-m-d H:i:s', or a UNIX timestamp
     */
    public function setForceVisitDateTime($dateTime)
    {
    	$this->forcedDatetime = $dateTime;
    }
    
    /**
     * Overrides IP address
     * 
     * Allowed only for Super User, must be used along with setTokenAuth()
     * @see setTokenAuth()
     * @param string IP string, eg. 130.54.2.1
     */
    public function setIp($ip)
    {
    	$this->ip = $ip;
    }
    
    /**
     * Forces the requests to be recorded for the specified Visitor ID
     * rather than using the heuristics based on IP and other attributes.
     * 
     * This is typically used with the Javascript getVisitorId() function.
     * 
     * Allowed only for Super User, must be used along with setTokenAuth()
     * @see setTokenAuth()
     * @param string $visitorId 16 hexadecimal characters visitor ID, eg. "33c31e01394bdc63"
     */
    public function setVisitorId($visitorId)
    {
    	if(strlen($visitorId) != self::LENGTH_VISITOR_ID)
    	{
    		throw new Exception("setVisitorId() expects a ".self::LENGTH_VISITOR_ID." characters ID");
    	}
    	$this->forcedVisitorId = $visitorId;
    }
    
    /**
     * If the user initiating the request has the Piwik first party cookie, 
     * this function will try and return the ID parsed from this first party cookie (found in $_COOKIE).
     * 
     * If you call this function from a server, where the call is triggered by a cron or script
     * not initiated by the actual visitor being tracked, then it will return 
     * the random Visitor ID that was assigned to this visit object.
     * 
     * This can be used if you wish to record more visits, actions or goals for this visitor ID later on.
     * 
     * @return string 16 hex chars visitor ID string
     */
    public function getVisitorId()
    {
    	if(!empty($this->forcedVisitorId))
    	{
    		return $this->forcedVisitorId;
    	}
    	
    	$idCookieName = 'id.'.$this->idSite.'.';
    	$idCookie = $this->getCookieMatchingName($idCookieName);
    	if($idCookie !== false)
    	{
    		$visitorId = substr($idCookie, 0, strpos($idCookie, '.'));
    		if(strlen($visitorId) == self::LENGTH_VISITOR_ID)
    		{
    			return $visitorId;
    		}
    	}
    	return $this->visitorId;
    }

    /**
     * Returns the currently assigned Attribution Information stored in a first party cookie.
     * 
     * This function will only work if the user is initiating the current request, and his cookies
     * can be read by PHP from the $_COOKIE array.
     * 
     * @return string JSON Encoded string containing the Referer information for Goal conversion attribution.
     *                Will return false if the cookie could not be found
     * @see Piwik.js getAttributionInfo()
     */
    public function getAttributionInfo()
    {
    	$attributionCookieName = 'ref.'.$this->idSite.'.';
    	return $this->getCookieMatchingName($attributionCookieName);
    }
    
	/**
	 * Some Tracking API functionnality requires express authentication, using either the 
	 * Super User token_auth, or a user with 'admin' access to the website.
	 * 
	 * The following features require access:
	 * - force the visitor IP
	 * - force the date & time of the tracking requests rather than track for the current datetime
	 * - force Piwik to track the requests to a specific VisitorId rather than use the standard visitor matching heuristic
	 *
	 * @param string token_auth 32 chars token_auth string
	 */
	public function setTokenAuth($token_auth)
	{
		$this->token_auth = $token_auth;
	}

    /**
     * Sets local visitor time
     * 
     * @param string $time HH:MM:SS format
     */
    public function setLocalTime($time)
    {
    	list($hour, $minute, $second) = explode(':', $time);
    	$this->localHour = (int)$hour;
    	$this->localMinute = (int)$minute;
    	$this->localSecond = (int)$second;
    }
    
    /**
     * Sets user resolution width and height.
     *
     * @param int $width
     * @param int $height
     */
    public function setResolution($width, $height)
    {
    	$this->width = $width;
    	$this->height = $height;
    }
    
    /**
     * Sets if the browser supports cookies 
     * This is reported in "List of plugins" report in Piwik.
     *
     * @param bool $bool
     */
    public function setBrowserHasCookies( $bool )
    {
    	$this->hasCookies = $bool ;
    }
    
    /**
     * Sets visitor browser supported plugins 
     *
     * @param bool $flash
     * @param bool $java
     * @param bool $director
     * @param bool $quickTime
     * @param bool $realPlayer
     * @param bool $pdf
     * @param bool $windowsMedia
     * @param bool $gears
     * @param bool $silverlight
     */
    public function setPlugins($flash = false, $java = false, $director = false, $quickTime = false, $realPlayer = false, $pdf = false, $windowsMedia = false, $gears = false, $silverlight = false)
    {
    	$this->plugins = 
    		'&fla='.(int)$flash.
    		'&java='.(int)$java.
    		'&dir='.(int)$director.
    		'&qt='.(int)$quickTime.
    		'&realp='.(int)$realPlayer.
    		'&pdf='.(int)$pdf.
    		'&wma='.(int)$windowsMedia.
    		'&gears='.(int)$gears.
    		'&ag='.(int)$silverlight
    	;
    }
    
    /**
     * By default, PiwikTracker will read third party cookies 
     * from the response and sets them in the next request.
     * This can be disabled by calling this function.
     * 
     * @return void
     */
    public function disableCookieSupport()
    {
    	$this->cookieSupport = false;
    }
    
    /**
     * @ignore
     */
    protected function sendRequest($url)
    {
		$timeout = 600; // Allow debug while blocking the request
		$response = '';

		if(!$this->cookieSupport)
		{
			$this->requestCookie = '';
		}
		if(function_exists('curl_init'))
		{
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_USERAGENT => $this->userAgent,
				CURLOPT_HEADER => true,
				CURLOPT_TIMEOUT => $timeout,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => array(
					'Accept-Language: ' . $this->acceptLanguage,
					'Cookie: '. $this->requestCookie,
				),
			));
			ob_start();
			$response = @curl_exec($ch);
			ob_end_clean();
			$header = $content = '';
			if(!empty($response))
			{
				list($header,$content) = explode("\r\n\r\n", $response, $limitCount = 2);
			}
		}
		else if(function_exists('stream_context_create'))
		{
			$stream_options = array(
				'http' => array(
					'user_agent' => $this->userAgent,
					'header' => "Accept-Language: " . $this->acceptLanguage . "\r\n" .
					            "Cookie: ".$this->requestCookie. "\r\n" ,
					'timeout' => $timeout, // PHP 5.2.1
				)
			);
			$ctx = stream_context_create($stream_options);
			$response = file_get_contents($url, 0, $ctx);
			$header = implode("\r\n", $http_response_header); 
			$content = $response;
		}
		// The cookie in the response will be set in the next request
		preg_match_all('/^Set-Cookie: (.*?);/m', $header, $cookie);
		if(!empty($cookie[1]))
		{
			// in case several cookies returned, we keep only the latest one (ie. XDEBUG puts its cookie first in the list)
			if(is_array($cookie[1]))
			{
				$cookie = end($cookie[1]);
			}
			else
			{
				$cookie = $cookie[1];
			}
			if(strpos($cookie, 'XDEBUG') === false)
			{
				$this->requestCookie = $cookie;
			}
		}

		return $content;
    }
    
    /**
     * @ignore
     */
    protected function getRequest( $idSite )
    {
    	if(empty(self::$URL))
    	{
    		throw new Exception('You must first set the Piwik Tracker URL by calling PiwikTracker::$URL = \'http://your-website.org/piwik/\';');
    	}
    	if(strpos(self::$URL, '/piwik.php') === false
    		&& strpos(self::$URL, '/proxy-piwik.php') === false)
    	{
    		self::$URL .= '/piwik.php';
    	}
    	$url = self::$URL .
	 		'?idsite=' . $idSite .
			'&rec=1' .
			'&apiv=' . self::VERSION . 
	        '&rand=' . mt_rand() .
    	
    		// PHP DEBUGGING: Optional since debugger can be triggered remotely
    		(!empty($_GET['XDEBUG_SESSION_START']) ? '&XDEBUG_SESSION_START=' . @$_GET['XDEBUG_SESSION_START'] : '') . 
	        (!empty($_GET['KEY']) ? '&KEY=' . @$_GET['KEY'] : '') .
    	 
    		// Only allowed for Super User, token_auth required
			(!empty($this->ip) ? '&cip=' . $this->ip : '') .
    		(!empty($this->forcedVisitorId) ? '&cid=' . $this->forcedVisitorId : '&_id=' . $this->visitorId) . 
			(!empty($this->forcedDatetime) ? '&cdt=' . urlencode($this->forcedDatetime) : '') .
			(!empty($this->token_auth) ? '&token_auth=' . urlencode($this->token_auth) : '') .
	        
			// These parameters are set by the JS, but optional when using API
	        (!empty($this->plugins) ? $this->plugins : '') . 
			(($this->localHour !== false && $this->localMinute !== false && $this->localSecond !== false) ? '&h=' . $this->localHour . '&m=' . $this->localMinute  . '&s=' . $this->localSecond : '' ).
	        (!empty($this->width) && !empty($this->height) ? '&res=' . $this->width . 'x' . $this->height : '') .
	        (!empty($this->hasCookies) ? '&cookie=' . $this->hasCookies : '') .
	        (!empty($this->customData) ? '&data=' . $this->customData : '') . 
	        (!empty($this->visitorCustomVar) ? '&_cvar=' . urlencode(json_encode($this->visitorCustomVar)) : '') .
	        
	        // URL parameters
	        '&url=' . urlencode($this->pageUrl) .
			'&urlref=' . urlencode($this->urlReferrer) .
	        
	        // Attribution information, so that Goal conversions are attributed to the right referrer or campaign
	        // Campaign name
    		(!empty($this->attributionInfo[0]) ? '&_rcn=' . urlencode($this->attributionInfo[0]) : '') .
    		// Campaign keyword
    		(!empty($this->attributionInfo[1]) ? '&_rck=' . urlencode($this->attributionInfo[1]) : '') .
    		// Timestamp at which the referrer was set
    		(!empty($this->attributionInfo[2]) ? '&_refts=' . $this->attributionInfo[2] : '') .
    		// Referrer URL
    		(!empty($this->attributionInfo[3]) ? '&_ref=' . urlencode($this->attributionInfo[3]) : '') .

    		// DEBUG 
	        $this->DEBUG_APPEND_URL
        ;
    	return $url;
    }
    
    
    /**
     * Returns a first party cookie which name contains $name
     * 
     * @param string $name
     * @return string String value of cookie, or false if not found
     * @ignore
     */
    protected function getCookieMatchingName($name)
    {
    	// Piwik cookie names use dots separators in piwik.js, 
    	// but PHP Replaces . with _ http://www.php.net/manual/en/language.variables.predefined.php#72571
    	$name = str_replace('.', '_', $name);
    	foreach($_COOKIE as $cookieName => $cookieValue)
    	{
    		if(strpos($cookieName, $name) !== false)
    		{
    			return $cookieValue;
    		}
    	}
    	return false;
    }
    

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "/dir1/dir2/index.php"
	 *
	 * @return string
     * @ignore
	 */
	static protected function getCurrentScriptName()
	{
		$url = '';
		if( !empty($_SERVER['PATH_INFO']) ) { 
			$url = $_SERVER['PATH_INFO'];
		} 
		else if( !empty($_SERVER['REQUEST_URI']) ) 	{
			if( ($pos = strpos($_SERVER['REQUEST_URI'], '?')) !== false ) {
				$url = substr($_SERVER['REQUEST_URI'], 0, $pos);
			} else {
				$url = $_SERVER['REQUEST_URI'];
			}
		} 
		if(empty($url)) {
			$url = $_SERVER['SCRIPT_NAME'];
		}

		if($url[0] !== '/')	{
			$url = '/' . $url;
		}
		return $url;
	}


	/**
	 * If the current URL is 'http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return 'http'
	 *
	 * @return string 'https' or 'http'
     * @ignore
	 */
	static protected function getCurrentScheme()
	{
		if(isset($_SERVER['HTTPS'])
				&& ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true))
		{
			return 'https';
		}
		return 'http';
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "http://example.org"
	 *
	 * @return string
     * @ignore
	 */
	static protected function getCurrentHost()
	{
		if(isset($_SERVER['HTTP_HOST'])) {
			return $_SERVER['HTTP_HOST'];
		}
		return 'unknown';
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "?param1=value1&param2=value2"
	 *
	 * @return string
     * @ignore
	 */
	static protected function getCurrentQueryString()
	{
		$url = '';	
		if(isset($_SERVER['QUERY_STRING'])
			&& !empty($_SERVER['QUERY_STRING']))
		{
			$url .= '?'.$_SERVER['QUERY_STRING'];
		}
		return $url;
	}
	
	/**
	 * Returns the current full URL (scheme, host, path and query string.
	 *  
	 * @return string
     * @ignore
	 */
    static protected function getCurrentUrl()
    {
		return self::getCurrentScheme() . '://'
			. self::getCurrentHost()
			. self::getCurrentScriptName() 
			. self::getCurrentQueryString();
	}
}


function Piwik_getUrlTrackPageView( $idSite, $documentTitle = false )
{
	$tracker = new PiwikTracker($idSite);
	return $tracker->getUrlTrackPageView($documentTitle);
}
function Piwik_getUrlTrackGoal($idSite, $idGoal, $revenue = false)
{
	$tracker = new PiwikTracker($idSite);
	return $tracker->getUrlTrackGoal($idGoal, $revenue);
}

