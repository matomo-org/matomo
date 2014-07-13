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
 * Caching class used for static caching.
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

    public function __construct($cacheKey)
    {
        $this->setCacheKey($cacheKey);
    }

    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $this->completeKey($cacheKey);
    }

    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    public function get()
    {
        return self::$staticCache[$this->cacheKey];
    }

    public function has()
    {
        return array_key_exists($this->cacheKey, self::$staticCache);
    }

    public function clear()
    {
        unset(self::$staticCache[$this->cacheKey]);
    }

    public function set($content)
    {
        self::$staticCache[$this->cacheKey] = $content;
    }

    protected function completeKey($cacheKey)
    {
        return $cacheKey;
    }
}