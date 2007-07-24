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
class Zend_Translate_Adapter_Xliff extends Zend_Translate_Adapter {
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
     * Generates the xliff adapter
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
     * Load translation data (XLIFF file reader)
     *
     * @param  string  $locale    Locale/Language to add data for, identical with locale identifier,
     *                            see Zend_Locale for more information
     * @param  string  $filename  XLIFF file to add, full path must be given for access
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
            case 'file':
                $this->_source = $attrib['source-language'];
                $this->_target = $attrib['target-language'];
                $this->_translate[$this->_source] = array();
                $this->_translate[$this->_target] = array();
                break;
            case 'trans-unit':
                $this->_transunit = true;
                break;
            case 'source':
                if ($this->_transunit === true) {
                    $this->_scontent = null;
                    $this->_stag = true;
                    $this->_ttag = false;
                }
                break;
            case 'target':
                if ($this->_transunit === true) {
                    $this->_tcontent = null;
                    $this->_ttag = true;
                    $this->_stag = false;
                }
                break;
            default:
                break;
        }
    }

    private function _endElement($file, $name)
    {
        switch (strtolower($name)) {
            case 'trans-unit':
                $this->_transunit = null;
                $this->_scontent = null;
                $this->_tcontent = null;
                break;
            case 'source':
                if (!empty($this->_scontent) and !empty($this->_tcontent) or 
                    !array_key_exists($this->_scontent, $this->_translate[$this->_source])) {
                    $this->_translate[$this->_source][$this->_scontent] = $this->_scontent;
                }
                $this->_stag = false;
                break;
            case 'target':
                if (!empty($this->_scontent) and !empty($this->_tcontent) or 
                    !array_key_exists($this->_scontent, $this->_translate[$this->_source])) {
                    $this->_translate[$this->_target][$this->_scontent] = $this->_tcontent;
                }
                $this->_ttag = false;
                break;
            default:
                break;
        }
    }

    private function _contentElement($file, $data)
    {
        if (($this->_transunit !== null) and ($this->_source !== null) and ($this->_stag === true)) {
            $this->_scontent .= $data;
        }

        if (($this->_transunit !== null) and ($this->_target !== null) and ($this->_ttag === true)) {
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
        return "Xliff";
    }
}
