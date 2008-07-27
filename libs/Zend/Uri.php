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
 * @package    Zend_Uri
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Uri.php 8064 2008-02-16 10:58:39Z thomas $
 */

/**
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';

/**
 * @category   Zend
 * @package    Zend_Uri
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Uri
{
    /**
     * Scheme of this URI (http, ftp, etc.)
     * @var string
     */
    protected $_scheme = '';

    /**
     * Return a string representation of this URI.
     *
     * @see     getUri()
     * @return  string
     */
    public function __toString()
    {
        return $this->getUri();
    }

    /**
     * Convenience function, checks that a $uri string is well-formed
     * by validating it but not returning an object.  Returns TRUE if
     * $uri is a well-formed URI, or FALSE otherwise.
     *
     * @param string $uri
     * @return boolean
     */
    public static function check($uri)
    {
        try {
            $uri = self::factory($uri);
        } catch (Exception $e) {
            return false;
        }

        return $uri->valid();
    }

    /**
     * Create a new Zend_Uri object for a URI.  If building a new URI, then $uri should contain
     * only the scheme (http, ftp, etc).  Otherwise, supply $uri with the complete URI.
     *
     * @param string $uri
     * @throws Zend_Uri_Exception
     * @return Zend_Uri
     */
    public static function factory($uri = 'http')
    {
        /**
         * Separate the scheme from the scheme-specific parts
         * @link http://www.faqs.org/rfcs/rfc2396.html
         */
        $uri = explode(':', $uri, 2);
        $scheme = strtolower($uri[0]);
        $schemeSpecific = isset($uri[1]) ? $uri[1] : '';

        if (!strlen($scheme)) {
            require_once 'Zend/Uri/Exception.php';   
            throw new Zend_Uri_Exception('An empty string was supplied for the scheme');
        }

        // Security check: $scheme is used to load a class file, so only alphanumerics are allowed.
        if (!ctype_alnum($scheme)) {
            require_once 'Zend/Uri/Exception.php';   
            throw new Zend_Uri_Exception('Illegal scheme supplied, only alphanumeric characters are permitted');
        }

        /**
         * Create a new Zend_Uri object for the $uri. If a subclass of Zend_Uri exists for the
         * scheme, return an instance of that class. Otherwise, a Zend_Uri_Exception is thrown.
         */
        switch ($scheme) {
            case 'http':
            case 'https':
                $className = 'Zend_Uri_Http';
                break;
            case 'mailto':
                // @todo
            default:
                require_once 'Zend/Uri/Exception.php';   
                throw new Zend_Uri_Exception("Scheme \"$scheme\" is not supported");
        }
        Zend_Loader::loadClass($className);
        return new $className($scheme, $schemeSpecific);

    }

    /**
     * Get the URI's scheme
     *
     * @return string|false Scheme or false if no scheme is set.
     */
    public function getScheme()
    {
        if (!empty($this->_scheme)) {
            return $this->_scheme;
        } else {
            return false;
        }
    }

    /******************************************************************************
     * Abstract Methods
     *****************************************************************************/

    /**
     * Zend_Uri and its subclasses cannot be instantiated directly.
     * Use Zend_Uri::factory() to return a new Zend_Uri object.
     */
    abstract protected function __construct($scheme, $schemeSpecific = '');

    /**
     * Return a string representation of this URI.
     *
     * @return string
     */
    abstract public function getUri();

    /**
     * Returns TRUE if this URI is valid, or FALSE otherwise.
     *
     * @return boolean
     */
    abstract public function valid();
}
