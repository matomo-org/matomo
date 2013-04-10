<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
    /**
     * @var string
     */
    protected $cachePath;
    /**
     * @var
     */
    protected $cachePrefix;

    /**
     * Minimum enforced TTL in seconds
     */
    const MINIMUM_TTL = 60;

    /**
     * @param string $directory  directory to use
     * @param int TTL
     */
    public function __construct($directory, $timeToLiveInSeconds = 300)
    {
        $this->cachePath = PIWIK_USER_PATH . '/tmp/cache/' . $directory . '/';
        if ($timeToLiveInSeconds < self::MINIMUM_TTL) {
            $timeToLiveInSeconds = self::MINIMUM_TTL;
        }
        $this->ttl = $timeToLiveInSeconds;
    }

    /**
     * Function to fetch a cache entry
     *
     * @param string $id  The cache entry ID
     * @return array|bool  False on error, or array the cache content
     */
    public function get($id)
    {
        if (empty($id)) {
            return false;
        }
        $id = $this->cleanupId($id);

        $cache_complete = false;
        $content = '';
        $expires_on = false;

        // We are assuming that most of the time cache will exists
        $ok = @include($this->cachePath . $id . '.php');

        if ($ok && $cache_complete == true) {

            if (empty($expires_on)
                || $expires_on < time()
            ) {
                return false;
            }
            return $content;
        }

        return false;
    }

    private function getExpiresTime()
    {
        return time() + $this->ttl;
    }

    protected function cleanupId($id)
    {
        if (!Piwik_Common::isValidFilename($id)) {
            throw new Exception("Invalid cache ID request $id");
        }
        return $id;
    }

    /**
     * A function to store content a cache entry.
     *
     * @param string $id       The cache entry ID
     * @param array $content  The cache content
     * @return bool  True if the entry was succesfully stored
     */
    public function set($id, $content)
    {
        if (empty($id)) {
            return false;
        }
        if (!is_dir($this->cachePath)) {
            Piwik_Common::mkdir($this->cachePath);
        }
        if (!is_writable($this->cachePath)) {
            return false;
        }
        $id = $this->cleanupId($id);

        $id = $this->cachePath . $id . '.php';

        $cache_literal = "<" . "?php\n";
        $cache_literal .= "$" . "content   = " . var_export($content, true) . ";\n";
        $cache_literal .= "$" . "expires_on   = " . $this->getExpiresTime() . ";\n";
        $cache_literal .= "$" . "cache_complete   = true;\n";
        $cache_literal .= "?" . ">";

        // Write cache to a temp file, then rename it, overwriting the old cache
        // On *nix systems this should guarantee atomicity
        $tmp_filename = tempnam($this->cachePath, 'tmp_');
        @chmod($tmp_filename, 0640);
        if ($fp = @fopen($tmp_filename, 'wb')) {
            @fwrite($fp, $cache_literal, strlen($cache_literal));
            @fclose($fp);

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
     * @param string $id  The cache entry ID
     * @return bool  True if the entry was succesfully deleted
     */
    public function delete($id)
    {
        if (empty($id)) {
            return false;
        }
        $id = $this->cleanupId($id);

        $filename = $this->cachePath . $id . '.php';
        if (file_exists($filename)) {
            @unlink($filename);
            return true;
        }
        return false;
    }

    /**
     * A function to delete all cache entries in the directory
     */
    public function deleteAll()
    {
        Piwik::unlinkRecursive($this->cachePath, $deleteRootToo = false);
    }
}
