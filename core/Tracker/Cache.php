<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Access;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Cache as PiwikCache;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Tracker;
use Psr\Log\LoggerInterface;

/**
 * Simple cache mechanism used in Tracker to avoid requesting settings from mysql on every request
 *
 */
class Cache
{
    /**
     * {@see self::withDelegatedCacheClears()}
     * @var bool
     */
    private static $delegatingCacheClears;

    /**
     * {@see self::withDelegatedCacheClears()}
     * @var array
     */
    private static $delegatedClears = [];

    private static $cacheIdGeneral = 'general';

    /**
     * Public for tests only
     * @var \Matomo\Cache\Lazy
     */
    public static $cache;

    /**
     * @return \Matomo\Cache\Lazy
     */
    private static function getCache()
    {
        if (is_null(self::$cache)) {
            self::$cache = PiwikCache::getLazyCache();
        }

        return self::$cache;
    }

    private static function getTtl()
    {
        return Config::getInstance()->Tracker['tracker_cache_file_ttl'];
    }

    /**
     * Returns array containing data about the website: goals, URLs, etc.
     *
     * @param int $idSite
     * @return array
     */
    public static function getCacheWebsiteAttributes($idSite)
    {
        if ('all' === $idSite) {
            return array();
        }

        $idSite = (int)$idSite;
        if ($idSite <= 0) {
            return array();
        }

        $cache = self::getCache();
        $cacheId = self::getCacheKeyWebsiteAttributes($idSite);
        $cacheContent = $cache->fetch($cacheId);

        if (false !== $cacheContent) {
            return $cacheContent;
        }

        return self::updateCacheWebsiteAttributes($idSite);
    }

    private static function getCacheKeyWebsiteAttributes($idSite)
    {
        return $idSite;
    }

    /**
     * Updates the website specific tracker cache containing data about the website: goals, URLs, etc.
     *
     * @param int $idSite
     *
     * @return array
     */
    public static function updateCacheWebsiteAttributes($idSite)
    {
        $cache = self::getCache();
        $cacheId = self::getCacheKeyWebsiteAttributes($idSite);

        Tracker::initCorePiwikInTrackerMode();

        $content = array();
        Access::doAsSuperUser(function () use (&$content, $idSite) {
            /**
             * Triggered to get the attributes of a site entity that might be used by the
             * Tracker.
             *
             * Plugins add new site attributes for use in other tracking events must
             * use this event to put those attributes in the Tracker Cache.
             *
             * **Example**
             *
             *     public function getSiteAttributes($content, $idSite)
             *     {
             *         $sql = "SELECT info FROM " . Common::prefixTable('myplugin_extra_site_info') . " WHERE idsite = ?";
             *         $content['myplugin_site_data'] = Db::fetchOne($sql, array($idSite));
             *     }
             *
             * @param array &$content Array mapping of site attribute names with values.
             * @param int $idSite The site ID to get attributes for.
             */
            Piwik::postEvent('Tracker.Cache.getSiteAttributes', array(&$content, $idSite));

            $logger = StaticContainer::get(LoggerInterface::class);
            $logger->debug("Website $idSite tracker cache was re-created.");
        });

        // if nothing is returned from the plugins, we don't save the content
        // this is not expected: all websites are expected to have at least one URL
        if (!empty($content)) {
            $cache->save($cacheId, $content, self::getTtl());
        }

        Tracker::restoreTrackerPlugins();

        return $content;
    }

    /**
     * Clear general (global) cache
     */
    public static function clearCacheGeneral()
    {
        if (self::$delegatingCacheClears) {
            self::$delegatedClears[__FUNCTION__] = [__FUNCTION__, []];
            return;
        }

        self::getCache()->delete(self::$cacheIdGeneral);
    }

    /**
     * Returns contents of general (global) cache.
     * If the cache file tmp/cache/tracker/general.php does not exist yet, create it
     *
     * @return array
     */
    public static function getCacheGeneral()
    {
        $cache = self::getCache();
        $cacheContent = $cache->fetch(self::$cacheIdGeneral);

        if (false !== $cacheContent) {
            return $cacheContent;
        }

        return self::updateGeneralCache();
    }

