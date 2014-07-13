<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;

/**
 * This class is used to cache data on the filesystem.
 *
 * It is for example used by the Tracker process to cache various settings and websites attributes in tmp/cache/tracker/*
 *
 */
class CacheFile
{
    // for testing purposes since tests run on both CLI/FPM (changes in CLI can't invalidate
    // opcache in FPM, so we have to invalidate before reading)
    public static $invalidateOpCacheBeforeRead = false;

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
     * @var \Callable[]
     */
    private static $onDeleteCallback = array();

    /**
     * @param string $directory directory to use
     * @param int $timeToLiveInSeconds TTL
     */
    public function __construct($directory, $timeToLiveInSeconds = 300)
    {
        $cachePath = PIWIK_USER_PATH . '/tmp/cache/' . $directory . '/';
        $this->cachePath = SettingsPiwik::rewriteTmpPathWithInstanceId($cachePath);

        if ($timeToLiveInSeconds < self::MINIMUM_TTL) {
            $timeToLiveInSeconds = self::MINIMUM_TTL;
        }
        $this->ttl = $timeToLiveInSeconds;
    }

    /**
     * Function to fetch a cache entry
     *
     * @param string $id The cache entry ID
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
        $cacheFilePath = $this->cachePath . $id . '.php';
        if (self::$invalidateOpCacheBeforeRead) {
            $this->opCacheInvalidate($cacheFilePath);
        }

        $ok = @include($cacheFilePath);

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
        if (!Filesystem::isValidFilename($id)) {
            throw new Exception("Invalid cache ID request $id");
        }
        return $id;
    }

    /**
     * A function to store content a cache entry.
     *
     * @param string $id The cache entry ID
     * @param array $content The cache content
     * @throws \Exception
     * @return bool  True if the entry was succesfully stored
     */
    public function set($id, $content)
    {
        if (empty($id)) {
            return false;
        }
        if (!is_dir($this->cachePath)) {
            Filesystem::mkdir($this->cachePath);
        }
        if (!is_writable($this->cachePath)) {
            return false;
        }
        $id = $this->cleanupId($id);

        $id = $this->cachePath . $id . '.php';

        if (is_object($content)) {
            throw new \Exception('You cannot use the CacheFile to cache an object, only arrays, strings and numbers.');
        }

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

            $this->opCacheInvalidate($id);

            return true;
        }
        return false;
    }

    /**
     * A function to delete a single cache entry
     *
     * @param string $id The cache entry ID
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
            $this->opCacheInvalidate($filename);
            @unlink($filename);
            return true;
        }
        return false;
    }

    public function addOnDeleteCallback($onDeleteCallback)
    {
        self::$onDeleteCallback[] = $onDeleteCallback;
    }

    /**
     * A function to delete all cache entries in the directory
     */
    public function deleteAll()
    {
        $self = $this;
        $beforeUnlink = function ($path) use ($self) {
            $self->opCacheInvalidate($path);
        };

        Filesystem::unlinkRecursive($this->cachePath, $deleteRootToo = false, $beforeUnlink);

        if (!empty(self::$onDeleteCallback)) {
            foreach (self::$onDeleteCallback as $callback) {
                $callback();
            }
        }
    }

    public function opCacheInvalidate($filepath)
    {
        if (function_exists('opcache_invalidate')
            && is_file($filepath)
        ) {
            opcache_invalidate($filepath, $force = true);
        }
    }
}
