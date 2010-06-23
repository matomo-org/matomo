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
 * @subpackage Zend_Cache_Backend
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Test.php 16541 2009-07-07 06:59:03Z bkarwin $
 */


/**
 * @see Zend_Cache_Backend_Interface
 */
require_once 'Zend/Cache/Backend/Interface.php';

/**
 * @see Zend_Cache_Backend
 */
require_once 'Zend/Cache/Backend.php';

/**
 * @package    Zend_Cache
 * @subpackage Zend_Cache_Backend
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cache_Backend_Test extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface
{
    /**
     * Available options
     *
     * @var array available options
     */
    protected $_options = array();

    /**
     * Frontend or Core directives
     *
     * @var array directives
     */
    protected $_directives = array();

    /**
     * Array to log actions
     *
     * @var array $_log
     */
    private $_log = array();

    /**
     * Current index for log array
     *
     * @var int $_index
     */
    private $_index = 0;

    /**
     * Constructor
     *
     * @param  array $options associative array of options
     * @return void
     */
    public function __construct($options = array())
    {
        $this->_addLog('construct', array($options));
    }

    /**
     * Set the frontend directives
     *
     * @param  array $directives assoc of directives
     * @return void
     */
    public function setDirectives($directives)
    {
        $this->_addLog('setDirectives', array($directives));
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * For this test backend only, if $id == 'false', then the method will return false
     * if $id == 'serialized', the method will return a serialized array
     * ('foo' else)
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string Cached datas (or false)
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        $this->_addLog('get', array($id, $doNotTestCacheValidity));
        if ($id=='false') {
            return false;
        }
        if ($id=='serialized') {
            return serialize(array('foo'));
        }
        if ($id=='serialized2') {
            return serialize(array('headers' => array(), 'data' => 'foo'));
        }
        if (($id=='71769f39054f75894288e397df04e445') or ($id=='615d222619fb20b527168340cebd0578')) {
            return serialize(array('foo', 'bar'));
        }
        if (($id=='8a02d218a5165c467e7a5747cc6bd4b6') or ($id=='648aca1366211d17cbf48e65dc570bee')) {
            return serialize(array('foo', 'bar'));
        }
        return 'foo';
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * For this test backend only, if $id == 'false', then the method will return false
     * (123456 else)
     *
     * @param  string $id Cache id
     * @return mixed|false false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($id)
    {
        $this->_addLog('test', array($id));
        if ($id=='false') {
            return false;
        }
        if (($id=='d8523b3ee441006261eeffa5c3d3a0a7') or ($id=='3c439c922209e2cb0b54d6deffccd75a')) {
            return false;
        }
        if (($id=='40f649b94977c0a6e76902e2a0b43587') or ($id=='e83249ea22178277d5befc2c5e2e9ace')) {
            return false;
        }
        return 123456;
    }

    /**
     * Save some string datas into a cache record
     *
     * For this test backend only, if $id == 'false', then the method will return false
     * (true else)
     *
     * @param  string $data             Datas to cache
     * @param  string $id               Cache id
     * @param  array  $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  int    $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean True if no problem
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        $this->_addLog('save', array($data, $id, $tags));
        if ($id=='false') {
            return false;
        }
        return true;
    }

    /**
     * Remove a cache record
     *
     * For this test backend only, if $id == 'false', then the method will return false
     * (true else)
     *
     * @param  string $id Cache id
     * @return boolean True if no problem
     */
    public function remove($id)
    {
        $this->_addLog('remove', array($id));
        if ($id=='false') {
            return false;
        }
        return true;
    }

    /**
     * Clean some cache records
     *
     * For this test backend only, if $mode == 'false', then the method will return false
     * (true else)
     *
     * Available modes are :
     * Zend_Cache::CLEANING_MODE_ALL (default)    => remove all cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_OLD              => remove too old cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_MATCHING_TAG     => remove cache entries matching all given tags
     *                                               ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG => remove cache entries not {matching one of the given tags}
     *                                               ($tags can be an array of strings or a single string)
     *
     * @param  string $mode Clean mode
     * @param  array  $tags Array of tags
     * @return boolean True if no problem
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        $this->_addLog('clean', array($mode, $tags));
        if ($mode=='false') {
            return false;
        }
        return true;
    }

    /**
     * Get the last log
     *
     * @return string The last log
     */
    public function getLastLog()
    {
        return $this->_log[$this->_index - 1];
    }

    /**
     * Get the log index
     *
     * @return int Log index
     */
    public function getLogIndex()
    {
        return $this->_index;
    }

    /**
     * Get the complete log array
     *
     * @return array Complete log array
     */
    public function getAllLogs()
    {
        return $this->_log;
    }

    /**
     * Return true if the automatic cleaning is available for the backend
     *
     * @return boolean
     */
    public function isAutomaticCleaningAvailable()
    {
        return true;
    }

    /**
     * Add an event to the log array
     *
     * @param  string $methodName MethodName
     * @param  array  $args       Arguments
     * @return void
     */
    private function _addLog($methodName, $args)
    {
        $this->_log[$this->_index] = array(
            'methodName' => $methodName,
            'args' => $args
        );
        $this->_index = $this->_index + 1;
    }

}
