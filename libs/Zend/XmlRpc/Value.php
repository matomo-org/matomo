<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to version 1.0 of the Zend Framework
 * license, that is bundled with this package in the file LICENSE, and
 * is available through the world-wide-web at the following URL:
 * http://www.zend.com/license/framework/1_0.txt. If you did not receive
 * a copy of the Zend Framework license and are unable to obtain it
 * through the world-wide-web, please send a note to license@zend.com
 * so we can mail you a copy immediately.
 *
 * @package    Zend_XmlRpc
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */


/** Zend_XmlRpc_Value_Exception */
require_once 'Zend/XmlRpc/Value/Exception.php';

/** Zend_XmlRpc_Value_Scalar */
require_once 'Zend/XmlRpc/Value/Scalar.php';

/** Zend_XmlRpc_Value_Base64 */
require_once 'Zend/XmlRpc/Value/Base64.php';

/** Zend_XmlRpc_Value_Boolean */
require_once 'Zend/XmlRpc/Value/Boolean.php';

/** Zend_XmlRpc_Value_DateTime */
require_once 'Zend/XmlRpc/Value/DateTime.php';

/** Zend_XmlRpc_Value_Double */
require_once 'Zend/XmlRpc/Value/Double.php';

/** Zend_XmlRpc_Value_Integer */
require_once 'Zend/XmlRpc/Value/Integer.php';

/** Zend_XmlRpc_Value_String */
require_once 'Zend/XmlRpc/Value/String.php';

/** Zend_XmlRpc_Value_Collection */
require_once 'Zend/XmlRpc/Value/Collection.php';

/** Zend_XmlRpc_Value_Array */
require_once 'Zend/XmlRpc/Value/Array.php';

/** Zend_XmlRpc_Value_Struct */
require_once 'Zend/XmlRpc/Value/Struct.php';


/**
 * Represent a native XML-RPC value entity, used as parameters for the methods
 * called by the Zend_XmlRpc_Client object and as the return value for those calls.
 *
 * This object as a very important static function Zend_XmlRpc_Value::getXmlRpcValue, this
 * function acts likes a factory for the Zend_XmlRpc_Value objects
 *
 * Using this function, users/Zend_XmlRpc_Client object can create the Zend_XmlRpc_Value objects
 * from PHP variables, XML string or by specifing the exact XML-RPC natvie type
 *
 * @package    Zend_XmlRpc
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */
abstract class Zend_XmlRpc_Value
{
    /**
     * The native XML-RPC representation of this object's value
     *
     * If the native type of this object is array or struct, this will be an array
     * of Zend_XmlRpc_Value objects
     */
    protected $_value;

    /**
     * The native XML-RPC type of this object
     * One of the XMLRPC_TYPE_* constants
     */
    protected $_type;

    /**
     * XML code representation of this object (will be calculated only once)
     */
    protected $_as_xml;

    /**
     * DOMElement representation of object (will be calculated only once)
     */
    protected $_as_dom;

    /**
     * Specify that the XML-RPC native type will be auto detected from a PHP variable type
     */
    const AUTO_DETECT_TYPE = 'auto_detect';

    /**
     * Specify that the XML-RPC value will be parsed out from a given XML code
     */
    const XML_STRING = 'xml';

    /**
     * All the XML-RPC native types
     */
    const XMLRPC_TYPE_I4       = 'i4';
    const XMLRPC_TYPE_INTEGER  = 'int';
    const XMLRPC_TYPE_DOUBLE   = 'double';
    const XMLRPC_TYPE_BOOLEAN  = 'boolean';
    const XMLRPC_TYPE_STRING   = 'string';
    const XMLRPC_TYPE_DATETIME = 'dateTime.iso8601';
    const XMLRPC_TYPE_BASE64   = 'base64';
    const XMLRPC_TYPE_ARRAY    = 'array';
    const XMLRPC_TYPE_STRUCT   = 'struct';


    /**
     * Get the native XML-RPC type (the type is one of the Zend_XmlRpc_Value::XMLRPC_TYPE_* constants)
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }


    /**
     * Return the value of this object, convert the XML-RPC native value into a PHP variable
     *
     * @return mixed
     */
    abstract public function getValue();


    /**
     * Return the XML code that represent a native MXL-RPC value
     *
     * @return string
     */
    abstract public function saveXML();

