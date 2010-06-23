<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage CookieJar
 * @version    $Id: CookieJar.php 20096 2010-01-06 02:05:09Z bkarwin $
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Uri
 */
// require_once "Zend/Uri.php";
/**
 * @see Zend_Http_Cookie
 */
// require_once "Zend/Http/Cookie.php";
/**
 * @see Zend_Http_Response
 */
// require_once "Zend/Http/Response.php";

/**
 * A Zend_Http_CookieJar object is designed to contain and maintain HTTP cookies, and should
 * be used along with Zend_Http_Client in order to manage cookies across HTTP requests and
 * responses.
 *
 * The class contains an array of Zend_Http_Cookie objects. Cookies can be added to the jar
 * automatically from a request or manually. Then, the jar can find and return the cookies
 * needed for a specific HTTP request.
 *
 * A special parameter can be passed to all methods of this class that return cookies: Cookies
 * can be returned either in their native form (as Zend_Http_Cookie objects) or as strings -
 * the later is suitable for sending as the value of the "Cookie" header in an HTTP request.
 * You can also choose, when returning more than one cookie, whether to get an array of strings
 * (by passing Zend_Http_CookieJar::COOKIE_STRING_ARRAY) or one unified string for all cookies
 * (by passing Zend_Http_CookieJar::COOKIE_STRING_CONCAT).
 *
 * @link       http://wp.netscape.com/newsref/std/cookie_spec.html for some specs.
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage CookieJar
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_CookieJar implements Countable, IteratorAggregate
{
    /**
     * Return cookie(s) as a Zend_Http_Cookie object
     *
     */
    const COOKIE_OBJECT = 0;

    /**
     * Return cookie(s) as a string (suitable for sending in an HTTP request)
     *
     */
    const COOKIE_STRING_ARRAY = 1;

    /**
     * Return all cookies as one long string (suitable for sending in an HTTP request)
     *
     */
    const COOKIE_STRING_CONCAT = 2;

    /**
     * Array storing cookies
     *
     * Cookies are stored according to domain and path:
     * $cookies
     *  + www.mydomain.com
     *    + /
     *      - cookie1
     *      - cookie2
     *    + /somepath
     *      - othercookie
     *  + www.otherdomain.net
     *    + /
     *      - alsocookie
     *
     * @var array
     */
    protected $cookies = array();

    /**
     * The Zend_Http_Cookie array
     *
     * @var array
     */
    protected $_rawCookies = array();

    /**
     * Construct a new CookieJar object
     *
     */
    public function __construct()
    { }

    /**
     * Add a cookie to the jar. Cookie should be passed either as a Zend_Http_Cookie object
     * or as a string - in which case an object is created from the string.
     *
     * @param Zend_Http_Cookie|string $cookie
     * @param Zend_Uri_Http|string    $ref_uri Optional reference URI (for domain, path, secure)
     */
    public function addCookie($cookie, $ref_uri = null)
    {
        if (is_string($cookie)) {
            $cookie = Zend_Http_Cookie::fromString($cookie, $ref_uri);
        }

        if ($cookie instanceof Zend_Http_Cookie) {
            $domain = $cookie->getDomain();
            $path = $cookie->getPath();
            if (! isset($this->cookies[$domain])) $this->cookies[$domain] = array();
            if (! isset($this->cookies[$domain][$path])) $this->cookies[$domain][$path] = array();
            $this->cookies[$domain][$path][$cookie->getName()] = $cookie;
            $this->_rawCookies[] = $cookie;
        } else {
            // require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception('Supplient argument is not a valid cookie string or object');
        }
    }

    /**
     * Parse an HTTP response, adding all the cookies set in that response
     * to the cookie jar.
     *
     * @param Zend_Http_Response $response
     * @param Zend_Uri_Http|string $ref_uri Requested URI
     */
    public function addCookiesFromResponse($response, $ref_uri)
    {
        if (! $response instanceof Zend_Http_Response) {
            // require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception('$response is expected to be a Response object, ' .
                gettype($response) . ' was passed');
        }

        $cookie_hdrs = $response->getHeader('Set-Cookie');

        if (is_array($cookie_hdrs)) {
            foreach ($cookie_hdrs as $cookie) {
                $this->addCookie($cookie, $ref_uri);
            }
        } elseif (is_string($cookie_hdrs)) {
            $this->addCookie($cookie_hdrs, $ref_uri);
        }
    }

    /**
     * Get all cookies in the cookie jar as an array
     *
     * @param int $ret_as Whether to return cookies as objects of Zend_Http_Cookie or as strings
     * @return array|string
     */
    public function getAllCookies($ret_as = self::COOKIE_OBJECT)
    {
        $cookies = $this->_flattenCookiesArray($this->cookies, $ret_as);
        return $cookies;
    }

    /**
     * Return an array of all cookies matching a specific request according to the request URI,
     * whether session cookies should be sent or not, and the time to consider as "now" when
     * checking cookie expiry time.
     *
     * @param string|Zend_Uri_Http $uri URI to check against (secure, domain, path)
     * @param boolean $matchSessionCookies Whether to send session cookies
     * @param int $ret_as Whether to return cookies as objects of Zend_Http_Cookie or as strings
     * @param int $now Override the current time when checking for expiry time
     * @return array|string
     */
    public function getMatchingCookies($uri, $matchSessionCookies = true,
        $ret_as = self::COOKIE_OBJECT, $now = null)
    {
        if (is_string($uri)) $uri = Zend_Uri::factory($uri);
        if (! $uri instanceof Zend_Uri_Http) {
            // require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception("Invalid URI string or object passed");
        }

        // First, reduce the array of cookies to only those matching domain and path
        $cookies = $this->_matchDomain($uri->getHost());
        $cookies = $this->_matchPath($cookies, $uri->getPath());
        $cookies = $this->_flattenCookiesArray($cookies, self::COOKIE_OBJECT);

        // Next, run Cookie->match on all cookies to check secure, time and session mathcing
        $ret = array();
        foreach ($cookies as $cookie)
            if ($cookie->match($uri, $matchSessionCookies, $now))
                $ret[] = $cookie;

        // Now, use self::_flattenCookiesArray again - only to convert to the return format ;)
        $ret = $this->_flattenCookiesArray($ret, $ret_as);

        return $ret;
    }

    /**
     * Get a specific cookie according to a URI and name
     *
     * @param Zend_Uri_Http|string $uri The uri (domain and path) to match
     * @param string $cookie_name The cookie's name
     * @param int $ret_as Whether to return cookies as objects of Zend_Http_Cookie or as strings
     * @return Zend_Http_Cookie|string
     */
    public function getCookie($uri, $cookie_name, $ret_as = self::COOKIE_OBJECT)
    {
        if (is_string($uri)) {
            $uri = Zend_Uri::factory($uri);
        }

        if (! $uri instanceof Zend_Uri_Http) {
            // require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception('Invalid URI specified');
        }

        // Get correct cookie path
        $path = $uri->getPath();
        $path = substr($path, 0, strrpos($path, '/'));
        if (! $path) $path = '/';

        if (isset($this->cookies[$uri->getHost()][$path][$cookie_name])) {
            $cookie = $this->cookies[$uri->getHost()][$path][$cookie_name];

            switch ($ret_as) {
                case self::COOKIE_OBJECT:
                    return $cookie;
                    break;

                case self::COOKIE_STRING_ARRAY:
                case self::COOKIE_STRING_CONCAT:
                    return $cookie->__toString();
                    break;

                default:
                    // require_once 'Zend/Http/Exception.php';
                    throw new Zend_Http_Exception("Invalid value passed for \$ret_as: {$ret_as}");
                    break;
            }
        } else {
            return false;
        }
    }

    /**
     * Helper function to recursivly flatten an array. Shoud be used when exporting the
     * cookies array (or parts of it)
     *
     * @param Zend_Http_Cookie|array $ptr
     * @param int $ret_as What value to return
     * @return array|string
     */
    protected function _flattenCookiesArray($ptr, $ret_as = self::COOKIE_OBJECT) {
        if (is_array($ptr)) {
            $ret = ($ret_as == self::COOKIE_STRING_CONCAT ? '' : array());
            foreach ($ptr as $item) {
                if ($ret_as == self::COOKIE_STRING_CONCAT) {
                    $ret .= $this->_flattenCookiesArray($item, $ret_as);
                } else {
                    $ret = array_merge($ret, $this->_flattenCookiesArray($item, $ret_as));
                }
            }
            return $ret;
        } elseif ($ptr instanceof Zend_Http_Cookie) {
            switch ($ret_as) {
                case self::COOKIE_STRING_ARRAY:
                    return array($ptr->__toString());
                    break;

                case self::COOKIE_STRING_CONCAT:
                    return $ptr->__toString();
                    break;

                case self::COOKIE_OBJECT:
                default:
                    return array($ptr);
                    break;
            }
        }

        return null;
    }

    /**
     * Return a subset of the cookies array matching a specific domain
     *
     * @param string $domain
     * @return array
     */
    protected function _matchDomain($domain)
    {
        $ret = array();

        foreach (array_keys($this->cookies) as $cdom) {
            if (Zend_Http_Cookie::matchCookieDomain($cdom, $domain)) {
                $ret[$cdom] = $this->cookies[$cdom];
            }
        }

        return $ret;
    }

    /**
     * Return a subset of a domain-matching cookies that also match a specified path
     *
     * @param array $dom_array
     * @param string $path
     * @return array
     */
    protected function _matchPath($domains, $path)
    {
        $ret = array();

        foreach ($domains as $dom => $paths_array) {
            foreach (array_keys($paths_array) as $cpath) {
                if (Zend_Http_Cookie::matchCookiePath($cpath, $path)) {
                    if (! isset($ret[$dom])) {
                        $ret[$dom] = array();
                    }

                    $ret[$dom][$cpath] = $paths_array[$cpath];
                }
            }
        }

        return $ret;
    }

    /**
     * Create a new CookieJar object and automatically load into it all the
     * cookies set in an Http_Response object. If $uri is set, it will be
     * considered as the requested URI for setting default domain and path
     * of the cookie.
     *
     * @param Zend_Http_Response $response HTTP Response object
     * @param Zend_Uri_Http|string $uri The requested URI
     * @return Zend_Http_CookieJar
     * @todo Add the $uri functionality.
     */
    public static function fromResponse(Zend_Http_Response $response, $ref_uri)
    {
        $jar = new self();
        $jar->addCookiesFromResponse($response, $ref_uri);
        return $jar;
    }

    /**
     * Required by Countable interface
     *
     * @return int
     */
    public function count()
    {
        return count($this->_rawCookies);
    }

    /**
     * Required by IteratorAggregate interface
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_rawCookies);
    }

    /**
     * Tells if the jar is empty of any cookie
     *
     * @return bool
     */
    public function isEmpty()
    {
        return count($this) == 0;
    }

    /**
     * Empties the cookieJar of any cookie
     *
     * @return Zend_Http_CookieJar
     */
    public function reset()
    {
        $this->cookies = $this->_rawCookies = array();
        return $this;
    }
}
