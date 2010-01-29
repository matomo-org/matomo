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
 * @version    $Id: Atom.php 19165 2009-11-21 16:46:40Z padraic $
 */

/**
 * @see Zend_Feed_Reader_Feed_Atom
 */
require_once 'Zend/Feed/Reader/Feed/Atom.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_Feed_Atom_Source extends Zend_Feed_Reader_Feed_Atom
{

    /**
     * Constructor: Create a Source object which is largely just a normal
     * Zend_Feed_Reader_FeedAbstract object only designed to retrieve feed level
     * metadata from an Atom entry's source element.
     *
     * @param DOMElement $source
     * @param string $xpathPrefix Passed from parent Entry object
     * @param string $type Nearly always Atom 1.0
     */
    public function __construct(DOMElement $source, $xpathPrefix, $type = Zend_Feed_Reader::TYPE_ATOM_10)
    {
        $this->_domDocument = $source->ownerDocument;
        $this->_xpath = new DOMXPath($this->_domDocument);
        $this->_data['type'] = $type;
        $this->_registerNamespaces();
        $this->_loadExtensions();
        
        $atomClass = Zend_Feed_Reader::getPluginLoader()->getClassName('Atom_Feed');
        $this->_extensions['Atom_Feed'] = new $atomClass($this->_domDocument, $this->_data['type'], $this->_xpath);
        $atomClass = Zend_Feed_Reader::getPluginLoader()->getClassName('DublinCore_Feed');
        $this->_extensions['DublinCore_Feed'] = new $atomClass($this->_domDocument, $this->_data['type'], $this->_xpath);
        foreach ($this->_extensions as $extension) {
            $extension->setXpathPrefix(rtrim($xpathPrefix, '/') . '/atom:source');
        }
    }
    
    /**
     * Since this is not an Entry carrier but a vehicle for Feed metadata, any
     * applicable Entry methods are stubbed out and do nothing.
     */
     
    /**
     * @return void
     */
    public function count() {}

    /**
     * @return void
     */
    public function current() {}
    
    /**
     * @return void
     */
    public function key() {}

    /**
     * @return void
     */
    public function next() {}

    /**
     * @return void
     */
    public function rewind() {}
    
    /**
     * @return void
     */
    public function valid() {}
    
    /**
     * @return void
     */
    protected function _indexEntries() {}

}
