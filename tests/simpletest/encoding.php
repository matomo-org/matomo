<?php
    /**
     *	base include file for SimpleTest
     *	@package	SimpleTest
     *	@subpackage	WebTester
     *	@version	$Id: encoding.php,v 1.18 2006/11/06 09:55:20 lastcraft Exp $
     */
     
    /**#@+
     *	include other SimpleTest class files
     */
    require_once(dirname(__FILE__) . '/socket.php');
    /**#@-*/

    /**
     *    Single post parameter.
	 *    @package SimpleTest
	 *    @subpackage WebTester
     */
    class SimpleEncodedPair {
        var $_key;
        var $_value;
        
        /**
         *    Stashes the data for rendering later.
         *    @param string $key       Form element name.
         *    @param string $value     Data to send.
         */
        function SimpleEncodedPair($key, $value) {
            $this->_key = $key;
            $this->_value = $value;
        }
        
        /**
         *    The pair as a single string.
         *    @return string        Encoded pair.
         *    @access public
         */
        function asRequest() {
            return urlencode($this->_key) . '=' . urlencode($this->_value);
        }
        
        /**
         *    The MIME part as a string.
         *    @return string        MIME part encoding.
         *    @access public
         */
        function asMime() {
            $part = 'Content-Disposition: form-data; ';
            $part .= "name=\"" . $this->_key . "\"\r\n";
            $part .= "\r\n" . $this->_value;
            return $part;
        }
        
        /**
         *    Is this the value we are looking for?
         *    @param string $key    Identifier.
         *    @return boolean       True if matched.
         *    @access public
         */
        function isKey($key) {
            return $key == $this->_key;
        }
        
        /**
         *    Is this the value we are looking for?
         *    @return string       Identifier.
         *    @access public
         */
        function getKey() {
            return $this->_key;
        }
        
        /**
         *    Is this the value we are looking for?
         *    @return string       Content.
         *    @access public
         */
        function getValue() {
            return $this->_value;
        }
    }

    /**
     *    Single post parameter.
	 *    @package SimpleTest
	 *    @subpackage WebTester
     */
    class SimpleAttachment {
        var $_key;
        var $_content;
        var $_filename;
        
        /**
         *    Stashes the data for rendering later.
         *    @param string $key          Key to add value to.
         *    @param string $content      Raw data.
         *    @param hash $filename       Original filename.
         */
        function SimpleAttachment($key, $content, $filename) {
            $this->_key = $key;
            $this->_content = $content;
            $this->_filename = $filename;
        }
        
        /**
         *    The pair as a single string.
         *    @return string        Encoded pair.
         *    @access public
         */
        function asRequest() {
            return '';
        }
        
        /**
         *    The MIME part as a string.
         *    @return string        MIME part encoding.
         *    @access public
         */
        function asMime() {
            $part = 'Content-Disposition: form-data; ';
            $part .= 'name="' . $this->_key . '"; ';
            $part .= 'filename="' . $this->_filename . '"';
            $part .= "\r\nContent-Type: " . $this->_deduceMimeType();
            $part .= "\r\n\r\n" . $this->_content;
            return $part;
        }
        
        /**
         *    Attempts to figure out the MIME type from the
         *    file extension and the content.
         *    @return string        MIME type.
         *    @access private
         */
        function _deduceMimeType() {
            if ($this->_isOnlyAscii($this->_content)) {
                return 'text/plain';
            }
            return 'application/octet-stream';
        }
        
        /**
         *    Tests each character is in the range 0-127.
         *    @param string $ascii    String to test.
         *    @access private
         */
        function _isOnlyAscii($ascii) {
            for ($i = 0, $length = strlen($ascii); $i < $length; $i++) {
                if (ord($ascii[$i]) > 127) {
                    return false;
                }
            }
            return true;
        }
        
        /**
         *    Is this the value we are looking for?
         *    @param string $key    Identifier.
         *    @return boolean       True if matched.
         *    @access public
         */
        function isKey($key) {
            return $key == $this->_key;
        }
        
        /**
         *    Is this the value we are looking for?
         *    @return string       Identifier.
         *    @access public
         */
        function getKey() {
            return $this->_key;
        }
        
        /**
         *    Is this the value we are looking for?
         *    @return string       Content.
         *    @access public
         */
        function getValue() {
            return $this->_filename;
        }
    }

    /**
     *    Bundle of GET/POST parameters. Can include
     *    repeated parameters.
	 *    @package SimpleTest
	 *    @subpackage WebTester
     */
    class SimpleEncoding {
        var $_request;
        
        /**
         *    Starts empty.
         *    @param array $query       Hash of parameters.
         *                              Multiple values are
         *                              as lists on a single key.
         *    @access public
         */
        function SimpleEncoding($query = false) {
            if (! $query) {
                $query = array();
            }
            $this->clear();
            $this->merge($query);
        }
        
        /**
         *    Empties the request of parameters.
         *    @access public
         */
        function clear() {
            $this->_request = array();
        }
        
        /**
         *    Adds a parameter to the query.
         *    @param string $key            Key to add value to.
         *    @param string/array $value    New data.
         *    @access public
         */
        function add($key, $value) {
            if ($value === false) {
                return;
            }
            if (is_array($value)) {
                foreach ($value as $item) {
                    $this->_addPair($key, $item);
                }
            } else {
                $this->_addPair($key, $value);
            }
        }
        
        /**
         *    Adds a new value into the request.
         *    @param string $key            Key to add value to.
         *    @param string/array $value    New data.
         *    @access private
         */
        function _addPair($key, $value) {
            $this->_request[] = new SimpleEncodedPair($key, $value);
        }
        
        /**
         *    Adds a MIME part to the query. Does nothing for a
         *    form encoded packet.
         *    @param string $key          Key to add value to.
         *    @param string $content      Raw data.
         *    @param hash $filename       Original filename.
         *    @access public
         */
        function attach($key, $content, $filename) {
            $this->_request[] = new SimpleAttachment($key, $content, $filename);
        }
        
        /**
         *    Adds a set of parameters to this query.
         *    @param array/SimpleQueryString $query  Multiple values are
         *                                           as lists on a single key.
         *    @access public
         */
        function merge($query) {
            if (is_object($query)) {
                $this->_request = array_merge($this->_request, $query->getAll());
            } elseif (is_array($query)) {
                foreach ($query as $key => $value) {
                    $this->add($key, $value);
                }
            }
        }
        
        /**
         *    Accessor for single value.
         *    @return string/array    False if missing, string
         *                            if present and array if
         *                            multiple entries.
         *    @access public
         */
        function getValue($key) {
            $values = array();
            foreach ($this->_request as $pair) {
                if ($pair->isKey($key)) {
                    $values[] = $pair->getValue();
                }
            }
            if (count($values) == 0) {
                return false;
            } elseif (count($values) == 1) {
                return $values[0];
            } else {
                return $values;
            }
        }
        
        /**
         *    Accessor for listing of pairs.
         *    @return array        All pair objects.
         *    @access public
         */
        function getAll() {
            return $this->_request;
        }
        
        /**
         *    Renders the query string as a URL encoded
         *    request part.
         *    @return string        Part of URL.
         *    @access protected
         */
        function _encode() {
            $statements = array();
            foreach ($this->_request as $pair) {
                if ($statement = $pair->asRequest()) {
                    $statements[] = $statement;
                }
            }
            return implode('&', $statements);
        }
    }
    
    /**
     *    Bundle of GET parameters. Can include
     *    repeated parameters.
	 *    @package SimpleTest
	 *    @subpackage WebTester
     */
    class SimpleGetEncoding extends SimpleEncoding {
        
        /**
         *    Starts empty.
         *    @param array $query       Hash of parameters.
         *                              Multiple values are
         *                              as lists on a single key.
         *    @access public
         */
        function SimpleGetEncoding($query = false) {
            $this->SimpleEncoding($query);
        }
        
        /**
         *    HTTP request method.
         *    @return string        Always GET.
         *    @access public
         */
        function getMethod() {
            return 'GET';
        }
        
        /**
         *    Writes no extra headers.
         *    @param SimpleSocket $socket        Socket to write to.
         *    @access public
         */
        function writeHeadersTo(&$socket) {
        }
        
        /**
         *    No data is sent to the socket as the data is encoded into
         *    the URL.
         *    @param SimpleSocket $socket        Socket to write to.
         *    @access public
         */
        function writeTo(&$socket) {
        }
        
        /**
         *    Renders the query string as a URL encoded
         *    request part for attaching to a URL.
         *    @return string        Part of URL.
         *    @access public
         */
        function asUrlRequest() {
            return $this->_encode();
        }
    }
    
    /**
     *    Bundle of URL parameters for a HEAD request.
	 *    @package SimpleTest
	 *    @subpackage WebTester
     */
    class SimpleHeadEncoding extends SimpleGetEncoding {
        
        /**
         *    Starts empty.
         *    @param array $query       Hash of parameters.
         *                              Multiple values are
         *                              as lists on a single key.
         *    @access public
         */
        function SimpleHeadEncoding($query = false) {
            $this->SimpleGetEncoding($query);
        }
        
        /**
         *    HTTP request method.
         *    @return string        Always HEAD.
         *    @access public
         */
        function getMethod() {
            return 'HEAD';
        }
    }
    
    /**
     *    Bundle of POST parameters. Can include
     *    repeated parameters.
	 *    @package SimpleTest
	 *    @subpackage WebTester
     */
    class SimplePostEncoding extends SimpleEncoding {
        
        /**
         *    Starts empty.
         *    @param array $query       Hash of parameters.
         *                              Multiple values are
         *                              as lists on a single key.
         *    @access public
         */
        function SimplePostEncoding($query = false) {
            $this->SimpleEncoding($query);
        }
        
        /**
         *    HTTP request method.
         *    @return string        Always POST.
         *    @access public
         */
        function getMethod() {
            return 'POST';
        }
        
        /**
         *    Dispatches the form headers down the socket.
         *    @param SimpleSocket $socket        Socket to write to.
         *    @access public
         */
        function writeHeadersTo(&$socket) {
            $socket->write("Content-Length: " . (integer)strlen($this->_encode()) . "\r\n");
            $socket->write("Content-Type: application/x-www-form-urlencoded\r\n");
        }
        
        /**
         *    Dispatches the form data down the socket.
         *    @param SimpleSocket $socket        Socket to write to.
         *    @access public
         */
        function writeTo(&$socket) {
            $socket->write($this->_encode());
        }
        
        /**
         *    Renders the query string as a URL encoded
         *    request part for attaching to a URL.
         *    @return string        Part of URL.
         *    @access public
         */
        function asUrlRequest() {
            return '';
        }
    }
    
    /**
     *    Bundle of POST parameters in the multipart
     *    format. Can include file uploads.
	 *    @package SimpleTest
	 *    @subpackage WebTester
     */
    class SimpleMultipartEncoding extends SimplePostEncoding {
        var $_boundary;
        
        /**
         *    Starts empty.
         *    @param array $query       Hash of parameters.
         *                              Multiple values are
         *                              as lists on a single key.
         *    @access public
         */
        function SimpleMultipartEncoding($query = false, $boundary = false) {
            $this->SimplePostEncoding($query);
            $this->_boundary = ($boundary === false ? uniqid('st') : $boundary);
        }
        
        /**
         *    Dispatches the form headers down the socket.
         *    @param SimpleSocket $socket        Socket to write to.
         *    @access public
         */
        function writeHeadersTo(&$socket) {
            $socket->write("Content-Length: " . (integer)strlen($this->_encode()) . "\r\n");
            $socket->write("Content-Type: multipart/form-data, boundary=" . $this->_boundary . "\r\n");
        }
        
        /**
         *    Dispatches the form data down the socket.
         *    @param SimpleSocket $socket        Socket to write to.
         *    @access public
         */
        function writeTo(&$socket) {
            $socket->write($this->_encode());
        }
        
        /**
         *    Renders the query string as a URL encoded
         *    request part.
         *    @return string        Part of URL.
         *    @access public
         */
        function _encode() {
            $stream = '';
            foreach ($this->_request as $pair) {
                $stream .= "--" . $this->_boundary . "\r\n";
                $stream .= $pair->asMime() . "\r\n";
            }
            $stream .= "--" . $this->_boundary . "--\r\n";
            return $stream;
        }
    }
?>