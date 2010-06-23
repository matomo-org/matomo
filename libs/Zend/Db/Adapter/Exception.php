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
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Exception.php 17860 2009-08-27 22:48:48Z beberlei $
 */

/**
 * Zend_Db_Exception
 */
require_once 'Zend/Db/Exception.php';

/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Adapter_Exception extends Zend_Db_Exception
{
    protected $_chainedException = null;

    public function __construct($message = null, Exception $e = null)
    {
        if ($e) {
            $this->_chainedException = $e;
            $this->code = $e->getCode();
        }
        parent::__construct($message);
    }

    public function hasChainedException()
    {
        return ($this->_chainedException!==null);
    }

    public function getChainedException()
    {
        return $this->_chainedException;
    }

}
