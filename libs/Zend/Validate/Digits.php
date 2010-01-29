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
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Digits.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Digits extends Zend_Validate_Abstract
{
    const NOT_DIGITS   = 'notDigits';
    const STRING_EMPTY = 'digitsStringEmpty';
    const INVALID      = 'digitsInvalid';

    /**
     * Digits filter used for validation
     *
     * @var Zend_Filter_Digits
     */
    protected static $_filter = null;

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_DIGITS   => "'%value%' contains not only digit characters",
        self::STRING_EMPTY => "'%value%' is an empty string",
        self::INVALID      => "Invalid type given, value should be string, integer or float",
    );

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value only contains digit characters
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue((string) $value);

        if ('' === $this->_value) {
            $this->_error(self::STRING_EMPTY);
            return false;
        }

        if (null === self::$_filter) {
            require_once 'Zend/Filter/Digits.php';
            self::$_filter = new Zend_Filter_Digits();
        }

        if ($this->_value !== self::$_filter->filter($this->_value)) {
            $this->_error(self::NOT_DIGITS);
            return false;
        }

        return true;
    }
}
