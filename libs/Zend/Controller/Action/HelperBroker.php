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

/** Zend_Loader **/
require_once 'Zend/Loader.php';

/**
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Action_HelperBroker
{
    /**
     * $_helpers - Helper array
     *
     * @var Zend_Controller_Action_Helper_Abstract[]
     */
    static protected $_helpers = array();
    
    /**
     * $_paths - paths to Action_Helpers
     *
     * @var array
     */
    static protected $_paths = array(array(
        'dir'    => 'Zend/Controller/Action/Helper/', 
        'prefix' => 'Zend_Controller_Action_Helper_'
    ));
    
    /**
     * $_actionController - ActionController reference
     * 
     * @var Zend_Controller_Action
     */
    protected $_actionController;
    
    /**
     * addHelper() - Add helper objects
     *
     * @param Zend_Controller_Action_Helper_Abstract $helper
     * @return void
     */
    static public function addHelper(Zend_Controller_Action_Helper_Abstract $helper)
    {
        $helper_name = $helper->getName();
        self::$_helpers[$helper_name] = $helper;
        return;
    }
    
    /**
     * addPrefix() - Add repository of helpers by prefix
     *
     * @param string $prefix
     */
    static public function addPrefix($prefix)
    {
        $prefix = rtrim($prefix, '_');
        $path = str_replace('_', DIRECTORY_SEPARATOR, $prefix);
        self::addPath($path, $prefix);
        return;
    }
    
    /**
     * resetHelpers()
     * 
     * @return void
     */
    static public function resetHelpers()
    {
        self::$_helpers = array();
        return;
    }
    
    /**
     * addPath() - Add path to repositories where Action_Helpers could be found.
     *
     * @param string $path
     * @param string $prefix Optional; defaults to 'Zend_Controller_Action_Helper'
     * @return void
     */
    static public function addPath($path, $prefix = 'Zend_Controller_Action_Helper')
    {
        // make sure it ends in a PATH_SEPARATOR
        if (substr($path, -1, 1) != DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }

        // make sure it ends in a PATH_SEPARATOR
        $prefix = rtrim($prefix, '_') . '_';
        
        $info['dir']    = $path;
        $info['prefix'] = $prefix;
        
        self::$_paths[] = $info;
        return;
    }

    /**
     * __construct() -
     *
     * @param Zend_Controller_Action $actionController
     * @return void
     */
    public function __construct(Zend_Controller_Action $actionController)
    {        
        $this->_actionController = $actionController;
        foreach (self::$_helpers as $helper) {
            $helper->setActionController($actionController);
            $helper->init();
        }
    }
    
    /**
     * notifyPreDispatch() - called by action controller dispatch method
     *
     * @return void
     */
    public function notifyPreDispatch()
    {
        foreach (self::$_helpers as $helper) {
            $helper->preDispatch();
        }
    }
    
    /**
     * notifyPostDispatch() - called by action controller dispatch method
     *
     * @return void
     */
    public function notifyPostDispatch()
    {
        foreach (self::$_helpers as $helper) {
            $helper->postDispatch();
        }        
    }

    /**
     * Normalize helper name for lookups
     * 
     * @param  string $name 
     * @return string
     */
    protected static function _normalizeHelperName($name)
    {
        if (strpos($name, '_') !== false) {
            $name = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        }
        
        return ucfirst($name);
    }
    
    /**
     * getHelper() - get helper by name
     *
     * @param  string $name
     * @return Zend_Controller_Action_Helper_Abstract
     */
    public function getHelper($name)
    {
        $name = self::_normalizeHelperName($name);
        
        if (!array_key_exists($name, self::$_helpers)) {
            self::_loadHelper($name);
        }
        
        $helper = self::$_helpers[$name];

        $initialize = false;
        if (null === ($actionController = $helper->getActionController())) {
            $initialize = true;
        } elseif ($actionController !== $this->_actionController) {
            $initialize = true;
        }

        if ($initialize) {
            $helper->setActionController($this->_actionController)
                ->init();
        }
        
        return $helper;
    }

    /**
     * Retrieve or initialize a helper statically
     *
     * Retrieves a helper object statically, loading on-demand if the helper 
     * does not already exist in the stack. Always returns a helper, unless
     * the helper class cannot be found.
     * 
     * @param  string $name 
     * @return Zend_Controller_Action_Helper_Abstract
     */
    public static function getStaticHelper($name)
    {
        $name = self::_normalizeHelperName($name);
        
        if (!array_key_exists($name, self::$_helpers)) {
            self::_loadHelper($name);
        }
        
        $helper = self::$_helpers[$name];

        return $helper;
    }
    
    /**
     * getExistingHelper() - get helper by name
     *
     * Static method to retrieve helper object. Only retrieves helpers already
     * initialized with the broker (either via addHelper() or on-demand loading
     * via getHelper()).
     *
     * Throws an exception if the referenced helper does not exist in the 
     * stack; use {@link hasHelper()} to check if the helper is registered
     * prior to retrieving it.
     *
     * @param  string $name
     * @return Zend_Controller_Action_Helper_Abstract
     * @throws Zend_Controller_Action_Exception
     */
    public static function getExistingHelper($name)
    {
        $name = self::_normalizeHelperName($name);
        
        if (array_key_exists($name, self::$_helpers)) {
            return self::$_helpers[$name];
        }

        throw new Zend_Controller_Action_Exception('Action helper "' . $name . '" has not been registered with the helper broker');
    }

    /**
     * Return all registered helpers as helper => object pairs
     * 
     * @return array
     */
    public static function getExistingHelpers()
    {
        return self::$_helpers;
    }

    /**
     * Is a particular helper loaded in the broker?
     * 
     * @param  string $name 
     * @return boolean
     */
    public static function hasHelper($name)
    {
        $name = self::_normalizeHelperName($name);
        return array_key_exists($name, self::$_helpers);
    }
    
    /**
     * Remove a particular helper from the broker
     * 
     * @param  string $name 
     * @return boolean
     */
    public static function removeHelper($name)
    {
        $name = self::_normalizeHelperName($name);
        if (array_key_exists($name, self::$_helpers)) {
            unset(self::$_helpers[$name]);
            return true;
        }

        return false;
    }

    /**
     * _loadHelper()
     *
     * @param  string $name
     * @return void
     */
    protected static function _loadHelper($name)
    {
        $file = $name . '.php';
        
        foreach (self::$_paths as $info) {
            $dir    = $info['dir'];
            $prefix = $info['prefix'];

            $class = $prefix . $name;
                        
            if (class_exists($class, false)) {
                $helper = new $class();
                
                if (!$helper instanceof Zend_Controller_Action_Helper_Abstract) {
                    throw new Zend_Controller_Action_Exception('Helper name ' . $name . ' -> class ' . $class . ' is not of type Zend_Controller_Action_Helper_Abstract');
                }
                
                self::$_helpers[$helper->getName()] = $helper;
                return;
                
            } elseif (Zend_Loader::isReadable($dir . $file)) {
                include_once $dir . $file;
                
                if (class_exists($class, false)) {
                    $helper = new $class();
                    if (!$helper instanceof Zend_Controller_Action_Helper_Abstract) {
                        throw new Zend_Controller_Action_Exception('Helper name ' . $name . ' -> class ' . $class . ' is not of type Zend_Controller_Action_Helper_Abstract');
                    }
                    
                    self::$_helpers[$helper->getName()] = $helper;
                    return;
                }
            }
        }
        
        throw new Zend_Controller_Action_Exception('Action Helper by name ' . $name . ' not found.');
    }
    
    /**
     * __call()
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Zend_Controller_Action_Exception if helper does not have a direct() method
     */
    public function __call($method, $args)
    {
        $helper = $this->getHelper($method);
        if (method_exists($helper, 'direct')) {
            return call_user_func_array(array($helper, 'direct'), $args);
        }

        throw new Zend_Controller_Action_Exception('Helper "' . $method . '" does not support overloading via direct()');
    }

    /**
     * __get()
     *
     * @param  string $name
     * @return Zend_Controller_Action_Helper_Abstract
     */
    public function __get($name)
    {
        return $this->getHelper($name);
    }
}
