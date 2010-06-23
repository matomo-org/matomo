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
 * @package    Zend_Log
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Priority.php 16219 2009-06-21 19:45:39Z thomas $
 */

/** Zend_Log_Filter_Interface */
require_once 'Zend/Log/Filter/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Priority.php 16219 2009-06-21 19:45:39Z thomas $
 */
class Zend_Log_Filter_Priority implements Zend_Log_Filter_Interface
{
    /**
     * @var integer
     */
    protected $_priority;

    /**
     * @var string
     */
    protected $_operator;

    /**
     * Filter logging by $priority.  By default, it will accept any log
     * event whose priority value is less than or equal to $priority.
     *
     * @param  integer  $priority  Priority
     * @param  string   $operator  Comparison operator
     * @throws Zend_Log_Exception
     */
    public function __construct($priority, $operator = '<=')
    {
        if (! is_integer($priority)) {
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('Priority must be an integer');
        }

        $this->_priority = $priority;
        $this->_operator = $operator;
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param  array    $event    event data
     * @return boolean            accepted?
     */
    public function accept($event)
    {
        return version_compare($event['priority'], $this->_priority, $this->_operator);
    }

}
