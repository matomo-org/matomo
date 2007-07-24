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
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Validate.php 4974 2007-05-25 21:11:56Z bkarwin $
 */


/**
 * @see Zend_Validate_Interface
 */
require_once 'Zend/Validate/Interface.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate implements Zend_Validate_Interface
{
    /**
     * Validator chain
     *
     * @var array
     */
    protected $_validators = array();

    /**
     * Array of validation failure messages
     *
     * @var array
     */
    protected $_messages = array();

    /**
     * Array of validation failure messages
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Adds a validator to the end of the chain
     *
     * If $breakChainOnFailure is true, then if the validator fails, the next validator in the chain,
     * if one exists, will not be executed.
     *
     * @param  Zend_Validate_Interface $validator
     * @param  boolean                 $breakChainOnFailure
     * @return Zend_Validate Provides a fluent interface
     */
    public function addValidator(Zend_Validate_Interface $validator, $breakChainOnFailure = false)
    {
        $this->_validators[] = array('instance' => $validator, 'breakChainOnFailure' => $breakChainOnFailure);
        return $this;
    }

    /**
     * Returns true if and only if $value passes all validations in the chain
     *
     * Validators are run in the order in which they were added to the chain (FIFO).
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_messages = array();
        $this->_errors   = array();
        $result = true;
        foreach ($this->_validators as $element) {
            $validator = $element['instance'];
            if ($validator->isValid($value)) {
                continue;
            }
            $result = false;
            $this->_messages = array_merge($this->_messages, $validator->getMessages());
            $this->_errors   = array_merge($this->_errors,   $validator->getErrors());
            if ($element['breakChainOnFailure']) {
                break;
            }
        }
        return $result;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns array of validation failure messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns array of validation failure messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @param mixed    $value
     * @param string   $classBaseName
     * @param array    $args          OPTIONAL
     * @param mixed    $namespaces    OPTIONAL
     * @return boolean
     * @throws Zend_Validate_Exception
     */
    public static function is($value, $classBaseName, array $args = array(), $namespaces = array())
    {
        $namespaces = array_merge(array('Zend_Validate'), (array) $namespaces);
        foreach ($namespaces as $namespace) {
            $className = $namespace . '_' . ucfirst($classBaseName);
            try {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($className);
                $class = new ReflectionClass($className);
                if ($class->implementsInterface('Zend_Validate_Interface')) {
                    if ($class->hasMethod('__construct')) {
                        $object = $class->newInstanceArgs($args);
                    } else {
                        $object = $class->newInstance();
                    }
                    return $object->isValid($value);
                }
            } catch (Zend_Exception $ze) {
                // fallthrough and continue
            }
        }
        require_once 'Zend/Validate/Exception.php';
        throw new Zend_Validate_Exception("Validate class not found from basename '$classBaseName'");
    }

}
