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
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FeedInterface.php 23953 2011-05-03 05:47:39Z ralph $
 */

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Feed_Reader_FeedInterface extends Iterator, Countable
{
    /**
     * Get a single author
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
     * Get the copyright entry
     *
     * @return string|null
     */
    public function getCopyright();

    /**
     * Get the feed creation date
     *
     * @return string|null
     */
    public function getDateCreated();

    /**
     * Get the feed modification date
     *
     * @return string|null
     */
    public function getDateModified();

    /**
     * Get the feed description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Get the feed generator entry
     *
     * @return string|null
     */
    public function getGenerator();

    /**
     * Get the feed ID
     *
     * @return string|null
     */
    public function getId();

    /**
     * Get the feed language
     *
     * @return string|null
     */
    public function getLanguage();

    /**
     * Get a link to the HTML source
     *
     * @return string|null
     */
    public function getLink();

    /**
     * Get a link to the XML feed
     *
     * @return string|null
     */
    public function getFeedLink();

    /**
     * Get the feed title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get all categories
     *
     * @return Zend_Feed_Reader_Collection_Category
     */
    public function getCategories();

}
