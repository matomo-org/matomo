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
 * @version    $Id: Ean13.php 18028 2009-09-08 20:52:23Z thomas $
 */

/**
 * @see Zend_Validate_Barcode_AdapterInterface
 */
require_once 'Zend/Validate/Barcode/AdapterInterface.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Validate_Barcode_AdapterAbstract
    implements Zend_Validate_Barcode_AdapterInterface
{
    /**
     * Allowed barcode lengths
     * @var integer|array|string
     */
    protected $_length;

    /**
     * Allowed barcode characters
     * @var string
     */
    protected $_characters;

    /**
     * Callback to checksum function
     * @var string|array
     */
    protected $_checksum;

    /**
     * Is a checksum value included?
     * @var boolean
     */
    protected $_hasChecksum = true;

    /**
     * Checks the length of a barcode
     *
     * @param  string $value The barcode to check for proper length
     * @return boolean
     */
    public function checkLength($value)
    {
        if (!is_string($value)) {
            return false;
        }

        $fixum  = strlen($value);
        $found  = false;
        $length = $this->getLength();
        if (is_array($length)) {
            foreach ($length as $value) {
                if ($fixum == $value) {
                    $found = true;
                }

                if ($value == -1) {
                    $found = true;
                }
            }
        } elseif ($fixum == $length) {
            $found = true;
        } elseif ($length == -1) {
            $found = true;
        } elseif ($length == 'even') {
            $count = $fixum % 2;
            $found = ($count == 0) ? true : false;
        } elseif ($length == 'odd') {
            $count = $fixum % 2;
            $found = ($count == 1) ? true : false;
        }

        return $found;
    }

    /**
     * Checks for allowed characters within the barcode
     *
     * @param  string $value The barcode to check for allowed characters
     * @return boolean
     */
    public function checkChars($value)
    {
        if (!is_string($value)) {
            return false;
        }

        $characters = $this->getCharacters();
        if ($characters == 128) {
            for ($x = 0; $x < 128; ++$x) {
                $value = str_replace(chr($x), '', $value);
            }
        } else {
            $chars = str_split($characters);
            foreach ($chars as $char) {
                $value = str_replace($char, '', $value);
            }
        }

        if (strlen($value) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Validates the checksum
     *
     * @param  string $value The barcode to check the checksum for
     * @return boolean
     */
    public function checksum($value)
    {
        $checksum = $this->getChecksum();
        if (!empty($checksum)) {
            if (method_exists($this, $checksum)) {
                return call_user_func(array($this, $checksum), $value);
            }
        }

        return false;
    }

    /**
     * Returns the allowed barcode length
     *
     * @return string
     */
    public function getLength()
    {
        return $this->_length;
    }

    /**
     * Returns the allowed characters
     *
     * @return integer|string
     */
    public function getCharacters()
    {
        return $this->_characters;
    }

    /**
     * Returns the checksum function name
     *
     */
    public function getChecksum()
    {
        return $this->_checksum;
    }

    /**
     * Returns if barcode uses checksum
     *
     * @return boolean
     */
    public function getCheck()
    {
        return $this->_hasChecksum;
    }

    /**
     * Sets the checksum validation
     *
     * @param  boolean $check
     * @return Zend_Validate_Barcode_AdapterAbstract
     */
    public function setCheck($check)
    {
        $this->_hasChecksum = (boolean) $check;
        return $this;
    }

    /**
     * Validates the checksum (Modulo 10)
     * GTIN implementation factor 3
     *
     * @param  string $value The barcode to validate
     * @return boolean
     */
    protected function _gtin($value)
    {
        $barcode = substr($value, 0, -1);
        $sum     = 0;
        $length  = strlen($barcode) - 1;

        for ($i = 0; $i <= $length; $i++) {
            if (($i % 2) === 0) {
                $sum += $barcode[$length - $i] * 3;
            } else {
                $sum += $barcode[$length - $i];
            }
        }

        $calc     = $sum % 10;
        $checksum = ($calc === 0) ? 0 : (10 - $calc);
        if ($value[$length + 1] != $checksum) {
            return false;
        }

        return true;
    }

    /**
     * Validates the checksum (Modulo 10)
     * IDENTCODE implementation factors 9 and 4
     *
     * @param  string $value The barcode to validate
     * @return boolean
     */
    protected function _identcode($value)
    {
        $barcode = substr($value, 0, -1);
        $sum     = 0;
        $length  = strlen($value) - 2;

        for ($i = 0; $i <= $length; $i++) {
            if (($i % 2) === 0) {
                $sum += $barcode[$length - $i] * 4;
            } else {
                $sum += $barcode[$length - $i] * 9;
            }
        }

        $calc     = $sum % 10;
        $checksum = ($calc === 0) ? 0 : (10 - $calc);
        if ($value[$length + 1] != $checksum) {
            return false;
        }

        return true;
    }

    /**
     * Validates the checksum (Modulo 10)
     * CODE25 implementation factor 3
     *
     * @param  string $value The barcode to validate
     * @return boolean
     */
    protected function _code25($value)
    {
        $barcode = substr($value, 0, -1);
        $sum     = 0;
        $length  = strlen($barcode) - 1;

        for ($i = 0; $i <= $length; $i++) {
            if (($i % 2) === 0) {
                $sum += $barcode[$i] * 3;
            } else {
                $sum += $barcode[$i];
            }
        }

        $calc     = $sum % 10;
        $checksum = ($calc === 0) ? 0 : (10 - $calc);
        if ($value[$length + 1] != $checksum) {
            return false;
        }

        return true;
    }

    /**
     * Validates the checksum ()
     * POSTNET implementation
     *
     * @param  string $value The barcode to validate
     * @return boolean
     */
    protected function _postnet($value)
    {
        $checksum = substr($value, -1, 1);
        $values   = str_split(substr($value, 0, -1));

        $check = 0;
        foreach($values as $row) {
            $check += $row;
        }

        $check %= 10;
        $check = 10 - $check;
        if ($check == $checksum) {
            return true;
        }

        return false;
    }
}
