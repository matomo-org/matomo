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
 * @version    $Id: Atom.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Feed_Reader
 */
require_once 'Zend/Feed/Reader.php';

/**
 * @see Zend_Feed_Reader_EntryInterface
 */
require_once 'Zend/Feed/Reader/EntryInterface.php';

/**
 * @see Zend_Feed_Reader_EntryAbstract
 */
require_once 'Zend/Feed/Reader/EntryAbstract.php';

/**
 * @see Zend_Feed_Reader_Extension_Atom_Entry
 */
require_once 'Zend/Feed/Reader/Extension/Atom/Entry.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_Entry_Atom extends Zend_Feed_Reader_EntryAbstract implements Zend_Feed_Reader_EntryInterface
{
    /**
     * XPath query
     *
     * @var string
     */
    protected $_xpathQuery = '';

    /**
     * Constructor
     *
     * @param  DOMElement $entry
     * @param  int $entryKey
     * @param  string $type
     * @return void
     */
    public function __construct(DOMElement $entry, $entryKey, $type = null)
    {
        parent::__construct($entry, $entryKey, $type);

        // Everyone by now should know XPath indices start from 1 not 0
        $this->_xpathQuery = '//atom:entry[' . ($this->_entryKey + 1) . ']';

        $atomClass = Zend_Feed_Reader::getPluginLoader()->getClassName('Atom_Entry');
        $this->_extensions['Atom_Entry'] = new $atomClass($entry, $entryKey, $type);

        $threadClass = Zend_Feed_Reader::getPluginLoader()->getClassName('Thread_Entry');
        $this->_extensions['Thread_Entry'] = new $threadClass($entry, $entryKey, $type);
        
        $threadClass = Zend_Feed_Reader::getPluginLoader()->getClassName('DublinCore_Entry');
        $this->_extensions['DublinCore_Entry'] = new $threadClass($entry, $entryKey, $type);
    }

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

        $people = $this->getExtension('Atom')->getAuthors();

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

        $content = $this->getExtension('Atom')->getContent();

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

        $dateCreated = $this->getExtension('Atom')->getDateCreated();

        $this->_data['datecreated'] = $dateCreated;

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

        $dateModified = $this->getExtension('Atom')->getDateModified();

        $this->_data['datemodified'] = $dateModified;

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

        $description = $this->getExtension('Atom')->getDescription();

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

        $enclosure = $this->getExtension('Atom')->getEnclosure();

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

        $id = $this->getExtension('Atom')->getId();

        $this->_data['id'] = $id;

        return $this->_data['id'];
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

        $links = $this->getExtension('Atom')->getLinks();

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

        $title = $this->getExtension('Atom')->getTitle();

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

        $commentcount = $this->getExtension('Thread')->getCommentCount();

        if (!$commentcount) {
            $commentcount = $this->getExtension('Atom')->getCommentCount();
        }

        $this->_data['commentcount'] = $commentcount;

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

        $commentlink = $this->getExtension('Atom')->getCommentLink();

        $this->_data['commentlink'] = $commentlink;

        return $this->_data['commentlink'];
    }

    /**
     * Returns a URI pointing to a feed of all comments for this entry
     *
     * @return string
     */
    public function getCommentFeedLink()
    {
        if (array_key_exists('commentfeedlink', $this->_data)) {
            return $this->_data['commentfeedlink'];
        }

        $commentfeedlink = $this->getExtension('Atom')->getCommentFeedLink();

        $this->_data['commentfeedlink'] = $commentfeedlink;

        return $this->_data['commentfeedlink'];
    }
    
    /**
     * Get category data as a Zend_Feed_Reader_Collection_Category object
     *
     * @return Zend_Feed_Reader_Collection_Category
     */
    public function getCategories()
    {
        if (array_key_exists('categories', $this->_data)) {
            return $this->_data['categories'];
        }

        $categoryCollection = $this->getExtension('Atom')->getCategories();
        
        if (count($categoryCollection) == 0) {
            $categoryCollection = $this->getExtension('DublinCore')->getCategories();
        }

        $this->_data['categories'] = $categoryCollection;

        return $this->_data['categories'];
    }
    
    /**
     * Get source feed metadata from the entry
     *
     * @return Zend_Feed_Reader_Feed_Atom_Source|null
     */
    public function getSource()
    {
        if (array_key_exists('source', $this->_data)) {
            return $this->_data['source'];
        }

        $source = $this->getExtension('Atom')->getSource();

        $this->_data['source'] = $source;

        return $this->_data['source']; 
    }

    /**
     * Set the XPath query (incl. on all Extensions)
     *
     * @param DOMXPath $xpath
     */
    public function setXpath(DOMXPath $xpath)
    {
        parent::setXpath($xpath);
        foreach ($this->_extensions as $extension) {
            $extension->setXpath($this->_xpath);
        }
    }
}
