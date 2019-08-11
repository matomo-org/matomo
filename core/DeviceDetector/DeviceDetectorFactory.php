<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DeviceDetector;

use DeviceDetector\DeviceDetector;
use Piwik\Common;

class DeviceDetectorFactory
{
    protected static $deviceDetectorInstances = array();

    /**
     * Returns a Singleton instance of DeviceDetector for the given user agent
     * @param string $userAgent
     * @param bool $useFileCache
     * @return DeviceDetector
     */
    public static function getInstance($userAgent, $useFileCache = true)
    {
        $userAgent = Common::mb_substr($userAgent, 0, 500);

        if (array_key_exists($userAgent, self::$deviceDetectorInstances)) {
            return self::$deviceDetectorInstances[$userAgent];
        }

        if ($useFileCache && DeviceDetectorCacheEntry::isCached($userAgent)) {
            $deviceDetector = new DeviceDetectorCacheEntry($userAgent);
        } else {
            $deviceDetector = new DeviceDetector($userAgent);
            $deviceDetector->discardBotInformation();
            $deviceDetector->setCache(new DeviceDetectorCache(86400));
            $deviceDetector->parse();
        }

        self::$deviceDetectorInstances[$userAgent] = $deviceDetector;

        return $deviceDetector;
    }

    public static function clearInstancesCache()
    {
        self::$deviceDetectorInstances = array();
    }
}