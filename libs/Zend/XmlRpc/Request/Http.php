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
 * XmlRpc Request object -- Request via HTTP
 *
 * Extends {@link Zend_XmlRpc_Request} to accept a request via HTTP. Request is 
 * built at construction time using a raw POST; if no data is available, the 
 * request is declared a fault.
 *
 * @category Zend
 * @package  Zend_XmlRpc
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version $Id: Http.php 3833 2007-03-09 05:01:14Z matthew $
 */
class Zend_XmlRpc_Request_Http extends Zend_XmlRpc_Request
{
    /**
     * Array of headers
     * @var array
     */
    protected $_headers;

    /**
     * Raw XML as received via request
     * @var string 
     */
    protected $_xml;

    /**
     * Constructor
     *
     * Attempts to read from php://input to get raw POST request; if an error 
     * occurs in doing so, or if the XML is invalid, the request is declared a 
     * fault.
     * 
     * @return void
     */
    public function __construct()
    {
        $fh = fopen('php://input', 'r');
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

    /**
     * Get headers
     *
     * Gets all headers as key => value pairs and returns them.
     * 
     * @return array
     */
    public function getHeaders()
    {
        if (null === $this->_headers) {
            $this->_headers = array();
            foreach ($_SERVER as $key => $value) {
                if ('HTTP_' == substr($key, 0, 5)) {
                    $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $this->_headers[$header] = $value;
                }
            }
        }

        return $this->_headers;
    }

    /**
     * Retrieve the full HTTP request, including headers and XML
     * 
     * @return string
     */
    public function getFullRequest()
    {
        $request = '';
        foreach ($this->getHeaders() as $key => $value) {
            $request .= $key . ': ' . $value . "\n";
        }

        $request .= $this->_xml;

        return $request;
    }
}
