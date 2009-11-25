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
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Entry.php 18951 2009-11-12 16:26:19Z alexander $
 */

/**
 * @see Zend_Feed_Reader
 */
require_once 'Zend/Feed/Reader.php';

/**
 * @see Zend_Feed_Reader_Extension_EntryAbstract
 */
require_once 'Zend/Feed/Reader/Extension/EntryAbstract.php';

/**
 * @see Zend_Date
 */
require_once 'Zend/Date.php';

/**
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_Extension_Atom_Entry
    extends Zend_Feed_Reader_Extension_EntryAbstract
{
    /**
     * Get the specified author
     *
     * @param  int $index
     * @return string|null
     */
    public function getAuthor($index = 0)
    {
        $authors = $this->getAuthors();

        if (isset($authors[$index])) {
            return $authors[$index];
        }

        return null;
    }

    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function getAuthors()
    {
        if (array_key_exists('authors', $this->_data)) {
            return $this->_data['authors'];
        }

        $authors = $this->_xpath->query(
            $this->getXpathPrefix() . '//atom:author' . '|'
                . $this->getXpathPrefix(). '//atom:contributor'
        );

        if (!$authors->length) {
            $authors = $this->_xpath->query(
                '//atom:author' . '|' . '//atom:contributor'
            );
        }

        $people = array();

        if ($authors->length) {
            foreach ($authors as $author) {
                $author = $this->_getAuthor($author);

                if (!empty($author)) {
                    $people[] = $author;
                }
            }
        }

        $people = array_unique($people);

        $this->_data['authors'] = $people;

        return $this->_data['authors'];
    }

    /**
     * Get the entry content
     *
     * @return string
     */
    public function getContent()
    {
        if (array_key_exists('content', $this->_data)) {
            return $this->_data['content'];
        }

        $content = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:content)');

        if ($content) {
            $content =  html_entity_decode($content, ENT_QUOTES, $this->getEncoding());
        }

        if (!$content) {
            $content = $this->getDescription();
        }

        $this->_data['content'] = $content;

        return $this->_data['content'];
    }

    /**
     * Get the entry creation date
     *
     * @return string
     */
    public function getDateCreated()
    {
        if (array_key_exists('datecreated', $this->_data)) {
            return $this->_data['datecreated'];
        }

        $date = null;

        if ($this->getType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $dateCreated = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:created)');
        } else {
            $dateCreated = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:published)');
        }

        if ($dateCreated) {
            $date = new Zend_Date;
            $date->set($dateCreated, Zend_Date::ISO_8601);
        }

        $this->_data['datecreated'] = $date;

        return $this->_data['datecreated'];
    }

    /**
     * Get the entry modification date
     *
     * @return string
     */
    public function getDateModified()
    {
        if (array_key_exists('datemodified', $this->_data)) {
            return $this->_data['datemodified'];
        }

        $date = null;

        if ($this->getType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $dateModified = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:modified)');
        } else {
            $dateModified = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:updated)');
        }

        if ($dateModified) {
            $date = new Zend_Date;
            $date->set($dateModified, Zend_Date::ISO_8601);
        }

        $this->_data['datemodified'] = $date;

        return $this->_data['datemodified'];
    }

    /**
     * Get the entry description
     *
     * @return string
     */
    public function getDescription()
    {
        if (array_key_exists('description', $this->_data)) {
            return $this->_data['description'];
        }

        $description = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:summary)');

        if (!$description) {
            $description = null;
        } else {
            $description = html_entity_decode($description, ENT_QUOTES, $this->getEncoding());
        }

        $this->_data['description'] = $description;

        return $this->_data['description'];
    }

    /**
     * Get the entry enclosure
     *
     * @return string
     */
    public function getEnclosure()
    {
        if (array_key_exists('enclosure', $this->_data)) {
            return $this->_data['enclosure'];
        }

        $enclosure = null;

        $nodeList = $this->_xpath->query($this->getXpathPrefix() . '/atom:link[@rel="enclosure"]');

        if ($nodeList->length > 0) {
            $enclosure = new stdClass();
            $enclosure->url    = $nodeList->item(0)->getAttribute('href');
            $enclosure->length = $nodeList->item(0)->getAttribute('length');
            $enclosure->type   = $nodeList->item(0)->getAttribute('type');
        }

        $this->_data['enclosure'] = $enclosure;

        return $this->_data['enclosure'];
    }

    /**
     * Get the entry ID
     *
     * @return string
     */
    public function getId()
    {
        if (array_key_exists('id', $this->_data)) {
            return $this->_data['id'];
        }

        $id = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:id)');

        if (!$id) {
            if ($this->getPermalink()) {
                $id = $this->getPermalink();
            } elseif ($this->getTitle()) {
                $id = $this->getTitle();
            } else {
                $id = null;
            }
        }

        $this->_data['id'] = $id;

        return $this->_data['id'];
    }

    /**
     * Get the base URI of the feed (if set).
     *
     * @return string|null
     */
    public function getBaseUrl()
    {
        if (array_key_exists('baseUrl', $this->_data)) {
            return $this->_data['baseUrl'];
        }

        $baseUrl = $this->_xpath->evaluate('string('
            . $this->getXpathPrefix() . '/@xml:base[1]'
        . ')');

        if (!$baseUrl) {
            $baseUrl = $this->_xpath->evaluate('string(//@xml:base[1])');
        }

        if (!$baseUrl) {
            $baseUrl = null;
        }

        $this->_data['baseUrl'] = $baseUrl;

        return $this->_data['baseUrl'];
    }

    /**
     * Get a specific link
     *
     * @param  int $index
     * @return string
     */
    public function getLink($index = 0)
    {
        if (!array_key_exists('links', $this->_data)) {
            $this->getLinks();
        }

        if (isset($this->_data['links'][$index])) {
            return $this->_data['links'][$index];
        }

        return null;
    }

    /**
     * Get all links
     *
     * @return array
     */
    public function getLinks()
    {
        if (array_key_exists('links', $this->_data)) {
            return $this->_data['links'];
        }

        $links = array();

        $list = $this->_xpath->query(
            $this->getXpathPrefix() . '//atom:link[@rel="alternate"]/@href' . '|' .
            $this->getXpathPrefix() . '//atom:link[not(@rel)]/@href'
        );

        if ($list->length) {
            foreach ($list as $link) {
                $links[] = $this->_absolutiseUri($link->value);
            }
        }

        $this->_data['links'] = $links;

        return $this->_data['links'];
    }

    /**
     * Get a permalink to the entry
     *
     * @return string
     */
    public function getPermalink()
    {
        return $this->getLink(0);
    }

    /**
     * Get the entry title
     *
     * @return string
     */
    public function getTitle()
    {
        if (array_key_exists('title', $this->_data)) {
            return $this->_data['title'];
        }

        $title = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:title)');

        if (!$title) {
            $title = null;
        } else {
            $title = html_entity_decode($title, ENT_QUOTES, $this->getEncoding());
        }

        $this->_data['title'] = $title;

        return $this->_data['title'];
    }

    /**
     * Get the number of comments/replies for current entry
     *
     * @return integer
     */
    public function getCommentCount()
    {
        if (array_key_exists('commentcount', $this->_data)) {
            return $this->_data['commentcount'];
        }

        $count = null;

        $this->_xpath->registerNamespace('thread10', 'http://purl.org/syndication/thread/1.0');
        $list = $this->_xpath->query(
            $this->getXpathPrefix() . '//atom:link[@rel="replies"]/@thread10:count'
        );

        if ($list->length) {
            $count = $list->item(0)->value;
        }

        $this->_data['commentcount'] = $count;

        return $this->_data['commentcount'];
    }

    /**
     * Returns a URI pointing to the HTML page where comments can be made on this entry
     *
     * @return string
     */
    public function getCommentLink()
    {
        if (array_key_exists('commentlink', $this->_data)) {
            return $this->_data['commentlink'];
        }

        $link = null;

        $list = $this->_xpath->query(
            $this->getXpathPrefix() . '//atom:link[@rel="replies" and @type="text/html"]/@href'
        );

        if ($list->length) {
            $link = $list->item(0)->value;
            $link = $this->_absolutiseUri($link);
        }

        $this->_data['commentlink'] = $link;

        return $this->_data['commentlink'];
    }

    /**
     * Returns a URI pointing to a feed of all comments for this entry
     *
     * @return string
     */
    public function getCommentFeedLink($type = 'atom')
    {
        if (array_key_exists('commentfeedlink', $this->_data)) {
            return $this->_data['commentfeedlink'];
        }

        $link = null;

        $list = $this->_xpath->query(
            $this->getXpathPrefix() . '//atom:link[@rel="replies" and @type="application/'.$type.'+xml"]/@href'
        );

        if ($list->length) {
            $link = $list->item(0)->value;
            $link = $this->_absolutiseUri($link);
        }

        $this->_data['commentfeedlink'] = $link;

        return $this->_data['commentfeedlink'];
    }

    /**
     *  Attempt to absolutise the URI, i.e. if a relative URI apply the
     *  xml:base value as a prefix to turn into an absolute URI.
     */
    protected function _absolutiseUri($link)
    {
        if (!Zend_Uri::check($link)) {
            if (!is_null($this->getBaseUrl())) {
                $link = $this->getBaseUrl() . $link;
                if (!Zend_Uri::check($link)) {
                    $link = null;
                }
            }
        }
        return $link;
    }

    /**
     * Get an author entry
     *
     * @param DOMElement $element
     * @return string
     */
    protected function _getAuthor(DOMElement $element)
    {
        $email = null;
        $name  = null;
        $uri   = null;

        $emailNode = $element->getElementsByTagName('email');
        $nameNode  = $element->getElementsByTagName('name');
        $uriNode   = $element->getElementsByTagName('uri');

        if ($emailNode->length) {
            $email = $emailNode->item(0)->nodeValue;
        }

        if ($nameNode->length) {
            $name = $nameNode->item(0)->nodeValue;
        }

        if ($uriNode->length) {
            $uri = $uriNode->item(0)->nodeValue;
        }

        if (!empty($email)) {
            return $email . (empty($name) ? '' : ' (' . $name . ')');
        } else if (!empty($name)) {
            return $name;
        } else if (!empty($uri)) {
            return $uri;
        }

        return null;
    }

    /**
     * Register the default namespaces for the current feed format
     */
    protected function _registerNamespaces()
    {
        if ($this->getType() == Zend_Feed_Reader::TYPE_ATOM_10
            || $this->getType() == Zend_Feed_Reader::TYPE_ATOM_03
        ) {
            return; // pre-registered at Feed level
        }
        $atomDetected = $this->_getAtomType();
        switch ($atomDetected) {
            case Zend_Feed_Reader::TYPE_ATOM_03:
                $this->_xpath->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_03);
                break;
            default:
                $this->_xpath->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_10);
                break;
        }
    }

    /**
     * Detect the presence of any Atom namespaces in use
     */
    protected function _getAtomType()
    {
        $nslist = $this->getDomDocument()->documentElement->attributes;
        if (!$nslist->length) {
            return null;
        }
        foreach ($nslist as $ns) {
            if ($ns->value == Zend_Feed_Reader::NAMESPACE_ATOM_10) {
                return Zend_Feed_Reader::TYPE_ATOM_10;
            }
            if ($ns->value == Zend_Feed_Reader::NAMESPACE_ATOM_03) {
                return Zend_Feed_Reader::TYPE_ATOM_03;
            }
        }
    }
}
