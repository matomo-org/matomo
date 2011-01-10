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
 * @package    Zend_OpenId
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Exception.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Exception
 */
// require_once "Zend/Exception.php";

/**
 * Exception class for Zend_OpenId
 *
 * @category   Zend
 * @package    Zend_OpenId
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_OpenId_Exception extends Zend_Exception
{

    /**
     * The specified digest algotithm is not supported by this PHP installation
     */
    const UNSUPPORTED_DIGEST    = 1;

    /**
     * The long math arithmetick is not supported by this PHP installation
     */
    const UNSUPPORTED_LONG_MATH = 2;

    /**
     * Internal long math arithmetic error
     */
    const ERROR_LONG_MATH       = 3;

    /**
     * Iternal storage error
     */
    const ERROR_STORAGE         = 4;
}
