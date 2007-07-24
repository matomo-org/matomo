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
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_Db_Adapter_Exception
 */
require_once 'Zend/Db/Adapter/Exception.php';

/**
 * Zend_Db_Adapter_Oracle_Exception
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Adapter_Oracle_Exception extends Zend_Db_Adapter_Exception
{
   protected $message = 'Unknown exception';
   protected $code = 0;

   function __construct($error = null, $code = 0) {
       if (is_array($error)) {
            if (!isset($error['offset'])) {
                $this->message = $error['code'] .' '. $error['message'];
            } else {
                $this->message = $error['code'] .' '. $error['message']." "
                               . substr($error['sqltext'], 0, $error['offset'])
                               . "*"
                               . substr($error['sqltext'], $error['offset']);
            }
            $this->code = $error['code'];
       } else if (is_string($error)) {
           $this->message = $error;
       }
       if (!$this->code && $code) {
           $this->code = $code;
       }
   }
}
