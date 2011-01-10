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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Session.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_OpenId_Provider_User
 */
// require_once "Zend/OpenId/Provider/User.php";

/**
 * @see Zend_Session_Namespace
 */
// require_once "Zend/Session/Namespace.php";

/**
 * Class to get/store information about logged in user in Web Browser using
 * PHP session
 *
 * @category   Zend
 * @package    Zend_OpenId
 * @subpackage Zend_OpenId_Provider
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_OpenId_Provider_User_Session extends Zend_OpenId_Provider_User
{
    /**
     * Reference to an implementation of Zend_Session_Namespace object
     *
     * @var Zend_Session_Namespace $_session
     */
    private $_session = null;

    /**
     * Creates Zend_OpenId_Provider_User_Session object with given session
     * namespace or creates new session namespace named "openid"
     *
     * @param Zend_Session_Namespace $session
     */
    public function __construct(Zend_Session_Namespace $session = null)
    {
        if ($session === null) {
            $this->_session = new Zend_Session_Namespace("openid");
        } else {
            $this->_session = $session;
        }
    }

    /**
     * Stores information about logged in user in session data
     *
     * @param string $id user identity URL
     * @return bool
     */
    public function setLoggedInUser($id)
    {
        $this->_session->logged_in = $id;
        return true;
    }

    /**
     * Returns identity URL of logged in user or false
     *
     * @return mixed
     */
    public function getLoggedInUser()
    {
        if (isset($this->_session->logged_in)) {
            return $this->_session->logged_in;
        }
        return false;
    }

    /**
     * Performs logout. Clears information about logged in user.
     *
     * @return bool
     */
    public function delLoggedInUser()
    {
        unset($this->_session->logged_in);
        return true;
    }

}
