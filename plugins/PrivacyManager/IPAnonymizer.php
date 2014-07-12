<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Common;
use Piwik\IP;

/**
 * Anonymize visitor IP addresses to comply with the privacy laws/guidelines in countries, such as Germany.
 */
class IPAnonymizer
{
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
        if (!$this->isActive()) {
            Common::printDebug("Visitor IP was _not_ anonymized: ". IP::N2P($ip));
            return;
        }

        $originalIp = $ip;

        $privacyConfig = new Config();

        $ip = self::applyIPMask($ip, $privacyConfig->ipAddressMaskLength);

        Common::printDebug("Visitor IP (was: ". IP::N2P($originalIp) .") has been anonymized: ". IP::N2P($ip));
    }

    /**
     * Deactivates IP anonymization. This function will not be called by the Tracker.
     */
    public static function deactivate()
    {
        $privacyConfig = new Config();
        $privacyConfig->ipAnonymizerEnabled = false;
    }

    /**
     * Activates IP anonymization. This function will not be called by the Tracker.
     */
    public static function activate()
    {
        $privacyConfig = new Config();
        $privacyConfig->ipAnonymizerEnabled = true;
    }

    /**
     * Returns true if IP anonymization support is enabled, false if otherwise.
     *
     * @return bool
     */
    public static function isActive()
    {
        $privacyConfig = new Config();
        return $privacyConfig->ipAnonymizerEnabled;
    }
}
