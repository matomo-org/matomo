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
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Controller_Request_Exception */
require_once 'Zend/Controller/Request/Exception.php';

/** Zend_Controller_Request_Abstract */
require_once 'Zend/Controller/Request/Abstract.php';

/** Zend_Uri */ 
require_once 'Zend/Uri.php'; 

/**
 * Zend_Controller_Request_Http
 *
 * HTTP request object for use with Zend_Controller family.
 *
 * @uses Zend_Controller_Request_Abstract
 * @package Zend_Controller
 * @subpackage Request
 */
class Zend_Controller_Request_Http extends Zend_Controller_Request_Abstract
{
    /**
     * REQUEST_URI
     * @var string;
     */
    protected $_requestUri; 

    /**
     * Base URL of request
     * @var string
     */
    protected $_baseUrl = null; 

    /**
     * Base path of request
     * @var string
     */
    protected $_basePath = null; 

    /**
     * PATH_INFO
     * @var string
     */
    protected $_pathInfo = ''; 

    /**
     * Instance parameters
     * @var array
     */
    protected $_params = array(); 

    /**
     * Alias keys for request parameters
     * @var array
     */
    protected $_aliases = array(); 

    /**
     * Constructor
     *
     * If a $uri is passed, the object will attempt to populate itself using 
     * that information.
     * 
     * @param string|Zend_Uri $uri 
     * @return void
     * @throws Zend_Controller_Request_Exception when invalid URI passed
     */
    public function __construct($uri = null)
    {
        if (null !== $uri) {
            if (!$uri instanceof Zend_Uri) {
                $uri = Zend_Uri::factory($uri);
            } 
            if ($uri->valid()) {
                $path  = $uri->getPath();
                $query = $uri->getQuery();
                if (!empty($query)) {
                    $path .= '?' . $query;
                }

                $this->setRequestUri($path);
            } else {
                require_once 'Zend/Controller/Request/Exception.php';
                throw new Zend_Controller_Request_Exception('Invalid URI provided to constructor');
            }
        } else {
            $this->setRequestUri();
        }
    }
     
    /**
     * Access values contained in the superglobals as public members
     * Order of precedence: 1. GET, 2. POST, 3. COOKIE, 4. SERVER, 5. ENV
     * 
     * @see http://msdn.microsoft.com/en-us/library/system.web.httprequest.item.aspx
     * @param string $key
     * @return mixed
     */ 
    public function __get($key) 
    { 
        switch (true) {
            case isset($this->_params[$key]):
                return $this->_params[$key];
            case isset($_GET[$key]):
                return $_GET[$key];
            case isset($_POST[$key]):
                return $_POST[$key];
            case isset($_COOKIE[$key]):
                return $_COOKIE[$key];
            case ($key == 'REQUEST_URI'):
                return $this->getRequestUri();
            case ($key == 'PATH_INFO'):
                return $this->getPathInfo();
            case isset($_SERVER[$key]):
                return $_SERVER[$key];
            case isset($_ENV[$key]):
                return $_ENV[$key];
            default:
                return null;
        }
    } 

    /**
     * Alias to __get
     * 
     * @param string $key 
     * @return mixed
     */
    public function get($key)
    {
        return $this->__get($key);
    }

    /**
     * Set values
     *
     * In order to follow {@link __get()}, which operates on a number of 
     * superglobals, setting values through overloading is not allowed and will 
     * raise an exception. Use setParam() instead.
     * 
     * @param string $key 
     * @param mixed $value 
     * @return void
     * @throws Zend_Controller_Request_Exception
     */
    public function __set($key, $value)
    {
        require_once 'Zend/Controller/Request/Exception.php';
        throw new Zend_Controller_Request_Exception('Setting values in superglobals not allowed; please use setParam()');
    }

    /**
     * Alias to __set()
     * 
     * @param string $key 
     * @param mixed $value 
     * @return void
     */
    public function set($key, $value)
    {
        return $this->__set($key, $value);
    }

    /**
     * Check to see if a property is set
     * 
     * @param string $key 
     * @return boolean
     */
    public function __isset($key)
    {
        switch (true) {
            case isset($this->_params[$key]):
                return true;
            case isset($_GET[$key]):
                return true;
            case isset($_POST[$key]):
                return true;
            case isset($_COOKIE[$key]):
                return true;
            case isset($_SERVER[$key]):
                return true;
            case isset($_ENV[$key]):
                return true;
            default:
                return false;
        }
    }

    /**
     * Alias to __isset()
     * 
     * @param string $key 
     * @return boolean
     */
    public function has($key)
    {
        return $this->__isset($key);
    }
     
