<?php
/**
 * Piwik - free/libre analytics platform
 *
 * Client to record visits, page views, Goals, Ecommerce activity (product views, add to carts, Ecommerce orders) in a Piwik server.
 * This is a PHP Version of the piwik.js standard Tracking API.
 * For more information, see http://piwik.org/docs/tracking-api/
 *
 * This class requires:
 *  - json extension (json_decode, json_encode)
 *  - CURL or STREAM extensions (to issue the http request to Piwik)
 *
 * @license released under BSD License http://www.opensource.org/licenses/bsd-license.php
 * @link http://piwik.org/docs/tracking-api/
 *
 * @category Piwik
 * @package PiwikTracker
 */

/**
 * PiwikTracker implements the Piwik Tracking Web API.
 *
 * The PHP Tracking Client provides all features of the Javascript Tracker, such as Ecommerce Tracking, Custom Variable, Event tracking and more.
 * Functions are named the same as the Javascript functions.
 *
 * See introduction docs at: {@link http://piwik.org/docs/tracking-api/}
 *
 * ### Example: using the PHP PiwikTracker class
 *
 * The following code snippet is an advanced example of how to track a Page View using the Tracking API PHP client.
 *
 *      $t = new PiwikTracker( $idSite = 1, 'http://example.org/piwik/');
 *
 *      // Optional function calls
 *      $t->setUserAgent( "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB) Firefox/3.6.6");
 *      $t->setBrowserLanguage('fr');
 *      $t->setLocalTime( '12:34:06' );
 *      $t->setResolution( 1024, 768 );
 *      $t->setBrowserHasCookies(true);
 *      $t->setPlugins($flash = true, $java = true, $director = false);
 *
 *      // set a Custom Variable called 'Gender'
 *      $t->setCustomVariable( 1, 'gender', 'male' );
 *
 *      // If you want to force the visitor IP, or force the server date time to a date in the past,
 *      // it is required to authenticate the Tracking request by calling setTokenAuth
 *      // You can pass the Super User token_auth or any user with 'admin' privilege on the website $idSite
 *      $t->setTokenAuth( $token_auth );
 *      $t->setIp( "134.10.22.1" );
 *      $t->setForceVisitDateTime( '2011-04-05 23:55:02' );
 *
 *      // if you wanted to force to record the page view or conversion to a specific User ID
 *      // $t->setUserId( "username@example.org" );
 *      // Mandatory: set the URL being tracked
 *      $t->setUrl( $url = 'http://example.org/store/list-category-toys/' );
 *
 *      // Finally, track the page view with a Custom Page Title
 *      // In the standard JS API, the content of the <title> tag would be set as the page title
 *      $t->doTrackPageView('This is the page title');
 *
 * ### Example: tracking Ecommerce interactions
 *
 * Here is an example showing how to track Ecommerce interactions on your website, using the PHP Tracking API.
 * Usually, Ecommerce tracking is done using standard Javascript code,
 * but it is very common to record Ecommerce interactions after the fact
 * (for example, when payment is done with Paypal and user doesn't come back on the website after purchase).
 * For more information about Ecommerce tracking in Piwik, check out the documentation: Tracking Ecommerce in Piwik.
 *
 *      $t = new PiwikTracker( $idSite = 1, 'http://example.org/piwik/');
 *
 *      // Force IP to the actual visitor IP
 *      $t->setTokenAuth( $token_auth );
 *      $t->setIp( "134.10.22.1" );
 *
 *      // Example 1: on a Product page, track an "Ecommerce Product view"
 *      $t->setUrl( $url = 'http://www.mystore.com/Endurance-Shackletons-Legendary-Antarctic-Expedition' );
 *      $t->setEcommerceView($sku = 'SKU0011', $name = 'Endurance - Shackleton', $category = 'Books');
 *      $t->doTrackPageView( 'Endurance Shackletons Legendary Antarctic Expedition - Mystore.com');
 *
 *      // Example 2: Tracking Ecommerce Cart containing 2 products
 *      $t->addEcommerceItem($sku = 'SKU0011', $name = 'Endurance - Shackleton' , $category = 'Books', $price = 17, $quantity = 1);
 *      // Note that when setting a product category, you can specify an array of up to 5 categories to track for this product
 *      $t->addEcommerceItem($sku = 'SKU0321', $name = 'Amélie' , $categories = array('DVD Foreign','Best sellers','Our pick'), $price = 25, $quantity = 1);
 *      $t->doTrackEcommerceCartUpdate($grandTotal = 42);
 *
 *      // Example 3: Tracking Ecommerce Order
 *      $t->addEcommerceItem($sku = 'SKU0011', $name = 'Endurance - Shackleton' , $category = 'Books', $price = 17, $quantity = 1);
 *      $t->addEcommerceItem($sku = 'SKU0321', $name = 'Amélie' , $categories = array('DVD Foreign','Best sellers','Our pick'), $price = 25, $quantity = 1);
 *      $t->doTrackEcommerceOrder($orderId = 'B000111387', $grandTotal = 55.5, $subTotal = 42, $tax = 8, $shipping = 5.5, $discount = 10);
 *
 * ### Note: authenticating with the token_auth
 *
 * To set the visitor IP, or the date and time of the visit, or to force to record the visit (or page, or goal conversion) to a specific Visitor ID,
 * you must call setTokenAuth( $token_auth ). The token_auth must be either the Super User token_auth,
 * or the token_auth of any user with 'admin' permission for the website you are recording data against.
 *
 * @package PiwikTracker
 * @api
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
     * Charset
     * @see setPageCharset
     * @ignore
     */
    const DEFAULT_CHARSET_PARAMETER_VALUES = 'utf-8';

    /**
     * See piwik.js
     */
    const FIRST_PARTY_COOKIES_PREFIX = '_pk_';

    /**
     * Ecommerce item page view tracking stores item's metadata in these Custom Variables slots.
     */
    const CVAR_INDEX_ECOMMERCE_ITEM_PRICE = 2;
    const CVAR_INDEX_ECOMMERCE_ITEM_SKU = 3;
    const CVAR_INDEX_ECOMMERCE_ITEM_NAME = 4;
    const CVAR_INDEX_ECOMMERCE_ITEM_CATEGORY = 5;

    const DEFAULT_COOKIE_PATH = '/';

    /**
     * Builds a PiwikTracker object, used to track visits, pages and Goal conversions
     * for a specific website, by using the Piwik Tracking API.
     *
     * @param int $idSite Id site to be tracked
     * @param string $apiUrl "http://example.org/piwik/" or "http://piwik.example.org/"
     *                         If set, will overwrite PiwikTracker::$URL
     */
    function __construct($idSite, $apiUrl = '')
    {
        $this->userAgent = false;
        $this->localHour = false;
        $this->localMinute = false;
        $this->localSecond = false;
        $this->hasCookies = false;
        $this->plugins = false;
        $this->pageCustomVar = false;
        $this->eventCustomVar = false;
        $this->customData = false;
        $this->forcedDatetime = false;
        $this->forcedNewVisit = false;
        $this->token_auth = false;
        $this->attributionInfo = false;
        $this->ecommerceLastOrderTimestamp = false;
        $this->ecommerceItems = array();
        $this->generationTime = false;

        $this->idSite = $idSite;
        $this->urlReferrer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
        $this->pageCharset = self::DEFAULT_CHARSET_PARAMETER_VALUES;
        $this->pageUrl = self::getCurrentUrl();
        $this->ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
        $this->acceptLanguage = !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : false;
        $this->userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;
        if (!empty($apiUrl)) {
            self::$URL = $apiUrl;
        }

        // Life of the visitor cookie (in sec)
        $this->configVisitorCookieTimeout = 33955200; // 13 months (365 + 28 days)
        // Life of the session cookie (in sec)
        $this->configSessionCookieTimeout = 1800; // 30 minutes
        // Life of the session cookie (in sec)
        $this->configReferralCookieTimeout = 15768000; // 6 months

        // Visitor Ids in order
        $this->userId = false;
        $this->forcedVisitorId = false;
        $this->cookieVisitorId = false;
        $this->randomVisitorId = false;

        $this->setNewVisitorId();

        $this->configCookiesDisabled = false;
        $this->configCookiePath = self::DEFAULT_COOKIE_PATH;
        $this->configCookieDomain = '';

        $this->currentTs = time();
        $this->createTs = $this->currentTs;
        $this->visitCount = 0;
        $this->currentVisitTs = false;
        $this->lastVisitTs = false;
        $this->lastEcommerceOrderTs = false;

        // Allow debug while blocking the request
        $this->requestTimeout = 600;
        $this->doBulkRequests = false;
        $this->storedTrackingActions = array();

        $this->visitorCustomVar = $this->getCustomVariablesFromCookie();
    }

    /**
     * By default, Piwik expects utf-8 encoded values, for example
     * for the page URL parameter values, Page Title, etc.
     * It is recommended to only send UTF-8 data to Piwik.
     * If required though, you can also specify another charset using this function.
     *
     * @param string $charset
     */
    public function setPageCharset($charset = '')
    {
        $this->pageCharset = $charset;
    }

    /**
     * Sets the current URL being tracked
     *
     * @param string $url Raw URL (not URL encoded)
     */
    public function setUrl($url)
    {
        $this->pageUrl = $url;
    }

    /**
     * Sets the URL referrer used to track Referrers details for new visits.
     *
     * @param string $url Raw URL (not URL encoded)
     */
    public function setUrlReferrer($url)
    {
        $this->urlReferrer = $url;
    }

    /**
     * Sets the time that generating the document on the server side took.
     *
     * @param int $timeMs Generation time in ms
     */
    public function setGenerationTime($timeMs)
    {
        $this->generationTime = $timeMs;
    }

    /**
     * @deprecated
     * @ignore
     */
    public function setUrlReferer($url)
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
     * If you call enableCookies() then these referral attribution values will be set
     * to the 'ref' first party cookie storing referral information.
     *
     * @param string $jsonEncoded JSON encoded array containing Attribution info
     * @throws Exception
     * @see function getAttributionInfo() in https://github.com/piwik/piwik/blob/master/js/piwik.js
     */
    public function setAttributionInfo($jsonEncoded)
    {
        $decoded = json_decode($jsonEncoded, $assoc = true);
        if (!is_array($decoded)) {
            throw new Exception("setAttributionInfo() is expecting a JSON encoded string, $jsonEncoded given");
        }
        $this->attributionInfo = $decoded;
    }

    /**
     * Sets Visit Custom Variable.
     * See http://piwik.org/docs/custom-variables/
     *
     * @param int $id Custom variable slot ID from 1-5
     * @param string $name Custom variable name
     * @param string $value Custom variable value
     * @param string $scope Custom variable scope. Possible values: visit, page, event
     * @throws Exception
     */
    public function setCustomVariable($id, $name, $value, $scope = 'visit')
    {
        if (!is_int($id)) {
            throw new Exception("Parameter id to setCustomVariable should be an integer");
        }
        if ($scope == 'page') {
            $this->pageCustomVar[$id] = array($name, $value);
        } elseif($scope == 'event') {
            $this->eventCustomVar[$id] = array($name, $value);
        } elseif ($scope == 'visit') {
            $this->visitorCustomVar[$id] = array($name, $value);
        } else {
            throw new Exception("Invalid 'scope' parameter value");
        }
    }

    /**
     * Returns the currently assigned Custom Variable.
     *
     * If scope is 'visit', it will attempt to read the value set in the first party cookie created by Piwik Tracker ($_COOKIE array).
     *
     * @param int $id Custom Variable integer index to fetch from cookie. Should be a value from 1 to 5
     * @param string $scope Custom variable scope. Possible values: visit, page, event
     *
     * @throws Exception
     * @return mixed An array with this format: array( 0 => CustomVariableName, 1 => CustomVariableValue ) or false
     * @see Piwik.js getCustomVariable()
     */
    public function getCustomVariable($id, $scope = 'visit')
    {
        if ($scope == 'page') {
            return isset($this->pageCustomVar[$id]) ? $this->pageCustomVar[$id] : false;
        } elseif ($scope == 'event') {
            return  isset($this->eventCustomVar[$id]) ? $this->eventCustomVar[$id] : false;
        } else if ($scope != 'visit') {
            throw new Exception("Invalid 'scope' parameter value");
        }
        if (!empty($this->visitorCustomVar[$id])) {
            return $this->visitorCustomVar[$id];
        }
        $cookieDecoded = $this->getCustomVariablesFromCookie();
        if (!is_int($id)) {
            throw new Exception("Parameter to getCustomVariable should be an integer");
        }
        if (!is_array($cookieDecoded)
            || !isset($cookieDecoded[$id])
            || !is_array($cookieDecoded[$id])
            || count($cookieDecoded[$id]) != 2
        ) {
            return false;
        }
        return $cookieDecoded[$id];
    }

    /**
     * Clears any Custom Variable that may be have been set.
     *
     * This can be useful when you have enabled bulk requests,
     * and you wish to clear Custom Variables of 'visit' scope.
     */
    public function clearCustomVariables()
    {
        $this->visitorCustomVar = array();
        $this->pageCustomVar = array();
        $this->eventCustomVar = array();
    }


    /**
     * Sets the current visitor ID to a random new one.
     */
    public function setNewVisitorId()
    {
        $this->randomVisitorId = substr(md5(uniqid(rand(), true)), 0, self::LENGTH_VISITOR_ID);
        $this->userId = false;
        $this->forcedVisitorId = false;
        $this->cookieVisitorId = false;
    }

    /**
     * Sets the current site ID.
     *
     * @param int $idSite
     */
    public function setIdSite($idSite)
    {
        $this->idSite = $idSite;
    }

    /**
     * Sets the Browser language. Used to guess visitor countries when GeoIP is not enabled
     *
     * @param string $acceptLanguage For example "fr-fr"
     */
    public function setBrowserLanguage($acceptLanguage)
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
     * Sets the country of the visitor. If not used, Piwik will try to find the country
     * using either the visitor's IP address or language.
     *
     * Allowed only for Admin/Super User, must be used along with setTokenAuth().
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Sets the region of the visitor. If not used, Piwik may try to find the region
     * using the visitor's IP address (if configured to do so).
     *
     * Allowed only for Admin/Super User, must be used along with setTokenAuth().
     * @param string $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * Sets the city of the visitor. If not used, Piwik may try to find the city
     * using the visitor's IP address (if configured to do so).
     *
     * Allowed only for Admin/Super User, must be used along with setTokenAuth().
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Sets the latitude of the visitor. If not used, Piwik may try to find the visitor's
     * latitude using the visitor's IP address (if configured to do so).
     *
     * Allowed only for Admin/Super User, must be used along with setTokenAuth().
     * @param float $lat
     */
    public function setLatitude($lat)
    {
        $this->lat = $lat;
    }

    /**
     * Sets the longitude of the visitor. If not used, Piwik may try to find the visitor's
     * longitude using the visitor's IP address (if configured to do so).
     *
     * Allowed only for Admin/Super User, must be used along with setTokenAuth().
     * @param float $long
     */
    public function setLongitude($long)
    {
        $this->long = $long;
    }

    /**
     * Enables the bulk request feature. When used, each tracking action is stored until the
     * doBulkTrack method is called. This method will send all tracking data at once.
     *
     */
    public function enableBulkTracking()
    {
        $this->doBulkRequests = true;
    }

    /**
     * Enable Cookie Creation - this will cause a first party VisitorId cookie to be set when the VisitorId is set or reset
     *
     * @param string $domain (optional) Set first-party cookie domain. Accepted values: example.com, *.example.com (same as .example.com) or subdomain.example.com
     * @param string $path (optional) Set first-party cookie path
     */
    public function enableCookies( $domain = '', $path = '/' )
    {
        $this->configCookiesDisabled = false;
        $this->configCookieDomain = self::domainFixup($domain);
        $this->configCookiePath = $path;
    }

    /**
     * Fix-up domain
     */
    static protected function domainFixup($domain)
    {
        $dl = strlen($domain) - 1;
        // remove trailing '.'
        if ($domain{$dl} === '.') {
            $domain = substr($domain, 0, $dl);
        }
        // remove leading '*'
        if (substr($domain, 0, 2) === '*.') {
            $domain = substr($domain, 1);
        }
        return $domain;
    }

    /**
     * Get cookie name with prefix and domain hash
     */
    protected function getCookieName($cookieName) {
        // NOTE: If the cookie name is changed, we must also update the method in piwik.js with the same name.
        $hash = substr( sha1( ($this->configCookieDomain == '' ? self::getCurrentHost() : $this->configCookieDomain)  . $this->configCookiePath ), 0, 4);
        return self::FIRST_PARTY_COOKIES_PREFIX . $cookieName . '.' . $this->idSite . '.' . $hash;
    }

    /**
     * Tracks a page view
     *
     * @param string $documentTitle Page title as it will appear in the Actions > Page titles report
     * @return mixed Response string or true if using bulk requests.
     */
    public function doTrackPageView($documentTitle)
    {
        $url = $this->getUrlTrackPageView($documentTitle);
        return $this->sendRequest($url);
    }

    /**
     * Tracks an event
     *
     * @param string $category The Event Category (Videos, Music, Games...)
     * @param string $action The Event's Action (Play, Pause, Duration, Add Playlist, Downloaded, Clicked...)
     * @param string $name (optional) The Event's object Name (a particular Movie name, or Song name, or File name...)
     * @param float $value (optional) The Event's value
     * @return mixed Response string or true if using bulk requests.
     */
    public function doTrackEvent($category, $action, $name = false, $value = false)
    {
        $url = $this->getUrlTrackEvent($category, $action, $name, $value);
        return $this->sendRequest($url);
    }

    /**
     * Tracks a content impression
     *
     * @param string $contentName The name of the content. For instance 'Ad Foo Bar'
     * @param string $contentPiece The actual content. For instance the path to an image, video, audio, any text
     * @param string|false $contentTarget (optional) The target of the content. For instance the URL of a landing page.
     * @return mixed Response string or true if using bulk requests.
     */
    public function doTrackContentImpression($contentName, $contentPiece = 'Unknown', $contentTarget = false)
    {
        $url = $this->getUrlTrackContentImpression($contentName, $contentPiece, $contentTarget);
        return $this->sendRequest($url);
    }

    /**
     * Tracks a content interaction. Make sure you have tracked a content impression using the same content name and
     * content piece, otherwise it will not count. To do so you should call the method doTrackContentImpression();
     *
     * @param string $interaction The name of the interaction with the content. For instance a 'click'
     * @param string $contentName The name of the content. For instance 'Ad Foo Bar'
     * @param string $contentPiece The actual content. For instance the path to an image, video, audio, any text
     * @param string|false $contentTarget (optional) The target the content leading to when an interaction occurs. For instance the URL of a landing page.
     * @return mixed Response string or true if using bulk requests.
     */
    public function doTrackContentInteraction($interaction, $contentName, $contentPiece = 'Unknown', $contentTarget = false)
    {
        $url = $this->getUrlTrackContentInteraction($interaction, $contentName, $contentPiece, $contentTarget);
        return $this->sendRequest($url);
    }

    /**
     * Tracks an internal Site Search query, and optionally tracks the Search Category, and Search results Count.
     * These are used to populate reports in Actions > Site Search.
     *
     * @param string $keyword Searched query on the site
     * @param string $category (optional) Search engine category if applicable
     * @param bool|int $countResults (optional) results displayed on the search result page. Used to track "zero result" keywords.
     *
     * @return mixed Response or true if using bulk requests.
     */
    public function doTrackSiteSearch($keyword, $category = '', $countResults = false)
    {
        $url = $this->getUrlTrackSiteSearch($keyword, $category, $countResults);
        return $this->sendRequest($url);
    }

    /**
     * Records a Goal conversion
     *
     * @param int $idGoal Id Goal to record a conversion
     * @param float $revenue Revenue for this conversion
     * @return mixed Response or true if using bulk request
     */
    public function doTrackGoal($idGoal, $revenue = 0.0)
    {
        $url = $this->getUrlTrackGoal($idGoal, $revenue);
        return $this->sendRequest($url);
    }

    /**
     * Tracks a download or outlink
     *
     * @param string $actionUrl URL of the download or outlink
     * @param string $actionType Type of the action: 'download' or 'link'
     * @return mixed Response or true if using bulk request
     */
    public function doTrackAction($actionUrl, $actionType)
    {
        // Referrer could be udpated to be the current URL temporarily (to mimic JS behavior)
        $url = $this->getUrlTrackAction($actionUrl, $actionType);
        return $this->sendRequest($url);
    }

    /**
     * Adds an item in the Ecommerce order.
     *
     * This should be called before doTrackEcommerceOrder(), or before doTrackEcommerceCartUpdate().
     * This function can be called for all individual products in the cart (or order).
     * SKU parameter is mandatory. Other parameters are optional (set to false if value not known).
     * Ecommerce items added via this function are automatically cleared when doTrackEcommerceOrder() or getUrlTrackEcommerceOrder() is called.
     *
     * @param string $sku (required) SKU, Product identifier
     * @param string $name (optional) Product name
     * @param string|array $category (optional) Product category, or array of product categories (up to 5 categories can be specified for a given product)
     * @param float|int $price (optional) Individual product price (supports integer and decimal prices)
     * @param int $quantity (optional) Product quantity. If not specified, will default to 1 in the Reports
     * @throws Exception
     */
    public function addEcommerceItem($sku, $name = '', $category = '', $price = 0.0, $quantity = 1)
    {
        if (empty($sku)) {
            throw new Exception("You must specify a SKU for the Ecommerce item");
        }
        $this->ecommerceItems[$sku] = array($sku, $name, $category, $price, $quantity);
    }

    /**
     * Tracks a Cart Update (add item, remove item, update item).
     *
     * On every Cart update, you must call addEcommerceItem() for each item (product) in the cart,
     * including the items that haven't been updated since the last cart update.
     * Items which were in the previous cart and are not sent in later Cart updates will be deleted from the cart (in the database).
     *
     * @param float $grandTotal Cart grandTotal (typically the sum of all items' prices)
     * @return mixed Response or true if using bulk request
     */
    public function doTrackEcommerceCartUpdate($grandTotal)
    {
        $url = $this->getUrlTrackEcommerceCartUpdate($grandTotal);
        return $this->sendRequest($url);
    }

    /**
     * Sends all stored tracking actions at once. Only has an effect if bulk tracking is enabled.
     *
     * To enable bulk tracking, call enableBulkTracking().
     *
     * @throws Exception
     * @return string Response
     */
    public function doBulkTrack()
    {
        if (empty($this->storedTrackingActions)) {
            throw new Exception("Error:  you must call the function doTrackPageView or doTrackGoal from this class, before calling this method doBulkTrack()");
        }

        $data = array('requests' => $this->storedTrackingActions);

        // token_auth is not required by default, except if bulk_requests_require_authentication=1
        if(!empty($this->token_auth)) {
            $data['token_auth'] = $this->token_auth;
        }

        $postData = json_encode($data);
        $response = $this->sendRequest($this->getBaseUrl(), 'POST', $postData, $force = true);

        $this->storedTrackingActions = array();

        return $response;
    }

    /**
     * Tracks an Ecommerce order.
     *
     * If the Ecommerce order contains items (products), you must call first the addEcommerceItem() for each item in the order.
     * All revenues (grandTotal, subTotal, tax, shipping, discount) will be individually summed and reported in Piwik reports.
     * Only the parameters $orderId and $grandTotal are required.
     *
     * @param string|int $orderId (required) Unique Order ID.
     *                This will be used to count this order only once in the event the order page is reloaded several times.
     *                orderId must be unique for each transaction, even on different days, or the transaction will not be recorded by Piwik.
     * @param float $grandTotal (required) Grand Total revenue of the transaction (including tax, shipping, etc.)
     * @param float $subTotal (optional) Sub total amount, typically the sum of items prices for all items in this order (before Tax and Shipping costs are applied)
     * @param float $tax (optional) Tax amount for this order
     * @param float $shipping (optional) Shipping amount for this order
     * @param float $discount (optional) Discounted amount in this order
     * @return mixed Response or true if using bulk request
     */
    public function doTrackEcommerceOrder($orderId, $grandTotal, $subTotal = 0.0, $tax = 0.0, $shipping = 0.0, $discount = 0.0)
    {
        $url = $this->getUrlTrackEcommerceOrder($orderId, $grandTotal, $subTotal, $tax, $shipping, $discount);
        return $this->sendRequest($url);
    }

    /**
     * Sets the current page view as an item (product) page view, or an Ecommerce Category page view.
     *
     * This must be called before doTrackPageView() on this product/category page.
     * It will set 3 custom variables of scope "page" with the SKU, Name and Category for this page view.
     * Note: Custom Variables of scope "page" slots 3, 4 and 5 will be used.
     *
     * On a category page, you may set the parameter $category only and set the other parameters to false.
     *
     * Tracking Product/Category page views will allow Piwik to report on Product & Categories
     * conversion rates (Conversion rate = Ecommerce orders containing this product or category / Visits to the product or category)
     *
     * @param string $sku Product SKU being viewed
     * @param string $name Product Name being viewed
     * @param string|array $category Category being viewed. On a Product page, this is the product's category.
     *                                You can also specify an array of up to 5 categories for a given page view.
     * @param float $price Specify the price at which the item was displayed
     */
    public function setEcommerceView($sku = '', $name = '', $category = '', $price = 0.0)
    {
        if (!empty($category)) {
            if (is_array($category)) {
                $category = json_encode($category);
            }
        } else {
            $category = "";
        }
        $this->pageCustomVar[self::CVAR_INDEX_ECOMMERCE_ITEM_CATEGORY] = array('_pkc', $category);

        if (!empty($price)) {
            $this->pageCustomVar[self::CVAR_INDEX_ECOMMERCE_ITEM_PRICE] = array('_pkp', (float)$price);
        }

        // On a category page, do not record "Product name not defined"
        if (empty($sku) && empty($name)) {
            return;
        }
        if (!empty($sku)) {
            $this->pageCustomVar[self::CVAR_INDEX_ECOMMERCE_ITEM_SKU] = array('_pks', $sku);
        }
        if (empty($name)) {
            $name = "";
        }
        $this->pageCustomVar[self::CVAR_INDEX_ECOMMERCE_ITEM_NAME] = array('_pkn', $name);
    }

    /**
     * Returns URL used to track Ecommerce Cart updates
     * Calling this function will reinitializes the property ecommerceItems to empty array
     * so items will have to be added again via addEcommerceItem()
     * @ignore
     */
    public function getUrlTrackEcommerceCartUpdate($grandTotal)
    {
        $url = $this->getUrlTrackEcommerce($grandTotal);
        return $url;
    }

    /**
     * Returns URL used to track Ecommerce Orders
     * Calling this function will reinitializes the property ecommerceItems to empty array
     * so items will have to be added again via addEcommerceItem()
     * @ignore
     */
    public function getUrlTrackEcommerceOrder($orderId, $grandTotal, $subTotal = 0.0, $tax = 0.0, $shipping = 0.0, $discount = 0.0)
    {
        if (empty($orderId)) {
            throw new Exception("You must specifiy an orderId for the Ecommerce order");
        }
        $url = $this->getUrlTrackEcommerce($grandTotal, $subTotal, $tax, $shipping, $discount);
        $url .= '&ec_id=' . urlencode($orderId);
        $this->ecommerceLastOrderTimestamp = $this->getTimestamp();
        return $url;
    }

    /**
     * Returns URL used to track Ecommerce orders
     * Calling this function will reinitializes the property ecommerceItems to empty array
     * so items will have to be added again via addEcommerceItem()
     * @ignore
     */
    protected function getUrlTrackEcommerce($grandTotal, $subTotal = 0.0, $tax = 0.0, $shipping = 0.0, $discount = 0.0)
    {
        if (!is_numeric($grandTotal)) {
            throw new Exception("You must specifiy a grandTotal for the Ecommerce order (or Cart update)");
        }

        $url = $this->getRequest($this->idSite);
        $url .= '&idgoal=0';
        if (!empty($grandTotal)) {
            $url .= '&revenue=' . $grandTotal;
        }
        if (!empty($subTotal)) {
            $url .= '&ec_st=' . $subTotal;
        }
        if (!empty($tax)) {
            $url .= '&ec_tx=' . $tax;
        }
        if (!empty($shipping)) {
            $url .= '&ec_sh=' . $shipping;
        }
        if (!empty($discount)) {
            $url .= '&ec_dt=' . $discount;
        }
        if (!empty($this->ecommerceItems)) {
            // Removing the SKU index in the array before JSON encoding
            $items = array();
            foreach ($this->ecommerceItems as $item) {
                $items[] = $item;
            }
            $url .= '&ec_items=' . urlencode(json_encode($items));
        }
        $this->ecommerceItems = array();
        return $url;
    }

    /**
     * Builds URL to track a page view.
     *
     * @see doTrackPageView()
     * @param string $documentTitle Page view name as it will appear in Piwik reports
     * @return string URL to piwik.php with all parameters set to track the pageview
     */
    public function getUrlTrackPageView($documentTitle = '')
    {
        $url = $this->getRequest($this->idSite);
        if (strlen($documentTitle) > 0) {
            $url .= '&action_name=' . urlencode($documentTitle);
        }
        return $url;
    }

    /**
     * Builds URL to track a custom event.
     *
     * @see doTrackEvent()
     * @param string $category The Event Category (Videos, Music, Games...)
     * @param string $action The Event's Action (Play, Pause, Duration, Add Playlist, Downloaded, Clicked...)
     * @param string $name (optional) The Event's object Name (a particular Movie name, or Song name, or File name...)
     * @param float $value (optional) The Event's value
     * @return string URL to piwik.php with all parameters set to track the pageview
     */
    public function getUrlTrackEvent($category, $action, $name = false, $value = false)
    {
        $url = $this->getRequest($this->idSite);
        if(strlen($category) == 0) {
            throw new Exception("You must specify an Event Category name (Music, Videos, Games...).");
        }
        if(strlen($action) == 0) {
            throw new Exception("You must specify an Event action (click, view, add...).");
        }

        $url .= '&e_c=' . urlencode($category);
        $url .= '&e_a=' . urlencode($action);

        if(strlen($name) > 0) {
            $url .= '&e_n=' . urlencode($name);
        }
        if(strlen($value) > 0) {
            $url .= '&e_v=' . $value;
        }
        return $url;
    }

    /**
     * Builds URL to track a content impression.
     *
     * @see doTrackContentImpression()
     * @param string $contentName The name of the content. For instance 'Ad Foo Bar'
     * @param string $contentPiece The actual content. For instance the path to an image, video, audio, any text
     * @param string|false $contentTarget (optional) The target of the content. For instance the URL of a landing page.
     * @throws Exception In case $contentName is empty
     * @return string URL to piwik.php with all parameters set to track the pageview
     */
    public function getUrlTrackContentImpression($contentName, $contentPiece, $contentTarget)
    {
        $url = $this->getRequest($this->idSite);

        if (strlen($contentName) == 0) {
            throw new Exception("You must specify a content name");
        }

        $url .= '&c_n=' . urlencode($contentName);

        if (!empty($contentPiece) && strlen($contentPiece) > 0) {
            $url .= '&c_p=' . urlencode($contentPiece);
        }
        if (!empty($contentTarget) && strlen($contentTarget) > 0) {
            $url .= '&c_t=' . urlencode($contentTarget);
        }

        return $url;
    }

    /**
     * Builds URL to track a content impression.
     *
     * @see doTrackContentInteraction()
     * @param string $interaction The name of the interaction with the content. For instance a 'click'
     * @param string $contentName The name of the content. For instance 'Ad Foo Bar'
     * @param string $contentPiece The actual content. For instance the path to an image, video, audio, any text
     * @param string|false $contentTarget (optional) The target the content leading to when an interaction occurs. For instance the URL of a landing page.
     * @throws Exception In case $interaction or $contentName is empty
     * @return string URL to piwik.php with all parameters set to track the pageview
     */
    public function getUrlTrackContentInteraction($interaction, $contentName, $contentPiece, $contentTarget)
    {
        $url = $this->getRequest($this->idSite);

        if (strlen($interaction) == 0) {
            throw new Exception("You must specify a name for the interaction");
        }

        if (strlen($contentName) == 0) {
            throw new Exception("You must specify a content name");
        }

        $url .= '&c_i=' . urlencode($interaction);
        $url .= '&c_n=' . urlencode($contentName);

        if (!empty($contentPiece) && strlen($contentPiece) > 0) {
            $url .= '&c_p=' . urlencode($contentPiece);
        }
        if (!empty($contentTarget) && strlen($contentTarget) > 0) {
            $url .= '&c_t=' . urlencode($contentTarget);
        }

        return $url;
    }

    /**
     * Builds URL to track a site search.
     *
     * @see doTrackSiteSearch()
     * @param string $keyword
     * @param string $category
     * @param int $countResults
     * @return string
     */
    public function getUrlTrackSiteSearch($keyword, $category, $countResults)
    {
        $url = $this->getRequest($this->idSite);
        $url .= '&search=' . urlencode($keyword);
        if (strlen($category) > 0) {
            $url .= '&search_cat=' . urlencode($category);
        }
        if (!empty($countResults) || $countResults === 0) {
            $url .= '&search_count=' . (int)$countResults;
        }
        return $url;
    }

    /**
     * Builds URL to track a goal with idGoal and revenue.
     *
     * @see doTrackGoal()
     * @param int $idGoal Id Goal to record a conversion
     * @param float $revenue Revenue for this conversion
     * @return string URL to piwik.php with all parameters set to track the goal conversion
     */
    public function getUrlTrackGoal($idGoal, $revenue = 0.0)
    {
        $url = $this->getRequest($this->idSite);
        $url .= '&idgoal=' . $idGoal;
        if (!empty($revenue)) {
            $url .= '&revenue=' . $revenue;
        }
        return $url;
    }

    /**
     * Builds URL to track a new action.
     *
     * @see doTrackAction()
     * @param string $actionUrl URL of the download or outlink
     * @param string $actionType Type of the action: 'download' or 'link'
     * @return string URL to piwik.php with all parameters set to track an action
     */
    public function getUrlTrackAction($actionUrl, $actionType)
    {
        $url = $this->getRequest($this->idSite);
        $url .= '&' . $actionType . '=' . $actionUrl;
        return $url;
    }

    /**
     * Overrides server date and time for the tracking requests.
     * By default Piwik will track requests for the "current datetime" but this function allows you
     * to track visits in the past. All times are in UTC.
     *
     * Allowed only for Super User, must be used along with setTokenAuth()
     * @see setTokenAuth()
     * @param string $dateTime Date with the format 'Y-m-d H:i:s', or a UNIX timestamp
     */
    public function setForceVisitDateTime($dateTime)
    {
        $this->forcedDatetime = $dateTime;
    }

    /**
     * Forces Piwik to create a new visit for the tracking request.
     *
     * By default, Piwik will create a new visit if the last request by this user was more than 30 minutes ago.
     * If you call setForceNewVisit() before calling doTrack*, then a new visit will be created for this request.
     *
     */
    public function setForceNewVisit()
    {
        $this->forcedNewVisit = true;
    }

    /**
     * Overrides IP address
     *
     * Allowed only for Super User, must be used along with setTokenAuth()
     * @see setTokenAuth()
     * @param string $ip IP string, eg. 130.54.2.1
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * Force the action to be recorded for a specific User. The User ID is a string representing a given user in your system.
     *
     * A User ID can be a username, UUID or an email address, or any number or string that uniquely identifies a user or client.
     *
     * @param string $userId  Any user ID string (eg. email address, ID, username). Must be non empty. Set to false to de-assign a user id previously set.
     * @throws Exception
     */
    public function setUserId($userId)
    {
        if($userId === false) {
            $this->setNewVisitorId();
            return;
        }
        if($userId === '') {
            throw new Exception("User ID cannot be empty.");
        }
        $this->userId = $userId;
    }

    /**
     * Hash function used internally by Piwik to hash a User ID into the Visitor ID.
     *
     * Note: matches implementation of Tracker\Request->getUserIdHashed()
     *
     * @param $id
     * @return string
     */
    static public function getUserIdHashed($id)
    {
        return substr( sha1( $id ), 0, 16);
    }

    /**
     * Forces the requests to be recorded for the specified Visitor ID.
     * Note: it is recommended to use ->setUserId($userId); instead.
     *
     * Rather than letting Piwik attribute the user with a heuristic based on IP and other user fingeprinting attributes,
     * force the action to be recorded for a particular visitor.
     *
     * If you use both setVisitorId and setUserId, setUserId will take precedence.
     * If not set, the visitor ID will be fetched from the 1st party cookie, or will be set to a random UUID.
     *
     * @deprecated We recommend to use  ->setUserId($userId).
     * @param string $visitorId 16 hexadecimal characters visitor ID, eg. "33c31e01394bdc63"
     * @throws Exception
     */
    public function setVisitorId($visitorId)
    {
        $hexChars = '01234567890abcdefABCDEF';
        if (strlen($visitorId) != self::LENGTH_VISITOR_ID
            || strspn($visitorId, $hexChars) !== strlen($visitorId)
        ) {
            throw new Exception("setVisitorId() expects a "
                . self::LENGTH_VISITOR_ID
                . " characters hexadecimal string (containing only the following: "
                . $hexChars
                . ")");
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
        if (!empty($this->userId)) {
            return $this->getUserIdHashed($this->userId);
        }
        if (!empty($this->forcedVisitorId)) {
            return $this->forcedVisitorId;
        }
        if ($this->loadVisitorIdCookie()) {
            return $this->cookieVisitorId;
        }
        return $this->randomVisitorId;
    }


    /**
     * Returns the User ID string, which may have been set via:
     *     $v->setUserId('username@example.org');
     *
     * @return bool
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Loads values from the VisitorId Cookie
     *
     * @return bool True if cookie exists and is valid, False otherwise
     */
    protected function loadVisitorIdCookie()
    {
        $idCookie = $this->getCookieMatchingName('id');
        if ($idCookie === false) {
            return false;
        }
        $parts = explode('.', $idCookie);
        if (strlen($parts[0]) != self::LENGTH_VISITOR_ID) {
            return false;
        }
        $this->cookieVisitorId = $parts[0]; // provides backward compatibility since getVisitorId() didn't change any existing VisitorId value
        $this->createTs = $parts[1];
        $this->visitCount = (int)$parts[2];
        $this->currentVisitTs = $parts[3];
        $this->lastVisitTs = $parts[4];
        if(isset($parts[5])) {
            $this->lastEcommerceOrderTs = $parts[5];
        }
        return true;
    }

    /**
     * Deletes all first party cookies from the client
     */
    public function deleteCookies()
    {
        $expire = $this->currentTs - 86400;
        $cookies = array('id', 'ses', 'cvar', 'ref');
        foreach($cookies as $cookie) {
            $this->setCookie($cookie, '', $expire);
        }
    }

    /**
     * Returns the currently assigned Attribution Information stored in a first party cookie.
     *
     * This function will only work if the user is initiating the current request, and his cookies
     * can be read by PHP from the $_COOKIE array.
     *
     * @return string JSON Encoded string containing the Referrer information for Goal conversion attribution.
     *                Will return false if the cookie could not be found
     * @see Piwik.js getAttributionInfo()
     */
    public function getAttributionInfo()
    {
        if(!empty($this->attributionInfo)) {
            return json_encode($this->attributionInfo);
        }
        return $this->getCookieMatchingName('ref');
    }

    /**
     * Some Tracking API functionnality requires express authentication, using either the
     * Super User token_auth, or a user with 'admin' access to the website.
     *
     * The following features require access:
     * - force the visitor IP
     * - force the date & time of the tracking requests rather than track for the current datetime
     *
     * @param string $token_auth token_auth 32 chars token_auth string
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
    public function setBrowserHasCookies($bool)
    {
        $this->hasCookies = $bool;
    }

    /**
     * Will append a custom string at the end of the Tracking request.
     * @param string $string
     */
    public function setDebugStringAppend($string)
    {
        $this->DEBUG_APPEND_URL = '&' . $string;
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
            '&fla=' . (int)$flash .
                '&java=' . (int)$java .
                '&dir=' . (int)$director .
                '&qt=' . (int)$quickTime .
                '&realp=' . (int)$realPlayer .
                '&pdf=' . (int)$pdf .
                '&wma=' . (int)$windowsMedia .
                '&gears=' . (int)$gears .
                '&ag=' . (int)$silverlight;
    }

    /**
     * By default, PiwikTracker will read first party cookies
     * from the request and write updated cookies in the response (using setrawcookie).
     * This can be disabled by calling this function.
     */
    public function disableCookieSupport()
    {
        $this->configCookiesDisabled = true;
    }

    /**
     * Returns the maximum number of seconds the tracker will spend waiting for a response
     * from Piwik. Defaults to 600 seconds.
     */
    public function getRequestTimeout()
    {
        return $this->requestTimeout;
    }

    /**
     * Sets the maximum number of seconds that the tracker will spend waiting for a response
     * from Piwik.
     *
     * @param int $timeout
     * @throws Exception
     */
    public function setRequestTimeout($timeout)
    {
        if (!is_int($timeout) || $timeout < 0) {
            throw new Exception("Invalid value supplied for request timeout: $timeout");
        }

        $this->requestTimeout = $timeout;
    }

    /**
     * Used in tests to output useful error messages.
     *
     * @ignore
     */
    static public $DEBUG_LAST_REQUESTED_URL = false;

    /**
     * @ignore
     */
    protected function sendRequest($url, $method = 'GET', $data = null, $force = false)
    {
        self::$DEBUG_LAST_REQUESTED_URL = $url;

        // if doing a bulk request, store the url
        if ($this->doBulkRequests && !$force) {
            $this->storedTrackingActions[]
                = $url
                . (!empty($this->userAgent) ? ('&ua=' . urlencode($this->userAgent)) : '')
                . (!empty($this->acceptLanguage) ? ('&lang=' . urlencode($this->acceptLanguage)) : '');

            // Clear custom variables so they don't get copied over to other users in the bulk request
            $this->clearCustomVariables();
            $this->userAgent = false;
            $this->acceptLanguage = false;
            return true;
        }

        if (function_exists('curl_init')) {
            $options = array(
                CURLOPT_URL            => $url,
                CURLOPT_USERAGENT      => $this->userAgent,
                CURLOPT_HEADER         => true,
                CURLOPT_TIMEOUT        => $this->requestTimeout,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => array(
                    'Accept-Language: ' . $this->acceptLanguage
                ));

            switch ($method) {
                case 'POST':
                    $options[CURLOPT_POST] = TRUE;
                    break;
                default:
                    break;
            }

            // only supports JSON data
            if (!empty($data)) {
                $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
                $options[CURLOPT_HTTPHEADER][] = 'Expect:';
                $options[CURLOPT_POSTFIELDS] = $data;
            }

            $ch = curl_init();
            curl_setopt_array($ch, $options);
            ob_start();
            $response = @curl_exec($ch);
            ob_end_clean();
            $content = '';
            if (!empty($response)) {
                list($header, $content) = explode("\r\n\r\n", $response, $limitCount = 2);
            }
        } else if (function_exists('stream_context_create')) {
            $stream_options = array(
                'http' => array(
                    'method'     => $method,
                    'user_agent' => $this->userAgent,
                    'header'     => "Accept-Language: " . $this->acceptLanguage . "\r\n",
                    'timeout'    => $this->requestTimeout, // PHP 5.2.1
                )
            );

            // only supports JSON data
            if (!empty($data)) {
                $stream_options['http']['header'] .= "Content-Type: application/json \r\n";
                $stream_options['http']['content'] = $data;
            }
            $ctx = stream_context_create($stream_options);
            $response = file_get_contents($url, 0, $ctx);
            $content = $response;
        }
        return $content;
    }

    /**
     * Returns current timestamp, or forced timestamp/datetime if it was set
     * @return string|int
     */
    protected function getTimestamp()
    {
        return !empty($this->forcedDatetime)
            ? strtotime($this->forcedDatetime)
            : time();
    }

    /**
     * Returns the base URL for the piwik server.
     */
    protected function getBaseUrl()
    {
        if (empty(self::$URL)) {
            throw new Exception('You must first set the Piwik Tracker URL by calling PiwikTracker::$URL = \'http://your-website.org/piwik/\';');
        }
        if (strpos(self::$URL, '/piwik.php') === false
            && strpos(self::$URL, '/proxy-piwik.php') === false
        ) {
            self::$URL .= '/piwik.php';
        }
        return self::$URL;
    }

    /**
     * @ignore
     */
    protected function getRequest($idSite)
    {
        $this->setFirstPartyCookies();

        $url = $this->getBaseUrl() .
            '?idsite=' . $idSite .
            '&rec=1' .
            '&apiv=' . self::VERSION .
            '&r=' . substr(strval(mt_rand()), 2, 6) .

            // XDEBUG_SESSIONS_START and KEY are related to the PHP Debugger, this can be ignored in other languages
            (!empty($_GET['XDEBUG_SESSION_START']) ? '&XDEBUG_SESSION_START=' . @urlencode($_GET['XDEBUG_SESSION_START']) : '') .
            (!empty($_GET['KEY']) ? '&KEY=' . @urlencode($_GET['KEY']) : '') .

            // Only allowed for Super User, token_auth required,
            (!empty($this->ip) ? '&cip=' . $this->ip : '') .
            (!empty($this->userId) ? '&uid=' . urlencode($this->userId) : '') .
            (!empty($this->forcedDatetime) ? '&cdt=' . urlencode($this->forcedDatetime) : '') .
            (!empty($this->forcedNewVisit) ? '&new_visit=1' : '') .
            ((!empty($this->token_auth) && !$this->doBulkRequests) ? '&token_auth=' . urlencode($this->token_auth) : '') .

            // Values collected from cookie
            '&_idts=' . $this->createTs .
            '&_idvc=' . $this->visitCount .
            (!empty($this->lastVisitTs) ? '&_viewts=' . $this->lastVisitTs : '' ) .
            (!empty($this->lastEcommerceOrderTs) ? '&_ects=' . $this->lastEcommerceOrderTs : '' ) .

            // These parameters are set by the JS, but optional when using API
            (!empty($this->plugins) ? $this->plugins : '') .
            (($this->localHour !== false && $this->localMinute !== false && $this->localSecond !== false) ? '&h=' . $this->localHour . '&m=' . $this->localMinute . '&s=' . $this->localSecond : '') .
            (!empty($this->width) && !empty($this->height) ? '&res=' . $this->width . 'x' . $this->height : '') .
            (!empty($this->hasCookies) ? '&cookie=' . $this->hasCookies : '') .
            (!empty($this->ecommerceLastOrderTimestamp) ? '&_ects=' . urlencode($this->ecommerceLastOrderTimestamp) : '') .

            // Various important attributes
            (!empty($this->customData) ? '&data=' . $this->customData : '') .
            (!empty($this->visitorCustomVar) ? '&_cvar=' . urlencode(json_encode($this->visitorCustomVar)) : '') .
            (!empty($this->pageCustomVar) ? '&cvar=' . urlencode(json_encode($this->pageCustomVar)) : '') .
            (!empty($this->eventCustomVar) ? '&e_cvar=' . urlencode(json_encode($this->eventCustomVar)) : '') .
            (!empty($this->generationTime) ? '&gt_ms=' . ((int)$this->generationTime) : '') .
            (!empty($this->forcedVisitorId) ? '&cid=' . $this->forcedVisitorId : '&_id=' . $this->getVisitorId()) .

            // URL parameters
            '&url=' . urlencode($this->pageUrl) .
            '&urlref=' . urlencode($this->urlReferrer) .
            ((!empty($this->pageCharset) && $this->pageCharset != self::DEFAULT_CHARSET_PARAMETER_VALUES) ? '&cs=' . $this->pageCharset : '') .

            // Attribution information, so that Goal conversions are attributed to the right referrer or campaign
            // Campaign name
            (!empty($this->attributionInfo[0]) ? '&_rcn=' . urlencode($this->attributionInfo[0]) : '') .
            // Campaign keyword
            (!empty($this->attributionInfo[1]) ? '&_rck=' . urlencode($this->attributionInfo[1]) : '') .
            // Timestamp at which the referrer was set
            (!empty($this->attributionInfo[2]) ? '&_refts=' . $this->attributionInfo[2] : '') .
            // Referrer URL
            (!empty($this->attributionInfo[3]) ? '&_ref=' . urlencode($this->attributionInfo[3]) : '') .

            // custom location info
            (!empty($this->country) ? '&country=' . urlencode($this->country) : '') .
            (!empty($this->region) ? '&region=' . urlencode($this->region) : '') .
            (!empty($this->city) ? '&city=' . urlencode($this->city) : '') .
            (!empty($this->lat) ? '&lat=' . urlencode($this->lat) : '') .
            (!empty($this->long) ? '&long=' . urlencode($this->long) : '') .

            // DEBUG
            $this->DEBUG_APPEND_URL;


        // Reset page level custom variables after this page view
        $this->pageCustomVar = array();
        $this->eventCustomVar = array();

        // force new visit only once, user must call again setForceNewVisit()
        $this->forcedNewVisit = false;

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
        if($this->configCookiesDisabled) {
            return false;
        }
        $name = $this->getCookieName($name);

        // Piwik cookie names use dots separators in piwik.js,
        // but PHP Replaces . with _ http://www.php.net/manual/en/language.variables.predefined.php#72571
        $name = str_replace('.', '_', $name);
        foreach ($_COOKIE as $cookieName => $cookieValue) {
            if (strpos($cookieName, $name) !== false) {
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
        if (!empty($_SERVER['PATH_INFO'])) {
            $url = $_SERVER['PATH_INFO'];
        } else if (!empty($_SERVER['REQUEST_URI'])) {
            if (($pos = strpos($_SERVER['REQUEST_URI'], '?')) !== false) {
                $url = substr($_SERVER['REQUEST_URI'], 0, $pos);
            } else {
                $url = $_SERVER['REQUEST_URI'];
            }
        }
        if (empty($url)) {
            $url = $_SERVER['SCRIPT_NAME'];
        }

        if ($url[0] !== '/') {
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
        if (isset($_SERVER['HTTPS'])
            && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true)
        ) {
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
        if (isset($_SERVER['HTTP_HOST'])) {
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
        if (isset($_SERVER['QUERY_STRING'])
            && !empty($_SERVER['QUERY_STRING'])
        ) {
            $url .= '?' . $_SERVER['QUERY_STRING'];
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

    /**
     * Sets the first party cookies as would the piwik.js
     * All cookies are supported: 'id' and 'ses' and 'ref' and 'cvar' cookies.
     */
    protected function setFirstPartyCookies()
    {
        if ($this->configCookiesDisabled) {
            return;
        }

        if (empty($this->cookieVisitorId)) {
            $this->loadVisitorIdCookie();
        }

        // Set the 'ref' cookie
        $attributionInfo = $this->getAttributionInfo();
        if(!empty($attributionInfo)) {
            $this->setCookie('ref', $attributionInfo, $this->configReferralCookieTimeout);
        }

        // Set the 'ses' cookie
        $this->setCookie('ses', '*', $this->configSessionCookieTimeout);

        // Set the 'id' cookie
        $visitCount = $this->visitCount + 1;
        $cookieValue = $this->getVisitorId() . '.' . $this->createTs . '.' . $visitCount . '.' . $this->currentTs . '.' . $this->lastVisitTs . '.' . $this->lastEcommerceOrderTs;
        $this->setCookie('id', $cookieValue, $this->configVisitorCookieTimeout);

        // Set the 'cvar' cookie
        $this->setCookie('cvar', json_encode($this->visitorCustomVar), $this->configSessionCookieTimeout);

    }

    /**
     * Sets a first party cookie to the client to improve dual JS-PHP tracking.
     *
     * This replicates the piwik.js tracker algorithms for consistency and better accuracy.
     *
     * @param $cookieName
     * @param $cookieValue
     * @param $cookieTTL
     */
    protected function setCookie($cookieName, $cookieValue, $cookieTTL)
    {
        $cookieExpire = $this->currentTs + $cookieTTL;
        if(!headers_sent()) {
            setcookie($this->getCookieName($cookieName), $cookieValue, $cookieExpire, $this->configCookiePath, $this->configCookieDomain);
        }
    }

    /**
     * @return bool|mixed
     */
    protected function getCustomVariablesFromCookie()
    {
        $cookie = $this->getCookieMatchingName('cvar');
        if (!$cookie) {
            return false;
        }
        return json_decode($cookie, $assoc = true);
    }
}

/**
 * Helper function to quickly generate the URL to track a page view.
 *
 * @param $idSite
 * @param string $documentTitle
 * @return string
 */
function Piwik_getUrlTrackPageView($idSite, $documentTitle = '')
{
    $tracker = new PiwikTracker($idSite);
    return $tracker->getUrlTrackPageView($documentTitle);
}

/**
 * Helper function to quickly generate the URL to track a goal.
 *
 * @param $idSite
 * @param $idGoal
 * @param float $revenue
 * @return string
 */
function Piwik_getUrlTrackGoal($idSite, $idGoal, $revenue = 0.0)
{
    $tracker = new PiwikTracker($idSite);
    return $tracker->getUrlTrackGoal($idGoal, $revenue);
}

