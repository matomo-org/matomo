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
 * Zend_XmlRpc_Request
 */
require_once 'Zend/XmlRpc/Request.php';

/**
 * XmlRpc Request object -- Request via STDIN
 *
 * Extends {@link Zend_XmlRpc_Request} to accept a request via STDIN. Request is 
 * built at construction time using data from STDIN; if no data is available, the 
 * request is declared a fault.
 *
 * @category Zend
 * @package  Zend_XmlRpc
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version $Id: Stdin.php 2810 2007-01-16 01:40:21Z bkarwin $
 */
class Zend_XmlRpc_Request_Stdin extends Zend_XmlRpc_Request
{
    /**
     * Raw XML as received via request
     * @var string 
     */
    protected $_xml;

    /**
     * Constructor
     *
     * Attempts to read from php://stdin to get raw POST request; if an error 
     * occurs in doing so, or if the XML is invalid, the request is declared a 
     * fault.
     * 
     * @return void
     */
    public function __construct()
    {
        $fh = fopen('php://stdin', 'r');
        if (!$fh) {
            $this->_fault = new Zend_XmlRpc_Server_Exception(630);
            return;
        }

        $xml = '';
        while (!feof($fh)) {
            $xml .= fgets($fh);
        }
        fclose($fh);

        $this->_xml = $xml;

        $this->loadXml($xml);
    }

    /**
     * Retrieve the raw XML request
     * 
     * @return string
     */
    public function getRawRequest()
    {
        return $this->_xml;
    }
}
