<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Cache;

use Piwik\Piwik;
use Piwik\SettingsServer;
use Piwik\Tracker;
use Piwik\Translate;

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
    protected static $entriesToPersist = array();

    private $persistForTracker = false;

    private $cacheKey;

    public function __construct($cacheKey)
    {
        $this->setCacheKey($cacheKey);
    }

    public function enablePersistForTracker()
    {
        $this->persistForTracker = true;
    }

    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $this->completeKey($cacheKey);
    }

    public function get()
    {
        return self::$staticCache[$this->cacheKey];
    }

    public function has()
    {
        return array_key_exists($this->cacheKey, self::$staticCache);
    }

    public function set($content)
    {
        self::$staticCache[$this->cacheKey] = $content;

        if ($this->persistForTracker) {
            self::$entriesToPersist[] = $this->cacheKey;
        }
    }

    public static function loadTrackerCache()
    {
        $cache = \Piwik\Tracker\Cache::getCacheGeneral();
        if (array_key_exists('staticCache', $cache)) {
            static::$staticCache = $cache['staticCache'];
        }
    }

    public static function saveTrackerCache()
    {
        $cache = \Piwik\Tracker\Cache::getCacheGeneral();

        if (array_key_exists('staticCache', $cache)) {
            $oldContent = array_keys($cache['staticCache']);
            $save = array_diff(self::$entriesToPersist, $oldContent);
        } else {
            $save = true;
        }

        if (!empty($save)) {
            $content = array();
            foreach (self::$entriesToPersist as $key) {
                $content[$key] = self::$staticCache[$key];
            }

            $cache['staticCache'] = $content;
            \Piwik\Tracker\Cache::setCacheGeneral($cache);
        }
    }

    protected function completeKey($cacheKey)
    {
        return $cacheKey;
    }
}
