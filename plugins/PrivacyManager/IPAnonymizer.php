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
use Piwik\Network\IP;

/**
 * Anonymize visitor IP addresses to comply with the privacy laws/guidelines in countries, such as Germany.
 */
class IPAnonymizer
{
    /**
     * Internal function to mask portions of the visitor IP address
     *
     * @param IP $ip
     * @param int $maskLength Number of octets to reset
     * @return IP
     */
    public static function applyIPMask(IP $ip, $maskLength)
    {
        $newIpObject = $ip->anonymize($maskLength);

        return $newIpObject;
    }

    /**
     * Hook on Tracker.Visit.setVisitorIp to anomymize visitor IP addresses
     * @param string $ip IP address in binary format (network format)
     */
    public function setVisitorIpAddress(&$ip)
    {
        $ipObject = IP::fromBinaryIP($ip);

        if (!$this->isActive()) {
            Common::printDebug("Visitor IP was _not_ anonymized: ". $ipObject->toString());
            return;
        }

        $privacyConfig = new Config();

        $newIpObject = self::applyIPMask($ipObject, $privacyConfig->ipAddressMaskLength);
        $ip = $newIpObject->toBinary();

        Common::printDebug("Visitor IP (was: ". $ipObject->toString() .") has been anonymized: ". $newIpObject->toString());
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
