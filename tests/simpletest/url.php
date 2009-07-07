<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id: url.php 1723 2008-04-08 00:34:10Z lastcraft $
 */

/**#@+
 *  include other SimpleTest class files
 */
require_once(dirname(__FILE__) . '/encoding.php');
/**#@-*/

/**
 *    URL parser to replace parse_url() PHP function which
 *    got broken in PHP 4.3.0. Adds some browser specific
 *    functionality such as expandomatics.
 *    Guesses a bit trying to separate the host from
 *    the path and tries to keep a raw, possibly unparsable,
 *    request string as long as possible.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleUrl {
    var $_scheme;
    var $_username;
    var $_password;
    var $_host;
    var $_port;
    var $_path;
    var $_request;
    var $_fragment;
    var $_x;
    var $_y;
    var $_target;
    var $_raw = false;
    
    /**
     *    Constructor. Parses URL into sections.
     *    @param string $url        Incoming URL.
     *    @access public
     */
    function SimpleUrl($url = '') {
        list($x, $y) = $this->_chompCoordinates($url);
        $this->setCoordinates($x, $y);
        $this->_scheme = $this->_chompScheme($url);
        list($this->_username, $this->_password) = $this->_chompLogin($url);
        $this->_host = $this->_chompHost($url);
        $this->_port = false;
        if (preg_match('/(.*?):(.*)/', $this->_host, $host_parts)) {
            $this->_host = $host_parts[1];
            $this->_port = (integer)$host_parts[2];
        }
        $this->_path = $this->_chompPath($url);
        $this->_request = $this->_parseRequest($this->_chompRequest($url));
        $this->_fragment = (strncmp($url, "#", 1) == 0 ? substr($url, 1) : false);
        $this->_target = false;
    }
    
    /**
     *    Extracts the X, Y coordinate pair from an image map.
     *    @param string $url   URL so far. The coordinates will be
     *                         removed.
     *    @return array        X, Y as a pair of integers.
     *    @access private
     */
    function _chompCoordinates(&$url) {
        if (preg_match('/(.*)\?(\d+),(\d+)$/', $url, $matches)) {
            $url = $matches[1];
            return array((integer)$matches[2], (integer)$matches[3]);
        }
        return array(false, false);
    }
    
    /**
     *    Extracts the scheme part of an incoming URL.
     *    @param string $url   URL so far. The scheme will be
     *                         removed.
     *    @return string       Scheme part or false.
     *    @access private
     */
    function _chompScheme(&$url) {
        if (preg_match('/^([^\/:]*):(\/\/)(.*)/', $url, $matches)) {
            $url = $matches[2] . $matches[3];
            return $matches[1];
        }
        return false;
    }
    
    /**
     *    Extracts the username and password from the
     *    incoming URL. The // prefix will be reattached
     *    to the URL after the doublet is extracted.
     *    @param string $url    URL so far. The username and
     *                          password are removed.
     *    @return array         Two item list of username and
     *                          password. Will urldecode() them.
     *    @access private
     */
    function _chompLogin(&$url) {
        $prefix = '';
        if (preg_match('/^(\/\/)(.*)/', $url, $matches)) {
            $prefix = $matches[1];
            $url = $matches[2];
        }
        if (preg_match('/^([^\/]*)@(.*)/', $url, $matches)) {
            $url = $prefix . $matches[2];
            $parts = explode(':', $matches[1]);
            return array(
                    urldecode($parts[0]),
                    isset($parts[1]) ? urldecode($parts[1]) : false);
        }
        $url = $prefix . $url;
        return array(false, false);
    }
    
    /**
     *    Extracts the host part of an incoming URL.
     *    Includes the port number part. Will extract
     *    the host if it starts with // or it has
     *    a top level domain or it has at least two
     *    dots.
     *    @param string $url    URL so far. The host will be
     *                          removed.
     *    @return string        Host part guess or false.
     *    @access private
     */
    function _chompHost(&$url) {
        if (preg_match('/^(\/\/)(.*?)(\/.*|\?.*|#.*|$)/', $url, $matches)) {
            $url = $matches[3];
            return $matches[2];
        }
        if (preg_match('/(.*?)(\.\.\/|\.\/|\/|\?|#|$)(.*)/', $url, $matches)) {
            $tlds = SimpleUrl::getAllTopLevelDomains();
            if (preg_match('/[a-z0-9\-]+\.(' . $tlds . ')/i', $matches[1])) {
                $url = $matches[2] . $matches[3];
                return $matches[1];
            } elseif (preg_match('/[a-z0-9\-]+\.[a-z0-9\-]+\.[a-z0-9\-]+/i', $matches[1])) {
                $url = $matches[2] . $matches[3];
                return $matches[1];
            }
        }
        return false;
    }
    
    /**
     *    Extracts the path information from the incoming
     *    URL. Strips this path from the URL.
     *    @param string $url     URL so far. The host will be
     *                           removed.
     *    @return string         Path part or '/'.
     *    @access private
     */
    function _chompPath(&$url) {
        if (preg_match('/(.*?)(\?|#|$)(.*)/', $url, $matches)) {
            $url = $matches[2] . $matches[3];
            return ($matches[1] ? $matches[1] : '');
        }
        return '';
    }
    
    /**
     *    Strips off the request data.
     *    @param string $url  URL so far. The request will be
     *                        removed.
     *    @return string      Raw request part.
     *    @access private
     */
    function _chompRequest(&$url) {
        if (preg_match('/\?(.*?)(#|$)(.*)/', $url, $matches)) {
            $url = $matches[2] . $matches[3];
            return $matches[1];
        }
        return '';
    }
        
    /**
     *    Breaks the request down into an object.
     *    @param string $raw           Raw request.
     *    @return SimpleFormEncoding    Parsed data.
     *    @access private
     */
    function _parseRequest($raw) {
        $this->_raw = $raw;
        $request = new SimpleGetEncoding();
        foreach (explode('&', $raw) as $pair) {
            if (preg_match('/(.*?)=(.*)/', $pair, $matches)) {
                $request->add($matches[1], urldecode($matches[2]));
            } elseif ($pair) {
                $request->add($pair, '');
            }
        }
        return $request;
    }
    
    /**
     *    Accessor for protocol part.
     *    @param string $default    Value to use if not present.
     *    @return string            Scheme name, e.g "http".
     *    @access public
     */
    function getScheme($default = false) {
        return $this->_scheme ? $this->_scheme : $default;
    }
    
    /**
     *    Accessor for user name.
     *    @return string    Username preceding host.
     *    @access public
     */
    function getUsername() {
        return $this->_username;
    }
    
    /**
     *    Accessor for password.
     *    @return string    Password preceding host.
     *    @access public
     */
    function getPassword() {
        return $this->_password;
    }
    
    /**
     *    Accessor for hostname and port.
     *    @param string $default    Value to use if not present.
     *    @return string            Hostname only.
     *    @access public
     */
    function getHost($default = false) {
        return $this->_host ? $this->_host : $default;
    }
    
    /**
     *    Accessor for top level domain.
     *    @return string       Last part of host.
     *    @access public
     */
    function getTld() {
        $path_parts = pathinfo($this->getHost());
        return (isset($path_parts['extension']) ? $path_parts['extension'] : false);
    }
    
    /**
     *    Accessor for port number.
     *    @return integer    TCP/IP port number.
     *    @access public
     */
    function getPort() {
        return $this->_port;
    }        
            
    /**
     *    Accessor for path.
     *    @return string    Full path including leading slash if implied.
     *    @access public
     */
    function getPath() {
        if (! $this->_path && $this->_host) {
            return '/';
        }
        return $this->_path;
    }
    
    /**
     *    Accessor for page if any. This may be a
     *    directory name if ambiguious.
     *    @return            Page name.
     *    @access public
     */
    function getPage() {
        if (! preg_match('/([^\/]*?)$/', $this->getPath(), $matches)) {
            return false;
        }
        return $matches[1];
    }
    
    /**
     *    Gets the path to the page.
     *    @return string       Path less the page.
     *    @access public
     */
    function getBasePath() {
        if (! preg_match('/(.*\/)[^\/]*?$/', $this->getPath(), $matches)) {
            return false;
        }
        return $matches[1];
    }
    
    /**
     *    Accessor for fragment at end of URL after the "#".
     *    @return string    Part after "#".
     *    @access public
     */
    function getFragment() {
        return $this->_fragment;
    }
    
    /**
     *    Sets image coordinates. Set to false to clear
     *    them.
     *    @param integer $x    Horizontal position.
     *    @param integer $y    Vertical position.
     *    @access public
     */
    function setCoordinates($x = false, $y = false) {
        if (($x === false) || ($y === false)) {
            $this->_x = $this->_y = false;
            return;
        }
        $this->_x = (integer)$x;
        $this->_y = (integer)$y;
    }
    
    /**
     *    Accessor for horizontal image coordinate.
     *    @return integer        X value.
     *    @access public
     */
    function getX() {
        return $this->_x;
    }
        
    /**
     *    Accessor for vertical image coordinate.
     *    @return integer        Y value.
     *    @access public
     */
    function getY() {
        return $this->_y;
    }
    
    /**
     *    Accessor for current request parameters
     *    in URL string form. Will return teh original request
     *    if at all possible even if it doesn't make much
     *    sense.
     *    @return string   Form is string "?a=1&b=2", etc.
     *    @access public
     */
    function getEncodedRequest() {
        if ($this->_raw) {
            $encoded = $this->_raw;
        } else {
            $encoded = $this->_request->asUrlRequest();
        }
        if ($encoded) {
            return '?' . preg_replace('/^\?/', '', $encoded);
        }
        return '';
    }
    
    /**
     *    Adds an additional parameter to the request.
     *    @param string $key            Name of parameter.
     *    @param string $value          Value as string.
     *    @access public
     */
    function addRequestParameter($key, $value) {
        $this->_raw = false;
        $this->_request->add($key, $value);
    }
    
    /**
     *    Adds additional parameters to the request.
     *    @param hash/SimpleFormEncoding $parameters   Additional
     *                                                parameters.
     *    @access public
     */
    function addRequestParameters($parameters) {
        $this->_raw = false;
        $this->_request->merge($parameters);
    }
    
    /**
     *    Clears down all parameters.
     *    @access public
     */
    function clearRequest() {
        $this->_raw = false;
        $this->_request = new SimpleGetEncoding();
    }
    
    /**
     *    Gets the frame target if present. Although
     *    not strictly part of the URL specification it
     *    acts as similarily to the browser.
     *    @return boolean/string    Frame name or false if none.
     *    @access public
     */
    function getTarget() {
        return $this->_target;
    }
    
    /**
     *    Attaches a frame target.
     *    @param string $frame        Name of frame.
     *    @access public
     */
    function setTarget($frame) {
        $this->_raw = false;
        $this->_target = $frame;
    }
    
    /**
     *    Renders the URL back into a string.
     *    @return string        URL in canonical form.
     *    @access public
     */
    function asString() {
        $path = $this->_path;
        $scheme = $identity = $host = $encoded = $fragment = '';
        if ($this->_username && $this->_password) {
            $identity = $this->_username . ':' . $this->_password . '@';
        }
        if ($this->getHost()) {
            $scheme = $this->getScheme() ? $this->getScheme() : 'http';
            $scheme .= "://";
            $host = $this->getHost();
        }
        if (substr($this->_path, 0, 1) == '/') {
            $path = $this->normalisePath($this->_path);
        }
        $encoded = $this->getEncodedRequest();
        $fragment = $this->getFragment() ? '#'. $this->getFragment() : '';
        $coords = $this->getX() === false ? '' : '?' . $this->getX() . ',' . $this->getY();
        return "$scheme$identity$host$path$encoded$fragment$coords";
    }
    
    /**
     *    Replaces unknown sections to turn a relative
     *    URL into an absolute one. The base URL can
     *    be either a string or a SimpleUrl object.
     *    @param string/SimpleUrl $base       Base URL.
     *    @access public
     */
    function makeAbsolute($base) {
        if (! is_object($base)) {
            $base = new SimpleUrl($base);
        }
        if ($this->getHost()) {
            $scheme = $this->getScheme();
            $host = $this->getHost();
            $port = $this->getPort() ? ':' . $this->getPort() : '';
            $identity = $this->getIdentity() ? $this->getIdentity() . '@' : '';
            if (! $identity) {
                $identity = $base->getIdentity() ? $base->getIdentity() . '@' : '';
            }
        } else {
            $scheme = $base->getScheme();
            $host = $base->getHost();
            $port = $base->getPort() ? ':' . $base->getPort() : '';
            $identity = $base->getIdentity() ? $base->getIdentity() . '@' : '';
        }
        $path = $this->normalisePath($this->_extractAbsolutePath($base));
        $encoded = $this->getEncodedRequest();
        $fragment = $this->getFragment() ? '#'. $this->getFragment() : '';
        $coords = $this->getX() === false ? '' : '?' . $this->getX() . ',' . $this->getY();
        return new SimpleUrl("$scheme://$identity$host$port$path$encoded$fragment$coords");
    }
    
    /**
     *    Replaces unknown sections of the path with base parts
     *    to return a complete absolute one.
     *    @param string/SimpleUrl $base       Base URL.
     *    @param string                       Absolute path.
     *    @access private
     */
    function _extractAbsolutePath($base) {
        if ($this->getHost()) {
            return $this->_path;
        }
        if (! $this->_isRelativePath($this->_path)) {
            return $this->_path;
        }
        if ($this->_path) {
            return $base->getBasePath() . $this->_path;
        }
        return $base->getPath();
    }
    
    /**
     *    Simple test to see if a path part is relative.
     *    @param string $path        Path to test.
     *    @return boolean            True if starts with a "/".
     *    @access private
     */
    function _isRelativePath($path) {
        return (substr($path, 0, 1) != '/');
    }
    
    /**
     *    Extracts the username and password for use in rendering
     *    a URL.
     *    @return string/boolean    Form of username:password or false.
     *    @access public
     */
    function getIdentity() {
        if ($this->_username && $this->_password) {
            return $this->_username . ':' . $this->_password;
        }
        return false;
    }
    
    /**
     *    Replaces . and .. sections of the path.
     *    @param string $path    Unoptimised path.
     *    @return string         Path with dots removed if possible.
     *    @access public
     */
    function normalisePath($path) {
        $path = preg_replace('|/\./|', '/', $path);
        return preg_replace('|/[^/]+/\.\./|', '/', $path);
    }
    
    /**
     *    A pipe seperated list of all TLDs that result in two part
     *    domain names.
     *    @return string        Pipe separated list.
     *    @access public
     *    @static
     */
    function getAllTopLevelDomains() {
        return 'com|edu|net|org|gov|mil|int|biz|info|name|pro|aero|coop|museum';
    }
}
?>
