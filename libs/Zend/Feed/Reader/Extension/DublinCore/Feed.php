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
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_Extension_DublinCore_Feed
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

        $authors = array();
        $list    = $this->_xpath->query('//dc11:creator');

        if (!$list->length) {
            $list = $this->_xpath->query('//dc10:creator');
        }
        if (!$list->length) {
            $list = $this->_xpath->query('//dc11:publisher');

            if (!$list->length) {
                $list = $this->_xpath->query('//dc10:publisher');
            }
        }

        foreach ($list as $authorObj) {
            $authors[] = $authorObj->nodeValue;
        }

        if (!empty($authors)) {
            $authors = array_unique($authors);
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
        $copyright = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc11:rights)');

        if (!$copyright) {
            $copyright = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc10:rights)');
        }

        if (!$copyright) {
            $copyright = null;
        }

        $this->_data['copyright'] = $copyright;

        return $this->_data['copyright'];
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
     * Get the feed ID
     *
     * @return string|null
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
     * Get the feed language
     *
     * @return string|null
     */
    public function getLanguage()
    {
        if (array_key_exists('language', $this->_data)) {
            return $this->_data['language'];
        }

        $language = null;
        $language = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc11:language)');

        if (!$language) {
            $language = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc10:language)');
        }

        if (!$language) {
            $language = null;
        }

        $this->_data['language'] = $language;

        return $this->_data['language'];
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

        $d = null;
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
     * Register the default namespaces for the current feed format
     *
     * @return void
     */
    protected function _registerNamespaces()
    {
        $this->_xpath->registerNamespace('dc10', 'http://purl.org/dc/elements/1.0/');
        $this->_xpath->registerNamespace('dc11', 'http://purl.org/dc/elements/1.1/');
    }
}
