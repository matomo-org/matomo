<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Settings\Storage;
use Piwik\Tracker;
use Piwik\Cache as PiwikCache;

/**
 * Loads settings from tracker cache instead of database. If not yet present in tracker cache will cache it.
 */
class SettingsStorage extends Storage
{
    protected function loadSettings()
    {
        $cacheId = $this->getOptionKey();
        $cache = $this->getCache();

        if ($cache->contains($cacheId)) {
            $settings = $cache->fetch($cacheId);
        } else {
            $settings = parent::loadSettings();

            $cache->save($cacheId, $settings);
        }

        return $settings;
    }

    public function save()
    {
        parent::save();
        self::clearCache();
    }

    private function getCache()
    {
        return self::buildCache($this->getOptionKey());
    }

    public static function clearCache()
    {
        Cache::deleteTrackerCache();
        self::buildCache()->flushAll();
    }

    private static function buildCache()
    {
        return PiwikCache::getEagerCache();
    }
}
