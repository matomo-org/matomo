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
 * @package    Zend_Feed_Pubsubhubbub
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Feed_Pubsubhubbub
 */
// require_once 'Zend/Feed/Pubsubhubbub.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Pubsubhubbub
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Pubsubhubbub_HttpResponse
{
    /**
     * The body of any response to the current callback request
     *
     * @var string
     */
    protected $_body = '';

    /**
     * Array of headers. Each header is an array with keys 'name' and 'value'
     *
     * @var array
     */
    protected $_headers = array();

    /**
     * HTTP response code to use in headers
     *
     * @var int
     */
    protected $_httpResponseCode = 200;

    /**
     * Send the response, including all headers
     *
     * @return void
     */
    public function sendResponse()
    {
        $this->sendHeaders();
        echo $this->getBody();
    }

    /**
     * Send all headers
     *
     * Sends any headers specified. If an {@link setHttpResponseCode() HTTP response code}
     * has been specified, it is sent with the first header.
     *
     * @return void
     */
    public function sendHeaders()
    {
        if (count($this->_headers) || (200 != $this->_httpResponseCode)) {
            $this->canSendHeaders(true);
        } elseif (200 == $this->_httpResponseCode) {
            return;
        }
        $httpCodeSent = false;
        foreach ($this->_headers as $header) {
            if (!$httpCodeSent && $this->_httpResponseCode) {
                header($header['name'] . ': ' . $header['value'], $header['replace'], $this->_httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header['name'] . ': ' . $header['value'], $header['replace']);
            }
        }
        if (!$httpCodeSent) {
            header('HTTP/1.1 ' . $this->_httpResponseCode);
            $httpCodeSent = true;
        }
    }

    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param  string $name
     * @param  string $value
     * @param  boolean $replace
     * @return Zend_Feed_Pubsubhubbub_HttpResponse
     */
    public function setHeader($name, $value, $replace = false)
    {
        $name  = $this->_normalizeHeader($name);
        $value = (string) $value;
        if ($replace) {
            foreach ($this->_headers as $key => $header) {
                if ($name == $header['name']) {
                    unset($this->_headers[$key]);
                }
            }
        }
        $this->_headers[] = array(
            'name'    => $name,
            'value'   => $value,
            'replace' => $replace,
        );

        return $this;
    }

    /**
     * Check if a specific Header is set and return its value
     *
     * @param  string $name
     * @return string|null
     */
    public function getHeader($name)
    {
        $name = $this->_normalizeHeader($name);
        foreach ($this->_headers as $header) {
            if ($header['name'] == $name) {
                return $header['value'];
            }
        }
    }

    /**
     * Return array of headers; see {@link $_headers} for format
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Can we send headers?
     *
     * @param  boolean $throw Whether or not to throw an exception if headers have been sent; defaults to false
     * @return boolean
     * @throws Zend_Feed_Pubsubhubbub_Exception
     */
    public function canSendHeaders($throw = false)
    {
        $ok = headers_sent($file, $line);
        if ($ok && $throw) {
            // require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Cannot send headers; headers already sent in ' . $file . ', line ' . $line);
        }
        return !$ok;
    }

    /**
     * Set HTTP response code to use with headers
     *
     * @param  int $code
     * @return Zend_Feed_Pubsubhubbub_HttpResponse
     */
    public function setHttpResponseCode($code)
    {
        if (!is_int($code) || (100 > $code) || (599 < $code)) {
            // require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid HTTP response'
            . ' code:' . $code);
        }
        $this->_httpResponseCode = $code;
        return $this;
    }

    /**
     * Retrieve HTTP response code
     *
     * @return int
     */
    public function getHttpResponseCode()
    {
        return $this->_httpResponseCode;
    }

    /**
     * Set body content
     *
     * @param  string $content
     * @return Zend_Feed_Pubsubhubbub_HttpResponse
     */
    public function setBody($content)
    {
        $this->_body = (string) $content;
        $this->setHeader('content-length', strlen($content));
        return $this;
    }

    /**
     * Return the body content
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Normalizes a header name to X-Capitalized-Names
     *
     * @param  string $name
     * @return string
     */
    protected function _normalizeHeader($name)
    {
        $filtered = str_replace(array('-', '_'), ' ', (string) $name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }
}
