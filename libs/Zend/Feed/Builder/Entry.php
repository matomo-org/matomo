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
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Entry.php 8064 2008-02-16 10:58:39Z thomas $
 */


/**
 * An entry of a custom build feed
 *
 * Classes implementing the Zend_Feed_Builder_Interface interface
 * uses this class to describe an entry of a feed
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Builder_Entry extends ArrayObject
{
    /**
     * Create a new builder entry
     *
     * @param  string $title
     * @param  string $link
     * @param  string $description short version of the entry, no html
     * @return void
     */
    public function __construct($title, $link, $description)
    {
        $this->offsetSet('title', $title);
        $this->offsetSet('link', $link);
        $this->offsetSet('description', $description);
        $this->setLastUpdate(time());
    }

    /**
     * Read only properties accessor
     *
     * @param  string $name property to read
     * @return mixed
     */
    public function __get($name)
    {
        if (!$this->offsetExists($name)) {
            return NULL;
        }

        return $this->offsetGet($name);
    }

    /**
     * Write properties accessor
     *
     * @param  string $name name of the property to set
     * @param  mixed $value value to set
     * @return void
     */
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * Isset accessor
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset accessor
     *
     * @param  string $key
     * @return void
     */
    public function __unset($key)
    {
        if ($this->offsetExists($key)) {
            $this->offsetUnset($key);
        }
    }

    /**
     * Sets the id/guid of the entry
     *
     * @param  string $id
     * @return Zend_Feed_Builder_Entry
     */
    public function setId($id)
    {
        $this->offsetSet('guid', $id);
        return $this;
    }

    /**
     * Sets the full html content of the entry
     *
     * @param  string $content
     * @return Zend_Feed_Builder_Entry
     */
    public function setContent($content)
    {
        $this->offsetSet('content', $content);
        return $this;
    }

    /**
     * Timestamp of the update date
     *
     * @param  int $lastUpdate
     * @return Zend_Feed_Builder_Entry
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->offsetSet('lastUpdate', $lastUpdate);
        return $this;
    }

    /**
     * Sets the url of the commented page associated to the entry
     *
     * @param  string $comments
     * @return Zend_Feed_Builder_Entry
     */
    public function setCommentsUrl($comments)
    {
        $this->offsetSet('comments', $comments);
        return $this;
    }

    /**
     * Sets the url of the comments feed link
     *
     * @param  string $commentRss
     * @return Zend_Feed_Builder_Entry
     */
    public function setCommentsRssUrl($commentRss)
    {
        $this->offsetSet('commentRss', $commentRss);
        return $this;
    }

    /**
     * Defines a reference to the original source
     *
     * @param  string $title
     * @param  string $url
     * @return Zend_Feed_Builder_Entry
     */
    public function setSource($title, $url)
    {
        $this->offsetSet('source', array('title' => $title,
                                         'url' => $url));
        return $this;
    }

    /**
     * Sets the categories of the entry
     * Format of the array:
     * <code>
     * array(
     *   array(
     *         'term' => 'first category label',
     *         'scheme' => 'url that identifies a categorization scheme' // optional
     *        ),
     *   // second category and so one
     * )
     * </code>
     *
     * @param  array $categories
     * @return Zend_Feed_Builder_Entry
     */
    public function setCategories(array $categories)
    {
        foreach ($categories as $category) {
            $this->addCategory($category);
        }
        return $this;
    }

    /**
     * Add a category to the entry
     *
     * @param  array $category see Zend_Feed_Builder_Entry::setCategories() for format
     * @return Zend_Feed_Builder_Entry
     * @throws Zend_Feed_Builder_Exception
     */
    public function addCategory(array $category)
    {
        if (empty($category['term'])) {
            /**
             * @see Zend_Feed_Builder_Exception
             */
            require_once 'Zend/Feed/Builder/Exception.php';
            throw new Zend_Feed_Builder_Exception("you have to define the name of the category");
        }

        if (!$this->offsetExists('category')) {
            $categories = array($category);
        } else {
            $categories = $this->offsetGet('category');
            $categories[] = $category;
        }
        $this->offsetSet('category', $categories);
        return $this;
    }

    /**
     * Sets the enclosures of the entry
     * Format of the array:
     * <code>
     * array(
     *   array(
     *         'url' => 'url of the linked enclosure',
     *         'type' => 'mime type of the enclosure' // optional
     *         'length' => 'length of the linked content in octets' // optional
     *        ),
     *   // second enclosure and so one
     * )
     * </code>
     *
     * @param  array $enclosures
     * @return Zend_Feed_Builder_Entry
     * @throws Zend_Feed_Builder_Exception
     */
    public function setEnclosures(array $enclosures)
    {
        foreach ($enclosures as $enclosure) {
            if (empty($enclosure['url'])) {
                /**
                 * @see Zend_Feed_Builder_Exception
                 */
                require_once 'Zend/Feed/Builder/Exception.php';
                throw new Zend_Feed_Builder_Exception("you have to supply an url for your enclosure");
            }
            $type = isset($enclosure['type']) ? $enclosure['type'] : '';
            $length = isset($enclosure['length']) ? $enclosure['length'] : '';
            $this->addEnclosure($enclosure['url'], $type, $length);
        }
        return $this;
    }

    /**
     * Add an enclosure to the entry
     *
     * @param  string $url
     * @param  string $type
     * @param  string $length
     * @return Zend_Feed_Builder_Entry
     */
    public function addEnclosure($url, $type = '', $length = '')
    {
        if (!$this->offsetExists('enclosure')) {
            $enclosure = array();
        } else {
            $enclosure = $this->offsetGet('enclosure');
        }
        $enclosure[] = array('url' => $url,
                             'type' => $type,
                             'length' => $length);
        $this->offsetSet('enclosure', $enclosure);
        return $this;
    }
}
