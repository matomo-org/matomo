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
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Rewrite.php 4635 2007-05-01 12:54:13Z martel $
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */

/** Zend_Loader */
require_once 'Zend/Loader.php';

/** Zend_Controller_Router_Abstract */
require_once 'Zend/Controller/Router/Abstract.php';

/** Zend_Controller_Router_Route */
require_once 'Zend/Controller/Router/Route.php';

/** Zend_Controller_Router_Route_Static */
require_once 'Zend/Controller/Router/Route/Static.php';

/**
 * Ruby routing based Router.
 *
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 * @see        http://manuals.rubyonrails.com/read/chapter/65
 */
class Zend_Controller_Router_Rewrite extends Zend_Controller_Router_Abstract
{
    /**
     * Whether or not to use default routes
     * @var boolean
     */
    protected $_useDefaultRoutes = true;

    /**
     * Array of routes to match against
     * @var array
     */
    protected $_routes = array();

    /**
     * Currently matched route
     * @var Zend_Controller_Router_Route_Interface
     */
    protected $_currentRoute = null;

    /** 
     * Add default routes which are used to mimic basic router behaviour
     */
    protected function addDefaultRoutes()
    {
        if (!$this->hasRoute('default')) {

            $dispatcher = $this->getFrontController()->getDispatcher();
            $request = $this->getFrontController()->getRequest();

            require_once 'Zend/Controller/Router/Route/Module.php';
            $compat = new Zend_Controller_Router_Route_Module(array(), $dispatcher, $request);
            
            $this->_routes = array_merge(array('default' => $compat), $this->_routes);
        }
    }

    /** 
     * Add route to the route chain
     * 
     * @param string Name of the route
     * @param Zend_Controller_Router_Route_Interface Route
     */
    public function addRoute($name, Zend_Controller_Router_Route_Interface $route) {
        $this->_routes[$name] = $route;
    }

    /** 
     * Add routes to the route chain
     * 
     * @param array Array of routes with names as keys and routes as values 
     */
    public function addRoutes($routes) {
        foreach ($routes as $name => $route) {
            $this->addRoute($name, $route);
        }
    }

    /** 
     * Create routes out of Zend_Config configuration
     * 
     * Example INI:
     * routes.archive.route = "archive/:year/*"
     * routes.archive.defaults.controller = archive
     * routes.archive.defaults.action = show
     * routes.archive.defaults.year = 2000
     * routes.archive.reqs.year = "\d+"
     * 
     * routes.news.type = "Zend_Controller_Router_Route_Static"
     * routes.news.route = "news"
     * routes.news.defaults.controller = "news"
     * routes.news.defaults.action = "list"
     * 
     * And finally after you have created a Zend_Config with above ini:
     * $router = new Zend_Controller_Router_Rewrite();
     * $router->addConfig($config, 'routes');
     * 
     * @param Zend_Config Configuration object
     * @param string Name of the config section containing route's definitions  
     * @throws Zend_Controller_Router_Exception
     */
    public function addConfig(Zend_Config $config, $section) 
    {
        if ($config->{$section} === null) {
            throw new Zend_Controller_Router_Exception("No route configuration in section '{$section}'");
        }
        foreach ($config->{$section} as $name => $info) {
            
            $class = (isset($info->type)) ? $info->type : 'Zend_Controller_Router_Route';
            Zend_Loader::loadClass($class);
               
            $route = call_user_func(array($class, 'getInstance'), $info);
            $this->addRoute($name, $route);
        }
    }

    /** 
     * Remove a route from the route chain
     * 
     * @param string Name of the route
     * @throws Zend_Controller_Router_Exception
     */
    public function removeRoute($name) {
        if (!isset($this->_routes[$name])) {
            throw new Zend_Controller_Router_Exception("Route $name is not defined");
        }
        unset($this->_routes[$name]);
    }

    /** 
     * Remove all standard default routes
     * 
     * @param string Name of the route
     * @param Zend_Controller_Router_Route_Interface Route
     */
    public function removeDefaultRoutes() {
        $this->_useDefaultRoutes = false;
    }

    /** 
     * Check if named route exists 
     * 
     * @param string Name of the route
     * @return boolean
     */
    public function hasRoute($name)
    {
        return isset($this->_routes[$name]);
    }

    /** 
     * Retrieve a named route 
     * 
     * @param string Name of the route
     * @throws Zend_Controller_Router_Exception
     * @return Zend_Controller_Router_Route_Interface Route object
     */
    public function getRoute($name)
    {
        if (!isset($this->_routes[$name])) {
            throw new Zend_Controller_Router_Exception("Route $name is not defined");
        }
        return $this->_routes[$name];
    }

    /** 
     * Retrieve a currently matched route 
     * 
     * @throws Zend_Controller_Router_Exception
     * @return Zend_Controller_Router_Route_Interface Route object
     */
    public function getCurrentRoute()
    {
        if (!isset($this->_currentRoute)) {
            throw new Zend_Controller_Router_Exception("Current route is not defined");
        }
        return $this->getRoute($this->_currentRoute);
    }

    /** 
     * Retrieve a name of currently matched route 
     * 
     * @throws Zend_Controller_Router_Exception
     * @return Zend_Controller_Router_Route_Interface Route object
     */
    public function getCurrentRouteName()
    {
        if (!isset($this->_currentRoute)) {
            throw new Zend_Controller_Router_Exception("Current route is not defined");
        }
        return $this->_currentRoute;
    }

    /** 
     * Retrieve an array of routes added to the route chain 
     * 
     * @return array All of the defined routes
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /** 
     * Find a matching route to the current PATH_INFO and inject 
     * returning values to the Request object. 
     * 
     * @throws Zend_Controller_Router_Exception 
     * @return Zend_Controller_Request_Abstract Request object
     */
    public function route(Zend_Controller_Request_Abstract $request)
    {
        
        if (!$request instanceof Zend_Controller_Request_Http) {
            throw new Zend_Controller_Router_Exception('Zend_Controller_Router_Rewrite requires a Zend_Controller_Request_Http-based request object');
        }

        if ($this->_useDefaultRoutes) {
            $this->addDefaultRoutes();
        }

        $pathInfo = $request->getPathInfo();

        /** Find the matching route */
        foreach (array_reverse($this->_routes) as $name => $route) {
            if ($params = $route->match($pathInfo)) {
                $this->_setRequestParams($request, $params);
                $this->_currentRoute = $name;
                break;
            }
        }
        
        return $request;

    }
    
    protected function _setRequestParams($request, $params)
    {
        foreach ($params as $param => $value) {

            $request->setParam($param, $value);
            
            if ($param === $request->getModuleKey()) {
                $request->setModuleName($value);
            }
            if ($param === $request->getControllerKey()) {
                $request->setControllerName($value);
            }
            if ($param === $request->getActionKey()) {
                $request->setActionName($value);
            }
                     
        }
    }

}
