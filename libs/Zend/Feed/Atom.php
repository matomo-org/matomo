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
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Atom.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Feed_Abstract
 */
require_once 'Zend/Feed/Abstract.php';

/**
 * @see Zend_Feed_Entry_Atom
 */
require_once 'Zend/Feed/Entry/Atom.php';


/**
 * Atom feed class
 *
 * The Zend_Feed_Atom class is a concrete subclass of the general
 * Zend_Feed_Abstract class, tailored for representing an Atom
 * feed. It shares all of the same methods with its abstract
 * parent. The distinction is made in the format of data that
 * Zend_Feed_Atom expects, and as a further pointer for users as to
 * what kind of feed object they have been passed.
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Atom extends Zend_Feed_Abstract
{

    /**
     * The classname for individual feed elements.
     *
     * @var string
     */
    protected $_entryClassName = 'Zend_Feed_Entry_Atom';

    /**
     * The element name for individual feed elements (Atom <entry>
     * elements).
     *
     * @var string
     */
    protected $_entryElementName = 'entry';

    /**
     * The default namespace for Atom feeds.
     *
     * @var string
     */
    protected $_defaultNamespace = 'atom';


    /**
     * Override Zend_Feed_Abstract to set up the $_element and $_entries aliases.
     *
     * @return void
     * @throws Zend_Feed_Exception
     */
    public function __wakeup()
    {
        parent::__wakeup();

        // Find the base feed element and create an alias to it.
        $element = $this->_element->getElementsByTagName('feed')->item(0);
        if (!$element) {
            // Try to find a single <entry> instead.
            $element = $this->_element->getElementsByTagName($this->_entryElementName)->item(0);
            if (!$element) {
                /**
                 * @see Zend_Feed_Exception
                 */
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('No root <feed> or <' . $this->_entryElementName
                                              . '> element found, cannot parse feed.');
            }

            $doc = new DOMDocument($this->_element->version,
                                   $this->_element->actualEncoding);
            $feed = $doc->appendChild($doc->createElement('feed'));
            $feed->appendChild($doc->importNode($element, true));
            $element = $feed;
        }

        $this->_element = $element;

        // Find the entries and save a pointer to them for speed and
        // simplicity.
        $this->_buildEntryCache();
    }


    /**
     * Easy access to <link> tags keyed by "rel" attributes.
     *
     * If $elt->link() is called with no arguments, we will attempt to
     * return the value of the <link> tag(s) like all other
     * method-syntax attribute access. If an argument is passed to
     * link(), however, then we will return the "href" value of the
     * first <link> tag that has a "rel" attribute matching $rel:
     *
     * $elt->link(): returns the value of the link tag.
     * $elt->link('self'): returns the href from the first <link rel="self"> in the entry.
     *
     * @param  string $rel The "rel" attribute to look for.
     * @return mixed
     */
    public function link($rel = null)
    {
        if ($rel === null) {
            return parent::__call('link', null);
        }

        // index link tags by their "rel" attribute.
        $links = parent::__get('link');
        if (!is_array($links)) {
            if ($links instanceof Zend_Feed_Element) {
                $links = array($links);
            } else {
                return $links;
            }
        }

        foreach ($links as $link) {
            if (empty($link['rel'])) {
                continue;
            }
            if ($rel == $link['rel']) {
                return $link['href'];
            }
        }

        return null;
    }


    /**
     * Make accessing some individual elements of the feed easier.
     *
     * Special accessors 'entry' and 'entries' are provided so that if
     * you wish to iterate over an Atom feed's entries, you can do so
     * using foreach ($feed->entries as $entry) or foreach
     * ($feed->entry as $entry).
     *
     * @param  string $var The property to access.
     * @return mixed
     */
    public function __get($var)
    {
        switch ($var) {
            case 'entry':
                // fall through to the next case
            case 'entries':
                return $this;

            default:
                return parent::__get($var);
        }
    }

    /**
     * Generate the header of the feed when working in write mode
     *
     * @param  array $array the data to use
     * @return DOMElement root node
     */
    protected function _mapFeedHeaders($array)
    {
        $feed = $this->_element->createElement('feed');
        $feed->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');

        $id = $this->_element->createElement('id', $array->link);
        $feed->appendChild($id);

        $title = $this->_element->createElement('title');
        $title->appendChild($this->_element->createCDATASection($array->title));
        $feed->appendChild($title);

        if (isset($array->author)) {
            $author = $this->_element->createElement('author');
            $name = $this->_element->createElement('name', $array->author);
            $author->appendChild($name);
            if (isset($array->email)) {
                $email = $this->_element->createElement('email', $array->email);
                $author->appendChild($email);
            }
            $feed->appendChild($author);
        }

        $updated = isset($array->lastUpdate) ? $array->lastUpdate : time();
        $updated = $this->_element->createElement('updated', date(DATE_ATOM, $updated));
        $feed->appendChild($updated);

        if (isset($array->published)) {
            $published = $this->_element->createElement('published', date(DATE_ATOM, $array->published));
            $feed->appendChild($published);
        }

        $link = $this->_element->createElement('link');
        $link->setAttribute('rel', 'self');
        $link->setAttribute('href', $array->link);
        if (isset($array->language)) {
            $link->setAttribute('hreflang', $array->language);
        }
        $feed->appendChild($link);

        if (isset($array->description)) {
            $subtitle = $this->_element->createElement('subtitle');
            $subtitle->appendChild($this->_element->createCDATASection($array->description));
            $feed->appendChild($subtitle);
        }

        if (isset($array->copyright)) {
            $copyright = $this->_element->createElement('rights', $array->copyright);
            $feed->appendChild($copyright);
        }

        if (isset($array->image)) {
            $image = $this->_element->createElement('logo', $array->image);
            $feed->appendChild($image);
        }

        $generator = !empty($array->generator) ? $array->generator : 'Zend_Feed';
        $generator = $this->_element->createElement('generator', $generator);
        $feed->appendChild($generator);

        return $feed;
    }

    /**
     * Generate the entries of the feed when working in write mode
     *
     * The following nodes are constructed for each feed entry
     * <entry>
     *    <id>url to feed entry</id>
     *    <title>entry title</title>
     *    <updated>last update</updated>
     *    <link rel="alternate" href="url to feed entry" />
     *    <summary>short text</summary>
     *    <content>long version, can contain html</content>
     * </entry>
     *
     * @param  array      $array the data to use
     * @param  DOMElement $root  the root node to use
     * @return void
     */
    protected function _mapFeedEntries(DOMElement $root, $array)
    {
        foreach ($array as $dataentry) {
            $entry = $this->_element->createElement('entry');

            $id = $this->_element->createElement('id', isset($dataentry->guid) ? $dataentry->guid : $dataentry->link);
            $entry->appendChild($id);

            $title = $this->_element->createElement('title');
            $title->appendChild($this->_element->createCDATASection($dataentry->title));
            $entry->appendChild($title);

            $updated = isset($dataentry->lastUpdate) ? $dataentry->lastUpdate : time();
            $updated = $this->_element->createElement('updated', date(DATE_ATOM, $updated));
            $entry->appendChild($updated);

            $link = $this->_element->createElement('link');
            $link->setAttribute('rel', 'alternate');
            $link->setAttribute('href', $dataentry->link);
            $entry->appendChild($link);

            $summary = $this->_element->createElement('summary');
            $summary->appendChild($this->_element->createCDATASection($dataentry->description));
            $entry->appendChild($summary);

            if (isset($dataentry->content)) {
                $content = $this->_element->createElement('content');
                $content->setAttribute('type', 'html');
                $content->appendChild($this->_element->createCDATASection($dataentry->content));
                $entry->appendChild($content);
            }

            if (isset($dataentry->category)) {
                foreach ($dataentry->category as $category) {
                    $node = $this->_element->createElement('category');
                    $node->setAttribute('term', $category['term']);
                    if (isset($category['scheme'])) {
                        $node->setAttribute('scheme', $category['scheme']);
                    }
                    $entry->appendChild($node);
                }
            }

            if (isset($dataentry->source)) {
                $source = $this->_element->createElement('source');
                $title = $this->_element->createElement('title', $dataentry->source['title']);
                $source->appendChild($title);
                $link = $this->_element->createElement('link', $dataentry->source['title']);
                $link->setAttribute('rel', 'alternate');
                $link->setAttribute('href', $dataentry->source['url']);
                $source->appendChild($link);
            }

            if (isset($dataentry->enclosure)) {
                foreach ($dataentry->enclosure as $enclosure) {
                    $node = $this->_element->createElement('link');
                    $node->setAttribute('rel', 'enclosure');
                    $node->setAttribute('href', $enclosure['url']);
                    if (isset($enclosure['type'])) {
                        $node->setAttribute('type', $enclosure['type']);
                    }
                    if (isset($enclosure['length'])) {
                        $node->setAttribute('length', $enclosure['length']);
                    }
                    $entry->appendChild($node);
                }
            }

            if (isset($dataentry->comments)) {
                $comments = $this->_element->createElementNS('http://wellformedweb.org/CommentAPI/',
                                                             'wfw:comment',
                                                             $dataentry->comments);
                $entry->appendChild($comments);
            }
            if (isset($dataentry->commentRss)) {
                $comments = $this->_element->createElementNS('http://wellformedweb.org/CommentAPI/',
                                                             'wfw:commentRss',
                                                             $dataentry->commentRss);
                $entry->appendChild($comments);
            }

            $root->appendChild($entry);
        }
    }

    /**
     * Override Zend_Feed_Element to allow formated feeds
     *
     * @return string
     */
    public function saveXml()
    {
        // Return a complete document including XML prologue.
        $doc = new DOMDocument($this->_element->ownerDocument->version,
                               $this->_element->ownerDocument->actualEncoding);
        $doc->appendChild($doc->importNode($this->_element, true));
        $doc->formatOutput = true;

        return $doc->saveXML();
    }

    /**
     * Send feed to a http client with the correct header
     *
     * @return void
     * @throws Zend_Feed_Exception if headers have already been sent
     */
    public function send()
    {
        if (headers_sent()) {
            /**
             * @see Zend_Feed_Exception
             */
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Cannot send ATOM because headers have already been sent.');
        }

        header('Content-Type: application/atom+xml; charset=' . $this->_element->ownerDocument->actualEncoding);

        echo $this->saveXML();
    }
}
