<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package PrivacyManager
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Common;
use Piwik\Config;
use Piwik\IP;
use Piwik\Tracker\Cache;
use Piwik\Option;

/**
 * Anonymize visitor IP addresses to comply with the privacy laws/guidelines in countries, such as Germany.
 *
 * @package PrivacyManager
 */
class IPAnonymizer
{
    const OPTION_NAME = "PrivacyManager.ipAnonymizerEnabled";

    /**
     * Internal function to mask portions of the visitor IP address
     *
     * @param string $ip IP address in network address format
     * @param int $maskLength Number of octets to reset
     * @return string
     */
    public static function applyIPMask($ip, $maskLength)
    {
        // IPv4 or mapped IPv4 in IPv6
        if (IP::isIPv4($ip)) {
            $i = strlen($ip);
            if ($maskLength > $i) {
                $maskLength = $i;
            }

            while ($maskLength-- > 0) {
                $ip[--$i] = chr(0);
            }
        } else {
            $masks = array(
                'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff',
                'ffff:ffff:ffff:ffff::',
                'ffff:ffff:ffff:0000::',
                'ffff:ff00:0000:0000::'
            );
            $ip = $ip & pack('a16', inet_pton($masks[$maskLength]));
        }
        return $ip;
    }

    /**
     * Hook on Tracker.Visit.setVisitorIp to anomymize visitor IP addresses
     */
    public function setVisitorIpAddress(&$ip)
    {

        if (!$this->isActiveInTracker()) {
            Common::printDebug("Visitor IP was _not_ anonymized: ". IP::N2P($ip));
            return;
        }

        $originalIp = $ip;
        $ip = self::applyIPMask($ip, Config::getInstance()->Tracker['ip_address_mask_length']);
        Common::printDebug("Visitor IP (was: ". IP::N2P($originalIp) .") has been anonymized: ". IP::N2P($ip));
    }

    /**
     * Returns true if IP anonymization is enabled. This function is called by the
     * Tracker.
     */
    private function isActiveInTracker()
    {
        $cache = Cache::getCacheGeneral();
        return !empty($cache[self::OPTION_NAME]);
    }

    /**
     * Caches the status of IP anonymization (whether it is enabled or not).
     */
    public function setTrackerCacheGeneral(&$cacheContent)
    {
        $cacheContent[self::OPTION_NAME] = Option::get(self::OPTION_NAME);
    }

    /**
     * Deactivates IP anonymization. This function will not be called by the Tracker.
     */
    public static function deactivate()
    {
        Option::set(self::OPTION_NAME, 0);
        Cache::clearCacheGeneral();
    }

    /**
     * Activates IP anonymization. This function will not be called by the Tracker.
     */
    public static function activate()
    {
        Option::set(self::OPTION_NAME, 1);
        Cache::clearCacheGeneral();
    }

    /**
     * Returns true if IP anonymization support is enabled, false if otherwise.
     *
     * @return bool
     */
    public static function isActive()
    {
        $active = Option::get(self::OPTION_NAME);
        return !empty($active);
    }
}