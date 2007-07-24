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

/** Zend_Translate_Adapter */
require_once 'Zend/Translate/Adapter.php';


/**
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Translate_Adapter_Qt extends Zend_Translate_Adapter {
    // Internal variables
    private $_file        = false;
    private $_cleared     = array();
    private $_transunit   = null;
    private $_source      = null;
    private $_target      = null;
    private $_scontent    = null;
    private $_tcontent    = null;
    private $_stag        = false;
    private $_ttag        = true;
    
    /**
     * Generates the Qt adapter
     * This adapter reads with php's xml_parser
     *
     * @param  string              $data     Translation data
     * @param  string|Zend_Locale  $locale   OPTIONAL Locale/Language to set, identical with locale identifier,
     *                                       see Zend_Locale for more information
     */
    public function __construct($data, $locale = null)
    {
        parent::__construct($data, $locale);
    }


    /**
     * Load translation data (QT file reader)
     *
     * @param  string  $locale    Locale/Language to add data for, identical with locale identifier,
     *                            see Zend_Locale for more information
     * @param  string  $filename  QT file to add, full path must be given for access
     * @param  array   $option    OPTIONAL Options to use
     * @throws Zend_Translation_Exception
     */
    protected function _loadTranslationData($filename, $locale, array $options = array())
    {
        $options = array_merge($this->_options, $options);

        if ($options['clear']) {
            $this->_translate = array();
        }

        if (!is_readable($filename)) {
            throw new Zend_Translate_Exception('Translation file \'' . $filename . '\' is not readable.');
        }

        $this->_target = $locale;
        
        $this->_file = xml_parser_create();
        xml_set_object($this->_file, $this);
        xml_parser_set_option($this->_file, XML_OPTION_CASE_FOLDING, 0);
        xml_set_element_handler($this->_file, "_startElement", "_endElement");
        xml_set_character_data_handler($this->_file, "_contentElement");

        if (!xml_parse($this->_file, file_get_contents($filename))) {
            throw new Zend_Translate_Exception(sprintf('XML error: %s at line %d', 
                      xml_error_string(xml_get_error_code($this->_file)),
                      xml_get_current_line_number($this->_file)));
            xml_parser_free($this->_file);
        }
    }

    private function _startElement($file, $name, $attrib)
    {
        switch(strtolower($name)) {
            case 'ts':
                $this->_translate[$this->_target] = array();
                break;
            case 'message':
                $this->_source = null;
                $this->_stag = false;
                $this->_ttag = false;
                $this->_scontent = null;
                $this->_tcontent = null;
                break;
            case 'source':
                $this->_stag = true;
                break;
            case 'translation':
                $this->_ttag = true;
                break;
            default:
                break;
        }
    }

    private function _endElement($file, $name)
    {
        switch (strtolower($name)) {
            case 'source':
                $this->_stag = false;
                break;
            case 'translation':
                if (!empty($this->_scontent) and !empty($this->_tcontent) or 
                    !array_key_exists($this->_scontent, $this->_translate[$this->_target])) {
                    $this->_translate[$this->_target][$this->_scontent] = $this->_tcontent;
                }
                $this->_ttag = false;
                break;
            case 'message':
            default:
                break;
        }
    }

    private function _contentElement($file, $data)
    {
        if ($this->_stag === true) {
            $this->_scontent .= $data;
        }

        if ($this->_ttag === true) {
            $this->_tcontent .= $data;
        }
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return "Qt";
    }
}
