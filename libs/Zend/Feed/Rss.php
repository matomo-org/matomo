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
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Rss.php 19133 2009-11-20 19:44:09Z padraic $
 */


/**
 * @see Zend_Feed_Abstract
 */
require_once 'Zend/Feed/Abstract.php';

/**
 * @see Zend_Feed_Entry_Rss
 */
require_once 'Zend/Feed/Entry/Rss.php';


/**
 * RSS channel class
 *
 * The Zend_Feed_Rss class is a concrete subclass of
 * Zend_Feed_Abstract meant for representing RSS channels. It does not
 * add any methods to its parent, just provides a classname to check
 * against with the instanceof operator, and expects to be handling
 * RSS-formatted data instead of Atom.
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Rss extends Zend_Feed_Abstract
{
    /**
     * The classname for individual channel elements.
     *
     * @var string
     */
    protected $_entryClassName = 'Zend_Feed_Entry_Rss';

    /**
     * The element name for individual channel elements (RSS <item>s).
     *
     * @var string
     */
    protected $_entryElementName = 'item';

    /**
     * The default namespace for RSS channels.
     *
     * @var string
     */
    protected $_defaultNamespace = 'rss';

    /**
     * Override Zend_Feed_Abstract to set up the $_element and $_entries aliases.
     *
     * @return void
     * @throws Zend_Feed_Exception
     */
    public function __wakeup()
    {
        parent::__wakeup();

        // Find the base channel element and create an alias to it.
        $rdfTags = $this->_element->getElementsByTagNameNS('http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'RDF');
        if ($rdfTags->length != 0) {
            $this->_element = $rdfTags->item(0);
        } else  {
            $this->_element = $this->_element->getElementsByTagName('channel')->item(0);
        }
        if (!$this->_element) {
            /**
             * @see Zend_Feed_Exception
             */
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('No root <channel> element found, cannot parse channel.');
        }

        // Find the entries and save a pointer to them for speed and
        // simplicity.
        $this->_buildEntryCache();
    }


    /**
     * Make accessing some individual elements of the channel easier.
     *
     * Special accessors 'item' and 'items' are provided so that if
     * you wish to iterate over an RSS channel's items, you can do so
     * using foreach ($channel->items as $item) or foreach
     * ($channel->item as $item).
     *
     * @param  string $var The property to access.
     * @return mixed
     */
    public function __get($var)
    {
        switch ($var) {
            case 'item':
                // fall through to the next case
            case 'items':
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
        $channel = $this->_element->createElement('channel');

        $title = $this->_element->createElement('title');
        $title->appendChild($this->_element->createCDATASection($array->title));
        $channel->appendChild($title);

        $link = $this->_element->createElement('link', $array->link);
        $channel->appendChild($link);

        $desc = isset($array->description) ? $array->description : '';
        $description = $this->_element->createElement('description');
        $description->appendChild($this->_element->createCDATASection($desc));
        $channel->appendChild($description);

        $pubdate = isset($array->lastUpdate) ? $array->lastUpdate : time();
        $pubdate = $this->_element->createElement('pubDate', date(DATE_RSS, $pubdate));
        $channel->appendChild($pubdate);

        if (isset($array->published)) {
            $lastBuildDate = $this->_element->createElement('lastBuildDate', date(DATE_RSS, $array->published));
            $channel->appendChild($lastBuildDate);
        }

        $editor = '';
        if (!empty($array->email)) {
            $editor .= $array->email;
        }
        if (!empty($array->author)) {
            $editor .= ' (' . $array->author . ')';
        }
        if (!empty($editor)) {
            $author = $this->_element->createElement('managingEditor', ltrim($editor));
            $channel->appendChild($author);
        }
        if (isset($array->webmaster)) {
            $channel->appendChild($this->_element->createElement('webMaster', $array->webmaster));
        }

        if (!empty($array->copyright)) {
            $copyright = $this->_element->createElement('copyright', $array->copyright);
            $channel->appendChild($copyright);
        }

        if (isset($array->category)) {
            $category = $this->_element->createElement('category', $array->category);
            $channel->appendChild($category);
        }

        if (!empty($array->image)) {
            $image = $this->_element->createElement('image');
            $url = $this->_element->createElement('url', $array->image);
            $image->appendChild($url);
            $imagetitle = $this->_element->createElement('title');
            $imagetitle->appendChild($this->_element->createCDATASection($array->title));
            $image->appendChild($imagetitle);
            $imagelink = $this->_element->createElement('link', $array->link);
            $image->appendChild($imagelink);

            $channel->appendChild($image);
        }

        $generator = !empty($array->generator) ? $array->generator : 'Zend_Feed';
        $generator = $this->_element->createElement('generator', $generator);
        $channel->appendChild($generator);

        if (!empty($array->language)) {
            $language = $this->_element->createElement('language', $array->language);
            $channel->appendChild($language);
        }

        $doc = $this->_element->createElement('docs', 'http://blogs.law.harvard.edu/tech/rss');
        $channel->appendChild($doc);

        if (isset($array->cloud)) {
            $cloud = $this->_element->createElement('cloud');
            $cloud->setAttribute('domain', $array->cloud['uri']->getHost());
            $cloud->setAttribute('port', $array->cloud['uri']->getPort());
            $cloud->setAttribute('path', $array->cloud['uri']->getPath());
            $cloud->setAttribute('registerProcedure', $array->cloud['procedure']);
            $cloud->setAttribute('protocol', $array->cloud['protocol']);
            $channel->appendChild($cloud);
        }

        if (isset($array->ttl)) {
            $ttl = $this->_element->createElement('ttl', $array->ttl);
            $channel->appendChild($ttl);
        }

        if (isset($array->rating)) {
            $rating = $this->_element->createElement('rating', $array->rating);
            $channel->appendChild($rating);
        }

        if (isset($array->textInput)) {
            $textinput = $this->_element->createElement('textInput');
            $textinput->appendChild($this->_element->createElement('title', $array->textInput['title']));
            $textinput->appendChild($this->_element->createElement('description', $array->textInput['description']));
            $textinput->appendChild($this->_element->createElement('name', $array->textInput['name']));
            $textinput->appendChild($this->_element->createElement('link', $array->textInput['link']));
            $channel->appendChild($textinput);
        }

        if (isset($array->skipHours)) {
            $skipHours = $this->_element->createElement('skipHours');
            foreach ($array->skipHours as $hour) {
                $skipHours->appendChild($this->_element->createElement('hour', $hour));
            }
            $channel->appendChild($skipHours);
        }

        if (isset($array->skipDays)) {
            $skipDays = $this->_element->createElement('skipDays');
            foreach ($array->skipDays as $day) {
                $skipDays->appendChild($this->_element->createElement('day', $day));
            }
            $channel->appendChild($skipDays);
        }

        if (isset($array->itunes)) {
            $this->_buildiTunes($channel, $array);
        }

        return $channel;
    }

    /**
     * Adds the iTunes extensions to a root node
     *
     * @param  DOMElement $root
     * @param  array $array
     * @return void
     */
    private function _buildiTunes(DOMElement $root, $array)
    {
        /* author node */
        $author = '';
        if (isset($array->itunes->author)) {
            $author = $array->itunes->author;
        } elseif (isset($array->author)) {
            $author = $array->author;
        }
        if (!empty($author)) {
            $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:author', $author);
            $root->appendChild($node);
        }

        /* owner node */
        $author = '';
        $email = '';
        if (isset($array->itunes->owner)) {
            if (isset($array->itunes->owner['name'])) {
                $author = $array->itunes->owner['name'];
            }
            if (isset($array->itunes->owner['email'])) {
                $email = $array->itunes->owner['email'];
            }
        }
        if (empty($author) && isset($array->author)) {
            $author = $array->author;
        }
        if (empty($email) && isset($array->email)) {
            $email = $array->email;
        }
        if (!empty($author) || !empty($email)) {
            $owner = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:owner');
            if (!empty($author)) {
                $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:name', $author);
                $owner->appendChild($node);
            }
            if (!empty($email)) {
                $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:email', $email);
                $owner->appendChild($node);
            }
            $root->appendChild($owner);
        }
        $image = '';
        if (isset($array->itunes->image)) {
            $image = $array->itunes->image;
        } elseif (isset($array->image)) {
            $image = $array->image;
        }
        if (!empty($image)) {
            $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:image');
            $node->setAttribute('href', $image);
            $root->appendChild($node);
        }
        $subtitle = '';
        if (isset($array->itunes->subtitle)) {
            $subtitle = $array->itunes->subtitle;
        } elseif (isset($array->description)) {
            $subtitle = $array->description;
        }
        if (!empty($subtitle)) {
            $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:subtitle', $subtitle);
            $root->appendChild($node);
        }
        $summary = '';
        if (isset($array->itunes->summary)) {
            $summary = $array->itunes->summary;
        } elseif (isset($array->description)) {
            $summary = $array->description;
        }
        if (!empty($summary)) {
            $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:summary', $summary);
            $root->appendChild($node);
        }
        if (isset($array->itunes->block)) {
            $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:block', $array->itunes->block);
            $root->appendChild($node);
        }
        if (isset($array->itunes->explicit)) {
            $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:explicit', $array->itunes->explicit);
            $root->appendChild($node);
        }
        if (isset($array->itunes->keywords)) {
            $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:keywords', $array->itunes->keywords);
            $root->appendChild($node);
        }
        if (isset($array->itunes->new_feed_url)) {
            $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:new-feed-url', $array->itunes->new_feed_url);
            $root->appendChild($node);
        }
        if (isset($array->itunes->category)) {
            foreach ($array->itunes->category as $i => $category) {
                $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:category');
                $node->setAttribute('text', $category['main']);
                $root->appendChild($node);
                $add_end_category = false;
                if (!empty($category['sub'])) {
                    $add_end_category = true;
                    $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:category');
                    $node->setAttribute('text', $category['sub']);
                    $root->appendChild($node);
                }
                if ($i > 0 || $add_end_category) {
                    $node = $this->_element->createElementNS('http://www.itunes.com/DTDs/Podcast-1.0.dtd', 'itunes:category');
                    $root->appendChild($node);
                }
            }
        }
    }

    /**
     * Generate the entries of the feed when working in write mode
     *
     * The following nodes are constructed for each feed entry
     * <item>
     *    <title>entry title</title>
     *    <link>url to feed entry</link>
     *    <guid>url to feed entry</guid>
     *    <description>short text</description>
     *    <content:encoded>long version, can contain html</content:encoded>
     * </item>
     *
     * @param  DOMElement $root the root node to use
     * @param  array $array the data to use
     * @return void
     */
    protected function _mapFeedEntries(DOMElement $root, $array)
    {
        Zend_Feed::registerNamespace('content', 'http://purl.org/rss/1.0/modules/content/');

        foreach ($array as $dataentry) {
            $item = $this->_element->createElement('item');

            $title = $this->_element->createElement('title');
            $title->appendChild($this->_element->createCDATASection($dataentry->title));
            $item->appendChild($title);

            if (isset($dataentry->author)) {
                $author = $this->_element->createElement('author', $dataentry->author);
                $item->appendChild($author);
            }

            $link = $this->_element->createElement('link', $dataentry->link);
            $item->appendChild($link);

            if (isset($dataentry->guid)) {
                $guid = $this->_element->createElement('guid', $dataentry->guid);
                if (!Zend_Uri::check($dataentry->guid)) {
                    $guid->setAttribute('isPermaLink', 'false');
                }
                $item->appendChild($guid);
            }

            $description = $this->_element->createElement('description');
            $description->appendChild($this->_element->createCDATASection($dataentry->description));
            $item->appendChild($description);

            if (isset($dataentry->content)) {
                $content = $this->_element->createElement('content:encoded');
                $content->appendChild($this->_element->createCDATASection($dataentry->content));
                $item->appendChild($content);
            }

            $pubdate = isset($dataentry->lastUpdate) ? $dataentry->lastUpdate : time();
            $pubdate = $this->_element->createElement('pubDate', date(DATE_RSS, $pubdate));
            $item->appendChild($pubdate);

            if (isset($dataentry->category)) {
                foreach ($dataentry->category as $category) {
                    $node = $this->_element->createElement('category', $category['term']);
                    if (isset($category['scheme'])) {
                        $node->setAttribute('domain', $category['scheme']);
                    }
                    $item->appendChild($node);
                }
            }

            if (isset($dataentry->source)) {
                $source = $this->_element->createElement('source', $dataentry->source['title']);
                $source->setAttribute('url', $dataentry->source['url']);
                $item->appendChild($source);
            }

            if (isset($dataentry->comments)) {
                $comments = $this->_element->createElement('comments', $dataentry->comments);
                $item->appendChild($comments);
            }
            if (isset($dataentry->commentRss)) {
                $comments = $this->_element->createElementNS('http://wellformedweb.org/CommentAPI/',
                                                             'wfw:commentRss',
                                                             $dataentry->commentRss);
                $item->appendChild($comments);
            }


            if (isset($dataentry->enclosure)) {
                foreach ($dataentry->enclosure as $enclosure) {
                    $node = $this->_element->createElement('enclosure');
                    $node->setAttribute('url', $enclosure['url']);
                    if (isset($enclosure['type'])) {
                        $node->setAttribute('type', $enclosure['type']);
                    }
                    if (isset($enclosure['length'])) {
                        $node->setAttribute('length', $enclosure['length']);
                    }
                    $item->appendChild($node);
                }
            }

            $root->appendChild($item);
        }
    }

    /**
     * Override Zend_Feed_Element to include <rss> root node
     *
     * @return string
     */
    public function saveXml()
    {
        // Return a complete document including XML prologue.
        $doc = new DOMDocument($this->_element->ownerDocument->version,
                               $this->_element->ownerDocument->actualEncoding);
        $root = $doc->createElement('rss');

        // Use rss version 2.0
        $root->setAttribute('version', '2.0');

        // Content namespace
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
        $root->appendChild($doc->importNode($this->_element, true));

        // Append root node
        $doc->appendChild($root);

        // Format output
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
            throw new Zend_Feed_Exception('Cannot send RSS because headers have already been sent.');
        }

        header('Content-Type: application/rss+xml; charset=' . $this->_element->ownerDocument->actualEncoding);

        echo $this->saveXml();
    }

}
