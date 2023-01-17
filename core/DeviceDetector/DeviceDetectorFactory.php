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
use Piwik\Cache;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Version;

class DeviceDetectorFactory
{
    protected static $deviceDetectorInstances = [];

    /**
     * Returns an instance of DeviceDetector for the given user agent. Uses template method pattern
     * and calls getDeviceDetectionInfo() when it doesn't find a matching instance in the cache.
     * @param string $userAgent
     * @param array $clientHints
     * @return DeviceDetector
     */
    public function makeInstance($userAgent, array $clientHints = [])
    {
        $userAgent = self::getNormalizedUserAgent($userAgent, $clientHints);
        if (array_key_exists($userAgent, self::$deviceDetectorInstances)) {
            return self::$deviceDetectorInstances[$userAgent];
        }

        $cacheKey = "ua." . Version::VERSION . '.' . sha1($userAgent);

        $lazyCache = Cache::getLazyCache();

        // check if a compatible device detector is in lazy cache
        $serialized = $lazyCache->fetch($cacheKey);
        if ($serialized !== false) {
            // if we find a detector, deserialize it
            $cdd = Common::safe_unserialize($serialized, true);
            if (isset($cdd) && $cdd !== FALSE) {
                return $cdd;
            }
        }

        // parse usr agent.
        $deviceDetector = $this->getDeviceDetectionInfo($userAgent, $clientHints);

        # remove parsers & caches from device detector
        # and serialize it into cache.
        $serialized = serialize(new SerializableDeviceDetector($deviceDetector));
        $lazyCache->save($cacheKey, $serialized, 3600);

        self::$deviceDetectorInstances[$userAgent] = $deviceDetector;

        return $deviceDetector;
    }

    public static function getNormalizedUserAgent($userAgent, array $clientHints = [])
    {
        $normalizedClientHints = '';
        if (is_array($clientHints) && count($clientHints)) {
            $hints = ClientHints::factory($clientHints);
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
    protected function getDeviceDetectionInfo($userAgent, array $clientHints = [])
    {
        $deviceDetector = new DeviceDetector($userAgent, ClientHints::factory($clientHints));
        $deviceDetector->discardBotInformation();
        $deviceDetector->setCache(StaticContainer::get('DeviceDetector\Cache\Cache'));
        $deviceDetector->parse();
        return $deviceDetector;
    }

    public static function clearInstancesCache()
    {
        self::$deviceDetectorInstances = [];
    }
}
