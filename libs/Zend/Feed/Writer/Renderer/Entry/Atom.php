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
 * @version    $Id: Atom.php 20507 2010-01-21 22:21:07Z padraic $
 */

/**
 * @see Zend_Feed_Writer_Renderer_RendererAbstract
 */
require_once 'Zend/Feed/Writer/Renderer/RendererAbstract.php';

require_once 'Zend/Feed/Writer/Renderer/Feed/Atom/Source.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Renderer_Entry_Atom
    extends Zend_Feed_Writer_Renderer_RendererAbstract
    implements Zend_Feed_Writer_Renderer_RendererInterface
{
    /**
     * Constructor
     * 
     * @param  Zend_Feed_Writer_Entry $container 
     * @return void
     */
    public function __construct (Zend_Feed_Writer_Entry $container)
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
        $entry = $this->_dom->createElementNS(Zend_Feed_Writer::NAMESPACE_ATOM_10, 'entry');
        $this->_dom->appendChild($entry);
        
        $this->_setSource($this->_dom, $entry);
        $this->_setTitle($this->_dom, $entry);
        $this->_setDescription($this->_dom, $entry);
        $this->_setDateCreated($this->_dom, $entry);
        $this->_setDateModified($this->_dom, $entry);
        $this->_setLink($this->_dom, $entry);
        $this->_setId($this->_dom, $entry);
        $this->_setAuthors($this->_dom, $entry);
        $this->_setEnclosure($this->_dom, $entry);
        $this->_setContent($this->_dom, $entry);
        $this->_setCategories($this->_dom, $entry);

        foreach ($this->_extensions as $ext) {
            $ext->setType($this->getType());
            $ext->setRootElement($this->getRootElement());
            $ext->setDomDocument($this->getDomDocument(), $entry);
            $ext->render();
        }
        
        return $this;
    }
    
    /**
     * Set entry title
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setTitle(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getTitle()) {
            require_once 'Zend/Feed/Exception.php';
            $message = 'Atom 1.0 entry elements MUST contain exactly one'
            . ' atom:title element but a title has not been set';
            $exception = new Zend_Feed_Exception($message);
            if (!$this->_ignoreExceptions) {
                throw $exception;
            } else {
                $this->_exceptions[] = $exception;
                return;
            }
        }
        $title = $dom->createElement('title');
        $root->appendChild($title);
        $title->setAttribute('type', 'html');
        $cdata = $dom->createCDATASection($this->getDataContainer()->getTitle());
        $title->appendChild($cdata);
    }
    
    /**
     * Set entry description
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setDescription(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getDescription()) {
            return; // unless src content or base64
        }
        $subtitle = $dom->createElement('summary');
        $root->appendChild($subtitle);
        $subtitle->setAttribute('type', 'html');
        $cdata = $dom->createCDATASection(
            $this->getDataContainer()->getDescription()
        );
        $subtitle->appendChild($cdata);
    }
    
    /**
     * Set date entry was modified
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setDateModified(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getDateModified()) {
            require_once 'Zend/Feed/Exception.php';
            $message = 'Atom 1.0 entry elements MUST contain exactly one'
            . ' atom:updated element but a modification date has not been set';
            $exception = new Zend_Feed_Exception($message);
            if (!$this->_ignoreExceptions) {
                throw $exception;
            } else {
                $this->_exceptions[] = $exception;
                return;
            }
        }

        $updated = $dom->createElement('updated');
        $root->appendChild($updated);
        $text = $dom->createTextNode(
            $this->getDataContainer()->getDateModified()->get(Zend_Date::ISO_8601)
        );
        $updated->appendChild($text);
    }
    
    /**
     * Set date entry was created
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setDateCreated(DOMDocument $dom, DOMElement $root)
    {
        if (!$this->getDataContainer()->getDateCreated()) {
            return;
        }
        $el = $dom->createElement('published');
        $root->appendChild($el);
        $text = $dom->createTextNode(
            $this->getDataContainer()->getDateCreated()->get(Zend_Date::ISO_8601)
        );
        $el->appendChild($text);
    }
    
    /**
     * Set entry authors 
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setAuthors(DOMDocument $dom, DOMElement $root)
    {
        $authors = $this->_container->getAuthors();
        if ((!$authors || empty($authors))) {
            /**
             * This will actually trigger an Exception at the feed level if
             * a feed level author is not set.
             */
            return;
        }
        foreach ($authors as $data) {
            $author = $this->_dom->createElement('author');
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
    
    /**
     * Set entry enclosure
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setEnclosure(DOMDocument $dom, DOMElement $root)
    {
        $data = $this->_container->getEnclosure();
        if ((!$data || empty($data))) {
            return;
        }
        $enclosure = $this->_dom->createElement('link');
        $enclosure->setAttribute('rel', 'enclosure');
        $enclosure->setAttribute('type', $data['type']);
        $enclosure->setAttribute('length', $data['length']);
        $enclosure->setAttribute('href', $data['uri']);
        $root->appendChild($enclosure);
    }
    
    protected function _setLink(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getLink()) {
            return;
        }
        $link = $dom->createElement('link');
        $root->appendChild($link);
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('type', 'text/html');
        $link->setAttribute('href', $this->getDataContainer()->getLink());
    }
    
    /**
     * Set entry identifier 
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setId(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getId()
        && !$this->getDataContainer()->getLink()) {
            require_once 'Zend/Feed/Exception.php';
            $message = 'Atom 1.0 entry elements MUST contain exactly one '
            . 'atom:id element, or as an alternative, we can use the same '
            . 'value as atom:link however neither a suitable link nor an '
            . 'id have been set';
            $exception = new Zend_Feed_Exception($message);
            if (!$this->_ignoreExceptions) {
                throw $exception;
            } else {
                $this->_exceptions[] = $exception;
                return;
            }
        }

        if (!$this->getDataContainer()->getId()) {
            $this->getDataContainer()->setId(
                $this->getDataContainer()->getLink());
        }
        if (!Zend_Uri::check($this->getDataContainer()->getId()) &&
        !preg_match("#^urn:[a-zA-Z0-9][a-zA-Z0-9\-]{1,31}:([a-zA-Z0-9\(\)\+\,\.\:\=\@\;\$\_\!\*\-]|%[0-9a-fA-F]{2})*#", $this->getDataContainer()->getId())) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Atom 1.0 IDs must be a valid URI/IRI');
        }
        $id = $dom->createElement('id');
        $root->appendChild($id);
        $text = $dom->createTextNode($this->getDataContainer()->getId());
        $id->appendChild($text);
    }
    
    /**
     * Set entry content 
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setContent(DOMDocument $dom, DOMElement $root)
    {
        $content = $this->getDataContainer()->getContent();
        if (!$content && !$this->getDataContainer()->getLink()) {
            require_once 'Zend/Feed/Exception.php';
            $message = 'Atom 1.0 entry elements MUST contain exactly one '
            . 'atom:content element, or as an alternative, at least one link '
            . 'with a rel attribute of "alternate" to indicate an alternate '
            . 'method to consume the content.';
            $exception = new Zend_Feed_Exception($message);
            if (!$this->_ignoreExceptions) {
                throw $exception;
            } else {
                $this->_exceptions[] = $exception;
                return;
            }
        }
        if (!$content) {
            return;
        }
        $element = $dom->createElement('content');
        $element->setAttribute('type', 'xhtml');
        $xhtmlElement = $this->_loadXhtml($content);
        $xhtml = $dom->importNode($xhtmlElement, true);
        $element->appendChild($xhtml);
        $root->appendChild($element);
    }
    
    /**
     * Load a HTML string and attempt to normalise to XML
     */
    protected function _loadXhtml($content)
    {
        $xhtml = '';
        if (class_exists('tidy', false)) {
            $tidy = new tidy;
            $config = array(
                'output-xhtml' => true,
                'show-body-only' => true
            );
            $encoding = str_replace('-', '', $this->getEncoding());
            $tidy->parseString($content, $config, $encoding);
            $tidy->cleanRepair();
            $xhtml = (string) $tidy;
        } else {
            $xhtml = $content;
        }
        $xhtml = preg_replace(array(
            "/(<[\/]?)([a-zA-Z]+)/"   
        ), '$1xhtml:$2', $xhtml);
        $dom = new DOMDocument('1.0', $this->getEncoding());
        $dom->loadXML('<xhtml:div xmlns:xhtml="http://www.w3.org/1999/xhtml">'
            . $xhtml . '</xhtml:div>');
        return $dom->documentElement;
    }
    
    /**
     * Set entry cateories 
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setCategories(DOMDocument $dom, DOMElement $root)
    {
        $categories = $this->getDataContainer()->getCategories();
        if (!$categories) {
            return;
        }
        foreach ($categories as $cat) {
            $category = $dom->createElement('category');
            $category->setAttribute('term', $cat['term']);
            if (isset($cat['label'])) {
                $category->setAttribute('label', $cat['label']);
            } else {
                $category->setAttribute('label', $cat['term']);
            }
            if (isset($cat['scheme'])) {
                $category->setAttribute('scheme', $cat['scheme']);
            }
            $root->appendChild($category);
        }
    }
    
    /**
     * Append Source element (Atom 1.0 Feed Metadata)
     *
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setSource(DOMDocument $dom, DOMElement $root)
    {
        $source = $this->getDataContainer()->getSource();
        if (!$source) {
            return;
        }
        $renderer = new Zend_Feed_Writer_Renderer_Feed_Atom_Source($source);
        $renderer->setType($this->getType());
        $element = $renderer->render()->getElement();
        $imported = $dom->importNode($element, true);
        $root->appendChild($imported); 
    }
}
