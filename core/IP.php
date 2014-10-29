<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Piwik\Network\IPUtils;
use Piwik\Network\IPv4;
use Piwik\Network\IPv6;

/**
 * Contains IP address helper functions (for both IPv4 and IPv6).
 *
 * As of Piwik 2.9, most methods in this class are deprecated. You are
 * encouraged to use classes from the Piwik "Network" component:
 *
 * @see \Piwik\Network\IP
 * @see \Piwik\Network\IPUtils
 * @link https://github.com/piwik/component-network
 *
 * As of Piwik 1.3, IP addresses are stored in the DB has VARBINARY(16),
 * and passed around in network address format which has the advantage of
 * being in big-endian byte order. This allows for binary-safe string
 * comparison of addresses (of the same length), even on Intel x86.
 *
 * As a matter of naming convention, we use `$ip` for the network address format
 * and `$ipString` for the presentation format (i.e., human-readable form).
 *
 * We're not using the network address format (in_addr) for socket functions,
 * so we don't have to worry about incompatibility with Windows UNICODE
 * and inetPtonW().
 *
 * @api
 */
class IP
{
    /**
     * Removes the port and the last portion of a CIDR IP address.
     *
     * @param string $ipString The IP address to sanitize.
     * @return string
     *
     * @deprecated Use IPUtils::sanitizeIp() instead
     * @see \Piwik\Network\IPUtils
     */
    public static function sanitizeIp($ipString)
    {
        return IPUtils::sanitizeIp($ipString);
    }

    /**
     * Sanitize human-readable (user-supplied) IP address range.
     *
     * Accepts the following formats for $ipRange:
     * - single IPv4 address, e.g., 127.0.0.1
     * - single IPv6 address, e.g., ::1/128
     * - IPv4 block using CIDR notation, e.g., 192.168.0.0/22 represents the IPv4 addresses from 192.168.0.0 to 192.168.3.255
     * - IPv6 block using CIDR notation, e.g., 2001:DB8::/48 represents the IPv6 addresses from 2001:DB8:0:0:0:0:0:0 to 2001:DB8:0:FFFF:FFFF:FFFF:FFFF:FFFF
     * - wildcards, e.g., 192.168.0.*
     *
     * @param string $ipRangeString IP address range
     * @return string|bool  IP address range in CIDR notation OR false
     *
     * @deprecated Use IPUtils::sanitizeIpRange() instead
     * @see \Piwik\Network\IPUtils
     */
    public static function sanitizeIpRange($ipRangeString)
    {
        $result = IPUtils::sanitizeIpRange($ipRangeString);

        return $result === null ? false : $result;
    }

    /**
     * Converts an IP address in presentation format to network address format.
     *
     * @param string $ipString IP address, either IPv4 or IPv6, e.g., `"127.0.0.1"`.
     * @return string Binary-safe string, e.g., `"\x7F\x00\x00\x01"`.
     *
     * @deprecated Use IPUtils::stringToBinaryIP() instead
     * @see \Piwik\Network\IPUtils
     */
    public static function P2N($ipString)
    {
        return IPUtils::stringToBinaryIP($ipString);
    }

    /**
     * Convert network address format to presentation format.
     *
     * See also {@link prettyPrint()}.
     *
     * @param string $ip IP address in network address format.
     * @return string IP address in presentation format.
     *
     * @deprecated Use IPUtils::binaryToStringIP() instead
     */
    public static function N2P($ip)
    {
        return IPUtils::binaryToStringIP($ip);
    }

    /**
     * Alias for {@link N2P()}.
     *
     * @param string $ip IP address in network address format.
     * @return string IP address in presentation format.
     *
     * @deprecated Will be removed
     */
    public static function prettyPrint($ip)
    {
        return IPUtils::binaryToStringIP($ip);
    }

    /**
     * Returns true if `$ip` is an IPv4, IPv4-compat, or IPv4-mapped address, false
     * if otherwise.
     *
     * @param string $ip IP address in network address format.
     * @return bool True if IPv4, else false.
     *
     * @deprecated Will be removed
     * @see \Piwik\Network\IP
     */
    public static function isIPv4($ip)
    {
        $ip = Network\IP::fromBinaryIP($ip);

        return $ip instanceof IPv4;
    }

