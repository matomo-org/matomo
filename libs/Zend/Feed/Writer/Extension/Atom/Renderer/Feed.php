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
 * @version    $Id: Feed.php 20326 2010-01-16 00:20:43Z padraic $
 */
 
/**
 * @see Zend_Feed_Writer_Extension_RendererAbstract
 */
require_once 'Zend/Feed/Writer/Extension/RendererAbstract.php';
 
/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Extension_Atom_Renderer_Feed
    extends Zend_Feed_Writer_Extension_RendererAbstract
{

    /**
     * Set to TRUE if a rendering method actually renders something. This
     * is used to prevent premature appending of a XML namespace declaration
     * until an element which requires it is actually appended.
     *
     * @var bool
     */
    protected $_called = false;
    
    /**
     * Render feed
     * 
     * @return void
     */
    public function render()
    {
        /**
         * RSS 2.0 only. Used mainly to include Atom links and
         * Pubsubhubbub Hub endpoint URIs under the Atom namespace
         */
        if (strtolower($this->getType()) == 'atom') {
            return;
        }
        $this->_setFeedLinks($this->_dom, $this->_base);
        $this->_setHubs($this->_dom, $this->_base);
        if ($this->_called) {
            $this->_appendNamespaces();
        }
    }
    
    /**
     * Append namespaces to root element of feed
     * 
     * @return void
     */
    protected function _appendNamespaces()
    {
        $this->getRootElement()->setAttribute('xmlns:atom',
            'http://www.w3.org/2005/Atom');  
    }

    /**
     * Set feed link elements
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setFeedLinks(DOMDocument $dom, DOMElement $root)
    {
        $flinks = $this->getDataContainer()->getFeedLinks();
        if(!$flinks || empty($flinks)) {
            return;
        }
        foreach ($flinks as $type => $href) {
            $mime  = 'application/' . strtolower($type) . '+xml';
            $flink = $dom->createElement('atom:link');
            $root->appendChild($flink);
            $flink->setAttribute('rel', 'self');
            $flink->setAttribute('type', $mime);
            $flink->setAttribute('href', $href);
        }
        $this->_called = true;
    }
    
    /**
     * Set PuSH hubs
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setHubs(DOMDocument $dom, DOMElement $root)
    {
        $hubs = $this->getDataContainer()->getHubs();
        if (!$hubs || empty($hubs)) {
            return;
        }
        foreach ($hubs as $hubUrl) {
            $hub = $dom->createElement('atom:link');
            $hub->setAttribute('rel', 'hub');
            $hub->setAttribute('href', $hubUrl);
            $root->appendChild($hub);
        }
        $this->_called = true;
    }
}
