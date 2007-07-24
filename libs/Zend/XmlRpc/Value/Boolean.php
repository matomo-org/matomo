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
 * @package    Zend_XmlRpc
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Zend_XmlRpc_Value_Scalar
 */
require_once 'Zend/XmlRpc/Value/Scalar.php';


/**
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_XmlRpc_Value_Boolean extends Zend_XmlRpc_Value_Scalar
{

    /**
     * Set the value of a boolean native type
     * We hold the boolean type as an integer (0 or 1)
     *
     * @param bool $value
     */
    public function __construct($value)
    {
        $this->_type = self::XMLRPC_TYPE_BOOLEAN;
        // Make sure the value is boolean and then convert it into a integer
        // The double convertion is because a bug in the ZendOptimizer in PHP version 5.0.4
        $this->_value = (int)(bool)$value;
    }

    /**
     * Return the value of this object, convert the XML-RPC native boolean value into a PHP boolean
     *
     * @return bool
     */
    public function getValue()
    {
        return (bool)$this->_value;
    }

    /**
     * Return the XML-RPC serialization of the boolean value
     *
     * @return string
     */
    public function saveXML()
    {
        if (! $this->_as_xml) {   // The XML was not generated yet
            $dom   = new DOMDocument('1.0', 'UTF-8');
            $value = $dom->appendChild($dom->createElement('value'));
            $type  = $value->appendChild($dom->createElement($this->_type));
            $type->appendChild($dom->createTextNode($this->_value));

            $this->_as_dom = $value;
            $this->_as_xml = $this->_stripXmlDeclaration($dom);
        }

        return $this->_as_xml;
    }
}

