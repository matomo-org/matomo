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
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Feed.php 8064 2008-02-16 10:58:39Z thomas $
 */


/**
 * Feed utility class
 *
 * Base Zend_Feed class, containing constants and the Zend_Http_Client instance
 * accessor.
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed
{

    /**
     * HTTP client object to use for retrieving feeds
     *
     * @var Zend_Http_Client
     */
    protected static $_httpClient = null;

    /**
     * Override HTTP PUT and DELETE request methods?
     *
     * @var boolean
     */
    protected static $_httpMethodOverride = false;

    /**
     * @var array
     */
    protected static $_namespaces = array(
        'opensearch' => 'http://a9.com/-/spec/opensearchrss/1.0/',
        'atom'       => 'http://www.w3.org/2005/Atom',
        'rss'        => 'http://blogs.law.harvard.edu/tech/rss',
    );


    /**
     * Set the HTTP client instance
     *
     * Sets the HTTP client object to use for retrieving the feeds.
     *
     * @param  Zend_Http_Client $httpClient
     * @return void
     */
    public static function setHttpClient(Zend_Http_Client $httpClient)
    {
        self::$_httpClient = $httpClient;
    }


    /**
     * Gets the HTTP client object. If none is set, a new Zend_Http_Client will be used.
     *
     * @return Zend_Http_Client_Abstract
     */
    public static function getHttpClient()
    {
        if (!self::$_httpClient instanceof Zend_Http_Client) {
            /**
             * @see Zend_Http_Client
             */
            require_once 'Zend/Http/Client.php';
            self::$_httpClient = new Zend_Http_Client();
        }

        return self::$_httpClient;
    }


    /**
     * Toggle using POST instead of PUT and DELETE HTTP methods
     *
     * Some feed implementations do not accept PUT and DELETE HTTP
     * methods, or they can't be used because of proxies or other
     * measures. This allows turning on using POST where PUT and
     * DELETE would normally be used; in addition, an
     * X-Method-Override header will be sent with a value of PUT or
     * DELETE as appropriate.
     *
     * @param  boolean $override Whether to override PUT and DELETE.
     * @return void
     */
    public static function setHttpMethodOverride($override = true)
    {
        self::$_httpMethodOverride = $override;
    }


    /**
     * Get the HTTP override state
     *
     * @return boolean
     */
    public static function getHttpMethodOverride()
    {
        return self::$_httpMethodOverride;
    }


    /**
     * Get the full version of a namespace prefix
     *
     * Looks up a prefix (atom:, etc.) in the list of registered
     * namespaces and returns the full namespace URI if
     * available. Returns the prefix, unmodified, if it's not
     * registered.
     *
     * @return string
     */
    public static function lookupNamespace($prefix)
    {
        return isset(self::$_namespaces[$prefix]) ?
            self::$_namespaces[$prefix] :
            $prefix;
    }


    /**
     * Add a namespace and prefix to the registered list
     *
     * Takes a prefix and a full namespace URI and adds them to the
     * list of registered namespaces for use by
     * Zend_Feed::lookupNamespace().
     *
     * @param  string $prefix The namespace prefix
     * @param  string $namespaceURI The full namespace URI
     * @return void
     */
    public static function registerNamespace($prefix, $namespaceURI)
    {
        self::$_namespaces[$prefix] = $namespaceURI;
    }


    /**
     * Imports a feed located at $uri.
     *
     * @param  string $uri
     * @throws Zend_Feed_Exception
     * @return Zend_Feed_Abstract
     */
    public static function import($uri)
    {
        $client = self::getHttpClient();
        $client->setUri($uri);
        $response = $client->request('GET');
        if ($response->getStatus() !== 200) {
            /**
             * @see Zend_Feed_Exception
             */
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Feed failed to load, got response code ' . $response->getStatus());
        }
        $feed = $response->getBody();
        return self::importString($feed);
    }


    /**
     * Imports a feed represented by $string.
     *
     * @param  string $string
     * @throws Zend_Feed_Exception
     * @return Zend_Feed_Abstract
     */
    public static function importString($string)
    {
        // Load the feed as an XML DOMDocument object
        @ini_set('track_errors', 1);
        $doc = @DOMDocument::loadXML($string);
        @ini_restore('track_errors');

        if (!$doc) {
            // prevent the class to generate an undefined variable notice (ZF-2590)
            if (!isset($php_errormsg)) {
                if (function_exists('xdebug_is_enabled')) {
                    $php_errormsg = '(error message not available, when XDebug is running)';
                } else {
                    $php_errormsg = '(error message not available)';
                }
            }

            /**
             * @see Zend_Feed_Exception
             */
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception("DOMDocument cannot parse XML: $php_errormsg");
        }

        // Try to find the base feed element or a single <entry> of an Atom feed
        if ($doc->getElementsByTagName('feed')->item(0) ||
            $doc->getElementsByTagName('entry')->item(0)) {
            /**
             * @see Zend_Feed_Atom
             */
            require_once 'Zend/Feed/Atom.php';
            // return a newly created Zend_Feed_Atom object
            return new Zend_Feed_Atom(null, $string);
        }

        // Try to find the base feed element of an RSS feed
        if ($doc->getElementsByTagName('channel')->item(0)) {
            /**
             * @see Zend_Feed_Rss
             */
            require_once 'Zend/Feed/Rss.php';
            // return a newly created Zend_Feed_Rss object
            return new Zend_Feed_Rss(null, $string);
        }

        // $string does not appear to be a valid feed of the supported types
        /**
         * @see Zend_Feed_Exception
         */
        require_once 'Zend/Feed/Exception.php';
        throw new Zend_Feed_Exception('Invalid or unsupported feed format');
    }


    /**
     * Imports a feed from a file located at $filename.
     *
     * @param  string $filename
     * @throws Zend_Feed_Exception
     * @return Zend_Feed_Abstract
     */
    public static function importFile($filename)
    {
        @ini_set('track_errors', 1);
        $feed = @file_get_contents($filename);
        @ini_restore('track_errors');
        if ($feed === false) {
            /**
             * @see Zend_Feed_Exception
             */
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception("File could not be loaded: $php_errormsg");
        }
        return self::importString($feed);
    }


    /**
     * Attempts to find feeds at $uri referenced by <link ... /> tags. Returns an
     * array of the feeds referenced at $uri.
     *
     * @todo Allow findFeeds() to follow one, but only one, code 302.
     *
     * @param  string $uri
     * @throws Zend_Feed_Exception
     * @return array
     */
    public static function findFeeds($uri)
    {
        // Get the HTTP response from $uri and save the contents
        $client = self::getHttpClient();
        $client->setUri($uri);
        $response = $client->request();
        if ($response->getStatus() !== 200) {
            /**
             * @see Zend_Feed_Exception
             */
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception("Failed to access $uri, got response code " . $response->getStatus());
        }
        $contents = $response->getBody();

        // Parse the contents for appropriate <link ... /> tags
        @ini_set('track_errors', 1);
        $pattern = '~(<link[^>]+)/?>~i';
        $result = @preg_match_all($pattern, $contents, $matches);
        @ini_restore('track_errors');
        if ($result === false) {
            /**
             * @see Zend_Feed_Exception
             */
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception("Internal error: $php_errormsg");
        }

        // Try to fetch a feed for each link tag that appears to refer to a feed
        $feeds = array();
        if (isset($matches[1]) && count($matches[1]) > 0) {
            foreach ($matches[1] as $link) {
                // force string to be an utf-8 one
                if (!mb_check_encoding($link, 'UTF-8')) {
                    $link = mb_convert_encoding($link, 'UTF-8');
                }
                $xml = @simplexml_load_string(rtrim($link, ' /') . ' />');
                if ($xml === false) {
                    continue;
                }
                $attributes = $xml->attributes();
                if (!isset($attributes['rel']) || !@preg_match('~^(?:alternate|service\.feed)~i', $attributes['rel'])) {
                    continue;
                }
                if (!isset($attributes['type']) ||
                        !@preg_match('~^application/(?:atom|rss|rdf)\+xml~', $attributes['type'])) {
                    continue;
                }
                if (!isset($attributes['href'])) {
                    continue;
                }
                try {
                    // checks if we need to canonize the given uri
                    try {
                        $uri = Zend_Uri::factory((string) $attributes['href']);
                    } catch (Zend_Uri_Exception $e) {
                        // canonize the uri
                        $path = (string) $attributes['href'];
                        $query = $fragment = '';
                        if (substr($path, 0, 1) != '/') {
                            // add the current root path to this one
                            $path = rtrim($client->getUri()->getPath(), '/') . '/' . $path;
                        }
                        if (strpos($path, '?') !== false) {
                            list($path, $query) = explode('?', $path, 2);
                        }
                        if (strpos($query, '#') !== false) {
                            list($query, $fragment) = explode('#', $query, 2);
                        }
                        $uri = Zend_Uri::factory($client->getUri(true));
                        $uri->setPath($path);
                        $uri->setQuery($query);
                        $uri->setFragment($fragment);
                    }

                    $feed = self::import($uri);
                } catch (Exception $e) {
                    continue;
                }
                $feeds[] = $feed;
            }
        }

        // Return the fetched feeds
        return $feeds;
    }

    /**
     * Construct a new Zend_Feed_Abstract object from a custom array
     *
     * @param  array  $data
     * @param  string $format (rss|atom) the requested output format
     * @return Zend_Feed_Abstract
     */
    public static function importArray(array $data, $format = 'atom')
    {
        $obj = 'Zend_Feed_' . ucfirst(strtolower($format));
        /**
         * @see Zend_Loader
         */
        require_once 'Zend/Loader.php';
        Zend_Loader::loadClass($obj);
        Zend_Loader::loadClass('Zend_Feed_Builder');

        return new $obj(null, null, new Zend_Feed_Builder($data));
    }

    /**
     * Construct a new Zend_Feed_Abstract object from a Zend_Feed_Builder_Interface data source
     *
     * @param  Zend_Feed_Builder_Interface $builder this object will be used to extract the data of the feed
     * @param  string                      $format (rss|atom) the requested output format
     * @return Zend_Feed_Abstract
     */
    public static function importBuilder(Zend_Feed_Builder_Interface $builder, $format = 'atom')
    {
        $obj = 'Zend_Feed_' . ucfirst(strtolower($format));
        /**
         * @see Zend_Loader
         */
        require_once 'Zend/Loader.php';
        Zend_Loader::loadClass($obj);

        return new $obj(null, null, $builder);
    }
}
