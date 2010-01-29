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
 * @version    $Id: Http.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';


/**
 * HTTP Authentication Adapter
 *
 * Implements a pretty good chunk of RFC 2617.
 *
 * @category   Zend
 * @package    Zend_Auth
 * @subpackage Zend_Auth_Adapter_Http
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @todo       Support auth-int
 * @todo       Track nonces, nonce-count, opaque for replay protection and stale support
 * @todo       Support Authentication-Info header
 */
class Zend_Auth_Adapter_Http implements Zend_Auth_Adapter_Interface
{
    /**
     * Reference to the HTTP Request object
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Reference to the HTTP Response object
     *
     * @var Zend_Controller_Response_Http
     */
    protected $_response;

    /**
     * Object that looks up user credentials for the Basic scheme
     *
     * @var Zend_Auth_Adapter_Http_Resolver_Interface
     */
    protected $_basicResolver;

    /**
     * Object that looks up user credentials for the Digest scheme
     *
     * @var Zend_Auth_Adapter_Http_Resolver_Interface
     */
    protected $_digestResolver;

    /**
     * List of authentication schemes supported by this class
     *
     * @var array
     */
    protected $_supportedSchemes = array('basic', 'digest');

    /**
     * List of schemes this class will accept from the client
     *
     * @var array
     */
    protected $_acceptSchemes;

    /**
     * Space-delimited list of protected domains for Digest Auth
     *
     * @var string
     */
    protected $_domains;

    /**
     * The protection realm to use
     *
     * @var string
     */
    protected $_realm;

    /**
     * Nonce timeout period
     *
     * @var integer
     */
    protected $_nonceTimeout;

    /**
     * Whether to send the opaque value in the header. True by default
     *
     * @var boolean
     */
    protected $_useOpaque;

    /**
     * List of the supported digest algorithms. I want to support both MD5 and
     * MD5-sess, but MD5-sess won't make it into the first version.
     *
     * @var array
     */
    protected $_supportedAlgos = array('MD5');

    /**
     * The actual algorithm to use. Defaults to MD5
     *
     * @var string
     */
    protected $_algo;

    /**
     * List of supported qop options. My intetion is to support both 'auth' and
     * 'auth-int', but 'auth-int' won't make it into the first version.
     *
     * @var array
     */
    protected $_supportedQops = array('auth');

    /**
     * Whether or not to do Proxy Authentication instead of origin server
     * authentication (send 407's instead of 401's). Off by default.
     *
     * @var boolean
     */
    protected $_imaProxy;

    /**
     * Flag indicating the client is IE and didn't bother to return the opaque string
     *
     * @var boolean
     */
    protected $_ieNoOpaque;

    /**
     * Constructor
     *
     * @param  array $config Configuration settings:
     *    'accept_schemes' => 'basic'|'digest'|'basic digest'
     *    'realm' => <string>
     *    'digest_domains' => <string> Space-delimited list of URIs
     *    'nonce_timeout' => <int>
     *    'use_opaque' => <bool> Whether to send the opaque value in the header
     *    'alogrithm' => <string> See $_supportedAlgos. Default: MD5
     *    'proxy_auth' => <bool> Whether to do authentication as a Proxy
     * @throws Zend_Auth_Adapter_Exception
     * @return void
     */
    public function __construct(array $config)
    {
        if (!extension_loaded('hash')) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception(__CLASS__  . ' requires the \'hash\' extension');
        }

        $this->_request  = null;
        $this->_response = null;
        $this->_ieNoOpaque = false;


