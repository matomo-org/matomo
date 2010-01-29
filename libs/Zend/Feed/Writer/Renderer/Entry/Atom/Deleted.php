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
 * @version    $Id: Atom.php 20506 2010-01-21 22:19:05Z padraic $
 */

/**
 * @see Zend_Feed_Writer_Renderer_RendererAbstract
 */
require_once 'Zend/Feed/Writer/Renderer/RendererAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Renderer_Entry_Atom_Deleted
    extends Zend_Feed_Writer_Renderer_RendererAbstract
    implements Zend_Feed_Writer_Renderer_RendererInterface
{
    /**
     * Constructor
     * 
     * @param  Zend_Feed_Writer_Deleted $container 
     * @return void
     */
    public function __construct (Zend_Feed_Writer_Deleted $container)
    {
        parent::__construct($container);
    }

    /**
     * Render atom entry
     * 
     * @return Zend_Feed_Writer_Renderer_Entry_Atom
     */
    public function render()
    {
        $this->_dom = new DOMDocument('1.0', $this->_container->getEncoding());
        $this->_dom->formatOutput = true;
        $entry = $this->_dom->createElement('at:deleted-entry');
        $this->_dom->appendChild($entry);
        
        $entry->setAttribute('ref', $this->_container->getReference());
        $entry->setAttribute('when', $this->_container->getWhen()->get(Zend_Date::ISO_8601));
        
        $this->_setBy($this->_dom, $entry);
        $this->_setComment($this->_dom, $entry);
        
        return $this;
    }
    
    /**
     * Set tombstone comment
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setComment(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getComment()) {
            return;
        }
        $c = $dom->createElement('at:comment');
        $root->appendChild($c);
        $c->setAttribute('type', 'html');
        $cdata = $dom->createCDATASection($this->getDataContainer()->getComment());
        $c->appendChild($cdata);
    }
    
    /**
     * Set entry authors 
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setBy(DOMDocument $dom, DOMElement $root)
    {
        $data = $this->_container->getBy();
        if ((!$data || empty($data))) {
            return;
        }
        $author = $this->_dom->createElement('at:by');
        $name = $this->_dom->createElement('name');
        $author->appendChild($name);
        $root->appendChild($author);
        $text = $dom->createTextNode($data['name']);
        $name->appendChild($text);
        if (array_key_exists('email', $data)) {
            $email = $this->_dom->createElement('email');
            $author->appendChild($email);
            $text = $dom->createTextNode($data['email']);
            $email->appendChild($text);
        }
        if (array_key_exists('uri', $data)) {
            $uri = $this->_dom->createElement('uri');
            $author->appendChild($uri);
            $text = $dom->createTextNode($data['uri']);
            $uri->appendChild($text);
        }
    }
    
}
