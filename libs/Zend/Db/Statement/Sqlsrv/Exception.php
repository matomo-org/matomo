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
 * @package    Zend_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Db_Statement_Exception
 */
require_once 'Zend/Db/Statement/Exception.php';

/**
 * @package    Zend_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Statement_Sqlsrv_Exception extends Zend_Db_Statement_Exception
{
    /**
     * Constructor
     *
     * If $message is an array, the assumption is that the return value of
     * sqlsrv_errors() was provided. If so, it then retrieves the most recent
     * error from that stack, and sets the message and code based on it.
     *
     * @param null|array|string $message
     * @param null|int $code
     */
    public function __construct($message = null, $code = 0)
    {
       if (is_array($message)) {
            // Error should be array of errors
            // We only need first one (?)
            if (isset($message[0])) {
                $message = $message[0];
            }

            $code    = (int)    $message['code'];
            $message = (string) $message['message'];
       }
       parent::__construct($message, $code);
   }
}

