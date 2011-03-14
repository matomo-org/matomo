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
 * @package    Zend_OpenId
 * @subpackage Zend_OpenId_Provider
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: User.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * Abstract class to get/store information about logged in user in Web Browser
 *
 * @category   Zend
 * @package    Zend_OpenId
 * @subpackage Zend_OpenId_Provider
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_OpenId_Provider_User
{

    /**
     * Stores information about logged in user
     *
     * @param string $id user identity URL
     * @return bool
     */
    abstract public function setLoggedInUser($id);

    /**
     * Returns identity URL of logged in user or false
     *
     * @return mixed
     */
    abstract public function getLoggedInUser();

    /**
     * Performs logout. Clears information about logged in user.
     *
     * @return bool
     */
    abstract public function delLoggedInUser();
}
