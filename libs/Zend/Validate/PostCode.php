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
 * @version    $Id: PostCode.php 21107 2010-02-19 21:40:22Z thomas $
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
class Zend_Validate_PostCode extends Zend_Validate_Abstract
{
    const INVALID  = 'postcodeInvalid';
    const NO_MATCH = 'postcodeNoMatch';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID  => "Invalid type given, value should be string or integer",
        self::NO_MATCH => "'%value%' does not appear to be an postal code",
    );

    /**
     * Locale to use
     *
     * @var string
     */
    protected $_locale;

    /**
     * Manual postal code format
     *
     * @var unknown_type
     */
    protected $_format;

    /**
     * Constructor for the integer validator
     *
     * Accepts either a string locale, a Zend_Locale object, or an array or
     * Zend_Config object containing the keys "locale" and/or "format".
     *
     * @param string|Zend_Locale|array|Zend_Config $options
     * @throws Zend_Validate_Exception On empty format
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (empty($options)) {
            require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Locale')) {
                $this->setLocale(Zend_Registry::get('Zend_Locale'));
            }
        } elseif (is_array($options)) {
            // Received
            if (array_key_exists('locale', $options)) {
                $this->setLocale($options['locale']);
            }

            if (array_key_exists('format', $options)) {
                $this->setFormat($options['format']);
            }
        } elseif ($options instanceof Zend_Locale || is_string($options)) {
            // Received Locale object or string locale
            $this->setLocale($options);
        }

        $format = $this->getFormat();
        if (empty($format)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Format has to be a not empty string");
        }
    }

    /**
     * Returns the set locale
     *
     * @return string|Zend_Locale The set locale
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Sets the locale to use
     *
     * @param string|Zend_Locale $locale
     * @throws Zend_Validate_Exception On unrecognised region
     * @throws Zend_Validate_Exception On not detected format
     * @return Zend_Validate_PostCode  Provides fluid interface
     */
    public function setLocale($locale = null)
    {
        require_once 'Zend/Locale.php';
        $this->_locale = Zend_Locale::findLocale($locale);
        $locale        = new Zend_Locale($this->_locale);
        $region        = $locale->getRegion();
        if (empty($region)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Unable to detect a region from the locale '$locale'");
        }

        $format = Zend_Locale::getTranslation(
            $locale->getRegion(),
            'postaltoterritory',
            $this->_locale
        );

        if (empty($format)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Unable to detect a format from the region '{$locale->getRegion()}'");
        }

        $this->setFormat($format);
        return $this;
    }

    /**
     * Returns the set postal code format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Sets a self defined postal format as regex
     *
     * @param string $format
     * @throws Zend_Validate_Exception On empty format
     * @return Zend_Validate_PostCode  Provides fluid interface
     */
    public function setFormat($format)
    {
        if (empty($format) || !is_string($format)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Format has to be a not empty string");
        }

        if ($format[0] !== '/') {
            $format = '/^' . $format;
        }

        if ($format[strlen($format) - 1] !== '/') {
            $format .= '$/';
        }

        $this->_format = $format;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is a valid postalcode
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue($value);
        if (!is_string($value) && !is_int($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $format = $this->getFormat();
        if (!preg_match($format, $value)) {
            $this->_error(self::NO_MATCH);
            return false;
        }

        return true;
    }
}
