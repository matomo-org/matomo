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
 * @version    $Id: Rss.php 20519 2010-01-22 14:06:24Z padraic $
 */

/** @see Zend_Feed_Writer_Feed */
require_once 'Zend/Feed/Writer/Feed.php';

/** @see Zend_Version */
require_once 'Zend/Version.php';

/** @see Zend_Feed_Writer_Renderer_RendererInterface */
require_once 'Zend/Feed/Writer/Renderer/RendererInterface.php';

/** @see Zend_Feed_Writer_Renderer_Entry_Rss */
require_once 'Zend/Feed/Writer/Renderer/Entry/Rss.php';

/** @see Zend_Feed_Writer_Renderer_RendererAbstract */
require_once 'Zend/Feed/Writer/Renderer/RendererAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Renderer_Feed_Rss
    extends Zend_Feed_Writer_Renderer_RendererAbstract
    implements Zend_Feed_Writer_Renderer_RendererInterface
{
    /**
     * Constructor
     * 
     * @param  Zend_Feed_Writer_Feed $container 
     * @return void
     */
    public function __construct (Zend_Feed_Writer_Feed $container)
    {
        parent::__construct($container);
    }

    /**
     * Render RSS feed
     * 
     * @return Zend_Feed_Writer_Renderer_Feed_Rss
     */
    public function render()
    {
        if (!$this->_container->getEncoding()) {
            $this->_container->setEncoding('UTF-8');
        }
        $this->_dom = new DOMDocument('1.0', $this->_container->getEncoding());
        $this->_dom->formatOutput = true;
        $this->_dom->substituteEntities = false;
        $rss = $this->_dom->createElement('rss');
        $this->setRootElement($rss);
        $rss->setAttribute('version', '2.0');
        
        $channel = $this->_dom->createElement('channel');
        $rss->appendChild($channel);
        $this->_dom->appendChild($rss);
        $this->_setLanguage($this->_dom, $channel);
        $this->_setBaseUrl($this->_dom, $channel);
        $this->_setTitle($this->_dom, $channel);
        $this->_setDescription($this->_dom, $channel);
        $this->_setDateCreated($this->_dom, $channel);
        $this->_setDateModified($this->_dom, $channel);
        $this->_setGenerator($this->_dom, $channel);
        $this->_setLink($this->_dom, $channel);
        $this->_setAuthors($this->_dom, $channel);
        $this->_setCopyright($this->_dom, $channel);
        $this->_setCategories($this->_dom, $channel);
        
        foreach ($this->_extensions as $ext) {
            $ext->setType($this->getType());
            $ext->setRootElement($this->getRootElement());
            $ext->setDomDocument($this->getDomDocument(), $channel);
            $ext->render();
        }
        
        foreach ($this->_container as $entry) {
            if ($this->getDataContainer()->getEncoding()) {
                $entry->setEncoding($this->getDataContainer()->getEncoding());
            }
            if ($entry instanceof Zend_Feed_Writer_Entry) {
                $renderer = new Zend_Feed_Writer_Renderer_Entry_Rss($entry);
            } else {
                continue;
            }
            if ($this->_ignoreExceptions === true) {
                $renderer->ignoreExceptions();
            }
            $renderer->setType($this->getType());
            $renderer->setRootElement($this->_dom->documentElement);
            $renderer->render();
            $element = $renderer->getElement();
            $imported = $this->_dom->importNode($element, true);
            $channel->appendChild($imported);
        }
        return $this;
    }

    /**
     * Set feed language
     * 
     * @param DOMDocument $dom 
     * @param DOMElement $root 
     * @return void
     */
    protected function _setLanguage(DOMDocument $dom, DOMElement $root)
    {
        $lang = $this->getDataContainer()->getLanguage();
        if (!$lang) {
            return;
        }
        $language = $dom->createElement('language');
        $root->appendChild($language);
        $language->nodeValue = $lang;
    }

    /**
     * Set feed title
     * 
     * @param DOMDocument $dom 
     * @param DOMElement $root 
     * @return void
     */
    protected function _setTitle(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getTitle()) {
            require_once 'Zend/Feed/Exception.php';
            $message = 'RSS 2.0 feed elements MUST contain exactly one'
            . ' title element but a title has not been set';
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
        $text = $dom->createTextNode($this->getDataContainer()->getTitle());
        $title->appendChild($text);
    }

    /**
     * Set feed description
     * 
     * @param DOMDocument $dom 
     * @param DOMElement $root 
     * @return void
     */
    protected function _setDescription(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getDescription()) {
            require_once 'Zend/Feed/Exception.php';
            $message = 'RSS 2.0 feed elements MUST contain exactly one'
            . ' description element but one has not been set';
            $exception = new Zend_Feed_Exception($message);
            if (!$this->_ignoreExceptions) {
                throw $exception;
            } else {
                $this->_exceptions[] = $exception;
                return;
            }
        }
        $subtitle = $dom->createElement('description');
        $root->appendChild($subtitle);
        $text = $dom->createTextNode($this->getDataContainer()->getDescription());
        $subtitle->appendChild($text);
    }

    /**
     * Set date feed was last modified
     * 
     * @param DOMDocument $dom 
     * @param DOMElement $root 
     * @return void
     */
    protected function _setDateModified(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getDateModified()) {
            return;
        }

        $updated = $dom->createElement('pubDate');
        $root->appendChild($updated);
        $text = $dom->createTextNode(
            $this->getDataContainer()->getDateModified()->get(Zend_Date::RSS)
        );
        $updated->appendChild($text);
    }

    /**
     * Set feed generator string
     * 
     * @param DOMDocument $dom 
     * @param DOMElement $root 
     * @return void
     */
    protected function _setGenerator(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getGenerator()) {
            $this->getDataContainer()->setGenerator('Zend_Feed_Writer',
                Zend_Version::VERSION, 'http://framework.zend.com');
        }

        $gdata = $this->getDataContainer()->getGenerator();
        $generator = $dom->createElement('generator');
        $root->appendChild($generator);
        $name = $gdata['name'];
        if (array_key_exists('version', $gdata)) {
            $name .= ' ' . $gdata['version'];
        }
        if (array_key_exists('uri', $gdata)) {
            $name .= ' (' . $gdata['uri'] . ')';
        }
        $text = $dom->createTextNode($name);
        $generator->appendChild($text);
    }

    /**
     * Set link to feed
     * 
     * @param DOMDocument $dom 
     * @param DOMElement $root 
     * @return void
     */
    protected function _setLink(DOMDocument $dom, DOMElement $root)
    {
        $value = $this->getDataContainer()->getLink();
        if(!$value) {
            require_once 'Zend/Feed/Exception.php';
            $message = 'RSS 2.0 feed elements MUST contain exactly one'
            . ' link element but one has not been set';
            $exception = new Zend_Feed_Exception($message);
            if (!$this->_ignoreExceptions) {
                throw $exception;
            } else {
                $this->_exceptions[] = $exception;
                return;
            }
        }
        $link = $dom->createElement('link');
        $root->appendChild($link);
        $text = $dom->createTextNode($value);
        $link->appendChild($text);
        if (!Zend_Uri::check($value)) {
            $link->setAttribute('isPermaLink', 'false');
        }
    }
    
    /**
     * Set feed authors
     * 
     * @param DOMDocument $dom 
     * @param DOMElement $root 
     * @return void
     */
    protected function _setAuthors(DOMDocument $dom, DOMElement $root)
    {
        $authors = $this->getDataContainer()->getAuthors();
        if (!$authors || empty($authors)) {
            return;
        }
        foreach ($authors as $data) {
            $author = $this->_dom->createElement('author');
            $name = $data['name'];
            if (array_key_exists('email', $data)) {
                $name = $data['email'] . ' (' . $data['name'] . ')';
            }
            $text = $dom->createTextNode($name);
            $author->appendChild($text);
            $root->appendChild($author);
        }
    }
    
    /**
     * Set feed copyright
     * 
     * @param DOMDocument $dom 
     * @param DOMElement $root 
     * @return void
     */
    protected function _setCopyright(DOMDocument $dom, DOMElement $root)
    {
        $copyright = $this->getDataContainer()->getCopyright();
        if (!$copyright) {
            return;
        }
        $copy = $dom->createElement('copyright');
        $root->appendChild($copy);
        $text = $dom->createTextNode($copyright);
        $copy->appendChild($text);
    }
    
    /**
     * Set date feed was created
     * 
     * @param DOMDocument $dom 
     * @param DOMElement $root 
     * @return void
     */
    protected function _setDateCreated(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getDateCreated()) {
            return;
        }
        if(!$this->getDataContainer()->getDateModified()) {
            $this->getDataContainer()->setDateModified(
                $this->getDataContainer()->getDateCreated()
            );
        }
    }
    
    /**
     * Set base URL to feed links
     * 
     * @param DOMDocument $dom 
     * @param DOMElement $root 
     * @return void
     */
    protected function _setBaseUrl(DOMDocument $dom, DOMElement $root)
    {
        $baseUrl = $this->getDataContainer()->getBaseUrl();
        if (!$baseUrl) {
            return;
        }
        $root->setAttribute('xml:base', $baseUrl);
    }
    
    /**
     * Set feed categories
     * 
     * @param DOMDocument $dom 
     * @param DOMElement $root 
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
            if (isset($cat['scheme'])) {
                $category->setAttribute('domain', $cat['scheme']);
            }
            $text = $dom->createTextNode($cat['term']);
            $category->appendChild($text);
            $root->appendChild($category);
        }
    }
}
