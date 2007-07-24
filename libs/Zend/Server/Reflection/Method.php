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
 * Zend_Server_Reflection_Function_Abstract
 */
require_once 'Zend/Server/Reflection/Function/Abstract.php';

/**
 * Method Reflection 
 *
 * @uses       Zend_Server_Reflection_Function_Abstract
 * @category   Zend
 * @package    Zend_Server
 * @subpackage Reflection
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version $Id: Method.php 2815 2007-01-16 01:42:33Z bkarwin $
 */
class Zend_Server_Reflection_Method extends Zend_Server_Reflection_Function_Abstract
{
    /**
     * Parent class name
     * @var string 
     */
    protected $_class;

    /**
     * Parent class reflection
     * @var Zend_Server_Reflection_Class 
     */
    protected $_classReflection;

    /**
     * Constructor
     * 
     * @param Zend_Server_Reflection_Class $class 
     * @param ReflectionMethod $r 
     * @param string $namespace 
     * @param array $argv 
     * @return void
     */
    public function __construct(Zend_Server_Reflection_Class $class, ReflectionMethod $r, $namespace = null, $argv = array())
    {
        $this->_classReflection = $class;
        $this->_reflection      = $r;

        $classNamespace = $class->getNamespace();

        // Determine namespace
        if (!empty($namespace)) {
            $this->setNamespace($namespace);
        } elseif (!empty($classNamespace)) {
            $this->setNamespace($classNamespace);
        }

        // Determine arguments
        if (is_array($argv)) {
            $this->_argv = $argv;
        }

        // If method call, need to store some info on the class
        $this->_class = $class->getName();

        // Perform some introspection
        $this->_reflect();
    }

    /**
     * Return the reflection for the class that defines this method
     * 
     * @return Zend_Server_Reflection_Class
     */
    public function getDeclaringClass()
    {
        return $this->_classReflection;
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
        $this->_classReflection = new Zend_Server_Reflection_Class(new ReflectionClass($this->_class), $this->getNamespace(), $this->getInvokeArguments());
        $this->_reflection = new ReflectionMethod($this->_classReflection->getName(), $this->getName());
    }

}
