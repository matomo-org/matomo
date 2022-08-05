<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Config;

class TrackerConfig
{
    /**
     * Update Tracker config
     *
     * @param string $name Setting name
     * @param mixed $value Value
     */
    public static function setConfigValue($name, $value)
    {
        $section = self::getConfig();
        $section[$name] = $value;
        Config::getInstance()->Tracker = $section;
    }

    public static function getConfigValue($name, $idSite = null)
    {
        $config = self::getConfig();
        if (!empty($idSite)) {
            $siteSpecificConfig = self::getSiteSpecificConfig($idSite);
            $config = array_merge($config, $siteSpecificConfig);
        }
        return $config[$name] ?? null;
    }

    private static function getConfig()
    {
        return Config::getInstance()->Tracker;
    }

    private static function getSiteSpecificConfig($idSite)
    {
        $key = 'Tracker_' . $idSite;
        return Config::getInstance()->$key;
    }
}
