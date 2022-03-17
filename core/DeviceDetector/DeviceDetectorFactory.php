<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DeviceDetector;

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use Piwik\Container\StaticContainer;

class DeviceDetectorFactory
{
    protected static $deviceDetectorInstances = array();

    /**
     * Returns an instance of DeviceDetector for the given user agent. Uses template method pattern
     * and calls getDeviceDetectionInfo() when it doesn't find a matching instance in the cache.
     * @param string $userAgent
     * @param array $clientHints
     * @return DeviceDetector|mixed
     */
    public function makeInstance($userAgent, $clientHints = [])
    {
        $cacheKey = self::getNormalizedUserAgent($userAgent, $clientHints);

        if (array_key_exists($cacheKey, self::$deviceDetectorInstances)) {
            return self::$deviceDetectorInstances[$cacheKey];
        }

        $deviceDetector = $this->getDeviceDetectionInfo($userAgent, $clientHints);

        self::$deviceDetectorInstances[$cacheKey] = $deviceDetector;

        return $deviceDetector;
    }

    public static function getNormalizedUserAgent($userAgent, $clientHints = [])
    {
        $normalizedClientHints = '';
        if ($clientHints) {
            $hints  = ClientHints::factory($clientHints);
            $brands = $hints->getBrandList();
            ksort($brands);

            // we only take the (sorted) list of brand, os + version and model name into account, as the other values
            // are actually not used and should not change the result
            $normalizedClientHints = md5(json_encode($brands) . $hints->getOperatingSystem() . $hints->getOperatingSystemVersion() . $hints->getModel());
        }

        return mb_substr($normalizedClientHints . trim($userAgent), 0, 500);
    }

    /**
     * Creates a new DeviceDetector for the user agent. Called by makeInstance() when no matching instance
     * was found in the cache.
     * @param string $userAgent
     * @param array $clientHints
     * @return DeviceDetector
     */
    protected function getDeviceDetectionInfo($userAgent, $clientHints = [])
    {
        $deviceDetector = new DeviceDetector($userAgent, ClientHints::factory($clientHints));
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