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
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: EntryAbstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Feed_Reader_EntryAbstract
{
    /**
     * Feed entry data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * DOM document object
     *
     * @var DOMDocument
     */
    protected $_domDocument = null;

    /**
     * Entry instance
     *
     * @var Zend_Feed_Entry_Interface
     */
    protected $_entry = null;

    /**
     * Pointer to the current entry
     *
     * @var int
     */
    protected $_entryKey = 0;

    /**
     * XPath object
     *
     * @var DOMXPath
     */
    protected $_xpath = null;

    /**
     * Registered extensions
     *
     * @var array
     */
    protected $_extensions = array();

    /**
     * Constructor
     *
     * @param  DOMElement $entry
     * @param  int $entryKey
     * @param  string $type
     * @return void
     */
    public function __construct(DOMElement $entry, $entryKey, $type = null)
    {
        $this->_entry       = $entry;
        $this->_entryKey    = $entryKey;
        $this->_domDocument = $entry->ownerDocument;
        if ($type !== null) {
            $this->_data['type'] = $type;
        } else {
            $this->_data['type'] = Zend_Feed_Reader::detectType($feed);
        }
        $this->_loadExtensions();
    }

    /**
     * Get the DOM
     *
     * @return DOMDocument
     */
    public function getDomDocument()
    {
        return $this->_domDocument;
    }

    /**
     * Get the entry element
     *
     * @return DOMElement
     */
    public function getElement()
    {
        return $this->_entry;
    }

    /**
     * Get the Entry's encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        $assumed = $this->getDomDocument()->encoding;
        if (empty($assumed)) {
            $assumed = 'UTF-8';
        }
        return $assumed;
    }

    /**
     * Get entry as xml
     *
     * @return string
     */
    public function saveXml()
    {
        $dom = new DOMDocument('1.0', $this->getEncoding());
        $entry = $dom->importNode($this->getElement(), true);
        $dom->appendChild($entry);
        return $dom->saveXml();
    }

    /**
     * Get the entry type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_data['type'];
    }

    /**
     * Get the XPath query object
     *
     * @return DOMXPath
     */
    public function getXpath()
    {
        if (!$this->_xpath) {
            $this->setXpath(new DOMXPath($this->getDomDocument()));
        }
        return $this->_xpath;
    }

    /**
     * Set the XPath query
     *
     * @param  DOMXPath $xpath
     * @return Zend_Feed_Reader_Entry_EntryAbstract
     */
    public function setXpath(DOMXPath $xpath)
    {
        $this->_xpath = $xpath;
        return $this;
    }

    /**
     * Get registered extensions
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->_extensions;
    }

    /**
     * Return an Extension object with the matching name (postfixed with _Entry)
     *
     * @param string $name
     * @return Zend_Feed_Reader_Extension_EntryAbstract
     */
    public function getExtension($name)
    {
        if (array_key_exists($name . '_Entry', $this->_extensions)) {
            return $this->_extensions[$name . '_Entry'];
        }
        return null;
    }

    /**
     * Method overloading: call given method on first extension implementing it
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Zend_Feed_Exception if no extensions implements the method
     */
    public function __call($method, $args)
    {
        foreach ($this->_extensions as $extension) {
            if (method_exists($extension, $method)) {
                return call_user_func_array(array($extension, $method), $args);
            }
        }
        require_once 'Zend/Feed/Exception.php';
        throw new Zend_Feed_Exception('Method: ' . $method
            . 'does not exist and could not be located on a registered Extension');
    }

    /**
     * Load extensions from Zend_Feed_Reader
     *
     * @return void
     */
    protected function _loadExtensions()
    {
        $all = Zend_Feed_Reader::getExtensions();
        $feed = $all['entry'];
        foreach ($feed as $extension) {
            if (in_array($extension, $all['core'])) {
                continue;
            }
            $className = Zend_Feed_Reader::getPluginLoader()->getClassName($extension);
            $this->_extensions[$extension] = new $className(
                $this->getElement(), $this->_entryKey, $this->_data['type']
            );
        }
    }
}
