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

/** Zend_Memory_Exception */
require_once 'Zend/Memory/Exception.php';

/** Zend_Memory_Container */
require_once 'Zend/Memory/Container.php';


/**
 * Memory value container
 *
 * Locked (always stored in memory).
 *
 * @category   Zend
 * @package    Zend_Memory
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Memory_Container_Locked extends Zend_Memory_Container
{
    /**
     * Value object
     *
     * @var string
     */
    public $value;


    /**
     * Object constructor
     *
     * @param Zend_Memory_Manager $memoryManager
     * @param integer $id
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Lock object in memory.
     */
    public function lock()
    {
        /* Do nothing */
    }

    /**
     * Unlock object
     */
    public function unlock()
    {
        /* Do nothing */
    }

    /**
     * Return true if object is locked
     *
     * @return boolean
     */
    public function isLocked()
    {
        return true;
    }

    /**
     * Get string value reference
     *
     * _Must_ be used for value access before PHP v 5.2
     * or _may_ be used for performance considerations
     *
     * @return &string
     */
    public function &getRef()
    {
        return $this->value;
    }

    /**
     * Signal, that value is updated by external code.
     *
     * Should be used together with getRef()
     */
    public function touch()
    {
        /* Do nothing */
    }

    /**
     * Destroy memory container and remove it from memory manager list
     */
    public function destroy()
    {
        /* Do nothing */
    }
}
