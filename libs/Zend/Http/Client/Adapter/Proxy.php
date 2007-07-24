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
 * @version    $Id: Proxy.php 4797 2007-05-14 19:18:13Z shahar $
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Uri/Http.php';
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Client/Adapter/Socket.php';
require_once 'Zend/Http/Client/Adapter/Exception.php';

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
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
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
        'proxy_host'    => '',
        'proxy_port'    => 8080,
        'proxy_user'    => '',
        'proxy_pass'    => '',
        'proxy_auth'    => Zend_Http_Client::AUTH_BASIC 
    );
    
    /**
     * Connect to the remote server
     * 
     * Will try to connect to the proxy server. If no proxy was set, will
     * fall back to the target server (behave like regular Socket adapter)
     *
     * @param string  $host
     * @param int     $port
     * @param boolean $secure
     * @param int     $timeout
     */
    public function connect($host, $port = 80, $secure = false)
    {
        // If no proxy is set, fall back to Socket adapter
        if (! $this->config['proxy_host']) return parent::connect($host, $port, $secure);
        
        // Go through a proxy - the connection is actually to the proxy server
        $host = $this->config['proxy_host'];
        $port = $this->config['proxy_port'];

        // If we are connected to the wrong proxy, disconnect first
        if (($this->connected_to[0] != $host || $this->connected_to[1] != $port)) {
            if (is_resource($this->socket)) $this->close();
        }

        // Now, if we are not connected, connect
        if (! is_resource($this->socket) || ! $this->config['keepalive']) {
            $this->socket = @fsockopen($host, $port, $errno, $errstr, (int) $this->config['timeout']);
            if (! $this->socket) {
                $this->close();
                throw new Zend_Http_Client_Adapter_Exception(
                    'Unable to Connect to proxy server ' . $host . ':' . $port . '. Error #' . $errno . ': ' . $errstr);
            }
           
            // Set the stream timeout
            if (!stream_set_timeout($this->socket, (int) $this->config['timeout'])) {
                throw new Zend_Http_Client_Adapter_Exception('Unable to set the connection timeout');
            }

            // Update connected_to
            $this->connected_to = array($host, $port);
        }
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
        if (! $this->socket)
            throw new Zend_Http_Client_Adapter_Exception("Trying to write but we are not connected");
        
        $host = $this->config['proxy_host'];
        $port = $this->config['proxy_port'];
                
        if ($this->connected_to[0] != $host || $this->connected_to[1] != $port)
            throw new Zend_Http_Client_Adapter_Exception("Trying to write but we are connected to the wrong proxy server");

        // Save request method for later
        $this->method = $method;
        
        // Build request headers
        $request = "{$method} {$uri->__toString()} HTTP/{$http_ver}\r\n";
        
        // Add Proxy-Authorization header
        if ($this->config['proxy_user'] && ! isset($headers['proxy-authorization']))
            $headers['proxy-authorization'] = Zend_Http_Client::encodeAuthHeader(
                $this->config['proxy_user'], $this->config['proxy_pass'], $this->config['proxy_auth']
            );
        
        // Add all headers to the request string
        foreach ($headers as $k => $v) {
            if (is_string($k)) $v = ucfirst($k) . ": $v";
            $request .= "$v\r\n";
        }
        
        // Add the request body
        $request .= "\r\n" . $body;
        
        // Send the request
        if (! @fwrite($this->socket, $request)) {
            throw new Zend_Http_Client_Adapter_Exception("Error writing request to proxy server");
        }
        
        return $request;
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
