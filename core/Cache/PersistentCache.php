<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Cache;

use Piwik\CacheFile;
use Piwik\Development;
use Piwik\Piwik;
use Piwik\SettingsServer;
use Piwik\Version;

/**
 * Caching class that persists all cached values between requests. Meaning whatever you cache will be stored on the
 * file system. It differs from other caches such as {@link CacheFile} that it does not create a file for each cacheKey.
 * Reading and writing new values does not cause multiple reads / writes on the file system and is therefore faster.
 * The cache won't be invalidated after any time by default but when the tracker cache is cleared. This is usually the
 * case when a new plugin is installed or an existing plugin or the core is updated.
 * You should be careful when caching any data since we won't modify the cache key. So if your data depends on which
 * plugins are activated or should not be available to each user than make sure to include unique names in the cache
 * key such as the names of all loaded plugin names.
 * If development mode is enabled in the config this cache acts as a {@link StaticCache}. Meaning it won't persist any
 * data between requests.
 */
class PersistentCache
{
    /**
     * @var CacheFile
     */
    private static $storage = null;
    private static $content = null;
    private static $isDirty = false;

    private $cacheKey;

    /**
     * Initializes the cache.
     * @param string $cacheKey
     */
    public function __construct($cacheKey)
    {
        $this->cacheKey = $cacheKey;

        if (is_null(self::$content)) {
            self::$content = array();
            self::populateCache();
        }
    }

    /**
     * Overwrites a previously set cache key. Useful if you want to reuse the same instance for different cache keys
     * for performance reasons.
     * @param string $cacheKey
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * Get the content related to the current cache key. Make sure to call the method {@link has()} to verify whether
     * there is actually any content set under this cache key.
     * @return mixed
     */
    public function get()
    {
        return self::$content[$this->cacheKey];
    }

    /**
     * Check whether any content was actually stored for the current cache key.
     * @return bool
     */
    public function has()
    {
        return array_key_exists($this->cacheKey, self::$content);
    }

    /**
     * Set (overwrite) any content related to the current set cache key.
     * @param $content
     */
    public function set($content)
    {
        self::$content[$this->cacheKey] = $content;
        self::$isDirty = true;
    }

    private static function populateCache()
    {
        if (Development::isEnabled()) {
            return;
        }

        if (SettingsServer::isTrackerApiRequest()) {
            $eventToPersist = 'Tracker.end';
            $mode           = '-tracker';
        } else {
            $eventToPersist = 'Request.dispatch.end';
            $mode           = '-ui';
        }

        $cache = self::getStorage()->get(self::getCacheFilename() . $mode);

        if (is_array($cache)) {
            self::$content = $cache;
        }

        Piwik::addAction($eventToPersist, array(__CLASS__, 'persistCache'));
    }

    private static function getCacheFilename()
    {
        return 'StaticCache-' . str_replace(array('.', '-'), '', Version::VERSION);
    }

    /**
     * @ignore
     */
    public static function persistCache()
    {
        if (self::$isDirty) {
            if (SettingsServer::isTrackerApiRequest()) {
                $mode = '-tracker';
            } else {
                $mode = '-ui';
            }

            self::getStorage()->set(self::getCacheFilename() . $mode, self::$content);
        }
    }

    /**
     * @ignore
     */
    public static function _reset()
    {
        self::$content = array();
    }

    /**
     * @return CacheFile
     */
    private static function getStorage()
    {
        if (is_null(self::$storage)) {
            self::$storage = new CacheFile('tracker', 43200);
            self::$storage->addOnDeleteCallback(function () {
                PersistentCache::_reset();
            });
        }

        return self::$storage;
    }
}
