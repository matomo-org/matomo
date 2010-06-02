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

/** @see Zend_Feed_Writer_Feed */
// require_once 'Zend/Feed/Writer/Feed.php';

/** @see Zend_Version */
// require_once 'Zend/Version.php';

/** @see Zend_Feed_Writer_Renderer_RendererInterface */
// require_once 'Zend/Feed/Writer/Renderer/RendererInterface.php';

/** @see Zend_Feed_Writer_Renderer_Entry_Atom */
// require_once 'Zend/Feed/Writer/Renderer/Entry/Atom.php';

/** @see Zend_Feed_Writer_Renderer_RendererAbstract */
// require_once 'Zend/Feed/Writer/Renderer/RendererAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Renderer_Feed_Atom_AtomAbstract
    extends Zend_Feed_Writer_Renderer_RendererAbstract
{
    /**
     * Constructor
     * 
     * @param  Zend_Feed_Writer_Feed $container 
     * @return void
     */
    public function __construct ($container)
    {
        parent::__construct($container);
    }

    /**
     * Set feed language
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setLanguage(DOMDocument $dom, DOMElement $root)
    {
        if ($this->getDataContainer()->getLanguage()) {
            $root->setAttribute('xml:lang', $this->getDataContainer()
                ->getLanguage());
        }
    }

    /**
     * Set feed title
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setTitle(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getTitle()) {
            // require_once 'Zend/Feed/Exception.php';
            $message = 'Atom 1.0 feed elements MUST contain exactly one'
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
        $title->setAttribute('type', 'text');
        $text = $dom->createTextNode($this->getDataContainer()->getTitle());
        $title->appendChild($text);
    }

    /**
     * Set feed description
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setDescription(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getDescription()) {
            return;
        }
        $subtitle = $dom->createElement('subtitle');
        $root->appendChild($subtitle);
        $subtitle->setAttribute('type', 'text');
        $text = $dom->createTextNode($this->getDataContainer()->getDescription());
        $subtitle->appendChild($text);
    }

    /**
     * Set date feed was last modified
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setDateModified(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getDateModified()) {
            // require_once 'Zend/Feed/Exception.php';
            $message = 'Atom 1.0 feed elements MUST contain exactly one'
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
     * Set feed generator string
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
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
        $text = $dom->createTextNode($gdata['name']);
        $generator->appendChild($text);
        if (array_key_exists('uri', $gdata)) {
            $generator->setAttribute('uri', $gdata['uri']);
        }
        if (array_key_exists('version', $gdata)) {
            $generator->setAttribute('version', $gdata['version']);
        }
    }

    /**
     * Set link to feed
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
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
     * Set feed links
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setFeedLinks(DOMDocument $dom, DOMElement $root)
    {
        $flinks = $this->getDataContainer()->getFeedLinks();
        if(!$flinks || !array_key_exists('atom', $flinks)) {
            // require_once 'Zend/Feed/Exception.php';
            $message = 'Atom 1.0 feed elements SHOULD contain one atom:link '
            . 'element with a rel attribute value of "self".  This is the '
            . 'preferred URI for retrieving Atom Feed Documents representing '
            . 'this Atom feed but a feed link has not been set';
            $exception = new Zend_Feed_Exception($message);
            if (!$this->_ignoreExceptions) {
                throw $exception;
            } else {
                $this->_exceptions[] = $exception;
                return;
            }
        }

        foreach ($flinks as $type => $href) {
            $mime = 'application/' . strtolower($type) . '+xml';
            $flink = $dom->createElement('link');
            $root->appendChild($flink);
            $flink->setAttribute('rel', 'self');
            $flink->setAttribute('type', $mime);
            $flink->setAttribute('href', $href);
        }
    }
    
    /**
     * Set feed authors 
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setAuthors(DOMDocument $dom, DOMElement $root)
    {
        $authors = $this->_container->getAuthors();
        if (!$authors || empty($authors)) {
            /**
             * Technically we should defer an exception until we can check
             * that all entries contain an author. If any entry is missing
             * an author, then a missing feed author element is invalid
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
     * Set feed identifier
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setId(DOMDocument $dom, DOMElement $root)
    {
        if(!$this->getDataContainer()->getId()
        && !$this->getDataContainer()->getLink()) {
            // require_once 'Zend/Feed/Exception.php';
            $message = 'Atom 1.0 feed elements MUST contain exactly one '
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
        $id = $dom->createElement('id');
        $root->appendChild($id);
        $text = $dom->createTextNode($this->getDataContainer()->getId());
        $id->appendChild($text);
    }
    
    /**
     * Set feed copyright
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setCopyright(DOMDocument $dom, DOMElement $root)
    {
        $copyright = $this->getDataContainer()->getCopyright();
        if (!$copyright) {
            return;
        }
        $copy = $dom->createElement('rights');
        $root->appendChild($copy);
        $text = $dom->createTextNode($copyright);
        $copy->appendChild($text);
    }

    /**
     * Set feed level logo (image)
     * 
     * @param DOMDocument $dom 
     * @param DOMElement $root 
     * @return void
     */
    protected function _setImage(DOMDocument $dom, DOMElement $root)
    {
        $image = $this->getDataContainer()->getImage();
        if (!$image) {
            return;
        }
        $img = $dom->createElement('logo');
        $root->appendChild($img);
        $text = $dom->createTextNode($image['uri']);
        $img->appendChild($text);
    }
    
    /**
     * Set date feed was created 
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
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
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
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
     * Set hubs to which this feed pushes 
     * 
     * @param  DOMDocument $dom 
     * @param  DOMElement $root 
     * @return void
     */
    protected function _setHubs(DOMDocument $dom, DOMElement $root)
    {
        $hubs = $this->getDataContainer()->getHubs();
        if (!$hubs) {
            return;
        }
        foreach ($hubs as $hubUrl) {
            $hub = $dom->createElement('link');
            $hub->setAttribute('rel', 'hub');
            $hub->setAttribute('href', $hubUrl);
            $root->appendChild($hub);
        }
    }
    
    /**
     * Set feed cateories 
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
}
