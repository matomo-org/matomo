<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Code originally inspired from OpenX 
 * - openx/plugins_repo/openXDeliveryCacheStore/extensions/deliveryCacheStore/oxCacheFile/oxCacheFile.class.php
 * - openx/plugins_repo/openXDeliveryCacheStore/extensions/deliveryCacheStore/oxCacheFile/oxCacheFile.delivery.php
 *
 * We may want to add support for cache expire, storing last modification time in the file. See code in:
 * - openx/lib/max/Delivery/cache.php
 *
 * @package Piwik
 */
class Piwik_CacheFile
{
    protected $cachePath;
    protected $cachePrefix;
    
    function __construct($directory)
    {
    	$this->cachePath = PIWIK_USER_PATH . '/tmp/cache/' . $directory . '/';
    }
    
	/**
	 * Function to fetch a cache entry
	 *
	 * @param string $filename The name of file where cache entry is stored
	 * @return mixed False on error, or array the cache content
	 */
	function get($id) 
	{
	    $cache_complete = false;
	    $content = '';
	
	    // We are assuming that most of the time cache will exists
	    $ok = @include($this->cachePath . $id . '.php');
	
	    if ($ok && $cache_complete == true) {
	        return $content;
	    }

	    return false;
	}
	
	/**
	 * A function to store content a cache entry.
	 *
	 * @param string $id The filename where cache entry is stored
	 * @param array $content  The cache content
	 * @return bool True if the entry was succesfully stored
	 */
	function set($id, $content)
	{
		if( !is_dir($this->cachePath))
		{
			Piwik_Common::mkdir($this->cachePath);
		}
	    if (!is_writable($this->cachePath)) {
	        return false;
	    }
	
	    $id = $this->cachePath . $id . '.php';
	
	    $cache_literal  = "<"."?php\n\n";
	    $cache_literal .= "$"."content   = ".var_export($content, true).";\n\n";
	    $cache_literal .= "$"."cache_complete   = true;\n\n";
	    $cache_literal .= "?".">";
	
	    // Write cache to a temp file, then rename it, overwritng the old cache
	    // On *nix systems this should guarantee atomicity
	    $tmp_filename = tempnam($this->cachePath, 'tmp_');
	    if ($fp = @fopen($tmp_filename, 'wb')) {
	        @fwrite ($fp, $cache_literal, strlen($cache_literal));
	        @fclose ($fp);
	
	        if (!@rename($tmp_filename, $id)) {
	            // On some systems rename() doesn't overwrite destination
	            @unlink($id);
	            if (!@rename($tmp_filename, $id)) {
	                // Make sure that no temporary file is left over
	                // if the destination is not writable
	                @unlink($tmp_filename);
	            }
	        }
	        return true;
	    }
	    return false;
	}

    /**
     * A function to delete a single cache entry
     *
     * @param string $filename The cache entry filename (hashed name)
     * @return bool True if the entres were succesfully deleted
     */
    function delete($id)
    {
        $filename = $this->cachePath . $id . '.php';
        if (file_exists($filename)) {
            @unlink ($filename);
            return true;
        }
        return false;
    }
    
    /**
     * A function to delete all cache entries in the directory
     */
    function deleteAll()
    {
    	Piwik::unlinkRecursive($this->cachePath, $deleteRootToo = false);
    }
}
