<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Access;
use Piwik\ArchiveProcessor\Rules;
use Piwik\CacheFile;
use Piwik\Common;
use Piwik\Config;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Tracker;

/**
 * Simple cache mechanism used in Tracker to avoid requesting settings from mysql on every request
 *
 */
class Cache
{
    /**
     * Public for tests only
     * @var CacheFile
     */
    public static $trackerCache = null;

    protected static function getInstance()
    {
        if (is_null(self::$trackerCache)) {
            $ttl = Config::getInstance()->Tracker['tracker_cache_file_ttl'];
            self::$trackerCache = new CacheFile('tracker', $ttl);
        }
        return self::$trackerCache;
    }

    /**
     * Returns array containing data about the website: goals, URLs, etc.
     *
     * @param int $idSite
     * @return array
     */
    static function getCacheWebsiteAttributes($idSite)
    {
        if ('all' == $idSite) {
            return array();
        }

        $idSite = (int)$idSite;
        if ($idSite <= 0) {
            return array();
        }

        $cache        = self::getInstance();
        $cacheContent = $cache->get($idSite);

        if (false !== $cacheContent) {
            return $cacheContent;
        }

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
            Common::printDebug("Website $idSite tracker cache was re-created.");
        });

        // if nothing is returned from the plugins, we don't save the content
        // this is not expected: all websites are expected to have at least one URL
        if (!empty($content)) {
            $cache->set($idSite, $content);
        }

        return $content;
    }

    /**
     * Clear general (global) cache
     */
    public static function clearCacheGeneral()
    {
        self::getInstance()->delete('general');
    }

    /**
     * Returns contents of general (global) cache.
     * If the cache file tmp/cache/tracker/general.php does not exist yet, create it
     *
     * @return array
     */
    public static function getCacheGeneral()
    {
        $cache   = self::getInstance();
        $cacheId = 'general';

        $cacheContent = $cache->get($cacheId);

        if (false !== $cacheContent) {
            return $cacheContent;
        }

        Tracker::initCorePiwikInTrackerMode();
        $cacheContent = array(
            'isBrowserTriggerEnabled' => Rules::isBrowserTriggerEnabled(),
            'lastTrackerCronRun'      => Option::get('lastTrackerCronRun'),
        );

        /**
         * Triggered before the [general tracker cache](/guides/all-about-tracking#the-tracker-cache)
         * is saved to disk. This event can be used to add extra content to the cache.
         *
         * Data that is used during tracking but is expensive to compute/query should be
         * cached to keep tracking efficient. One example of such data are options
         * that are stored in the piwik_option table. Querying data for each tracking
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
        Common::printDebug("General tracker cache was re-created.");
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
        $cache   = self::getInstance();
        $cacheId = 'general';
        $cache->set($cacheId, $value);

        return true;
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
        $idSite = (int)$idSite;
        self::getInstance()->delete($idSite);
    }

    /**
     * Deletes all Tracker cache files
     */
    public static function deleteTrackerCache()
    {
        self::getInstance()->deleteAll();
    }
}