    /**
     * Return DOMElement representation of object
     * 
     * @return DOMElement
     */
    public function getAsDOM()
    {
        if (!$this->_as_dom) {
            $doc = new DOMDocument('1.0');
            $doc->loadXML($this->saveXML());
            $this->_as_dom = $doc->documentElement;
        }

        return $this->_as_dom;
    }

    protected function _stripXmlDeclaration(DOMDocument $dom)
    {
        return preg_replace('/<\?xml version="1.0"( encoding="[^\"]*")?\?>\n/u', '', $dom->saveXML());
    }

    /**
     * Creates a Zend_XmlRpc_Value* object, representing a native XML-RPC value
     * A XmlRpcValue object can be created in 3 ways:
     * 1. Autodetecting the native type out of a PHP variable
     *    (if $type is not set or equal to Zend_XmlRpc_Value::AUTO_DETECT_TYPE)
     * 2. By specifing the native type ($type is one of the Zend_XmlRpc_Value::XMLRPC_TYPE_* constants)
     * 3. From a XML string ($type is set to Zend_XmlRpc_Value::XML_STRING)
     *
     * By default the value type is autodetected according to it's PHP type
     *
     * @param mixed $value
     * @param Zend_XmlRpc_Value::constant $type
     *
     * @return Zend_XmlRpc_Value
     * @static
     */
    public static function getXmlRpcValue($value, $type = self::AUTO_DETECT_TYPE)
    {
        switch ($type) {
            case self::AUTO_DETECT_TYPE:
                // Auto detect the XML-RPC native type from the PHP type of $value
                return self::_phpVarToNativeXmlRpc($value);

            case self::XML_STRING:
                // Parse the XML string given in $value and get the XML-RPC value in it
                return self::_xmlStringToNativeXmlRpc($value);

            case self::XMLRPC_TYPE_I4:
                // fall through to the next case
            case self::XMLRPC_TYPE_INTEGER:
                return new Zend_XmlRpc_Value_Integer($value);

            case self::XMLRPC_TYPE_DOUBLE:
                return new Zend_XmlRpc_Value_Double($value);

            case self::XMLRPC_TYPE_BOOLEAN:
                return new Zend_XmlRpc_Value_Boolean($value);

            case self::XMLRPC_TYPE_STRING:
                return new Zend_XmlRpc_Value_String($value);

            case self::XMLRPC_TYPE_BASE64:
                return new Zend_XmlRpc_Value_Base64($value);

            case self::XMLRPC_TYPE_DATETIME:
                return new Zend_XmlRpc_Value_DateTime($value);

            case self::XMLRPC_TYPE_ARRAY:
                return new Zend_XmlRpc_Value_Array($value);

            case self::XMLRPC_TYPE_STRUCT:
                return new Zend_XmlRpc_Value_Struct($value);

            default:
                throw new Zend_XmlRpc_Value_Exception('Given type is not a '. __CLASS__ .' constant');
        }
    }


    /**
     * Transform a PHP native variable into a XML-RPC native value
     *
     * @param mixed $value The PHP variable for convertion
     *
     * @return Zend_XmlRpc_Value
     * @static
     */
    private static function _phpVarToNativeXmlRpc($value)
    {
        switch (gettype($value)) {
            case 'object':
                // We convert the object into a struct
                $value = get_object_vars($value);
                // Break intentionally omitted
            case 'array':
                // Default native type for a PHP array (a simple numeric array) is 'array'
                // If the PHP array is an assosiative array the native type will be 'struct'
                $obj = 'Zend_XmlRpc_Value_Array';

                // Go over the elements in the array, if the key is different than the index
                //  it means this array has associative keys and it's a struct
                if (is_array($value)) { // If the value is not array, it can't be an associated array
                    $i = 0;
                    foreach ($value as $key => $element) {
                        if ($i !== $key) {
                            $obj = 'Zend_XmlRpc_Value_Struct';
                            break;
                        }
                        ++$i;
                    }
                }
                return new $obj($value);

            case 'integer':
                return new Zend_XmlRpc_Value_Integer($value);

            case 'double':
                return new Zend_XmlRpc_Value_Double($value);

            case 'boolean':
                return new Zend_XmlRpc_Value_Boolean($value);

            case 'string':
                // Fall through to the next case
            default:
                // If type isn't identified (or identified as string), it treated as string
                return new Zend_XmlRpc_Value_String($value);
        }
    }


