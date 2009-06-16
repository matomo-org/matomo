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
 * Zend_Cache_Backend_Interface
 */
require_once 'Zend/Cache/Backend/Interface.php';

/**
 * Zend_Cache_Backend
 */
require_once 'Zend/Cache/Backend.php';


/**
 * @package    Zend_Cache
 * @subpackage Backend
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cache_Backend_File extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface 
{
    
    // ------------------
    // --- Properties ---
    // ------------------
       
    /**
     * Available options
     * 
     * =====> (string) cache_dir :
     * - Directory where to put the cache files
     * 
     * =====> (boolean) file_locking :
     * - Enable / disable file_locking
     * - Can avoid cache corruption under bad circumstances but it doesn't work on multithread
     * webservers and on NFS filesystems for example
     * 
     * =====> (boolean) read_control :
     * - Enable / disable read control
     * - If enabled, a control key is embeded in cache file and this key is compared with the one
     * calculated after the reading.
     * 
     * =====> (string) read_control_type :
     * - Type of read control (only if read control is enabled). Available values are :
     *   'md5' for a md5 hash control (best but slowest)
     *   'crc32' for a crc32 hash control (lightly less safe but faster, better choice)
     *   'strlen' for a length only test (fastest)
     *   
     * =====> (int) hashed_directory_level :
     * - Hashed directory level
     * - Set the hashed directory structure level. 0 means "no hashed directory 
     * structure", 1 means "one level of directory", 2 means "two levels"... 
     * This option can speed up the cache only when you have many thousands of 
     * cache file. Only specific benchs can help you to choose the perfect value 
     * for you. Maybe, 1 or 2 is a good start.
     * 
     * =====> (int) hashed_directory_umask :
     * - Umask for hashed directory structure
     * 
     * =====> (string) file_name_prefix :
     * - prefix for cache files 
     * - be really carefull with this option because a too generic value in a system cache dir
     *   (like /tmp) can cause disasters when cleaning the cache
     * 
     * @var array available options
     */
    protected $_options = array(
        'cache_dir' => null,
        'file_locking' => true,
        'read_control' => true,
        'read_control_type' => 'crc32',
        'hashed_directory_level' => 0,
        'hashed_directory_umask' => 0700,
        'file_name_prefix' => 'zend_cache'
    );
    
    /**
     * backward compatibility becase of ZF-879 and ZF-1172 (it will be removed in ZF 1.1)
     *
     * @var array 
     */
    protected $_backwardCompatibilityArray = array(
        'cacheDir' => 'cache_dir',
        'fileLocking' => 'file_locking',
        'readControl' => 'read_control',
        'readControlType' => 'read_control_type',
        'hashedDirectoryLevel' => 'hashed_directory_level',
        'hashedDirectoryUmask' => 'hashed_directory_umask',
        'fileNamePrefix' => 'file_name_prefix'
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
        parent::__construct($options);
        if (!is_null($this->_options['cache_dir'])) { // particular case for this option
            $this->setCacheDir($this->_options['cache_dir']);
        } else {
            $this->_options['cache_dir'] = self::getTmpDir() . DIRECTORY_SEPARATOR;
        }
        if (isset($this->_options['file_name_prefix'])) { // particular case for this option
            if (!preg_match('~^[\w]+$~', $this->_options['file_name_prefix'])) {
                Zend_Cache::throwException('Invalid file_name_prefix : must use only [a-zA-A0-9_]');
            }
        }
    }  
    
    /**
     * Set the cache_dir (particular case of setOption() method)
     * 
     * @param mixed $value
     */
    public function setCacheDir($value)
    {
        // add a trailing DIRECTORY_SEPARATOR if necessary 
        $value = rtrim($value, '\\/') . DIRECTORY_SEPARATOR;
        $this->setOption('cache_dir', $value);
    }
       
    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     * 
     * @param string $id cache id
     * @param boolean $doNotTestCacheValidity if set to true, the cache validity won't be tested
     * @return string cached datas (or false)
     */
    public function load($id, $doNotTestCacheValidity = false) 
    {
        if (!($this->_test($id, $doNotTestCacheValidity))) {
            // The cache is not hit !
            return false;
        }
        $file = $this->_file($id);
        if (is_null($file)) {
            return false;
        }
        // There is an available cache file !
        $fp = @fopen($file, 'rb');
        if (!$fp) return false;
        if ($this->_options['file_locking']) @flock($fp, LOCK_SH);
        $length = @filesize($file);
        $mqr = get_magic_quotes_runtime();
        set_magic_quotes_runtime(0);
        if ($this->_options['read_control']) {
            $hashControl = @fread($fp, 32);
            $length = $length - 32;
        } 
        if ($length) {
            $data = @fread($fp, $length);
        } else {
            $data = '';
        }
        set_magic_quotes_runtime($mqr);
        if ($this->_options['file_locking']) @flock($fp, LOCK_UN);
        @fclose($fp);
        if ($this->_options['read_control']) {
            $hashData = $this->_hash($data, $this->_options['read_control_type']);
            if ($hashData != $hashControl) {
                // Problem detected by the read control !
                $this->_log('Zend_Cache_Backend_File::load() / read_control : stored hash and computed hash do not match');
                $this->_remove($file);
                return false;    
            }
        }
        return $data;
    }
    
    /**
     * Test if a cache is available or not (for the given id)
     * 
     * @param string $id cache id
     * @return mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($id)
    {
        return $this->_test($id, false);
    }
    
    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the 
     * core not by the backend)
     *
     * @param string $data datas to cache
     * @param string $id cache id
     * @param array $tags array of strings, the cache record will be tagged by each string entry
     * @param int $specificLifetime if != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean true if no problem
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        if ((!is_dir($this->_options['cache_dir'])) or (!is_writable($this->_options['cache_dir']))) {
            $this->_log("Zend_Cache_Backend_File::save() : cache_dir doesn't exist or is not writable");
        }
        $this->remove($id); // to avoid multiple files with the same cache id
        $lifetime = $this->getLifetime($specificLifetime);
        $expire = $this->_expireTime($lifetime);
        $file = $this->_file($id, $expire);
        $firstTry = true;
        $result = false;
        while (1 == 1) {
            $fp = @fopen($file, "wb");
            if ($fp) {
                // we can open the file, so the directory structure is ok
                if ($this->_options['file_locking']) @flock($fp, LOCK_EX);
                if ($this->_options['read_control']) {
                    @fwrite($fp, $this->_hash($data, $this->_options['read_control_type']), 32);
                }
                $mqr = get_magic_quotes_runtime();
                set_magic_quotes_runtime(0);
                @fwrite($fp, $data);
                if ($this->_options['file_locking']) @flock($fp, LOCK_UN);
                @fclose($fp);
                set_magic_quotes_runtime($mqr);
                $result = true;
                break;
            }         
            // we can't open the file but it's maybe only the directory structure
            // which has to be built
            if ($this->_options['hashed_directory_level']==0) break;
            if ((!$firstTry) || ($this->_options['hashed_directory_level'] == 0)) {
                // it's not a problem of directory structure
                break;
            } 
            $firstTry = false;
            // In this case, maybe we just need to create the corresponding directory
            @mkdir($this->_path($id), $this->_options['hashed_directory_umask'], true);    
            @chmod($this->_path($id), $this->_options['hashed_directory_umask']); // see #ZF-320 (this line is required in some configurations)
        }
        if ($result) {
            foreach ($tags as $tag) {
                $this->_registerTag($id, $tag);
            }
        }
        return $result;
    }
    
    /**
     * Remove a cache record
     * 
     * @param string $id cache id
     * @return boolean true if no problem
     */
    public function remove($id) 
    {
        $result1 = true;
        $files = @glob($this->_file($id, '*'));
        if (count($files) == 0) {
            return false;
        }
        foreach ($files as $file) {
            $result1 = $result1 && $this->_remove($file);
        }
        $result2 = $this->_unregisterTag($id);
        return ($result1 && $result2);
    }
    
    /**
     * Clean some cache records
     *
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => remove too old cache entries ($tags is not used) 
     * 'matchingTag'    => remove cache entries matching all given tags 
     *                     ($tags can be an array of strings or a single string) 
     * 'notMatchingTag' => remove cache entries not matching one of the given tags
     *                     ($tags can be an array of strings or a single string)    
     * 
     * @param string $mode clean mode
     * @param tags array $tags array of tags
     * @return boolean true if no problem
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array()) 
    {
        // We use this private method to hide the recursive stuff
        clearstatcache();
        return $this->_clean($this->_options['cache_dir'], $mode, $tags);
    }
    
    /**
     * PUBLIC METHOD FOR UNIT TESTING ONLY !
     * 
     * Force a cache record to expire
     * 
     * @param string $id cache id
     */
    public function ___expire($id)
    {
        $file = $this->_file($id);
        if (!(is_null($file))) {
            $file2 = $this->_file($id, 1);
            @rename($file, $file2);
        }
    }
    
    // -----------------------
    // --- Private methods ---
    // -----------------------
    
    /**
     * Remove a file
     * 
     * If we can't remove the file (because of locks or any problem), we will touch 
     * the file to invalidate it
     * 
     * @param string $file complete file path
     * @return boolean true if ok
     */  
    private function _remove($file)
    {
        if (!@unlink($file)) {
            # we can't remove the file (because of locks or any problem)
            $this->_log("Zend_Cache_Backend_File::_remove() : we can't remove $file => we are going to try to invalidate it");
            return false;
        } 
        return true;
    }
    
    /**
     * Test if the given cache id is available (and still valid as a cache record)
     * 
     * @param string $id cache id
     * @param boolean $doNotTestCacheValidity if set to true, the cache validity won't be tested
     * @return boolean mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    private function _test($id, $doNotTestCacheValidity)
    {
        clearstatcache();
        $file = $this->_file($id);
        if (is_null($file)) {
            return false;
        }
        $fileName = @basename($file);
        $expire = (int) $this->_fileNameToExpireField($fileName);
        if ($doNotTestCacheValidity) {
            return $expire;
        }
        if (time() <= $expire) {
            return @filemtime($file);
        }
        return false;
    }
    
    /**
     * Clean some cache records (private method used for recursive stuff)
     *
     * Available modes are :
     * Zend_Cache::CLEANING_MODE_ALL (default)    => remove all cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_OLD              => remove too old cache entries ($tags is not used) 
     * Zend_Cache::CLEANING_MODE_MATCHING_TAG     => remove cache entries matching all given tags 
     *                                               ($tags can be an array of strings or a single string) 
     * Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG => remove cache entries not {matching one of the given tags}
     *                                               ($tags can be an array of strings or a single string)    
     * 
     * @param string $dir directory to clean
     * @param string $mode clean mode
     * @param tags array $tags array of tags
     * @return boolean true if no problem
     */
    private function _clean($dir, $mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array()) 
    {
        if (!is_dir($dir)) {
            return false;
        }
        $result = true;
        $prefix = $this->_options['file_name_prefix'];
        $glob = @glob($dir . $prefix . '--*');
        foreach ($glob as $file)  {
            if (is_file($file)) {
                if ($mode==Zend_Cache::CLEANING_MODE_ALL) {
                    $result = ($result) && ($this->_remove($file));
                }
                if ($mode==Zend_Cache::CLEANING_MODE_OLD) {
                    $fileName = @basename($file);
                    $expire = (int) $this->_fileNameToExpireField($fileName);
                    if (time() > $expire) {
                        $result = ($result) && ($this->_remove($file));
                    }
                }
                if ($mode==Zend_Cache::CLEANING_MODE_MATCHING_TAG) {
                    $matching = true;
                    $id = $this->_fileNameToId(basename($file)); 
                    if (!($this->_isATag($id))) {
                        foreach ($tags as $tag) {
                            if (!($this->_testTag($id, $tag))) {
                                $matching = false;
                                break;
                            }
                        }
                        if ($matching) {
                            $result = ($result) && ($this->remove($id));
                        }
                    }
                }
                if ($mode==Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG) {
                    $matching = false;
                    $id = $this->_fileNameToId(basename($file));
                    if (!($this->_isATag($id))) {
                        foreach ($tags as $tag) {
                            if ($this->_testTag($id, $tag)) {
                                $matching = true;
                                break;
                            }
                        }
                        if (!$matching) {
                            $result = ($result) && ($this->remove($id));
                        }
                    }                               
                }
            }
            if ((is_dir($file)) and ($this->_options['hashed_directory_level']>0)) {
                // Recursive call
                $result = ($result) && ($this->_clean($file . DIRECTORY_SEPARATOR, $mode, $tags));
                if ($mode=='all') {
                    // if mode=='all', we try to drop the structure too                    
                    @rmdir($file);
                }
            }
        }
        return $result;  
    }
    
    /**
     * Register a cache id with the given tag
     * 
     * @param string $id cache id
     * @param string $tag tag
     * @return boolean true if no problem
     */
    private function _registerTag($id, $tag) 
    {
        return $this->save('1', $this->_tagCacheId($id, $tag));
    }
    
    /**
     * Unregister tags of a cache id
     * 
     * @param string $id cache id
     * @return boolean true if no problem
     */
    private function _unregisterTag($id) 
    {
        $filesToRemove = @glob($this->_file($this->_tagCacheId($id, '*'), '*'));
        $result = true;
        foreach ($filesToRemove as $file) {
            $result = $result && ($this->_remove($file));
        }    
        return $result;    
    }
    
    /**
     * Test if a cache id was saved with the given tag
     * 
     * @param string $id cache id
     * @param string $tag tag name
     * @return true if the cache id was saved with the given tag
     */
    private function _testTag($id, $tag) 
    {
        if ($this->test($this->_tagCacheId($id, $tag))) {
           return true;
        }
        return false;
    }
    
    /**
     * Compute & return the expire time
     * 
     * @return int expire time (unix timestamp)
     */
    private function _expireTime($lifetime) 
    {
        if (is_null($this->_directives['lifetime'])) {
            return 9999999999;
        }
        return time() + $lifetime;
    }
    
    /**
     * Make a control key with the string containing datas
     *
     * @param string $data data
     * @param string $controlType type of control 'md5', 'crc32' or 'strlen'
     * @return string control key
     */
    private function _hash($data, $controlType)
    {
        switch ($controlType) {
        case 'md5':
            return md5($data);
        case 'crc32':
            return sprintf('% 32d', crc32($data));
        case 'strlen':
            return sprintf('% 32d', strlen($data));
        default:
            Zend_Cache::throwException("Incorrect hash function : $controlType");
        }
    }
      
    /**
     * Return a special/reserved cache id for storing the given tag on the given id
     * 
     * @param string $id cache id
     * @param string $tag tag name
     * @return string cache id for the tag
     */
    private function _tagCacheId($id, $tag) {
        return 'internal-' . $id . '-' . $tag;
    }
    
    /**
     * Return true is the given id is a tag
     * 
     * @param string $id
     * @return boolean
     */
    private function _isATag($id)
    {
        if (substr($id, 0, 9) == 'internal-') {
            return true;
        }
        return false;
    }
    
    /**
     * Transform a cache id into a file name and return it
     * 
     * @param string $id cache id
     * @param int expire timestamp
     * @return string file name
     */
    private function _idToFileName($id, $expire)
    {
        $prefix = $this->_options['file_name_prefix'];
        $result = $prefix . '---' . $id . '---' . $expire;
        return $result;
    }
    
    /**
     * Get the father cache id from the tag cache id
     * 
     * @param string $id tag cache id
     * @return string father cache id
     */
    private function _tagCacheIdToFatherCacheId($id)
    {
        return preg_replace('~internal-(\w*)-.*$~', '$1', $id);    
    }
    
    /**
     * Return the expire field from the file name
     * 
     * @param string $fileName
     * @return string expire field
     */
    private function _fileNameToExpireField($fileName)
    {
        $prefix = $this->_options['file_name_prefix'];
        return preg_replace('~^' . $prefix . '---.*---(\d*)$~', '$1', $fileName);
    }
    
    /**
     * Transform a file name into cache id and return it
     * 
     * @param string $fileName file name
     * @return string cache id
     */
    private function _fileNameToId($fileName) 
    {       
        $prefix = $this->_options['file_name_prefix'];
        return preg_replace('~^' . $prefix . '---(.*)---.*$~', '$1', $fileName);
    }
    
    /**
     * Return the complete directory path of a filename (including hashedDirectoryStructure)
     * 
     * @param string $id cache id
     * @return string complete directory path
     */
    private function _path($id)
    {
        $root = $this->_options['cache_dir'];
        $prefix = $this->_options['file_name_prefix'];
        if ($this->_options['hashed_directory_level']>0) {
            if ($this->_isATag($id)) {
                // we store tags in the same directory than the father
                $id2 = $this->_tagCacheIdToFatherCacheId($id);
                $hash = md5($this->_tagCacheIdToFatherCacheId($id));
            } else {
                $hash = md5($id);
            }
            for ($i=0 ; $i<$this->_options['hashed_directory_level'] ; $i++) {
                $root = $root . $prefix . '--' . substr($hash, 0, $i + 1) . DIRECTORY_SEPARATOR;
            }             
        }
        return $root;
    }
    
    /**
     * Make and return a file name (with path)
     *
     * if $expire is null (default), the function try to guess the real file name
     * (if it fails (no cache files or several cache files for this id, the method returns null)
     *
     * @param string $id cache id
     * @param int expire timestamp
     * @return string file name (with path)
     */  
    private function _file($id, $expire = null)
    {
        $path = $this->_path($id);
        if (is_null($expire)) {
            $glob = @glob($path . $this->_idToFileName($id, '*'));
            $nbr = count($glob);
            if ($nbr == 1) {
                return $glob[0];
            }
            return null;       
        }
        $fileName = $this->_idToFileName($id, $expire);
        return $path . $fileName;
    }
    
}
