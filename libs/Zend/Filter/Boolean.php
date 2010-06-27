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
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id:$
 */

/**
 * @see Zend_Filter_Interface
 */
// require_once 'Zend/Filter/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Boolean implements Zend_Filter_Interface
{
    const BOOLEAN      = 1;
    const INTEGER      = 2;
    const FLOAT        = 4;
    const STRING       = 8;
    const ZERO         = 16;
    const EMPTY_ARRAY  = 32;
    const NULL         = 64;
    const PHP          = 127;
    const FALSE_STRING = 128;
    const YES          = 256;
    const ALL          = 511;

    protected $_constants = array(
        self::BOOLEAN      => 'boolean',
        self::INTEGER      => 'integer',
        self::FLOAT        => 'float',
        self::STRING       => 'string',
        self::ZERO         => 'zero',
        self::EMPTY_ARRAY  => 'array',
        self::NULL         => 'null',
        self::PHP          => 'php',
        self::FALSE_STRING => 'false',
        self::YES          => 'yes',
        self::ALL          => 'all',
    );

    /**
     * Internal type to detect
     *
     * @var integer
     */
    protected $_type = self::PHP;

    /**
     * Internal locale
     *
     * @var array
     */
    protected $_locale = array('auto');

    /**
     * Internal mode
     *
     * @var boolean
     */
    protected $_casting = true;

    /**
     * Constructor
     *
     * @param string|array|Zend_Config $options OPTIONAL
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $options = func_get_args();
            $temp    = array();
            if (!empty($options)) {
                $temp['type'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['casting'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['locale'] = array_shift($options);
            }

            $options = $temp;
        }

        if (array_key_exists('type', $options)) {
            $this->setType($options['type']);
        }

        if (array_key_exists('casting', $options)) {
            $this->setCasting($options['casting']);
        }

        if (array_key_exists('locale', $options)) {
            $this->setLocale($options['locale']);
        }
    }

    /**
     * Returns the set null types
     *
     * @return int
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set the null types
     *
     * @param  integer|array $type
     * @throws Zend_Filter_Exception
     * @return Zend_Filter_Boolean
     */
    public function setType($type = null)
    {
        if (is_array($type)) {
            $detected = 0;
            foreach($type as $value) {
                if (is_int($value)) {
                    $detected += $value;
                } elseif (in_array($value, $this->_constants)) {
                    $detected += array_search($value, $this->_constants);
                }
            }

            $type = $detected;
        } elseif (is_string($type) && in_array($type, $this->_constants)) {
            $type = array_search($type, $this->_constants);
        }

        if (!is_int($type) || ($type < 0) || ($type > self::ALL)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Unknown type');
        }

        $this->_type = $type;
        return $this;
    }

    /**
     * Returns the set locale
     *
     * @return array
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Set the locales which are accepted
     *
     * @param  string|array|Zend_Locale $locale
     * @throws Zend_Filter_Exception
     * @return Zend_Filter_Boolean
     */
    public function setLocale($locale = null)
    {
        if (is_string($locale)) {
            $locale = array($locale);
        } elseif ($locale instanceof Zend_Locale) {
            $locale = array($locale->toString());
        } elseif (!is_array($locale)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Locale has to be string, array or an instance of Zend_Locale');
        }

        // require_once 'Zend/Locale.php';
        foreach ($locale as $single) {
            if (!Zend_Locale::isLocale($single)) {
                // require_once 'Zend/Filter/Exception.php';
                throw new Zend_Filter_Exception("Unknown locale '$single'");
            }
        }

        $this->_locale = $locale;
        return $this;
    }

    /**
     * Returns the casting option
     *
     * @return boolean
     */
    public function getCasting()
    {
        return $this->_casting;
    }

    /**
     * Set the working mode
     *
     * @param  boolean $locale When true this filter works like cast
     *                         When false it recognises only true and false
     *                         and all other values are returned as is
     * @throws Zend_Filter_Exception
     * @return Zend_Filter_Boolean
     */
    public function setCasting($casting = true)
    {
        $this->_casting = (boolean) $casting;
        return $this;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns a boolean representation of $value
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $type    = $this->getType();
        $casting = $this->getCasting();

        // STRING YES (Localized)
        if ($type >= self::YES) {
            $type -= self::YES;
            if (is_string($value)) {
                // require_once 'Zend/Locale.php';
                $locales = $this->getLocale();
                foreach ($locales as $locale) {
                    if ($this->_getLocalizedQuestion($value, false, $locale) === false) {
                        return false;
                    }

                    if (!$casting && ($this->_getLocalizedQuestion($value, true, $locale) === true)) {
                        return true;
                    }
                }
            }
        }

        // STRING FALSE ('false')
        if ($type >= self::FALSE_STRING) {
            $type -= self::FALSE_STRING;
            if (is_string($value) && (strtolower($value) == 'false')) {
                return false;
            }

            if ((!$casting) && is_string($value) && (strtolower($value) == 'true')) {
                return true;
            }
        }

        // NULL (null)
        if ($type >= self::NULL) {
            $type -= self::NULL;
            if (is_null($value)) {
                return false;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type >= self::EMPTY_ARRAY) {
            $type -= self::EMPTY_ARRAY;
            if (is_array($value) && ($value == array())) {
                return false;
            }
        }

        // ZERO ('0')
        if ($type >= self::ZERO) {
            $type -= self::ZERO;
            if (is_string($value) && ($value == '0')) {
                return false;
            }

            if ((!$casting) && (is_string($value)) && ($value == '1')) {
                return true;
            }
        }

        // STRING ('')
        if ($type >= self::STRING) {
            $type -= self::STRING;
            if (is_string($value) && ($value == '')) {
                return false;
            }
        }

        // FLOAT (0.0)
        if ($type >= self::FLOAT) {
            $type -= self::FLOAT;
            if (is_float($value) && ($value == 0.0)) {
                return false;
            }

            if ((!$casting) && is_float($value) && ($value == 1.0)) {
                return true;
            }
        }

        // INTEGER (0)
        if ($type >= self::INTEGER) {
            $type -= self::INTEGER;
            if (is_int($value) && ($value == 0)) {
                return false;
            }

            if ((!$casting) && is_int($value) && ($value == 1)) {
                return true;
            }
        }

        // BOOLEAN (false)
        if ($type >= self::BOOLEAN) {
            $type -= self::BOOLEAN;
            if (is_bool($value)) {
                return $value;
            }
        }

        if ($casting) {
            return true;
        }

        return $value;
    }

    /**
     * Determine the value of a localized string, and compare it to a given value
     *
     * @param  string $value
     * @param  boolean $yes
     * @param  array $locale
     * @return boolean
     */
    protected function _getLocalizedQuestion($value, $yes, $locale)
    {
        if ($yes == true) {
            $question = 'yes';
            $return   = true;
        } else {
            $question = 'no';
            $return   = false;
        }
        $str = Zend_Locale::getTranslation($question, 'question', $locale);
        $str = explode(':', $str);
        if (!empty($str)) {
            foreach($str as $no) {
                if (($no == $value) || (strtolower($no) == strtolower($value))) {
                    return $return;
                }
            }
        }
    }
}
