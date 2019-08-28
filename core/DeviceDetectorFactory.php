<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use DeviceDetector\DeviceDetector;
use Piwik\Container\StaticContainer;

class DeviceDetectorFactory
{
    /**
     * Returns a Singleton instance of DeviceDetector for the given user agent.
     * @param string $userAgent
     * @return DeviceDetector
     * @deprecated Should get a factory via StaticContainer and call makeInstance() on it instead
     */
    public static function getInstance($userAgent)
    {
        $factory = StaticContainer::get(\Piwik\DeviceDetector\DeviceDetectorFactory::class);
        return $factory->makeInstance($userAgent);
    }
}