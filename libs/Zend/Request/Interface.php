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
 * @package    Zend_Request
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Request_Interface
{
    /**
     * Overloading for accessing class property values
     * 
     * @param string $key 
     * @return mixed
     */
    public function __get($key);

    /**
     * Overloading for setting class property values
     * 
     * @param string $key 
     * @param mixed $value 
     * @return void
     */
    public function __set($key, $value);

    /**
     * Overloading to determine if a property is set
     * 
     * @param string $key 
     * @return boolean
     */
    public function __isset($key);

    /**
     * Alias for __get()
     * 
     * @param string $key 
     * @return mixed
     */
    public function get($key);

    /**
     * Alias for __set()
     * 
     * @param string $key 
     * @param mixed $value 
     * @return void
     */
    public function set($key, $value);

    /**
     * Alias for __isset()
     * 
     * @param string $key 
     * @return boolean
     */
    public function has($key);

    /**
     * Either alias for __get(), or provides ability to maintain separate 
     * configuration registry for request object.
     * 
     * @param string $key 
     * @return mixed
     */
    public function getParam($key);

    /**
     * Either alias for __set(), or provides ability to maintain separate 
     * configuration registry for request object.
     * 
     * @param string $key 
     * @param mixed $value
     * @return void
     */
    public function setParam($key, $value);

    /**
     * Get all params handled by get/setParam()
     * 
     * @return array
     */
    public function getParams();

    /**
     * Set all values handled by get/setParam()
     * 
     * @param array $params 
     * @return void
     */
    public function setParams(array $params);
}
