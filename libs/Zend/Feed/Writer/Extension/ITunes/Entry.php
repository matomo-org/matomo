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
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Entry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
 
/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Extension_ITunes_Entry
{
    /**
     * Array of Feed data for rendering by Extension's renderers
     *
     * @var array
     */
    protected $_data = array();
    
    /**
     * Encoding of all text values
     *
     * @var string
     */
    protected $_encoding = 'UTF-8';
    
    /**
     * Set feed encoding
     * 
     * @param  string $enc 
     * @return Zend_Feed_Writer_Extension_ITunes_Entry
     */
    public function setEncoding($enc)
    {
        $this->_encoding = $enc;
        return $this;
    }
    
    /**
     * Get feed encoding
     * 
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }
    
    /**
     * Set a block value of "yes" or "no". You may also set an empty string.
     *
     * @param  string
     * @return Zend_Feed_Writer_Extension_ITunes_Entry
     */
    public function setItunesBlock($value)
    {
        if (!ctype_alpha($value) && strlen($value) > 0) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('invalid parameter: "block" may only'
            . ' contain alphabetic characters');
        }
        if (iconv_strlen($value, $this->getEncoding()) > 255) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('invalid parameter: "block" may only'
            . ' contain a maximum of 255 characters');
        }
        $this->_data['block'] = $value;
    }
    
    /**
     * Add authors to itunes entry
     * 
     * @param  array $values 
     * @return Zend_Feed_Writer_Extension_ITunes_Entry
     */
    public function addItunesAuthors(array $values)
    {
        foreach ($values as $value) {
            $this->addItunesAuthor($value);
        }
        return $this;
    }
    
    /**
     * Add author to itunes entry
     * 
     * @param  string $value 
     * @return Zend_Feed_Writer_Extension_ITunes_Entry
     */
    public function addItunesAuthor($value)
    {
        if (iconv_strlen($value, $this->getEncoding()) > 255) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('invalid parameter: any "author" may only'
            . ' contain a maximum of 255 characters each');
        }
        if (!isset($this->_data['authors'])) {
            $this->_data['authors'] = array();
        }
        $this->_data['authors'][] = $value;   
        return $this;
    }
    
    /**
     * Set duration
     * 
     * @param  int $value 
     * @return Zend_Feed_Writer_Extension_ITunes_Entry
     */
    public function setItunesDuration($value)
    {
        $value = (string) $value;
        if (!ctype_digit($value)
            && !preg_match("/^\d+:[0-5]{1}[0-9]{1}$/", $value)
            && !preg_match("/^\d+:[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/", $value)
        ) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('invalid parameter: "duration" may only'
            . ' be of a specified [[HH:]MM:]SS format');
        }
        $this->_data['duration'] = $value;
        return $this;
    }
    
    /**
     * Set "explicit" flag
     * 
     * @param  bool $value 
     * @return Zend_Feed_Writer_Extension_ITunes_Entry
     */
    public function setItunesExplicit($value)
    {
        if (!in_array($value, array('yes','no','clean'))) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('invalid parameter: "explicit" may only'
            . ' be one of "yes", "no" or "clean"');
        }
        $this->_data['explicit'] = $value;
        return $this;
    }
    
    /**
     * Set keywords
     * 
     * @param  array $value 
     * @return Zend_Feed_Writer_Extension_ITunes_Entry
     */
    public function setItunesKeywords(array $value)
    {
        if (count($value) > 12) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('invalid parameter: "keywords" may only'
            . ' contain a maximum of 12 terms');
        }
        $concat = implode(',', $value);
        if (iconv_strlen($concat, $this->getEncoding()) > 255) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('invalid parameter: "keywords" may only'
            . ' have a concatenated length of 255 chars where terms are delimited'
            . ' by a comma');
        }
        $this->_data['keywords'] = $value;
        return $this;
    }
    
    /**
     * Set subtitle
     * 
     * @param  string $value 
     * @return Zend_Feed_Writer_Extension_ITunes_Entry
     */
    public function setItunesSubtitle($value)
    {
        if (iconv_strlen($value, $this->getEncoding()) > 255) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('invalid parameter: "subtitle" may only'
            . ' contain a maximum of 255 characters');
        }
        $this->_data['subtitle'] = $value;
        return $this;
    }
    
    /**
     * Set summary
     * 
     * @param  string $value 
     * @return Zend_Feed_Writer_Extension_ITunes_Entry
     */
    public function setItunesSummary($value)
    {
        if (iconv_strlen($value, $this->getEncoding()) > 4000) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('invalid parameter: "summary" may only'
            . ' contain a maximum of 4000 characters');
        }
        $this->_data['summary'] = $value;
        return $this;
    }
    
    /**
     * Overloading to itunes specific setters
     * 
     * @param  string $method 
     * @param  array $params 
     * @return mixed
     */
    public function __call($method, array $params)
    {
        $point = Zend_Feed_Writer::lcfirst(substr($method, 9));
        if (!method_exists($this, 'setItunes' . ucfirst($point))
            && !method_exists($this, 'addItunes' . ucfirst($point))
        ) {
            require_once 'Zend/Feed/Writer/Exception/InvalidMethodException.php';
            throw new Zend_Feed_Writer_Exception_InvalidMethodException(
                'invalid method: ' . $method
            );
        }
        if (!array_key_exists($point, $this->_data) 
            || empty($this->_data[$point])
        ) {
            return null;
        }
        return $this->_data[$point];
    }
}
