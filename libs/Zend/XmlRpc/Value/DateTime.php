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
class Zend_XmlRpc_Value_DateTime extends Zend_XmlRpc_Value_Scalar
{

    /**
     * Set the value of a dateTime.iso8601 native type 
     *
     * The value is in iso8601 format, minus any timezone information or dashes
     *
     * @param mixed $value Integer of the unix timestamp or any string that can be parsed 
     *                     to a unix timestamp using the PHP strtotime() function
     */
    public function __construct($value)
    {
        $this->_type = self::XMLRPC_TYPE_DATETIME;

        // If the value is not numeric, we try to convert it to a timestamp (using the strtotime function)
        if (is_numeric($value)) {   // The value is numeric, we make sure it is an integer
            $value = (int)$value;
        } else {
            $value = strtotime($value);
            if ($value === false || $value == -1) { // cannot convert the value to a timestamp
                throw new Zend_XmlRpc_Value_Exception('Cannot convert given value \''. $value .'\' to a timestamp');
            }
        }
        $value = date('c', $value); // Convert the timestamp to iso8601 format

        // Strip out TZ information and dashes
        $value = preg_replace('/(\+|-)\d{2}:\d{2}$/', '', $value);
        $value = str_replace('-', '', $value);

        $this->_value = $value;
    }

    /**
     * Return the value of this object as iso8601 dateTime value 
     *
     * @return int As a Unix timestamp
     */
    public function getValue()
    {
        return $this->_value;
    }

}

