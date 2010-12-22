<?php
/**
 * Piwik - Open source web analytics
 * 
 * Client to record visits, page views, Goals, in a Piwik server.
 * For more information, see http://piwik.org/docs/tracking-api/
 * 
 * @license released under BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version $Id$
 * @link http://piwik.org/docs/tracking-api/
 *
 * @category Piwik
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
	
	const VERSION = 1;
	
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
    	$this->customData = false;
    	$this->forcedDatetime = false;

    	$this->requestCookie = '';
    	$this->idSite = $idSite;
    	$this->urlReferer = @$_SERVER['HTTP_REFERER'];
    	$this->pageUrl = self::getCurrentUrl();
    	$this->ip = @$_SERVER['REMOTE_ADDR'];
    	$this->acceptLanguage = @$_SERVER['HTTP_ACCEPT_LANGUAGE'];
    	$this->userAgent = @$_SERVER['HTTP_USER_AGENT'];
    	if(!empty($apiUrl)) {
    		self::$URL = $apiUrl;
    	}
    }
    
    /**
     * Sets the current URL being tracked
     * @param string $url
     */
	public function setUrl( $url )
    {
    	$this->pageUrl = $url;
    }

    /**
     * Sets the URL referer used to track Referers details for new visits.
     * @param string $url
     */
    public function setUrlReferer( $url )
    {
    	$this->urlReferer = $url;
    }
    
    /**
     * Sets custom data to be passed to the piwik.php script, 
     * with the variable name 'data'. Data will be JSON encoded.
     * 
     * @param mixed $data An array, strings, ints, etc.
     */
    public function setCustomData( $data )
    {
    	$this->customData = json_encode($data);
    }
    
    /**
     * Sets the Browser language. Used to detect visitor Countries.
     * 
     * @param string $acceptLanguage
     */
    public function setBrowserLanguage( $acceptLanguage )
    {
    	$this->acceptLanguage = $acceptLanguage;
    }
    
    /**
     * Sets the user agent, used to detect OS and browser.
     * 
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
    	$this->userAgent = $userAgent;
    }
    
    /**
     * Sets local visitor time.
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
     * Sets cookie support (Cookie appears in the List of plugins report)
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
     * Tracks a page view
     * 
     * @param string $documentTitle Page view name as it will appear in Piwik reports
     * @return string Response
     */
    public function doTrackPageView( $documentTitle )
    {
    	$url = $this->getUrlTrackPageView($documentTitle);
    	return $this->sendRequest($url);
    } 
    
    /**
     * Tracks a Goal
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
        // Referer could be udpated to be the current URL temporarily (to mimic JS behavior)
    	$url = $this->getUrlTrackAction($actionUrl, $actionType);
    	return $this->sendRequest($url); 
    }
    
    /**
     * By default, PiwikTracker will read cookies from the response and sets them in the next request.
     * This can be disabled by calling this function.
     * @return void
     */
    public function disableCookieSupport()
    {
    	$this->cookieSupport = false;
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
     * Do not use - this will only work when used in Piwik unit tests
     * @ignore
     */
    public function setForceVisitDateTime($dateTime)
    {
    	$this->forcedDatetime = $dateTime;
    }
    
    /**
     * Do not use - this will only work when used in Piwik unit tests
     * @ignore
     */
    public function setIp($ip)
    {
    	$this->ip = $ip;
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
			
    		list($header,$content) = explode("\r\n\r\n", $response, $limitCount = 2);
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
		preg_match('/^Set-Cookie: (.*?);/m', $header, $cookie);
		if(!empty($cookie[1]))
		{
			$this->requestCookie = $cookie[1];
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
    	if(strpos(self::$URL, '/piwik.php') === false)
    	{
    		self::$URL .= '/piwik.php';
    	}
    	$url = self::$URL .
	 		'?idsite=' . $idSite .
			'&rec=1' .
			'&apiv=' . self::VERSION . 
	        '&url=' . urlencode($this->pageUrl) .
			'&urlref=' . urlencode($this->urlReferer) .
	        '&rand=' . mt_rand() .
    	
    		// Optional since debugger can be triggered remotely
    		'&XDEBUG_SESSION_START=' . @$_GET['XDEBUG_SESSION_START'] . 
	        '&KEY=' . @$_GET['KEY'] .
    	 
    		// only allowed in tests (see tests/integration/piwik.php)
			(!empty($this->ip) ? '&cip=' . $this->ip : '') .
			(!empty($this->forcedDatetime) ? '&cdt=' . urlencode($this->forcedDatetime) : '') .
	        
			// These parameters are set by the JS, but optional when using API
	        (!empty($this->plugins) ? $this->plugins : '') . 
			(($this->localHour !== false && $this->localMinute !== false && $this->localSecond !== false) ? '&h=' . $this->localHour . '&m=' . $this->localMinute  . '&s=' . $this->localSecond : '' ).
	        (!empty($this->width) && !empty($this->height) ? '&res=' . $this->width . 'x' . $this->height : '') .
	        (!empty($this->hasCookies) ? '&cookie=' . $this->hasCookies : '') .
	        (!empty($this->customData) ? '&data=' . $this->customData : '') 
        ;
    	return $url;
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

