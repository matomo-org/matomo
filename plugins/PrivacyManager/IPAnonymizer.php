<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager;

use Piwik\Common;
use Matomo\Network\IP;

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
     *
     * @param int|null $idSite Site ID to get anonymization config from. Uses global settings if not provided.
     * @param string $ip IP address in binary format (network format)
     */
    public function setVisitorIpAddress(&$ip, ?int $idSite = null)
    {
        $ipObject = IP::fromBinaryIP($ip);

        if (!self::isActive($idSite)) {
            Common::printDebug("Visitor IP was _not_ anonymized: " . $ipObject->toString());
            return;
        }

        $privacyConfig = new Config($idSite);

        $newIpObject = self::applyIPMask($ipObject, $privacyConfig->ipAddressMaskLength);
        $ip = $newIpObject->toBinary();

        Common::printDebug("Visitor IP (was: " . $ipObject->toString() . ") has been anonymized: " . $newIpObject->toString());
    }

    /**
     * Deactivates IP anonymization. This function will not be called by the Tracker.
     *
     * @param int|null $idSite Site ID to apply the config to. Applies globally if not provided.
     */
    public static function deactivate(?int $idSite = null)
    {
        $privacyConfig = new Config($idSite);
        $privacyConfig->ipAnonymizerEnabled = false;
    }

    /**
     * Activates IP anonymization. This function will not be called by the Tracker.
     *
     * @param int|null $idSite Site ID to apply the config to. Applies globally if not provided.
     */
    public static function activate(?int $idSite = null)
    {
        $privacyConfig = new Config($idSite);
        $privacyConfig->ipAnonymizerEnabled = true;
    }

    /**
     * Returns true if IP anonymization support is enabled, false if otherwise.
     *
     * @param int|null $idSite Site ID to check for. Checks global settings if not provided.
     * @return bool
     */
    public static function isActive(?int $idSite = null)
    {
        $privacyConfig = new Config($idSite);
        return $privacyConfig->ipAnonymizerEnabled;
    }
}
