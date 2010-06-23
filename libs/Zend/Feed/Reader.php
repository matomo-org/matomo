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
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Reader.php 22093 2010-05-04 12:55:06Z padraic $
 */

/**
 * @see Zend_Feed
 */
// require_once 'Zend/Feed.php';

/**
 * @see Zend_Feed_Reader_Feed_Rss
 */
// require_once 'Zend/Feed/Reader/Feed/Rss.php';

/**
 * @see Zend_Feed_Reader_Feed_Atom
 */
// require_once 'Zend/Feed/Reader/Feed/Atom.php';

/**
 * @see Zend_Feed_Reader_FeedSet
 */
// require_once 'Zend/Feed/Reader/FeedSet.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Reader
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Reader
{
    /**
     * Namespace constants
     */
    const NAMESPACE_ATOM_03  = 'http://purl.org/atom/ns#';
    const NAMESPACE_ATOM_10  = 'http://www.w3.org/2005/Atom';
    const NAMESPACE_RDF      = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    const NAMESPACE_RSS_090  = 'http://my.netscape.com/rdf/simple/0.9/';
    const NAMESPACE_RSS_10   = 'http://purl.org/rss/1.0/';

    /**
     * Feed type constants
     */
    const TYPE_ANY              = 'any';
    const TYPE_ATOM_03          = 'atom-03';
    const TYPE_ATOM_10          = 'atom-10';
    const TYPE_ATOM_10_ENTRY    = 'atom-10-entry';
    const TYPE_ATOM_ANY         = 'atom';
    const TYPE_RSS_090          = 'rss-090';
    const TYPE_RSS_091          = 'rss-091';
    const TYPE_RSS_091_NETSCAPE = 'rss-091n';
    const TYPE_RSS_091_USERLAND = 'rss-091u';
    const TYPE_RSS_092          = 'rss-092';
    const TYPE_RSS_093          = 'rss-093';
    const TYPE_RSS_094          = 'rss-094';
    const TYPE_RSS_10           = 'rss-10';
    const TYPE_RSS_20           = 'rss-20';
    const TYPE_RSS_ANY          = 'rss';

    /**
     * Cache instance
     *
     * @var Zend_Cache_Core
     */
    protected static $_cache = null;

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

    protected static $_httpConditionalGet = false;

    protected static $_pluginLoader = null;

    protected static $_prefixPaths = array();

    protected static $_extensions = array(
        'feed' => array(
            'DublinCore_Feed',
            'Atom_Feed'
        ),
        'entry' => array(
            'Content_Entry',
            'DublinCore_Entry',
            'Atom_Entry'
        ),
        'core' => array(
            'DublinCore_Feed',
            'Atom_Feed',
            'Content_Entry',
            'DublinCore_Entry',
            'Atom_Entry'
        )
    );

    /**
     * Get the Feed cache
     *
     * @return Zend_Cache_Core
     */
    public static function getCache()
    {
        return self::$_cache;
    }

    /**
     * Set the feed cache
     *
     * @param Zend_Cache_Core $cache
     * @return void
     */
    public static function setCache(Zend_Cache_Core $cache)
    {
        self::$_cache = $cache;
    }

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
            // require_once 'Zend/Http/Client.php';
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
     * Set the flag indicating whether or not to use HTTP conditional GET
     *
     * @param  bool $bool
     * @return void
     */
    public static function useHttpConditionalGet($bool = true)
    {
        self::$_httpConditionalGet = $bool;
    }

    /**
     * Import a feed by providing a URL
     *
     * @param  string $url The URL to the feed
     * @param  string $etag OPTIONAL Last received ETag for this resource
     * @param  string $lastModified OPTIONAL Last-Modified value for this resource
     * @return Zend_Feed_Reader_FeedInterface
     */
    public static function import($uri, $etag = null, $lastModified = null)
    {
        $cache       = self::getCache();
        $feed        = null;
        $responseXml = '';
        $client      = self::getHttpClient();
        $client->resetParameters();
        $client->setHeaders('If-None-Match', null);
        $client->setHeaders('If-Modified-Since', null);
        $client->setUri($uri);
        $cacheId = 'Zend_Feed_Reader_' . md5($uri);

        if (self::$_httpConditionalGet && $cache) {
            $data = $cache->load($cacheId);
            if ($data) {
                if (is_null($etag)) {
                    $etag = $cache->load($cacheId.'_etag');
                }
                if (is_null($lastModified)) {
                    $lastModified = $cache->load($cacheId.'_lastmodified');;
                }
                if ($etag) {
                    $client->setHeaders('If-None-Match', $etag);
                }
                if ($lastModified) {
                    $client->setHeaders('If-Modified-Since', $lastModified);
                }
            }
            $response = $client->request('GET');
            if ($response->getStatus() !== 200 && $response->getStatus() !== 304) {
                // require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Feed failed to load, got response code ' . $response->getStatus());
            }
            if ($response->getStatus() == 304) {
                $responseXml = $data;
            } else {
                $responseXml = $response->getBody();
                $cache->save($responseXml, $cacheId);
                if ($response->getHeader('ETag')) {
                    $cache->save($response->getHeader('ETag'), $cacheId.'_etag');
                }
                if ($response->getHeader('Last-Modified')) {
                    $cache->save($response->getHeader('Last-Modified'), $cacheId.'_lastmodified');
                }
            }
            return self::importString($responseXml);
        } elseif ($cache) {
            $data = $cache->load($cacheId);
            if ($data !== false) {
                return self::importString($data);
            }
            $response = $client->request('GET');
            if ($response->getStatus() !== 200) {
                // require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Feed failed to load, got response code ' . $response->getStatus());
            }
            $responseXml = $response->getBody();
            $cache->save($responseXml, $cacheId);
            return self::importString($responseXml);
        } else {
            $response = $client->request('GET');
            if ($response->getStatus() !== 200) {
                // require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Feed failed to load, got response code ' . $response->getStatus());
            }
            $reader = self::importString($response->getBody());
            $reader->setOriginalSourceUri($uri);
            return $reader;
        }
    }

    /**
     * Import a feed by providing a Zend_Feed_Abstract object
     *
     * @param  Zend_Feed_Abstract $feed A fully instantiated Zend_Feed object
     * @return Zend_Feed_Reader_FeedInterface
     */
    public static function importFeed(Zend_Feed_Abstract $feed)
    {
        $dom  = $feed->getDOM()->ownerDocument;
        $type = self::detectType($dom);
        self::_registerCoreExtensions();
        if (substr($type, 0, 3) == 'rss') {
            $reader = new Zend_Feed_Reader_Feed_Rss($dom, $type);
        } else {
            $reader = new Zend_Feed_Reader_Feed_Atom($dom, $type);
        }

        return $reader;
    }

    /**
     * Import a feed froma string
     *
     * @param  string $string
     * @return Zend_Feed_Reader_FeedInterface
     */
    public static function importString($string)
    {
        $libxml_errflag = libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $status = $dom->loadXML($string);
        libxml_use_internal_errors($libxml_errflag);

        if (!$status) {
            // Build error message
            $error = libxml_get_last_error();
            if ($error && $error->message) {
                $errormsg = "DOMDocument cannot parse XML: {$error->message}";
            } else {
                $errormsg = "DOMDocument cannot parse XML: Please check the XML document's validity";
            }

            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception($errormsg);
        }

        $type = self::detectType($dom);

        self::_registerCoreExtensions();

        if (substr($type, 0, 3) == 'rss') {
            $reader = new Zend_Feed_Reader_Feed_Rss($dom, $type);
        } elseif (substr($type, 8, 5) == 'entry') {
            $reader = new Zend_Feed_Reader_Entry_Atom($dom->documentElement, 0, Zend_Feed_Reader::TYPE_ATOM_10);
        } elseif (substr($type, 0, 4) == 'atom') {
            $reader = new Zend_Feed_Reader_Feed_Atom($dom, $type);
        } else {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('The URI used does not point to a '
            . 'valid Atom, RSS or RDF feed that Zend_Feed_Reader can parse.');
        }
        return $reader;
    }

    /**
     * Imports a feed from a file located at $filename.
     *
     * @param  string $filename
     * @throws Zend_Feed_Exception
     * @return Zend_Feed_Reader_FeedInterface
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
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception("File could not be loaded: $php_errormsg");
        }
        return self::importString($feed);
    }

    public static function findFeedLinks($uri)
    {
        // Get the HTTP response from $uri and save the contents
        $client = self::getHttpClient();
        $client->setUri($uri);
        $response = $client->request();
        if ($response->getStatus() !== 200) {
            /**
             * @see Zend_Feed_Exception
             */
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception("Failed to access $uri, got response code " . $response->getStatus());
        }
        $responseHtml = $response->getBody();
        $libxml_errflag = libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $status = $dom->loadHTML($responseHtml);
        libxml_use_internal_errors($libxml_errflag);
        if (!$status) {
            // Build error message
            $error = libxml_get_last_error();
            if ($error && $error->message) {
                $errormsg = "DOMDocument cannot parse HTML: {$error->message}";
            } else {
                $errormsg = "DOMDocument cannot parse HTML: Please check the XML document's validity";
            }

            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception($errormsg);
        }
        $feedSet = new Zend_Feed_Reader_FeedSet;
        $links = $dom->getElementsByTagName('link');
        $feedSet->addLinks($links, $uri);
        return $feedSet;
    }

    /**
     * Detect the feed type of the provided feed
     *
     * @param  Zend_Feed_Abstract|DOMDocument|string $feed
     * @return string
     */
    public static function detectType($feed, $specOnly = false)
    {
        if ($feed instanceof Zend_Feed_Reader_FeedInterface) {
            $dom = $feed->getDomDocument();
        } elseif($feed instanceof DOMDocument) {
            $dom = $feed;
        } elseif(is_string($feed) && !empty($feed)) {
            @ini_set('track_errors', 1);
            $dom = new DOMDocument;
            $status = @$dom->loadXML($feed);
            @ini_restore('track_errors');
            if (!$status) {
                if (!isset($php_errormsg)) {
                    if (function_exists('xdebug_is_enabled')) {
                        $php_errormsg = '(error message not available, when XDebug is running)';
                    } else {
                        $php_errormsg = '(error message not available)';
                    }
                }
                // require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception("DOMDocument cannot parse XML: $php_errormsg");
            }
        } else {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid object/scalar provided: must'
            . ' be of type Zend_Feed_Reader_FeedInterface, DomDocument or string');
        }
        $xpath = new DOMXPath($dom);

        if ($xpath->query('/rss')->length) {
            $type = self::TYPE_RSS_ANY;
            $version = $xpath->evaluate('string(/rss/@version)');

            if (strlen($version) > 0) {
                switch($version) {
                    case '2.0':
                        $type = self::TYPE_RSS_20;
                        break;

                    case '0.94':
                        $type = self::TYPE_RSS_094;
                        break;

                    case '0.93':
                        $type = self::TYPE_RSS_093;
                        break;

                    case '0.92':
                        $type = self::TYPE_RSS_092;
                        break;

                    case '0.91':
                        $type = self::TYPE_RSS_091;
                        break;
                }
            }

            return $type;
        }

        $xpath->registerNamespace('rdf', self::NAMESPACE_RDF);

        if ($xpath->query('/rdf:RDF')->length) {
            $xpath->registerNamespace('rss', self::NAMESPACE_RSS_10);

            if ($xpath->query('/rdf:RDF/rss:channel')->length
                || $xpath->query('/rdf:RDF/rss:image')->length
                || $xpath->query('/rdf:RDF/rss:item')->length
                || $xpath->query('/rdf:RDF/rss:textinput')->length
            ) {
                return self::TYPE_RSS_10;
            }

            $xpath->registerNamespace('rss', self::NAMESPACE_RSS_090);

            if ($xpath->query('/rdf:RDF/rss:channel')->length
                || $xpath->query('/rdf:RDF/rss:image')->length
                || $xpath->query('/rdf:RDF/rss:item')->length
                || $xpath->query('/rdf:RDF/rss:textinput')->length
            ) {
                return self::TYPE_RSS_090;
            }
        }

        $type = self::TYPE_ATOM_ANY;
        $xpath->registerNamespace('atom', self::NAMESPACE_ATOM_10);

        if ($xpath->query('//atom:feed')->length) {
            return self::TYPE_ATOM_10;
        }
        
        if ($xpath->query('//atom:entry')->length) {
            if ($specOnly == true) {
                return self::TYPE_ATOM_10;
            } else {
                return self::TYPE_ATOM_10_ENTRY;
            }
        }

        $xpath->registerNamespace('atom', self::NAMESPACE_ATOM_03);

        if ($xpath->query('//atom:feed')->length) {
            return self::TYPE_ATOM_03;
        }

        return self::TYPE_ANY;
    }

    /**
     * Set plugin loader for use with Extensions
     *
     * @param  Zend_Loader_PluginLoader_Interface $loader
     */
    public static function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader)
    {
        self::$_pluginLoader = $loader;
    }

    /**
     * Get plugin loader for use with Extensions
     *
     * @return  Zend_Loader_PluginLoader_Interface $loader
     */
    public static function getPluginLoader()
    {
        if (!isset(self::$_pluginLoader)) {
            // require_once 'Zend/Loader/PluginLoader.php';
            self::$_pluginLoader = new Zend_Loader_PluginLoader(array(
                'Zend_Feed_Reader_Extension_' => 'Zend/Feed/Reader/Extension/',
            ));
        }
        return self::$_pluginLoader;
    }

    /**
     * Add prefix path for loading Extensions
     *
     * @param  string $prefix
     * @param  string $path
     * @return void
     */
    public static function addPrefixPath($prefix, $path)
    {
        $prefix = rtrim($prefix, '_');
        $path   = rtrim($path, DIRECTORY_SEPARATOR);
        self::getPluginLoader()->addPrefixPath($prefix, $path);
    }

    /**
     * Add multiple Extension prefix paths at once
     *
     * @param  array $spec
     * @return void
     */
    public static function addPrefixPaths(array $spec)
    {
        if (isset($spec['prefix']) && isset($spec['path'])) {
            self::addPrefixPath($spec['prefix'], $spec['path']);
        }
        foreach ($spec as $prefixPath) {
            if (isset($prefixPath['prefix']) && isset($prefixPath['path'])) {
                self::addPrefixPath($prefixPath['prefix'], $prefixPath['path']);
            }
        }
    }

    /**
     * Register an Extension by name
     *
     * @param  string $name
     * @return void
     * @throws Zend_Feed_Exception if unable to resolve Extension class
     */
    public static function registerExtension($name)
    {
        $feedName  = $name . '_Feed';
        $entryName = $name . '_Entry';
        if (self::isRegistered($name)) {
            if (self::getPluginLoader()->isLoaded($feedName) ||
                self::getPluginLoader()->isLoaded($entryName)) {
                return;
            }
        }
        try {
            self::getPluginLoader()->load($feedName);
            self::$_extensions['feed'][] = $feedName;
        } catch (Zend_Loader_PluginLoader_Exception $e) {
        }
        try {
            self::getPluginLoader()->load($entryName);
            self::$_extensions['entry'][] = $entryName;
        } catch (Zend_Loader_PluginLoader_Exception $e) {
        }
        if (!self::getPluginLoader()->isLoaded($feedName)
            && !self::getPluginLoader()->isLoaded($entryName)
        ) {
            // require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Could not load extension: ' . $name
                . 'using Plugin Loader. Check prefix paths are configured and extension exists.');
        }
    }

    /**
     * Is a given named Extension registered?
     *
     * @param  string $extensionName
     * @return boolean
     */
    public static function isRegistered($extensionName)
    {
        $feedName  = $extensionName . '_Feed';
        $entryName = $extensionName . '_Entry';
        if (in_array($feedName, self::$_extensions['feed'])
            || in_array($entryName, self::$_extensions['entry'])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get a list of extensions
     *
     * @return array
     */
    public static function getExtensions()
    {
        return self::$_extensions;
    }

    /**
     * Reset class state to defaults
     *
     * @return void
     */
    public static function reset()
    {
        self::$_cache              = null;
        self::$_httpClient         = null;
        self::$_httpMethodOverride = false;
        self::$_httpConditionalGet = false;
        self::$_pluginLoader       = null;
        self::$_prefixPaths        = array();
        self::$_extensions         = array(
            'feed' => array(
                'DublinCore_Feed',
                'Atom_Feed'
            ),
            'entry' => array(
                'Content_Entry',
                'DublinCore_Entry',
                'Atom_Entry'
            ),
            'core' => array(
                'DublinCore_Feed',
                'Atom_Feed',
                'Content_Entry',
                'DublinCore_Entry',
                'Atom_Entry'
            )
        );
    }

    /**
     * Register core (default) extensions
     *
     * @return void
     */
    protected static function _registerCoreExtensions()
    {
        self::registerExtension('DublinCore');
        self::registerExtension('Content');
        self::registerExtension('Atom');
        self::registerExtension('Slash');
        self::registerExtension('WellFormedWeb');
        self::registerExtension('Thread');
        self::registerExtension('Podcast');
    }
    
    /**
     * Utility method to apply array_unique operation to a multidimensional
     * array.
     *
     * @param array
     * @return array
     */
    public static function arrayUnique(array $array)
    {
        foreach ($array as &$value) {
            $value = serialize($value);
        }
        $array = array_unique($array);
        foreach ($array as &$value) {
            $value = unserialize($value);
        }
        return $array;
    }
 
}
