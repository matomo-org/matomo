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
 * @version    $Id: Atom.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
 
require_once 'Zend/Feed/Writer/Renderer/Feed/Atom/AtomAbstract.php';
 
/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Renderer_Feed_Atom_Source
    extends Zend_Feed_Writer_Renderer_Feed_Atom_AtomAbstract
    implements Zend_Feed_Writer_Renderer_RendererInterface
{

    /**
     * Constructor
     * 
     * @param  Zend_Feed_Writer_Feed_Source $container 
     * @return void
     */
    public function __construct (Zend_Feed_Writer_Source $container)
    {
        parent::__construct($container);
    }
    
    /**
     * Render Atom Feed Metadata (Source element)
     * 
     * @return Zend_Feed_Writer_Renderer_Feed_Atom
     */
    public function render()
    {
        if (!$this->_container->getEncoding()) {
            $this->_container->setEncoding('UTF-8');
        }
        $this->_dom = new DOMDocument('1.0', $this->_container->getEncoding());
        $this->_dom->formatOutput = true;
        $root = $this->_dom->createElement('source');
        $this->setRootElement($root);
        $this->_dom->appendChild($root);
        $this->_setLanguage($this->_dom, $root);
        $this->_setBaseUrl($this->_dom, $root);
        $this->_setTitle($this->_dom, $root);
        $this->_setDescription($this->_dom, $root);
        $this->_setDateCreated($this->_dom, $root);
        $this->_setDateModified($this->_dom, $root);
        $this->_setGenerator($this->_dom, $root);
        $this->_setLink($this->_dom, $root);
        $this->_setFeedLinks($this->_dom, $root);
        $this->_setId($this->_dom, $root);
        $this->_setAuthors($this->_dom, $root);
        $this->_setCopyright($this->_dom, $root);
        $this->_setCategories($this->_dom, $root);
        
        foreach ($this->_extensions as $ext) {
            $ext->setType($this->getType());
            $ext->setRootElement($this->getRootElement());
            $ext->setDomDocument($this->getDomDocument(), $root);
            $ext->render();
        }
        return $this;
    }
    
    /**
     * Set feed generator string
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setGenerator(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getGenerator()) {
            return;
        }

        $gdata = $this->getDataContainer()->getGenerator();
        $generator = $dom->createElement('generator');
        $root->appendChild($generator);
        $text = $dom->createTextNode($gdata['name']);
        $generator->appendChild($text);
        if (array_key_exists('uri', $gdata)) {
            $generator->setAttribute('uri', $gdata['uri']);
        }
        if (array_key_exists('version', $gdata)) {
            $generator->setAttribute('version', $gdata['version']);
        }
    }

}