        if (empty($config['accept_schemes'])) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('Config key \'accept_schemes\' is required');
        }

        $schemes = explode(' ', $config['accept_schemes']);
        $this->_acceptSchemes = array_intersect($schemes, $this->_supportedSchemes);
        if (empty($this->_acceptSchemes)) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('No supported schemes given in \'accept_schemes\'. Valid values: '
                                                . implode(', ', $this->_supportedSchemes));
        }

        // Double-quotes are used to delimit the realm string in the HTTP header,
        // and colons are field delimiters in the password file.
        if (empty($config['realm']) ||
            !ctype_print($config['realm']) ||
            strpos($config['realm'], ':') !== false ||
            strpos($config['realm'], '"') !== false) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('Config key \'realm\' is required, and must contain only printable '
                                                . 'characters, excluding quotation marks and colons');
        } else {
            $this->_realm = $config['realm'];
        }

        if (in_array('digest', $this->_acceptSchemes)) {
            if (empty($config['digest_domains']) ||
                !ctype_print($config['digest_domains']) ||
                strpos($config['digest_domains'], '"') !== false) {
                /**
                 * @see Zend_Auth_Adapter_Exception
                 */
                require_once 'Zend/Auth/Adapter/Exception.php';
                throw new Zend_Auth_Adapter_Exception('Config key \'digest_domains\' is required, and must contain '
                                                    . 'only printable characters, excluding quotation marks');
            } else {
                $this->_domains = $config['digest_domains'];
            }

            if (empty($config['nonce_timeout']) ||
                !is_numeric($config['nonce_timeout'])) {
                /**
                 * @see Zend_Auth_Adapter_Exception
                 */
                require_once 'Zend/Auth/Adapter/Exception.php';
                throw new Zend_Auth_Adapter_Exception('Config key \'nonce_timeout\' is required, and must be an '
                                                    . 'integer');
            } else {
                $this->_nonceTimeout = (int) $config['nonce_timeout'];
            }

            // We use the opaque value unless explicitly told not to
            if (isset($config['use_opaque']) && false == (bool) $config['use_opaque']) {
                $this->_useOpaque = false;
            } else {
                $this->_useOpaque = true;
            }

            if (isset($config['algorithm']) && in_array($config['algorithm'], $this->_supportedAlgos)) {
                $this->_algo = $config['algorithm'];
            } else {
                $this->_algo = 'MD5';
            }
        }

        // Don't be a proxy unless explicitly told to do so
        if (isset($config['proxy_auth']) && true == (bool) $config['proxy_auth']) {
            $this->_imaProxy = true;  // I'm a Proxy
        } else {
            $this->_imaProxy = false;
        }
    }

    /**
     * Setter for the _basicResolver property
     *
     * @param  Zend_Auth_Adapter_Http_Resolver_Interface $resolver
     * @return Zend_Auth_Adapter_Http Provides a fluent interface
     */
    public function setBasicResolver(Zend_Auth_Adapter_Http_Resolver_Interface $resolver)
    {
        $this->_basicResolver = $resolver;

        return $this;
    }

    /**
     * Getter for the _basicResolver property
     *
     * @return Zend_Auth_Adapter_Http_Resolver_Interface
     */
    public function getBasicResolver()
    {
        return $this->_basicResolver;
    }

    /**
     * Setter for the _digestResolver property
     *
     * @param  Zend_Auth_Adapter_Http_Resolver_Interface $resolver
     * @return Zend_Auth_Adapter_Http Provides a fluent interface
     */
    public function setDigestResolver(Zend_Auth_Adapter_Http_Resolver_Interface $resolver)
    {
        $this->_digestResolver = $resolver;

        return $this;
    }

    /**
     * Getter for the _digestResolver property
     *
     * @return Zend_Auth_Adapter_Http_Resolver_Interface
     */
    public function getDigestResolver()
    {
        return $this->_digestResolver;
    }

    /**
     * Setter for the Request object
     *
     * @param  Zend_Controller_Request_Http $request
     * @return Zend_Auth_Adapter_Http Provides a fluent interface
     */
    public function setRequest(Zend_Controller_Request_Http $request)
    {
        $this->_request = $request;

        return $this;
    }

    /**
     * Getter for the Request object
     *
     * @return Zend_Controller_Request_Http
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Setter for the Response object
     *
     * @param  Zend_Controller_Response_Http $response
     * @return Zend_Auth_Adapter_Http Provides a fluent interface
     */
    public function setResponse(Zend_Controller_Response_Http $response)
    {
        $this->_response = $response;

        return $this;
    }

    /**
     * Getter for the Response object
     *
     * @return Zend_Controller_Response_Http
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Authenticate
     *
     * @throws Zend_Auth_Adapter_Exception
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        if (empty($this->_request) ||
            empty($this->_response)) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('Request and Response objects must be set before calling '
                                                . 'authenticate()');
        }

        if ($this->_imaProxy) {
            $getHeader = 'Proxy-Authorization';
        } else {
            $getHeader = 'Authorization';
        }

        $authHeader = $this->_request->getHeader($getHeader);
        if (!$authHeader) {
            return $this->_challengeClient();
        }

        list($clientScheme) = explode(' ', $authHeader);
        $clientScheme = strtolower($clientScheme);

        // The server can issue multiple challenges, but the client should
        // answer with only the selected auth scheme.
        if (!in_array($clientScheme, $this->_supportedSchemes)) {
            $this->_response->setHttpResponseCode(400);
            return new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE_UNCATEGORIZED,
                array(),
                array('Client requested an incorrect or unsupported authentication scheme')
            );
        }

        // client sent a scheme that is not the one required
        if (!in_array($clientScheme, $this->_acceptSchemes)) {
            // challenge again the client
            return $this->_challengeClient();
        }

        switch ($clientScheme) {
            case 'basic':
                $result = $this->_basicAuth($authHeader);
                break;
            case 'digest':
                $result = $this->_digestAuth($authHeader);
            break;
            default:
                /**
                 * @see Zend_Auth_Adapter_Exception
                 */
                require_once 'Zend/Auth/Adapter/Exception.php';
                throw new Zend_Auth_Adapter_Exception('Unsupported authentication scheme');
        }

        return $result;
    }

    /**
     * Challenge Client
     *
     * Sets a 401 or 407 Unauthorized response code, and creates the
     * appropriate Authenticate header(s) to prompt for credentials.
     *
     * @return Zend_Auth_Result Always returns a non-identity Auth result
     */
    protected function _challengeClient()
    {
        if ($this->_imaProxy) {
            $statusCode = 407;
            $headerName = 'Proxy-Authenticate';
        } else {
            $statusCode = 401;
            $headerName = 'WWW-Authenticate';
        }

        $this->_response->setHttpResponseCode($statusCode);

        // Send a challenge in each acceptable authentication scheme
        if (in_array('basic', $this->_acceptSchemes)) {
            $this->_response->setHeader($headerName, $this->_basicHeader());
        }
        if (in_array('digest', $this->_acceptSchemes)) {
            $this->_response->setHeader($headerName, $this->_digestHeader());
        }
        return new Zend_Auth_Result(
            Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
            array(),
            array('Invalid or absent credentials; challenging client')
        );
    }

    /**
     * Basic Header
     *
     * Generates a Proxy- or WWW-Authenticate header value in the Basic
     * authentication scheme.
     *
     * @return string Authenticate header value
     */
    protected function _basicHeader()
    {
        return 'Basic realm="' . $this->_realm . '"';
    }

    /**
     * Digest Header
     *
     * Generates a Proxy- or WWW-Authenticate header value in the Digest
     * authentication scheme.
     *
     * @return string Authenticate header value
     */
    protected function _digestHeader()
    {
        $wwwauth = 'Digest realm="' . $this->_realm . '", '
                 . 'domain="' . $this->_domains . '", '
                 . 'nonce="' . $this->_calcNonce() . '", '
                 . ($this->_useOpaque ? 'opaque="' . $this->_calcOpaque() . '", ' : '')
                 . 'algorithm="' . $this->_algo . '", '
                 . 'qop="' . implode(',', $this->_supportedQops) . '"';

        return $wwwauth;
    }

    /**
     * Basic Authentication
     *
     * @param  string $header Client's Authorization header
     * @throws Zend_Auth_Adapter_Exception
     * @return Zend_Auth_Result
     */
    protected function _basicAuth($header)
    {
        if (empty($header)) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('The value of the client Authorization header is required');
        }
        if (empty($this->_basicResolver)) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('A basicResolver object must be set before doing Basic '
                                                . 'authentication');
        }

        // Decode the Authorization header
        $auth = substr($header, strlen('Basic '));
        $auth = base64_decode($auth);
        if (!$auth) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('Unable to base64_decode Authorization header value');
        }

        // See ZF-1253. Validate the credentials the same way the digest
        // implementation does. If invalid credentials are detected,
        // re-challenge the client.
        if (!ctype_print($auth)) {
            return $this->_challengeClient();
        }
        // Fix for ZF-1515: Now re-challenges on empty username or password
        $creds = array_filter(explode(':', $auth));
        if (count($creds) != 2) {
            return $this->_challengeClient();
        }

        $password = $this->_basicResolver->resolve($creds[0], $this->_realm);
        if ($password && $password == $creds[1]) {
            $identity = array('username'=>$creds[0], 'realm'=>$this->_realm);
            return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity);
        } else {
            return $this->_challengeClient();
        }
    }

    /**
     * Digest Authentication
     *
     * @param  string $header Client's Authorization header
     * @throws Zend_Auth_Adapter_Exception
     * @return Zend_Auth_Result Valid auth result only on successful auth
     */
    protected function _digestAuth($header)
    {
        if (empty($header)) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('The value of the client Authorization header is required');
        }
        if (empty($this->_digestResolver)) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('A digestResolver object must be set before doing Digest authentication');
        }

        $data = $this->_parseDigestAuth($header);
        if ($data === false) {
            $this->_response->setHttpResponseCode(400);
            return new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE_UNCATEGORIZED,
                array(),
                array('Invalid Authorization header format')
            );
        }

        // See ZF-1052. This code was a bit too unforgiving of invalid
        // usernames. Now, if the username is bad, we re-challenge the client.
        if ('::invalid::' == $data['username']) {
            return $this->_challengeClient();
        }

        // Verify that the client sent back the same nonce
        if ($this->_calcNonce() != $data['nonce']) {
            return $this->_challengeClient();
        }
        // The opaque value is also required to match, but of course IE doesn't
        // play ball.
        if (!$this->_ieNoOpaque && $this->_calcOpaque() != $data['opaque']) {
            return $this->_challengeClient();
        }

        // Look up the user's password hash. If not found, deny access.
        // This makes no assumptions about how the password hash was
        // constructed beyond that it must have been built in such a way as
        // to be recreatable with the current settings of this object.
        $ha1 = $this->_digestResolver->resolve($data['username'], $data['realm']);
        if ($ha1 === false) {
            return $this->_challengeClient();
        }

        // If MD5-sess is used, a1 value is made of the user's password
        // hash with the server and client nonce appended, separated by
        // colons.
        if ($this->_algo == 'MD5-sess') {
            $ha1 = hash('md5', $ha1 . ':' . $data['nonce'] . ':' . $data['cnonce']);
        }

        // Calculate h(a2). The value of this hash depends on the qop
        // option selected by the client and the supported hash functions
        switch ($data['qop']) {
            case 'auth':
                $a2 = $this->_request->getMethod() . ':' . $data['uri'];
                break;
            case 'auth-int':
                // Should be REQUEST_METHOD . ':' . uri . ':' . hash(entity-body),
                // but this isn't supported yet, so fall through to default case
            default:
                /**
                 * @see Zend_Auth_Adapter_Exception
                 */
                require_once 'Zend/Auth/Adapter/Exception.php';
                throw new Zend_Auth_Adapter_Exception('Client requested an unsupported qop option');
        }
        // Using hash() should make parameterizing the hash algorithm
        // easier
        $ha2 = hash('md5', $a2);


        // Calculate the server's version of the request-digest. This must
        // match $data['response']. See RFC 2617, section 3.2.2.1
        $message = $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $ha2;
        $digest  = hash('md5', $ha1 . ':' . $message);

        // If our digest matches the client's let them in, otherwise return
        // a 401 code and exit to prevent access to the protected resource.
        if ($digest == $data['response']) {
            $identity = array('username'=>$data['username'], 'realm'=>$data['realm']);
            return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity);
        } else {
            return $this->_challengeClient();
        }
    }

    /**
     * Calculate Nonce
     *
     * @return string The nonce value
     */
    protected function _calcNonce()
    {
        // Once subtle consequence of this timeout calculation is that it
        // actually divides all of time into _nonceTimeout-sized sections, such
        // that the value of timeout is the point in time of the next
        // approaching "boundary" of a section. This allows the server to
        // consistently generate the same timeout (and hence the same nonce
        // value) across requests, but only as long as one of those
        // "boundaries" is not crossed between requests. If that happens, the
        // nonce will change on its own, and effectively log the user out. This
        // would be surprising if the user just logged in.
        $timeout = ceil(time() / $this->_nonceTimeout) * $this->_nonceTimeout;

        $nonce = hash('md5', $timeout . ':' . $this->_request->getServer('HTTP_USER_AGENT') . ':' . __CLASS__);
        return $nonce;
    }

    /**
     * Calculate Opaque
     *
     * The opaque string can be anything; the client must return it exactly as
     * it was sent. It may be useful to store data in this string in some
     * applications. Ideally, a new value for this would be generated each time
     * a WWW-Authenticate header is sent (in order to reduce predictability),
     * but we would have to be able to create the same exact value across at
     * least two separate requests from the same client.
     *
     * @return string The opaque value
     */
    protected function _calcOpaque()
    {
        return hash('md5', 'Opaque Data:' . __CLASS__);
    }

    /**
     * Parse Digest Authorization header
     *
     * @param  string $header Client's Authorization: HTTP header
     * @return array|false Data elements from header, or false if any part of
     *         the header is invalid
     */
    protected function _parseDigestAuth($header)
    {
        $temp = null;
        $data = array();

        // See ZF-1052. Detect invalid usernames instead of just returning a
        // 400 code.
        $ret = preg_match('/username="([^"]+)"/', $header, $temp);
        if (!$ret || empty($temp[1])
                  || !ctype_print($temp[1])
                  || strpos($temp[1], ':') !== false) {
            $data['username'] = '::invalid::';
        } else {
            $data['username'] = $temp[1];
        }
        $temp = null;

        $ret = preg_match('/realm="([^"]+)"/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        if (!ctype_print($temp[1]) || strpos($temp[1], ':') !== false) {
            return false;
        } else {
            $data['realm'] = $temp[1];
        }
        $temp = null;

        $ret = preg_match('/nonce="([^"]+)"/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        if (!ctype_xdigit($temp[1])) {
            return false;
        } else {
            $data['nonce'] = $temp[1];
        }
        $temp = null;

        $ret = preg_match('/uri="([^"]+)"/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        // Section 3.2.2.5 in RFC 2617 says the authenticating server must
        // verify that the URI field in the Authorization header is for the
        // same resource requested in the Request Line.
        $rUri = @parse_url($this->_request->getRequestUri());
        $cUri = @parse_url($temp[1]);
        if (false === $rUri || false === $cUri) {
            return false;
        } else {
            // Make sure the path portion of both URIs is the same
            if ($rUri['path'] != $cUri['path']) {
                return false;
            }
            // Section 3.2.2.5 seems to suggest that the value of the URI
            // Authorization field should be made into an absolute URI if the
            // Request URI is absolute, but it's vague, and that's a bunch of
            // code I don't want to write right now.
            $data['uri'] = $temp[1];
        }
        $temp = null;

        $ret = preg_match('/response="([^"]+)"/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        if (32 != strlen($temp[1]) || !ctype_xdigit($temp[1])) {
            return false;
        } else {
            $data['response'] = $temp[1];
        }
        $temp = null;

        // The spec says this should default to MD5 if omitted. OK, so how does
        // that square with the algo we send out in the WWW-Authenticate header,
        // if it can easily be overridden by the client?
        $ret = preg_match('/algorithm="?(' . $this->_algo . ')"?/', $header, $temp);
        if ($ret && !empty($temp[1])
                 && in_array($temp[1], $this->_supportedAlgos)) {
            $data['algorithm'] = $temp[1];
        } else {
            $data['algorithm'] = 'MD5';  // = $this->_algo; ?
        }
        $temp = null;

        // Not optional in this implementation
        $ret = preg_match('/cnonce="([^"]+)"/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        if (!ctype_print($temp[1])) {
            return false;
        } else {
            $data['cnonce'] = $temp[1];
        }
        $temp = null;

        // If the server sent an opaque value, the client must send it back
        if ($this->_useOpaque) {
            $ret = preg_match('/opaque="([^"]+)"/', $header, $temp);
            if (!$ret || empty($temp[1])) {

                // Big surprise: IE isn't RFC 2617-compliant.
                if (false !== strpos($this->_request->getHeader('User-Agent'), 'MSIE')) {
                    $temp[1] = '';
                    $this->_ieNoOpaque = true;
                } else {
                    return false;
                }
            }
            // This implementation only sends MD5 hex strings in the opaque value
            if (!$this->_ieNoOpaque &&
                (32 != strlen($temp[1]) || !ctype_xdigit($temp[1]))) {
                return false;
            } else {
                $data['opaque'] = $temp[1];
            }
            $temp = null;
        }

        // Not optional in this implementation, but must be one of the supported
        // qop types
        $ret = preg_match('/qop="?(' . implode('|', $this->_supportedQops) . ')"?/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        if (!in_array($temp[1], $this->_supportedQops)) {
            return false;
        } else {
            $data['qop'] = $temp[1];
        }
        $temp = null;

        // Not optional in this implementation. The spec says this value
        // shouldn't be a quoted string, but apparently some implementations
        // quote it anyway. See ZF-1544.
        $ret = preg_match('/nc="?([0-9A-Fa-f]{8})"?/', $header, $temp);
        if (!$ret || empty($temp[1])) {
            return false;
        }
        if (8 != strlen($temp[1]) || !ctype_xdigit($temp[1])) {
            return false;
        } else {
            $data['nc'] = $temp[1];
        }
        $temp = null;

        return $data;
    }
}
