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
 * @version    $Id: EntryInterface.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Feed_Reader_EntryInterface
{
    /**
     * Get the specified author
     *
     * @param  int $index
     * @return string|null
     */
    public function getAuthor($index = 0);

    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function getAuthors();

    /**
     * Get the entry content
     *
     * @return string
     */
    public function getContent();

    /**
     * Get the entry creation date
     *
     * @return string
     */
    public function getDateCreated();

    /**
     * Get the entry modification date
     *
     * @return string
     */
    public function getDateModified();

    /**
     * Get the entry description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get the entry enclosure
     *
     * @return stdClass
     */
    public function getEnclosure();

    /**
     * Get the entry ID
     *
     * @return string
     */
    public function getId();

    /**
     * Get a specific link
     *
     * @param  int $index
     * @return string
     */
    public function getLink($index = 0);

    /**
     * Get all links
     *
     * @return array
     */
    public function getLinks();

    /**
     * Get a permalink to the entry
     *
     * @return string
     */
    public function getPermalink();

    /**
     * Get the entry title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get the number of comments/replies for current entry
     *
     * @return integer
     */
    public function getCommentCount();

    /**
     * Returns a URI pointing to the HTML page where comments can be made on this entry
     *
     * @return string
     */
    public function getCommentLink();

    /**
     * Returns a URI pointing to a feed of all comments for this entry
     *
     * @return string
     */
    public function getCommentFeedLink();
    
    /**
     * Get all categories
     *
     * @return Zend_Feed_Reader_Collection_Category
     */
    public function getCategories();
}
