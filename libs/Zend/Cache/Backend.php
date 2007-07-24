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
 * @subpackage Backend
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

 
/**
 * @package    Zend_Cache
 * @subpackage Backend
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cache_Backend
{   
   
    // ------------------
    // --- Properties ---
    // ------------------
    
    /**
     * Frontend or Core directives
     * 
     * =====> (int) lifetime :
     * - Cache lifetime (in seconds)
     * - If null, the cache is valid forever
     * 
     * =====> (int) logging :
     * - if set to true, a logging is activated throw Zend_Log
     * 
     * @var array directives
     */
    protected $_directives = array(
        'lifetime' => 3600,
        'logging'  => false,
        'logger'   => null
    );  
    
    /**
     * Available options
     * 
     * @var array available options
     */
    protected $_options = array();
    
    /**
     * backward compatibility becase of ZF-879 and ZF-1172 (it will be removed in ZF 1.1)
     *
     * @var array 
     */
    protected $_backwardCompatibilityArray = array();
    
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
        if (!is_array($options)) Zend_Cache::throwException('Options parameter must be an array');
        while (list($name, $value) = each($options)) {
            $this->setOption($name, $value);
        }
    }  
     
    /**
     * Set the frontend directives
     * 
     * @param array $directives assoc of directives
     */
    public function setDirectives($directives)
    {
        if (!is_array($directives)) Zend_Cache::throwException('Directives parameter must be an array');
        while (list($name, $value) = each($directives)) {
            if (!is_string($name)) {
                Zend_Cache::throwException("Incorrect option name : $name");
            } 
            $name = strtolower($name);
            if (array_key_exists($name, $this->_directives)) {
                $this->_directives[$name] = $value;
            }

        }

        $this->_loggerSanity();
    } 
    
    /**
     * Set an option
     * 
     * @param string $name
     * @param mixed $value
     */ 
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            Zend_Cache::throwException("Incorrect option name : $name");
        }
        if (array_key_exists($name, $this->_backwardCompatibilityArray)) {
            $tmp = $this->_backwardCompatibilityArray[$name];
            $this->_log("$name option is deprecated, use $tmp instead (same syntax) !");
            $name = $tmp;
        } else {
            $name = strtolower($name);
        }
        if (!array_key_exists($name, $this->_options)) {
            Zend_Cache::throwException("Incorrect option name : $name");
        }
        $this->_options[$name] = $value;
    }   
    
    /**
     * Get the life time
     * 
     * if $specificLifetime is not false, the given specific life time is used
     * else, the global lifetime is used
     * 
     * @return int cache life time
     */
    public function getLifetime($specificLifetime)
    {
        if ($specificLifetime === false) {
            return $this->_directives['lifetime'];
        }
        return $specificLifetime;
    }
    
    /**
     * Return a system-wide tmp directory 
     *
     * @return string system-wide tmp directory
     */
    static function getTmpDir()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // windows...
            foreach (array($_ENV, $_SERVER) as $tab) {
                foreach (array('TEMP', 'TMP', 'windir', 'SystemRoot') as $key) {
                    if (isset($tab[$key])) {
                        $result = $tab[$key];
                        if (($key == 'windir') or ($key == 'SystemRoot')) {
                            $result = $result . '\\temp';
                        }
                        return $result;
                    }
                }
            }
            return '\temp';
        } else {
            // unix...
            if (isset($_ENV['TMPDIR']))    return $_ENV['TMPDIR'];
            if (isset($_SERVER['TMPDIR'])) return $_SERVER['TMPDIR'];
            return '/tmp';
        }
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
        if (!isset($this->_directives['logging']) || !$this->_directives['logging']) {
            return;
        }
        try {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass('Zend_Log');
        } catch (Zend_Exception $e) {
            Zend_Cache::throwException('Logging feature is enabled but the Zend_Log class is not available');
        }
        if (isset($this->_directives['logger']) && $this->_directives['logger'] instanceof Zend_Log) {
            return;
        }
        // Create a default logger to the standard output stream
        Zend_Loader::loadClass('Zend_Log_Writer_Stream');
        $logger = new Zend_Log(new Zend_Log_Writer_Stream('php://output'));
        $this->_directives['logger'] = $logger;
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
        if (!$this->_directives['logging']) {
            return;
        }
        if (!(isset($this->_directives['logger']) || $this->_directives['logger'] instanceof Zend_Log)) {
            Zend_Cache::throwException('Logging is enabled but logger is not set');
        }
        $logger = $this->_directives['logger'];
        $logger->log($message, $priority);
    }
    
}
