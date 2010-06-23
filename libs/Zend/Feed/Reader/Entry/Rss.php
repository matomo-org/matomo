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
 * @version    $Id: Rss.php 18367 2009-09-22 14:55:59Z padraic $
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
 * @see Zend_Feed_Reader_Extension_DublinCore_Entry
 */
require_once 'Zend/Feed/Reader/Extension/DublinCore/Entry.php';

/**
 * @see Zend_Feed_Reader_Extension_Content_Entry
 */
require_once 'Zend/Feed/Reader/Extension/Content/Entry.php';

/**
 * @see Zend_Feed_Reader_Extension_Atom_Entry
 */
require_once 'Zend/Feed/Reader/Extension/Atom/Entry.php';

/**
 * @see Zend_Feed_Reader_Extension_WellformedWeb_Entry
 */
require_once 'Zend/Feed/Reader/Extension/WellFormedWeb/Entry.php';

/**
 * @see Zend_Feed_Reader_Extension_Slash_Entry
 */
require_once 'Zend/Feed/Reader/Extension/Slash/Entry.php';

/**
 * @see Zend_Feed_Reader_Extension_Thread_Entry
 */
require_once 'Zend/Feed/Reader/Extension/Thread/Entry.php';

/**
 * @see Zend_Date
 */
require_once 'Zend/Date.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_Entry_Rss extends Zend_Feed_Reader_EntryAbstract implements Zend_Feed_Reader_EntryInterface
{

    /**
     * XPath query for RDF
     *
     * @var string
     */
    protected $_xpathQueryRdf = '';

    /**
     * XPath query for RSS
     *
     * @var string
     */
    protected $_xpathQueryRss = '';

    /**
     * Constructor
     *
     * @param  Zend_Feed_Entry_Abstract $entry
     * @param  string $entryKey
     * @param  string $type
     * @return void
     */
    public function __construct(DOMElement $entry, $entryKey, $type = null)
    {
        parent::__construct($entry, $entryKey, $type);
        $this->_xpathQueryRss = '//item[' . ($this->_entryKey+1) . ']';
        $this->_xpathQueryRdf = '//rss:item[' . ($this->_entryKey+1) . ']';

        $pluginLoader = Zend_Feed_Reader::getPluginLoader();

        $dublinCoreClass = $pluginLoader->getClassName('DublinCore_Entry');
        $this->_extensions['DublinCore_Entry'] = new $dublinCoreClass($entry, $entryKey, $type);

        $contentClass   = $pluginLoader->getClassName('Content_Entry');
        $this->_extensions['Content_Entry'] = new $contentClass($entry, $entryKey, $type);

        $atomClass   = $pluginLoader->getClassName('Atom_Entry');
        $this->_extensions['Atom_Entry'] = new $atomClass($entry, $entryKey, $type);

        $wfwClass   = $pluginLoader->getClassName('WellFormedWeb_Entry');
        $this->_extensions['WellFormedWeb_Entry'] = new $wfwClass($entry, $entryKey, $type);

        $slashClass   = $pluginLoader->getClassName('Slash_Entry');
        $this->_extensions['Slash_Entry'] = new $slashClass($entry, $entryKey, $type);

        $threadClass   = $pluginLoader->getClassName('Thread_Entry');
        $this->_extensions['Thread_Entry'] = new $threadClass($entry, $entryKey, $type);
    }

    /**
     * Get an author entry
     *
     * @param DOMElement $element
     * @return string
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

        $authors = array();
        // @todo: create a list from all potential sources rather than from alternatives
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 &&
            $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $list = $this->_xpath->evaluate($this->_xpathQueryRss.'//author');
        } else {
            $list = $this->_xpath->evaluate($this->_xpathQueryRdf.'//rss:author');
        }
        if (!$list->length) {
            if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
                $list = $this->_xpath->query('//author');
            } else {
                $list = $this->_xpath->query('//rss:author');
            }
        }

        if ($list->length) {
            foreach ($list as $author) {
                if ($this->getType() == Zend_Feed_Reader::TYPE_RSS_20
                    && preg_match("/\(([^\)]+)\)/", $author->nodeValue, $matches, PREG_OFFSET_CAPTURE)
                ) {
                    // source name from RSS 2.0 <author>
                    // format "joe@example.com (Joe Bloggs)"
                    $authors[] = $matches[1][0];
                } else {
                    $authors[] = $author->nodeValue;
                }
            }

            $authors = array_unique($authors);
        }

        if (empty($authors)) {
            $authors = $this->getExtension('DublinCore')->getAuthors();
        }

        if (empty($authors)) {
            $authors = $this->getExtension('Atom')->getAuthors();
        }

        $this->_data['authors'] = $authors;

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

        $content = $this->getExtension('Content')->getContent();

        if (!$content) {
            $content = $this->getDescription();
        }

        if (empty($content)) {
            $content = $this->getExtension('Atom')->getContent();
        }

        $this->_data['content'] = $content;

        return $this->_data['content'];
    }

    /**
     * Get the entry's date of creation
     *
     * @return string
     */
    public function getDateCreated()
    {
        return $this->getDateModified();
    }

    /**
     * Get the entry's date of modification
     *
     * @return string
     */
    public function getDateModified()
    {
        if (array_key_exists('datemodified', $this->_data)) {
            return $this->_data['datemodified'];
        }

        $dateModified = null;
        $date = null;

        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10
            && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090
        ) {
            $dateModified = $this->_xpath->evaluate('string('.$this->_xpathQueryRss.'/pubDate)');
            if ($dateModified) {
                $dateStandards = array(Zend_Date::RSS, Zend_Date::RFC_822,
                Zend_Date::RFC_2822, Zend_Date::DATES);
                $date = new Zend_Date;
                foreach ($dateStandards as $standard) {
                    try {
                        $date->set($dateModified, $standard);
                        break;
                    } catch (Zend_Date_Exception $e) {
                        if ($standard == Zend_Date::DATES) {
                            require_once 'Zend/Feed/Exception.php';
                            throw new Zend_Feed_Exception(
                                'Could not load date due to unrecognised'
                                .' format (should follow RFC 822 or 2822):'
                                . $e->getMessage()
                            );
                        }
                    }
                }
            }
        }

        if (!$date) {
            $date = $this->getExtension('DublinCore')->getDate();
        }

        if (!$date) {
            $date = $this->getExtension('Atom')->getDateModified();
        }

        if (!$date) {
            $date = null;
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

        $description = null;

        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10
            && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090
        ) {
            $description = $this->_xpath->evaluate('string('.$this->_xpathQueryRss.'/description)');
        } else {
            $description = $this->_xpath->evaluate('string('.$this->_xpathQueryRdf.'/rss:description)');
        }

        if (!$description) {
            $description = $this->getExtension('DublinCore')->getDescription();
        }

        if (empty($description)) {
            $description = $this->getExtension('Atom')->getDescription();
        }

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
     * @return string
     */
    public function getEnclosure()
    {
        if (array_key_exists('enclosure', $this->_data)) {
            return $this->_data['enclosure'];
        }

        $enclosure = null;

        if ($this->getType() == Zend_Feed_Reader::TYPE_RSS_20) {
            $nodeList = $this->_xpath->query($this->_xpathQueryRss . '/enclosure');

            if ($nodeList->length > 0) {
                $enclosure = new stdClass();
                $enclosure->url    = $nodeList->item(0)->getAttribute('url');
                $enclosure->length = $nodeList->item(0)->getAttribute('length');
                $enclosure->type   = $nodeList->item(0)->getAttribute('type');
            }
        }

        if (!$enclosure) {
            $enclosure = $this->getExtension('Atom')->getEnclosure();
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

        $id = null;

        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10
            && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090
        ) {
            $id = $this->_xpath->evaluate('string('.$this->_xpathQueryRss.'/guid)');
        }

        if (!$id) {
            $id = $this->getExtension('DublinCore')->getId();
        }

        if (empty($id)) {
            $id = $this->getExtension('Atom')->getId();
        }

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

        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 &&
            $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $list = $this->_xpath->query($this->_xpathQueryRss.'//link');
        } else {
            $list = $this->_xpath->query($this->_xpathQueryRdf.'//rss:link');
        }

        if (!$list->length) {
            $links = $this->getExtension('Atom')->getLinks();
        } else {
            foreach ($list as $link) {
                $links[] = $link->nodeValue;
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

        $title = null;

        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10
            && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090
        ) {
            $title = $this->_xpath->evaluate('string('.$this->_xpathQueryRss.'/title)');
        } else {
            $title = $this->_xpath->evaluate('string('.$this->_xpathQueryRdf.'/rss:title)');
        }

        if (!$title) {
            $title = $this->getExtension('DublinCore')->getTitle();
        }

        if (!$title) {
            $title = $this->getExtension('Atom')->getTitle();
        }

        if (!$title) {
            $title = null;
        }

        $this->_data['title'] = $title;

        return $this->_data['title'];
    }

    /**
     * Get the number of comments/replies for current entry
     *
     * @return string|null
     */
    public function getCommentCount()
    {
        if (array_key_exists('commentcount', $this->_data)) {
            return $this->_data['commentcount'];
        }

        $commentcount = $this->getExtension('Slash')->getCommentCount();

        if (!$commentcount) {
            $commentcount = $this->getExtension('Thread')->getCommentCount();
        }

        if (!$commentcount) {
            $commentcount = $this->getExtension('Atom')->getCommentCount();
        }

        if (!$commentcount) {
            $commentcount = null;
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

        $commentlink = null;

        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10
            && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090
        ) {
            $commentlink = $this->_xpath->evaluate('string('.$this->_xpathQueryRss.'/comments)');
        }

        if (!$commentlink) {
            $commentlink = $this->getExtension('Atom')->getCommentLink();
        }

        if (!$commentlink) {
            $commentlink = null;
        }

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

        $commentfeedlink = $this->getExtension('WellFormedWeb')->getCommentFeedLink();

        if (!$commentfeedlink) {
            $commentfeedlink = $this->getExtension('Atom')->getCommentFeedLink('rss');
        }

        if (!$commentfeedlink) {
            $commentfeedlink = $this->getExtension('Atom')->getCommentFeedLink('rdf');
        }

        if (!$commentfeedlink) {
            $commentfeedlink = null;
        }

        $this->_data['commentfeedlink'] = $commentfeedlink;

        return $this->_data['commentfeedlink'];
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
