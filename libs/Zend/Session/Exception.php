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
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Exception.php 16210 2009-06-21 19:22:17Z thomas $
 * @since      Preview Release 0.2
 */


/**
 * @see Zend_Exception
 */
require_once 'Zend/Exception.php';


/**
 * Zend_Session_Exception
 *
 * @category   Zend
 * @package    Zend_Session
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Session_Exception extends Zend_Exception
{
    /**
     * sessionStartError
     *
     * @see http://framework.zend.com/issues/browse/ZF-1325
     * @var string PHP Error Message
     */
    static public $sessionStartError = null;

    /**
     * handleSessionStartError() - interface for set_error_handler()
     *
     * @see    http://framework.zend.com/issues/browse/ZF-1325
     * @param  int    $errno
     * @param  string $errstr
     * @return void
     */
    static public function handleSessionStartError($errno, $errstr, $errfile, $errline, $errcontext)
    {
        self::$sessionStartError = $errfile . '(Line:' . $errline . '): Error #' . $errno . ' ' . $errstr . ' ' . $errcontext;
    }

    /**
     * handleSilentWriteClose() - interface for set_error_handler()
     *
     * @see    http://framework.zend.com/issues/browse/ZF-1325
     * @param  int    $errno
     * @param  string $errstr
     * @return void
     */
    static public function handleSilentWriteClose($errno, $errstr, $errfile, $errline, $errcontext)
    {
        self::$sessionStartError .= PHP_EOL . $errfile . '(Line:' . $errline . '): Error #' . $errno . ' ' . $errstr . ' ' . $errcontext;
    }
}

