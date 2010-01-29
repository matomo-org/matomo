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
 * @see Zend_Feed_Reader_Extension_EntryAbstract
 */
require_once 'Zend/Feed/Reader/Extension/EntryAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader_Extension_Thread_Entry
    extends Zend_Feed_Reader_Extension_EntryAbstract
{
    /**
     * Get the "in-reply-to" value
     *
     * @return string
     */
    public function getInReplyTo()
    {
        // TODO: to be implemented
    }

    // TODO: Implement "replies" and "updated" constructs from standard

    /**
     * Get the total number of threaded responses (i.e comments)
     *
     * @return int|null
     */
    public function getCommentCount()
    {
        return $this->_getData('total');
    }

    /**
     * Get the entry data specified by name
     *
     * @param  string $name
     * @param  string $type
     * @return mixed|null
     */
    protected function _getData($name)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }

        $data = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/thread10:' . $name . ')');

        if (!$data) {
            $data = null;
        }

        $this->_data[$name] = $data;

        return $data;
    }

    /**
     * Register Atom Thread Extension 1.0 namespace
     *
     * @return void
     */
    protected function _registerNamespaces()
    {
        $this->_xpath->registerNamespace('thread10', 'http://purl.org/syndication/thread/1.0');
    }
}
