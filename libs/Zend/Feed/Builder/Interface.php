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
 * @version    $Id: Interface.php 3941 2007-03-14 21:36:13Z darby $
 */


/**
 * Input feed data interface
 *
 * Classes implementing this interface can be passe to Zend_Feed::importBuilder
 * as an input data source for the Zend_Feed construction
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Feed_Builder_Interface
{
    /**
     * Returns an instance of Zend_Feed_Builder_Header
     * describing the header of the feed
     *
     * @return Zend_Feed_Builder_Header
     */
    public function getHeader();

    /**
     * Returns an array of Zend_Feed_Builder_Entry instances
     * describing the entries of the feed
     *
     * @return array of Zend_Feed_Builder_Entry
     */
    public function getEntries();
}
