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
 * Zend_Server_Reflection_Method
 */
require_once 'Zend/Server/Reflection/Method.php';

/**
 * Zend_Server_Reflection_Exception
 */
require_once 'Zend/Server/Reflection/Exception.php';

/**
 * Class/Object reflection
 *
 * Proxies calls to a ReflectionClass object, and decorates getMethods() by 
 * creating its own list of {@link Zend_Server_Reflection_Method}s.
 * 
 * @category   Zend
 * @package    Zend_Server
 * @subpackage Reflection
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version $Id: Class.php 2815 2007-01-16 01:42:33Z bkarwin $
 */
class Zend_Server_Reflection_Class
{
    /**
     * Optional configuration parameters; accessible via {@link __get} and 
     * {@link __set()}
     * @var array 
     */
    protected $_config = array();

    /**
     * Array of {@link Zend_Server_Reflection_Method}s
     * @var array 
     */
    protected $_methods = array();

    /**
     * Namespace
     * @var string 
     */
    protected $_namespace = null;

    /**
     * ReflectionClass object
     * @var ReflectionClass
     */
    protected $_reflection;

    /**
     * Constructor
     *
     * Create array of dispatchable methods, each a 
     * {@link Zend_Server_Reflection_Method}. Sets reflection object property.
     * 
     * @param ReflectionClass $reflection 
     * @param string $namespace 
     * @param mixed $argv 
     * @return void
     */
    public function __construct(ReflectionClass $reflection, $namespace = null, $argv = false)
    {
        $this->_reflection = $reflection;
        $this->setNamespace($namespace);

        foreach ($reflection->getMethods() as $method) {
            // Don't aggregate magic methods
            if ('__' == substr($method->getName(), 0, 2)) {
                continue;
            }

            if ($method->isPublic()) {
                // Get signatures and description
                $this->_methods[] = new Zend_Server_Reflection_Method($this, $method, $this->getNamespace(), $argv);
            }
        }
    }

    /**
     * Proxy reflection calls
     * 
     * @param string $method 
     * @param array $args 
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->_reflection, $method)) {
            return call_user_func_array(array($this->_reflection, $method), $args);
        }

        throw new Zend_Server_Reflection_Exception('Invalid reflection method');
    }

    /**
     * Retrieve configuration parameters
     *
     * Values are retrieved by key from {@link $_config}. Returns null if no 
     * value found.
     * 
     * @param string $key 
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }

        return null;
    }

    /**
     * Set configuration parameters
     *
     * Values are stored by $key in {@link $_config}.
     * 
     * @param string $key 
     * @param mixed $value 
     * @return void
     */
    public function __set($key, $value)
    {
        $this->_config[$key] = $value;
    }

    /**
     * Return array of dispatchable {@link Zend_Server_Reflection_Method}s.
     * 
     * @access public
     * @return array
     */
    public function getMethods()
    {
        return $this->_methods;
    }

    /**
     * Get namespace for this class
     * 
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Set namespace for this class
     * 
     * @param string $namespace
     * @return void
     */
    public function setNamespace($namespace)
    {
        if (empty($namespace)) {
            $this->_namespace = '';
            return;
        }

        if (!is_string($namespace) || !preg_match('/[a-z0-9_\.]+/i', $namespace)) {
            throw new Zend_Server_Reflection_Exception('Invalid namespace');
        }

        $this->_namespace = $namespace;
    }

    /**
     * Wakeup from serialization
     * 
     * Reflection needs explicit instantiation to work correctly. Re-instantiate 
     * reflection object on wakeup.
     * 
     * @return void
     */
    public function __wakeup()
    {
        $this->_reflection = new ReflectionClass($this->getName());
    }
}
