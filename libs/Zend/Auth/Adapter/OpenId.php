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
 * @package    Zend_Auth
 * @subpackage Zend_Auth_Adapter
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: OpenId.php 16200 2009-06-21 18:50:06Z thomas $
 */


/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';


/**
 * @see Zend_OpenId_Consumer
 */
require_once 'Zend/OpenId/Consumer.php';


/**
 * A Zend_Auth Authentication Adapter allowing the use of OpenID protocol as an
 * authentication mechanism
 *
 * @category   Zend
 * @package    Zend_Auth
 * @subpackage Zend_Auth_Adapter
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Auth_Adapter_OpenId implements Zend_Auth_Adapter_Interface
{
    /**
     * The identity value being authenticated
     *
     * @var string
     */
    private $_id = null;

    /**
     * Reference to an implementation of a storage object
     *
     * @var Zend_OpenId_Consumer_Storage
     */
    private $_storage = null;

    /**
     * The URL to redirect response from server to
     *
     * @var string
     */
    private $_returnTo = null;

    /**
     * The HTTP URL to identify consumer on server
     *
     * @var string
     */
    private $_root = null;

    /**
     * Extension object or array of extensions objects
     *
     * @var string
     */
    private $_extensions = null;

    /**
     * The response object to perform HTTP or HTML form redirection
     *
     * @var Zend_Controller_Response_Abstract
     */
    private $_response = null;

    /**
     * Enables or disables interaction with user during authentication on
     * OpenID provider.
     *
     * @var bool
     */
    private $_check_immediate = false;

    /**
     * HTTP client to make HTTP requests
     *
     * @var Zend_Http_Client $_httpClient
     */
    private $_httpClient = null;

    /**
     * Constructor
     *
     * @param string $id the identity value
     * @param Zend_OpenId_Consumer_Storage $storage an optional implementation
     *        of a storage object
     * @param string $returnTo HTTP URL to redirect response from server to
     * @param string $root HTTP URL to identify consumer on server
     * @param mixed $extensions extension object or array of extensions objects
     * @param Zend_Controller_Response_Abstract $response an optional response
     *        object to perform HTTP or HTML form redirection
     * @return void
     */
    public function __construct($id = null,
                                Zend_OpenId_Consumer_Storage $storage = null,
                                $returnTo = null,
                                $root = null,
                                $extensions = null,
                                Zend_Controller_Response_Abstract $response = null) {
        $this->_id         = $id;
        $this->_storage    = $storage;
        $this->_returnTo   = $returnTo;
        $this->_root       = $root;
        $this->_extensions = $extensions;
        $this->_response   = $response;
    }

    /**
     * Sets the value to be used as the identity
     *
     * @param  string $id the identity value
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setIdentity($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Sets the storage implementation which will be use by OpenId
     *
     * @param  Zend_OpenId_Consumer_Storage $storage
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setStorage(Zend_OpenId_Consumer_Storage $storage)
    {
        $this->_storage = $storage;
        return $this;
    }

    /**
     * Sets the HTTP URL to redirect response from server to
     *
     * @param  string $returnTo
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setReturnTo($returnTo)
    {
        $this->_returnTo = $returnTo;
        return $this;
    }

    /**
     * Sets HTTP URL to identify consumer on server
     *
     * @param  string $root
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setRoot($root)
    {
        $this->_root = $root;
        return $this;
    }

    /**
     * Sets OpenID extension(s)
     *
     * @param  mixed $extensions
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setExtensions($extensions)
    {
        $this->_extensions = $extensions;
        return $this;
    }

    /**
     * Sets an optional response object to perform HTTP or HTML form redirection
     *
     * @param  string $root
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setResponse($response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Enables or disables interaction with user during authentication on
     * OpenID provider.
     *
     * @param  bool $check_immediate
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setCheckImmediate($check_immediate)
    {
        $this->_check_immediate = $check_immediate;
        return $this;
    }

    /**
     * Sets HTTP client object to make HTTP requests
     *
     * @param Zend_Http_Client $client HTTP client object to be used
     */
    public function setHttpClient($client) {
        $this->_httpClient = $client;
    }

    /**
     * Authenticates the given OpenId identity.
     * Defined by Zend_Auth_Adapter_Interface.
     *
     * @throws Zend_Auth_Adapter_Exception If answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate() {
        $id = $this->_id;
        if (!empty($id)) {
            $consumer = new Zend_OpenId_Consumer($this->_storage);
            $consumer->setHttpClient($this->_httpClient);
            /* login() is never returns on success */
            if (!$this->_check_immediate) {
                if (!$consumer->login($id,
                        $this->_returnTo,
                        $this->_root,
                        $this->_extensions,
                        $this->_response)) {
                    return new Zend_Auth_Result(
                        Zend_Auth_Result::FAILURE,
                        $id,
                        array("Authentication failed", $consumer->getError()));
                }
            } else {
                if (!$consumer->check($id,
                        $this->_returnTo,
                        $this->_root,
                        $this->_extensions,
                        $this->_response)) {
                    return new Zend_Auth_Result(
                        Zend_Auth_Result::FAILURE,
                        $id,
                        array("Authentication failed", $consumer->getError()));
                }
            }
        } else {
            $params = (isset($_SERVER['REQUEST_METHOD']) &&
                       $_SERVER['REQUEST_METHOD']=='POST') ? $_POST: $_GET;
            $consumer = new Zend_OpenId_Consumer($this->_storage);
            $consumer->setHttpClient($this->_httpClient);
            if ($consumer->verify(
                    $params,
                    $id,
                    $this->_extensions)) {
                return new Zend_Auth_Result(
                    Zend_Auth_Result::SUCCESS,
                    $id,
                    array("Authentication successful"));
            } else {
                return new Zend_Auth_Result(
                    Zend_Auth_Result::FAILURE,
                    $id,
                    array("Authentication failed", $consumer->getError()));
            }
        }
    }

}
