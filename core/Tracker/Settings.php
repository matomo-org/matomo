<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Tracker;
use Piwik\DeviceDetectorFactory;
use Piwik\SettingsPiwik;

class Settings
{
    const OS_BOT = 'BOT';

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

        $deviceDetector = DeviceDetectorFactory::getInstance($userAgent);
        $aBrowserInfo   = $deviceDetector->getClient();

        if ($aBrowserInfo['type'] != 'browser') {
            // for now only track browsers
            unset($aBrowserInfo);
        }

        $browserName    = !empty($aBrowserInfo['short_name']) ? $aBrowserInfo['short_name'] : 'UNK';
        $browserVersion = !empty($aBrowserInfo['version']) ? $aBrowserInfo['version'] : '';

        if ($deviceDetector->isBot()) {
            $os = self::OS_BOT;
        } else {
            $os = $deviceDetector->getOS();
            $os = empty($os['short_name']) ? 'UNK' : $os['short_name'];
        }

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
        // prevent the config hash from being the same, across different Piwik instances
        // (limits ability of different Piwik instances to cross-match users)
        $salt = SettingsPiwik::getSalt();

        $configString =
              $os
            . $browserName . $browserVersion
            . $plugin_Flash . $plugin_Java . $plugin_Director . $plugin_Quicktime . $plugin_RealPlayer . $plugin_PDF . $plugin_WindowsMedia . $plugin_Gears . $plugin_Silverlight . $plugin_Cookie
            . $ip
            . $browserLang
            . $salt;

        $hash = md5($configString, $raw_output = true);

        return substr($hash, 0, Tracker::LENGTH_BINARY_ID);
    }
}