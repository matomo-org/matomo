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

/** Zend_Controller_Action_Exception */
require_once 'Zend/Controller/Action/Exception.php';

/** Zend_Controller_Action_Helper_Abstract */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Action_Helper_Redirector extends Zend_Controller_Action_Helper_Abstract 
{
    /**
     * HTTP status code for redirects
     * @var int
     */
    protected $_code = 302;

    /**
     * Whether or not calls to _redirect() should exit script execution
     * @var bool
     */
    protected $_exit = true;

    /**
     * Whether or not _redirect() should attempt to prepend the base URL to the 
     * passed URL (if it's a relative URL)
     * @var bool
     */
    protected $_prependBase = true;
    
    /**
     * Url to which to redirect
     * @var string
     */
    protected $_redirectUrl = null;

    /**
     * Whether or not to use an absolute URI when redirecting
     * @var bool
     */
    protected $_useAbsoluteUri = false;
    
    /**
     * Retrieve HTTP status code to emit on {@link _redirect()} call
     * 
     * @return int
     */
    public function getCode()
    {
        return $this->_code;
    }
    
    /**
     * Validate HTTP status redirect code
     * 
     * @param  int $code 
     * @return true
     * @throws Zend_Controller_Action_Exception on invalid HTTP status code
     */
    protected function _checkCode($code)
    {
        if (!is_int($code) || (300 > $code) || (307 < $code)) {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Action_Exception('Invalid redirect HTTP status code (' . $code  . ')');
        }

        return true;
    }

    /**
     * Retrieve HTTP status code for {@link _redirect()} behaviour
     * 
     * @param  int $code 
     * @return Zend_Controller_Action_Helper_Redirector
     */
    public function setCode($code)
    {
        $this->_checkCode($code);
        $this->_code = $code;
        return $this;
    }

    /**
     * Retrieve flag for whether or not {@link _redirect()} will exit when finished.
     * 
     * @return bool
     */
    public function getExit()
    {
        return $this->_exit;
    }

    /**
     * Retrieve exit flag for {@link _redirect()} behaviour
     * 
     * @param  bool $flag 
     * @return Zend_Controller_Action_Helper_Redirector
     */
    public function setExit($flag)
    {
        $this->_exit = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve flag for whether or not {@link _redirect()} will prepend the 
     * base URL on relative URLs
     * 
     * @return bool
     */
    public function getPrependBase()
    {
        return $this->_prependBase;
    }

    /**
     * Retrieve 'prepend base' flag for {@link _redirect()} behaviour
     * 
     * @param  bool $flag 
     * @return Zend_Controller_Action_Helper_Redirector
     */
    public function setPrependBase($flag)
    {
        $this->_prependBase = ($flag) ? true : false;
        return $this;
    }
    
    /**
     * Return use absolute URI flag
     * 
     * @return boolean
     */
    public function getUseAbsoluteUri()
    {
        return $this->_useAbsoluteUri;
    }

    /**
     * Set use absolute URI flag
     * 
     * @param  bool $flag 
     * @return Zend_Controller_Action_Helper_Redirector
     */
    public function setUseAbsoluteUri($flag = true)
    {
        $this->_useAbsoluteUri = ($flag) ? true : false;
        return $this;
    }
    
    /**
     * Set redirect in response object
     * 
     * @return void
     */
    protected function _redirect($url)
    {
        $this->_redirectUrl = $url;
        if ($this->getUseAbsoluteUri() && !preg_match('#^(https?|ftp)://#', $url)) {
            $host  = $_SERVER['HTTP_HOST'];
            $proto = (empty($_SERVER['HTTPS'])) ? 'http' : 'https';
            $port  = $_SERVER['SERVER_PORT'];
            $uri   = $proto . '://' . $host;
            if ((('http' == $proto) && (80 != $port)) || (('https' == $proto) && (443 != $port))) {
                $uri .= ':' . $port;
            }
            $url = $uri . '/' . ltrim($url, '/');
        }
        $this->getResponse()->setRedirect($url, $this->getCode());
    }

    /**
     * Retrieve currently set URL for redirect
     * 
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_redirectUrl;
    }

    /**
     * Determine if the baseUrl should be prepended, and prepend if necessary
     * 
     * @param  string $url 
     * @return string
     */
    protected function _prependBase($url)
    {
        if ($this->getPrependBase()) {
            $request = $this->getRequest();
            if ($request instanceof Zend_Controller_Request_Http) {
                $base = rtrim($request->getBaseUrl(), '/');
                if (!empty($base) && ('/' != $base)) {
                    $url = $base . '/' . ltrim($url, '/');
                }
            }
        }

        return $url;
    }
    
    /**
     * Set a redirect URL of the form /module/controller/action/params
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array $params
     * @return void
     */
    public function setGoto($action, $controller = null, $module = null, array $params = array())
    {
        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();
        $request    = $this->getRequest();

        if (null === $module) {
            $module = $request->getModuleName();
            if ($module == $dispatcher->getDefaultModule()) {
                $module = '';
            }
        }

        if (null === $controller) {
            $controller = $request->getControllerName();
            if (empty($controller)) {
                $controller = $dispatcher->getDefaultControllerName();
            }
        }

        $paramsNormalized = array();
        foreach ($params as $key => $value) {
            $paramsNormalized[] = $key . '/' . $value;
        }
        $paramsString = implode('/', $paramsNormalized);

        $url = $module . '/' . $controller . '/' . $action . '/' . $paramsString;
        $url = '/' . trim($url, '/');

        $url = $this->_prependBase($url);

        $this->_redirect($url);
    }
    
    /**
     * Build a URL based on a route
     * 
     * @param  array $urlOptions 
     * @param  string $name Route name
     * @param  boolean $reset 
     * @return void
     */
    public function setGotoRoute(array $urlOptions = array(), $name = null, $reset = false)
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        if (empty($name)) {
            try {
                $name = $router->getCurrentRouteName();
            } catch (Zend_Controller_Router_Exception $e) {
                if ($router->hasRoute('default')) {
                    $name = 'default';
                }
            }
        } 

        $route   = $router->getRoute($name);
        $request = $this->getRequest();
        
        $url  = rtrim($request->getBaseUrl(), '/') . '/';
        $url .= $route->assemble($urlOptions, $reset);

        $this->_redirect($url);
    }

    /**
     * Set a redirect URL string
     * 
     * By default, emits a 302 HTTP status header, prepends base URL as defined 
     * in request object if url is relative, and halts script execution by 
     * calling exit().
     *
     * $options is an optional associative array that can be used to control 
     * redirect behaviour. The available option keys are:
     * - exit: boolean flag indicating whether or not to halt script execution when done
     * - prependBase: boolean flag indicating whether or not to prepend the base URL when a relative URL is provided
     * - code: integer HTTP status code to use with redirect. Should be between 300 and 307.
     *
     * _redirect() sets the Location header in the response object. If you set 
     * the exit flag to false, you can override this header later in code 
     * execution.
     *
     * If the exit flag is true (true by default), _redirect() will write and 
     * close the current session, if any.
     *
     * @param  string $url 
     * @param  array $options
     * @return void
     */
    public function setGotoUrl($url, array $options = array())
    {
        // prevent header injections
        $url = str_replace(array("\n", "\r"), '', $url);

        $exit        = $this->getExit();
        $prependBase = $this->getPrependBase();
        $code        = $this->getCode();
        if (null !== $options) {
            if (isset($options['exit'])) {
                $this->setExit(($options['exit']) ? true : false);
            }
            if (isset($options['prependBase'])) {
                $this->setPrependBase(($options['prependBase']) ? true : false);
            }
            if (isset($options['code'])) {
                $this->setCode($options['code']);
            }
        }

        // If relative URL, decide if we should prepend base URL
        if (!preg_match('|^[a-z]+://|', $url)) {
            $url = $this->_prependBase($url);
        }

        $this->_redirect($url);
    }
    
    /**
     * Perform a redirect to an action/controller/module with params
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array $params
     * @return void
     */
    public function goto($action, $controller = null, $module = null, array $params = array())
    {
        $this->setGoto($action, $controller, $module, $params);
        
        if ($this->getExit()) {
            $this->redirectAndExit();
        }
    }
    
    /**
     * Perform a redirect to an action/controller/module with params, forcing an immdiate exit
     * 
     * @param mixed $action 
     * @param mixed $controller 
     * @param mixed $module 
     * @param array $params 
     * @return void
     */
    public function gotoAndExit($action, $controller = null, $module = null, array $params = array())
    {
        $this->setGoto($action, $controller, $module, $params);
        $this->redirectAndExit();
    }

    /**
     * Redirect to a route-based URL
     *
     * Uses route's assemble method tobuild the URL; route is specified by $name; 
     * default route is used if none provided.
     * 
     * @param  array $urlOptions Array of key/value pairs used to assemble URL
     * @param  string $name 
     * @param  boolean $reset 
     * @return void
     */
    public function gotoRoute(array $urlOptions = array(), $name = null, $reset = false)
    {
        $this->setGotoRoute($urlOptions, $name, $reset);

        if ($this->getExit()) {
            $this->redirectAndExit();
        }
    }

    /**
     * Redirect to a route-based URL, and immediately exit
     *
     * Uses route's assemble method tobuild the URL; route is specified by $name; 
     * default route is used if none provided.
     * 
     * @param  array $urlOptions Array of key/value pairs used to assemble URL
     * @param  string $name 
     * @param  boolean $reset 
     * @return void
     */
    public function gotoRouteAndExit(array $urlOptions = array(), $name = null, $reset = false)
    {
        $this->setGotoRoute($urlOptions, $name, $reset);
        $this->redirectAndExit();
    }
    
    /**
     * Perform a redirect to a url
     *
     * @param  string $url
     * @param  array $options
     * @return void
     */
    public function gotoUrl($url, array $options = array())
    {
        $this->setGotoUrl($url, $options);
        
        if ($this->getExit()) {
            $this->redirectAndExit();
        }
    }
    
    /**
     * Set a URL string for a redirect, perform redirect, and immediately exit
     * 
     * @param  string $url 
     * @param  array $options 
     * @return void
     */
    public function gotoUrlAndExit($url, array $options = array())
    {
        $this->gotoUrl($url, $options);
        $this->redirectAndExit();
    }
    
    /**
     * exit(): Perform exit for redirector
     *
     * @return void
     */
    public function redirectAndExit()
    {
        // Close session, if started
        if (class_exists('Zend_Session', false) && Zend_Session::isStarted()) {
            Zend_Session::writeClose();
        } elseif (isset($_SESSION)) {
            session_write_close();
        }

        $this->getResponse()->sendHeaders();
        exit();
    }
    
    /**
     * direct(): Perform helper when called as 
     * $this->_helper->redirector($action, $controller, $module, $params)
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module
     * @param  array $params
     * @return void
     */
    public function direct($action, $controller = null, $module = null, array $params = array())
    {
        $this->goto($action, $controller, $module, $params);
    }
}
