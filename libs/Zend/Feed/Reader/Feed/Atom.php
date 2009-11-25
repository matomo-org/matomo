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
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Atom.php 19044 2009-11-19 16:44:24Z padraic $
 */

/**
 * @see Zend_Feed_Reader_FeedAbstract
 */
require_once 'Zend/Feed/Reader/FeedAbstract.php';

/**
 * @see Zend_Feed_Reader_Extension_Atom_Feed
 */
require_once 'Zend/Feed/Reader/Extension/Atom/Feed.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_Feed_Atom extends Zend_Feed_Reader_FeedAbstract
{

    /**
     * Constructor
     *
     * @param  DOMDocument $dom
     * @param  string $type
     */
    public function __construct(DomDocument $dom, $type = null)
    {
        parent::__construct($dom, $type);
        $atomClass = Zend_Feed_Reader::getPluginLoader()->getClassName('Atom_Feed');
        $this->_extensions['Atom_Feed'] = new $atomClass($dom, $this->_data['type'], $this->_xpath);
        foreach ($this->_extensions as $extension) {
            $extension->setXpathPrefix('/atom:feed');
        }
    }

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

        $people = $this->getExtension('Atom')->getAuthors();

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

        $copyright = $this->getExtension('Atom')->getCopyright();

        if (!$copyright) {
            $copyright = null;
        }

        $this->_data['copyright'] = $copyright;

        return $this->_data['copyright'];
    }

    /**
     * Get the feed creation date
     *
     * @return string|null
     */
    public function getDateCreated()
    {
        if (array_key_exists('datecreated', $this->_data)) {
            return $this->_data['datecreated'];
        }

        $dateCreated = $this->getExtension('Atom')->getDateCreated();

        if (!$dateCreated) {
            $dateCreated = null;
        }

        $this->_data['datecreated'] = $dateCreated;

        return $this->_data['datecreated'];
    }

    /**
     * Get the feed modification date
     *
     * @return string|null
     */
    public function getDateModified()
    {
        if (array_key_exists('datemodified', $this->_data)) {
            return $this->_data['datemodified'];
        }

        $dateModified = $this->getExtension('Atom')->getDateModified();

        if (!$dateModified) {
            $dateModified = null;
        }

        $this->_data['datemodified'] = $dateModified;

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

        $description = $this->getExtension('Atom')->getDescription();

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

        $generator = $this->getExtension('Atom')->getGenerator();

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

        $id = $this->getExtension('Atom')->getId();

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

        $language = $this->getExtension('Atom')->getLanguage();

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
     * Get a link to the source website
     *
     * @return string|null
     */
    public function getBaseUrl()
    {
        if (array_key_exists('baseUrl', $this->_data)) {
            return $this->_data['baseUrl'];
        }

        $baseUrl = $this->getExtension('Atom')->getBaseUrl();

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

        $link = $this->getExtension('Atom')->getLink();

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

        $link = $this->getExtension('Atom')->getFeedLink();

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

        $title = $this->getExtension('Atom')->getTitle();

        $this->_data['title'] = $title;

        return $this->_data['title'];
    }

    /**
     * Read all entries to the internal entries array
     *
     */
    protected function _indexEntries()
    {
        if ($this->getType() == Zend_Feed_Reader::TYPE_ATOM_10 ||
            $this->getType() == Zend_Feed_Reader::TYPE_ATOM_03) {
            $entries = array();
            $entries = $this->_xpath->evaluate('//atom:entry');

            foreach($entries as $index=>$entry) {
                $this->_entries[$index] = $entry;
            }
        }
    }

    /**
     * Register the default namespaces for the current feed format
     *
     */
    protected function _registerNamespaces()
    {
        switch ($this->_data['type']) {
            case Zend_Feed_Reader::TYPE_ATOM_03:
                $this->_xpath->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_03);
                break;
            case Zend_Feed_Reader::TYPE_ATOM_10:
            default:
                $this->_xpath->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_10);
        }
    }
}
