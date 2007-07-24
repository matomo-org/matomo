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

/**
 * @category   Zend
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Controller_Request_Abstract
{
    /**
     * Has the action been dispatched?
     * @var boolean 
     */
    protected $_dispatched = false;

    /**
     * Module
     * @var string
     */
    protected $_module;

    /**
     * Module key for retrieving module from params
     * @var string 
     */
    protected $_moduleKey = 'module';

    /**
     * Controller
     * @var string
     */
    protected $_controller;

    /**
     * Controller key for retrieving controller from params
     * @var string 
     */
    protected $_controllerKey = 'controller';

    /**
     * Action
     * @var string
     */
    protected $_action;

    /**
     * Action key for retrieving action from params
     * @var string 
     */
    protected $_actionKey = 'action';

    /**
     * Request parameters
     * @var array 
     */
    protected $_params = array();

    /**
     * Retrieve the module name
     * 
     * @return string
     */
    public function getModuleName()
    {
        if (null === $this->_module) {
            $this->_module = $this->getParam($this->getModuleKey());
        }

        return $this->_module;
    }

    /**
     * Set the module name to use
     * 
     * @param string $value 
     * @return Zend_Controller_Request_Abstract
     */
    public function setModuleName($value)
    {
        $this->_module = $value;
        return $this;
    }

    /**
     * Retrieve the controller name
     * 
     * @return string
     */
    public function getControllerName()
    {
        if (null === $this->_controller) {
            $this->_controller = $this->getParam($this->getControllerKey());
        }

        return $this->_controller;
    }

    /**
     * Set the controller name to use
     * 
     * @param string $value 
     * @return Zend_Controller_Request_Abstract
     */
    public function setControllerName($value)
    {
        $this->_controller = $value;
        return $this;
    }

    /**
     * Retrieve the action name
     * 
     * @return string
     */
    public function getActionName()
    {
        if (null === $this->_action) {
            $this->_action = $this->getParam($this->getActionKey());
        }

        return $this->_action;
    }

    /**
     * Set the action name 
     * 
     * @param string $value 
     * @return Zend_Controller_Request_Abstract
     */
    public function setActionName($value)
    {
        $this->_action = $value;
        return $this;
    }

    /**
     * Retrieve the module key
     * 
     * @return string
     */
    public function getModuleKey() 
    {
        return $this->_moduleKey;
    }

    /**
     * Set the module key
     * 
     * @param string $key 
     * @return Zend_Controller_Request_Abstract
     */
    public function setModuleKey($key)
    {
        $this->_moduleKey = (string) $key;
        return $this;
    }

    /**
     * Retrieve the controller key
     * 
     * @return string
     */
    public function getControllerKey() 
    {
        return $this->_controllerKey;
    }

    /**
     * Set the controller key
     * 
     * @param string $key 
     * @return Zend_Controller_Request_Abstract
     */
    public function setControllerKey($key)
    {
        $this->_controllerKey = (string) $key;
        return $this;
    }

    /**
     * Retrieve the action key
     * 
     * @return string
     */
    public function getActionKey()
    {
        return $this->_actionKey;
    }

    /**
     * Set the action key 
     * 
     * @param string $key 
     * @return Zend_Controller_Request_Abstract
     */
    public function setActionKey($key)
    {
        $this->_actionKey = (string) $key;
        return $this;
    }

    /**
     * Get an action parameter
     * 
     * @param string $key 
     * @param mixed $default Default value to use if key not found
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        $key = (string) $key;
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }

        return $default;
    }

    /**
     * Retrieve only user params (i.e, any param specific to the object and not the environment)
     * 
     * @return array
     */
    public function getUserParams()
    {
        return $this->_params;
    }

    /**
     * Retrieve a single user param (i.e, a param specific to the object and not the environment)
     * 
     * @param string $key
     * @param string $default Default value to use if key not found
     * @return mixed
     */
    public function getUserParam($key, $default = null)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }

        return $default;
    }

    /**
     * Set an action parameter
     *
     * A $value of null will unset the $key if it exists
     * 
     * @param string $key 
     * @param mixed $value 
     * @return Zend_Controller_Request_Abstract
     */
    public function setParam($key, $value)
    {
        $key = (string) $key;

        if ((null === $value) && isset($this->_params[$key])) {
            unset($this->_params[$key]);
        } elseif (null !== $value) {
            $this->_params[$key] = $value;
        }

        return $this;
    }

    /**
     * Get all action parameters
     * 
     * @return array
     */
     public function getParams()
     {
         return $this->_params;
     }

    /**
     * Set action parameters en masse; does not overwrite
     *
     * Null values will unset the associated key.
     * 
     * @param array $array 
     * @return Zend_Controller_Request_Abstract
     */
    public function setParams(array $array)
    {
        $this->_params = $this->_params + (array) $array;

        foreach ($this->_params as $key => $value) {
            if (null === $value) {
                unset($this->_params[$key]);
            }
        }

        return $this;
    }

    /**
     * Set flag indicating whether or not request has been dispatched
     * 
     * @param boolean $flag 
     * @return Zend_Controller_Request_Abstract
     */
    public function setDispatched($flag = true)
    {
        $this->_dispatched = $flag ? true : false;
        return $this;
    }

    /**
     * Determine if the request has been dispatched
     * 
     * @return boolean
     */
    public function isDispatched()
    {
        return $this->_dispatched;
    }
}
