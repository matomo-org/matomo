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
 * @package    Zend_Http
 * @subpackage Client_Adapter
 * @version    $Id: Proxy.php 20947 2010-02-06 17:09:07Z padraic $
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Uri_Http
 */
require_once 'Zend/Uri/Http.php';
/**
 * @see Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';
/**
 * @see Zend_Http_Client_Adapter_Socket
 */
require_once 'Zend/Http/Client/Adapter/Socket.php';

/**
 * HTTP Proxy-supporting Zend_Http_Client adapter class, based on the default
 * socket based adapter.
 *
 * Should be used if proxy HTTP access is required. If no proxy is set, will
 * fall back to Zend_Http_Client_Adapter_Socket behavior. Just like the
 * default Socket adapter, this adapter does not require any special extensions
 * installed.
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage Client_Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_Client_Adapter_Proxy extends Zend_Http_Client_Adapter_Socket
{
    /**
     * Parameters array
     *
     * @var array
     */
    protected $config = array(
        'ssltransport'  => 'ssl',
        'sslcert'       => null,
        'sslpassphrase' => null,
        'proxy_host'    => '',
        'proxy_port'    => 8080,
        'proxy_user'    => '',
        'proxy_pass'    => '',
        'proxy_auth'    => Zend_Http_Client::AUTH_BASIC,
        'persistent'    => false
    );

    /**
     * Whether HTTPS CONNECT was already negotiated with the proxy or not
     *
     * @var boolean
     */
    protected $negotiated = false;

    /**
     * Connect to the remote server
     *
     * Will try to connect to the proxy server. If no proxy was set, will
     * fall back to the target server (behave like regular Socket adapter)
     *
     * @param string  $host
     * @param int     $port
     * @param boolean $secure
     */
    public function connect($host, $port = 80, $secure = false)
    {
        // If no proxy is set, fall back to Socket adapter
        if (! $this->config['proxy_host']) {
            return parent::connect($host, $port, $secure);
        }
        
        /* Url might require stream context even if proxy connection doesn't */
        if ($secure) {
        	$this->config['sslusecontext'] = true;
        }

        // Connect (a non-secure connection) to the proxy server
        return parent::connect(
            $this->config['proxy_host'],
            $this->config['proxy_port'],
            false
        );
    }

    /**
     * Send request to the proxy server
     *
     * @param string        $method
     * @param Zend_Uri_Http $uri
     * @param string        $http_ver
     * @param array         $headers
     * @param string        $body
     * @return string Request as string
     */
    public function write($method, $uri, $http_ver = '1.1', $headers = array(), $body = '')
    {
        // If no proxy is set, fall back to default Socket adapter
        if (! $this->config['proxy_host']) return parent::write($method, $uri, $http_ver, $headers, $body);

        // Make sure we're properly connected
        if (! $this->socket) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Trying to write but we are not connected");
        }

        $host = $this->config['proxy_host'];
        $port = $this->config['proxy_port'];

        if ($this->connected_to[0] != "tcp://$host" || $this->connected_to[1] != $port) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Trying to write but we are connected to the wrong proxy server");
        }

        // Add Proxy-Authorization header
        if ($this->config['proxy_user'] && ! isset($headers['proxy-authorization'])) {
            $headers['proxy-authorization'] = Zend_Http_Client::encodeAuthHeader(
                $this->config['proxy_user'], $this->config['proxy_pass'], $this->config['proxy_auth']
            );
        }

        // if we are proxying HTTPS, preform CONNECT handshake with the proxy
        if ($uri->getScheme() == 'https' && (! $this->negotiated)) {
            $this->connectHandshake($uri->getHost(), $uri->getPort(), $http_ver, $headers);
            $this->negotiated = true;
        }

        // Save request method for later
        $this->method = $method;

        // Build request headers
        if ($this->negotiated) {
            $path = $uri->getPath();
            if ($uri->getQuery()) {
                $path .= '?' . $uri->getQuery();
            }
            $request = "$method $path HTTP/$http_ver\r\n";
        } else {
            $request = "$method $uri HTTP/$http_ver\r\n";
        }

        // Add all headers to the request string
        foreach ($headers as $k => $v) {
            if (is_string($k)) $v = "$k: $v";
            $request .= "$v\r\n";
        }

        // Add the request body
        $request .= "\r\n" . $body;

        // Send the request
        if (! @fwrite($this->socket, $request)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Error writing request to proxy server");
        }

        return $request;
    }

    /**
     * Preform handshaking with HTTPS proxy using CONNECT method
     *
     * @param string  $host
     * @param integer $port
     * @param string  $http_ver
     * @param array   $headers
     */
    protected function connectHandshake($host, $port = 443, $http_ver = '1.1', array &$headers = array())
    {
        $request = "CONNECT $host:$port HTTP/$http_ver\r\n" .
                   "Host: " . $this->config['proxy_host'] . "\r\n";

        // Add the user-agent header
        if (isset($this->config['useragent'])) {
            $request .= "User-agent: " . $this->config['useragent'] . "\r\n";
        }

        // If the proxy-authorization header is set, send it to proxy but remove
        // it from headers sent to target host
        if (isset($headers['proxy-authorization'])) {
            $request .= "Proxy-authorization: " . $headers['proxy-authorization'] . "\r\n";
            unset($headers['proxy-authorization']);
        }

        $request .= "\r\n";

        // Send the request
        if (! @fwrite($this->socket, $request)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Error writing request to proxy server");
        }

        // Read response headers only
        $response = '';
        $gotStatus = false;
        while ($line = @fgets($this->socket)) {
            $gotStatus = $gotStatus || (strpos($line, 'HTTP') !== false);
            if ($gotStatus) {
                $response .= $line;
                if (!chop($line)) break;
            }
        }

        // Check that the response from the proxy is 200
        if (Zend_Http_Response::extractCode($response) != 200) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Unable to connect to HTTPS proxy. Server response: " . $response);
        }

        // If all is good, switch socket to secure mode. We have to fall back
        // through the different modes
        $modes = array(
            STREAM_CRYPTO_METHOD_TLS_CLIENT,
            STREAM_CRYPTO_METHOD_SSLv3_CLIENT,
            STREAM_CRYPTO_METHOD_SSLv23_CLIENT,
            STREAM_CRYPTO_METHOD_SSLv2_CLIENT
        );

        $success = false;
        foreach($modes as $mode) {
            $success = stream_socket_enable_crypto($this->socket, true, $mode);
            if ($success) break;
        }

        if (! $success) {
                require_once 'Zend/Http/Client/Adapter/Exception.php';
                throw new Zend_Http_Client_Adapter_Exception("Unable to connect to" .
                    " HTTPS server through proxy: could not negotiate secure connection.");
        }
    }

    /**
     * Close the connection to the server
     *
     */
    public function close()
    {
        parent::close();
        $this->negotiated = false;
    }

    /**
     * Destructor: make sure the socket is disconnected
     *
     */
    public function __destruct()
    {
        if ($this->socket) $this->close();
    }
}
