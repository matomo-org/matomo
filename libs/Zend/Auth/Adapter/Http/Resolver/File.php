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
 * @subpackage Zend_Auth_Adapter_Http
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: File.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Auth_Adapter_Http_Resolver_Interface
 */
require_once 'Zend/Auth/Adapter/Http/Resolver/Interface.php';


/**
 * HTTP Authentication File Resolver
 *
 * @category   Zend
 * @package    Zend_Auth
 * @subpackage Zend_Auth_Adapter_Http
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Auth_Adapter_Http_Resolver_File implements Zend_Auth_Adapter_Http_Resolver_Interface
{
    /**
     * Path to credentials file
     *
     * @var string
     */
    protected $_file;

    /**
     * Constructor
     *
     * @param  string $path Complete filename where the credentials are stored
     * @return void
     */
    public function __construct($path = '')
    {
        if (!empty($path)) {
            $this->setFile($path);
        }
    }

    /**
     * Set the path to the credentials file
     *
     * @param  string $path
     * @throws Zend_Auth_Adapter_Http_Resolver_Exception
     * @return Zend_Auth_Adapter_Http_Resolver_File Provides a fluent interface
     */
    public function setFile($path)
    {
        if (empty($path) || !is_readable($path)) {
            /**
             * @see Zend_Auth_Adapter_Http_Resolver_Exception
             */
            require_once 'Zend/Auth/Adapter/Http/Resolver/Exception.php';
            throw new Zend_Auth_Adapter_Http_Resolver_Exception('Path not readable: ' . $path);
        }
        $this->_file = $path;

        return $this;
    }

    /**
     * Returns the path to the credentials file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * Resolve credentials
     *
     * Only the first matching username/realm combination in the file is
     * returned. If the file contains credentials for Digest authentication,
     * the returned string is the password hash, or h(a1) from RFC 2617. The
     * returned string is the plain-text password for Basic authentication.
     *
     * The expected format of the file is:
     *   username:realm:sharedSecret
     *
     * That is, each line consists of the user's username, the applicable
     * authentication realm, and the password or hash, each delimited by
     * colons.
     *
     * @param  string $username Username
     * @param  string $realm    Authentication Realm
     * @throws Zend_Auth_Adapter_Http_Resolver_Exception
     * @return string|false User's shared secret, if the user is found in the
     *         realm, false otherwise.
     */
    public function resolve($username, $realm)
    {
        if (empty($username)) {
            /**
             * @see Zend_Auth_Adapter_Http_Resolver_Exception
             */
            require_once 'Zend/Auth/Adapter/Http/Resolver/Exception.php';
            throw new Zend_Auth_Adapter_Http_Resolver_Exception('Username is required');
        } else if (!ctype_print($username) || strpos($username, ':') !== false) {
            /**
             * @see Zend_Auth_Adapter_Http_Resolver_Exception
             */
            require_once 'Zend/Auth/Adapter/Http/Resolver/Exception.php';
            throw new Zend_Auth_Adapter_Http_Resolver_Exception('Username must consist only of printable characters, '
                                                              . 'excluding the colon');
        }
        if (empty($realm)) {
            /**
             * @see Zend_Auth_Adapter_Http_Resolver_Exception
             */
            require_once 'Zend/Auth/Adapter/Http/Resolver/Exception.php';
            throw new Zend_Auth_Adapter_Http_Resolver_Exception('Realm is required');
        } else if (!ctype_print($realm) || strpos($realm, ':') !== false) {
            /**
             * @see Zend_Auth_Adapter_Http_Resolver_Exception
             */
            require_once 'Zend/Auth/Adapter/Http/Resolver/Exception.php';
            throw new Zend_Auth_Adapter_Http_Resolver_Exception('Realm must consist only of printable characters, '
                                                              . 'excluding the colon.');
        }

        // Open file, read through looking for matching credentials
        $fp = @fopen($this->_file, 'r');
        if (!$fp) {
            /**
             * @see Zend_Auth_Adapter_Http_Resolver_Exception
             */
            require_once 'Zend/Auth/Adapter/Http/Resolver/Exception.php';
            throw new Zend_Auth_Adapter_Http_Resolver_Exception('Unable to open password file: ' . $this->_file);
        }

        // No real validation is done on the contents of the password file. The
        // assumption is that we trust the administrators to keep it secure.
        while (($line = fgetcsv($fp, 512, ':')) !== false) {
            if ($line[0] == $username && $line[1] == $realm) {
                $password = $line[2];
                fclose($fp);
                return $password;
            }
        }

        fclose($fp);
        return false;
    }
}
