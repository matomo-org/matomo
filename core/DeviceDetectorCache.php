<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Cache as PiwikCache;
use Exception;

/**
 * Caching class used for DeviceDetector caching
 *
 * Combines Piwik\CacheFile with an additional caching in static property
 *
 * Static caching speeds up multiple detections in one request, which is the case when sending bulk requests
 */
class DeviceDetectorCache implements \DeviceDetector\Cache\CacheInterface
{
    protected static $staticCache = array();

    private $cache;
    private $ttl;

    public function __construct($ttl = 300)
    {
        $this->ttl   = (int) $ttl;
        $this->cache = PiwikCache::getLazyCache();
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

        if (array_key_exists($id, self::$staticCache)) {
            return self::$staticCache[$id];
        }

        return $this->cache->fetch($id);
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

        self::$staticCache[$id] = $content;

        return $this->cache->save($id, $content, $this->ttl);
    }

}
