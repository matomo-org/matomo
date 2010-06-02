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
 * @version    $Id: Feed.php 22301 2010-05-26 10:15:13Z padraic $
 */

/**
 * @see Zend_Feed_Reader_Extension_FeedAbstract
 */
// require_once 'Zend/Feed/Reader/Extension/FeedAbstract.php';

/**
 * @see Zend_Date
 */
// require_once 'Zend/Date.php';

/**
 * @see Zend_Uri
 */
// require_once 'Zend/Uri.php';

/**
 * @see Zend_Feed_Reader_Collection_Author
 */
// require_once 'Zend/Feed/Reader/Collection/Author.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
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

        $list = $this->_xpath->query('//atom:author');

        $authors = array();

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
     * Get the feed image
     *
     * @return array|null
     */
    public function getImage()
    {
        if (array_key_exists('image', $this->_data)) {
            return $this->_data['image'];
        }

        $imageUrl = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:logo)');

        if (!$imageUrl) {
            $image = null;
        } else {
            $image = array('uri'=>$imageUrl);
        }

        $this->_data['image'] = $image;

        return $this->_data['image'];
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
     * Get an array of any supported Pusubhubbub endpoints
     *
     * @return array|null
     */
    public function getHubs()
    {
        if (array_key_exists('hubs', $this->_data)) {
            return $this->_data['hubs'];
        }
        $hubs = array();
        
        $list = $this->_xpath->query($this->getXpathPrefix()
            . '//atom:link[@rel="hub"]/@href');

        if ($list->length) {
            foreach ($list as $uri) {
                $hubs[] = $this->_absolutiseUri($uri->nodeValue);
            }
        } else {
            $hubs = null;
        }

        $this->_data['hubs'] = $hubs;

        return $this->_data['hubs'];
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
     * Get all categories
     *
     * @return Zend_Feed_Reader_Collection_Category
     */
    public function getCategories()
    {
        if (array_key_exists('categories', $this->_data)) {
            return $this->_data['categories'];
        }

        if ($this->getType() == Zend_Feed_Reader::TYPE_ATOM_10) {
            $list = $this->_xpath->query($this->getXpathPrefix() . '/atom:category');
        } else {
            /**
             * Since Atom 0.3 did not support categories, it would have used the
             * Dublin Core extension. However there is a small possibility Atom 0.3
             * may have been retrofittied to use Atom 1.0 instead.
             */
            $this->_xpath->registerNamespace('atom10', Zend_Feed_Reader::NAMESPACE_ATOM_10);
            $list = $this->_xpath->query($this->getXpathPrefix() . '/atom10:category');
        }

        if ($list->length) {
            $categoryCollection = new Zend_Feed_Reader_Collection_Category;
            foreach ($list as $category) {
                $categoryCollection[] = array(
                    'term' => $category->getAttribute('term'),
                    'scheme' => $category->getAttribute('scheme'),
                    'label' => $category->getAttribute('label')
                );
            }
        } else {
            return new Zend_Feed_Reader_Collection_Category;
        }

        $this->_data['categories'] = $categoryCollection;

        return $this->_data['categories'];
    }

    /**
     * Get an author entry in RSS format
     *
     * @param  DOMElement $element
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
        $dom = $this->getDomDocument();
        $prefixAtom03 = $dom->lookupPrefix(Zend_Feed_Reader::NAMESPACE_ATOM_03);
        $prefixAtom10 = $dom->lookupPrefix(Zend_Feed_Reader::NAMESPACE_ATOM_10);
        if ($dom->isDefaultNamespace(Zend_Feed_Reader::NAMESPACE_ATOM_10)
        || !empty($prefixAtom10)) {
            return Zend_Feed_Reader::TYPE_ATOM_10;
        }
        if ($dom->isDefaultNamespace(Zend_Feed_Reader::NAMESPACE_ATOM_03)
        || !empty($prefixAtom03)) {
            return Zend_Feed_Reader::TYPE_ATOM_03;
        }
    }
}
