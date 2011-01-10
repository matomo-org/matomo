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
 * @version    $Id: Consumer.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_OpenId
 */
// require_once "Zend/OpenId.php";

/**
 * @see Zend_OpenId_Extension
 */
// require_once "Zend/OpenId/Extension.php";

/**
 * @see Zend_OpenId_Consumer_Storage
 */
// require_once "Zend/OpenId/Consumer/Storage.php";

/**
 * @see Zend_Http_Client
 */
// require_once 'Zend/Http/Client.php';

/**
 * OpenID consumer implementation
 *
 * @category   Zend
 * @package    Zend_OpenId
 * @subpackage Zend_OpenId_Consumer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_OpenId_Consumer
{

    /**
     * Reference to an implementation of storage object
     *
     * @var Zend_OpenId_Consumer_Storage $_storage
     */
    protected $_storage = null;

    /**
     * Enables or disables consumer to use association with server based on
     * Diffie-Hellman key agreement
     *
     * @var Zend_OpenId_Consumer_Storage $_dumbMode
     */
    protected $_dumbMode = false;

    /**
     * Internal cache to prevent unnecessary access to storage
     *
     * @var array $_cache
     */
    protected $_cache = array();

    /**
     * HTTP client to make HTTP requests
     *
     * @var Zend_Http_Client $_httpClient
     */
    private $_httpClient = null;

    /**
     * HTTP session to store climed_id between requests
     *
     * @var Zend_Session_Namespace $_session
     */
    private $_session = null;

    /**
     * Last error message for logi, check or verify failure
     *
     * @var string $_error
     */
    private $_error = '';

    /**
     * Constructs a Zend_OpenId_Consumer object with given $storage.
     * Enables or disables future association with server based on
     * Diffie-Hellman key agreement.
     *
     * @param Zend_OpenId_Consumer_Storage $storage implementation of custom
     *  storage object
     * @param bool $dumbMode Enables or disables consumer to use association
     *  with server based on Diffie-Hellman key agreement
     */
    public function __construct(Zend_OpenId_Consumer_Storage $storage = null,
                                $dumbMode = false)
    {
        if ($storage === null) {
            // require_once "Zend/OpenId/Consumer/Storage/File.php";
            $this->_storage = new Zend_OpenId_Consumer_Storage_File();
        } else {
            $this->_storage = $storage;
        }
        $this->_dumbMode = $dumbMode;
    }

    /**
     * Performs check (with possible user interaction) of OpenID identity.
     *
     * This is the first step of OpenID authentication process.
     * On success the function does not return (it does HTTP redirection to
     * server and exits). On failure it returns false.
     *
     * @param string $id OpenID identity
     * @param string $returnTo URL to redirect response from server to
     * @param string $root HTTP URL to identify consumer on server
     * @param mixed $extensions extension object or array of extensions objects
     * @param Zend_Controller_Response_Abstract $response an optional response
     *  object to perform HTTP or HTML form redirection
     * @return bool
     */
    public function login($id, $returnTo = null, $root = null, $extensions = null,
                          Zend_Controller_Response_Abstract $response = null)
    {
        return $this->_checkId(
            false,
            $id,
            $returnTo,
            $root,
            $extensions,
            $response);
    }

    /**
     * Performs immediate check (without user interaction) of OpenID identity.
     *
     * This is the first step of OpenID authentication process.
     * On success the function does not return (it does HTTP redirection to
     * server and exits). On failure it returns false.
     *
     * @param string $id OpenID identity
     * @param string $returnTo HTTP URL to redirect response from server to
     * @param string $root HTTP URL to identify consumer on server
     * @param mixed $extensions extension object or array of extensions objects
     * @param Zend_Controller_Response_Abstract $response an optional response
     *  object to perform HTTP or HTML form redirection
     * @return bool
     */
    public function check($id, $returnTo=null, $root=null, $extensions = null,
                          Zend_Controller_Response_Abstract $response = null)

    {
        return $this->_checkId(
            true,
            $id,
            $returnTo,
            $root,
            $extensions,
            $response);
    }

    /**
     * Verifies authentication response from OpenID server.
     *
     * This is the second step of OpenID authentication process.
     * The function returns true on successful authentication and false on
     * failure.
     *
     * @param array $params HTTP query data from OpenID server
     * @param string &$identity this argument is set to end-user's claimed
     *  identifier or OpenID provider local identifier.
     * @param mixed $extensions extension object or array of extensions objects
     * @return bool
     */
    public function verify($params, &$identity = "", $extensions = null)
    {
        $this->_setError('');

        $version = 1.1;
        if (isset($params['openid_ns']) &&
            $params['openid_ns'] == Zend_OpenId::NS_2_0) {
            $version = 2.0;
        }

        if (isset($params["openid_claimed_id"])) {
            $identity = $params["openid_claimed_id"];
        } else if (isset($params["openid_identity"])){
            $identity = $params["openid_identity"];
        } else {
            $identity = "";
        }

        if ($version < 2.0 && !isset($params["openid_claimed_id"])) {
            if ($this->_session !== null) {
                if ($this->_session->identity === $identity) {
                    $identity = $this->_session->claimed_id;
                }
            } else if (defined('SID')) {
                if (isset($_SESSION["zend_openid"]["identity"]) &&
                    isset($_SESSION["zend_openid"]["claimed_id"]) &&
                    $_SESSION["zend_openid"]["identity"] === $identity) {
                    $identity = $_SESSION["zend_openid"]["claimed_id"];
                }
            } else {
                // require_once "Zend/Session/Namespace.php";
                $this->_session = new Zend_Session_Namespace("zend_openid");
                if ($this->_session->identity === $identity) {
                    $identity = $this->_session->claimed_id;
                }
            }
        }

        if (empty($params['openid_mode'])) {
            $this->_setError("Missing openid.mode");
            return false;
        }
        if (empty($params['openid_return_to'])) {
            $this->_setError("Missing openid.return_to");
            return false;
        }
        if (empty($params['openid_signed'])) {
            $this->_setError("Missing openid.signed");
            return false;
        }
        if (empty($params['openid_sig'])) {
            $this->_setError("Missing openid.sig");
            return false;
        }
        if ($params['openid_mode'] != 'id_res') {
            $this->_setError("Wrong openid.mode '".$params['openid_mode']."' != 'id_res'");
            return false;
        }
        if (empty($params['openid_assoc_handle'])) {
            $this->_setError("Missing openid.assoc_handle");
            return false;
        }
        if ($params['openid_return_to'] != Zend_OpenId::selfUrl()) {
            /* Ignore query part in openid.return_to */
            $pos = strpos($params['openid_return_to'], '?');
            if ($pos === false ||
                SUBSTR($params['openid_return_to'], 0 , $pos) != Zend_OpenId::selfUrl()) {

                $this->_setError("Wrong openid.return_to '".
                    $params['openid_return_to']."' != '" . Zend_OpenId::selfUrl() ."'");
                return false;
            }
        }

        if ($version >= 2.0) {
            if (empty($params['openid_response_nonce'])) {
                $this->_setError("Missing openid.response_nonce");
                return false;
            }
            if (empty($params['openid_op_endpoint'])) {
                $this->_setError("Missing openid.op_endpoint");
                return false;
            /* OpenID 2.0 (11.3) Checking the Nonce */
            } else if (!$this->_storage->isUniqueNonce($params['openid_op_endpoint'], $params['openid_response_nonce'])) {
                $this->_setError("Duplicate openid.response_nonce");
                return false;
            }
        }


        if (!empty($params['openid_invalidate_handle'])) {
            if ($this->_storage->getAssociationByHandle(
                $params['openid_invalidate_handle'],
                $url,
                $macFunc,
                $secret,
                $expires)) {
                $this->_storage->delAssociation($url);
            }
        }

        if ($this->_storage->getAssociationByHandle(
                $params['openid_assoc_handle'],
                $url,
                $macFunc,
                $secret,
                $expires)) {
            $signed = explode(',', $params['openid_signed']);
            $data = '';
            foreach ($signed as $key) {
                $data .= $key . ':' . $params['openid_' . strtr($key,'.','_')] . "\n";
            }
            if (base64_decode($params['openid_sig']) ==
                Zend_OpenId::hashHmac($macFunc, $data, $secret)) {
                if (!Zend_OpenId_Extension::forAll($extensions, 'parseResponse', $params)) {
                    $this->_setError("Extension::parseResponse failure");
                    return false;
                }
                /* OpenID 2.0 (11.2) Verifying Discovered Information */
                if (isset($params['openid_claimed_id'])) {
                    $id = $params['openid_claimed_id'];
                    if (!Zend_OpenId::normalize($id)) {
                        $this->_setError("Normalization failed");
                        return false;
                    } else if (!$this->_discovery($id, $discovered_server, $discovered_version)) {
                        $this->_setError("Discovery failed: " . $this->getError());
                        return false;
                    } else if ((!empty($params['openid_identity']) &&
                                $params["openid_identity"] != $id) ||
                               (!empty($params['openid_op_endpoint']) &&
                                $params['openid_op_endpoint'] != $discovered_server) ||
                               $discovered_version != $version) {
                        $this->_setError("Discovery information verification failed");
                        return false;
                    }
                }
                return true;
            }
            $this->_storage->delAssociation($url);
            $this->_setError("Signature check failed");
            return false;
        }
        else
        {
            /* Use dumb mode */
            if (isset($params['openid_claimed_id'])) {
                $id = $params['openid_claimed_id'];
            } else if (isset($params['openid_identity'])) {
                $id = $params['openid_identity'];
            } else {
                $this->_setError("Missing openid.claimed_id and openid.identity");
                return false;
            }

            if (!Zend_OpenId::normalize($id)) {
                $this->_setError("Normalization failed");
                return false;
            } else if (!$this->_discovery($id, $server, $discovered_version)) {
                $this->_setError("Discovery failed: " . $this->getError());
                return false;
            }

            /* OpenID 2.0 (11.2) Verifying Discovered Information */
            if ((isset($params['openid_identity']) &&
                 $params["openid_identity"] != $id) ||
                (isset($params['openid_op_endpoint']) &&
                 $params['openid_op_endpoint'] != $server) ||
                $discovered_version != $version) {
                $this->_setError("Discovery information verification failed");
                return false;
            }

            $params2 = array();
            foreach ($params as $key => $val) {
                if (strpos($key, 'openid_ns_') === 0) {
                    $key = 'openid.ns.' . substr($key, strlen('openid_ns_'));
                } else if (strpos($key, 'openid_sreg_') === 0) {
                    $key = 'openid.sreg.' . substr($key, strlen('openid_sreg_'));
                } else if (strpos($key, 'openid_') === 0) {
                    $key = 'openid.' . substr($key, strlen('openid_'));
                }
                $params2[$key] = $val;
            }
            $params2['openid.mode'] = 'check_authentication';
            $ret = $this->_httpRequest($server, 'POST', $params2, $status);
            if ($status != 200) {
                $this->_setError("'Dumb' signature verification HTTP request failed");
                return false;
            }
            $r = array();
            if (is_string($ret)) {
                foreach(explode("\n", $ret) as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $x = explode(':', $line, 2);
                        if (is_array($x) && count($x) == 2) {
                            list($key, $value) = $x;
                            $r[trim($key)] = trim($value);
                        }
                    }
                }
            }
            $ret = $r;
            if (!empty($ret['invalidate_handle'])) {
                if ($this->_storage->getAssociationByHandle(
                    $ret['invalidate_handle'],
                    $url,
                    $macFunc,
                    $secret,
                    $expires)) {
                    $this->_storage->delAssociation($url);
                }
            }
            if (isset($ret['is_valid']) && $ret['is_valid'] == 'true') {
                if (!Zend_OpenId_Extension::forAll($extensions, 'parseResponse', $params)) {
                    $this->_setError("Extension::parseResponse failure");
                    return false;
                }
                return true;
            }
            $this->_setError("'Dumb' signature verification failed");
            return false;
        }
    }

    /**
     * Store assiciation in internal chace and external storage
     *
     * @param string $url OpenID server url
     * @param string $handle association handle
     * @param string $macFunc HMAC function (sha1 or sha256)
     * @param string $secret shared secret
     * @param integer $expires expiration UNIX time
     * @return void
     */
    protected function _addAssociation($url, $handle, $macFunc, $secret, $expires)
    {
        $this->_cache[$url] = array($handle, $macFunc, $secret, $expires);
        return $this->_storage->addAssociation(
            $url,
            $handle,
            $macFunc,
            $secret,
            $expires);
    }

    /**
     * Retrive assiciation information for given $url from internal cahce or
     * external storage
     *
     * @param string $url OpenID server url
     * @param string &$handle association handle
     * @param string &$macFunc HMAC function (sha1 or sha256)
     * @param string &$secret shared secret
     * @param integer &$expires expiration UNIX time
     * @return void
     */
    protected function _getAssociation($url, &$handle, &$macFunc, &$secret, &$expires)
    {
        if (isset($this->_cache[$url])) {
            $handle   = $this->_cache[$url][0];
            $macFunc = $this->_cache[$url][1];
            $secret   = $this->_cache[$url][2];
            $expires  = $this->_cache[$url][3];
            return true;
        }
        if ($this->_storage->getAssociation(
                $url,
                $handle,
                $macFunc,
                $secret,
                $expires)) {
            $this->_cache[$url] = array($handle, $macFunc, $secret, $expires);
            return true;
        }
        return false;
    }

    /**
     * Performs HTTP request to given $url using given HTTP $method.
     * Send additinal query specified by variable/value array,
     * On success returns HTTP response without headers, false on failure.
     *
     * @param string $url OpenID server url
     * @param string $method HTTP request method 'GET' or 'POST'
     * @param array $params additional qwery parameters to be passed with
     * @param int &$staus HTTP status code
     *  request
     * @return mixed
     */
    protected function _httpRequest($url, $method = 'GET', array $params = array(), &$status = null)
    {
        $client = $this->_httpClient;
        if ($client === null) {
            $client = new Zend_Http_Client(
                    $url,
                    array(
                        'maxredirects' => 4,
                        'timeout'      => 15,
                        'useragent'    => 'Zend_OpenId'
                    )
                );
        } else {
            $client->setUri($url);
        }

        $client->resetParameters();
        if ($method == 'POST') {
            $client->setMethod(Zend_Http_Client::POST);
            $client->setParameterPost($params);
        } else {
            $client->setMethod(Zend_Http_Client::GET);
            $client->setParameterGet($params);
        }

        try {
            $response = $client->request();
        } catch (Exception $e) {
            $this->_setError('HTTP Request failed: ' . $e->getMessage());
            return false;
        }
        $status = $response->getStatus();
        $body = $response->getBody();
        if ($status == 200 || ($status == 400 && !empty($body))) {
            return $body;
        }else{
            $this->_setError('Bad HTTP response');
            return false;
        }
    }

    /**
     * Create (or reuse existing) association between OpenID consumer and
     * OpenID server based on Diffie-Hellman key agreement. Returns true
     * on success and false on failure.
     *
     * @param string $url OpenID server url
     * @param float $version OpenID protocol version
     * @param string $priv_key for testing only
     * @return bool
     */
    protected function _associate($url, $version, $priv_key=null)
    {

        /* Check if we already have association in chace or storage */
        if ($this->_getAssociation(
                $url,
                $handle,
                $macFunc,
                $secret,
                $expires)) {
            return true;
        }

        if ($this->_dumbMode) {
            /* Use dumb mode */
            return true;
        }

        $params = array();

        if ($version >= 2.0) {
            $params = array(
                'openid.ns'           => Zend_OpenId::NS_2_0,
                'openid.mode'         => 'associate',
                'openid.assoc_type'   => 'HMAC-SHA256',
                'openid.session_type' => 'DH-SHA256',
            );
        } else {
            $params = array(
                'openid.mode'         => 'associate',
                'openid.assoc_type'   => 'HMAC-SHA1',
                'openid.session_type' => 'DH-SHA1',
            );
        }

        $dh = Zend_OpenId::createDhKey(pack('H*', Zend_OpenId::DH_P),
                                       pack('H*', Zend_OpenId::DH_G),
                                       $priv_key);
        $dh_details = Zend_OpenId::getDhKeyDetails($dh);

        $params['openid.dh_modulus']         = base64_encode(
            Zend_OpenId::btwoc($dh_details['p']));
        $params['openid.dh_gen']             = base64_encode(
            Zend_OpenId::btwoc($dh_details['g']));
        $params['openid.dh_consumer_public'] = base64_encode(
            Zend_OpenId::btwoc($dh_details['pub_key']));

        while(1) {
            $ret = $this->_httpRequest($url, 'POST', $params, $status);
            if ($ret === false) {
                $this->_setError("HTTP request failed");
                return false;
            }

            $r = array();
            $bad_response = false;
            foreach(explode("\n", $ret) as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $x = explode(':', $line, 2);
                    if (is_array($x) && count($x) == 2) {
                        list($key, $value) = $x;
                        $r[trim($key)] = trim($value);
                    } else {
                        $bad_response = true;
                    }
                }
            }
            if ($bad_response && strpos($ret, 'Unknown session type') !== false) {
                $r['error_code'] = 'unsupported-type';
            }
            $ret = $r;

            if (isset($ret['error_code']) &&
                $ret['error_code'] == 'unsupported-type') {
                if ($params['openid.session_type'] == 'DH-SHA256') {
                    $params['openid.session_type'] = 'DH-SHA1';
                    $params['openid.assoc_type'] = 'HMAC-SHA1';
                } else if ($params['openid.session_type'] == 'DH-SHA1') {
                    $params['openid.session_type'] = 'no-encryption';
                } else {
                    $this->_setError("The OpenID service responded with: " . $ret['error_code']);
                    return false;
                }
            } else {
                break;
            }
        }

        if ($status != 200) {
            $this->_setError("The server responded with status code: " . $status);
            return false;
        }

        if ($version >= 2.0 &&
            isset($ret['ns']) &&
            $ret['ns'] != Zend_OpenId::NS_2_0) {
            $this->_setError("Wrong namespace definition in the server response");
            return false;
        }

        if (!isset($ret['assoc_handle']) ||
            !isset($ret['expires_in']) ||
            !isset($ret['assoc_type']) ||
            $params['openid.assoc_type'] != $ret['assoc_type']) {
            if ($params['openid.assoc_type'] != $ret['assoc_type']) {
                $this->_setError("The returned assoc_type differed from the supplied openid.assoc_type");
            } else {
                $this->_setError("Missing required data from provider (assoc_handle, expires_in, assoc_type are required)");
            }
            return false;
        }

        $handle     = $ret['assoc_handle'];
        $expiresIn = $ret['expires_in'];

        if ($ret['assoc_type'] == 'HMAC-SHA1') {
            $macFunc = 'sha1';
        } else if ($ret['assoc_type'] == 'HMAC-SHA256' &&
            $version >= 2.0) {
            $macFunc = 'sha256';
        } else {
            $this->_setError("Unsupported assoc_type");
            return false;
        }

        if ((empty($ret['session_type']) ||
             ($version >= 2.0 && $ret['session_type'] == 'no-encryption')) &&
             isset($ret['mac_key'])) {
            $secret = base64_decode($ret['mac_key']);
        } else if (isset($ret['session_type']) &&
            $ret['session_type'] == 'DH-SHA1' &&
            !empty($ret['dh_server_public']) &&
            !empty($ret['enc_mac_key'])) {
            $dhFunc = 'sha1';
        } else if (isset($ret['session_type']) &&
            $ret['session_type'] == 'DH-SHA256' &&
            $version >= 2.0 &&
            !empty($ret['dh_server_public']) &&
            !empty($ret['enc_mac_key'])) {
            $dhFunc = 'sha256';
        } else {
            $this->_setError("Unsupported session_type");
            return false;
        }
        if (isset($dhFunc)) {
            $serverPub = base64_decode($ret['dh_server_public']);
            $dhSec = Zend_OpenId::computeDhSecret($serverPub, $dh);
            if ($dhSec === false) {
                $this->_setError("DH secret comutation failed");
                return false;
            }
            $sec = Zend_OpenId::digest($dhFunc, $dhSec);
            if ($sec === false) {
                $this->_setError("Could not create digest");
                return false;
            }
            $secret = $sec ^ base64_decode($ret['enc_mac_key']);
        }
        if ($macFunc == 'sha1') {
            if (Zend_OpenId::strlen($secret) != 20) {
                $this->_setError("The length of the sha1 secret must be 20");
                return false;
            }
        } else if ($macFunc == 'sha256') {
            if (Zend_OpenId::strlen($secret) != 32) {
                $this->_setError("The length of the sha256 secret must be 32");
                return false;
            }
        }
        $this->_addAssociation(
            $url,
            $handle,
            $macFunc,
            $secret,
            time() + $expiresIn);
        return true;
    }

    /**
     * Performs discovery of identity and finds OpenID URL, OpenID server URL
     * and OpenID protocol version. Returns true on succees and false on
     * failure.
     *
     * @param string &$id OpenID identity URL
     * @param string &$server OpenID server URL
     * @param float &$version OpenID protocol version
     * @return bool
     * @todo OpenID 2.0 (7.3) XRI and Yadis discovery
     */
    protected function _discovery(&$id, &$server, &$version)
    {
        $realId = $id;
        if ($this->_storage->getDiscoveryInfo(
                $id,
                $realId,
                $server,
                $version,
                $expire)) {
            $id = $realId;
            return true;
        }

        /* TODO: OpenID 2.0 (7.3) XRI and Yadis discovery */

        /* HTML-based discovery */
        $response = $this->_httpRequest($id, 'GET', array(), $status);
        if ($status != 200 || !is_string($response)) {
            return false;
        }
        if (preg_match(
                '/<link[^>]*rel=(["\'])[ \t]*(?:[^ \t"\']+[ \t]+)*?openid2.provider[ \t]*[^"\']*\\1[^>]*href=(["\'])([^"\']+)\\2[^>]*\/?>/i',
                $response,
                $r)) {
            $version = 2.0;
            $server = $r[3];
        } else if (preg_match(
                '/<link[^>]*href=(["\'])([^"\']+)\\1[^>]*rel=(["\'])[ \t]*(?:[^ \t"\']+[ \t]+)*?openid2.provider[ \t]*[^"\']*\\3[^>]*\/?>/i',
                $response,
                $r)) {
            $version = 2.0;
            $server = $r[2];
        } else if (preg_match(
                '/<link[^>]*rel=(["\'])[ \t]*(?:[^ \t"\']+[ \t]+)*?openid.server[ \t]*[^"\']*\\1[^>]*href=(["\'])([^"\']+)\\2[^>]*\/?>/i',
                $response,
                $r)) {
            $version = 1.1;
            $server = $r[3];
        } else if (preg_match(
                '/<link[^>]*href=(["\'])([^"\']+)\\1[^>]*rel=(["\'])[ \t]*(?:[^ \t"\']+[ \t]+)*?openid.server[ \t]*[^"\']*\\3[^>]*\/?>/i',
                $response,
                $r)) {
            $version = 1.1;
            $server = $r[2];
        } else {
            return false;
        }
        if ($version >= 2.0) {
            if (preg_match(
                    '/<link[^>]*rel=(["\'])[ \t]*(?:[^ \t"\']+[ \t]+)*?openid2.local_id[ \t]*[^"\']*\\1[^>]*href=(["\'])([^"\']+)\\2[^>]*\/?>/i',
                    $response,
                    $r)) {
                $realId = $r[3];
            } else if (preg_match(
                    '/<link[^>]*href=(["\'])([^"\']+)\\1[^>]*rel=(["\'])[ \t]*(?:[^ \t"\']+[ \t]+)*?openid2.local_id[ \t]*[^"\']*\\3[^>]*\/?>/i',
                    $response,
                    $r)) {
                $realId = $r[2];
            }
        } else {
            if (preg_match(
                    '/<link[^>]*rel=(["\'])[ \t]*(?:[^ \t"\']+[ \t]+)*?openid.delegate[ \t]*[^"\']*\\1[^>]*href=(["\'])([^"\']+)\\2[^>]*\/?>/i',
                    $response,
                    $r)) {
                $realId = $r[3];
            } else if (preg_match(
                    '/<link[^>]*href=(["\'])([^"\']+)\\1[^>]*rel=(["\'])[ \t]*(?:[^ \t"\']+[ \t]+)*?openid.delegate[ \t]*[^"\']*\\3[^>]*\/?>/i',
                    $response,
                    $r)) {
                $realId = $r[2];
            }
        }

        $expire = time() + 60 * 60;
        $this->_storage->addDiscoveryInfo($id, $realId, $server, $version, $expire);
        $id = $realId;
        return true;
    }

    /**
     * Performs check of OpenID identity.
     *
     * This is the first step of OpenID authentication process.
     * On success the function does not return (it does HTTP redirection to
     * server and exits). On failure it returns false.
     *
     * @param bool $immediate enables or disables interaction with user
     * @param string $id OpenID identity
     * @param string $returnTo HTTP URL to redirect response from server to
     * @param string $root HTTP URL to identify consumer on server
     * @param mixed $extensions extension object or array of extensions objects
     * @param Zend_Controller_Response_Abstract $response an optional response
     *  object to perform HTTP or HTML form redirection
     * @return bool
     */
    protected function _checkId($immediate, $id, $returnTo=null, $root=null,
        $extensions=null, Zend_Controller_Response_Abstract $response = null)
    {
        $this->_setError('');

        if (!Zend_OpenId::normalize($id)) {
            $this->_setError("Normalisation failed");
            return false;
        }
        $claimedId = $id;

        if (!$this->_discovery($id, $server, $version)) {
            $this->_setError("Discovery failed: " . $this->getError());
            return false;
        }
        if (!$this->_associate($server, $version)) {
            $this->_setError("Association failed: " . $this->getError());
            return false;
        }
        if (!$this->_getAssociation(
                $server,
                $handle,
                $macFunc,
                $secret,
                $expires)) {
            /* Use dumb mode */
            unset($handle);
            unset($macFunc);
            unset($secret);
            unset($expires);
        }

        $params = array();
        if ($version >= 2.0) {
            $params['openid.ns'] = Zend_OpenId::NS_2_0;
        }

        $params['openid.mode'] = $immediate ?
            'checkid_immediate' : 'checkid_setup';

        $params['openid.identity'] = $id;

        $params['openid.claimed_id'] = $claimedId;

        if ($version <= 2.0) {
            if ($this->_session !== null) {
                $this->_session->identity = $id;
                $this->_session->claimed_id = $claimedId;
            } else if (defined('SID')) {
                $_SESSION["zend_openid"] = array(
                    "identity" => $id,
                    "claimed_id" => $claimedId);
            } else {
                // require_once "Zend/Session/Namespace.php";
                $this->_session = new Zend_Session_Namespace("zend_openid");
                $this->_session->identity = $id;
                $this->_session->claimed_id = $claimedId;
            }
        }

        if (isset($handle)) {
            $params['openid.assoc_handle'] = $handle;
        }

        $params['openid.return_to'] = Zend_OpenId::absoluteUrl($returnTo);

        if (empty($root)) {
            $root = Zend_OpenId::selfUrl();
            if ($root[strlen($root)-1] != '/') {
                $root = dirname($root);
            }
        }
        if ($version >= 2.0) {
            $params['openid.realm'] = $root;
        } else {
            $params['openid.trust_root'] = $root;
        }

        if (!Zend_OpenId_Extension::forAll($extensions, 'prepareRequest', $params)) {
            $this->_setError("Extension::prepareRequest failure");
            return false;
        }

        Zend_OpenId::redirect($server, $params, $response);
        return true;
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
     * Returns HTTP client object that will be used to make HTTP requests
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient() {
        return $this->_httpClient;
    }

    /**
     * Sets session object to store climed_id
     *
     * @param Zend_Session_Namespace $session HTTP client object to be used
     */
    public function setSession(Zend_Session_Namespace $session) {
        $this->_session = $session;
    }

    /**
     * Returns session object that is used to store climed_id
     *
     * @return Zend_Session_Namespace
     */
    public function getSession() {
        return $this->_session;
    }

    /**
     * Saves error message
     *
     * @param string $message error message
     */
    protected function _setError($message)
    {
        $this->_error = $message;
    }

    /**
     * Returns error message that explains failure of login, check or verify
     *
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

}
