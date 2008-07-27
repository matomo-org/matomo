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
 * @subpackage Cookie
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com/)
 * @version    $Id: Cookie.php 9130 2008-04-04 08:30:24Z thomas $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Uri/Http.php';

/**
 * Zend_Http_Cookie is a class describing an HTTP cookie and all it's parameters.
 *
 * Zend_Http_Cookie is a class describing an HTTP cookie and all it's parameters. The
 * class also enables validating whether the cookie should be sent to the server in
 * a specified scenario according to the request URI, the expiry time and whether
 * session cookies should be used or not. Generally speaking cookies should be
 * contained in a Cookiejar object, or instantiated manually and added to an HTTP
 * request.
 *
 * See http://wp.netscape.com/newsref/std/cookie_spec.html for some specs.
 *
 * @category    Zend
 * @package     Zend_Http
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com/)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_Cookie
{
    /**
     * Cookie name
     *
     * @var string
     */
    protected $name;

    /**
     * Cookie value
     *
     * @var string
     */
    protected $value;

    /**
     * Cookie expiry date
     *
     * @var int
     */
    protected $expires;

    /**
     * Cookie domain
     *
     * @var string
     */
    protected $domain;

    /**
     * Cookie path
     *
     * @var string
     */
    protected $path;

    /**
     * Whether the cookie is secure or not
     *
     * @var boolean
     */
    protected $secure;

    /**
     * Cookie object constructor
     *
     * @todo Add validation of each one of the parameters (legal domain, etc.)
     *
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $domain
     * @param string $path
     * @param bool $secure
     */
    public function __construct($name, $value, $domain, $expires = null, $path = null, $secure = false)
    {
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception("Cookie name cannot contain these characters: =,; \\t\\r\\n\\013\\014 ({$name})");
        }

        if (! $this->name = (string) $name) {
            require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception('Cookies must have a name');
        }

        if (! $this->domain = (string) $domain) {
            require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception('Cookies must have a domain');
        }

        $this->value = (string) $value;
        $this->expires = ($expires === null ? null : (int) $expires);
        $this->path = ($path ? $path : '/');
        $this->secure = $secure;
    }

    /**
     * Get Cookie name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get cookie value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get cookie domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get the cookie path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the expiry time of the cookie, or null if no expiry time is set
     *
     * @return int|null
     */
    public function getExpiryTime()
    {
        return $this->expires;
    }

    /**
     * Check whether the cookie should only be sent over secure connections
     *
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Check whether the cookie has expired
     *
     * Always returns false if the cookie is a session cookie (has no expiry time)
     *
     * @param int $now Timestamp to consider as "now"
     * @return boolean
     */
    public function isExpired($now = null)
    {
        if ($now === null) $now = time();
        if (is_int($this->expires) && $this->expires < $now) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check whether the cookie is a session cookie (has no expiry time set)
     *
     * @return boolean
     */
    public function isSessionCookie()
    {
        return ($this->expires === null);
    }

    /**
     * Checks whether the cookie should be sent or not in a specific scenario
     *
     * @param string|Zend_Uri_Http $uri URI to check against (secure, domain, path)
     * @param boolean $matchSessionCookies Whether to send session cookies
     * @param int $now Override the current time when checking for expiry time
     * @return boolean
     */
    public function match($uri, $matchSessionCookies = true, $now = null)
    {
        if (is_string ($uri)) {
            $uri = Zend_Uri_Http::factory($uri);
        }

        // Make sure we have a valid Zend_Uri_Http object
        if (! ($uri->valid() && ($uri->getScheme() == 'http' || $uri->getScheme() =='https'))) {
            require_once 'Zend/Http/Exception.php';    
            throw new Zend_Http_Exception('Passed URI is not a valid HTTP or HTTPS URI');
        }

        // Check that the cookie is secure (if required) and not expired
        if ($this->secure && $uri->getScheme() != 'https') return false;
        if ($this->isExpired($now)) return false;
        if ($this->isSessionCookie() && ! $matchSessionCookies) return false;

        // Validate domain and path
        // Domain is validated using tail match, while path is validated using head match
        $domain_preg = preg_quote($this->getDomain(), "/");
        if (! preg_match("/{$domain_preg}$/", $uri->getHost())) return false;
        $path_preg = preg_quote($this->getPath(), "/");
        if (! preg_match("/^{$path_preg}/", $uri->getPath())) return false;

        // If we didn't die until now, return true.
        return true;
    }

    /**
     * Get the cookie as a string, suitable for sending as a "Cookie" header in an
     * HTTP request
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name . '=' . urlencode($this->value) . ';';
    }

    /**
     * Generate a new Cookie object from a cookie string
     * (for example the value of the Set-Cookie HTTP header)
     *
     * @param string $cookieStr
     * @param Zend_Uri_Http|string $ref_uri Reference URI for default values (domain, path)
     * @return Zend_Http_Cookie A new Zend_Http_Cookie object or false on failure.
     */
    public static function fromString($cookieStr, $ref_uri = null)
    {
        // Set default values
        if (is_string($ref_uri)) {
            $ref_uri = Zend_Uri_Http::factory($ref_uri);
        }

        $name    = '';
        $value   = '';
        $domain  = '';
        $path    = '';
        $expires = null;
        $secure  = false;
        $parts   = explode(';', $cookieStr);

        // If first part does not include '=', fail
        if (strpos($parts[0], '=') === false) return false;

        // Get the name and value of the cookie
        list($name, $value) = explode('=', trim(array_shift($parts)), 2);
        $name  = trim($name);
        $value = urldecode(trim($value));

        // Set default domain and path
        if ($ref_uri instanceof Zend_Uri_Http) {
            $domain = $ref_uri->getHost();
            $path = $ref_uri->getPath();
            $path = substr($path, 0, strrpos($path, '/'));
        }

        // Set other cookie parameters
        foreach ($parts as $part) {
            $part = trim($part);
            if (strtolower($part) == 'secure') {
                $secure = true;
                continue;
            }

            $keyValue = explode('=', $part, 2);
            if (count($keyValue) == 2) {
                list($k, $v) = $keyValue;
                switch (strtolower($k))    {
                    case 'expires':
                        $expires = strtotime($v);
                        break;
                    case 'path':
                        $path = $v;
                        break;
                    case 'domain':
                        $domain = $v;
                        break;
                    default:
                        break;
                }
            }
        }

        if ($name !== '') {
            return new Zend_Http_Cookie($name, $value, $domain, $expires, $path, $secure);
        } else {
            return false;
        }
    }
}
