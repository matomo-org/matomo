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
 * @package    Zend_Auth
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: NonPersistent.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Auth_Storage_Interface
 */
// require_once 'Zend/Auth/Storage/Interface.php';


/**
 * Non-Persistent Auth Storage
 *
 * Since HTTP Authentication happens again on each request, this will always be
 * re-populated. So there's no need to use sessions, this simple value class
 * will hold the data for rest of the current request.
 *
 * @category   Zend
 * @package    Zend_Auth
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Auth_Storage_NonPersistent implements Zend_Auth_Storage_Interface
{
    /**
     * Holds the actual auth data
     */
    protected $_data;

    /**
     * Returns true if and only if storage is empty
     *
     * @throws Zend_Auth_Storage_Exception If it is impossible to determine whether storage is empty
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->_data);
    }

    /**
     * Returns the contents of storage
     * Behavior is undefined when storage is empty.
     *
     * @throws Zend_Auth_Storage_Exception If reading contents from storage is impossible
     * @return mixed
     */
    public function read()
    {
        return $this->_data;
    }

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws Zend_Auth_Storage_Exception If writing $contents to storage is impossible
     * @return void
     */
    public function write($contents)
    {
        $this->_data = $contents;
    }

    /**
     * Clears contents from storage
     *
     * @throws Zend_Auth_Storage_Exception If clearing contents from storage is impossible
     * @return void
     */
    public function clear()
    {
        $this->_data = null;
    }
}
