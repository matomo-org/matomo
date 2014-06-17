<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\CacheFile;
use Piwik\Tracker;

class Settings
{
    function __construct(Request $request, $ip)
    {
        $this->request   = $request;
        $this->ipAddress = $ip;
        $this->configId  = null;
    }

    function getConfigId()
    {
        if (empty($this->configId)) {
            $this->loadInfo();
        }

        return $this->configId;
    }

    protected function loadInfo()
    {
        list($plugin_Flash, $plugin_Java, $plugin_Director, $plugin_Quicktime, $plugin_RealPlayer, $plugin_PDF,
            $plugin_WindowsMedia, $plugin_Gears, $plugin_Silverlight, $plugin_Cookie) = $this->request->getPlugins();

        $userAgent = $this->request->getUserAgent();

        $deviceDetector = new \DeviceDetector($userAgent);
        $deviceDetector->setCache(new CacheFile('tracker', 86400));
        $deviceDetector->parse();
        $aBrowserInfo = $deviceDetector->getBrowser();

        $browserName    = !empty($aBrowserInfo['short_name']) ? $aBrowserInfo['short_name'] : 'UNK';
        $browserVersion = !empty($aBrowserInfo['version']) ? $aBrowserInfo['version'] : '';

        $os = $deviceDetector->getOS();
        $os = empty($os['short_name']) ? 'UNK' : $os['short_name'];

        $browserLang = substr($this->request->getBrowserLanguage(), 0, 20); // limit the length of this string to match db

        $this->configId = $this->getConfigHash(
            $os,
            $browserName,
            $browserVersion,
            $plugin_Flash,
            $plugin_Java,
            $plugin_Director,
            $plugin_Quicktime,
            $plugin_RealPlayer,
            $plugin_PDF,
            $plugin_WindowsMedia,
            $plugin_Gears,
            $plugin_Silverlight,
            $plugin_Cookie,
            $this->ipAddress,
            $browserLang);
    }


    /**
     * Returns a 64-bit hash of all the configuration settings
     * @param $os
     * @param $browserName
     * @param $browserVersion
     * @param $plugin_Flash
     * @param $plugin_Java
     * @param $plugin_Director
     * @param $plugin_Quicktime
     * @param $plugin_RealPlayer
     * @param $plugin_PDF
     * @param $plugin_WindowsMedia
     * @param $plugin_Gears
     * @param $plugin_Silverlight
     * @param $plugin_Cookie
     * @param $ip
     * @param $browserLang
     * @return string
     */
    protected function getConfigHash($os, $browserName, $browserVersion, $plugin_Flash, $plugin_Java, $plugin_Director, $plugin_Quicktime, $plugin_RealPlayer, $plugin_PDF, $plugin_WindowsMedia, $plugin_Gears, $plugin_Silverlight, $plugin_Cookie, $ip, $browserLang)
    {
        $hash = md5($os . $browserName . $browserVersion . $plugin_Flash . $plugin_Java . $plugin_Director . $plugin_Quicktime . $plugin_RealPlayer . $plugin_PDF . $plugin_WindowsMedia . $plugin_Gears . $plugin_Silverlight . $plugin_Cookie . $ip . $browserLang, $raw_output = true);
        return substr($hash, 0, Tracker::LENGTH_BINARY_ID);
    }
} 