    /**
     * Updates the contents of the general (global) cache.
     *
     * @return array
     */
    public static function updateGeneralCache()
    {
        Tracker::initCorePiwikInTrackerMode();
        $cacheContent = array(
            'isBrowserTriggerEnabled' => Rules::isBrowserTriggerEnabled(),
            'lastTrackerCronRun' => Option::get('lastTrackerCronRun'),
        );

        /**
         * Triggered before the [general tracker cache](/guides/all-about-tracking#the-tracker-cache)
         * is saved to disk. This event can be used to add extra content to the cache.
         *
         * Data that is used during tracking but is expensive to compute/query should be
         * cached to keep tracking efficient. One example of such data are options
         * that are stored in the option table. Querying data for each tracking
         * request means an extra unnecessary database query for each visitor action. Using
         * a cache solves this problem.
         *
         * **Example**
         *
         *     public function setTrackerCacheGeneral(&$cacheContent)
         *     {
         *         $cacheContent['MyPlugin.myCacheKey'] = Option::get('MyPlugin_myOption');
         *     }
         *
         * @param array &$cacheContent Array of cached data. Each piece of data must be
         *                             mapped by name.
         */
        Piwik::postEvent('Tracker.setTrackerCacheGeneral', array(&$cacheContent));
        self::setCacheGeneral($cacheContent);

        $logger = StaticContainer::get(LoggerInterface::class);
        $logger->debug("General tracker cache was re-created.");

        Tracker::restoreTrackerPlugins();

        return $cacheContent;
    }

    /**
     * Store data in general (global cache)
     *
     * @param mixed $value
     * @return bool
     */
    public static function setCacheGeneral($value)
    {
        $cache = self::getCache();

        return $cache->save(self::$cacheIdGeneral, $value, self::getTtl());
    }

    /**
     * Regenerate Tracker cache files
     *
     * @param array|int $idSites Array of idSites to clear cache for
     */
    public static function regenerateCacheWebsiteAttributes($idSites = array())
    {
        if (!is_array($idSites)) {
            $idSites = array($idSites);
        }

        foreach ($idSites as $idSite) {
            self::deleteCacheWebsiteAttributes($idSite);
            self::getCacheWebsiteAttributes($idSite);
        }
    }

    /**
     * Delete existing Tracker cache
     *
     * @param string $idSite (website ID of the site to clear cache for
     */
    public static function deleteCacheWebsiteAttributes($idSite)
    {
        if (self::$delegatingCacheClears) {
            self::$delegatedClears[__FUNCTION__ . $idSite] = [__FUNCTION__, func_get_args()];
            return;
        }

        self::getCache()->delete((int)$idSite);
    }

    /**
     * Deletes all Tracker cache files
     */
    public static function deleteTrackerCache()
    {
        if (self::$delegatingCacheClears) {
            self::$delegatedClears[__FUNCTION__] = [__FUNCTION__, []];
            return;
        }

        self::getCache()->flushAll();
    }

    /**
     * Runs `$callback` without clearing any tracker cache, just collecting which delete methods were called.
     * After `$callback` finishes, we clear caches, but just once per type of delete/clear method collected.
     *
     * Use this method if your code will create many cache clears in a short amount of time (eg, if you
     * are invalidating a lot of archives at once).
     *
     * @param $callback
     */
    public static function withDelegatedCacheClears($callback)
    {
        try {
            self::$delegatingCacheClears = true;
            self::$delegatedClears = [];

            return $callback();
        } finally {
            self::$delegatingCacheClears = false;

            self::callAllDelegatedClears();

            self::$delegatedClears = [];
        }
    }

    private static function callAllDelegatedClears()
    {
        foreach (self::$delegatedClears as list($methodName, $params)) {
            if (!method_exists(self::class, $methodName)) {
                continue;
            }

            call_user_func_array([self::class, $methodName], $params);
        }
    }
}
