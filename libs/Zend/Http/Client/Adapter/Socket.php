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
 * @version    $Id: Socket.php 8064 2008-02-16 10:58:39Z thomas $
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Uri/Http.php';
require_once 'Zend/Http/Client/Adapter/Interface.php';

/**
 * A sockets based (stream_socket_client) adapter class for Zend_Http_Client. Can be used
 * on almost every PHP environment, and does not require any special extensions.
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage Client_Adapter
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_Client_Adapter_Socket implements Zend_Http_Client_Adapter_Interface
{
    /**
     * The socket for server connection
     *
     * @var resource|null
     */
    protected $socket = null;

    /**
     * What host/port are we connected to?
     *
     * @var array
     */
    protected $connected_to = array(null, null);

    /**
     * Parameters array
     *
     * @var array
     */
    protected $config = array(
        'ssltransport'  => 'ssl',
        'sslcert'       => null,
        'sslpassphrase' => null
    );

    /**
     * Request method - will be set by write() and might be used by read()
     *
     * @var string
     */
    protected $method = null;

    /**
     * Adapter constructor, currently empty. Config is set using setConfig()
     *
     */
    public function __construct()
    {
    }

    /**
     * Set the configuration array for the adapter
     *
     * @param array $config
     */
    public function setConfig($config = array())
    {
        if (! is_array($config)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                '$config expects an array, ' . gettype($config) . ' recieved.');
        }

        foreach ($config as $k => $v) {
            $this->config[strtolower($k)] = $v;
        }
    }

    /**
     * Connect to the remote server
     *
     * @param string  $host
     * @param int     $port
     * @param boolean $secure
     * @param int     $timeout
     */
    public function connect($host, $port = 80, $secure = false)
    {
        // If the URI should be accessed via SSL, prepend the Hostname with ssl://
        $host = ($secure ? $this->config['ssltransport'] : 'tcp') . '://' . $host;

        // If we are connected to the wrong host, disconnect first
        if (($this->connected_to[0] != $host || $this->connected_to[1] != $port)) {
            if (is_resource($this->socket)) $this->close();
        }

        // Now, if we are not connected, connect
        if (! is_resource($this->socket) || ! $this->config['keepalive']) {
            $context = stream_context_create();
            if ($secure) {
                if ($this->config['sslcert'] !== null) {
                    if (! stream_context_set_option($context, 'ssl', 'local_cert',
                                                    $this->config['sslcert'])) {
                        require_once 'Zend/Http/Client/Adapter/Exception.php';
                        throw new Zend_Http_Client_Adapter_Exception('Unable to set sslcert option');
                    }
                }
                if ($this->config['sslpassphrase'] !== null) {
                    if (! stream_context_set_option($context, 'ssl', 'passphrase',
                                                    $this->config['sslpassphrase'])) {
                        require_once 'Zend/Http/Client/Adapter/Exception.php';
                        throw new Zend_Http_Client_Adapter_Exception('Unable to set sslpassphrase option');
                    }
                }
            }

            $this->socket = @stream_socket_client($host . ':' . $port,
                                                  $errno,
                                                  $errstr,
                                                  (int) $this->config['timeout'],
                                                  STREAM_CLIENT_CONNECT,
                                                  $context);
            if (! $this->socket) {
                $this->close();
                require_once 'Zend/Http/Client/Adapter/Exception.php';
                throw new Zend_Http_Client_Adapter_Exception(
                    'Unable to Connect to ' . $host . ':' . $port . '. Error #' . $errno . ': ' . $errstr);
            }

            // Set the stream timeout
            if (! stream_set_timeout($this->socket, (int) $this->config['timeout'])) {
                require_once 'Zend/Http/Client/Adapter/Exception.php';
                throw new Zend_Http_Client_Adapter_Exception('Unable to set the connection timeout');
            }

            // Update connected_to
            $this->connected_to = array($host, $port);
        }
    }

    /**
     * Send request to the remote server
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
        // Make sure we're properly connected
        if (! $this->socket) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception('Trying to write but we are not connected');
        }

        $host = $uri->getHost();
        $host = (strtolower($uri->getScheme()) == 'https' ? $this->config['ssltransport'] : 'tcp') . '://' . $host;
        if ($this->connected_to[0] != $host || $this->connected_to[1] != $uri->getPort()) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception('Trying to write but we are connected to the wrong host');
        }

        // Save request method for later
        $this->method = $method;

        // Build request headers
        $path = $uri->getPath();
        if ($uri->getQuery()) $path .= '?' . $uri->getQuery();
        $request = "{$method} {$path} HTTP/{$http_ver}\r\n";
        foreach ($headers as $k => $v) {
            if (is_string($k)) $v = ucfirst($k) . ": $v";
            $request .= "$v\r\n";
        }

        // Add the request body
        $request .= "\r\n" . $body;

        // Send the request
        if (! @fwrite($this->socket, $request)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception('Error writing request to server');
        }

        return $request;
    }

    /**
     * Read response from server
     *
     * @return string
     */
    public function read()
    {
        // First, read headers only
        $response = '';
        $gotStatus = false;
        while ($line = @fgets($this->socket)) {
            $gotStatus = $gotStatus || (strpos($line, 'HTTP') !== false);
            if ($gotStatus) {
                $response .= $line;
                if (!chop($line)) break;
            }
        }

        // Handle 100 and 101 responses internally by restarting the read again
        if (Zend_Http_Response::extractCode($response) == 100 ||
            Zend_Http_Response::extractCode($response) == 101) return $this->read();

        // If this was a HEAD request, return after reading the header (no need to read body)
        if ($this->method == Zend_Http_Client::HEAD) return $response;

        // Check headers to see what kind of connection / transfer encoding we have
        $headers = Zend_Http_Response::extractHeaders($response);

        // if the connection is set to close, just read until socket closes
        if (isset($headers['connection']) && $headers['connection'] == 'close') {
            while ($buff = @fread($this->socket, 8192)) {
                $response .= $buff;
            }

            $this->close();

        // Else, if we got a transfer-encoding header (chunked body)
        } elseif (isset($headers['transfer-encoding'])) {
            if ($headers['transfer-encoding'] == 'chunked') {
                do {
                    $chunk = '';
                    $line = @fgets($this->socket);
                    $chunk .= $line;

                    $hexchunksize = ltrim(chop($line), '0');
                    $hexchunksize = strlen($hexchunksize) ? strtolower($hexchunksize) : 0;

                    $chunksize = hexdec(chop($line));
                    if (dechex($chunksize) != $hexchunksize) {
                        @fclose($this->socket);
                        require_once 'Zend/Http/Client/Adapter/Exception.php';
                        throw new Zend_Http_Client_Adapter_Exception('Invalid chunk size "' .
                            $hexchunksize . '" unable to read chunked body');
                    }

                    $left_to_read = $chunksize;
                    while ($left_to_read > 0) {
                        $line = @fread($this->socket, $left_to_read);
                        $chunk .= $line;
                        $left_to_read -= strlen($line);
                    }

                    $chunk .= @fgets($this->socket);
                    $response .= $chunk;
                } while ($chunksize > 0);
            } else {
                throw new Zend_Http_Client_Adapter_Exception('Cannot handle "' .
                    $headers['transfer-encoding'] . '" transfer encoding');
            }

        // Else, if we got the content-length header, read this number of bytes
        } elseif (isset($headers['content-length'])) {
            $left_to_read = $headers['content-length'];
            $chunk = '';
            while ($left_to_read > 0) {
                $chunk = @fread($this->socket, $left_to_read);
                $left_to_read -= strlen($chunk);
                $response .= $chunk;
            }

        // Fallback: just read the response (should not happen)
        } else {
            while ($buff = @fread($this->socket, 8192)) {
                $response .= $buff;
            }

            $this->close();
        }

        return $response;
    }

    /**
     * Close the connection to the server
     *
     */
    public function close()
    {
        if (is_resource($this->socket)) @fclose($this->socket);
        $this->socket = null;
        $this->connected_to = array(null, null);
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
