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
 * @version    $Id: Provider.php 23775 2011-03-01 17:25:24Z ralph $
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
 * OpenID provider (server) implementation
 *
 * @category   Zend
 * @package    Zend_OpenId
 * @subpackage Zend_OpenId_Provider
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_OpenId_Provider
{

    /**
     * Reference to an implementation of storage object
     *
     * @var Zend_OpenId_Provider_Storage $_storage
     */
    private $_storage;

    /**
     * Reference to an implementation of user object
     *
     * @var Zend_OpenId_Provider_User $_user
     */
    private $_user;

    /**
     * Time to live of association session in secconds
     *
     * @var integer $_sessionTtl
     */
    private $_sessionTtl;

    /**
     * URL to peform interactive user login
     *
     * @var string $_loginUrl
     */
    private $_loginUrl;

    /**
     * URL to peform interactive validation of consumer by user
     *
     * @var string $_trustUrl
     */
    private $_trustUrl;

    /**
     * The OP Endpoint URL
     *
     * @var string $_opEndpoint
     */
    private $_opEndpoint;

    /**
     * Constructs a Zend_OpenId_Provider object with given parameters.
     *
     * @param string $loginUrl is an URL that provides login screen for
     *  end-user (by default it is the same URL with additional GET variable
     *  openid.action=login)
     * @param string $trustUrl is an URL that shows a question if end-user
     *  trust to given consumer (by default it is the same URL with additional
     *  GET variable openid.action=trust)
     * @param Zend_OpenId_Provider_User $user is an object for communication
     *  with User-Agent and store information about logged-in user (it is a
     *  Zend_OpenId_Provider_User_Session object by default)
     * @param Zend_OpenId_Provider_Storage $storage is an object for keeping
     *  persistent database (it is a Zend_OpenId_Provider_Storage_File object
     *  by default)
     * @param integer $sessionTtl is a default time to live for association
     *   session in seconds (1 hour by default). Consumer must reestablish
     *   association after that time.
     */
    public function __construct($loginUrl = null,
                                $trustUrl = null,
                                Zend_OpenId_Provider_User $user = null,
                                Zend_OpenId_Provider_Storage $storage = null,
                                $sessionTtl = 3600)
    {
        if ($loginUrl === null) {
            $loginUrl = Zend_OpenId::selfUrl() . '?openid.action=login';
        } else {
            $loginUrl = Zend_OpenId::absoluteUrl($loginUrl);
        }
        $this->_loginUrl = $loginUrl;
        if ($trustUrl === null) {
            $trustUrl = Zend_OpenId::selfUrl() . '?openid.action=trust';
        } else {
            $trustUrl = Zend_OpenId::absoluteUrl($trustUrl);
        }
        $this->_trustUrl = $trustUrl;
        if ($user === null) {
            // require_once "Zend/OpenId/Provider/User/Session.php";
            $this->_user = new Zend_OpenId_Provider_User_Session();
        } else {
            $this->_user = $user;
        }
        if ($storage === null) {
            // require_once "Zend/OpenId/Provider/Storage/File.php";
            $this->_storage = new Zend_OpenId_Provider_Storage_File();
        } else {
            $this->_storage = $storage;
        }
        $this->_sessionTtl = $sessionTtl;
    }

    /**
     * Sets the OP Endpoint URL
     *
     * @param string $url the OP Endpoint URL
     * @return null
     */
    public function setOpEndpoint($url)
    {
        $this->_opEndpoint = $url;
    }

    /**
     * Registers a new user with given $id and $password
     * Returns true in case of success and false if user with given $id already
     * exists
     *
     * @param string $id user identity URL
     * @param string $password encoded user password
     * @return bool
     */
    public function register($id, $password)
    {
        if (!Zend_OpenId::normalize($id) || empty($id)) {
            return false;
        }
        return $this->_storage->addUser($id, md5($id.$password));
    }

    /**
     * Returns true if user with given $id exists and false otherwise
     *
     * @param string $id user identity URL
     * @return bool
     */
    public function hasUser($id) {
        if (!Zend_OpenId::normalize($id)) {
            return false;
        }
        return $this->_storage->hasUser($id);
    }

    /**
     * Performs login of user with given $id and $password
     * Returns true in case of success and false otherwise
     *
     * @param string $id user identity URL
     * @param string $password user password
     * @return bool
     */
    public function login($id, $password)
    {
        if (!Zend_OpenId::normalize($id)) {
            return false;
        }
        if (!$this->_storage->checkUser($id, md5($id.$password))) {
            return false;
        }
        $this->_user->setLoggedInUser($id);
        return true;
    }

    /**
     * Performs logout. Clears information about logged in user.
     *
     * @return void
     */
    public function logout()
    {
        $this->_user->delLoggedInUser();
        return true;
    }

    /**
     * Returns identity URL of current logged in user or false
     *
     * @return mixed
     */
    public function getLoggedInUser() {
        return $this->_user->getLoggedInUser();
    }

    /**
     * Retrieve consumer's root URL from request query.
     * Returns URL or false in case of failure
     *
     * @param array $params query arguments
     * @return mixed
     */
    public function getSiteRoot($params)
    {
        $version = 1.1;
        if (isset($params['openid_ns']) &&
            $params['openid_ns'] == Zend_OpenId::NS_2_0) {
            $version = 2.0;
        }
        if ($version >= 2.0 && isset($params['openid_realm'])) {
            $root = $params['openid_realm'];
        } else if ($version < 2.0 && isset($params['openid_trust_root'])) {
            $root = $params['openid_trust_root'];
        } else if (isset($params['openid_return_to'])) {
            $root = $params['openid_return_to'];
        } else {
            return false;
        }
        if (Zend_OpenId::normalizeUrl($root) && !empty($root)) {
            return $root;
        }
        return false;
    }

    /**
     * Allows consumer with given root URL to authenticate current logged
     * in user. Returns true on success and false on error.
     *
     * @param string $root root URL
     * @param mixed $extensions extension object or array of extensions objects
     * @return bool
     */
    public function allowSite($root, $extensions=null)
    {
        $id = $this->getLoggedInUser();
        if ($id === false) {
            return false;
        }
        if ($extensions !== null) {
            $data = array();
            Zend_OpenId_Extension::forAll($extensions, 'getTrustData', $data);
        } else {
            $data = true;
        }
        $this->_storage->addSite($id, $root, $data);
        return true;
    }

    /**
     * Prohibit consumer with given root URL to authenticate current logged
     * in user. Returns true on success and false on error.
     *
     * @param string $root root URL
     * @return bool
     */
    public function denySite($root)
    {
        $id = $this->getLoggedInUser();
        if ($id === false) {
            return false;
        }
        $this->_storage->addSite($id, $root, false);
        return true;
    }

    /**
     * Delete consumer with given root URL from known sites of current logged
     * in user. Next time this consumer will try to authenticate the user,
     * Provider will ask user's confirmation.
     * Returns true on success and false on error.
     *
     * @param string $root root URL
     * @return bool
     */
    public function delSite($root)
    {
        $id = $this->getLoggedInUser();
        if ($id === false) {
            return false;
        }
        $this->_storage->addSite($id, $root, null);
        return true;
    }

    /**
     * Returns list of known consumers for current logged in user or false
     * if he is not logged in.
     *
     * @return mixed
     */
    public function getTrustedSites()
    {
        $id = $this->getLoggedInUser();
        if ($id === false) {
            return false;
        }
        return $this->_storage->getTrustedSites($id);
    }

    /**
     * Handles HTTP request from consumer
     *
     * @param array $params GET or POST variables. If this parameter is omited
     *  or set to null, then $_GET or $_POST superglobal variable is used
     *  according to REQUEST_METHOD.
     * @param mixed $extensions extension object or array of extensions objects
     * @param Zend_Controller_Response_Abstract $response an optional response
     *  object to perform HTTP or HTML form redirection
     * @return mixed
     */
    public function handle($params=null, $extensions=null,
                           Zend_Controller_Response_Abstract $response = null)
    {
        if ($params === null) {
            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                $params = $_GET;
            } else if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $params = $_POST;
            } else {
                return false;
            }
        }
        $version = 1.1;
        if (isset($params['openid_ns']) &&
            $params['openid_ns'] == Zend_OpenId::NS_2_0) {
            $version = 2.0;
        }
        if (isset($params['openid_mode'])) {
            if ($params['openid_mode'] == 'associate') {
                $response = $this->_associate($version, $params);
                $ret = '';
                foreach ($response as $key => $val) {
                    $ret .= $key . ':' . $val . "\n";
                }
                return $ret;
            } else if ($params['openid_mode'] == 'checkid_immediate') {
                $ret = $this->_checkId($version, $params, 1, $extensions, $response);
                if (is_bool($ret)) return $ret;
                if (!empty($params['openid_return_to'])) {
                    Zend_OpenId::redirect($params['openid_return_to'], $ret, $response);
                }
                return true;
            } else if ($params['openid_mode'] == 'checkid_setup') {
                $ret = $this->_checkId($version, $params, 0, $extensions, $response);
                if (is_bool($ret)) return $ret;
                if (!empty($params['openid_return_to'])) {
                    Zend_OpenId::redirect($params['openid_return_to'], $ret, $response);
                }
                return true;
            } else if ($params['openid_mode'] == 'check_authentication') {
                $response = $this->_checkAuthentication($version, $params);
                $ret = '';
                foreach ($response as $key => $val) {
                    $ret .= $key . ':' . $val . "\n";
                }
                return $ret;
            }
        }
        return false;
    }

    /**
     * Generates a secret key for given hash function, returns RAW key or false
     * if function is not supported
     *
     * @param string $func hash function (sha1 or sha256)
     * @return mixed
     */
    protected function _genSecret($func)
    {
        if ($func == 'sha1') {
            $macLen = 20; /* 160 bit */
        } else if ($func == 'sha256') {
            $macLen = 32; /* 256 bit */
        } else {
            return false;
        }
        return Zend_OpenId::randomBytes($macLen);
    }

    /**
     * Processes association request from OpenID consumerm generates secret
     * shared key and send it back using Diffie-Hellman encruption.
     * Returns array of variables to push back to consumer.
     *
     * @param float $version OpenID version
     * @param array $params GET or POST request variables
     * @return array
     */
    protected function _associate($version, $params)
    {
        $ret = array();

        if ($version >= 2.0) {
            $ret['ns'] = Zend_OpenId::NS_2_0;
        }

        if (isset($params['openid_assoc_type']) &&
            $params['openid_assoc_type'] == 'HMAC-SHA1') {
            $macFunc = 'sha1';
        } else if (isset($params['openid_assoc_type']) &&
            $params['openid_assoc_type'] == 'HMAC-SHA256' &&
            $version >= 2.0) {
            $macFunc = 'sha256';
        } else {
            $ret['error'] = 'Wrong "openid.assoc_type"';
            $ret['error-code'] = 'unsupported-type';
            return $ret;
        }

        $ret['assoc_type'] = $params['openid_assoc_type'];

        $secret = $this->_genSecret($macFunc);

        if (empty($params['openid_session_type']) ||
            $params['openid_session_type'] == 'no-encryption') {
            $ret['mac_key'] = base64_encode($secret);
        } else if (isset($params['openid_session_type']) &&
            $params['openid_session_type'] == 'DH-SHA1') {
            $dhFunc = 'sha1';
        } else if (isset($params['openid_session_type']) &&
            $params['openid_session_type'] == 'DH-SHA256' &&
            $version >= 2.0) {
            $dhFunc = 'sha256';
        } else {
            $ret['error'] = 'Wrong "openid.session_type"';
            $ret['error-code'] = 'unsupported-type';
            return $ret;
        }

        if (isset($params['openid_session_type'])) {
            $ret['session_type'] = $params['openid_session_type'];
        }

        if (isset($dhFunc)) {
            if (empty($params['openid_dh_consumer_public'])) {
                $ret['error'] = 'Wrong "openid.dh_consumer_public"';
                return $ret;
            }
            if (empty($params['openid_dh_gen'])) {
                $g = pack('H*', Zend_OpenId::DH_G);
            } else {
                $g = base64_decode($params['openid_dh_gen']);
            }
            if (empty($params['openid_dh_modulus'])) {
                $p = pack('H*', Zend_OpenId::DH_P);
            } else {
                $p = base64_decode($params['openid_dh_modulus']);
            }

            $dh = Zend_OpenId::createDhKey($p, $g);
            $dh_details = Zend_OpenId::getDhKeyDetails($dh);

            $sec = Zend_OpenId::computeDhSecret(
                base64_decode($params['openid_dh_consumer_public']), $dh);
            if ($sec === false) {
                $ret['error'] = 'Wrong "openid.session_type"';
                $ret['error-code'] = 'unsupported-type';
                return $ret;
            }
            $sec = Zend_OpenId::digest($dhFunc, $sec);
            $ret['dh_server_public'] = base64_encode(
                Zend_OpenId::btwoc($dh_details['pub_key']));
            $ret['enc_mac_key']      = base64_encode($secret ^ $sec);
        }

        $handle = uniqid();
        $expiresIn = $this->_sessionTtl;

        $ret['assoc_handle'] = $handle;
        $ret['expires_in'] = $expiresIn;

        $this->_storage->addAssociation($handle,
            $macFunc, $secret, time() + $expiresIn);

        return $ret;
    }

    /**
     * Performs authentication (or authentication check).
     *
     * @param float $version OpenID version
     * @param array $params GET or POST request variables
     * @param bool $immediate enables or disables interaction with user
     * @param mixed $extensions extension object or array of extensions objects
     * @param Zend_Controller_Response_Abstract $response
     * @return array
     */
    protected function _checkId($version, $params, $immediate, $extensions=null,
        Zend_Controller_Response_Abstract $response = null)
    {
        $ret = array();

        if ($version >= 2.0) {
            $ret['openid.ns'] = Zend_OpenId::NS_2_0;
        }
        $root = $this->getSiteRoot($params);
        if ($root === false) {
            return false;
        }

        if (isset($params['openid_identity']) &&
            !$this->_storage->hasUser($params['openid_identity'])) {
            $ret['openid.mode'] = ($immediate && $version >= 2.0) ? 'setup_needed': 'cancel';
            return $ret;
        }

        /* Check if user already logged in into the server */
        if (!isset($params['openid_identity']) ||
            $this->_user->getLoggedInUser() !== $params['openid_identity']) {
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
            if ($immediate) {
                $params2['openid.mode'] = 'checkid_setup';
                $ret['openid.mode'] = ($version >= 2.0) ? 'setup_needed': 'id_res';
                $ret['openid.user_setup_url'] = $this->_loginUrl
                    . (strpos($this->_loginUrl, '?') === false ? '?' : '&')
                    . Zend_OpenId::paramsToQuery($params2);
                return $ret;
            } else {
                /* Redirect to Server Login Screen */
                Zend_OpenId::redirect($this->_loginUrl, $params2, $response);
                return true;
            }
        }

        if (!Zend_OpenId_Extension::forAll($extensions, 'parseRequest', $params)) {
            $ret['openid.mode'] = ($immediate && $version >= 2.0) ? 'setup_needed': 'cancel';
            return $ret;
        }

        /* Check if user trusts to the consumer */
        $trusted = null;
        $sites = $this->_storage->getTrustedSites($params['openid_identity']);
        if (isset($params['openid_return_to'])) {
            $root = $params['openid_return_to'];
        }
        if (isset($sites[$root])) {
            $trusted = $sites[$root];
        } else {
            foreach ($sites as $site => $t) {
                if (strpos($root, $site) === 0) {
                    $trusted = $t;
                    break;
                } else {
                    /* OpenID 2.0 (9.2) check for realm wild-card matching */
                    $n = strpos($site, '://*.');
                    if ($n != false) {
                        $regex = '/^'
                               . preg_quote(substr($site, 0, $n+3), '/')
                               . '[A-Za-z1-9_\.]+?'
                               . preg_quote(substr($site, $n+4), '/')
                               . '/';
                        if (preg_match($regex, $root)) {
                            $trusted = $t;
                            break;
                        }
                    }
                }
            }
        }

        if (is_array($trusted)) {
            if (!Zend_OpenId_Extension::forAll($extensions, 'checkTrustData', $trusted)) {
                $trusted = null;
            }
        }

        if ($trusted === false) {
            $ret['openid.mode'] = 'cancel';
            return $ret;
        } else if ($trusted === null) {
            /* Redirect to Server Trust Screen */
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
            if ($immediate) {
                $params2['openid.mode'] = 'checkid_setup';
                $ret['openid.mode'] = ($version >= 2.0) ? 'setup_needed': 'id_res';
                $ret['openid.user_setup_url'] = $this->_trustUrl
                    . (strpos($this->_trustUrl, '?') === false ? '?' : '&')
                    . Zend_OpenId::paramsToQuery($params2);
                return $ret;
            } else {
                Zend_OpenId::redirect($this->_trustUrl, $params2, $response);
                return true;
            }
        }

        return $this->_respond($version, $ret, $params, $extensions);
    }

    /**
     * Perepares information to send back to consumer's authentication request,
     * signs it using shared secret and send back through HTTP redirection
     *
     * @param array $params GET or POST request variables
     * @param mixed $extensions extension object or array of extensions objects
     * @param Zend_Controller_Response_Abstract $response an optional response
     *  object to perform HTTP or HTML form redirection
     * @return bool
     */
    public function respondToConsumer($params, $extensions=null,
                           Zend_Controller_Response_Abstract $response = null)
    {
        $version = 1.1;
        if (isset($params['openid_ns']) &&
            $params['openid_ns'] == Zend_OpenId::NS_2_0) {
            $version = 2.0;
        }
        $ret = array();
        if ($version >= 2.0) {
            $ret['openid.ns'] = Zend_OpenId::NS_2_0;
        }
        $ret = $this->_respond($version, $ret, $params, $extensions);
        if (!empty($params['openid_return_to'])) {
            Zend_OpenId::redirect($params['openid_return_to'], $ret, $response);
        }
        return true;
    }

    /**
     * Perepares information to send back to consumer's authentication request
     * and signs it using shared secret.
     *
     * @param float $version OpenID protcol version
     * @param array $ret arguments to be send back to consumer
     * @param array $params GET or POST request variables
     * @param mixed $extensions extension object or array of extensions objects
     * @return array
     */
    protected function _respond($version, $ret, $params, $extensions=null)
    {
        if (empty($params['openid_assoc_handle']) ||
            !$this->_storage->getAssociation($params['openid_assoc_handle'],
                $macFunc, $secret, $expires)) {
            /* Use dumb mode */
            if (!empty($params['openid_assoc_handle'])) {
                $ret['openid.invalidate_handle'] = $params['openid_assoc_handle'];
            }
            $macFunc = $version >= 2.0 ? 'sha256' : 'sha1';
            $secret = $this->_genSecret($macFunc);
            $handle = uniqid();
            $expiresIn = $this->_sessionTtl;
            $this->_storage->addAssociation($handle,
                $macFunc, $secret, time() + $expiresIn);
            $ret['openid.assoc_handle'] = $handle;
        } else {
            $ret['openid.assoc_handle'] = $params['openid_assoc_handle'];
        }
        if (isset($params['openid_return_to'])) {
            $ret['openid.return_to'] = $params['openid_return_to'];
        }
        if (isset($params['openid_claimed_id'])) {
            $ret['openid.claimed_id'] = $params['openid_claimed_id'];
        }
        if (isset($params['openid_identity'])) {
            $ret['openid.identity'] = $params['openid_identity'];
        }

        if ($version >= 2.0) {
            if (!empty($this->_opEndpoint)) {
                $ret['openid.op_endpoint'] = $this->_opEndpoint;
            } else {
                $ret['openid.op_endpoint'] = Zend_OpenId::selfUrl();
            }
        }
        $ret['openid.response_nonce'] = gmdate('Y-m-d\TH:i:s\Z') . uniqid();
        $ret['openid.mode'] = 'id_res';

        Zend_OpenId_Extension::forAll($extensions, 'prepareResponse', $ret);

        $signed = '';
        $data = '';
        foreach ($ret as $key => $val) {
            if (strpos($key, 'openid.') === 0) {
                $key = substr($key, strlen('openid.'));
                if (!empty($signed)) {
                    $signed .= ',';
                }
                $signed .= $key;
                $data .= $key . ':' . $val . "\n";
            }
        }
        $signed .= ',signed';
        $data .= 'signed:' . $signed . "\n";
        $ret['openid.signed'] = $signed;

        $ret['openid.sig'] = base64_encode(
            Zend_OpenId::hashHmac($macFunc, $data, $secret));

        return $ret;
    }

    /**
     * Performs authentication validation for dumb consumers
     * Returns array of variables to push back to consumer.
     * It MUST contain 'is_valid' variable with value 'true' or 'false'.
     *
     * @param float $version OpenID version
     * @param array $params GET or POST request variables
     * @return array
     */
    protected function _checkAuthentication($version, $params)
    {
        $ret = array();
        if ($version >= 2.0) {
            $ret['ns'] = Zend_OpenId::NS_2_0;
        }
        $ret['openid.mode'] = 'id_res';

        if (empty($params['openid_assoc_handle']) ||
            empty($params['openid_signed']) ||
            empty($params['openid_sig']) ||
            !$this->_storage->getAssociation($params['openid_assoc_handle'],
                $macFunc, $secret, $expires)) {
            $ret['is_valid'] = 'false';
            return $ret;
        }

        $signed = explode(',', $params['openid_signed']);
        $data = '';
        foreach ($signed as $key) {
            $data .= $key . ':';
            if ($key == 'mode') {
                $data .= "id_res\n";
            } else {
                $data .= $params['openid_' . strtr($key,'.','_')]."\n";
            }
        }
        if ($this->_secureStringCompare(base64_decode($params['openid_sig']),
            Zend_OpenId::hashHmac($macFunc, $data, $secret))) {
            $ret['is_valid'] = 'true';
        } else {
            $ret['is_valid'] = 'false';
        }
        return $ret;
    }

    /**
     * Securely compare two strings for equality while avoided C level memcmp()
     * optimisations capable of leaking timing information useful to an attacker
     * attempting to iteratively guess the unknown string (e.g. password) being
     * compared against.
     *
     * @param string $a
     * @param string $b
     * @return bool
     */
    protected function _secureStringCompare($a, $b)
    {
        if (strlen($a) !== strlen($b)) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < strlen($a); $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $result == 0;
    }
}