    /**
     * Converts an IP address (in network address format) to presentation format.
     * This is a backward compatibility function for code that only expects
     * IPv4 addresses (i.e., doesn't support IPv6).
     *
     * This function does not support the long (or its string representation)
     * returned by the built-in ip2long() function, from Piwik 1.3 and earlier.
     *
     * @param string $ip IPv4 address in network address format.
     * @return string IP address in presentation format.
     *
     * @deprecated This method was kept for backward compatibility and doesn't seem used
     */
    public static function long2ip($ip)
    {
        // IPv4
        if (strlen($ip) == 4) {
            return IPUtils::binaryToStringIP($ip);
        }

        // IPv6 - transitional address?
        if (strlen($ip) == 16) {
            if (substr_compare($ip, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff", 0, 12) === 0
                || substr_compare($ip, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", 0, 12) === 0
            ) {
                // remap 128-bit IPv4-mapped and IPv4-compat addresses
                return IPUtils::binaryToStringIP(substr($ip, 12));
            }
        }

        return '0.0.0.0';
    }

    /**
     * Returns true if $ip is an IPv6 address, false if otherwise. This function does
     * a naive check. It assumes that whatever format $ip is in, it is well-formed.
     *
     * @param string $ip
     * @return bool
     *
     * @deprecated Will be removed
     * @see \Piwik\Network\IP
     */
    public static function isIPv6($ip)
    {
        $ip = Network\IP::fromBinaryIP($ip);

        return $ip instanceof IPv6;
    }

    /**
     * Returns true if $ip is a IPv4 mapped address, false if otherwise.
     *
     * @param string $ip
     * @return bool
     *
     * @deprecated Will be removed
     * @see \Piwik\Network\IP
     */
    public static function isMappedIPv4($ip)
    {
        $ip = Network\IP::fromStringIP($ip);

        if (! $ip instanceof IPv6) {
            return false;
        }

        return $ip->isMappedIPv4();
    }

    /**
     * Returns an IPv4 address from a 'mapped' IPv6 address.
     *
     * @param string $ip eg, `'::ffff:192.0.2.128'`
     * @return string eg, `'192.0.2.128'`
     *
     * @deprecated Use Piwik\Network\IP::toIPv4String() instead
     * @see \Piwik\Network\IP
     */
    public static function getIPv4FromMappedIPv6($ip)
    {
        $ip = Network\IP::fromStringIP($ip);

        return $ip->toIPv4String();
    }

    /**
     * Get low and high IP addresses for a specified range.
     *
     * @param array $ipRange An IP address range in presentation format.
     * @return array|bool  Array `array($lowIp, $highIp)` in network address format, or false on failure.
     *
     * @deprecated Use Piwik\Network\IPUtils::getIPRangeBounds() instead
     * @see \Piwik\Network\IPUtils
     */
    public static function getIpsForRange($ipRange)
    {
        $result = IPUtils::getIPRangeBounds($ipRange);

        return $result === null ? false : $result;
    }

    /**
     * Determines if an IP address is in a specified IP address range.
     *
     * An IPv4-mapped address should be range checked with an IPv4-mapped address range.
     *
     * @param string $ip IP address in network address format
     * @param array $ipRanges List of IP address ranges
     * @return bool  True if in any of the specified IP address ranges; else false.
     *
     * @deprecated Use Piwik\Network\IP::isInRanges() instead
     * @see \Piwik\Network\IP
     */
    public static function isIpInRange($ip, $ipRanges)
    {
        $ip = Network\IP::fromBinaryIP($ip);

        return $ip->isInRanges($ipRanges);
    }

    /**
     * Returns the most accurate IP address availble for the current user, in
     * IPv4 format. This could be the proxy client's IP address.
     *
     * @return string IP address in presentation format.
     */
    public static function getIpFromHeader()
    {
        $clientHeaders = @Config::getInstance()->General['proxy_client_headers'];
        if (!is_array($clientHeaders)) {
            $clientHeaders = array();
        }

        $default = '0.0.0.0';
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $default = $_SERVER['REMOTE_ADDR'];
        }

        $ipString = self::getNonProxyIpFromHeader($default, $clientHeaders);
        return IPUtils::sanitizeIp($ipString);
    }

    /**
     * Returns a non-proxy IP address from header.
     *
     * @param string $default Default value to return if there no matching proxy header.
     * @param array $proxyHeaders List of proxy headers.
     * @return string
     */
    public static function getNonProxyIpFromHeader($default, $proxyHeaders)
    {
        $proxyIps = array();
        $config = Config::getInstance()->General;
        if (isset($config['proxy_ips'])) {
            $proxyIps = $config['proxy_ips'];
        }
        if (!is_array($proxyIps)) {
            $proxyIps = array();
        }

        $proxyIps[] = $default;

        // examine proxy headers
        foreach ($proxyHeaders as $proxyHeader) {
            if (!empty($_SERVER[$proxyHeader])) {
                $proxyIp = self::getLastIpFromList($_SERVER[$proxyHeader], $proxyIps);
                if (strlen($proxyIp) && stripos($proxyIp, 'unknown') === false) {
                    return $proxyIp;
                }
            }
        }

        return $default;
    }

    /**
     * Returns the last IP address in a comma separated list, subject to an optional exclusion list.
     *
     * @param string $csv Comma separated list of elements.
     * @param array $excludedIps Optional list of excluded IP addresses (or IP address ranges).
     * @return string Last (non-excluded) IP address in the list.
     */
    public static function getLastIpFromList($csv, $excludedIps = null)
    {
        $p = strrpos($csv, ',');
        if ($p !== false) {
            $elements = explode(',', $csv);
            for ($i = count($elements); $i--;) {
                $element = trim(Common::sanitizeInputValue($elements[$i]));
                $ip = \Piwik\Network\IP::fromStringIP(IPUtils::sanitizeIp($element));
                if (empty($excludedIps) || (!in_array($element, $excludedIps) && !$ip->isInRanges($excludedIps))) {
                    return $element;
                }
            }
        }
        return trim(Common::sanitizeInputValue($csv));
    }

    /**
     * Returns the hostname for a given IP address.
     *
     * @param string $ipStr Human-readable IP address.
     * @return string The hostname or unmodified $ipStr on failure.
     *
     * @deprecated Use Piwik\Network\IP::getHostname() instead
     * @see \Piwik\Network\IP
     */
    public static function getHostByAddr($ipStr)
    {
        $ip = Network\IP::fromStringIP($ipStr);

        $host = $ip->getHostname();

        return $host === null ? $ipStr : $host;
    }
}
