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
 * @version    $Id: Feed.php 18951 2009-11-12 16:26:19Z alexander $
 */

/**
 * @see Zend_Feed_Reader_Extension_FeedAbstract
 */
require_once 'Zend/Feed/Reader/Extension/FeedAbstract.php';

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
class Zend_Feed_Reader_Extension_Atom_Feed
    extends Zend_Feed_Reader_Extension_FeedAbstract
{
    /**
     * Get a single author
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

        $authors = $this->_xpath->query('//atom:author');
        $contributors = $this->_xpath->query('//atom:contributor');

        $people = array();

        if ($authors->length) {
            foreach ($authors as $author) {
                $author = $this->_getAuthor($author);

                if (!empty($author)) {
                    $people[] = $author;
                }
            }
        }

        if ($contributors->length) {
            foreach ($contributors as $contributor) {
                $contributor = $this->_getAuthor($contributor);

                if (!empty($contributor)) {
                    $people[] = $contributor;
                }
            }
        }

        if (empty($people)) {
            $people = null;
        } else {
            $people = array_unique($people);
        }

        $this->_data['authors'] = $people;

        return $this->_data['authors'];
    }

    /**
     * Get the copyright entry
     *
     * @return string|null
     */
    public function getCopyright()
    {
        if (array_key_exists('copyright', $this->_data)) {
            return $this->_data['copyright'];
        }

        $copyright = null;

        if ($this->getType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $copyright = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:copyright)');
        } else {
            $copyright = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:rights)');
        }

        if (!$copyright) {
            $copyright = null;
        }

        $this->_data['copyright'] = $copyright;

        return $this->_data['copyright'];
    }

    /**
     * Get the feed creation date
     *
     * @return Zend_Date|null
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
     * Get the feed modification date
     *
     * @return Zend_Date|null
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
     * Get the feed description
     *
     * @return string|null
     */
    public function getDescription()
    {
        if (array_key_exists('description', $this->_data)) {
            return $this->_data['description'];
        }

        $description = null;

        if ($this->getType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $description = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:tagline)'); // TODO: Is this the same as subtitle?
        } else {
            $description = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:subtitle)');
        }

        if (!$description) {
            $description = null;
        }

        $this->_data['description'] = $description;

        return $this->_data['description'];
    }

    /**
     * Get the feed generator entry
     *
     * @return string|null
     */
    public function getGenerator()
    {
        if (array_key_exists('generator', $this->_data)) {
            return $this->_data['generator'];
        }
        // TODO: Add uri support
        $generator = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:generator)');

        if (!$generator) {
            $generator = null;
        } else {
            $generator = html_entity_decode($generator, ENT_QUOTES, $this->getEncoding());
        }

        $this->_data['generator'] = $generator;

        return $this->_data['generator'];
    }

    /**
     * Get the feed ID
     *
     * @return string|null
     */
    public function getId()
    {
        if (array_key_exists('id', $this->_data)) {
            return $this->_data['id'];
        }

        $id = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:id)');

        if (!$id) {
            if ($this->getLink()) {
                $id = $this->getLink();
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
     * Get the feed language
     *
     * @return string|null
     */
    public function getLanguage()
    {
        if (array_key_exists('language', $this->_data)) {
            return $this->_data['language'];
        }

        $language = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:lang)');

        if (!$language) {
            $language = $this->_xpath->evaluate('string(//@xml:lang[1])');
        }

        if (!$language) {
            $language = null;
        }

        $this->_data['language'] = $language;

        return $this->_data['language'];
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

        $baseUrl = $this->_xpath->evaluate('string(//@xml:base[1])');

        if (!$baseUrl) {
            $baseUrl = null;
        }
        $this->_data['baseUrl'] = $baseUrl;

        return $this->_data['baseUrl'];
    }

    /**
     * Get a link to the source website
     *
     * @return string|null
     */
    public function getLink()
    {
        if (array_key_exists('link', $this->_data)) {
            return $this->_data['link'];
        }

        $link = null;

        $list = $this->_xpath->query(
            $this->getXpathPrefix() . '/atom:link[@rel="alternate"]/@href' . '|' .
            $this->getXpathPrefix() . '/atom:link[not(@rel)]/@href'
        );

        if ($list->length) {
            $link = $list->item(0)->nodeValue;
            $link = $this->_absolutiseUri($link);
        }

        $this->_data['link'] = $link;

        return $this->_data['link'];
    }

    /**
     * Get a link to the feed's XML Url
     *
     * @return string|null
     */
    public function getFeedLink()
    {
        if (array_key_exists('feedlink', $this->_data)) {
            return $this->_data['feedlink'];
        }

        $link = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:link[@rel="self"]/@href)');

        $link = $this->_absolutiseUri($link);

        $this->_data['feedlink'] = $link;

        return $this->_data['feedlink'];
    }

    /**
     * Get the feed title
     *
     * @return string|null
     */
    public function getTitle()
    {
        if (array_key_exists('title', $this->_data)) {
            return $this->_data['title'];
        }

        $title = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:title)');

        if (!$title) {
            $title = null;
        }

        $this->_data['title'] = $title;

        return $this->_data['title'];
    }

    /**
     * Get an author entry in RSS format
     *
     * @param  DOMElement $element
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
