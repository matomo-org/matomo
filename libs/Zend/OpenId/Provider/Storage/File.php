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
 * @version    $Id: File.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_OpenId_Provider_Storage
 */
// require_once "Zend/OpenId/Provider/Storage.php";

/**
 * External storage implemmentation using serialized files
 *
 * @category   Zend
 * @package    Zend_OpenId
 * @subpackage Zend_OpenId_Provider
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_OpenId_Provider_Storage_File extends Zend_OpenId_Provider_Storage
{

    /**
     * Directory name to store data files in
     *
     * @var string $_dir
     */
    private $_dir;

    /**
     * Constructs storage object and creates storage directory
     *
     * @param string $dir directory name to store data files in
     * @throws Zend_OpenId_Exception
     */
    public function __construct($dir = null)
    {
        if ($dir === null) {
            $tmp = getenv('TMP');
            if (empty($tmp)) {
                $tmp = getenv('TEMP');
                if (empty($tmp)) {
                    $tmp = "/tmp";
                }
            }
            $user = get_current_user();
            if (is_string($user) && !empty($user)) {
                $tmp .= '/' . $user;
            }
            $dir = $tmp . '/openid/provider';
        }
        $this->_dir = $dir;
        if (!is_dir($this->_dir)) {
            if (!@mkdir($this->_dir, 0700, 1)) {
                throw new Zend_OpenId_Exception(
                    "Cannot access storage directory $dir",
                    Zend_OpenId_Exception::ERROR_STORAGE);
            }
        }
        if (($f = fopen($this->_dir.'/assoc.lock', 'w+')) === null) {
            throw new Zend_OpenId_Exception(
                'Cannot create a lock file in the directory ' . $dir,
                Zend_OpenId_Exception::ERROR_STORAGE);
        }
        fclose($f);
        if (($f = fopen($this->_dir.'/user.lock', 'w+')) === null) {
            throw new Zend_OpenId_Exception(
                'Cannot create a lock file in the directory ' . $dir,
                Zend_OpenId_Exception::ERROR_STORAGE);
        }
        fclose($f);
    }

    /**
     * Stores information about session identified by $handle
     *
     * @param string $handle assiciation handle
     * @param string $macFunc HMAC function (sha1 or sha256)
     * @param string $secret shared secret
     * @param string $expires expiration UNIX time
     * @return bool
     */
    public function addAssociation($handle, $macFunc, $secret, $expires)
    {
        $name = $this->_dir . '/assoc_' . md5($handle);
        $lock = @fopen($this->_dir . '/assoc.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'w+');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $data = serialize(array($handle, $macFunc, $secret, $expires));
            fwrite($f, $data);
            fclose($f);
            fclose($lock);
            return true;
        } catch (Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Gets information about association identified by $handle
     * Returns true if given association found and not expired and false
     * otherwise
     *
     * @param string $handle assiciation handle
     * @param string &$macFunc HMAC function (sha1 or sha256)
     * @param string &$secret shared secret
     * @param string &$expires expiration UNIX time
     * @return bool
     */
    public function getAssociation($handle, &$macFunc, &$secret, &$expires)
    {
        $name = $this->_dir . '/assoc_' . md5($handle);
        $lock = @fopen($this->_dir . '/assoc.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'r');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $ret = false;
            $data = stream_get_contents($f);
            if (!empty($data)) {
                list($storedHandle, $macFunc, $secret, $expires) = unserialize($data);
                if ($handle === $storedHandle && $expires > time()) {
                    $ret = true;
                } else {
                    fclose($f);
                    @unlink($name);
                    fclose($lock);
                    return false;
                }
            }
            fclose($f);
            fclose($lock);
            return $ret;
        } catch (Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Removes information about association identified by $handle
     *
     * @param string $handle assiciation handle
     * @return bool
     */
    public function delAssociation($handle)
    {
        $name = $this->_dir . '/assoc_' . md5($handle);
        $lock = @fopen($this->_dir . '/assoc.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            @unlink($name);
            fclose($lock);
            return true;
        } catch (Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Register new user with given $id and $password
     * Returns true in case of success and false if user with given $id already
     * exists
     *
     * @param string $id user identity URL
     * @param string $password encoded user password
     * @return bool
     */
    public function addUser($id, $password)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'x');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $data = serialize(array($id, $password, array()));
            fwrite($f, $data);
            fclose($f);
            fclose($lock);
            return true;
        } catch (Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Returns true if user with given $id exists and false otherwise
     *
     * @param string $id user identity URL
     * @return bool
     */
    public function hasUser($id)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_SH)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'r');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $ret = false;
            $data = stream_get_contents($f);
            if (!empty($data)) {
                list($storedId, $storedPassword, $trusted) = unserialize($data);
                if ($id === $storedId) {
                    $ret = true;
                }
            }
            fclose($f);
            fclose($lock);
            return $ret;
        } catch (Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Verify if user with given $id exists and has specified $password
     *
     * @param string $id user identity URL
     * @param string $password user password
     * @return bool
     */
    public function checkUser($id, $password)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_SH)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'r');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $ret = false;
            $data = stream_get_contents($f);
            if (!empty($data)) {
                list($storedId, $storedPassword, $trusted) = unserialize($data);
                if ($id === $storedId && $password === $storedPassword) {
                    $ret = true;
                }
            }
            fclose($f);
            fclose($lock);
            return $ret;
        } catch (Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Removes information abou specified user
     *
     * @param string $id user identity URL
     * @return bool
     */
    public function delUser($id)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            @unlink($name);
            fclose($lock);
            return true;
        } catch (Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Returns array of all trusted/untrusted sites for given user identified
     * by $id
     *
     * @param string $id user identity URL
     * @return array
     */
    public function getTrustedSites($id)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_SH)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'r');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $ret = false;
            $data = stream_get_contents($f);
            if (!empty($data)) {
                list($storedId, $storedPassword, $trusted) = unserialize($data);
                if ($id === $storedId) {
                    $ret = $trusted;
                }
            }
            fclose($f);
            fclose($lock);
            return $ret;
        } catch (Exception $e) {
            fclose($lock);
            throw $e;
        }
    }

    /**
     * Stores information about trusted/untrusted site for given user
     *
     * @param string $id user identity URL
     * @param string $site site URL
     * @param mixed $trusted trust data from extension or just a boolean value
     * @return bool
     */
    public function addSite($id, $site, $trusted)
    {
        $name = $this->_dir . '/user_' . md5($id);
        $lock = @fopen($this->_dir . '/user.lock', 'w+');
        if ($lock === false) {
            return false;
        }
        if (!flock($lock, LOCK_EX)) {
            fclose($lock);
            return false;
        }
        try {
            $f = @fopen($name, 'r+');
            if ($f === false) {
                fclose($lock);
                return false;
            }
            $ret = false;
            $data = stream_get_contents($f);
            if (!empty($data)) {
                list($storedId, $storedPassword, $sites) = unserialize($data);
                if ($id === $storedId) {
                    if ($trusted === null) {
                        unset($sites[$site]);
                    } else {
                        $sites[$site] = $trusted;
                    }
                    rewind($f);
                    ftruncate($f, 0);
                    $data = serialize(array($id, $storedPassword, $sites));
                    fwrite($f, $data);
                    $ret = true;
                }
            }
            fclose($f);
            fclose($lock);
            return $ret;
        } catch (Exception $e) {
            fclose($lock);
            throw $e;
        }
    }
}
