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
 * @version    $Id: Feed.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Feed_Reader_Extension_FeedAbstract
 */
require_once 'Zend/Feed/Reader/Extension/FeedAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_Extension_Podcast_Feed extends Zend_Feed_Reader_Extension_FeedAbstract
{
    /**
     * Get the entry author
     *
     * @return string
     */
    public function getCastAuthor()
    {
        if (isset($this->_data['author'])) {
            return $this->_data['author'];
        }

        $author = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:author)');

        if (!$author) {
            $author = null;
        }

        $this->_data['author'] = $author;

        return $this->_data['author'];
    }

    /**
     * Get the entry block
     *
     * @return string
     */
    public function getBlock()
    {
        if (isset($this->_data['block'])) {
            return $this->_data['block'];
        }

        $block = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:block)');

        if (!$block) {
            $block = null;
        }

        $this->_data['block'] = $block;

        return $this->_data['block'];
    }

    /**
     * Get the entry category
     *
     * @return string
     */
    public function getCategories()
    {
        if (isset($this->_data['categories'])) {
            return $this->_data['categories'];
        }

        $categoryList = $this->_xpath->query($this->getXpathPrefix() . '/itunes:category');

        $categories = array();

        if ($categoryList->length > 0) {
            foreach ($categoryList as $node) {
                $children = null;

                if ($node->childNodes->length > 0) {
                    $children = array();

                    foreach ($node->childNodes as $childNode) {
                        if (!($childNode instanceof DOMText)) {
                            $children[$childNode->getAttribute('text')] = null;
                        }
                    }
                }

                $categories[$node->getAttribute('text')] = $children;
            }
        }


        if (!$categories) {
            $categories = null;
        }

        $this->_data['categories'] = $categories;

        return $this->_data['categories'];
    }

    /**
     * Get the entry explicit
     *
     * @return string
     */
    public function getExplicit()
    {
        if (isset($this->_data['explicit'])) {
            return $this->_data['explicit'];
        }

        $explicit = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:explicit)');

        if (!$explicit) {
            $explicit = null;
        }

        $this->_data['explicit'] = $explicit;

        return $this->_data['explicit'];
    }

    /**
     * Get the entry image
     *
     * @return string
     */
    public function getImage()
    {
        if (isset($this->_data['image'])) {
            return $this->_data['image'];
        }

        $image = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:image/@href)');

        if (!$image) {
            $image = null;
        }

        $this->_data['image'] = $image;

        return $this->_data['image'];
    }

    /**
     * Get the entry keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        if (isset($this->_data['keywords'])) {
            return $this->_data['keywords'];
        }

        $keywords = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:keywords)');

        if (!$keywords) {
            $keywords = null;
        }

        $this->_data['keywords'] = $keywords;

        return $this->_data['keywords'];
    }

    /**
     * Get the entry's new feed url
     *
     * @return string
     */
    public function getNewFeedUrl()
    {
        if (isset($this->_data['new-feed-url'])) {
            return $this->_data['new-feed-url'];
        }

        $newFeedUrl = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:new-feed-url)');

        if (!$newFeedUrl) {
            $newFeedUrl = null;
        }

        $this->_data['new-feed-url'] = $newFeedUrl;

        return $this->_data['new-feed-url'];
    }

    /**
     * Get the entry owner
     *
     * @return string
     */
    public function getOwner()
    {
        if (isset($this->_data['owner'])) {
            return $this->_data['owner'];
        }

        $owner = null;

        $email = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:owner/itunes:email)');
        $name  = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:owner/itunes:name)');

        if (!empty($email)) {
            $owner = $email . (empty($name) ? '' : ' (' . $name . ')');
        } else if (!empty($name)) {
            $owner = $name;
        }

        if (!$owner) {
            $owner = null;
        }

        $this->_data['owner'] = $owner;

        return $this->_data['owner'];
    }

    /**
     * Get the entry subtitle
     *
     * @return string
     */
    public function getSubtitle()
    {
        if (isset($this->_data['subtitle'])) {
            return $this->_data['subtitle'];
        }

        $subtitle = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:subtitle)');

        if (!$subtitle) {
            $subtitle = null;
        }

        $this->_data['subtitle'] = $subtitle;

        return $this->_data['subtitle'];
    }

    /**
     * Get the entry summary
     *
     * @return string
     */
    public function getSummary()
    {
        if (isset($this->_data['summary'])) {
            return $this->_data['summary'];
        }

        $summary = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/itunes:summary)');

        if (!$summary) {
            $summary = null;
        }

        $this->_data['summary'] = $summary;

        return $this->_data['summary'];
    }

    /**
     * Register iTunes namespace
     *
     */
    protected function _registerNamespaces()
    {
        $this->_xpath->registerNamespace('itunes', 'http://www.itunes.com/dtds/podcast-1.0.dtd');
    }
}