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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Rss.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Feed_Entry_Abstract
 */
// require_once 'Zend/Feed/Entry/Abstract.php';


/**
 * Concrete class for working with RSS items.
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Entry_Rss extends Zend_Feed_Entry_Abstract
{
    /**
     * Root XML element for RSS items.
     *
     * @var string
     */
    protected $_rootElement = 'item';

    /**
     * Overwrites parent::_get method to enable read access
     * to content:encoded element.
     *
     * @param  string $var The property to access.
     * @return mixed
     */
    public function __get($var)
    {
        switch ($var) {
            case 'content':
                $prefix = $this->_element->lookupPrefix('http://purl.org/rss/1.0/modules/content/');
                return parent::__get("$prefix:encoded");
            default:
                return parent::__get($var);
        }
    }

    /**
     * Overwrites parent::_set method to enable write access
     * to content:encoded element.
     *
     * @param  string $var The property to change.
     * @param  string $val The property's new value.
     * @return void
     */
    public function __set($var, $value)
    {
        switch ($var) {
            case 'content':
                parent::__set('content:encoded', $value);
                break;
            default:
                parent::__set($var, $value);
        }
    }

    /**
     * Overwrites parent::_isset method to enable access
     * to content:encoded element.
     *
     * @param  string $var
     * @return boolean
     */
    public function __isset($var)
    {
        switch ($var) {
            case 'content':
                // don't use other callback to prevent invalid returned value
                return $this->content() !== null;
            default:
                return parent::__isset($var);
        }
    }

    /**
     * Overwrites parent::_call method to enable read access
     * to content:encoded element.
     * Please note that method-style write access is not currently supported
     * by parent method, consequently this method doesn't as well.
     *
     * @param  string $var    The element to get the string value of.
     * @param  mixed  $unused This parameter is not used.
     * @return mixed The node's value, null, or an array of nodes.
     */
    public function __call($var, $unused)
    {
        switch ($var) {
            case 'content':
                $prefix = $this->_element->lookupPrefix('http://purl.org/rss/1.0/modules/content/');
                return parent::__call("$prefix:encoded", $unused);
            default:
                return parent::__call($var, $unused);
        }
    }
}
