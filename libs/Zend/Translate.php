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

/** Zend_Translate_Exception */
require_once 'Zend/Translate/Exception.php';

/** Zend_Locale */
require_once 'Zend/Locale.php';


/**
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Translate {
    /**
     * Adapter names constants
     */
    const AN_ARRAY   = 'array';
    const AN_CSV     = 'csv';
    const AN_GETTEXT = 'gettext';
    const AN_QT      = 'qt';
    const AN_TMX     = 'tmx';
    const AN_XLIFF   = 'xliff';

    /**
     * Adapter
     *
     * @var Zend_Translate_Adapter
     */
    private $_adapter;


    /**
     * Generates the standard translation object
     *
     * @param  string              $adapter  Adapter to use
     * @param  array               $options  Options for this adapter to set
     *                                       Depends on the Adapter
     * @param  string|Zend_Locale  $locale   OPTIONAL locale to use
     * @throws Zend_Translate_Exception
     */
    public function __construct($adapter, $options, $locale = null)
    {
        $this->setAdapter($adapter, $options, $locale);
    }


    /**
     * Sets a new adapter
     *
     * @param  string              $adapter  Adapter to use
     * @param  string|array        $data     Translation data
     * @param  string|Zend_Locale  $locale   OPTIONAL locale to use
     * @param  array               $options  OPTIONAL Options to use
     * @throws Zend_Translate_Exception
     */
    public function setAdapter($adapter, $data, $locale = null, array $options = array())
    {
        switch (strtolower($adapter)) {
            case 'array':
                /** Zend_Translate_Adapter_Array */
                require_once('Zend/Translate/Adapter/Array.php');
                $this->_adapter = new Zend_Translate_Adapter_Array($data, $locale, $options);
                break;
            case 'csv':
                /** Zend_Translate_Adapter_Csv */
                require_once('Zend/Translate/Adapter/Csv.php');
                $this->_adapter = new Zend_Translate_Adapter_Csv($data, $locale, $options);
                break;
            case 'gettext':
                /** Zend_Translate_Adapter_Gettext */
                require_once('Zend/Translate/Adapter/Gettext.php');
                $this->_adapter = new Zend_Translate_Adapter_Gettext($data, $locale, $options);
                break;
            case 'qt':
                /** Zend_Translate_Adapter_Qt */
                require_once('Zend/Translate/Adapter/Qt.php');
                $this->_adapter = new Zend_Translate_Adapter_Qt($data, $locale, $options);
                break;
            case 'tmx':
                /** Zend_Translate_Adapter_Tmx */
                require_once('Zend/Translate/Adapter/Tmx.php');
                $this->_adapter = new Zend_Translate_Adapter_Tmx($data, $locale, $options);
                break;
            case 'xliff':
                /** Zend_Translate_Adapter_Xliff */
                require_once('Zend/Translate/Adapter/Xliff.php');
                $this->_adapter = new Zend_Translate_Adapter_Xliff($data, $locale, $options);
                break;
            case 'sql':
            case 'tbx':
            case 'xmltm':
                throw new Zend_Translate_Exception("adapter '$adapter' is not supported for now");
                break;
            default:
                throw new Zend_Translate_Exception('no adapter selected');
                break;
        }
    }


    /**
     * Returns the adapters name and it's options
     *
     * @return Zend_Translate_Adapter
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }


    /**
     * Add translation data.
     *
     * It may be a new language or additional data for existing language
     * If $clear parameter is true, then translation data for specified
     * language is replaced and added otherwise
     *
     * @param  string|array        $data     Translation data
     * @param  string|Zend_Locale  $locale   Locale/Language to add to this adapter
     * @param  array               $options  OPTIONAL Options to use
     */
    public function addTranslation($data, $locale, array $options = array())
    {
        $this->_adapter->addTranslation($data, $locale, $options);
    }


    /**
     * Sets a new locale/language
     *
     * @param  string|Zend_Locale  $locale  Locale/Language to set for translations
     */
    public function setLocale($locale)
    {
        $this->_adapter->setLocale($locale);
    }


    /**
     * Returns the actual set locale/language
     *
     * @return Zend_Locale|null
     */
    public function getLocale()
    {
        return $this->_adapter->getLocale();
    }


    /**
     * Returns all avaiable locales/languages from this adapter
     *
     * @return array
     */
    public function getList()
    {
        return $this->_adapter->getList();
    }


    /**
     * Is the wished language avaiable ?
     *
     * @param  string|Zend_Locale  $locale  Is the locale/language avaiable
     * @return boolean
     */
    public function isAvailable($locale)
    {
        return $this->_adapter->isAvailable($locale);
    }


    /**
     * Translate the given string
     *
     * @param  string              $messageId  Original to translate
     * @param  string|Zend_Locale  $locale     OPTIONAL locale/language to translate to
     * @return string
     */
    public function _($messageId, $locale = null)
    {
        return $this->_adapter->translate($messageId, $locale);
    }


    /**
     * Translate the given string
     *
     * @param  string              $messageId  Original to translate
     * @param  string|Zend_Locale  $locale     OPTIONAL locale/language to translate to
     * @return string
     */
    public function translate($messageId, $locale = null)
    {
        return $this->_adapter->translate($messageId, $locale);
    }


    /**
     * Checks if a given string can be translated
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
        return $this->_adapter->isTranslated($messageId, $original, $locale);
    }
}
