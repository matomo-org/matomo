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
 * @version    $Id: Isbn.php 20358 2010-01-17 19:03:49Z thomas $
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
class Zend_Validate_Isbn extends Zend_Validate_Abstract
{
    const AUTO    = 'auto';
    const ISBN10  = '10';
    const ISBN13  = '13';
    const INVALID = 'isbnInvalid';

    /**
     * Validation failure message template definitions.
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => "'%value%' is no valid ISBN number",
    );

    /**
     * Allowed type.
     *
     * @var string
     */
    protected $_type = self::AUTO;

    /**
     * Separator character.
     *
     * @var string
     */
    protected $_separator = '';

    /**
     * Set up options.
     *
     * @param  Zend_Config|array $options
     * @throws Zend_Validate_Exception When $options is not valid
     * @return void
     */
    public function __construct($options = array())
    {
        // prepare options
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (!is_array($options)) {
            /**
             * @see Zend_Validate_Exception
             */
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Invalid options provided.');
        }

        // set type
        if (array_key_exists('type', $options)) {
            $this->setType($options['type']);
        }

        // set separator
        if (array_key_exists('separator', $options)) {
            $this->setSeparator($options['separator']);
        }
    }

    /**
     * Detect input format.
     *
     * @return string
     */
    protected function _detectFormat()
    {
        // prepare separator and pattern list
        $sep      = quotemeta($this->_separator);
        $patterns = array();
        $lengths  = array();

        // check for ISBN-10
        if ($this->_type == self::ISBN10 || $this->_type == self::AUTO) {
            if (empty($sep)) {
                $pattern = '/^[0-9]{9}[0-9X]{1}$/';
                $length  = 10;
            } else {
                $pattern = "/^[0-9]{1,7}[{$sep}]{1}[0-9]{1,7}[{$sep}]{1}[0-9]{1,7}[{$sep}]{1}[0-9X]{1}$/";
                $length  = 13;
            }

            $patterns[$pattern] = self::ISBN10;
            $lengths[$pattern]  = $length;
        }

        // check for ISBN-13
        if ($this->_type == self::ISBN13 || $this->_type == self::AUTO) {
            if (empty($sep)) {
                $pattern = '/^[0-9]{13}$/';
                $length  = 13;
            } else {
                $pattern = "/^[0-9]{1,9}[{$sep}]{1}[0-9]{1,5}[{$sep}]{1}[0-9]{1,9}[{$sep}]{1}[0-9]{1,9}[{$sep}]{1}[0-9]{1}$/";
                $length  = 17;
            }

            $patterns[$pattern] = self::ISBN13;
            $lengths[$pattern]  = $length;
        }

        // check pattern list
        foreach ($patterns as $pattern => $type) {
            if ((strlen($this->_value) == $lengths[$pattern]) && preg_match($pattern, $this->_value)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Defined by Zend_Validate_Interface.
     *
     * Returns true if and only if $value contains a valid ISBN.
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        // save value
        $value = (string) $value;
        $this->_setValue($value);

        switch ($this->_detectFormat()) {
            case self::ISBN10:
                // sum
                $isbn10 = preg_replace('/[^0-9X]/', '', $value);
                $sum    = 0;
                for ($i = 0; $i < 9; $i++) {
                    $sum += (10 - $i) * $isbn10{$i};
                }

                // checksum
                $checksum = 11 - ($sum % 11);
                if ($checksum == 11) {
                    $checksum = '0';
                } elseif ($checksum == 10) {
                    $checksum = 'X';
                }
                break;

            case self::ISBN13:
                // sum
                $isbn13 = preg_replace('/[^0-9]/', '', $value);
                $sum    = 0;
                for ($i = 0; $i < 12; $i++) {
                    if ($i % 2 == 0) {
                        $sum += $isbn13{$i};
                    } else {
                        $sum += 3 * $isbn13{$i};
                    }
                }
                // checksum
                $checksum = 10 - ($sum % 10);
                if ($checksum == 10) {
                    $checksum = '0';
                }
                break;

            default:
                $this->_error(self::INVALID);
                return false;
        }

        // validate
        if (substr($this->_value, -1) != $checksum) {
            $this->_error(self::INVALID);
            return false;
        }
        return true;
    }

    /**
     * Set separator characters.
     *
     * It is allowed only empty string, hyphen and space.
     *
     * @param  string $separator
     * @throws Zend_Validate_Exception When $separator is not valid
     * @return Zend_Validate_Isbn Provides a fluent interface
     */
    public function setSeparator($separator)
    {
        // check separator
        if (!in_array($separator, array('-', ' ', ''))) {
            /**
             * @see Zend_Validate_Exception
             */
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Invalid ISBN separator.');
        }

        $this->_separator = $separator;
        return $this;
    }

    /**
     * Get separator characters.
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

    /**
     * Set allowed ISBN type.
     *
     * @param  string $type
     * @throws Zend_Validate_Exception When $type is not valid
     * @return Zend_Validate_Isbn Provides a fluent interface
     */
    public function setType($type)
    {
        // check type
        if (!in_array($type, array(self::AUTO, self::ISBN10, self::ISBN13))) {
            /**
             * @see Zend_Validate_Exception
             */
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Invalid ISBN type');
        }

        $this->_type = $type;
        return $this;
    }

    /**
     * Get allowed ISBN type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }
}
