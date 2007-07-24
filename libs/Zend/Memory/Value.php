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
 * @package    Zend_Memory
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * String value object
 *
 * It's an OO string wrapper.
 * Used to intercept string updates.
 *
 * @package    Zend_Memory
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @todo       also implement Countable for PHP 5.1 but not yet to stay 5.0 compatible
 */
class Zend_Memory_Value implements ArrayAccess {
    /**
     * Value
     *
     * @var string
     */
    private $_value;

    /**
     * Container
     *
     * @var Zend_Memory_Container_Interface
     */
    private $_container;

    /**
     * Boolean flag which signals to trace value modifications
     *
     * @var boolean
     */
    private $_trace;


    /**
     * Object constructor
     *
     * @param string $value
     * @param Zend_Memory_Container_Movable $container
     */
    public function __construct($value, Zend_Memory_Container_Movable $container)
    {
        $this->_container = $container;

        $this->_value = (string)$value;

        /**
         * Object is marked as just modified by memory manager
         * So we don't need to trace followed object modifications and
         * object is processed (and marked as traced) when another
         * memory object is modified.
         *
         * It reduces overall numberr of calls necessary to modification trace
         */
        $this->_trace = false;
    }


    /**
     * ArrayAccess interface method
     * returns true if string offset exists
     *
     * @param integer $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $offset >= 0  &&  $offset < strlen($this->_value);
    }

    /**
     * ArrayAccess interface method
     * Get character at $offset position
     *
     * @param integer $offset
     * @return string
     */
    public function offsetGet($offset)
    {
        return $this->_value[$offset];
    }

    /**
     * ArrayAccess interface method
     * Set character at $offset position
     *
     * @param integer $offset
     * @param string $char
     */
    public function offsetSet($offset, $char)
    {
        $this->_value[$offset] = $char;

        if ($this->_trace) {
            $this->_trace = false;
            $this->_container->processUpdate();
        }
    }

    /**
     * ArrayAccess interface method
     * Unset character at $offset position
     *
     * @param integer $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->_value[$offset]);

        if ($this->_trace) {
            $this->_trace = false;
            $this->_container->processUpdate();
        }
    }


    /**
     * To string conversion
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_value;
    }


    /**
     * Get string value reference
     *
     * _Must_ be used for value access before PHP v 5.2
     * or _may_ be used for performance considerations
     *
     * @internal
     * @return string
     */
    public function &getRef()
    {
        return $this->_value;
    }

    /**
     * Start modifications trace
     *
     * _Must_ be used for value access before PHP v 5.2
     * or _may_ be used for performance considerations
     *
     * @internal
     */
    public function startTrace()
    {
        $this->_trace = true;
    }
}
