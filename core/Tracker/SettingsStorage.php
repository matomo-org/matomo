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

/**
 * Loads settings from tracker cache instead of database. If not yet present in tracker cache will cache it.
 */
class SettingsStorage extends Storage
{

    protected function loadSettings()
    {
        $trackerCache = Cache::getCacheGeneral();
        $settings = null;

        if (array_key_exists('settingsStorage', $trackerCache)) {
            $allSettings = $trackerCache['settingsStorage'];

            if (is_array($allSettings) && array_key_exists($this->getOptionKey(), $allSettings)) {
                $settings = $allSettings[$this->getOptionKey()];
            }
        } else {
            $trackerCache['settingsStorage'] = array();
        }

        if (is_null($settings)) {
            $settings = parent::loadSettings();

            $trackerCache['settingsStorage'][$this->getOptionKey()] = $settings;
            Cache::setCacheGeneral($trackerCache);
        }

        return $settings;
    }

    public function save()
    {
        parent::save();
        self::clearCache();
    }

    public static function clearCache()
    {
        Cache::clearCacheGeneral();
    }

}
