<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Cache;

/**
 * Caching class used for static caching. Any content that is set here won't be cached between requests. If you do want
 * to persist any content between requests have a look at {@link PersistentCache}
 *
 * TODO the default static cache should actually not be language aware. Especially since we would end up in classes like
 * LanguageAwareStaticCache, PluginAwareStaticCache, PluginAwareLanguageAwareStaticCache, PluginAwareXYZStaticCache,...
 * once we have dependency injection we should "build" all the caches we need removing duplicated code and extend the
 * static cache by using decorators which "enrich" the cache key depending on their awareness.
 */
class StaticCache
{
    protected static $staticCache = array();

    private $cacheKey;

    /**
     * Initializes the cache.
     * @param string $cacheKey
     */
    public function __construct($cacheKey)
    {
        $this->setCacheKey($cacheKey);
    }

    /**
     * Overwrites a previously set cache key. Useful if you want to reuse the same instance for different cache keys
     * for performance reasons.
     * @param string $cacheKey
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $this->completeKey($cacheKey);
    }

    /**
     * Get the content related to the current cache key. Make sure to call the method {@link has()} to verify whether
     * there is actually any content set under this cache key.
     * @return mixed
     */
    public function get()
    {
        return self::$staticCache[$this->cacheKey];
    }

    /**
     * Check whether any content was actually stored for the current cache key.
     * @return bool
     */
    public function has()
    {
        return array_key_exists($this->cacheKey, self::$staticCache);
    }

    /**
     * Reset the stored content of the current cache key.
     */
    public function clear()
    {
        unset(self::$staticCache[$this->cacheKey]);
    }

    /**
     * Reset the stored content of the current cache key.
     * @ignore
     */
    public static function clearAll()
    {
        self::$staticCache = array();
    }

    /**
     * Set (overwrite) any content related to the current set cache key.
     * @param $content
     */
    public function set($content)
    {
        self::$staticCache[$this->cacheKey] = $content;
    }

    protected function completeKey($cacheKey)
    {
        return $cacheKey;
    }
}