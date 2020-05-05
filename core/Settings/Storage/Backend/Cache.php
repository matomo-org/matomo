<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage\Backend;

use Piwik\Tracker;
use Piwik\Cache as PiwikCache;

/**
 * Loads settings from tracker cache instead of database. If not yet present in tracker cache will cache it.
 *
 * Can be used as a decorator in combination with any other storage backend.
 */
class Cache implements BackendInterface
{
    /**
     * @var BackendInterface
     */
    private $backend;

    public function __construct(BackendInterface $backend)
    {
        $this->backend = $backend;
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save($values)
    {
        $this->backend->save($values);
        self::clearCache();
    }

    public function getStorageId()
    {
        return $this->backend->getStorageId();
    }

    public function delete()
    {
        $this->backend->delete();
        self::clearCache();
    }

    public function load()
    {
        $cacheId = $this->getStorageId();
        $cache = self::buildCache();

        if ($cache->contains($cacheId)) {
            return $cache->fetch($cacheId);
        }

        $settings = $this->backend->load();
        $cache->save($cacheId, $settings);

        return $settings;
    }

    public static function clearCache()
    {
        Tracker\Cache::deleteTrackerCache();
        self::buildCache()->flushAll();
    }

    public static function buildCache()
    {
        return PiwikCache::getEagerCache();
    }
}
