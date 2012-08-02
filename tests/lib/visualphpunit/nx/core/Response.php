<?php

namespace nx\core;

/**
 * The Response class is used to render an HTTP response.
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright 2011-2012 Nick Sinopoli
 * @license   http://opensource.org/licenses/BSD-3-Clause The BSD License
 */
class Response {

   /**
    * The configuration settings.
    *
    * @var array
    */
    protected $_config = array();

   /**
    *  The HTTP status codes.
    *
    *  @var array
    */
    protected $_statuses = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out'
    );

   /**
    * Sets the configuration options.
    *
    * Possible keys include the following:
    *
    * * 'buffer_size' - The number of bytes each chunk of output should contain
    *
    * @param array $config    The configuration options.
    * @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'buffer_size'  => 8192
        );
        $this->_config = $config + $defaults;
    }

   /**
    * Converts an integer status to a well-formed HTTP status header.
    *
    * @param int $code    The integer associated with the HTTP status.
    * @return string
    */
    protected function _convert_status($code) {
        if ( isset($this->_statuses[$code]) ) {
            return "HTTP/1.1 {$code} {$this->_statuses[$code]}";
        }
        return "HTTP/1.1 200 OK";
    }

   /**
    * Parses a response.
    *
    * @param mixed $response    The response to be parsed.  Can be an array
    *                           containing 'body', 'headers', and/or 'status'
    *                           keys, or a string which will be used as the
    *                           body of the response.  Note that the headers
    *                           must be well-formed HTTP headers, and the
    *                           status must be an integer (i.e., the one
    *                           associated with the HTTP status code).
    * @return array
    */
    protected function _parse($response) {
        $defaults = array(
            'body'    => '',
            'headers' => array('Content-Type: text/html; charset=utf-8'),
            'status'  => 200
        );
        if ( is_array($response) ) {
            $response += $defaults;
        } elseif ( is_string($response) ) {
            $defaults['body'] = $response;
            $response = $defaults;
        } else {
            $defaults['status'] = 500;
            $response = $defaults;
        }
        return $response;
    }

   /**
    * Renders a response.
    *
    * @param mixed $response    The response to be rendered.  Can be an array
    *                           containing 'body', 'headers', and/or 'status'
    *                           keys, or a string which will be used as the
    *                           body of the response.  Note that the headers
    *                           must be well-formed HTTP headers, and the
    *                           status must be an integer (i.e., the one
    *                           associated with the HTTP status code).  The
    *                           response body is chunked according to the
    *                           buffer_size set in the constructor.
    * @return void
    */
    public function render($response) {
        $response = $this->_parse($response);
        $status = $this->_convert_status($response['status']);
        header($status);
        foreach ( $response['headers'] as $header ) {
            header($header, false);
        }

        $buffer_size = $this->_config['buffer_size'];
        $length = strlen($response['body']);
        for ( $i = 0; $i < $length; $i += $buffer_size ) {
            echo substr($response['body'], $i, $buffer_size);
        }
    }

}

?>
