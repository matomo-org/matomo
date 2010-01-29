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
 * @version    $Id: Socket.php 20096 2010-01-06 02:05:09Z bkarwin $
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Uri_Http
 */
require_once 'Zend/Uri/Http.php';
/**
 * @see Zend_Http_Client_Adapter_Interface
 */
require_once 'Zend/Http/Client/Adapter/Interface.php';
/**
 * @see Zend_Http_Client_Adapter_Stream
 */
require_once 'Zend/Http/Client/Adapter/Stream.php';

/**
 * A sockets based (stream_socket_client) adapter class for Zend_Http_Client. Can be used
 * on almost every PHP environment, and does not require any special extensions.
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage Client_Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_Client_Adapter_Socket implements Zend_Http_Client_Adapter_Interface, Zend_Http_Client_Adapter_Stream
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
     * Stream for storing output
     * 
     * @var resource
     */
    protected $out_stream = null;
    
    /**
     * Parameters array
     *
     * @var array
     */
    protected $config = array(
        'persistent'    => false,
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
     * Stream context
     *
     * @var resource
     */
    protected $_context = null;

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
     * @param Zend_Config | array $config
     */
    public function setConfig($config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();

        } elseif (! is_array($config)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                'Array or Zend_Config object expected, got ' . gettype($config)
            );
        }

        foreach ($config as $k => $v) {
            $this->config[strtolower($k)] = $v;
        }
    }

	/**
 	 * Retrieve the array of all configuration options
 	 *
 	 * @return array
 	 */
 	public function getConfig()
 	{
 	    return $this->config;
 	}

 	/**
     * Set the stream context for the TCP connection to the server
     *
     * Can accept either a pre-existing stream context resource, or an array
     * of stream options, similar to the options array passed to the
     * stream_context_create() PHP function. In such case a new stream context
     * will be created using the passed options.
     *
     * @since  Zend Framework 1.9
     *
     * @param  mixed $context Stream context or array of context options
     * @return Zend_Http_Client_Adapter_Socket
     */
    public function setStreamContext($context)
    {
        if (is_resource($context) && get_resource_type($context) == 'stream-context') {
            $this->_context = $context;

        } elseif (is_array($context)) {
            $this->_context = stream_context_create($context);

        } else {
            // Invalid parameter
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                "Expecting either a stream context resource or array, got " . gettype($context)
            );
        }

        return $this;
    }

    /**
     * Get the stream context for the TCP connection to the server.
     *
     * If no stream context is set, will create a default one.
     *
     * @return resource
     */
    public function getStreamContext()
    {
        if (! $this->_context) {
            $this->_context = stream_context_create();
        }

        return $this->_context;
    }

    /**
     * Connect to the remote server
     *
     * @param string  $host
     * @param int     $port
     * @param boolean $secure
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
            $context = $this->getStreamContext();
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

            $flags = STREAM_CLIENT_CONNECT;
            if ($this->config['persistent']) $flags |= STREAM_CLIENT_PERSISTENT;

            $this->socket = @stream_socket_client($host . ':' . $port,
                                                  $errno,
                                                  $errstr,
                                                  (int) $this->config['timeout'],
                                                  $flags,
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

        if(is_resource($body)) {
            $request .= "\r\n";
        } else {
            // Add the request body
            $request .= "\r\n" . $body;
        }
        
        // Send the request
        if (! @fwrite($this->socket, $request)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception('Error writing request to server');
        }
        
        if(is_resource($body)) {
            if(stream_copy_to_stream($body, $this->socket) == 0) {
                require_once 'Zend/Http/Client/Adapter/Exception.php';
                throw new Zend_Http_Client_Adapter_Exception('Error writing request to server');
            }
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
        $stream = !empty($this->config['stream']);

        while (($line = @fgets($this->socket)) !== false) {
            $gotStatus = $gotStatus || (strpos($line, 'HTTP') !== false);
            if ($gotStatus) {
                $response .= $line;
                if (rtrim($line) === '') break;
            }
        }
        
        $this->_checkSocketReadTimeout();

        $statusCode = Zend_Http_Response::extractCode($response);

        // Handle 100 and 101 responses internally by restarting the read again
        if ($statusCode == 100 || $statusCode == 101) return $this->read();

        // Check headers to see what kind of connection / transfer encoding we have
        $headers = Zend_Http_Response::extractHeaders($response);

        /**
         * Responses to HEAD requests and 204 or 304 responses are not expected
         * to have a body - stop reading here
         */
        if ($statusCode == 304 || $statusCode == 204 ||
            $this->method == Zend_Http_Client::HEAD) {

            // Close the connection if requested to do so by the server
            if (isset($headers['connection']) && $headers['connection'] == 'close') {
                $this->close();
            }
            return $response;
        }

        // If we got a 'transfer-encoding: chunked' header
        if (isset($headers['transfer-encoding'])) {
            
            if (strtolower($headers['transfer-encoding']) == 'chunked') {

                do {
                    $line  = @fgets($this->socket);
                    $this->_checkSocketReadTimeout();

                    $chunk = $line;

                    // Figure out the next chunk size
                    $chunksize = trim($line);
                    if (! ctype_xdigit($chunksize)) {
                        $this->close();
                        require_once 'Zend/Http/Client/Adapter/Exception.php';
                        throw new Zend_Http_Client_Adapter_Exception('Invalid chunk size "' .
                            $chunksize . '" unable to read chunked body');
                    }

                    // Convert the hexadecimal value to plain integer
                    $chunksize = hexdec($chunksize);

                    // Read next chunk
                    $read_to = ftell($this->socket) + $chunksize;

                    do {
                        $current_pos = ftell($this->socket);
                        if ($current_pos >= $read_to) break;

                        if($this->out_stream) {
                            if(stream_copy_to_stream($this->socket, $this->out_stream, $read_to - $current_pos) == 0) {
                              $this->_checkSocketReadTimeout();
                              break;   
                             }
                        } else {
                            $line = @fread($this->socket, $read_to - $current_pos);
                            if ($line === false || strlen($line) === 0) {
                                $this->_checkSocketReadTimeout();
                                break;
                            }
                                    $chunk .= $line;
                        }
                    } while (! feof($this->socket));

                    $chunk .= @fgets($this->socket);
                    $this->_checkSocketReadTimeout();

                    if(!$this->out_stream) {
                        $response .= $chunk;
                    }
                } while ($chunksize > 0);
            } else {
                $this->close();
                throw new Zend_Http_Client_Adapter_Exception('Cannot handle "' .
                    $headers['transfer-encoding'] . '" transfer encoding');
            }
            
            // We automatically decode chunked-messages when writing to a stream
            // this means we have to disallow the Zend_Http_Response to do it again
            if ($this->out_stream) {
                $response = str_ireplace("Transfer-Encoding: chunked\r\n", '', $response);
            }
        // Else, if we got the content-length header, read this number of bytes
        } elseif (isset($headers['content-length'])) {

            $current_pos = ftell($this->socket);
            $chunk = '';

            for ($read_to = $current_pos + $headers['content-length'];
                 $read_to > $current_pos;
                 $current_pos = ftell($this->socket)) {

                 if($this->out_stream) {
                     if(@stream_copy_to_stream($this->socket, $this->out_stream, $read_to - $current_pos) == 0) {
                          $this->_checkSocketReadTimeout();
                          break;   
                     }
                 } else {
                    $chunk = @fread($this->socket, $read_to - $current_pos);
                    if ($chunk === false || strlen($chunk) === 0) {
                        $this->_checkSocketReadTimeout();
                        break;
                    }

                    $response .= $chunk;
                }

                // Break if the connection ended prematurely
                if (feof($this->socket)) break;
            }

        // Fallback: just read the response until EOF
        } else {

            do {
                if($this->out_stream) {
                    if(@stream_copy_to_stream($this->socket, $this->out_stream) == 0) {
                          $this->_checkSocketReadTimeout();
                          break;   
                     }
                }  else {
                    $buff = @fread($this->socket, 8192);
                    if ($buff === false || strlen($buff) === 0) {
                        $this->_checkSocketReadTimeout();
                        break;
                    } else {
                        $response .= $buff;
                    }
                }

            } while (feof($this->socket) === false);

            $this->close();
        }

        // Close the connection if requested to do so by the server
        if (isset($headers['connection']) && $headers['connection'] == 'close') {
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
     * Check if the socket has timed out - if so close connection and throw
     * an exception
     *
     * @throws Zend_Http_Client_Adapter_Exception with READ_TIMEOUT code
     */
    protected function _checkSocketReadTimeout()
    {
        if ($this->socket) {
            $info = stream_get_meta_data($this->socket);
            $timedout = $info['timed_out'];
            if ($timedout) {
                $this->close();
                require_once 'Zend/Http/Client/Adapter/Exception.php';
                throw new Zend_Http_Client_Adapter_Exception(
                    "Read timed out after {$this->config['timeout']} seconds",
                    Zend_Http_Client_Adapter_Exception::READ_TIMEOUT
                );
            }
        }
    }
    
    /**
     * Set output stream for the response
     * 
     * @param resource $stream
     * @return Zend_Http_Client_Adapter_Socket
     */
    public function setOutputStream($stream) 
    {
        $this->out_stream = $stream;
        return $this;
    }
    
    /**
     * Destructor: make sure the socket is disconnected
     *
     * If we are in persistent TCP mode, will not close the connection
     *
     */
    public function __destruct()
    {
        if (! $this->config['persistent']) {
            if ($this->socket) $this->close();
        }
    }
}
