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
 * @version    $Id: Int.php 20532 2010-01-22 20:18:23Z thomas $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @see Zend_Locale_Format
 */
require_once 'Zend/Locale/Format.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Int extends Zend_Validate_Abstract
{
    const INVALID = 'intInvalid';
    const NOT_INT = 'notInt';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => "Invalid type given, value should be string or integer",
        self::NOT_INT => "'%value%' does not appear to be an integer",
    );

    protected $_locale;

    /**
     * Constructor for the integer validator
     *
     * @param string|Zend_Config|Zend_Locale $locale
     */
    public function __construct($locale = null)
    {
        if ($locale instanceof Zend_Config) {
            $locale = $locale->toArray();
        }

        if (is_array($locale)) {
            if (array_key_exists('locale', $locale)) {
                $locale = $locale['locale'];
            } else {
                $locale = null;
            }
        }

        if (empty($locale)) {
            require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Locale')) {
                $locale = Zend_Registry::get('Zend_Locale');
            }
        }

        if ($locale !== null) {
            $this->setLocale($locale);
        }
    }

    /**
     * Returns the set locale
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Sets the locale to use
     *
     * @param string|Zend_Locale $locale
     */
    public function setLocale($locale = null)
    {
        require_once 'Zend/Locale.php';
        $this->_locale = Zend_Locale::findLocale($locale);
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is a valid integer
     *
     * @param  string|integer $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        if (is_int($value)) {
            return true;
        }

        $this->_setValue($value);
        if ($this->_locale === null) {
            $locale        = localeconv();
            $valueFiltered = str_replace($locale['decimal_point'], '.', $value);
            $valueFiltered = str_replace($locale['thousands_sep'], '', $valueFiltered);

            if (strval(intval($valueFiltered)) != $valueFiltered) {
                $this->_error(self::NOT_INT);
                return false;
            }

        } else {
            try {
                if (!Zend_Locale_Format::isInteger($value, array('locale' => $this->_locale))) {
                    $this->_error(self::NOT_INT);
                    return false;
                }
            } catch (Zend_Locale_Exception $e) {
                $this->_error(self::NOT_INT);
                return false;
            }
        }

        return true;
    }
}
