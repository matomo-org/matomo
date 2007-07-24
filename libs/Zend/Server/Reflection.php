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
 * Zend_Server_Reflection_Exception
 */
require_once 'Zend/Server/Reflection/Exception.php';

/**
 * Zend_Server_Reflection_Function
 */
require_once 'Zend/Server/Reflection/Function.php';

/**
 * Zend_Server_Reflection_Class
 */
require_once 'Zend/Server/Reflection/Class.php';

/**
 * Reflection for determining method signatures to use with server classes
 * 
 * @category   Zend
 * @package    Zend_Server
 * @subpackage Reflection
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version $Id: Reflection.php 2815 2007-01-16 01:42:33Z bkarwin $
 */
class Zend_Server_Reflection
{
    /**
     * Perform class reflection to create dispatch signatures
     *
     * Creates a {@link Zend_Server_Reflection_Class} object for the class or 
     * object provided.
     *
     * If extra arguments should be passed to dispatchable methods, these may 
     * be provided as an array to $argv.
     * 
     * @param string|object $class Class name or object
     * @param null|array $argv Optional arguments to be used during the method call
     * @param string $namespace Optional namespace with which to prefix the 
     * method name (used for the signature key). Primarily to avoid collisions, 
     * also for XmlRpc namespacing
     * @return Zend_Server_Reflection_Class
     * @throws Zend_Server_Reflection_Exception
     */
    public static function reflectClass($class, $argv = false, $namespace = '')
    {
        if (is_object($class)) {
            $reflection = new ReflectionObject($class);
        } elseif (class_exists($class)) {
            $reflection = new ReflectionClass($class);
        } else {
            throw new Zend_Server_Reflection_Exception('Invalid class or object passed to attachClass()');
        }

        if ($argv && !is_array($argv)) {
            throw new Zend_Server_Reflection_Exception('Invalid argv argument passed to reflectClass');
        }

        return new Zend_Server_Reflection_Class($reflection, $namespace, $argv);
    }

    /**
     * Perform function reflection to create dispatch signatures
     *
     * Creates dispatch prototypes for a function. It returns a 
     * {@link Zend_Server_Reflection_Function} object.
     *
     * If extra arguments should be passed to the dispatchable function, these 
     * may be provided as an array to $argv.
     * 
     * @param string $function Function name
     * @param null|array $argv Optional arguments to be used during the method call
     * @param string $namespace Optional namespace with which to prefix the 
     * function name (used for the signature key). Primarily to avoid 
     * collisions, also for XmlRpc namespacing
     * @return Zend_Server_Reflection_Function 
     * @throws Zend_Server_Reflection_Exception
     */
    public static function reflectFunction($function, $argv = false, $namespace = '')
    {
        if (!is_string($function) || !function_exists($function)) {
            throw new Zend_Server_Reflection_Exception('Invalid function "' . $function . '" passed to reflectFunction');
        }


        if ($argv && !is_array($argv)) {
            throw new Zend_Server_Reflection_Exception('Invalid argv argument passed to reflectClass');
        }

        return new Zend_Server_Reflection_Function(new ReflectionFunction($function), $namespace, $argv);
    }
}
