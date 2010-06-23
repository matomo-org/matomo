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
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Ean13.php 16223 2009-06-21 20:04:53Z thomas $
 */


/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Barcode_Ean13 extends Zend_Validate_Abstract
{
    /**
     * Validation failure message key for when the value is
     * an invalid barcode
     */
    const INVALID = 'invalid';

    /**
     * Validation failure message key for when the value is
     * not 13 characters long
     */
    const INVALID_LENGTH = 'invalidLength';

    /**
     * Validation failure message key for when the value
     * does not only contain numeric characters
     */
    const NOT_NUMERIC = 'ean13NotNumeric';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID        => "'%value%' is an invalid EAN-13 barcode",
        self::INVALID_LENGTH => "'%value%' should be 13 characters",
        self::NOT_NUMERIC    => "'%value%' should contain only numeric characters",
    );

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value contains a valid barcode
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value) || !ctype_digit($value)) {
            $this->_error(self::NOT_NUMERIC);
            return false;
        }

        $this->_setValue($value);
        if (strlen($value) !== 13) {
            $this->_error(self::INVALID_LENGTH);
            return false;
        }

        $barcode = strrev(substr($value, 0, -1));
        $oddSum  = 0;
        $evenSum = 0;

        for ($i = 0; $i < 12; $i++) {
            if ($i % 2 === 0) {
                $oddSum += $barcode[$i] * 3;
            } elseif ($i % 2 === 1) {
                $evenSum += $barcode[$i];
            }
        }

        $calculation = ($oddSum + $evenSum) % 10;
        $checksum    = ($calculation === 0) ? 0 : 10 - $calculation;

        if ($value[12] != $checksum) {
            $this->_error(self::INVALID);
            return false;
        }

        return true;
    }
}
