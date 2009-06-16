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
 * @package    Zend_Cache
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * @package    Zend_Cache
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cache_Core 
{

    // ------------------
    // --- Properties ---
    // ------------------

    /**
     * Backend Object
     * 
     * @var object
     */
    private $_backend = null;

    /**
     * Available options
     * 
     * ====> (boolean) write_control :  
     * - Enable / disable write control (the cache is read just after writing to detect corrupt entries)
     * - Enable write control will lightly slow the cache writing but not the cache reading
     * Write control can detect some corrupt cache files but maybe it's not a perfect control
     *
     * ====> (boolean) caching :
     * - Enable / disable caching
     * (can be very usefull for the debug of cached scripts)
     * 
     * ====> (boolean) automatic_serialization :
     * - Enable / disable automatic serialization
     * - It can be used to save directly datas which aren't strings (but it's slower)
     * 
     * ====> (int) automatic_cleaning_factor :
     * - Disable / Tune the automatic cleaning process
     * - The automatic cleaning process destroy too old (for the given life time)
     *   cache files when a new cache file is written :
     *     0               => no automatic cache cleaning
     *     1               => systematic cache cleaning
     *     x (integer) > 1 => automatic cleaning randomly 1 times on x cache write    
     *
     * ====> (int) lifetime :
     * - Cache lifetime (in seconds)
     * - If null, the cache is valid forever.
     * 
     * ====> (boolean) logging :
     * - If set to true, logging is activated (but the system is slower)
     * 
     * @var array available options
     */
    protected $_options = array(
        'write_control'             => true, 
        'caching'                   => true, 
        'automatic_serialization'   => false,
        'automatic_cleaning_factor' => 10,
        'lifetime'                  => 3600,
        'logging'                   => false,
        'logger'                    => null
    ); 

    /**
     * Array of options which have to be transfered to backend
     */
    protected static $_directivesList = array('lifetime', 'logging', 'logger');

    /**
     * Not used for the core, just a sort a hint to get a common setOption() method (for the core and for frontends)
     */
    protected $_specificOptions = array();

    /**
     * Last used cache id
     * 
     * @var string $_lastId
     */
    private $_lastId = null;
    
    /**
     * backward compatibility becase of ZF-879 and ZF-1172 (it will be removed in ZF 1.1)
     *
     * @var array 
     */
    protected $_backwardCompatibilityArray = array(
        'lifeTime' => 'lifetime',
        'writeControl' => 'write_control',
        'automaticSerialization' => 'automatic_serialization',
        'automaticCleaningFactor' => 'automatic_cleaning_factor',
        'cachedEntity' => 'cached_entity',
        'cacheByDefault' => 'cache_by_default',
        'cachedMethods' => 'cached_methods',
        'nonCachedMethods' => 'non_cached_methods',
        'cachedFunctions' => 'cached_functions',
        'nonCachedFunctions' => 'non_cached_functions',
        'masterFile' => 'master_file',
        'httpConditional' => 'http_conditional',
        'debugHeader' => 'debug_header',
        'defaultOptions' => 'default_options',
        'cacheWithGetVariables' => 'cache_with_get_variables',
        'cacheWithPostVariables' => 'cache_with_post_variables',
        'cacheWithSessionVariables' => 'cache_with_session_variables',
        'cacheWithFilesVariables' => 'cache_with_files_variables',
        'cacheWithCookieVariables' => 'cache_with_cookie_variables',
        'makeIdWithGetVariables' => 'make_id_with_get_variables',
        'makeIdWithPostVariables' => 'make_id_with_post_variables',
        'makeIdWithSessionVariables' => 'make_id_with_session_variables',
        'makeIdWithFilesVariables' => 'make_id_with_files_variables',
        'makeIdWithCookieVariables' => 'make_id_with_cookie_variables'
    );

    // ----------------------
    // --- Public methods ---
    // ----------------------

    /**
     * Constructor
     * 
     * @param array $options associative array of options
     */
    public function __construct($options = array())
    {
        if (!is_array($options)) {
            Zend_Cache::throwException('Options parameter must be an array');
        }  
        while (list($name, $value) = each($options)) {
            $this->setOption($name, $value);
        }
        $this->_loggerSanity();
    }

    /**
     * Set the backend 
     * 
     * @param object $backendObject
     */
    public function setBackend($backendObject)
    {
        if (!is_object($backendObject)) {
            Zend_Cache::throwException('Incorrect backend object !');
        }
        $this->_backend= $backendObject;
        // some options (listed in $_directivesList) have to be given
        // to the backend too (even if they are not "backend specific") 
        $directives = array();
        foreach (Zend_Cache_Core::$_directivesList as $directive) {
            $directives[$directive] = $this->_options[$directive];
        }
        $this->_backend->setDirectives($directives);
    }

    /**
     * Public frontend to set an option
     * 
     * There is an additional validation (relatively to the protected _setOption method) 
     * 
     * @param string $name name of the option
     * @param mixed $value value of the option
     */
    public function setOption($name, $value)
    {
        if (is_string($name)) {
            if (array_key_exists($name, $this->_backwardCompatibilityArray)) {
                $tmp = $this->_backwardCompatibilityArray[$name];
                $this->_log("$name option is deprecated, use $tmp instead (same syntax) !");
                $name = $tmp;
            } else {
                $name = strtolower($name);
            }
            if (array_key_exists($name, $this->_options)) {
                // This is a Core option
                $this->_setOption($name, $value);
                return;
            }
            if (array_key_exists($name, $this->_specificOptions)) { 
                // This a specic option of this frontend
                $this->_specificOptions[$name] = $value;
                return;
            }
        } 
        Zend_Cache::throwException("Incorrect option name : $name");
    }

    /**
     * Set an option
     * 
     * @param string $name name of the option
     * @param mixed $value value of the option
     */
    private function _setOption($name, $value)
    {
        if (!is_string($name) || !array_key_exists($name, $this->_options)) {
            Zend_Cache::throwException("Incorrect option name : $name");
        }
        $this->_options[$name] = $value;
    }

    /**
     * Force a new lifetime
     * 
     * The new value is set for the core/frontend but for the backend too (directive)
     * 
     * @param int $newLifetime new lifetime (in seconds)
     */
    public function setLifetime($newLifetime)
    {
        $this->_options['lifetime'] = $newLifetime;
        $this->_backend->setDirectives(array(
            'lifetime' => $newLifetime
        ));
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     * 
     * @param string $id cache id
     * @param boolean $doNotTestCacheValidity if set to true, the cache validity won't be tested
     * @param boolean $doNotUnserialize do not serialize (even if automatic_serialization is true) => for internal use
     * @return mixed cached datas (or false)
     */
    public function load($id, $doNotTestCacheValidity = false, $doNotUnserialize = false)
    {
        if (!$this->_options['caching']) {
            return false;
        }
        $this->_lastId = $id;        
        self::_validateIdOrTag($id);
        $data = $this->_backend->load($id, $doNotTestCacheValidity);
        if ($data===false) {
            // no cache available
            return false;
        }
        if ((!$doNotUnserialize) && $this->_options['automatic_serialization']) {
            // we need to unserialize before sending the result
            return unserialize($data);
        }
        return $data;
    }

    /**
     * THIS METHOD IS DEPRECATED : USE LOAD() INSTEAD (same syntax) !
     * 
     * it will be removed in ZF 1.1 !
     */
    public function get($id, $doNotTestCacheValidity = false, $doNotUnserialize = false)
    {
        $this->_log("get() method is deprecated => use load() method instead (same syntax) !");
        return $this->load($id, $doNotTestCacheValidity, $doNotUnserialize);
    }

    /**
     * Test if a cache is available for the given id 
     *
     * @param string $id cache id
     * @return boolean true is a cache is available, false else
     */
    public function test($id) 
    {
        if (!$this->_options['caching']) {
            return false;
        }
        self::_validateIdOrTag($id);
        $this->_lastId = $id;
        return $this->_backend->test($id);
    }

    /**
     * Save some data in a cache 
     * 
     * @param mixed $data data to put in cache (can be another type than string if automatic_serialization is on)
     * @param cache $id cache id (if not set, the last cache id will be used)
     * @param array $tags cache tags
     * @param int $specificLifetime if != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean true if no problem
     */
    public function save($data, $id = null, $tags = array(), $specificLifetime = false) 
    {
        if (!$this->_options['caching']) {
            return true;
        }
        if (is_null($id)) {
            $id = $this->_lastId;
        }
        self::_validateIdOrTag($id);  
        self::_validateTagsArray($tags);   
        if ($this->_options['automatic_serialization']) {
            // we need to serialize datas before storing them
            $data = serialize($data);
        } else {
            if (!is_string($data)) {
                Zend_Cache::throwException("Datas must be string or set automatic_serialization = true");
            }
        }
        // automatic cleaning 
        if ($this->_options['automatic_cleaning_factor'] > 0) {
            $rand = rand(1, $this->_options['automatic_cleaning_factor']);
            if ($rand==1) {
                $this->clean('old');
            }
        }
        $result = $this->_backend->save($data, $id, $tags, $specificLifetime);
        if (!$result) {
            // maybe the cache is corrupted, so we remove it !
            if ($this->_options['logging']) {
                $this->_log("Zend_Cache_Core::save() : impossible to save cache (id=$id)");
            }
            $this->remove($id);
            return false;
        }
        if ($this->_options['write_control']) {
            $data2 = $this->_backend->load($id, true);
            if ($data!=$data2) {
                $this->_log('Zend_Cache_Core::save() / write_control : written and read data do not match');
                $this->remove($id);
                return false;
            }
        }
        return true;
    }

    /**
     * Remove a cache 
     * 
     * @param string $id cache id to remove
     * @return boolean true if ok
     */
    public function remove($id)
    {
        if (!$this->_options['caching']) {
            return true;
        }
        self::_validateIdOrTag($id);
        return $this->_backend->remove($id);
    }

    /**
     * Clean cache entries
     * 
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => remove too old cache entries ($tags is not used) 
     * 'matchingTag'    => remove cache entries matching all given tags 
     *                     ($tags can be an array of strings or a single string) 
     * 'notMatchingTag' => remove cache entries not matching one of the given tags
     *                     ($tags can be an array of strings or a single string)                            
     * 
     * @param string $mode
     * @param mixed $parameters  
     * @return boolean true if ok
     */
    public function clean($mode = 'all', $tags = array())
    {
        if (!$this->_options['caching']) {
            return true;
        }
        if (!in_array($mode, array(Zend_Cache::CLEANING_MODE_ALL, Zend_Cache::CLEANING_MODE_OLD, Zend_Cache::CLEANING_MODE_MATCHING_TAG, Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG))) {
            Zend_Cache::throwException('Invalid cleaning mode');
        }
        self::_validateTagsArray($tags);
        return $this->_backend->clean($mode, $tags);
    }

    // ------------------------------------
    // --- Private or protected methods ---
    // ------------------------------------

    /**
     * Validate a cache id or a tag (security, reliable filenames, reserved prefixes...)
     * 
     * Throw an exception if a problem is found
     * 
     * @param string $string cache id or tag
     */
    private static function _validateIdOrTag($string)
    {
        if (!is_string($string)) {
            Zend_Cache::throwException('Invalid id or tag : must be a string');
        }
        if (substr($string, 0, 9) == 'internal-') {
            Zend_Cache::throwException('"internal-*" ids or tags are reserved');
        }
        if (!preg_match('~^[\w]+$~', $string)) {
            Zend_Cache::throwException('Invalid id or tag : must use only [a-zA-Z0-9_]');
        }
    }

    /**
     * Validate a tags array (security, reliable filenames, reserved prefixes...)
     * 
     * Throw an exception if a problem is found
     * 
     * @param array $tags array of tags
     */
    private static function _validateTagsArray($tags)
    {
        if (!is_array($tags)) {
            Zend_Cache::throwException('Invalid tags array : must be an array');
        }
        foreach($tags as $tag) {
            self::_validateIdOrTag($tag);
        }
        reset($tags);
    }

    /**
     * Make sure if we enable logging that the Zend_Log class
     * is available.
     * Create a default log object if none is set.
     *
     * @return void
     * @throws Zend_Cache_Exception
     */
    protected function _loggerSanity()
    {
        if (!isset($this->_options['logging']) || !$this->_options['logging']) {
            return;
        }
        try {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass('Zend_Log');
        } catch (Zend_Exception $e) {
            Zend_Cache::throwException('Logging feature is enabled but the Zend_Log class is not available');
        }
        if (isset($this->_options['logger']) && $this->_options['logger'] instanceof Zend_Log) {
            return;
        }
        // Create a default logger to the standard output stream
        Zend_Loader::loadClass('Zend_Log_Writer_Stream');
        $logger = new Zend_Log(new Zend_Log_Writer_Stream('php://output'));
        $this->_options['logger'] = $logger;
    }

    /**
     * Log a message at the WARN (4) priority.
     *
     * @param string $message
     * @return void
     * @throws Zend_Cache_Exception
     */
    protected function _log($message, $priority = 4)
    {
        if (!$this->_options['logging']) {
            return;
        }
        if (!(isset($this->_options['logger']) || $this->_options['logger'] instanceof Zend_Log)) {
            Zend_Cache::throwException('Logging is enabled but logger is not set');
        }
        $logger = $this->_options['logger'];
        $logger->log($message, $priority);
    }

}
