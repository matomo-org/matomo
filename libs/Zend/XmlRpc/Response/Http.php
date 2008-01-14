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
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */ 

/**
 * Zend_XmlRpc_Response
 */
require_once 'Zend/XmlRpc/Response.php';

/**
 * HTTP response
 * 
 * @uses Zend_XmlRpc_Response
 * @category Zend
 * @package  Zend_XmlRpc
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version $Id$
 */
class Zend_XmlRpc_Response_Http extends Zend_XmlRpc_Response
{
    /**
     * Override __toString() to send HTTP Content-Type header
     * 
     * @return string
     */
    public function __toString()
    {
        if (!headers_sent()) {
            header('Content-Type: application/xml; charset=' . strtolower($this->getEncoding()));
        }

        return parent::__toString();
    }
}