    /**
     * Retrieve a member of the $_GET superglobal
     * 
     * If no $key is passed, returns the entire $_GET array.
     * 
     * @todo How to retrieve from nested arrays
     * @param string $key 
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getQuery($key = null, $default = null) 
    { 
        if (null === $key) {
            return $_GET;
        }

        return (isset($_GET[$key])) ? $_GET[$key] : $default; 
    } 
     
    /**
     * Retrieve a member of the $_POST superglobal
     *
     * If no $key is passed, returns the entire $_POST array.
     * 
     * @todo How to retrieve from nested arrays
     * @param string $key 
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getPost($key = null, $default = null) 
    { 
        if (null === $key) {
            return $_POST;
        }

        return (isset($_POST[$key])) ? $_POST[$key] : $default; 
    } 
     
    /**
     * Retrieve a member of the $_COOKIE superglobal
     * 
     * If no $key is passed, returns the entire $_COOKIE array.
     * 
     * @todo How to retrieve from nested arrays
     * @param string $key 
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getCookie($key = null, $default = null) 
    { 
        if (null === $key) {
            return $_COOKIE;
        }

        return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default; 
    } 
     
    /**
     * Retrieve a member of the $_SERVER superglobal
     * 
     * If no $key is passed, returns the entire $_COOKIE array.
     * 
     * @param string $key 
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getServer($key = null, $default = null) 
    { 
        if (null === $key) {
            return $_SERVER;
        }

        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default; 
    } 
     
    /**
     * Retrieve a member of the $_ENV superglobal
     * 
     * If no $key is passed, returns the entire $_COOKIE array.
     * 
     * @param string $key 
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public function getEnv($key = null, $default = null) 
    { 
        if (null === $key) {
            return $_ENV;
        }

        return (isset($_ENV[$key])) ? $_ENV[$key] : $default; 
    } 
     
    /**
     * Set the REQUEST_URI on which the instance operates
     *
     * If no request URI is passed, uses the value in $_SERVER['REQUEST_URI'], 
     * $_SERVER['HTTP_X_REWRITE_URL'], or $_SERVER['ORIG_PATH_INFO'] + $_SERVER['QUERY_STRING'].
     * 
     * @param string $requestUri 
     * @return Zend_Controller_Request_Http
     */
    public function setRequestUri($requestUri = null) 
    { 
        if ($requestUri === null) { 
            if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // check this first so IIS will catch
                $requestUri = $_SERVER['HTTP_X_REWRITE_URL']; 
            } elseif (isset($_SERVER['REQUEST_URI'])) { 
                $requestUri = $_SERVER['REQUEST_URI']; 
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0, PHP as CGI
                $requestUri = $_SERVER['ORIG_PATH_INFO'];
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $requestUri .= '?' . $_SERVER['QUERY_STRING'];
                }
            } else { 
                return $this; 
            } 
        } elseif (!is_string($requestUri)) {
            return $this;
        } else {
            // Set GET items, if available
            $_GET = array();
            if (false !== ($pos = strpos($requestUri, '?'))) {
                // Get key => value pairs and set $_GET
                $query = substr($requestUri, $pos + 1);
                parse_str($query, $vars);
                $_GET = $vars;
            }
        }
         
        $this->_requestUri = $requestUri; 
        return $this;
    } 
     
    /**
     * Returns the REQUEST_URI taking into account
     * platform differences between Apache and IIS
     *
     * @return string
     */ 
    public function getRequestUri() 
    { 
        if (empty($this->_requestUri)) { 
            $this->setRequestUri(); 
        } 
         
        return $this->_requestUri; 
    } 
     
    /**
     * Set the base URL of the request; i.e., the segment leading to the script name
     *
     * E.g.:
     * - /admin
     * - /myapp
     * - /subdir/index.php
     *
     * Do not use the full URI when providing the base. The following are 
     * examples of what not to use:
     * - http://example.com/admin (should be just /admin)
     * - http://example.com/subdir/index.php (should be just /subdir/index.php)
     *
     * If no $baseUrl is provided, attempts to determine the base URL from the 
     * environment, using SCRIPT_FILENAME, SCRIPT_NAME, PHP_SELF, and 
     * ORIG_SCRIPT_NAME in its determination.
     * 
     * @param mixed $baseUrl 
     * @return Zend_Controller_Request_Http
     */
    public function setBaseUrl($baseUrl = null) 
    { 
        if ((null !== $baseUrl) && !is_string($baseUrl)) {
            return $this;
        }

        if ($baseUrl === null) { 
            $filename = basename($_SERVER['SCRIPT_FILENAME']); 
             
            if (basename($_SERVER['SCRIPT_NAME']) === $filename) { 
                $baseUrl = $_SERVER['SCRIPT_NAME']; 
            } elseif (basename($_SERVER['PHP_SELF']) === $filename) { 
                $baseUrl = $_SERVER['PHP_SELF']; 
            } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) { 
                $baseUrl = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility 
            } else { 
                // Backtrack up the script_filename to find the portion matching 
                // php_self
                $path    = $_SERVER['PHP_SELF'];
                $segs    = explode('/', trim($_SERVER['SCRIPT_FILENAME'], '/'));
                $segs    = array_reverse($segs);
                $index   = 0;
                $last    = count($segs);
                $baseUrl = '';
                do {
                    $seg     = $segs[$index];
                    $baseUrl = '/' . $seg . $baseUrl;
                    ++$index;
                } while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
            } 

            // Does the baseUrl have anything in common with the request_uri?
            $requestUri = $this->getRequestUri();

            if (0 === strpos($requestUri, $baseUrl)) {
                // full $baseUrl matches
                $this->_baseUrl = $baseUrl;
                return $this;
            }

            if (0 === strpos($requestUri, dirname($baseUrl))) {
                // directory portion of $baseUrl matches
                $this->_baseUrl = rtrim(dirname($baseUrl), '/');
                return $this;
            }

            if (!strpos($requestUri, basename($baseUrl))) {
                // no match whatsoever; set it blank
                $this->_baseUrl = '';
                return $this;
            }
             
            // If using mod_rewrite or ISAPI_Rewrite strip the script filename 
            // out of baseUrl. $pos !== 0 makes sure it is not matching a value 
            // from PATH_INFO or QUERY_STRING 
            if ((strlen($requestUri) >= strlen($baseUrl))
                && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0))) 
            { 
                $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
            } 
        } 
         
        $this->_baseUrl = rtrim($baseUrl, '/'); 
        return $this;
    } 
 
    /**
     * Everything in REQUEST_URI before PATH_INFO
     * <form action="<?=$baseUrl?>/news/submit" method="POST"/>
     *
     * @return string
     */ 
    public function getBaseUrl() 
    { 
        if (null === $this->_baseUrl) { 
            $this->setBaseUrl(); 
        } 
         
        return $this->_baseUrl; 
    } 
     
    /**
     * Set the base path for the URL
     * 
     * @param string|null $basePath 
     * @return Zend_Controller_Request_Http
     */
    public function setBasePath($basePath = null) 
    { 
        if ($basePath === null) { 
            $filename = basename($_SERVER['SCRIPT_FILENAME']); 
             
            $baseUrl = $this->getBaseUrl();
            if (empty($baseUrl)) {
                $this->_basePath = '';
                return $this; 
            } 
             
            if (basename($baseUrl) === $filename) { 
                $basePath = dirname($baseUrl); 
            } else { 
                $basePath = $baseUrl; 
            } 
        } 
             
        $this->_basePath = rtrim($basePath, '/'); 
        return $this;
    } 
     
    /**
     * Everything in REQUEST_URI before PATH_INFO not including the filename
     * <img src="<?=$basePath?>/images/zend.png"/>
     *
     * @return string
     */ 
    public function getBasePath() 
    { 
        if (null === $this->_basePath) { 
            $this->setBasePath(); 
        } 
         
        return $this->_basePath; 
    } 
     
    /**
     * Set the PATH_INFO string
     * 
     * @param string|null $pathInfo 
     * @return Zend_Controller_Request_Http
     */
    public function setPathInfo($pathInfo = null) 
    { 
        if ($pathInfo === null) { 
            $baseUrl = $this->getBaseUrl();
             
            if (null === ($requestUri = $this->getRequestUri())) { 
                return $this; 
            } 
             
            // Remove the query string from REQUEST_URI 
            if ($pos = strpos($requestUri, '?')) { 
                $requestUri = substr($requestUri, 0, $pos); 
            } 
             
            if ((null !== $baseUrl)
                && (false === ($pathInfo = substr($requestUri, strlen($baseUrl))))) 
            { 
                // If substr() returns false then PATH_INFO is set to an empty string 
                $pathInfo = ''; 
            } elseif (null === $baseUrl) {
                $pathInfo = $requestUri;
            }
        } 
         
        $this->_pathInfo = (string) $pathInfo; 
        return $this;
    } 
 
    /**
     * Returns everything between the BaseUrl and QueryString.
     * This value is calculated instead of reading PATH_INFO
     * directly from $_SERVER due to cross-platform differences.
     *
     * @return string
     */ 
    public function getPathInfo() 
    { 
        if (empty($this->_pathInfo)) { 
            $this->setPathInfo(); 
        } 
         
        return $this->_pathInfo; 
    } 
     
    /**
     * Set a userland parameter
     *
     * Uses $key to set a userland parameter. If $key is an alias, the actual 
     * key will be retrieved and used to set the parameter.
     * 
     * @param mixed $key 
     * @param mixed $value 
     * @return Zend_Controller_Request_Http
     */
    public function setParam($key, $value) 
    { 
        $keyName = (null !== ($alias = $this->getAlias($key))) ? $alias : $key; 

        parent::setParam($key, $value);
        return $this;
    } 
     
    /**
     * Retrieve a parameter
     *
     * Retrieves a parameter from the instance. Priority is in the order of 
     * userland parameters (see {@link setParam()}), $_GET, $_POST. If a 
     * parameter matching the $key is not found, null is returned.
     *
     * If the $key is an alias, the actual key aliased will be used.
     * 
     * @param mixed $key 
     * @param mixed $default Default value to use if key not found
     * @return mixed
     */
    public function getParam($key, $default = null) 
    { 
        $keyName = (null !== ($alias = $this->getAlias($key))) ? $alias : $key; 
         
        if (isset($this->_params[$keyName])) { 
            return $this->_params[$keyName]; 
        } elseif ((isset($_GET[$keyName]))) { 
            return $_GET[$keyName]; 
        } elseif ((isset($_POST[$keyName]))) { 
            return $_POST[$keyName]; 
        } 
         
        return $default; 
    } 
     
    /**
     * Retrieve an array of parameters
     *
     * Retrieves a merged array of parameters, with precedence of userland 
     * params (see {@link setParam()}), $_GET, $POST (i.e., values in the 
     * userland params will take precedence over all others).
     * 
     * @return array
     */
    public function getParams() 
    { 
        $return = $this->_params;
        if (isset($_GET) && is_array($_GET)) {
            $return += $_GET;
        }
        if (isset($_POST) && is_array($_POST)) {
            $return += $_POST;
        }
        return $return; 
    } 
     
    /**
     * Set parameters
     * 
     * Set one or more parameters. Parameters are set as userland parameters, 
     * using the keys specified in the array.
     * 
     * @param array $params 
     * @return Zend_Controller_Request_Http
     */
    public function setParams(array $params) 
    { 
        foreach ($params as $key => $value) { 
            $this->setParam($key, $value); 
        } 
        return $this;
    } 
     
    /**
     * Set a key alias
     *
     * Set an alias used for key lookups. $name specifies the alias, $target 
     * specifies the actual key to use.
     * 
     * @param string $name 
     * @param string $target 
     * @return Zend_Controller_Request_Http
     */
    public function setAlias($name, $target) 
    { 
        $this->_aliases[$name] = $target; 
        return $this;
    } 
     
    /**
     * Retrieve an alias
     *
     * Retrieve the actual key represented by the alias $name.
     * 
     * @param string $name 
     * @return string|null Returns null when no alias exists
     */
    public function getAlias($name) 
    { 
        if (isset($this->_aliases[$name])) { 
            return $this->_aliases[$name]; 
        } 
         
        return null; 
    } 
     
    /**
     * Retrieve the list of all aliases
     * 
     * @return array
     */
    public function getAliases() 
    { 
        return $this->_aliases; 
    } 

    /**
     * Return the method by which the request was made
     * 
     * @return string
     */
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }

    /**
     * Was the request made by POST?
     * 
     * @return boolean
     */
    public function isPost()
    {
        if ('POST' == $this->getMethod()) {
            return true;
        }

        return false;
    }

    /**
     * Return the value of the given HTTP header. Pass the header name as the 
     * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the 
     * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
     *
     * @param string HTTP header name
     * @return string|false HTTP header value, or false if not found
     * @throws Zend_Controller_Request_Exception
     */
    public function getHeader($header)
    {
        if (empty($header)) {
            require_once 'Zend/Controller/Request/Exception.php';
            throw new Zend_Controller_Request_Exception('An HTTP header name is required');
        }

        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        // This seems to be the only way to get the Authorization header on 
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }

        return false;
    }

    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * Should work with Prototype/Script.aculo.us, possibly others.
     * 
     * @return boolean
     */
    public function isXmlHttpRequest()
    {
        return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
    }
}
