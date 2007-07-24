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
class Zend_Translate_Adapter_Csv extends Zend_Translate_Adapter {

    /**
     * Generates the adapter
     *
     * @param  string              $data     Translation data
     * @param  string|Zend_Locale  $locale   OPTIONAL Locale/Language to set, identical with locale identifier,
     *                                       see Zend_Locale for more information
     * @param  array               $options  Options for this adapter
     */
    public function __construct($data, $locale = null, array $options = array())
    {
        $this->_options['separator'] = ";";
        $options = array_merge($this->_options, $options);

        parent::__construct($data, $locale, $options);
    }

    /**
     * Load translation data
     *
     * @param  string|array  $filename  Filename and full path to the translation source
     * @param  string        $locale    Locale/Language to add data for, identical with locale identifier,
     *                                  see Zend_Locale for more information
     * @param  array         $option    OPTIONAL Options to use
     */
    protected function _loadTranslationData($filename, $locale, array $options = array())
    {
        $options = array_merge($this->_options, $options);

        if ($options['clear']  ||  !isset($this->_translate[$locale])) {
            $this->_translate[$locale] = array();
        }

        $this->_file = @fopen($filename, 'rb');
        if (!$this->_file) {
            throw new Zend_Translate_Exception('Error opening translation file \'' . $filename . '\'.');
        }

        while(!feof($this->_file)) {
            $content = fgets($this->_file);
            $content = explode($options['separator'], $content);
            for ($x = 0; $x < count($content); ++$x) {
                if (isset($content[$x+1]) and (empty($content[$x+1]))) {
                    $content[$x] .= $options['separator'];
                    $length = 1;
                    if (isset($content[$x+2])) {
                        $content[$x] .= $content[$x+2];
                        $length = 2;
                    }
                    array_splice($content, $x + 1, $length);
                }
            }
            // # marks a comment in the translation source
            if ((!is_array($content) and (substr(trim($content), 0, 1) == "#")) or
                 (is_array($content) and (substr(trim($content[0]), 0, 1) == "#"))) {
                continue;
            }
            if (!empty($content[1])) {
                if (feof($this->_file)) {
                    $this->_translate[$locale][$content[0]] = $content[1];
                } else {
                    if (substr($content[1], -2, 2) == "\r\n") {
                        $this->_translate[$locale][$content[0]] = substr($content[1], 0, -2);
                    } else {
                        $this->_translate[$locale][$content[0]] = substr($content[1], 0, -1);
                    }
                }
            }
        }
    }

    /**
     * returns the adapters name
     *
     * @return string
     */
    public function toString()
    {
        return "Csv";
    }
}
