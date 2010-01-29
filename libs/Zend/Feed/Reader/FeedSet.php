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
 * @version    $Id: FeedSet.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Feed_Reader
 */
require_once 'Zend/Feed/Reader.php';

/**
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_FeedSet extends ArrayObject
{

    public $rss = null;

    public $rdf = null;

    public $atom = null;

    /**
     * Import a DOMNodeList from any document containing a set of links
     * for alternate versions of a document, which will normally refer to
     * RSS/RDF/Atom feeds for the current document.
     *
     * All such links are stored internally, however the first instance of
     * each RSS, RDF or Atom type has its URI stored as a public property
     * as a shortcut where the use case is simply to get a quick feed ref.
     *
     * Note that feeds are not loaded at this point, but will be lazy
     * loaded automatically when each links 'feed' array key is accessed.
     *
     * @param DOMNodeList $links
     * @param string $uri
     * @return void
     */
    public function addLinks(DOMNodeList $links, $uri)
    {
        foreach ($links as $link) {
            if (strtolower($link->getAttribute('rel')) !== 'alternate'
                || !$link->getAttribute('type') || !$link->getAttribute('href')) {
                continue;
            }
            if (!isset($this->rss) && $link->getAttribute('type') == 'application/rss+xml') {
                $this->rss = $this->_absolutiseUri(trim($link->getAttribute('href')), $uri);
            } elseif(!isset($this->atom) && $link->getAttribute('type') == 'application/atom+xml') {
                $this->atom = $this->_absolutiseUri(trim($link->getAttribute('href')), $uri);
            } elseif(!isset($this->rdf) && $link->getAttribute('type') == 'application/rdf+xml') {
                $this->rdf = $this->_absolutiseUri(trim($link->getAttribute('href')), $uri);
            }
            $this[] = new self(array(
                'rel' => 'alternate',
                'type' => $link->getAttribute('type'),
                'href' => $this->_absolutiseUri(trim($link->getAttribute('href')), $uri),
            ));
        }
    }
    
    /**
     *  Attempt to turn a relative URI into an absolute URI
     */
    protected function _absolutiseUri($link, $uri = null)
    {
        if (!Zend_Uri::check($link)) {
            if (!is_null($uri)) {
                $uri = Zend_Uri::factory($uri);

                if ($link[0] !== '/') {
                    $link = $uri->getPath() . '/' . $link;
                }

                $link = $uri->getScheme() . '://' . $uri->getHost() . '/' . $this->_canonicalizePath($link);
                if (!Zend_Uri::check($link)) {
                    $link = null;
                }
            }
        }
        return $link;
    }
    
    /**
     *  Canonicalize relative path
     */
    protected function _canonicalizePath($path)
    {
        $parts = array_filter(explode('/', $path));
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode('/', $absolutes);
    }

    /**
     * Supports lazy loading of feeds using Zend_Feed_Reader::import() but
     * delegates any other operations to the parent class.
     *
     * @param string $offset
     * @return mixed
     * @uses Zend_Feed_Reader
     */
    public function offsetGet($offset)
    {
        if ($offset == 'feed' && !$this->offsetExists('feed')) {
            if (!$this->offsetExists('href')) {
                return null;
            }
            $feed = Zend_Feed_Reader::import($this->offsetGet('href'));
            $this->offsetSet('feed', $feed);
            return $feed;
        }
        return parent::offsetGet($offset);
    }

}
