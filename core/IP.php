<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

/**
 * Contains IP address helper functions (for both IPv4 and IPv6).
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
    const MAPPED_IPv4_START = '::ffff:';

    /**
     * Removes the port and the last portion of a CIDR IP address.
     *
     * @param string $ipString The IP address to sanitize.
     * @return string
     */
    public static function sanitizeIp($ipString)
    {
        $ipString = trim($ipString);

        // CIDR notation, A.B.C.D/E
        $posSlash = strrpos($ipString, '/');
        if ($posSlash !== false) {
            $ipString = substr($ipString, 0, $posSlash);
        }

        $posColon = strrpos($ipString, ':');
        $posDot = strrpos($ipString, '.');
        if ($posColon !== false) {
            // IPv6 address with port, [A:B:C:D:E:F:G:H]:EEEE
            $posRBrac = strrpos($ipString, ']');
            if ($posRBrac !== false && $ipString[0] == '[') {
                $ipString = substr($ipString, 1, $posRBrac - 1);
            }

            if ($posDot !== false) {
                // IPv4 address with port, A.B.C.D:EEEE
                if ($posColon > $posDot) {
                    $ipString = substr($ipString, 0, $posColon);
                }
                // else: Dotted quad IPv6 address, A:B:C:D:E:F:G.H.I.J
            } else if (strpos($ipString, ':') === $posColon) {
                $ipString = substr($ipString, 0, $posColon);
            }
            // else: IPv6 address, A:B:C:D:E:F:G:H
        }
        // else: IPv4 address, A.B.C.D

        return $ipString;
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
     */
    public static function sanitizeIpRange($ipRangeString)
    {
        $ipRangeString = trim($ipRangeString);
        if (empty($ipRangeString)) {
            return false;
        }

        // IPv4 address with wildcards '*'
        if (strpos($ipRangeString, '*') !== false) {
            if (preg_match('~(^|\.)\*\.\d+(\.|$)~D', $ipRangeString)) {
                return false;
            }

            $bits = 32 - 8 * substr_count($ipRangeString, '*');
            $ipRangeString = str_replace('*', '0', $ipRangeString);
        }

        // CIDR
        if (($pos = strpos($ipRangeString, '/')) !== false) {
            $bits = substr($ipRangeString, $pos + 1);
            $ipRangeString = substr($ipRangeString, 0, $pos);
        }

        // single IP
        if (($ip = @inet_pton($ipRangeString)) === false)
            return false;

        $maxbits = strlen($ip) * 8;
        if (!isset($bits))
            $bits = $maxbits;

        if ($bits < 0 || $bits > $maxbits) {
            return false;
        }

        return "$ipRangeString/$bits";
    }

    /**
     * Converts an IP address in presentation format to network address format.
     *
     * @param string $ipString IP address, either IPv4 or IPv6, e.g., `"127.0.0.1"`.
     * @return string Binary-safe string, e.g., `"\x7F\x00\x00\x01"`.
     */
    public static function P2N($ipString)
    {
        // use @inet_pton() because it throws an exception and E_WARNING on invalid input
        $ip = @inet_pton($ipString);
        return $ip === false ? "\x00\x00\x00\x00" : $ip;
    }

    /**
     * Convert network address format to presentation format.
     *
     * See also {@link prettyPrint()}.
     *
     * @param string $ip IP address in network address format.
     * @return string IP address in presentation format.
     */
    public static function N2P($ip)
    {
        // use @inet_ntop() because it throws an exception and E_WARNING on invalid input
        $ipStr = @inet_ntop($ip);
        return $ipStr === false ? '0.0.0.0' : $ipStr;
    }

    /**
     * Alias for {@link N2P()}.
     *
     * @param string $ip IP address in network address format.
     * @return string IP address in presentation format.
     */
    public static function prettyPrint($ip)
    {
        return self::N2P($ip);
    }

    /**
     * Returns true if `$ip` is an IPv4, IPv4-compat, or IPv4-mapped address, false
     * if otherwise.
     *
     * @param string $ip IP address in network address format.
     * @return bool True if IPv4, else false.
     */
    public static function isIPv4($ip)
    {
        // in case mbstring overloads strlen function
        $strlen = function_exists('mb_orig_strlen') ? 'mb_orig_strlen' : 'strlen';

        // IPv4
        if ($strlen($ip) == 4) {
            return true;
        }

        // IPv6 - transitional address?
        if ($strlen($ip) == 16) {
            if (substr_compare($ip, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff", 0, 12) === 0
                || substr_compare($ip, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", 0, 12) === 0
            ) {
                return true;
            }
        }

        return false;
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
     */
    public static function long2ip($ip)
    {
        // IPv4
        if (strlen($ip) == 4) {
            return self::N2P($ip);
        }

        // IPv6 - transitional address?
        if (strlen($ip) == 16) {
            if (substr_compare($ip, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff", 0, 12) === 0
                || substr_compare($ip, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", 0, 12) === 0
            ) {
                // remap 128-bit IPv4-mapped and IPv4-compat addresses
                return self::N2P(substr($ip, 12));
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
     */
    public static function isIPv6($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * Returns true if $ip is a IPv4 mapped address, false if otherwise.
     *
     * @param string $ip
     * @return bool
     */
    public static function isMappedIPv4($ip)
    {
        return substr($ip, 0, strlen(self::MAPPED_IPv4_START)) === self::MAPPED_IPv4_START;
    }

    /**
     * Returns an IPv4 address from a 'mapped' IPv6 address.
     *
     * @param string $ip eg, `'::ffff:192.0.2.128'`
     * @return string eg, `'192.0.2.128'`
     */
    public static function getIPv4FromMappedIPv6($ip)
    {
        return substr($ip, strlen(self::MAPPED_IPv4_START));
    }

    /**
     * Get low and high IP addresses for a specified range.
     *
     * @param array $ipRange An IP address range in presentation format.
     * @return array|bool  Array `array($lowIp, $highIp)` in network address format, or false on failure.
     */
    public static function getIpsForRange($ipRange)
    {
        if (strpos($ipRange, '/') === false) {
            $ipRange = self::sanitizeIpRange($ipRange);
        }
        $pos = strpos($ipRange, '/');

        $bits = substr($ipRange, $pos + 1);
        $range = substr($ipRange, 0, $pos);
        $high = $low = @inet_pton($range);
        if ($low === false) {
            return false;
        }

        $lowLen = strlen($low);
        $i = $lowLen - 1;
        $bits = $lowLen * 8 - $bits;

        for ($n = (int)($bits / 8); $n > 0; $n--, $i--) {
            $low[$i] = chr(0);
            $high[$i] = chr(255);
        }

        $n = $bits % 8;
        if ($n) {
            $low[$i] = chr(ord($low[$i]) & ~((1 << $n) - 1));
            $high[$i] = chr(ord($high[$i]) | ((1 << $n) - 1));
        }

        return array($low, $high);
    }

    /**
     * Determines if an IP address is in a specified IP address range.
     *
     * An IPv4-mapped address should be range checked with an IPv4-mapped address range.
     *
     * @param string $ip IP address in network address format
     * @param array $ipRanges List of IP address ranges
     * @return bool  True if in any of the specified IP address ranges; else false.
     */
    public static function isIpInRange($ip, $ipRanges)
    {
        $ipLen = strlen($ip);
        if (empty($ip) || empty($ipRanges) || ($ipLen != 4 && $ipLen != 16)) {
            return false;
        }

        foreach ($ipRanges as $range) {
            if (is_array($range)) {
                // already split into low/high IP addresses
                $range[0] = self::P2N($range[0]);
                $range[1] = self::P2N($range[1]);
            } else {
                // expect CIDR format but handle some variations
                $range = self::getIpsForRange($range);
            }
            if ($range === false) {
                continue;
            }

            $low = $range[0];
            $high = $range[1];
            if (strlen($low) != $ipLen) {
                continue;
            }

            // binary-safe string comparison
            if ($ip >= $low && $ip <= $high) {
                return true;
            }
        }

        return false;
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
        return self::sanitizeIp($ipString);
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
        if(isset($config['proxy_ips'])) {
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
                if (empty($excludedIps) || (!in_array($element, $excludedIps) && !self::isIpInRange(self::P2N(self::sanitizeIp($element)), $excludedIps))) {
                    return $element;
                }
            }
        }
        return trim(Common::sanitizeInputValue($csv));
    }

    /**
     * Retirms the hostname for a given IP address.
     *
     * @param string $ipStr Human-readable IP address.
     * @return string The hostname or unmodified $ipStr on failure.
     */
    public static function getHostByAddr($ipStr)
    {
        // PHP's reverse lookup supports ipv4 and ipv6
        // except on Windows before PHP 5.3
        $host = strtolower(@gethostbyaddr($ipStr));
        return $host === '' ? $ipStr : $host;
    }
}
