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
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Writer.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer
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
     * PluginLoader instance used by component
     *
     * @var Zend_Loader_PluginLoader_Interface
     */
    protected static $_pluginLoader = null;

    /**
     * Path on which to search for Extension classes
     *
     * @var array
     */
    protected static $_prefixPaths = array();

    /**
     * Array of registered extensions by class postfix (after the base class
     * name) across four categories - data containers and renderers for entry
     * and feed levels.
     *
     * @var array
     */
    protected static $_extensions = array(
        'entry'         => array(),
        'feed'          => array(),
        'entryRenderer' => array(),
        'feedRenderer'  => array(),
    );
    
    /**
     * Set plugin loader for use with Extensions
     *
     * @param  Zend_Loader_PluginLoader_Interface
     */
    public static function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader)
    {
        self::$_pluginLoader = $loader;
    }

    /**
     * Get plugin loader for use with Extensions
     *
     * @return  Zend_Loader_PluginLoader_Interface
     */
    public static function getPluginLoader()
    {
        if (!isset(self::$_pluginLoader)) {
            require_once 'Zend/Loader/PluginLoader.php';
            self::$_pluginLoader = new Zend_Loader_PluginLoader(array(
                'Zend_Feed_Writer_Extension_' => 'Zend/Feed/Writer/Extension/',
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
        $feedRendererName  = $name . '_Renderer_Feed';
        $entryRendererName = $name . '_Renderer_Entry';
        if (self::isRegistered($name)) {
            if (self::getPluginLoader()->isLoaded($feedName)
                || self::getPluginLoader()->isLoaded($entryName)
                || self::getPluginLoader()->isLoaded($feedRendererName)
                || self::getPluginLoader()->isLoaded($entryRendererName)
            ) {
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
        try {
            self::getPluginLoader()->load($feedRendererName);
            self::$_extensions['feedRenderer'][] = $feedRendererName;
        } catch (Zend_Loader_PluginLoader_Exception $e) {
        }
        try {
            self::getPluginLoader()->load($entryRendererName);
            self::$_extensions['entryRenderer'][] = $entryRendererName;
        } catch (Zend_Loader_PluginLoader_Exception $e) {
        }
        if (!self::getPluginLoader()->isLoaded($feedName)
            && !self::getPluginLoader()->isLoaded($entryName)
            && !self::getPluginLoader()->isLoaded($feedRendererName)
            && !self::getPluginLoader()->isLoaded($entryRendererName)
        ) {
            require_once 'Zend/Feed/Exception.php';
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
        $feedRendererName  = $extensionName . '_Renderer_Feed';
        $entryRendererName = $extensionName . '_Renderer_Entry';
        if (in_array($feedName, self::$_extensions['feed'])
            || in_array($entryName, self::$_extensions['entry'])
            || in_array($feedRendererName, self::$_extensions['feedRenderer'])
            || in_array($entryRendererName, self::$_extensions['entryRenderer'])
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
        self::$_pluginLoader = null;
        self::$_prefixPaths  = array();
        self::$_extensions   = array(
            'entry'         => array(),
            'feed'          => array(),
            'entryRenderer' => array(),
            'feedRenderer'  => array(),
        );
    }

    /**
     * Register core (default) extensions
     *
     * @return void
     */
    public static function registerCoreExtensions()
    {
        self::registerExtension('DublinCore');
        self::registerExtension('Content');
        self::registerExtension('Atom');
        self::registerExtension('Slash');
        self::registerExtension('WellFormedWeb');
        self::registerExtension('Threading');
        self::registerExtension('ITunes');
    }
    
    public static function lcfirst($str)
    {
        $str[0] = strtolower($str[0]);
        return $str;
    }

}
