<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package AnonymizeIP
 */
namespace Piwik\Plugins\AnonymizeIP;

use Piwik\Common;
use Piwik\Config;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Version;

/**
 * Anonymize visitor IP addresses to comply with the privacy laws/guidelines in countries, such as Germany.
 *
 * @package AnonymizeIP
 */
class AnonymizeIP extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'description'     => Piwik::translate('AnonymizeIP_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Version::VERSION,
        );
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Tracker.setVisitorIp' => 'setVisitorIpAddress',
        );
    }

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
            return $ip & pack('a16', inet_pton($masks[$maskLength]));
        }
        return $ip;
    }

    /**
     * Hook on Tracker.Visit.setVisitorIp to anomymize visitor IP addresses
     */
    public function setVisitorIpAddress(&$ip)
    {
        $ip = self::applyIPMask($ip, Config::getInstance()->Tracker['ip_address_mask_length']);
    }
}
