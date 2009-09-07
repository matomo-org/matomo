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
 * @category  Zend
 * @package   Zend_Uri
 * @copyright Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Http.php 16208 2009-06-21 19:19:26Z thomas $
 */

/**
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';

/**
 * @see Zend_Validate_Hostname
 */
require_once 'Zend/Validate/Hostname.php';

/**
 * HTTP(S) URI handler
 *
 * @category  Zend
 * @package   Zend_Uri
 * @uses      Zend_Uri
 * @copyright Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Uri_Http extends Zend_Uri
{
    /**
     * Character classes for validation regular expressions
     */
    const CHAR_ALNUM    = 'A-Za-z0-9';
    const CHAR_MARK     = '-_.!~*\'()\[\]';
    const CHAR_RESERVED = ';\/?:@&=+$,';
    const CHAR_SEGMENT  = ':@&=+$,;';
    const CHAR_UNWISE   = '{}|\\\\^`';

    /**
     * HTTP username
     *
     * @var string
     */
    protected $_username = '';

    /**
     * HTTP password
     *
     * @var string
     */
    protected $_password = '';

    /**
     * HTTP host
     *
     * @var string
     */
    protected $_host = '';

    /**
     * HTTP post
     *
     * @var string
     */
    protected $_port = '';

    /**
     * HTTP part
     *
     * @var string
     */
    protected $_path = '';

    /**
     * HTTP query
     *
     * @var string
     */
    protected $_query = '';

    /**
     * HTTP fragment
     *
     * @var string
     */
    protected $_fragment = '';

    /**
     * Regular expression grammar rules for validation; values added by constructor
     *
     * @var array
     */
    protected $_regex = array();

    /**
     * Constructor accepts a string $scheme (e.g., http, https) and a scheme-specific part of the URI
     * (e.g., example.com/path/to/resource?query=param#fragment)
     *
     * @param  string $scheme         The scheme of the URI
     * @param  string $schemeSpecific The scheme-specific part of the URI
     * @throws Zend_Uri_Exception When the URI is not valid
     */
    protected function __construct($scheme, $schemeSpecific = '')
    {
        // Set the scheme
        $this->_scheme = $scheme;

        // Set up grammar rules for validation via regular expressions. These
        // are to be used with slash-delimited regular expression strings.

        // Escaped special characters (eg. '%25' for '%')
        $this->_regex['escaped']    = '%[[:xdigit:]]{2}';

        // Unreserved characters
        $this->_regex['unreserved'] = '[' . self::CHAR_ALNUM . self::CHAR_MARK . ']';

        // Segment can use escaped, unreserved or a set of additional chars
        $this->_regex['segment']    = '(?:' . $this->_regex['escaped'] . '|[' .
            self::CHAR_ALNUM . self::CHAR_MARK . self::CHAR_SEGMENT . '])*';

        // Path can be a series of segmets char strings seperated by '/'
        $this->_regex['path']       = '(?:\/(?:' . $this->_regex['segment'] . ')?)+';

        // URI characters can be escaped, alphanumeric, mark or reserved chars
        $this->_regex['uric']       = '(?:' . $this->_regex['escaped'] . '|[' .
            self::CHAR_ALNUM . self::CHAR_MARK . self::CHAR_RESERVED .

        // If unwise chars are allowed, add them to the URI chars class
            (self::$_config['allow_unwise'] ? self::CHAR_UNWISE : '') . '])';

        // If no scheme-specific part was supplied, the user intends to create
        // a new URI with this object.  No further parsing is required.
        if (strlen($schemeSpecific) === 0) {
            return;
        }

        // Parse the scheme-specific URI parts into the instance variables.
        $this->_parseUri($schemeSpecific);

        // Validate the URI
        if ($this->valid() === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Invalid URI supplied');
        }
    }

    /**
     * Creates a Zend_Uri_Http from the given string
     *
     * @param  string $uri String to create URI from, must start with
     *                     'http://' or 'https://'
     * @throws InvalidArgumentException  When the given $uri is not a string or
     *                                   does not start with http:// or https://
     * @throws Zend_Uri_Exception        When the given $uri is invalid
     * @return Zend_Uri_Http
     */
    public static function fromString($uri)
    {
        if (is_string($uri) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('$uri is not a string');
        }

        $uri            = explode(':', $uri, 2);
        $scheme         = strtolower($uri[0]);
        $schemeSpecific = isset($uri[1]) === true ? $uri[1] : '';

        if (in_array($scheme, array('http', 'https')) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Invalid scheme: '$scheme'");
        }

        $schemeHandler = new Zend_Uri_Http($scheme, $schemeSpecific);
        return $schemeHandler;
    }

    /**
     * Parse the scheme-specific portion of the URI and place its parts into instance variables.
     *
     * @param  string $schemeSpecific The scheme-specific portion to parse
     * @throws Zend_Uri_Exception When scheme-specific decoposition fails
     * @throws Zend_Uri_Exception When authority decomposition fails
     * @return void
     */
    protected function _parseUri($schemeSpecific)
    {
        // High-level decomposition parser
        $pattern = '~^((//)([^/?#]*))([^?#]*)(\?([^#]*))?(#(.*))?$~';
        $status  = @preg_match($pattern, $schemeSpecific, $matches);
        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: scheme-specific decomposition failed');
        }

        // Failed decomposition; no further processing needed
        if ($status === false) {
            return;
        }

        // Save URI components that need no further decomposition
        $this->_path     = isset($matches[4]) === true ? $matches[4] : '';
        $this->_query    = isset($matches[6]) === true ? $matches[6] : '';
        $this->_fragment = isset($matches[8]) === true ? $matches[8] : '';

        // Additional decomposition to get username, password, host, and port
        $combo   = isset($matches[3]) === true ? $matches[3] : '';
        $pattern = '~^(([^:@]*)(:([^@]*))?@)?([^:]+)(:(.*))?$~';
        $status  = @preg_match($pattern, $combo, $matches);
        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: authority decomposition failed');
        }

        // Failed decomposition; no further processing needed
        if ($status === false) {
            return;
        }

        // Save remaining URI components
        $this->_username = isset($matches[2]) === true ? $matches[2] : '';
        $this->_password = isset($matches[4]) === true ? $matches[4] : '';
        $this->_host     = isset($matches[5]) === true ? $matches[5] : '';
        $this->_port     = isset($matches[7]) === true ? $matches[7] : '';

    }

    /**
     * Returns a URI based on current values of the instance variables. If any
     * part of the URI does not pass validation, then an exception is thrown.
     *
     * @throws Zend_Uri_Exception When one or more parts of the URI are invalid
     * @return string
     */
    public function getUri()
    {
        if ($this->valid() === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('One or more parts of the URI are invalid');
        }

        $password = strlen($this->_password) > 0 ? ":$this->_password" : '';
        $auth     = strlen($this->_username) > 0 ? "$this->_username$password@" : '';
        $port     = strlen($this->_port) > 0 ? ":$this->_port" : '';
        $query    = strlen($this->_query) > 0 ? "?$this->_query" : '';
        $fragment = strlen($this->_fragment) > 0 ? "#$this->_fragment" : '';

        return $this->_scheme
             . '://'
             . $auth
             . $this->_host
             . $port
             . $this->_path
             . $query
             . $fragment;
    }

    /**
     * Validate the current URI from the instance variables. Returns true if and only if all
     * parts pass validation.
     *
     * @return boolean
     */
    public function valid()
    {
        // Return true if and only if all parts of the URI have passed validation
        return $this->validateUsername()
           and $this->validatePassword()
           and $this->validateHost()
           and $this->validatePort()
           and $this->validatePath()
           and $this->validateQuery()
           and $this->validateFragment();
    }

    /**
     * Returns the username portion of the URL, or FALSE if none.
     *
     * @return string
     */
    public function getUsername()
    {
        return strlen($this->_username) > 0 ? $this->_username : false;
    }

    /**
     * Returns true if and only if the username passes validation. If no username is passed,
     * then the username contained in the instance variable is used.
     *
     * @param  string $username The HTTP username
     * @throws Zend_Uri_Exception When username validation fails
     * @return boolean
     * @link   http://www.faqs.org/rfcs/rfc2396.html
     */
    public function validateUsername($username = null)
    {
        if ($username === null) {
            $username = $this->_username;
        }

        // If the username is empty, then it is considered valid
        if (strlen($username) === 0) {
            return true;
        }

        // Check the username against the allowed values
        $status = @preg_match('/^(?:' . $this->_regex['escaped'] . '|[' .
            self::CHAR_ALNUM . self::CHAR_MARK . ';:&=+$,' . '])+$/', $username);

        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: username validation failed');
        }

        return $status === 1;
    }

    /**
     * Sets the username for the current URI, and returns the old username
     *
     * @param  string $username The HTTP username
     * @throws Zend_Uri_Exception When $username is not a valid HTTP username
     * @return string
     */
    public function setUsername($username)
    {
        if ($this->validateUsername($username) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Username \"$username\" is not a valid HTTP username");
        }

        $oldUsername     = $this->_username;
        $this->_username = $username;

        return $oldUsername;
    }

    /**
     * Returns the password portion of the URL, or FALSE if none.
     *
     * @return string
     */
    public function getPassword()
    {
        return strlen($this->_password) > 0 ? $this->_password : false;
    }

    /**
     * Returns true if and only if the password passes validation. If no password is passed,
     * then the password contained in the instance variable is used.
     *
     * @param  string $password The HTTP password
     * @throws Zend_Uri_Exception When password validation fails
     * @return boolean
     * @link   http://www.faqs.org/rfcs/rfc2396.html
     */
    public function validatePassword($password = null)
    {
        if ($password === null) {
            $password = $this->_password;
        }

        // If the password is empty, then it is considered valid
        if (strlen($password) === 0) {
            return true;
        }

        // If the password is nonempty, but there is no username, then it is considered invalid
        if (strlen($password) > 0 and strlen($this->_username) === 0) {
            return false;
        }

        // Check the password against the allowed values
        $status = @preg_match('/^(?:' . $this->_regex['escaped'] . '|[' .
            self::CHAR_ALNUM . self::CHAR_MARK . ';:&=+$,' . '])+$/', $password);

        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: password validation failed.');
        }

        return $status == 1;
    }

    /**
     * Sets the password for the current URI, and returns the old password
     *
     * @param  string $password The HTTP password
     * @throws Zend_Uri_Exception When $password is not a valid HTTP password
     * @return string
     */
    public function setPassword($password)
    {
        if ($this->validatePassword($password) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Password \"$password\" is not a valid HTTP password.");
        }

        $oldPassword     = $this->_password;
        $this->_password = $password;

        return $oldPassword;
    }

    /**
     * Returns the domain or host IP portion of the URL, or FALSE if none.
     *
     * @return string
     */
    public function getHost()
    {
        return strlen($this->_host) > 0 ? $this->_host : false;
    }

    /**
     * Returns true if and only if the host string passes validation. If no host is passed,
     * then the host contained in the instance variable is used.
     *
     * @param  string $host The HTTP host
     * @return boolean
     * @uses   Zend_Filter
     */
    public function validateHost($host = null)
    {
        if ($host === null) {
            $host = $this->_host;
        }

        // If the host is empty, then it is considered invalid
        if (strlen($host) === 0) {
            return false;
        }

        // Check the host against the allowed values; delegated to Zend_Filter.
        $validate = new Zend_Validate_Hostname(Zend_Validate_Hostname::ALLOW_ALL);

        return $validate->isValid($host);
    }

    /**
     * Sets the host for the current URI, and returns the old host
     *
     * @param  string $host The HTTP host
     * @throws Zend_Uri_Exception When $host is nota valid HTTP host
     * @return string
     */
    public function setHost($host)
    {
        if ($this->validateHost($host) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Host \"$host\" is not a valid HTTP host");
        }

        $oldHost     = $this->_host;
        $this->_host = $host;

        return $oldHost;
    }

    /**
     * Returns the TCP port, or FALSE if none.
     *
     * @return string
     */
    public function getPort()
    {
        return strlen($this->_port) > 0 ? $this->_port : false;
    }

    /**
     * Returns true if and only if the TCP port string passes validation. If no port is passed,
     * then the port contained in the instance variable is used.
     *
     * @param  string $port The HTTP port
     * @return boolean
     */
    public function validatePort($port = null)
    {
        if ($port === null) {
            $port = $this->_port;
        }

        // If the port is empty, then it is considered valid
        if (strlen($port) === 0) {
            return true;
        }

        // Check the port against the allowed values
        return ctype_digit((string) $port) and 1 <= $port and $port <= 65535;
    }

    /**
     * Sets the port for the current URI, and returns the old port
     *
     * @param  string $port The HTTP port
     * @throws Zend_Uri_Exception When $port is not a valid HTTP port
     * @return string
     */
    public function setPort($port)
    {
        if ($this->validatePort($port) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Port \"$port\" is not a valid HTTP port.");
        }

        $oldPort     = $this->_port;
        $this->_port = $port;

        return $oldPort;
    }

    /**
     * Returns the path and filename portion of the URL, or FALSE if none.
     *
     * @return string
     */
    public function getPath()
    {
        return strlen($this->_path) > 0 ? $this->_path : '/';
    }

    /**
     * Returns true if and only if the path string passes validation. If no path is passed,
     * then the path contained in the instance variable is used.
     *
     * @param  string $path The HTTP path
     * @throws Zend_Uri_Exception When path validation fails
     * @return boolean
     */
    public function validatePath($path = null)
    {
        if ($path === null) {
            $path = $this->_path;
        }

        // If the path is empty, then it is considered valid
        if (strlen($path) === 0) {
            return true;
        }

        // Determine whether the path is well-formed
        $pattern = '/^' . $this->_regex['path'] . '$/';
        $status  = @preg_match($pattern, $path);
        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: path validation failed');
        }

        return (boolean) $status;
    }

    /**
     * Sets the path for the current URI, and returns the old path
     *
     * @param  string $path The HTTP path
     * @throws Zend_Uri_Exception When $path is not a valid HTTP path
     * @return string
     */
    public function setPath($path)
    {
        if ($this->validatePath($path) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Path \"$path\" is not a valid HTTP path");
        }

        $oldPath     = $this->_path;
        $this->_path = $path;

        return $oldPath;
    }

    /**
     * Returns the query portion of the URL (after ?), or FALSE if none.
     *
     * @return string
     */
    public function getQuery()
    {
        return strlen($this->_query) > 0 ? $this->_query : false;
    }

    /**
     * Returns true if and only if the query string passes validation. If no query is passed,
     * then the query string contained in the instance variable is used.
     *
     * @param  string $query The query to validate
     * @throws Zend_Uri_Exception When query validation fails
     * @return boolean
     * @link   http://www.faqs.org/rfcs/rfc2396.html
     */
    public function validateQuery($query = null)
    {
        if ($query === null) {
            $query = $this->_query;
        }

        // If query is empty, it is considered to be valid
        if (strlen($query) === 0) {
            return true;
        }

        // Determine whether the query is well-formed
        $pattern = '/^' . $this->_regex['uric'] . '*$/';
        $status  = @preg_match($pattern, $query);
        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: query validation failed');
        }

        return $status == 1;
    }

    /**
     * Set the query string for the current URI, and return the old query
     * string This method accepts both strings and arrays.
     *
     * @param  string|array $query The query string or array
     * @throws Zend_Uri_Exception When $query is not a valid query string
     * @return string              Old query string
     */
    public function setQuery($query)
    {
        $oldQuery = $this->_query;

        // If query is empty, set an empty string
        if (empty($query) === true) {
            $this->_query = '';
            return $oldQuery;
        }

        // If query is an array, make a string out of it
        if (is_array($query) === true) {
            $query = http_build_query($query, '', '&');
        } else {
            // If it is a string, make sure it is valid. If not parse and encode it
            $query = (string) $query;
            if ($this->validateQuery($query) === false) {
                parse_str($query, $queryArray);
                $query = http_build_query($queryArray, '', '&');
            }
        }

        // Make sure the query is valid, and set it
        if ($this->validateQuery($query) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("'$query' is not a valid query string");
        }

        $this->_query = $query;

        return $oldQuery;
    }

    /**
     * Returns the fragment portion of the URL (after #), or FALSE if none.
     *
     * @return string|false
     */
    public function getFragment()
    {
        return strlen($this->_fragment) > 0 ? $this->_fragment : false;
    }

    /**
     * Returns true if and only if the fragment passes validation. If no fragment is passed,
     * then the fragment contained in the instance variable is used.
     *
     * @param  string $fragment Fragment of an URI
     * @throws Zend_Uri_Exception When fragment validation fails
     * @return boolean
     * @link   http://www.faqs.org/rfcs/rfc2396.html
     */
    public function validateFragment($fragment = null)
    {
        if ($fragment === null) {
            $fragment = $this->_fragment;
        }

        // If fragment is empty, it is considered to be valid
        if (strlen($fragment) === 0) {
            return true;
        }

        // Determine whether the fragment is well-formed
        $pattern = '/^' . $this->_regex['uric'] . '*$/';
        $status  = @preg_match($pattern, $fragment);
        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: fragment validation failed');
        }

        return (boolean) $status;
    }

    /**
     * Sets the fragment for the current URI, and returns the old fragment
     *
     * @param  string $fragment Fragment of the current URI
     * @throws Zend_Uri_Exception When $fragment is not a valid HTTP fragment
     * @return string
     */
    public function setFragment($fragment)
    {
        if ($this->validateFragment($fragment) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Fragment \"$fragment\" is not a valid HTTP fragment");
        }

        $oldFragment     = $this->_fragment;
        $this->_fragment = $fragment;

        return $oldFragment;
    }
}
