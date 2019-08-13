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
     * Returns a Singleton instance of DeviceDetector for the given user agent.
     * @param string $userAgent
     * @return DeviceDetector
     * @deprecated Should get a factory via StaticContainer and call makeInstance() on it instead
     */
    public static function getInstance($userAgent)
    {
        return (new DeviceDetectorFactory())->makeInstance($userAgent);
    }

    /**
     * Returns an instance of DeviceDetector for the given user agent. Uses template method pattern
     * and calls getDeviceDetectionInfo() when it doesn't find a matching instance in the cache.
     * @param string $userAgent
     * @return DeviceDetector|mixed
     */
    public function makeInstance($userAgent)
    {
        $userAgent = Common::mb_substr($userAgent, 0, 500);

        if (array_key_exists($userAgent, self::$deviceDetectorInstances)) {
            return self::$deviceDetectorInstances[$userAgent];
        }

        $deviceDetector = $this->getDeviceDetectionInfo($userAgent);

        self::$deviceDetectorInstances[$userAgent] = $deviceDetector;

        return $deviceDetector;
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
        $deviceDetector->setCache(new DeviceDetectorCache(86400));
        $deviceDetector->parse();
        return $deviceDetector;
    }

    public static function clearInstancesCache()
    {
        self::$deviceDetectorInstances = array();
    }
}