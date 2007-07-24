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
 * Zend_XmlRpc_Value
 */
require_once 'Zend/XmlRpc/Value.php';


/**
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_XmlRpc_Value_Collection extends Zend_XmlRpc_Value
{

    /**
     * Set the value of a collection type (array and struct) native types
     *
     * @param array $value
     */
    public function __construct($value)
    {
        $values = (array)$value;   // Make sure that the value is an array
        foreach ($values as $key => $value) {
            // If the elements of the given array are not Zend_XmlRpc_Value objects,
            // we need to convert them as such (using auto-detection from PHP value)
            if (!$value instanceof parent) {
                $value = self::getXmlRpcValue($value, self::AUTO_DETECT_TYPE);
            }
            $this->_value[$key] = $value;
        }
    }


    /**
     * Return the value of this object, convert the XML-RPC native collection values into a PHP array
     *
     * @return arary
     */
    public function getValue()
    {
        $values = (array)$this->_value;
        foreach ($values as $key => $value) {
            /* @var $value Zend_XmlRpc_Value */

            if (!$value instanceof parent) {
                throw new Zend_Xml_Rpc_Value_Exception('Values of '. get_class($this) .' type must be Zend_XmlRpc_Value objects');
            }
            $values[$key] = $value->getValue();
        }
        return $values;
    }

}

