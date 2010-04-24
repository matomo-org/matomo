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
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Feed.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

// require_once 'Zend/Feed/Writer/Feed/FeedAbstract.php';
 
 /**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Deleted
{

    /**
     * Internal array containing all data associated with this entry or item.
     *
     * @var array
     */
    protected $_data = array();
    
    /**
     * Holds the value "atom" or "rss" depending on the feed type set when
     * when last exported.
     *
     * @var string
     */
    protected $_type = null;
    
    /**
     * Set the feed character encoding
     *
     * @return string|null
     */
    public function setEncoding($encoding)
    {
        if (empty($encoding) || !is_string($encoding)) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['encoding'] = $encoding;
    }

    /**
     * Get the feed character encoding
     *
     * @return string|null
     */
    public function getEncoding()
    {
        if (!array_key_exists('encoding', $this->_data)) {
            return 'UTF-8';
        }
        return $this->_data['encoding'];
    }
    
    /**
     * Unset a specific data point
     *
     * @param string $name
     */
    public function remove($name)
    {
        if (isset($this->_data[$name])) {
            unset($this->_data[$name]);
        }
    }
    
    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }
    
    /**
     * Retrieve the current or last feed type exported.
     *
     * @return string Value will be "rss" or "atom"
     */
    public function getType()
    {
        return $this->_type;
    }
    
    public function setReference($reference)
    {
        if (empty($reference) || !is_string($reference)) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: reference must be a non-empty string');
        }
        $this->_data['reference'] = $reference;
    }
    
    public function getReference()
    {
        if (!array_key_exists('reference', $this->_data)) {
            return null;
        }
        return $this->_data['reference'];
    }
    
    public function setWhen($date = null)
    {
        $zdate = null;
        if (is_null($date)) {
            $zdate = new Zend_Date;
        } elseif (ctype_digit($date) && strlen($date) == 10) {
            $zdate = new Zend_Date($date, Zend_Date::TIMESTAMP);
        } elseif ($date instanceof Zend_Date) {
            $zdate = $date;
        } else {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid Zend_Date object or UNIX Timestamp passed as parameter');
        }
        $this->_data['when'] = $zdate;
    }
    
    public function getWhen()
    {
        if (!array_key_exists('when', $this->_data)) {
            return null;
        }
        return $this->_data['when'];
    }
    
    public function setBy(array $by)
    {
        $author = array();
        if (!array_key_exists('name', $by) 
            || empty($by['name']) 
            || !is_string($by['name'])
        ) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: author array must include a "name" key with a non-empty string value');
        }
        $author['name'] = $by['name'];
        if (isset($by['email'])) {
            if (empty($by['email']) || !is_string($by['email'])) {
                // require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Invalid parameter: "email" array value must be a non-empty string');
            }
            $author['email'] = $by['email'];
        }
        if (isset($by['uri'])) {
            if (empty($by['uri']) 
                || !is_string($by['uri']) 
                || !Zend_Uri::check($by['uri'])
            ) {
                // require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Invalid parameter: "uri" array value must be a non-empty string and valid URI/IRI');
            }
            $author['uri'] = $by['uri'];
        }
        $this->_data['by'] = $author;
    }
    
    public function getBy()
    {
        if (!array_key_exists('by', $this->_data)) {
            return null;
        }
        return $this->_data['by'];
    }
    
    public function setComment($comment)
    {
        $this->_data['comment'] = $comment;
    }
    
    public function getComment()
    {
        if (!array_key_exists('comment', $this->_data)) {
            return null;
        }
        return $this->_data['comment'];
    }

}
