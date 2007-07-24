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
 * @version    $Id: Header.php 3941 2007-03-14 21:36:13Z darby $
 */


/**
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';


/**
 * @see Zend_Feed_Builder_Exception
 */
require_once 'Zend/Feed/Builder/Exception.php';


/**
 * @see Zend_Feed_Builder_Header_Itunes
 */
require_once 'Zend/Feed/Builder/Header/Itunes.php';


/**
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';


/**
 * Header of a custom build feed
 *
 * Classes implementing the Zend_Feed_Builder_Interface interface
 * uses this class to describe the header of a feed
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Builder_Header extends ArrayObject
{
    /**
     * Constructor
     *
     * @param  string $title title of the feed
     * @param  string $link canonical url of the feed
     * @param  string $charset charset of the textual data
     * @return void
     */
    public function __construct($title, $link, $charset = 'utf-8')
    {
        $this->offsetSet('title', $title);
        $this->offsetSet('link', $link);
        $this->offsetSet('charset', $charset);
        $this->setLastUpdate(time())
             ->setGenerator('Zend_Feed');
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
     * @param string $name  name of the property to set
     * @param mixed  $value value to set
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
     * Timestamp of the update date
     *
     * @param  int $lastUpdate
     * @return Zend_Feed_Builder_Header
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->offsetSet('lastUpdate', $lastUpdate);
        return $this;
    }

    /**
     * Timestamp of the publication date
     *
     * @param  int $published
     * @return Zend_Feed_Builder_Header
     */
    public function setPublishedDate($published)
    {
        $this->offsetSet('published', $published);
        return $this;
    }

    /**
     * Short description of the feed
     *
     * @param  string $description
     * @return Zend_Feed_Builder_Header
     */
    public function setDescription($description)
    {
        $this->offsetSet('description', $description);
        return $this;
    }

    /**
     * Sets the author of the feed
     *
     * @param  string $author
     * @return Zend_Feed_Builder_Header
     */
    public function setAuthor($author)
    {
        $this->offsetSet('author', $author);
        return $this;
    }

    /**
     * Sets the author's email
     *
     * @param  string $email
     * @return Zend_Feed_Builder_Header
     * @throws Zend_Feed_Builder_Exception
     */
    public function setEmail($email)
    {
        Zend_Loader::loadClass('Zend_Validate_EmailAddress');
        $validate = new Zend_Validate_EmailAddress();
        if (!$validate->isValid($email)) {
            throw new Zend_Feed_Builder_Exception("you have to set a valid email address into the email property");
        }
        $this->offsetSet('email', $email);
        return $this;
    }

    /**
     * Sets the copyright notice
     *
     * @param  string $copyright
     * @return Zend_Feed_Builder_Header
     */
    public function setCopyright($copyright)
    {
        $this->offsetSet('copyright', $copyright);
        return $this;
    }

    /**
     * Sets the image of the feed
     *
     * @param  string $image
     * @return Zend_Feed_Builder_Header
     */
    public function setImage($image)
    {
        $this->offsetSet('image', $image);
        return $this;
    }

    /**
     * Sets the generator of the feed
     *
     * @param  string $generator
     * @return Zend_Feed_Builder_Header
     */
    public function setGenerator($generator)
    {
        $this->offsetSet('generator', $generator);
        return $this;
    }

    /**
     * Sets the language of the feed
     *
     * @param  string $language
     * @return Zend_Feed_Builder_Header
     */
    public function setLanguage($language)
    {
        $this->offsetSet('language', $language);
        return $this;
    }

    /**
     * Email address for person responsible for technical issues
     * Ignored if atom is used
     *
     * @param  string $webmaster
     * @return Zend_Feed_Builder_Header
     * @throws Zend_Feed_Builder_Exception
     */
    public function setWebmaster($webmaster)
    {
        Zend_Loader::loadClass('Zend_Validate_EmailAddress');
        $validate = new Zend_Validate_EmailAddress();
        if (!$validate->isValid($webmaster)) {
            throw new Zend_Feed_Builder_Exception("you have to set a valid email address into the webmaster property");
        }
        $this->offsetSet('webmaster', $webmaster);
        return $this;
    }

    /**
     * How long in minutes a feed can be cached before refreshing
     * Ignored if atom is used
     *
     * @param  int $ttl
     * @return Zend_Feed_Builder_Header
     * @throws Zend_Feed_Builder_Exception
     */
    public function setTtl($ttl)
    {
        Zend_Loader::loadClass('Zend_Validate_Int');
        $validate = new Zend_Validate_Int();
        if (!$validate->isValid($ttl)) {
            throw new Zend_Feed_Builder_Exception("you have to set an integer value to the ttl property");
        }
        $this->offsetSet('ttl', $ttl);
        return $this;
    }

    /**
     * PICS rating for the feed
     * Ignored if atom is used
     *
     * @param  string $rating
     * @return Zend_Feed_Builder_Header
     */
    public function setRating($rating)
    {
        $this->offsetSet('rating', $rating);
        return $this;
    }

    /**
     * Cloud to be notified of updates of the feed
     * Ignored if atom is used
     *
     * @param  string|Zend_Uri_Http $uri
     * @param  string               $procedure procedure to call, e.g. myCloud.rssPleaseNotify
     * @param  string               $protocol  protocol to use, e.g. soap or xml-rpc
     * @return Zend_Feed_Builder_Header
     * @throws Zend_Feed_Builder_Exception
     */
    public function setCloud($uri, $procedure, $protocol)
    {
        if (is_string($uri) && Zend_Uri_Http::check($uri)) {
            $uri = Zend_Uri::factory($uri);
        }
        if (!$uri instanceof Zend_Uri_Http) {
            throw new Zend_Feed_Builder_Exception('Passed parameter is not a valid HTTP URI');
        }
        if (!$uri->getPort()) {
            $uri->setPort(80);
        }
        $this->offsetSet('cloud', array('uri' => $uri,
                                        'procedure' => $procedure,
                                        'protocol' => $protocol));
        return $this;
    }

    /**
     * A text input box that can be displayed with the feed
     * Ignored if atom is used
     *
     * @param  string $title       the label of the Submit button in the text input area
     * @param  string $description explains the text input area
     * @param  string $name        the name of the text object in the text input area
     * @param  string $link        the URL of the CGI script that processes text input requests
     * @return Zend_Feed_Builder_Header
     */
    public function setTextInput($title, $description, $name, $link)
    {
        $this->offsetSet('textInput', array('title' => $title,
                                            'description' => $description,
                                            'name' => $name,
                                            'link' => $link));
        return $this;
    }

    /**
     * Hint telling aggregators which hours they can skip
     * Ignored if atom is used
     *
     * @param  array $hours list of hours in 24 format
     * @return Zend_Feed_Builder_Header
     * @throws Zend_Feed_Builder_Exception
     */
    public function setSkipHours(array $hours)
    {
        if (count($hours) > 24) {
            throw new Zend_Feed_Builder_Exception("you can not have more than 24 rows in the skipHours property");
        }
        foreach ($hours as $hour) {
            if ($hour < 0 || $hour > 23) {
                throw new Zend_Feed_Builder_Exception("$hour has te be between 0 and 23");
            }
        }
        $this->offsetSet('skipHours', $hours);
        return $this;
    }

    /**
     * Hint telling aggregators which days they can skip
     * Ignored if atom is used
     *
     * @param  array $days list of days to skip, e.g. Monday
     * @return Zend_Feed_Builder_Header
     * @throws Zend_Feed_Builder_Exception
     */
    public function setSkipDays(array $days)
    {
        if (count($days) > 7) {
            throw new Zend_Feed_Builder_Exception("you can not have more than 7 days in the skipDays property");
        }
        $valid = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        foreach ($days as $day) {
            if (!in_array(strtolower($day), $valid)) {
                throw new Zend_Feed_Builder_Exception("$day is not a valid day");
            }
        }
        $this->offsetSet('skipDays', $days);
        return $this;
    }

    /**
     * Sets the iTunes rss extension
     *
     * @param  Zend_Feed_Builder_Header_Itunes $itunes
     * @return Zend_Feed_Builder_Header
     */
    public function setITunes(Zend_Feed_Builder_Header_Itunes $itunes)
    {
        $this->offsetSet('itunes', $itunes);
        return $this;
    }
}
