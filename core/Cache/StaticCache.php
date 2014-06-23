<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Cache;
use Piwik\Translate;

/**
 * Caching class used for static caching. The cache automatically caches the key aware of the current loaded language
 * to prevent you from having to reset it all the time during tests.
 *
 * TODO the default static cache should actually not be language aware. Especially since we would end up in classes like
 * PluginAwareLanguageAwareStaticCache, PluginAwareStaticCache, PluginAwareXYZStaticCache,... once we have dependency
 * injection we could "build" all the caches we need removing duplicated code and extend the static cache by using
 * decorators which "enrich" the cache key depending on their awareness.
 */
class StaticCache
{
    private static $staticCache = array();

    private $cacheKey;

    public function __construct($cacheKey)
    {
        $this->cacheKey = $this->completeKey($cacheKey);
    }

    public function get()
    {
        if (self::has()) {
            return self::$staticCache[$this->cacheKey];
        }

        return null;
    }

    public function has()
    {
        return array_key_exists($this->cacheKey, self::$staticCache);
    }

    public function set($content)
    {
        self::$staticCache[$this->cacheKey] = $content;
    }

    protected function completeKey($cacheKey)
    {
        return $cacheKey . Translate::getLanguageLoaded();
    }
}
