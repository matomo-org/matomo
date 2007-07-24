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


/**
 * Zend_XmlRpc_Value_Scalar
 */
require_once 'Zend/XmlRpc/Value/Scalar.php';

/**
 * @package    Zend_XmlRpc
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */
class Zend_XmlRpc_Value_String extends Zend_XmlRpc_Value_Scalar
{

    /**
     * Set the value of a string native type
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $this->_type = self::XMLRPC_TYPE_STRING;
        
        // Make sure this value is string and all XML characters are encoded
        $this->_value = $this->_xml_entities($value);    
    }

    /**
     * Return the value of this object, convert the XML-RPC native string value into a PHP string
     * Decode all encoded risky XML entities back to normal characters
     *
     * @return string
     */
    public function getValue()
    {
        return html_entity_decode($this->_value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Make sure a string will be safe for XML, convert risky characters to HTML entities
     *
     * @param string $str
     * @return string
     */
    private function _xml_entities($str)
    {
        return htmlentities($str, ENT_QUOTES, 'UTF-8');
    }

}

