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
 * @package    Zend_Session
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 * @since      Preview Release 0.2
 */


/**
 * Zend_Session_Abstract
 *
 * @category   Zend
 * @package    Zend_Session
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Session_Abstract
{
    /**
     * Whether or not session permits writing (modification of $_SESSION[])
     *
     * @var bool
     */
    protected static $_writable = false;

    /**
     * Whether or not session permits reading (reading data in $_SESSION[])
     *
     * @var bool
     */
    protected static $_readable = false;

    /**
     * Since expiring data is handled at startup to avoid __destruct difficulties,
     * the data that will be expiring at end of this request is held here
     *
     * @var array
     */
    protected static $_expiringData = array();


    /**
     * Error message thrown when an action requires modification,
     * but current Zend_Session has been marked as read-only.
     */
    const _THROW_NOT_WRITABLE_MSG = 'Zend_Session is currently marked as read-only.';


    /**
     * Error message thrown when an action requires reading session data,
     * but current Zend_Session is not marked as readable.
     */
    const _THROW_NOT_READABLE_MSG = 'Zend_Session is not marked as readable.';


    /**
     * namespaceIsset() - check to see if a namespace or a variable within a namespace is set
     *
     * @param  string $namespace
     * @param  string $name
     * @return bool
     */
    protected static function _namespaceIsset($namespace, $name = null)
    {
        if (self::$_readable === false) {
            /**
             * @see Zend_Session_Exception
             */
            require_once 'Zend/Session/Exception.php';
            throw new Zend_Session_Exception(self::_THROW_NOT_READABLE_MSG);
        }

        if ($name === null) {
            return ( isset($_SESSION[$namespace]) || isset(self::$_expiringData[$namespace]) );
        } else {
            return ( isset($_SESSION[$namespace][$name]) || isset(self::$_expiringData[$namespace][$name]) );
        }
    }


    /**
     * namespaceUnset() - unset a namespace or a variable within a namespace
     *
     * @param  string $namespace
     * @param  string $name
     * @throws Zend_Session_Exception
     * @return void
     */
    protected static function _namespaceUnset($namespace, $name = null)
    {
        if (self::$_writable === false) {
            /**
             * @see Zend_Session_Exception
             */
            require_once 'Zend/Session/Exception.php';
            throw new Zend_Session_Exception(self::_THROW_NOT_WRITABLE_MSG);
        }

        $name = (string) $name;

        // check to see if the api wanted to remove a var from a namespace or a namespace
        if ($name === '') {
            unset($_SESSION[$namespace]);
            unset(self::$_expiringData[$namespace]);
        } else {
            unset($_SESSION[$namespace][$name]);
            unset(self::$_expiringData[$namespace]);
        }

        // if we remove the last value, remove namespace.
        if (empty($_SESSION[$namespace])) {
            unset($_SESSION[$namespace]);
        }
    }


    /**
     * namespaceGet() - Get $name variable from $namespace, returning by reference.
     *
     * @param  string $namespace
     * @param  string $name
     * @return mixed
     */
    protected static function & _namespaceGet($namespace, $name = null)
    {
        if (self::$_readable === false) {
            /**
             * @see Zend_Session_Exception
             */
            require_once 'Zend/Session/Exception.php';
            throw new Zend_Session_Exception(self::_THROW_NOT_READABLE_MSG);
        }

        if ($name === null) {
            if (isset($_SESSION[$namespace])) { // check session first for data requested
                return $_SESSION[$namespace];
            } elseif (isset(self::$_expiringData[$namespace])) { // check expiring data for data reqeusted
                return self::$_expiringData[$namespace];
            } else {
                return $_SESSION[$namespace]; // satisfy return by reference
            }
        } else {
            if (isset($_SESSION[$namespace][$name])) { // check session first
                return $_SESSION[$namespace][$name];
            } elseif (isset(self::$_expiringData[$namespace][$name])) { // check expiring data
                return self::$_expiringData[$namespace][$name];
            } else {
                return $_SESSION[$namespace][$name]; // satisfy return by reference
            }
        }
    }


    /**
     * namespaceGetAll() - Get an array containing $namespace, including expiring data.
     *
     * @param string $namespace
     * @param string $name
     * @return mixed
     */
    protected static function _namespaceGetAll($namespace)
    {
        $currentData  = (isset($_SESSION[$namespace]) && is_array($_SESSION[$namespace])) ?
            $_SESSION[$namespace] : array();
        $expiringData = (isset(self::$_expiringData[$namespace]) && is_array(self::$_expiringData[$namespace])) ?
            self::$_expiringData[$namespace] : array();
        return array_merge($currentData, $expiringData);
    }
}
