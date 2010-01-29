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
 * @version    $Id: Entry.php 20326 2010-01-16 00:20:43Z padraic $
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
class Zend_Feed_Writer_Extension_Threading_Renderer_Entry
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
     * Render entry
     * 
     * @return void
     */
    public function render()
    {
        if (strtolower($this->getType()) == 'rss') {
            return; // Atom 1.0 only
        }
        $this->_setCommentLink($this->_dom, $this->_base);
        $this->_setCommentFeedLinks($this->_dom, $this->_base);
        $this->_setCommentCount($this->_dom, $this->_base);
        if ($this->_called) {
            $this->_appendNamespaces();
        }
    }
    
    /**
     * Append entry namespaces
     * 
     * @return void
     */
    protected function _appendNamespaces()
    {
        $this->getRootElement()->setAttribute('xmlns:thr',
            'http://purl.org/syndication/thread/1.0');  
    }
    
    /**
     * Set comment link
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setCommentLink(DOMDocument $dom, DOMElement $root)
    {
        $link = $this->getDataContainer()->getCommentLink();
        if (!$link) {
            return;
        }
        $clink = $this->_dom->createElement('link');
        $clink->setAttribute('rel', 'replies');
        $clink->setAttribute('type', 'text/html');
        $clink->setAttribute('href', $link);
        $count = $this->getDataContainer()->getCommentCount();
        if (!is_null($count)) {
            $clink->setAttribute('thr:count', $count);
        }
        $root->appendChild($clink);
        $this->_called = true;
    }
    
    /**
     * Set comment feed links
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setCommentFeedLinks(DOMDocument $dom, DOMElement $root)
    {
        $links = $this->getDataContainer()->getCommentFeedLinks();
        if (!$links || empty($links)) {
            return;
        }
        foreach ($links as $link) {
            $flink = $this->_dom->createElement('link');
            $flink->setAttribute('rel', 'replies');
            $flink->setAttribute('type', 'application/'. $link['type'] .'+xml');
            $flink->setAttribute('href', $link['uri']);
            $count = $this->getDataContainer()->getCommentCount();
            if (!is_null($count)) {
                $flink->setAttribute('thr:count', $count);
            }
            $root->appendChild($flink);
            $this->_called = true;
        }
    }

    /**
     * Set entry comment count
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setCommentCount(DOMDocument $dom, DOMElement $root)
    {
        $count = $this->getDataContainer()->getCommentCount();
        if (is_null($count)) {
            return;
        }
        $tcount = $this->_dom->createElement('thr:total');
        $tcount->nodeValue = $count;
        $root->appendChild($tcount);
        $this->_called = true;
    }
}
