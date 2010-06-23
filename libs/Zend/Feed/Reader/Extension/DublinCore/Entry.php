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
 * @version    $Id: Entry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Feed_Reader
 */
// require_once 'Zend/Feed/Reader.php';

/**
 * @see Zend_Feed_Reader_Extension_EntryAbstract
 */
// require_once 'Zend/Feed/Reader/Extension/EntryAbstract.php';

/**
 * @see Zend_Date
 */
// require_once 'Zend/Date.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_Extension_DublinCore_Entry
    extends Zend_Feed_Reader_Extension_EntryAbstract
{
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
        $list = $this->_xpath->evaluate($this->getXpathPrefix() . '//dc11:creator');

        if (!$list->length) {
            $list = $this->_xpath->evaluate($this->getXpathPrefix() . '//dc10:creator');
        }
        if (!$list->length) {
            $list = $this->_xpath->evaluate($this->getXpathPrefix() . '//dc11:publisher');

            if (!$list->length) {
                $list = $this->_xpath->evaluate($this->getXpathPrefix() . '//dc10:publisher');
            }
        }

        if ($list->length) {
            foreach ($list as $author) {
                $authors[] = array(
                    'name' => $author->nodeValue
                );
            }
            $authors = new Zend_Feed_Reader_Collection_Author(
                Zend_Feed_Reader::arrayUnique($authors)
            );
        } else {
            $authors = null;
        }

        $this->_data['authors'] = $authors;

        return $this->_data['authors'];
    }
    
    /**
     * Get categories (subjects under DC)
     *
     * @return Zend_Feed_Reader_Collection_Category
     */
    public function getCategories()
    {
        if (array_key_exists('categories', $this->_data)) {
            return $this->_data['categories'];
        }
        
        $list = $this->_xpath->evaluate($this->getXpathPrefix() . '//dc11:subject');

        if (!$list->length) {
            $list = $this->_xpath->evaluate($this->getXpathPrefix() . '//dc10:subject');
        }
        
        if ($list->length) {
            $categoryCollection = new Zend_Feed_Reader_Collection_Category;
            foreach ($list as $category) {
                $categoryCollection[] = array(
                    'term' => $category->nodeValue,
                    'scheme' => null,
                    'label' => $category->nodeValue,
                );
            }
        } else {
            $categoryCollection = new Zend_Feed_Reader_Collection_Category;
        }
        
        $this->_data['categories'] = $categoryCollection;
        return $this->_data['categories'];  
    }
    

    /**
     * Get the entry content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->getDescription();
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
        $description = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc11:description)');

        if (!$description) {
            $description = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc10:description)');
        }

        if (!$description) {
            $description = null;
        }

        $this->_data['description'] = $description;

        return $this->_data['description'];
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
        $id = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc11:identifier)');

        if (!$id) {
            $id = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc10:identifier)');
        }

        $this->_data['id'] = $id;

        return $this->_data['id'];
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
        $title = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc11:title)');

        if (!$title) {
            $title = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc10:title)');
        }

        if (!$title) {
            $title = null;
        }

        $this->_data['title'] = $title;

        return $this->_data['title'];
    }

    /**
     *
     *
     * @return Zend_Date|null
     */
    public function getDate()
    {
        if (array_key_exists('date', $this->_data)) {
            return $this->_data['date'];
        }

        $d    = null;
        $date = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc11:date)');

        if (!$date) {
            $date = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc10:date)');
        }

        if ($date) {
            $d = new Zend_Date;
            $d->set($date, Zend_Date::ISO_8601);
        }

        $this->_data['date'] = $d;

        return $this->_data['date'];
    }

    /**
     * Register DC namespaces
     *
     * @return void
     */
    protected function _registerNamespaces()
    {
        $this->_xpath->registerNamespace('dc10', 'http://purl.org/dc/elements/1.0/');
        $this->_xpath->registerNamespace('dc11', 'http://purl.org/dc/elements/1.1/');
    }
}
