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
 * @subpackage Client
 * @version    $Id: Client.php 19309 2009-11-30 11:03:01Z bate $
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';


/**
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';


/**
 * @see Zend_Http_Client_Adapter_Interface
 */
require_once 'Zend/Http/Client/Adapter/Interface.php';


/**
 * @see Zend_Http_Response
 */
require_once 'Zend/Http/Response.php';

/**
 * @see Zend_Http_Response_Stream
 */
require_once 'Zend/Http/Response/Stream.php';

/**
 * Zend_Http_Client is an implemetation of an HTTP client in PHP. The client
 * supports basic features like sending different HTTP requests and handling
 * redirections, as well as more advanced features like proxy settings, HTTP
 * authentication and cookie persistance (using a Zend_Http_CookieJar object)
 *
 * @todo Implement proxy settings
 * @category   Zend
 * @package    Zend_Http
 * @subpackage Client
 * @throws     Zend_Http_Client_Exception
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_Client
{
    /**
     * HTTP request methods
     */
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const HEAD    = 'HEAD';
    const DELETE  = 'DELETE';
    const TRACE   = 'TRACE';
    const OPTIONS = 'OPTIONS';
    const CONNECT = 'CONNECT';

    /**
     * Supported HTTP Authentication methods
     */
    const AUTH_BASIC = 'basic';
    //const AUTH_DIGEST = 'digest'; <-- not implemented yet

    /**
     * HTTP protocol versions
     */
    const HTTP_1 = '1.1';
    const HTTP_0 = '1.0';

    /**
     * Content attributes
     */
    const CONTENT_TYPE   = 'Content-Type';
    const CONTENT_LENGTH = 'Content-Length';

    /**
     * POST data encoding methods
     */
    const ENC_URLENCODED = 'application/x-www-form-urlencoded';
    const ENC_FORMDATA   = 'multipart/form-data';

    /**
     * Configuration array, set using the constructor or using ::setConfig()
     *
     * @var array
     */
    protected $config = array(
        'maxredirects'    => 5,
        'strictredirects' => false,
        'useragent'       => 'Zend_Http_Client',
        'timeout'         => 10,
        'adapter'         => 'Zend_Http_Client_Adapter_Socket',
        'httpversion'     => self::HTTP_1,
        'keepalive'       => false,
        'storeresponse'   => true,
        'strict'          => true,
        'output_stream'	  => false,
    );

    /**
     * The adapter used to preform the actual connection to the server
     *
     * @var Zend_Http_Client_Adapter_Interface
     */
    protected $adapter = null;

    /**
     * Request URI
     *
     * @var Zend_Uri_Http
     */
    protected $uri = null;

    /**
     * Associative array of request headers
     *
     * @var array
     */
    protected $headers = array();

    /**
     * HTTP request method
     *
     * @var string
     */
    protected $method = self::GET;

    /**
     * Associative array of GET parameters
     *
     * @var array
     */
    protected $paramsGet = array();

    /**
     * Assiciative array of POST parameters
     *
     * @var array
     */
    protected $paramsPost = array();

    /**
     * Request body content type (for POST requests)
     *
     * @var string
     */
    protected $enctype = null;

    /**
     * The raw post data to send. Could be set by setRawData($data, $enctype).
     *
     * @var string
     */
    protected $raw_post_data = null;

    /**
     * HTTP Authentication settings
     *
     * Expected to be an associative array with this structure:
     * $this->auth = array('user' => 'username', 'password' => 'password', 'type' => 'basic')
     * Where 'type' should be one of the supported authentication types (see the AUTH_*
     * constants), for example 'basic' or 'digest'.
     *
     * If null, no authentication will be used.
     *
     * @var array|null
     */
    protected $auth;

    /**
     * File upload arrays (used in POST requests)
     *
     * An associative array, where each element is of the format:
     *   'name' => array('filename.txt', 'text/plain', 'This is the actual file contents')
     *
     * @var array
     */
    protected $files = array();

    /**
     * The client's cookie jar
     *
     * @var Zend_Http_CookieJar
     */
    protected $cookiejar = null;

    /**
     * The last HTTP request sent by the client, as string
     *
     * @var string
     */
    protected $last_request = null;

    /**
     * The last HTTP response received by the client
     *
     * @var Zend_Http_Response
     */
    protected $last_response = null;

    /**
     * Redirection counter
     *
     * @var int
     */
    protected $redirectCounter = 0;

    /**
     * Fileinfo magic database resource
     *
     * This varaiable is populated the first time _detectFileMimeType is called
     * and is then reused on every call to this method
     *
     * @var resource
     */
    static protected $_fileInfoDb = null;

    /**
     * Contructor method. Will create a new HTTP client. Accepts the target
     * URL and optionally configuration array.
     *
     * @param Zend_Uri_Http|string $uri
     * @param array $config Configuration key-value pairs.
     */
    public function __construct($uri = null, $config = null)
    {
        if ($uri !== null) {
            $this->setUri($uri);
        }
        if ($config !== null) {
            $this->setConfig($config);
        }
    }

    /**
     * Set the URI for the next request
     *
     * @param  Zend_Uri_Http|string $uri
     * @return Zend_Http_Client
     * @throws Zend_Http_Client_Exception
     */
    public function setUri($uri)
    {
        if (is_string($uri)) {
            $uri = Zend_Uri::factory($uri);
        }

        if (!$uri instanceof Zend_Uri_Http) {
            /** @see Zend_Http_Client_Exception */
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('Passed parameter is not a valid HTTP URI.');
        }

        // Set auth if username and password has been specified in the uri
        if ($uri->getUsername() && $uri->getPassword()) {
            $this->setAuth($uri->getUsername(), $uri->getPassword());
        }

        // We have no ports, set the defaults
        if (! $uri->getPort()) {
            $uri->setPort(($uri->getScheme() == 'https' ? 443 : 80));
        }

        $this->uri = $uri;

        return $this;
    }

    /**
     * Get the URI for the next request
     *
     * @param boolean $as_string If true, will return the URI as a string
     * @return Zend_Uri_Http|string
     */
    public function getUri($as_string = false)
    {
        if ($as_string && $this->uri instanceof Zend_Uri_Http) {
            return $this->uri->__toString();
        } else {
            return $this->uri;
        }
    }

    /**
     * Set configuration parameters for this HTTP client
     *
     * @param  Zend_Config | array $config
     * @return Zend_Http_Client
     * @throws Zend_Http_Client_Exception
     */
    public function setConfig($config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();

        } elseif (! is_array($config)) {
            /** @see Zend_Http_Client_Exception */
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('Array or Zend_Config object expected, got ' . gettype($config));
        }

        foreach ($config as $k => $v) {
            $this->config[strtolower($k)] = $v;
        }

        // Pass configuration options to the adapter if it exists
        if ($this->adapter instanceof Zend_Http_Client_Adapter_Interface) {
            $this->adapter->setConfig($config);
        }

        return $this;
    }

    /**
     * Set the next request's method
     *
     * Validated the passed method and sets it. If we have files set for
     * POST requests, and the new method is not POST, the files are silently
     * dropped.
     *
     * @param string $method
     * @return Zend_Http_Client
     * @throws Zend_Http_Client_Exception
     */
    public function setMethod($method = self::GET)
    {
        if (! preg_match('/^[^\x00-\x1f\x7f-\xff\(\)<>@,;:\\\\"\/\[\]\?={}\s]+$/', $method)) {
            /** @see Zend_Http_Client_Exception */
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception("'{$method}' is not a valid HTTP request method.");
        }

        if ($method == self::POST && $this->enctype === null) {
            $this->setEncType(self::ENC_URLENCODED);
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Set one or more request headers
     *
     * This function can be used in several ways to set the client's request
     * headers:
     * 1. By providing two parameters: $name as the header to set (eg. 'Host')
     *    and $value as it's value (eg. 'www.example.com').
     * 2. By providing a single header string as the only parameter
     *    eg. 'Host: www.example.com'
     * 3. By providing an array of headers as the first parameter
     *    eg. array('host' => 'www.example.com', 'x-foo: bar'). In This case
     *    the function will call itself recursively for each array item.
     *
     * @param string|array $name Header name, full header string ('Header: value')
     *     or an array of headers
     * @param mixed $value Header value or null
     * @return Zend_Http_Client
     * @throws Zend_Http_Client_Exception
     */
    public function setHeaders($name, $value = null)
    {
        // If we got an array, go recusive!
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                if (is_string($k)) {
                    $this->setHeaders($k, $v);
                } else {
                    $this->setHeaders($v, null);
                }
            }
        } else {
            // Check if $name needs to be split
            if ($value === null && (strpos($name, ':') > 0)) {
                list($name, $value) = explode(':', $name, 2);
            }

            // Make sure the name is valid if we are in strict mode
            if ($this->config['strict'] && (! preg_match('/^[a-zA-Z0-9-]+$/', $name))) {
                /** @see Zend_Http_Client_Exception */
                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception("{$name} is not a valid HTTP header name");
            }

            $normalized_name = strtolower($name);

            // If $value is null or false, unset the header
            if ($value === null || $value === false) {
                unset($this->headers[$normalized_name]);

            // Else, set the header
            } else {
                // Header names are stored lowercase internally.
                if (is_string($value)) {
                    $value = trim($value);
                }
                $this->headers[$normalized_name] = array($name, $value);
            }
        }

        return $this;
    }

    /**
     * Get the value of a specific header
     *
     * Note that if the header has more than one value, an array
     * will be returned.
     *
     * @param string $key
     * @return string|array|null The header value or null if it is not set
     */
    public function getHeader($key)
    {
        $key = strtolower($key);
        if (isset($this->headers[$key])) {
            return $this->headers[$key][1];
        } else {
            return null;
        }
    }

    /**
     * Set a GET parameter for the request. Wrapper around _setParameter
     *
     * @param string|array $name
     * @param string $value
     * @return Zend_Http_Client
     */
    public function setParameterGet($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v)
                $this->_setParameter('GET', $k, $v);
        } else {
            $this->_setParameter('GET', $name, $value);
        }

        return $this;
    }

    /**
     * Set a POST parameter for the request. Wrapper around _setParameter
     *
     * @param string|array $name
     * @param string $value
     * @return Zend_Http_Client
     */
    public function setParameterPost($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v)
                $this->_setParameter('POST', $k, $v);
        } else {
            $this->_setParameter('POST', $name, $value);
        }

        return $this;
    }

    /**
     * Set a GET or POST parameter - used by SetParameterGet and SetParameterPost
     *
     * @param string $type GET or POST
     * @param string $name
     * @param string $value
     * @return null
     */
    protected function _setParameter($type, $name, $value)
    {
        $parray = array();
        $type = strtolower($type);
        switch ($type) {
            case 'get':
                $parray = &$this->paramsGet;
                break;
            case 'post':
                $parray = &$this->paramsPost;
                break;
        }

        if ($value === null) {
            if (isset($parray[$name])) unset($parray[$name]);
        } else {
            $parray[$name] = $value;
        }
    }

    /**
     * Get the number of redirections done on the last request
     *
     * @return int
     */
    public function getRedirectionsCount()
    {
        return $this->redirectCounter;
    }

    /**
     * Set HTTP authentication parameters
     *
     * $type should be one of the supported types - see the self::AUTH_*
     * constants.
     *
     * To enable authentication:
     * <code>
     * $this->setAuth('shahar', 'secret', Zend_Http_Client::AUTH_BASIC);
     * </code>
     *
     * To disable authentication:
     * <code>
     * $this->setAuth(false);
     * </code>
     *
     * @see http://www.faqs.org/rfcs/rfc2617.html
     * @param string|false $user User name or false disable authentication
     * @param string $password Password
     * @param string $type Authentication type
     * @return Zend_Http_Client
     * @throws Zend_Http_Client_Exception
     */
    public function setAuth($user, $password = '', $type = self::AUTH_BASIC)
    {
        // If we got false or null, disable authentication
        if ($user === false || $user === null) {
            $this->auth = null;

            // Clear the auth information in the uri instance as well
            if ($this->uri instanceof Zend_Uri_Http) {
                $this->getUri()->setUsername('');
                $this->getUri()->setPassword('');
            }
        // Else, set up authentication
        } else {
            // Check we got a proper authentication type
            if (! defined('self::AUTH_' . strtoupper($type))) {
                /** @see Zend_Http_Client_Exception */
                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception("Invalid or not supported authentication type: '$type'");
            }

            $this->auth = array(
                'user' => (string) $user,
                'password' => (string) $password,
                'type' => $type
            );
        }

        return $this;
    }

    /**
     * Set the HTTP client's cookie jar.
     *
     * A cookie jar is an object that holds and maintains cookies across HTTP requests
     * and responses.
     *
     * @param Zend_Http_CookieJar|boolean $cookiejar Existing cookiejar object, true to create a new one, false to disable
     * @return Zend_Http_Client
     * @throws Zend_Http_Client_Exception
     */
    public function setCookieJar($cookiejar = true)
    {
        if (! class_exists('Zend_Http_CookieJar')) {
            require_once 'Zend/Http/CookieJar.php';
        }

        if ($cookiejar instanceof Zend_Http_CookieJar) {
            $this->cookiejar = $cookiejar;
        } elseif ($cookiejar === true) {
            $this->cookiejar = new Zend_Http_CookieJar();
        } elseif (! $cookiejar) {
            $this->cookiejar = null;
        } else {
            /** @see Zend_Http_Client_Exception */
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('Invalid parameter type passed as CookieJar');
        }

        return $this;
    }

    /**
     * Return the current cookie jar or null if none.
     *
     * @return Zend_Http_CookieJar|null
     */
    public function getCookieJar()
    {
        return $this->cookiejar;
    }

    /**
     * Add a cookie to the request. If the client has no Cookie Jar, the cookies
     * will be added directly to the headers array as "Cookie" headers.
     *
     * @param Zend_Http_Cookie|string $cookie
     * @param string|null $value If "cookie" is a string, this is the cookie value.
     * @return Zend_Http_Client
     * @throws Zend_Http_Client_Exception
     */
    public function setCookie($cookie, $value = null)
    {
        if (! class_exists('Zend_Http_Cookie')) {
            require_once 'Zend/Http/Cookie.php';
        }

        if (is_array($cookie)) {
            foreach ($cookie as $c => $v) {
                if (is_string($c)) {
                    $this->setCookie($c, $v);
                } else {
                    $this->setCookie($v);
                }
            }

            return $this;
        }

        if ($value !== null) {
            $value = urlencode($value);
        }

        if (isset($this->cookiejar)) {
            if ($cookie instanceof Zend_Http_Cookie) {
                $this->cookiejar->addCookie($cookie);
            } elseif (is_string($cookie) && $value !== null) {
                $cookie = Zend_Http_Cookie::fromString("{$cookie}={$value}", $this->uri);
                $this->cookiejar->addCookie($cookie);
            }
        } else {
            if ($cookie instanceof Zend_Http_Cookie) {
                $name = $cookie->getName();
                $value = $cookie->getValue();
                $cookie = $name;
            }

            if (preg_match("/[=,; \t\r\n\013\014]/", $cookie)) {
                /** @see Zend_Http_Client_Exception */
                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception("Cookie name cannot contain these characters: =,; \t\r\n\013\014 ({$cookie})");
            }

            $value = addslashes($value);

            if (! isset($this->headers['cookie'])) {
                $this->headers['cookie'] = array('Cookie', '');
            }
            $this->headers['cookie'][1] .= $cookie . '=' . $value . '; ';
        }

        return $this;
    }

    /**
     * Set a file to upload (using a POST request)
     *
     * Can be used in two ways:
     *
     * 1. $data is null (default): $filename is treated as the name if a local file which
     *    will be read and sent. Will try to guess the content type using mime_content_type().
     * 2. $data is set - $filename is sent as the file name, but $data is sent as the file
     *    contents and no file is read from the file system. In this case, you need to
     *    manually set the Content-Type ($ctype) or it will default to
     *    application/octet-stream.
     *
     * @param string $filename Name of file to upload, or name to save as
     * @param string $formname Name of form element to send as
     * @param string $data Data to send (if null, $filename is read and sent)
     * @param string $ctype Content type to use (if $data is set and $ctype is
     *     null, will be application/octet-stream)
     * @return Zend_Http_Client
     * @throws Zend_Http_Client_Exception
     */
    public function setFileUpload($filename, $formname, $data = null, $ctype = null)
    {
        if ($data === null) {
            if (($data = @file_get_contents($filename)) === false) {
                /** @see Zend_Http_Client_Exception */
                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception("Unable to read file '{$filename}' for upload");
            }

            if (! $ctype) {
                $ctype = $this->_detectFileMimeType($filename);
            }
        }

        // Force enctype to multipart/form-data
        $this->setEncType(self::ENC_FORMDATA);

        $this->files[] = array(
            'formname' => $formname,
            'filename' => basename($filename),
            'ctype'    => $ctype,
            'data'     => $data
        );

        return $this;
    }

    /**
     * Set the encoding type for POST data
     *
     * @param string $enctype
     * @return Zend_Http_Client
     */
    public function setEncType($enctype = self::ENC_URLENCODED)
    {
        $this->enctype = $enctype;

        return $this;
    }

    /**
     * Set the raw (already encoded) POST data.
     *
     * This function is here for two reasons:
     * 1. For advanced user who would like to set their own data, already encoded
     * 2. For backwards compatibilty: If someone uses the old post($data) method.
     *    this method will be used to set the encoded data.
     *
     * $data can also be stream (such as file) from which the data will be read.
     *
     * @param string|resource $data
     * @param string $enctype
     * @return Zend_Http_Client
     */
    public function setRawData($data, $enctype = null)
    {
        $this->raw_post_data = $data;
        $this->setEncType($enctype);
        if (is_resource($data)) {
            // We've got stream data
            $stat = @fstat($data);
            if($stat) {
                $this->setHeaders(self::CONTENT_LENGTH, $stat['size']);
            }
        }
        return $this;
    }

    /**
     * Clear all GET and POST parameters
     *
     * Should be used to reset the request parameters if the client is
     * used for several concurrent requests.
     *
     * clearAll parameter controls if we clean just parameters or also
     * headers and last_*
     *
     * @param bool $clearAll Should all data be cleared?
     * @return Zend_Http_Client
     */
    public function resetParameters($clearAll = false)
    {
        // Reset parameter data
        $this->paramsGet     = array();
        $this->paramsPost    = array();
        $this->files         = array();
        $this->raw_post_data = null;

        if($clearAll) {
            $this->headers = array();
            $this->last_request = null;
            $this->last_response = null;
        } else {
            // Clear outdated headers
            if (isset($this->headers[strtolower(self::CONTENT_TYPE)])) {
                unset($this->headers[strtolower(self::CONTENT_TYPE)]);
            }
            if (isset($this->headers[strtolower(self::CONTENT_LENGTH)])) {
                unset($this->headers[strtolower(self::CONTENT_LENGTH)]);
            }
        }

        return $this;
    }

    /**
     * Get the last HTTP request as string
     *
     * @return string
     */
    public function getLastRequest()
    {
        return $this->last_request;
    }

    /**
     * Get the last HTTP response received by this client
     *
     * If $config['storeresponse'] is set to false, or no response was
     * stored yet, will return null
     *
     * @return Zend_Http_Response or null if none
     */
    public function getLastResponse()
    {
        return $this->last_response;
    }

    /**
     * Load the connection adapter
     *
     * While this method is not called more than one for a client, it is
     * seperated from ->request() to preserve logic and readability
     *
     * @param Zend_Http_Client_Adapter_Interface|string $adapter
     * @return null
     * @throws Zend_Http_Client_Exception
     */
    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            if (!class_exists($adapter)) {
                try {
                    require_once 'Zend/Loader.php';
                    Zend_Loader::loadClass($adapter);
                } catch (Zend_Exception $e) {
                    /** @see Zend_Http_Client_Exception */
                    require_once 'Zend/Http/Client/Exception.php';
                    throw new Zend_Http_Client_Exception("Unable to load adapter '$adapter': {$e->getMessage()}");
                }
            }

            $adapter = new $adapter;
        }

        if (! $adapter instanceof Zend_Http_Client_Adapter_Interface) {
            /** @see Zend_Http_Client_Exception */
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('Passed adapter is not a HTTP connection adapter');
        }

        $this->adapter = $adapter;
        $config = $this->config;
        unset($config['adapter']);
        $this->adapter->setConfig($config);
    }

    /**
     * Load the connection adapter
     *
     * @return Zend_Http_Client_Adapter_Interface $adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set streaming for received data
     *
     * @param string|boolean $streamfile Stream file, true for temp file, false/null for no streaming
     * @return Zend_Http_Client
     */
    public function setStream($streamfile = true)
    {
        $this->setConfig(array("output_stream" => $streamfile));
        return $this;
    }

    /**
     * Get status of streaming for received data
     * @return boolean|string
     */
    public function getStream()
    {
        return $this->config["output_stream"];
    }

    /**
     * Create temporary stream
     *
     * @return resource
     */
    protected function _openTempStream()
    {
        $this->_stream_name = $this->config['output_stream'];
        if(!is_string($this->_stream_name)) {
            // If name is not given, create temp name
            $this->_stream_name = tempnam(isset($this->config['stream_tmp_dir'])?$this->config['stream_tmp_dir']:sys_get_temp_dir(),
                 'Zend_Http_Client');
        }

        $fp = fopen($this->_stream_name, "w+b");
        if(!$fp) {
                $this->close();
                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception("Could not open temp file $name");

        }
        return $fp;
    }

    /**
     * Send the HTTP request and return an HTTP response object
     *
     * @param string $method
     * @return Zend_Http_Response
     * @throws Zend_Http_Client_Exception
     */
    public function request($method = null)
    {
        if (! $this->uri instanceof Zend_Uri_Http) {
            /** @see Zend_Http_Client_Exception */
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('No valid URI has been passed to the client');
        }

        if ($method) {
            $this->setMethod($method);
        }
        $this->redirectCounter = 0;
        $response = null;

        // Make sure the adapter is loaded
        if ($this->adapter == null) {
            $this->setAdapter($this->config['adapter']);
        }

        // Send the first request. If redirected, continue.
        do {
            // Clone the URI and add the additional GET parameters to it
            $uri = clone $this->uri;
            if (! empty($this->paramsGet)) {
                $query = $uri->getQuery();
                   if (! empty($query)) {
                       $query .= '&';
                   }
                $query .= http_build_query($this->paramsGet, null, '&');

                $uri->setQuery($query);
            }

            $body = $this->_prepareBody();
            $headers = $this->_prepareHeaders();

            // check that adapter supports streaming before using it
            if(is_resource($body) && !($this->adapter instanceof Zend_Http_Client_Adapter_Stream)) {
                /** @see Zend_Http_Client_Exception */
                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception('Adapter does not support streaming');
            }

            // Open the connection, send the request and read the response
            $this->adapter->connect($uri->getHost(), $uri->getPort(),
                ($uri->getScheme() == 'https' ? true : false));

            if($this->config['output_stream']) {
                if($this->adapter instanceof Zend_Http_Client_Adapter_Stream) {
                    $stream = $this->_openTempStream();
                    $this->adapter->setOutputStream($stream);
                } else {
                    /** @see Zend_Http_Client_Exception */
                    require_once 'Zend/Http/Client/Exception.php';
                    throw new Zend_Http_Client_Exception('Adapter does not support streaming');
                }
            }

            $this->last_request = $this->adapter->write($this->method,
                $uri, $this->config['httpversion'], $headers, $body);

            $response = $this->adapter->read();
            if (! $response) {
                /** @see Zend_Http_Client_Exception */
                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception('Unable to read response, or response is empty');
            }

            if($this->config['output_stream']) {
                rewind($stream);
                // cleanup the adapter
                $this->adapter->setOutputStream(null);
                $response = Zend_Http_Response_Stream::fromStream($response, $stream);
                $response->setStreamName($this->_stream_name);
                if(!is_string($this->config['output_stream'])) {
                    // we used temp name, will need to clean up
                    $response->setCleanup(true);
                }
            } else {
                $response = Zend_Http_Response::fromString($response);
            }

            if ($this->config['storeresponse']) {
                $this->last_response = $response;
            }

            // Load cookies into cookie jar
            if (isset($this->cookiejar)) {
                $this->cookiejar->addCookiesFromResponse($response, $uri);
            }

            // If we got redirected, look for the Location header
            if ($response->isRedirect() && ($location = $response->getHeader('location'))) {

                // Check whether we send the exact same request again, or drop the parameters
                // and send a GET request
                if ($response->getStatus() == 303 ||
                   ((! $this->config['strictredirects']) && ($response->getStatus() == 302 ||
                       $response->getStatus() == 301))) {

                    $this->resetParameters();
                    $this->setMethod(self::GET);
                }

                // If we got a well formed absolute URI
                if (Zend_Uri_Http::check($location)) {
                    $this->setHeaders('host', null);
                    $this->setUri($location);

                } else {

                    // Split into path and query and set the query
                    if (strpos($location, '?') !== false) {
                        list($location, $query) = explode('?', $location, 2);
                    } else {
                        $query = '';
                    }
                    $this->uri->setQuery($query);

                    // Else, if we got just an absolute path, set it
                    if(strpos($location, '/') === 0) {
                        $this->uri->setPath($location);

                        // Else, assume we have a relative path
                    } else {
                        // Get the current path directory, removing any trailing slashes
                        $path = $this->uri->getPath();
                        $path = rtrim(substr($path, 0, strrpos($path, '/')), "/");
                        $this->uri->setPath($path . '/' . $location);
                    }
                }
                ++$this->redirectCounter;

            } else {
                // If we didn't get any location, stop redirecting
                break;
            }

        } while ($this->redirectCounter < $this->config['maxredirects']);

        return $response;
    }

    /**
     * Prepare the request headers
     *
     * @return array
     */
    protected function _prepareHeaders()
    {
        $headers = array();

        // Set the host header
        if (! isset($this->headers['host'])) {
            $host = $this->uri->getHost();

            // If the port is not default, add it
            if (! (($this->uri->getScheme() == 'http' && $this->uri->getPort() == 80) ||
                  ($this->uri->getScheme() == 'https' && $this->uri->getPort() == 443))) {
                $host .= ':' . $this->uri->getPort();
            }

            $headers[] = "Host: {$host}";
        }

        // Set the connection header
        if (! isset($this->headers['connection'])) {
            if (! $this->config['keepalive']) {
                $headers[] = "Connection: close";
            }
        }

        // Set the Accept-encoding header if not set - depending on whether
        // zlib is available or not.
        if (! isset($this->headers['accept-encoding'])) {
            if (function_exists('gzinflate')) {
                $headers[] = 'Accept-encoding: gzip, deflate';
            } else {
                $headers[] = 'Accept-encoding: identity';
            }
        }

        // Set the Content-Type header
        if ($this->method == self::POST &&
           (! isset($this->headers[strtolower(self::CONTENT_TYPE)]) && isset($this->enctype))) {

            $headers[] = self::CONTENT_TYPE . ': ' . $this->enctype;
        }

        // Set the user agent header
        if (! isset($this->headers['user-agent']) && isset($this->config['useragent'])) {
            $headers[] = "User-Agent: {$this->config['useragent']}";
        }

        // Set HTTP authentication if needed
        if (is_array($this->auth)) {
            $auth = self::encodeAuthHeader($this->auth['user'], $this->auth['password'], $this->auth['type']);
            $headers[] = "Authorization: {$auth}";
        }

        // Load cookies from cookie jar
        if (isset($this->cookiejar)) {
            $cookstr = $this->cookiejar->getMatchingCookies($this->uri,
                true, Zend_Http_CookieJar::COOKIE_STRING_CONCAT);

            if ($cookstr) {
                $headers[] = "Cookie: {$cookstr}";
            }
        }

        // Add all other user defined headers
        foreach ($this->headers as $header) {
            list($name, $value) = $header;
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $headers[] = "$name: $value";
        }

        return $headers;
    }

    /**
     * Prepare the request body (for POST and PUT requests)
     *
     * @return string
     * @throws Zend_Http_Client_Exception
     */
    protected function _prepareBody()
    {
        // According to RFC2616, a TRACE request should not have a body.
        if ($this->method == self::TRACE) {
            return '';
        }

        if (isset($this->raw_post_data) && is_resource($this->raw_post_data)) {
            return $this->raw_post_data;
        }
        // If mbstring overloads substr and strlen functions, we have to
        // override it's internal encoding
        if (function_exists('mb_internal_encoding') &&
           ((int) ini_get('mbstring.func_overload')) & 2) {

            $mbIntEnc = mb_internal_encoding();
            mb_internal_encoding('ASCII');
        }

        // If we have raw_post_data set, just use it as the body.
        if (isset($this->raw_post_data)) {
            $this->setHeaders(self::CONTENT_LENGTH, strlen($this->raw_post_data));
            if (isset($mbIntEnc)) {
                mb_internal_encoding($mbIntEnc);
            }

            return $this->raw_post_data;
        }

        $body = '';

        // If we have files to upload, force enctype to multipart/form-data
        if (count ($this->files) > 0) {
            $this->setEncType(self::ENC_FORMDATA);
        }

        // If we have POST parameters or files, encode and add them to the body
        if (count($this->paramsPost) > 0 || count($this->files) > 0) {
            switch($this->enctype) {
                case self::ENC_FORMDATA:
                    // Encode body as multipart/form-data
                    $boundary = '---ZENDHTTPCLIENT-' . md5(microtime());
                    $this->setHeaders(self::CONTENT_TYPE, self::ENC_FORMDATA . "; boundary={$boundary}");

                    // Get POST parameters and encode them
                    $params = self::_flattenParametersArray($this->paramsPost);
                    foreach ($params as $pp) {
                        $body .= self::encodeFormData($boundary, $pp[0], $pp[1]);
                    }

                    // Encode files
                    foreach ($this->files as $file) {
                        $fhead = array(self::CONTENT_TYPE => $file['ctype']);
                        $body .= self::encodeFormData($boundary, $file['formname'], $file['data'], $file['filename'], $fhead);
                    }

                    $body .= "--{$boundary}--\r\n";
                    break;

                case self::ENC_URLENCODED:
                    // Encode body as application/x-www-form-urlencoded
                    $this->setHeaders(self::CONTENT_TYPE, self::ENC_URLENCODED);
                    $body = http_build_query($this->paramsPost, '', '&');
                    break;

                default:
                    if (isset($mbIntEnc)) {
                        mb_internal_encoding($mbIntEnc);
                    }

                    /** @see Zend_Http_Client_Exception */
                    require_once 'Zend/Http/Client/Exception.php';
                    throw new Zend_Http_Client_Exception("Cannot handle content type '{$this->enctype}' automatically." .
                        " Please use Zend_Http_Client::setRawData to send this kind of content.");
                    break;
            }
        }

        // Set the Content-Length if we have a body or if request is POST/PUT
        if ($body || $this->method == self::POST || $this->method == self::PUT) {
            $this->setHeaders(self::CONTENT_LENGTH, strlen($body));
        }

        if (isset($mbIntEnc)) {
            mb_internal_encoding($mbIntEnc);
        }

        return $body;
    }

    /**
     * Helper method that gets a possibly multi-level parameters array (get or
     * post) and flattens it.
     *
     * The method returns an array of (key, value) pairs (because keys are not
     * necessarily unique. If one of the parameters in as array, it will also
     * add a [] suffix to the key.
     *
     * This method is deprecated since Zend Framework 1.9 in favour of
     * self::_flattenParametersArray() and will be dropped in 2.0
     *
     * @deprecated since 1.9
     *
     * @param  array $parray    The parameters array
     * @param  bool  $urlencode Whether to urlencode the name and value
     * @return array
     */
    protected function _getParametersRecursive($parray, $urlencode = false)
    {
        // Issue a deprecated notice
        trigger_error("The " .  __METHOD__ . " method is deprecated and will be dropped in 2.0.",
            E_USER_NOTICE);

        if (! is_array($parray)) {
            return $parray;
        }
        $parameters = array();

        foreach ($parray as $name => $value) {
            if ($urlencode) {
                $name = urlencode($name);
            }

            // If $value is an array, iterate over it
            if (is_array($value)) {
                $name .= ($urlencode ? '%5B%5D' : '[]');
                foreach ($value as $subval) {
                    if ($urlencode) {
                        $subval = urlencode($subval);
                    }
                    $parameters[] = array($name, $subval);
                }
            } else {
                if ($urlencode) {
                    $value = urlencode($value);
                }
                $parameters[] = array($name, $value);
            }
        }

        return $parameters;
    }

    /**
     * Attempt to detect the MIME type of a file using available extensions
     *
     * This method will try to detect the MIME type of a file. If the fileinfo
     * extension is available, it will be used. If not, the mime_magic
     * extension which is deprected but is still available in many PHP setups
     * will be tried.
     *
     * If neither extension is available, the default application/octet-stream
     * MIME type will be returned
     *
     * @param  string $file File path
     * @return string       MIME type
     */
    protected function _detectFileMimeType($file)
    {
        $type = null;

        // First try with fileinfo functions
        if (function_exists('finfo_open')) {
            if (self::$_fileInfoDb === null) {
                self::$_fileInfoDb = @finfo_open(FILEINFO_MIME);
            }

            if (self::$_fileInfoDb) {
                $type = finfo_file(self::$_fileInfoDb, $file);
            }

        } elseif (function_exists('mime_content_type')) {
            $type = mime_content_type($file);
        }

        // Fallback to the default application/octet-stream
        if (! $type) {
            $type = 'application/octet-stream';
        }

        return $type;
    }

    /**
     * Encode data to a multipart/form-data part suitable for a POST request.
     *
     * @param string $boundary
     * @param string $name
     * @param mixed $value
     * @param string $filename
     * @param array $headers Associative array of optional headers @example ("Content-Transfer-Encoding" => "binary")
     * @return string
     */
    public static function encodeFormData($boundary, $name, $value, $filename = null, $headers = array()) {
        $ret = "--{$boundary}\r\n" .
            'Content-Disposition: form-data; name="' . $name .'"';

        if ($filename) {
            $ret .= '; filename="' . $filename . '"';
        }
        $ret .= "\r\n";

        foreach ($headers as $hname => $hvalue) {
            $ret .= "{$hname}: {$hvalue}\r\n";
        }
        $ret .= "\r\n";

        $ret .= "{$value}\r\n";

        return $ret;
    }

    /**
     * Create a HTTP authentication "Authorization:" header according to the
     * specified user, password and authentication method.
     *
     * @see http://www.faqs.org/rfcs/rfc2617.html
     * @param string $user
     * @param string $password
     * @param string $type
     * @return string
     * @throws Zend_Http_Client_Exception
     */
    public static function encodeAuthHeader($user, $password, $type = self::AUTH_BASIC)
    {
        $authHeader = null;

        switch ($type) {
            case self::AUTH_BASIC:
                // In basic authentication, the user name cannot contain ":"
                if (strpos($user, ':') !== false) {
                    /** @see Zend_Http_Client_Exception */
                    require_once 'Zend/Http/Client/Exception.php';
                    throw new Zend_Http_Client_Exception("The user name cannot contain ':' in 'Basic' HTTP authentication");
                }

                $authHeader = 'Basic ' . base64_encode($user . ':' . $password);
                break;

            //case self::AUTH_DIGEST:
                /**
                 * @todo Implement digest authentication
                 */
            //    break;

            default:
                /** @see Zend_Http_Client_Exception */
                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception("Not a supported HTTP authentication type: '$type'");
        }

        return $authHeader;
    }

    /**
     * Convert an array of parameters into a flat array of (key, value) pairs
     *
     * Will flatten a potentially multi-dimentional array of parameters (such
     * as POST parameters) into a flat array of (key, value) paris. In case
     * of multi-dimentional arrays, square brackets ([]) will be added to the
     * key to indicate an array.
     *
     * @since  1.9
     *
     * @param  array  $parray
     * @param  string $prefix
     * @return array
     */
    static protected function _flattenParametersArray($parray, $prefix = null)
    {
        if (! is_array($parray)) {
            return $parray;
        }

        $parameters = array();

        foreach($parray as $name => $value) {

            // Calculate array key
            if ($prefix) {
                if (is_int($name)) {
                    $key = $prefix . '[]';
                } else {
                    $key = $prefix . "[$name]";
                }
            } else {
                $key = $name;
            }

            if (is_array($value)) {
                $parameters = array_merge($parameters, self::_flattenParametersArray($value, $key));

            } else {
                $parameters[] = array($key, $value);
            }
        }

        return $parameters;
    }

}