    /**
     * Transform an XML string into a XML-RPC native value
     *
     * @param string|SimpleXMLElement $simple_xml A SimpleXMLElement object represent the XML string
     *                                            It can be also a valid XML string for convertion
     *
     * @return Zend_XmlRpc_Value
     * @static
     */
    private static function _xmlStringToNativeXmlRpc($simple_xml)
    {
        if (!$simple_xml instanceof SimpleXMLElement) {
            try {
                $simple_xml = @new SimpleXMLElement($simple_xml);
            } catch (Exception $e) {
                // The given string is not a valid XML
                throw new Zend_XmlRpc_Value_Exception('Failed to create XML-RPC value from XML string: '.$e->getMessage(),$e->getCode());
            }
        }

        // Get the key (tag name) and value from the simple xml object and convert the value to an XML-RPC native value
        list($type, $value) = each($simple_xml);
        if (!$type) {    // If no type was specified, the default is string
            $type = self::XMLRPC_TYPE_STRING;
        }

        switch ($type) {
            // All valid and known XML-RPC native values
            case self::XMLRPC_TYPE_I4:
                // Fall through to the next case
            case self::XMLRPC_TYPE_INTEGER:
                $xmlrpc_val = new Zend_XmlRpc_Value_Integer($value);
                break;
            case self::XMLRPC_TYPE_DOUBLE:
                $xmlrpc_val = new Zend_XmlRpc_Value_Double($value);
                break;
            case self::XMLRPC_TYPE_BOOLEAN:
                $xmlrpc_val = new Zend_XmlRpc_Value_Boolean($value);
                break;
            case self::XMLRPC_TYPE_STRING:
                $xmlrpc_val = new Zend_XmlRpc_Value_String($value);
                break;
            case self::XMLRPC_TYPE_DATETIME:  // The value should already be in a iso8601 format
                $xmlrpc_val = new Zend_XmlRpc_Value_DateTime($value);
                break;
            case self::XMLRPC_TYPE_BASE64:    // The value should already be base64 encoded
                $xmlrpc_val = new Zend_XmlRpc_Value_Base64($value ,true);
                break;
            case self::XMLRPC_TYPE_ARRAY:
                // If the XML is valid, $value must be an SimpleXML element and contain the <data> tag
                if (!$value instanceof SimpleXMLElement) {
                    throw new Zend_XmlRpc_Value_Exception('XML string is invalid for XML-RPC native '. self::XMLRPC_TYPE_ARRAY .' type');
                } elseif (empty($value->data)) {
                    throw new Zend_XmlRpc_Value_Exception('Invalid XML for XML-RPC native '. self::XMLRPC_TYPE_ARRAY .' type: ARRAY tag must contain DATA tag');
                }
                $values = array();
                // Parse all the elements of the array from the XML string
                // (simple xml element) to Zend_XmlRpc_Value objects
                foreach ($value->data->value as $element) {
                    $values[] = self::_xmlStringToNativeXmlRpc($element);
                }
                $xmlrpc_val = new Zend_XmlRpc_Value_Array($values);
                break;
            case self::XMLRPC_TYPE_STRUCT:
                // If the XML is valid, $value must be an SimpleXML
                if ((!$value instanceof SimpleXMLElement)) {
                    throw new Zend_XmlRpc_Value_Exception('XML string is invalid for XML-RPC native '. self::XMLRPC_TYPE_STRUCT .' type');
                }
                $values = array();
                // Parse all the memebers of the struct from the XML string
                // (simple xml element) to Zend_XmlRpc_Value objects
                foreach ($value->member as $member) {
                    // @todo? If a member doesn't have a <value> tag, we don't add it to the struct
                    // Maybe we want to throw an exception here ?
                    if ((!$member->value instanceof SimpleXMLElement) || empty($member->value)) {
                        continue;
                        //throw new Zend_XmlRpc_Value_Exception('Member of the '. self::XMLRPC_TYPE_STRUCT .' XML-RPC native type must contain a VALUE tag');
                    }
                    $values[(string)$member->name] = self::_xmlStringToNativeXmlRpc($member->value);
                }
                $xmlrpc_val = new Zend_XmlRpc_Value_Struct($values);
                break;
            default:
                throw new Zend_XmlRpc_Value_Exception('Value type \''. $type .'\' parsed from the XML string is not a known XML-RPC native type');
                break;
        }
        $xmlrpc_val->_setXML($simple_xml->asXML());

        return $xmlrpc_val;
    }

    
    private function _setXML($xml)
    {
        $this->_as_xml = $xml;
    }
    
}


