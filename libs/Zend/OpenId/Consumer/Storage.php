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
 * @subpackage Zend_OpenId_Consumer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Storage.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Abstract class to implement external storage for OpenID consumer
 *
 * @category   Zend
 * @package    Zend_OpenId
 * @subpackage Zend_OpenId_Consumer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_OpenId_Consumer_Storage
{

    /**
     * Stores information about association identified by $url/$handle
     *
     * @param string $url OpenID server URL
     * @param string $handle assiciation handle
     * @param string $macFunc HMAC function (sha1 or sha256)
     * @param string $secret shared secret
     * @param long $expires expiration UNIX time
     * @return void
     */
    abstract public function addAssociation($url, $handle, $macFunc, $secret, $expires);

    /**
     * Gets information about association identified by $url
     * Returns true if given association found and not expired and false
     * otherwise
     *
     * @param string $url OpenID server URL
     * @param string &$handle assiciation handle
     * @param string &$macFunc HMAC function (sha1 or sha256)
     * @param string &$secret shared secret
     * @param long &$expires expiration UNIX time
     * @return bool
     */
    abstract public function getAssociation($url, &$handle, &$macFunc, &$secret, &$expires);

    /**
     * Gets information about association identified by $handle
     * Returns true if given association found and not expired and false
     * othverwise
     *
     * @param string $handle assiciation handle
     * @param string &$url OpenID server URL
     * @param string &$macFunc HMAC function (sha1 or sha256)
     * @param string &$secret shared secret
     * @param long &$expires expiration UNIX time
     * @return bool
     */
    abstract public function getAssociationByHandle($handle, &$url, &$macFunc, &$secret, &$expires);

    /**
     * Deletes association identified by $url
     *
     * @param string $url OpenID server URL
     * @return void
     */
    abstract public function delAssociation($url);

    /**
     * Stores information discovered from identity $id
     *
     * @param string $id identity
     * @param string $realId discovered real identity URL
     * @param string $server discovered OpenID server URL
     * @param float $version discovered OpenID protocol version
     * @param long $expires expiration UNIX time
     * @return void
     */
    abstract public function addDiscoveryInfo($id, $realId, $server, $version, $expires);

    /**
     * Gets information discovered from identity $id
     * Returns true if such information exists and false otherwise
     *
     * @param string $id identity
     * @param string &$realId discovered real identity URL
     * @param string &$server discovered OpenID server URL
     * @param float &$version discovered OpenID protocol version
     * @param long &$expires expiration UNIX time
     * @return bool
     */
    abstract public function getDiscoveryInfo($id, &$realId, &$server, &$version, &$expires);

    /**
     * Removes cached information discovered from identity $id
     *
     * @param string $id identity
     * @return bool
     */
    abstract public function delDiscoveryInfo($id);

    /**
     * The function checks the uniqueness of openid.response_nonce
     *
     * @param string $provider openid.openid_op_endpoint field from authentication response
     * @param string $nonce openid.response_nonce field from authentication response
     * @return bool
     */
    abstract public function isUniqueNonce($provider, $nonce);

    /**
     * Removes data from the uniqueness database that is older then given date
     *
     * @param string $date Date of expired data
     */
    abstract public function purgeNonces($date=null);
}
