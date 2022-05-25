<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DeviceDetector;

use DeviceDetector\DeviceDetector;
use Piwik\Container\StaticContainer;

class DeviceDetectorFactory
{
    protected static $deviceDetectorInstances = array();

    /**
     * Returns an instance of DeviceDetector for the given user agent. Uses template method pattern
     * and calls getDeviceDetectionInfo() when it doesn't find a matching instance in the cache.
     * @param string $userAgent
     * @return DeviceDetector|mixed
     */
    public function makeInstance($userAgent)
    {
        $userAgent = self::getNormalizedUserAgent($userAgent);

        if (array_key_exists($userAgent, self::$deviceDetectorInstances)) {
            return self::$deviceDetectorInstances[$userAgent];
        }

        $deviceDetector = $this->getDeviceDetectionInfo($userAgent);

        self::$deviceDetectorInstances[$userAgent] = $deviceDetector;

        return $deviceDetector;
    }

    public static function getNormalizedUserAgent($userAgent)
    {
        return mb_substr(trim($userAgent), 0, 500);
    }

    /**
     * Creates a new DeviceDetector for the user agent. Called by makeInstance() when no matching instance
     * was found in the cache.
     * @param $userAgent
     * @return DeviceDetector
     */
    protected function getDeviceDetectionInfo($userAgent)
    {
        $deviceDetector = new DeviceDetector($userAgent);
        $deviceDetector->discardBotInformation();
        $deviceDetector->setCache(StaticContainer::get('DeviceDetector\Cache\Cache'));
        $deviceDetector->parse();
        return $deviceDetector;
    }

    public static function clearInstancesCache()
    {
        self::$deviceDetectorInstances = array();
    }
}