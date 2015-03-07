<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings;

/*
 * The UserSettings API lets you access reports about some of your Visitors technical settings:
 * plugins supported in their browser, Screen resolution and Screen types (normal, widescreen, dual screen or mobile).
 *
 * @method static \Piwik\Plugins\UserSettings\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\Resolution\API} for new implementation.
     */
    public function getResolution($idSite, $period, $date, $segment = false)
    {
        return \Piwik\Plugins\Resolution\API::getInstance()->getResolution($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\Resolution\API} for new implementation.
     */
    public function getConfiguration($idSite, $period, $date, $segment = false)
    {
        return \Piwik\Plugins\Resolution\API::getInstance()->getConfiguration($idSite, $period, $date, $segment);
    }

    protected function getDevicesDetectorApi()
    {
        return \Piwik\Plugins\DevicesDetection\API::getInstance();
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\DevicesDetector\API} for new implementation.
     */
    public function getOS($idSite, $period, $date, $segment = false, $addShortLabel = true)
    {
        return $this->getDevicesDetectorApi()->getOsVersions($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\DevicesDetector\API} for new implementation.
     */
    public function getOSFamily($idSite, $period, $date, $segment = false)
    {
        return $this->getDevicesDetectorApi()->getOsFamilies($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\DevicesDetector\API} for new implementation.
     */
    public function getMobileVsDesktop($idSite, $period, $date, $segment = false)
    {
        return $this->getDevicesDetectorApi()->getType($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\DevicesDetector\API} for new implementation.
     */
    public function getBrowserVersion($idSite, $period, $date, $segment = false)
    {
        return $this->getDevicesDetectorApi()->getBrowserVersions($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\DevicesDetector\API} for new implementation.
     */
    public function getBrowser($idSite, $period, $date, $segment = false)
    {
        return $this->getDevicesDetectorApi()->getBrowsers($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.9.0   See {@link Piwik\Plugins\DevicesDetector\API} for new implementation.
     */
    public function getBrowserType($idSite, $period, $date, $segment = false)
    {
        return $this->getDevicesDetectorApi()->getBrowserEngines($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.10.0   See {@link Piwik\Plugins\DevicePlugins\API} for new implementation.
     */
    public function getPlugin($idSite, $period, $date, $segment = false)
    {
        return \Piwik\Plugins\DevicePlugins\API::getInstance()->getPlugin($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.11.0   See {@link Piwik\Plugins\UserLanguage\API} for new implementation.
     */
    public function getLanguage($idSite, $period, $date, $segment = false)
    {
        return \Piwik\Plugins\UserLanguage\API::getInstance()->getLanguage($idSite, $period, $date, $segment);
    }

    /**
     * @deprecated since 2.11.0   See {@link Piwik\Plugins\UserLanguage\API} for new implementation.
     */
    public function getLanguageCode($idSite, $period, $date, $segment = false)
    {
        return \Piwik\Plugins\UserLanguage\API::getInstance()->getLanguageCode($idSite, $period, $date, $segment);
    }
}
