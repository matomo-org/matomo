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
 * Zend_XmlRpc_Value
 */
require_once 'Zend/XmlRpc/Value.php';

/**
 * Zend_XmlRpc_Fault
 */
require_once 'Zend/XmlRpc/Fault.php';

/**
 * XmlRpc Response 
 *
 * Container for accessing an XMLRPC return value and creating the XML response.
 * 
 * @category Zend
 * @package  Zend_XmlRpc
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version $Id$
 */
class Zend_XmlRpc_Response
{
    /**
     * Return value
     * @var mixed 
     */
    protected $_return;

    /**
     * Return type
     * @var string 
     */
    protected $_type;

    /**
     * Response character encoding
     * @var string 
     */
    protected $_encoding = 'UTF-8';

    /**
     * Fault, if response is a fault response
     * @var null|Zend_XmlRpc_Fault 
     */
    protected $_fault = null;

    /**
     * Constructor
     *
     * Can optionally pass in the return value and type hinting; otherwise, the 
     * return value can be set via {@link setReturnValue()}.
     * 
     * @param mixed $return 
     * @param string $type 
     * @return void
     */
    public function __construct($return = null, $type = null)
    {
        $this->setReturnValue($return, $type);
    }

    /**
     * Set encoding to use in response
     * 
     * @param string $encoding 
     * @return Zend_XmlRpc_Response
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Retrieve current response encoding
     * 
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set the return value
     *
     * Sets the return value, with optional type hinting if provided.
     * 
     * @param mixed $value 
     * @param string $type 
     * @return void
     */
    public function setReturnValue($value, $type = null)
    {
        $this->_return = $value;
        $this->_type = (string) $type;
    }

    /**
     * Retrieve the return value
     * 
     * @return mixed
     */
    public function getReturnValue()
    {
        return $this->_return;
    }

    /**
     * Retrieve the XMLRPC value for the return value
     * 
     * @return Zend_XmlRpc_Value
     */
    protected function _getXmlRpcReturn()
    {
        return Zend_XmlRpc_Value::getXmlRpcValue($this->_return);
    }

    /**
     * Is the response a fault response?
     *
     * @return boolean 
     */
    public function isFault()
    {
        return $this->_fault instanceof Zend_XmlRpc_Fault;
    }

    /**
     * Returns the fault, if any.
     *
     * @return null|Zend_XmlRpc_Fault
     */
    public function getFault()
    {
        return $this->_fault;
    }

    /**
     * Load a response from an XML response
     *
     * Attempts to load a response from an XMLRPC response, autodetecting if it 
     * is a fault response.
     * 
     * @param string $response 
     * @return boolean True if a valid XMLRPC response, false if a fault 
     * response or invalid input
     */
    public function loadXml($response)
    {
        if (!is_string($response)) {
            $this->_fault = new Zend_XmlRpc_Fault(650);
            $this->_fault->setEncoding($this->getEncoding());
            return false;
        }

        try {
            $xml = @new SimpleXMLElement($response);
        } catch (Exception $e) {
            // Not valid XML
            $this->_fault = new Zend_XmlRpc_Fault(651);
            $this->_fault->setEncoding($this->getEncoding());
            return false;
        } 

        if (!empty($xml->fault)) {
            // fault response
            $this->_fault = new Zend_XmlRpc_Fault();
            $this->_fault->setEncoding($this->getEncoding());
            $this->_fault->loadXml($response);
            return false;
        }

        if (empty($xml->params)) {
            // Invalid response
            $this->_fault = new Zend_XmlRpc_Fault(652);
            $this->_fault->setEncoding($this->getEncoding());
            return false;
        }

        try {
            if (!isset($xml->params) || !isset($xml->params->param) || !isset($xml->params->param->value)) {
                throw new Zend_XmlRpc_Value_Exception('Missing XML-RPC value in XML');
            }
            $valueXml = $xml->params->param->value->asXML();
            $valueXml = preg_replace('/<\?xml version=.*?\?>/i', '', $valueXml);
            $value = Zend_XmlRpc_Value::getXmlRpcValue(trim($valueXml), Zend_XmlRpc_Value::XML_STRING);
        } catch (Zend_XmlRpc_Value_Exception $e) {
            $this->_fault = new Zend_XmlRpc_Fault(653);
            $this->_fault->setEncoding($this->getEncoding());
            return false;
        }

        $this->setReturnValue($value->getValue());
        return true;
    }

    /**
     * Return response as XML
     * 
     * @return string
     */
    public function saveXML()
    {
        $value = $this->_getXmlRpcReturn();
        $valueDOM = new DOMDocument('1.0', $this->getEncoding());
        $valueDOM->loadXML($value->saveXML());

        $dom      = new DOMDocument('1.0', $this->getEncoding());
        $response = $dom->appendChild($dom->createElement('methodResponse'));
        $params   = $response->appendChild($dom->createElement('params'));
        $param    = $params->appendChild($dom->createElement('param'));

        $param->appendChild($dom->importNode($valueDOM->documentElement, true));

        return $dom->saveXML();
    }

    /**
     * Return XML response
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->saveXML();
    }
}
