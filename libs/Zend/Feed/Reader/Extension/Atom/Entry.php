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
 * @version    $Id: Entry.php 20507 2010-01-21 22:21:07Z padraic $
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
 * @see Zend_Feed_Reader_Collection_Category
 */
require_once 'Zend/Feed/Reader/Collection/Category.php';

/**
 * @see Zend_Feed_Reader_Feed_Atom_Source
 */
require_once 'Zend/Feed/Reader/Feed/Atom/Source.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
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

        $authors = array();
        $list = $this->getXpath()->query($this->getXpathPrefix() . '//atom:author');

        if (!$list->length) {
            /**
             * TODO: Limit query to feed level els only!
             */
            $list = $this->getXpath()->query('//atom:author');
        }

        if ($list->length) {
            foreach ($list as $author) {
                $author = $this->_getAuthor($author);
                if (!empty($author)) {
                    $authors[] = $author;
                }
            }
        }

        if (count($authors) == 0) {
            $authors = null;
        } else {
            $authors = new Zend_Feed_Reader_Collection_Author(
                Zend_Feed_Reader::arrayUnique($authors)
            );
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
        
        $content = null;
        
        $el = $this->getXpath()->query($this->getXpathPrefix() . '/atom:content');
        if($el->length > 0) {
            $el = $el->item(0);
            $type = $el->getAttribute('type');
            switch ($type) {
                case '':
                case 'text':
                case 'text/plain':
                case 'html':
                case 'text/html':
                    $content = $el->nodeValue;
                break;
                case 'xhtml':
                    $this->getXpath()->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
                    $xhtml = $this->getXpath()->query(
                        $this->getXpathPrefix() . '/atom:content/xhtml:div'
                    )->item(0);
                    //$xhtml->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
                    $d = new DOMDocument('1.0', $this->getEncoding());
                    $xhtmls = $d->importNode($xhtml, true);
                    $d->appendChild($xhtmls);
                    $content = $this->_collectXhtml(
                        $d->saveXML(),
                        $d->lookupPrefix('http://www.w3.org/1999/xhtml')
                    );
                break;
            }
        }
        
        //var_dump($content); exit;

        if (!$content) {
            $content = $this->getDescription();
        }

        $this->_data['content'] = trim($content);

        return $this->_data['content'];
    }
    
    /**
     * Parse out XHTML to remove the namespacing
     */
    protected function _collectXhtml($xhtml, $prefix)
    {
        if (!empty($prefix)) $prefix = $prefix . ':';
        $matches = array(
            "/<\?xml[^<]*>[^<]*<" . $prefix . "div[^<]*/",
            "/<\/" . $prefix . "div>\s*$/"
        );
        $xhtml = preg_replace($matches, '', $xhtml);
        if (!empty($prefix)) {
            $xhtml = preg_replace("/(<[\/]?)" . $prefix . "([a-zA-Z]+)/", '$1$2', $xhtml);
        }
        return $xhtml;
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

        if ($this->_getAtomType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $dateCreated = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:created)');
        } else {
            $dateCreated = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:published)');
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

        if ($this->_getAtomType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $dateModified = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:modified)');
        } else {
            $dateModified = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:updated)');
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

        $description = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:summary)');

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

        $nodeList = $this->getXpath()->query($this->getXpathPrefix() . '/atom:link[@rel="enclosure"]');

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

        $id = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:id)');

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

        $baseUrl = $this->getXpath()->evaluate('string('
            . $this->getXpathPrefix() . '/@xml:base[1]'
        . ')');

        if (!$baseUrl) {
            $baseUrl = $this->getXpath()->evaluate('string(//@xml:base[1])');
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

        $list = $this->getXpath()->query(
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

        $title = $this->getXpath()->evaluate('string(' . $this->getXpathPrefix() . '/atom:title)');

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

        $this->getXpath()->registerNamespace('thread10', 'http://purl.org/syndication/thread/1.0');
        $list = $this->getXpath()->query(
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

        $list = $this->getXpath()->query(
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

        $list = $this->getXpath()->query(
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
     * Get all categories
     *
     * @return Zend_Feed_Reader_Collection_Category
     */
    public function getCategories()
    {
        if (array_key_exists('categories', $this->_data)) {
            return $this->_data['categories'];
        }

        if ($this->_getAtomType() == Zend_Feed_Reader::TYPE_ATOM_10) {
            $list = $this->getXpath()->query($this->getXpathPrefix() . '//atom:category');
        } else {
            /**
             * Since Atom 0.3 did not support categories, it would have used the
             * Dublin Core extension. However there is a small possibility Atom 0.3
             * may have been retrofittied to use Atom 1.0 instead.
             */
            $this->getXpath()->registerNamespace('atom10', Zend_Feed_Reader::NAMESPACE_ATOM_10);
            $list = $this->getXpath()->query($this->getXpathPrefix() . '//atom10:category');
        }

        if ($list->length) {
            $categoryCollection = new Zend_Feed_Reader_Collection_Category;
            foreach ($list as $category) {
                $categoryCollection[] = array(
                    'term' => $category->getAttribute('term'),
                    'scheme' => $category->getAttribute('scheme'),
                    'label' => html_entity_decode($category->getAttribute('label'))
                );
            }
        } else {
            return new Zend_Feed_Reader_Collection_Category;
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
        
        $source = null;
        // TODO: Investigate why _getAtomType() fails here. Is it even needed?
        if ($this->getType() == Zend_Feed_Reader::TYPE_ATOM_10) {
            $list = $this->getXpath()->query($this->getXpathPrefix() . '/atom:source[1]');
            if ($list->length) {
                $element = $list->item(0);
                $source = new Zend_Feed_Reader_Feed_Atom_Source($element, $this->getXpathPrefix());
            }
        }
        
        $this->_data['source'] = $source;
        return $this->_data['source']; 
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
        $author = array();

        $emailNode = $element->getElementsByTagName('email');
        $nameNode  = $element->getElementsByTagName('name');
        $uriNode   = $element->getElementsByTagName('uri');
        
        if ($emailNode->length && strlen($emailNode->item(0)->nodeValue) > 0) {
            $author['email'] = $emailNode->item(0)->nodeValue;
        }

        if ($nameNode->length && strlen($nameNode->item(0)->nodeValue) > 0) {
            $author['name'] = $nameNode->item(0)->nodeValue;
        }

        if ($uriNode->length && strlen($uriNode->item(0)->nodeValue) > 0) {
            $author['uri'] = $uriNode->item(0)->nodeValue;
        }

        if (empty($author)) {
            return null;
        }
        return $author;
    }

    /**
     * Register the default namespaces for the current feed format
     */
    protected function _registerNamespaces()
    {
        switch ($this->_getAtomType()) {
            case Zend_Feed_Reader::TYPE_ATOM_03:
                $this->getXpath()->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_03);
                break;
            default:
                $this->getXpath()->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_10);
                break;
        }
    }

    /**
     * Detect the presence of any Atom namespaces in use
     */
    protected function _getAtomType()
    {
        $dom = $this->getDomDocument();
        $prefixAtom03 = $dom->lookupPrefix(Zend_Feed_Reader::NAMESPACE_ATOM_03);
        $prefixAtom10 = $dom->lookupPrefix(Zend_Feed_Reader::NAMESPACE_ATOM_10);
        if ($dom->isDefaultNamespace(Zend_Feed_Reader::NAMESPACE_ATOM_03)
        || !empty($prefixAtom03)) {
            return Zend_Feed_Reader::TYPE_ATOM_03;
        }
        if ($dom->isDefaultNamespace(Zend_Feed_Reader::NAMESPACE_ATOM_10)
        || !empty($prefixAtom10)) {
            return Zend_Feed_Reader::TYPE_ATOM_10;
        }
    }
}
