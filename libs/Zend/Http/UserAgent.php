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
 * @package    Zend_Http_UserAgent
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Lists of User Agent chains for testing :
 *
 * - http://www.useragentstring.com/layout/useragentstring.php
 * - http://user-agent-string.info/list-of-ua
 * - http://www.user-agents.org/allagents.xml
 * - http://en.wikipedia.org/wiki/List_of_user_agents_for_mobile_phones
 * - http://www.mobilemultimedia.be/fr/
 *
 * @category   Zend
 * @package    Zend_Http_UserAgent
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_UserAgent implements Serializable
{
    /**
     * 'desktop' by default if the sequence return false for each item or is empty
     */
    const DEFAULT_IDENTIFICATION_SEQUENCE = 'mobile,desktop';

    /**
     * Default persitent storage adapter : Session or NonPersitent
     */
    const DEFAULT_PERSISTENT_STORAGE_ADAPTER = 'Session';

    /**
     * 'desktop' by default if the sequence return false for each item
     */
    const DEFAULT_BROWSER_TYPE = 'desktop';

    /**
     * Default User Agent chain to prevent empty value
     */
    const DEFAULT_HTTP_USER_AGENT = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)';

    /**
     * Default Http Accept param to prevent empty value
     */
    const DEFAULT_HTTP_ACCEPT = "application/xhtml+xml";

    /**
     * Default markup language
     */
    const DEFAULT_MARKUP_LANGUAGE = "xhtml";

    /**
     * Browser type
     *
     * @var string
     */
    protected $_browserType;

    /**
     * Browser type class
     *
     * Map of browser types to classes.
     *
     * @var array
     */
    protected $_browserTypeClass = array();

    /**
     * Array to store config
     *
     * Default values are provided to ensure specific keys are present at
     * instantiation.
     *
     * @var array
     */
    protected $_config = array(
        'identification_sequence' => self::DEFAULT_IDENTIFICATION_SEQUENCE,
        'storage'                 => array(
            'adapter'             => self::DEFAULT_PERSISTENT_STORAGE_ADAPTER,
        ),
    );

    /**
     * Identified device
     *
     * @var Zend_Http_UserAgent_Device
     */
    protected $_device;

    /**
     * Whether or not this instance is immutable.
     *
     * If true, none of the following may be modified:
     * - $_server
     * - $_browserType
     * - User-Agent (defined in $_server)
     * - HTTP Accept value (defined in $_server)
     * - $_storage
     *
     * @var bool
     */
    protected $_immutable = false;

    /**
     * Plugin loaders
     * @var array
     */
    protected $_loaders = array();

    /**
     * Valid plugin loader types
     * @var array
     */
    protected $_loaderTypes = array('storage', 'device');

    /**
     * Trace of items matched to identify the browser type
     *
     * @var array
     */
    protected $_matchLog = array();

    /**
     * Server variable
     *
     * @var array
     */
    protected $_server;

    /**
     * Persistent storage handler
     *
     * @var Zend_Http_UserAgent_Storage
     */
    protected $_storage;

    /**
     * Constructor
     *
     * @param  null|array|Zend_Config|ArrayAccess $options
     * @return void
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Serialized representation of the object
     *
     * @return string
     */
    public function serialize()
    {
        $device = $this->getDevice();
        $spec = array(
            'browser_type' => $this->_browserType,
            'config'       => $this->_config,
            'device_class' => get_class($device),
            'device'       => $device->serialize(),
            'user_agent'   => $this->getServerValue('http_user_agent'),
            'http_accept'  => $this->getServerValue('http_accept'),
        );
        return serialize($spec);
    }

    /**
     * Unserialize a previous representation of the object
     *
     * @param  string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        $spec = unserialize($serialized);

        $this->setOptions($spec);

        // Determine device class and ensure the class is loaded
        $deviceClass          = $spec['device_class'];
        if (!class_exists($deviceClass)) {
            $this->_getUserAgentDevice($this->getBrowserType());
        }

        // Get device specification and instantiate
        $deviceSpec            = unserialize($spec['device']);
        $deviceSpec['_config'] = $this->getConfig();
        $deviceSpec['_server'] = $this->getServer();
        $this->_device = new $deviceClass($deviceSpec);
    }

    /**
     * Configure instance
     *
     * @param  array|Zend_Config|ArrayAccess $options
     * @return Zend_Http_UserAgent
     */
    public function setOptions($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options)
            && !$options instanceof ArrayAccess
            && !$options instanceof Traversable
        ) {
            // require_once 'Zend/Http/UserAgent/Exception.php';
            throw new Zend_Http_UserAgent_Exception(sprintf(
                'Invalid argument; expected array, Zend_Config object, or object implementing ArrayAccess and Traversable; received %s',
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        // Set $_SERVER first
        if (isset($options['server'])) {
            $this->setServer($options['server']);
            unset($options['server']);
        }

        // Get plugin loaders sorted
        if (isset($options['plugin_loader'])) {
            $plConfig = $options['plugin_loader'];
            if (is_array($plConfig) || $plConfig instanceof Traversable) {
                foreach ($plConfig as $type => $class) {
                    $this->setPluginLoader($type, $class);
                }
            }
            unset($plConfig, $options['plugin_loader']);
        }

        // And then loop through the remaining options
        $config = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'browser_type':
                    $this->setBrowserType($value);
                    break;
                case 'http_accept':
                    $this->setHttpAccept($value);
                    break;
                case 'user_agent':
                    $this->setUserAgent($value);
                    break;
                default:
                    // Cache remaining options for $_config
                    $config[$key] = $value;
                    break;
            }
        }
        $this->setConfig($config);

        return $this;
    }

    /**
     * Comparison of the UserAgent chain and browser signatures.
     *
     * The comparison is case-insensitive : the browser signatures must be in lower
     * case
     *
     * @param  string $deviceClass Name of class against which a match will be attempted
     * @return bool
     */
    protected function _match($deviceClass)
    {
        // Validate device class
        $r = new ReflectionClass($deviceClass);
        if (!$r->implementsInterface('Zend_Http_UserAgent_Device')) {
            throw new Zend_Http_UserAgent_Exception(sprintf(
                'Invalid device class provided ("%s"); must implement Zend_Http_UserAgent_Device',
                $deviceClass
            ));
        }

        $userAgent = $this->getUserAgent();

        // Call match method on device class
        return call_user_func(
            array($deviceClass, 'match'),
            $userAgent,
            $this->getServer()
        );
    }

    /**
     * Loads class for a user agent device
     *
     * @param  string $browserType Browser type
     * @return string
     * @throws Zend_Loader_PluginLoader_Exception if unable to load UA device
     */
    protected function _getUserAgentDevice($browserType)
    {
        $browserType = strtolower($browserType);
        if (isset($this->_browserTypeClass[$browserType])) {
            return $this->_browserTypeClass[$browserType];
        }

        if (isset($this->_config[$browserType])
            && isset($this->_config[$browserType]['device'])
        ) {
            $deviceConfig = $this->_config[$browserType]['device'];
            if (is_array($deviceConfig) && isset($deviceConfig['classname'])) {
                $device = (string) $deviceConfig['classname'];
                if (!class_exists($device)) {
                    // require_once 'Zend/Http/UserAgent/Exception.php';
                    throw new Zend_Http_UserAgent_Exception(sprintf(
                        'Invalid classname "%s" provided in device configuration for browser type "%s"',
                        $device,
                        $browserType
                    ));
                }
            } elseif (is_array($deviceConfig) && isset($deviceConfig['path'])) {
                $loader = $this->getPluginLoader('device');
                $path   = $deviceConfig['path'];
                $prefix = isset($deviceConfig['prefix']) ? $deviceConfig['prefix'] : 'Zend_Http_UserAgent';
                $loader->addPrefixPath($prefix, $path);

                $device = $loader->load($browserType);
            } else {
                $loader = $this->getPluginLoader('device');
                $device = $loader->load($browserType);
            }
        } else {
            $loader = $this->getPluginLoader('device');
            $device = $loader->load($browserType);
        }

        $this->_browserTypeClass[$browserType] = $device;

        return $device;
    }

    /**
     * Returns the User Agent value
     *
     * If $userAgent param is null, the value of $_server['HTTP_USER_AGENT'] is
     * returned.
     *
     * @return string
     */
    public function getUserAgent()
    {
        if (null === ($ua = $this->getServerValue('http_user_agent'))) {
            $ua = self::DEFAULT_HTTP_USER_AGENT;
            $this->setUserAgent($ua);
        }

        return $ua;
    }

    /**
     * Force or replace the UA chain in $_server variable
     *
     * @param  string $userAgent Forced UserAgent chain
     * @return Zend_Http_UserAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->setServerValue('http_user_agent', $userAgent);
        return $this;
    }

    /**
     * Returns the HTTP Accept server param
     *
     * @param  string $httpAccept (option) forced HTTP Accept chain
     * @return string
     */
    public function getHttpAccept($httpAccept = null)
    {
        if (null === ($accept = $this->getServerValue('http_accept'))) {
            $accept = self::DEFAULT_HTTP_ACCEPT;
            $this->setHttpAccept($accept);
        }
        return $accept;
    }

    /**
     * Force or replace the HTTP_ACCEPT chain in self::$_server variable
     *
     * @param  string $httpAccept Forced HTTP Accept chain
     * @return Zend_Http_UserAgent
     */
    public function setHttpAccept($httpAccept)
    {
        $this->setServerValue('http_accept', $httpAccept);
        return $this;
    }

    /**
     * Returns the persistent storage handler
     *
     * Session storage is used by default unless a different storage adapter
     * has been set via the "persistent_storage_adapter" key. That key should
     * contain either a fully qualified class name, or a short name that
     * resolves via the plugin loader.
     *
     * @param  string $browser Browser identifier (User Agent chain)
     * @return Zend_Http_UserAgent_Storage
     */
    public function getStorage($browser = null)
    {
        if (null === $browser) {
            $browser = $this->getUserAgent();
        }
        if (null === $this->_storage) {
            $config  = $this->_config['storage'];
            $adapter = $config['adapter'];
            if (!class_exists($adapter)) {
                $loader = $this->getPluginLoader('storage');
                $adapter = $loader->load($adapter);
                $loader = $this->getPluginLoader('storage');
            }
            $options = array('browser_type' => $browser);
            if (isset($config['options'])) {
                $options = array_merge($options, $config['options']);
            }
            $this->setStorage(new $adapter($options));
        }
        return $this->_storage;
    }

    /**
     * Sets the persistent storage handler
     *
     * @param  Zend_Http_UserAgent_Storage $storage
     * @return Zend_Http_UserAgent
     */
    public function setStorage(Zend_Http_UserAgent_Storage $storage)
    {
        if ($this->_immutable) {
            // require_once 'Zend/Http/UserAgent/Exception.php';
            throw new Zend_Http_UserAgent_Exception(
                'The User-Agent device object has already been retrieved; the storage object is now immutable'
            );
        }

        $this->_storage = $storage;
        return $this;
    }

    /**
     * Clean the persistent storage
     *
     * @param  string $browser Browser identifier (User Agent chain)
     * @return void
     */
    public function clearStorage($browser = null)
    {
        $this->getStorage($browser)->clear();
    }

    /**
     * Get user configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Config parameters is an Array or a Zend_Config object
     *
     * The allowed parameters are :
     * - the identification sequence (can be empty) => desktop browser type is the
     * default browser type returned
     * $config['identification_sequence'] : ',' separated browser types
     * - the persistent storage adapter
     * $config['persistent_storage_adapter'] = "Session" or "NonPersistent"
     * - to add or replace a browser type device
     * $config[(type)]['device']['path']
     * $config[(type)]['device']['classname']
     * - to add or replace a browser type features adapter
     * $config[(type)]['features']['path']
     * $config[(type)]['features']['classname']
     *
     * @param  mixed $config (option) Config array
     * @return Zend_Http_UserAgent
     */
    public function setConfig($config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        // Verify that Config parameters are in an array.
        if (!is_array($config) && !$config instanceof Traversable) {
            // require_once 'Zend/Http/UserAgent/Exception.php';
            throw new Zend_Http_UserAgent_Exception(sprintf(
                'Config parameters must be in an array or a Traversable object; received "%s"',
                (is_object($config) ? get_class($config) : gettype($config))
            ));
        }

        if ($config instanceof Traversable) {
            $tmp = array();
            foreach ($config as $key => $value) {
                $tmp[$key] = $value;
            }
            $config = $tmp;
            unset($tmp);
        }

        $this->_config = array_merge($this->_config, $config);
        return $this;
    }

    /**
     * Returns the device object
     *
     * This is the object that will contain the various discovered device
     * capabilities.
     *
     * @return Zend_Http_UserAgent_Device $device
     */
    public function getDevice()
    {
        if (null !== $this->_device) {
            return $this->_device;
        }

        $userAgent = $this->getUserAgent();

        // search an existing identification in the session
        $storage = $this->getStorage($userAgent);

        if (!$storage->isEmpty()) {
            // If the user agent and features are already existing, the
            // Zend_Http_UserAgent object is serialized in the session
            $object = $storage->read();
            $this->unserialize($object);
        } else {
            // Otherwise, the identification is made and stored in the session.
            // Find the browser type:
            $this->setBrowserType($this->_matchUserAgent());
            $this->_createDevice();

            // put the result in storage:
            $this->getStorage($userAgent)
                 ->write($this->serialize());
        }

        // Mark the object as immutable
        $this->_immutable = true;

        // Return the device instance
        return $this->_device;
    }

    /**
     * Retrieve the browser type
     *
     * @return string $browserType
     */
    public function getBrowserType()
    {
        return $this->_browserType;
    }

    /**
     * Set the browser "type"
     *
     * @param string $browserType
     * @return Zend_Http_UserAgent
     */
    public function setBrowserType($browserType)
    {
        if ($this->_immutable) {
            // require_once 'Zend/Http/UserAgent/Exception.php';
            throw new Zend_Http_UserAgent_Exception(
                'The User-Agent device object has already been retrieved; the browser type is now immutable'
            );
        }

        $this->_browserType = $browserType;
        return $this;
    }

    /**
     * Retrieve the "$_SERVER" array
     *
     * Basically, the $_SERVER array or an equivalent container storing the
     * data that will be introspected.
     *
     * If the value has not been previously set, it sets itself from the
     * $_SERVER superglobal.
     *
     * @return array
     */
    public function getServer()
    {
        if (null === $this->_server) {
            $this->setServer($_SERVER);
        }
        return $this->_server;
    }

    /**
     * Set the "$_SERVER" array
     *
     * Basically, the $_SERVER array or an equivalent container storing the
     * data that will be introspected.
     *
     * @param  array|ArrayAccess $server
     * @return void
     * @throws Zend_Http_UserAgent_Exception on invalid parameter
     */
    public function setServer($server)
    {
        if ($this->_immutable) {
            // require_once 'Zend/Http/UserAgent/Exception.php';
            throw new Zend_Http_UserAgent_Exception(
                'The User-Agent device object has already been retrieved; the server array is now immutable'
            );
        }

        if (!is_array($server) && !$server instanceof Traversable) {
            // require_once 'Zend/Http/UserAgent/Exception.php';
            throw new Zend_Http_UserAgent_Exception(sprintf(
                'Expected an array or object implementing Traversable; received %s',
                (is_object($server) ? get_class($server) : gettype($server))
            ));
        }

        // Get an array if we don't have one
        if ($server instanceof ArrayObject) {
            $server = $server->getArrayCopy();
        } elseif ($server instanceof Traversable) {
            $tmp = array();
            foreach ($server as $key => $value) {
                $tmp[$key] = $value;
            }
            $server = $tmp;
            unset($tmp);
        }

        // Normalize key case
        $server = array_change_key_case($server, CASE_LOWER);

        $this->_server = $server;
        return $this;
    }

    /**
     * Retrieve a server value
     *
     * @param  string $key
     * @return mixed
     */
    public function getServerValue($key)
    {
        $key    = strtolower($key);
        $server = $this->getServer();
        $return = null;
        if (isset($server[$key])) {
            $return = $server[$key];
        }
        unset($server);
        return $return;
    }

    /**
     * Set a server value
     *
     * @param  string|int|float $key
     * @param  mixed $value
     * @return void
     */
    public function setServerValue($key, $value)
    {
        if ($this->_immutable) {
            // require_once 'Zend/Http/UserAgent/Exception.php';
            throw new Zend_Http_UserAgent_Exception(
                'The User-Agent device object has already been retrieved; the server array is now immutable'
            );
        }

        $server = $this->getServer(); // ensure it's been initialized
        $key    = strtolower($key);
        $this->_server[$key] = $value;
        return $this;
    }

    /**
     * Set plugin loader
     *
     * @param  string $type Type of plugin loader; one of 'storage', (?)
     * @param  string|Zend_Loader_PluginLoader $loader
     * @return Zend_Http_UserAgent
     */
    public function setPluginLoader($type, $loader)
    {
        $type       = $this->_validateLoaderType($type);

        if (is_string($loader)) {
            if (!class_exists($loader)) {
                // require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($loader);
            }
            $loader = new $loader();
        } elseif (!is_object($loader)) {
            // require_once 'Zend/Http/UserAgent/Exception.php';
            throw new Zend_Http_UserAgent_Exception(sprintf(
                'Expected a plugin loader class or object; received %s',
                gettype($loader)
            ));
        }
        if (!$loader instanceof Zend_Loader_PluginLoader) {
            // require_once 'Zend/Http/UserAgent/Exception.php';
            throw new Zend_Http_UserAgent_Exception(sprintf(
                'Expected an object extending Zend_Loader_PluginLoader; received %s',
                get_class($loader)
            ));
        }

        $basePrefix = 'Zend_Http_UserAgent_';
        $basePath   = 'Zend/Http/UserAgent/';
        switch ($type) {
            case 'storage':
                $prefix = $basePrefix . 'Storage';
                $path   = $basePath   . 'Storage';
                break;
            case 'device':
                $prefix = $basePrefix;
                $path   = $basePath;
                break;
        }
        $loader->addPrefixPath($prefix, $path);
        $this->_loaders[$type] = $loader;
        return $this;
    }

    /**
     * Get a plugin loader
     *
     * @param  string $type A valid plugin loader type; see {@link $_loaderTypes}
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader($type)
    {
        $type = $this->_validateLoaderType($type);
        if (!isset($this->_loaders[$type])) {
            // require_once 'Zend/Loader/PluginLoader.php';
            $this->setPluginLoader($type, new Zend_Loader_PluginLoader());
        }
        return $this->_loaders[$type];
    }

    /**
     * Validate a plugin loader type
     *
     * Verifies that it is in {@link $_loaderTypes}, and returns a normalized
     * version of the type.
     *
     * @param  string $type
     * @return string
     * @throws Zend_Http_UserAgent_Exception on invalid type
     */
    protected function _validateLoaderType($type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->_loaderTypes)) {
            $types = implode(', ', $this->_loaderTypes);

            // require_once 'Zend/Http/UserAgent/Exception.php';
            throw new Zend_Http_UserAgent_Exception(sprintf(
                'Expected one of "%s" for plugin loader type; received "%s"',
                $types,
                (string) $type
            ));
        }
        return $type;
    }

    /**
     * Run the identification sequence to match the right browser type according to the
     * user agent
     *
     * @return Zend_Http_UserAgent_Result
     */
    protected function _matchUserAgent()
    {
        $type = self::DEFAULT_BROWSER_TYPE;

        // If we have no identification sequence, just return the default type
        if (empty($this->_config['identification_sequence'])) {
            return $type;
        }

        // Get sequence against which to match
        $sequence = explode(',', $this->_config['identification_sequence']);

        // If a browser type is already configured, push that to the front of the list
        if (null !== ($browserType = $this->getBrowserType())) {
            array_unshift($sequence, $browserType);
        }

        // Append the default browser type to the list if not alread in the list
        if (!in_array($type, $sequence)) {
            $sequence[] = $type;
        }

        // Test each type until we find a match
        foreach ($sequence as $browserType) {
            $browserType = trim($browserType);
            $className   = $this->_getUserAgentDevice($browserType);

            // Attempt to match this device class
            if ($this->_match($className)) {
                $type = $browserType;
                $this->_browserTypeClass[$type] = $className;
                break;
            }
        }

        return $type;
    }

    /**
     * Creates device object instance
     *
     * @return void
     */
    protected function _createDevice()
    {
        $browserType = $this->getBrowserType();
        $classname   = $this->_getUserAgentDevice($browserType);
        $this->_device = new $classname($this->getUserAgent(), $this->getServer(), $this->getConfig());
    }
}
