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
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Date.php 2498 2006-12-23 22:13:38Z thomas $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/** Zend_Locale */
require_once 'Zend/Locale.php';

/** Zend_Translate_Exception */
require_once 'Zend/Translate/Exception.php';

/**
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Translate_Adapter {
    /**
     * Current locale/language
     *
     * @var string|null
     */
    protected $_locale;

    /**
     * Table of all supported languages
     *
     * @var array
     */
    protected $_languages = array();

    /**
     * Array with all options, each adapter can have own additional options
     *
     * @var array
     */
    protected $_options = array('clear' => false);

    /**
     * Translation table
     *
     * @var array
     */
    protected $_translate = array();


    /**
     * Generates the adapter
     *
     * @param  string|array        $data      Translation data for this adapter
     * @param  string|Zend_Locale  $locale    OPTIONAL Locale/Language to set, identical with Locale identifiers
     *                                        see Zend_Locale for more information
     * @param  string|array        $options   Options for the adaptor
     * @throws Zend_Translate_Exception
     */
    public function __construct($data, $locale = null, array $options = array())
    {
        if ($locale === null) {
            $locale = new Zend_Locale();
        }

        $this->addTranslation($data, $locale, $options);
        $this->setLocale($locale);
    }


    /**
     * Sets new adapter options
     *
     * @param  array  $options  Adapter options
     * @throws Zend_Translate_Exception
     */
    public function setOptions(array $options = array())
    {
        foreach ($options as $key => $option) {
            $this->_options[strtolower($key)] = $option;
        }
    }

    /**
     * Returns the adapters name and it's options
     *
     * @param  string|null  $optionKey  String returns this option
     *                                  null returns all options
     * @return integer|string|array
     */
    public function getOptions($optionKey = null)
    {
        if ($optionKey === null) {
            return $this->_options;
        }
        if (array_key_exists(strtolower($optionKey), $this->_options)) {
            return $this->_options[strtolower($optionKey)];
        }
        return null;
    }


    /**
     * Gets locale
     *
     * @return Zend_Locale|null
     */
    public function getLocale()
    {
        return $this->_locale;
    }


    /**
     * Sets locale
     *
     * @param  string|Zend_Locale  $locale  Locale to set
     * @throws Zend_Translate_Exception
     */
    public function setLocale($locale)
    {
        if ($locale instanceof Zend_Locale) {
            $locale = $locale->toString();
        } else if (!$locale = Zend_Locale::isLocale($locale)) {
            throw new Zend_Translate_Exception("The given Language ({$locale}) does not exist");
        }

        if (!in_array($locale, $this->_languages)) {
            throw new Zend_Translate_Exception("Language ({$locale}) has to be added before it can be used.");
        }

        $this->_locale = $locale;
    }


    /**
     * Returns the avaiable languages from this adapter
     *
     * @return array
     */
    public function getList()
    {
        return $this->_languages;
    }


    /**
     * Is the wished language avaiable ?
     *
     * @param  string|Zend_Locale  $locale  Language to search for, identical with locale identifier,
     *                                      see Zend_Locale for more information
     * @return boolean
     */
    public function isAvailable($locale)
    {
        if ($locale instanceof Zend_Locale) {
            $locale = $locale->toString();
        }

        return in_array($locale, $this->_languages);
    }

    /**
     * Load translation data
     *
     * @param  mixed               $data
     * @param  string|Zend_Locale  $locale
     * @param  array               $options
     */
    abstract protected function _loadTranslationData($data, $locale, array $options = array());

    /**
     * Add translation data
     *
     * It may be a new language or additional data for existing language
     * If $clear parameter is true, then translation data for specified
     * language is replaced and added otherwise
     *
     * @param  array|string          $data    Translation data
     * @param  string|Zend_Locale    $locale  Locale/Language to add data for, identical with locale identifier,
     *                                        see Zend_Locale for more information
     * @param  array                 $options OPTIONAL Option for this Adapter
     * @throws Zend_Translate_Exception
     */
    public function addTranslation($data, $locale, array $options = array())
    {
        if (!$locale = Zend_Locale::isLocale($locale)) {
            throw new Zend_Translate_Exception("The given Language ({$locale}) does not exist");
        }

        if (!in_array($locale, $this->_languages)) {
            $this->_languages[$locale] = $locale;
        }

        $this->_loadTranslationData($data, $locale, $options);
    }


    /**
     * Translates the given string
     * returns the translation
     *
     * @param  string              $messageId  Translation string
     * @param  string|Zend_Locale  $locale     OPTIONAL Locale/Language to use, identical with locale identifier,
     *                                         see Zend_Locale for more information
     * @return string
     */
    public function translate($messageId, $locale = null)
    {
        if ($locale === null) {
            $locale = $this->_locale;
        } else {
            if (!$locale = Zend_Locale::isLocale($locale)) {
                // language does not exist, return original string
                return $messageId;
            }
        }

        if ((array_key_exists($locale, $this->_translate)) and
            (array_key_exists($messageId, $this->_translate[$locale]))) {
            // return original translation
            return $this->_translate[$locale][$messageId];
        } else if (strlen($locale) != 2) {
            // faster than creating a new locale and separate the leading part
            $locale = substr($locale, 0, -strlen(strrchr($locale, '_')));

            if ((array_key_exists($locale, $this->_translate)) and
                (array_key_exists($messageId, $this->_translate[$locale]))) {
                // return regionless translation (en_US -> en)
                return $this->_translate[$locale][$messageId];
            }
        }

        // no translation found, return original
        return $messageId;
    }


    /**
     * Checks if a string is translated within the source or not
     * returns boolean
     *
     * @param  string              $messageId  Translation string
     * @param  boolean             $original   OPTIONAL Allow translation only for original language
     *                                         when true, a translation for 'en_US' would give false when it can
     *                                         be translated with 'en' only
     * @param  string|Zend_Locale  $locale     OPTIONAL Locale/Language to use, identical with locale identifier,
     *                                         see Zend_Locale for more information
     * @return boolean
     */
    public function isTranslated($messageId, $original = false, $locale = null)
    {
        if (($original !== false) and ($original !== true)) {
            $locale = $original;
            $original = false;
        }
        if ($locale === null) {
            $locale = $this->_locale;
        } else {
            if (!$locale = Zend_Locale::isLocale($locale)) {
                // language does not exist, return original string
                return false;
            }
        }

        if ((array_key_exists($locale, $this->_translate)) and
            (array_key_exists($messageId, $this->_translate[$locale]))) {
            // return original translation
            return true;
        } else if ((strlen($locale) != 2) and ($original === false)) {
            // faster than creating a new locale and separate the leading part
            $locale = substr($locale, 0, -strlen(strrchr($locale, '_')));

            if ((array_key_exists($locale, $this->_translate)) and
                (array_key_exists($messageId, $this->_translate[$locale]))) {
                // return regionless translation (en_US -> en)
                return true;
            }
        }

        // no translation found, return original
        return false;
    }


    /**
     * Returns the adapter name
     *
     * @return string
     */
    abstract public function toString();
}
