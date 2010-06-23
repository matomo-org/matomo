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
 * @version    $Id: Message.php 16219 2009-06-21 19:45:39Z thomas $
 */

/** Zend_Log_Filter_Interface */
require_once 'Zend/Log/Filter/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Message.php 16219 2009-06-21 19:45:39Z thomas $
 */
class Zend_Log_Filter_Message implements Zend_Log_Filter_Interface
{
    /**
     * @var string
     */
    protected $_regexp;

    /**
     * Filter out any log messages not matching $regexp.
     *
     * @param  string  $regexp     Regular expression to test the log message
     * @throws Zend_Log_Exception
     */
    public function __construct($regexp)
    {
        if (@preg_match($regexp, '') === false) {
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception("Invalid regular expression '$regexp'");
        }
        $this->_regexp = $regexp;
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param  array    $event    event data
     * @return boolean            accepted?
     */
    public function accept($event)
    {
        return preg_match($this->_regexp, $event['message']) > 0;
    }

}
