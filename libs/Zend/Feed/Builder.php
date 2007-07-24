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
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Builder.php 3941 2007-03-14 21:36:13Z darby $
 */


/**
 * @see Zend_Feed_Builder_Interface
 */
require_once 'Zend/Feed/Builder/Interface.php';


/**
 * @see Zend_Feed_Builder_Header
 */
require_once 'Zend/Feed/Builder/Header.php';


/**
 * @see Zend_Feed_Builder_Entry
 */
require_once 'Zend/Feed/Builder/Entry.php';


/**
 * @see Zend_Feed_Exception
 */
require_once 'Zend/Feed/Exception.php';


/**
 * A simple implementation of Zend_Feed_Builder_Interface.
 *
 * Users are encouraged to make their own classes to implement Zend_Feed_Builder_Interface
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Builder implements Zend_Feed_Builder_Interface
{
    /**
     * The data of the feed
     *
     * @var $_data array
     */
    private $_data;

    /**
     * Header of the feed
     *
     * @var $_header Zend_Feed_Builder_Header
     */
    private $_header;

    /**
     * List of the entries of the feed
     *
     * @var $_entries array
     */
    private $_entries = array();

    /**
     * Constructor. The $data array must conform to the following format:
     * <code>
     *  array(
     *  'title'       => 'title of the feed', //required
     *  'link'        => 'canonical url to the feed', //required
     *  'lastUpdate'  => 'timestamp of the update date', // optional
     *  'published'   => 'timestamp of the publication date', //optional
     *  'charset'     => 'charset', // required
     *  'description' => 'short description of the feed', //optional
     *  'author'      => 'author/publisher of the feed', //optional
     *  'email'       => 'email of the author', //optional
     *  'webmaster'   => 'email address for person responsible for technical issues' // optional, ignored if atom is used
     *  'copyright'   => 'copyright notice', //optional
     *  'image'       => 'url to image', //optional
     *  'generator'   => 'generator', // optional
     *  'language'    => 'language the feed is written in', // optional
     *  'ttl'         => 'how long in minutes a feed can be cached before refreshing', // optional, ignored if atom is used
     *  'rating'      => 'The PICS rating for the channel.', // optional, ignored if atom is used
     *  'cloud'       => array(
     *                    'domain'            => 'domain of the cloud, e.g. rpc.sys.com' // required
     *                    'port'              => 'port to connect to' // optional, default to 80
     *                    'path'              => 'path of the cloud, e.g. /RPC2 //required
     *                    'registerProcedure' => 'procedure to call, e.g. myCloud.rssPleaseNotify' // required
     *                    'protocol'          => 'protocol to use, e.g. soap or xml-rpc' // required
     *                    ), a cloud to be notified of updates // optional, ignored if atom is used
     *  'textInput'   => array(
     *                    'title'       => 'the label of the Submit button in the text input area' // required,
     *                    'description' => 'explains the text input area' // required
     *                    'name'        => 'the name of the text object in the text input area' // required
     *                    'link'        => 'the URL of the CGI script that processes text input requests' // required
     *                    ) // a text input box that can be displayed with the feed // optional, ignored if atom is used
     *  'skipHours'   => array(
     *                    'hour in 24 format', // e.g 13 (1pm)
     *                    // up to 24 rows whose value is a number between 0 and 23
     *                    ) // Hint telling aggregators which hours they can skip // optional, ignored if atom is used
     *  'skipDays '   => array(
     *                    'a day to skip', // e.g Monday
     *                    // up to 7 rows whose value is a Monday, Tuesday, Wednesday, Thursday, Friday, Saturday or Sunday
     *                    ) // Hint telling aggregators which days they can skip // optional, ignored if atom is used
     *  'itunes'      => array(
     *                    'author'       => 'Artist column' // optional, default to the main author value
     *                    'owner'        => array(
     *                                        'name' => 'name of the owner' // optional, default to main author value
     *                                        'email' => 'email of the owner' // optional, default to main email value
     *                                        ) // Owner of the podcast // optional
     *                    'image'        => 'album/podcast art' // optional, default to the main image value
     *                    'subtitle'     => 'short description' // optional, default to the main description value
     *                    'summary'      => 'longer description' // optional, default to the main description value
     *                    'block'        => 'Prevent an episode from appearing (yes|no)' // optional
     *                    'category'     => array(
     *                                      array('main' => 'main category', // required
     *                                            'sub'  => 'sub category' // optional
     *                                        ),
     *                                        // up to 3 rows
     *                                        ) // 'Category column and in iTunes Music Store Browse' // required
     *                    'explicit'     => 'parental advisory graphic (yes|no|clean)' // optional
     *                    'keywords'     => 'a comma separated list of 12 keywords maximum' // optional
     *                    'new-feed-url' => 'used to inform iTunes of new feed URL location' // optional
     *                    ) // Itunes extension data // optional, ignored if atom is used
     *  'entries'     => array(
     *                   array(
     *                    'title'        => 'title of the feed entry', //required
     *                    'link'         => 'url to a feed entry', //required
     *                    'description'  => 'short version of a feed entry', // only text, no html, required
     *                    'guid'         => 'id of the article, if not given link value will used', //optional
     *                    'content'      => 'long version', // can contain html, optional
     *                    'lastUpdate'   => 'timestamp of the publication date', // optional
     *                    'comments'     => 'comments page of the feed entry', // optional
     *                    'commentRss'   => 'the feed url of the associated comments', // optional
     *                    'source'       => array(
     *                                        'title' => 'title of the original source' // required,
     *                                        'url' => 'url of the original source' // required
     *                                           ) // original source of the feed entry // optional
     *                    'category'     => array(
     *                                      array(
     *                                        'term' => 'first category label' // required,
     *                                        'scheme' => 'url that identifies a categorization scheme' // optional
     *                                            ),
     *                                      array(
     *                                         //data for the second category and so on
     *                                           )
     *                                        ) // list of the attached categories // optional
     *                    'enclosure'    => array(
     *                                      array(
     *                                        'url' => 'url of the linked enclosure' // required
     *                                        'type' => 'mime type of the enclosure' // optional
     *                                        'length' => 'length of the linked content in octets' // optional
     *                                           ),
     *                                      array(
     *                                         //data for the second enclosure and so on
     *                                           )
     *                                        ) // list of the enclosures of the feed entry // optional
     *                   ),
     *                   array(
     *                   //data for the second entry and so on
     *                   )
     *                 )
     * );
     * </code>
     *
     * @param  array $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->_data = $data;
        $this->_createHeader($data);
        if (isset($data['entries'])) {
            $this->_createEntries($data['entries']);
        }
    }

    /**
     * Returns an instance of Zend_Feed_Builder_Header
     * describing the header of the feed
     *
     * @return Zend_Feed_Builder_Header
     */
    public function getHeader()
    {
        return $this->_header;
    }

    /**
     * Returns an array of Zend_Feed_Builder_Entry instances
     * describing the entries of the feed
     *
     * @return array of Zend_Feed_Builder_Entry
     */
    public function getEntries()
    {
        return $this->_entries;
    }

    /**
     * Create the Zend_Feed_Builder_Header instance
     *
     * @param  array $data
     * @throws Zend_Feed_Builder_Exception
     * @return void
     */
    private function _createHeader(array $data)
    {
        $mandatories = array('title', 'link', 'charset');
        foreach ($mandatories as $mandatory) {
            if (!isset($data[$mandatory])) {
                throw new Zend_Feed_Builder_Exception("$mandatory key is missing");
            }
        }
        $this->_header = new Zend_Feed_Builder_Header($data['title'], $data['link'], $data['charset']);
        if (isset($data['lastUpdate'])) {
            $this->_header->setLastUpdate($data['lastUpdate']);
        }
        if (isset($data['published'])) {
            $this->_header->setPublishedDate($data['published']);
        }
        if (isset($data['description'])) {
            $this->_header->setDescription($data['description']);
        }
        if (isset($data['author'])) {
            $this->_header->setAuthor($data['author']);
        }
        if (isset($data['email'])) {
            $this->_header->setEmail($data['email']);
        }
        if (isset($data['webmaster'])) {
            $this->_header->setWebmaster($data['webmaster']);
        }
        if (isset($data['copyright'])) {
            $this->_header->setCopyright($data['copyright']);
        }
        if (isset($data['image'])) {
            $this->_header->setImage($data['image']);
        }
        if (isset($data['generator'])) {
            $this->_header->setGenerator($data['generator']);
        }
        if (isset($data['language'])) {
            $this->_header->setLanguage($data['language']);
        }
        if (isset($data['ttl'])) {
            $this->_header->setTtl($data['ttl']);
        }
        if (isset($data['rating'])) {
            $this->_header->setRating($data['rating']);
        }
        if (isset($data['cloud'])) {
            $mandatories = array('domain', 'path', 'registerProcedure', 'protocol');
            foreach ($mandatories as $mandatory) {
                if (!isset($data['cloud'][$mandatory])) {
                    throw new Zend_Feed_Builder_Exception("you have to define $mandatory property of your cloud");
                }
            }
            $uri_str = 'http://' . $data['cloud']['domain'] . $data['cloud']['path'];
            $this->_header->setCloud($uri_str, $data['cloud']['registerProcedure'], $data['cloud']['protocol']);
        }
        if (isset($data['textInput'])) {
            $mandatories = array('title', 'description', 'name', 'link');
            foreach ($mandatories as $mandatory) {
                if (!isset($data['textInput'][$mandatory])) {
                    throw new Zend_Feed_Builder_Exception("you have to define $mandatory property of your textInput");
                }
            }
            $this->_header->setTextInput($data['textInput']['title'],
                                         $data['textInput']['description'],
                                         $data['textInput']['name'],
                                         $data['textInput']['link']);
        }
        if (isset($data['skipHours'])) {
            $this->_header->setSkipHours($data['skipHours']);
        }
        if (isset($data['skipDays'])) {
            $this->_header->setSkipDays($data['skipDays']);
        }
        if (isset($data['itunes'])) {
            $itunes = new Zend_Feed_Builder_Header_Itunes($data['itunes']['category']);
            if (isset($data['itunes']['author'])) {
                $itunes->setAuthor($data['itunes']['author']);
            }
            if (isset($data['itunes']['owner'])) {
                $name = isset($data['itunes']['owner']['name']) ? $data['itunes']['owner']['name'] : '';
                $email = isset($data['itunes']['owner']['email']) ? $data['itunes']['owner']['email'] : '';
                $itunes->setOwner($name, $email);
            }
            if (isset($data['itunes']['image'])) {
                $itunes->setImage($data['itunes']['image']);
            }
            if (isset($data['itunes']['subtitle'])) {
                $itunes->setSubtitle($data['itunes']['subtitle']);
            }
            if (isset($data['itunes']['summary'])) {
                $itunes->setSummary($data['itunes']['summary']);
            }
            if (isset($data['itunes']['block'])) {
                $itunes->setBlock($data['itunes']['block']);
            }
            if (isset($data['itunes']['explicit'])) {
                $itunes->setExplicit($data['itunes']['explicit']);
            }
            if (isset($data['itunes']['keywords'])) {
                $itunes->setKeywords($data['itunes']['keywords']);
            }
            if (isset($data['itunes']['new-feed-url'])) {
                $itunes->setNewFeedUrl($data['itunes']['new-feed-url']);
            }

            $this->_header->setITunes($itunes);
        }
    }

    /**
     * Create the array of article entries
     *
     * @param  array $data
     * @throws Zend_Feed_Builder_Exception
     * @return void
     */
    private function _createEntries(array $data)
    {
        foreach ($data as $row) {
            $mandatories = array('title', 'link', 'description');
            foreach ($mandatories as $mandatory) {
                if (!isset($row[$mandatory])) {
                    throw new Zend_Feed_Builder_Exception("$mandatory key is missing");
                }
            }
            $entry = new Zend_Feed_Builder_Entry($row['title'], $row['link'], $row['description']);
            if (isset($row['guid'])) {
                $entry->setId($row['guid']);
            }
            if (isset($row['content'])) {
                $entry->setContent($row['content']);
            }
            if (isset($row['lastUpdate'])) {
                $entry->setLastUpdate($row['lastUpdate']);
            }
            if (isset($row['comments'])) {
                $entry->setCommentsUrl($row['comments']);
            }
            if (isset($row['commentRss'])) {
                $entry->setCommentsRssUrl($row['commentRss']);
            }
            if (isset($row['source'])) {
                $mandatories = array('title', 'url');
                foreach ($mandatories as $mandatory) {
                    if (!isset($row['source'][$mandatory])) {
                        throw new Zend_Feed_Builder_Exception("$mandatory key of source property is missing");
                    }
                }
                $entry->setSource($row['source']['title'], $row['source']['url']);
            }
            if (isset($row['category'])) {
                $entry->setCategories($row['category']);
            }
            if (isset($row['enclosure'])) {
                $entry->setEnclosures($row['enclosure']);
            }

            $this->_entries[] = $entry;
        }
    }
}