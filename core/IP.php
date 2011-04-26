<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik
 */

if(Piwik_Common::isWindows()) {
	function _inet_ntop($in_addr) {
		return php_compat_inet_ntop($in_addr);
	}
	function _inet_pton($address) {
		return php_compat_inet_pton($address);
	}
} else {
	function _inet_ntop($in_addr) {
		return inet_ntop($in_addr);
	}
	function _inet_pton($address) {
		return inet_pton($address);
	}
}

/**
 * Handling IP addresses (both IPv4 and IPv6).
 *
 * As of Piwik 1.3, IP addresses are stored in the DB has VARBINARY(16),
 * and passed around in network address format which has the advantage of
 * being in big-endian byte order, allowing for binary-safe string
 * comparison of addresses (of the same length), even on Intel x86.
 *
 * As a matter of naming convention, we use $ip for the network address format
 * and $ipString for the presentation format (i.e., human-readable form).
 *
 * We're not using the network address format (in_addr) for socket functions,
 * so we don't have to worry about incompatibility with Windows UNICODE
 * and inetPtonW().
 *
 * @package Piwik
 */
class Piwik_IP
{
	/**
	 * Sanitize human-readable IP address.
	 *
	 * @param string $ipString IP address
	 * @return string|false
	 */
	static public function sanitizeIp($ipString)
	{
		$ipString = trim($ipString);

		// CIDR notation, A.B.C.D/E
		$posSlash = strrpos($ipString, '/');
		if($posSlash !== false)
		{
			$ipString = substr($ipString, 0, $posSlash);
		}

		$posColon = strrpos($ipString, ':');
		$posDot = strrpos($ipString, '.');
		if($posColon !== false)
		{
			// IPv6 address with port, [A:B:C:D:E:F:G:H]:EEEE
			$posRBrac = strrpos($ipString, ']');
			if($posRBrac !== false && $ipString[0] == '[')
			{
				$ipString = substr($ipString, 1, $posRBrac - 1);
			}

			if($posDot !== false)
			{
				// IPv4 address with port, A.B.C.D:EEEE
				if($posColon > $posDot)
				{
					$ipString = substr($ipString, 0, $posColon);
				}
				// else: Dotted quad IPv6 address, A:B:C:D:E:F:G.H.I.J
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
	 * @return string|false IP address range in CIDR notation
	 */
	static public function sanitizeIpRange($ipRangeString)
	{
		// in case mbstring overloads strlen function
		$strlen = function_exists('mb_orig_strlen') ? 'mb_orig_strlen' : 'strlen';

		$ipRangeString = trim($ipRangeString);
		if(empty($ipRangeString))
		{
			return false;
		}

		// IPv4 address with wildcards '*'
		if(strpos($ipRangeString, '*') !== false)
		{
			if(preg_match('~(^|\.)\*\.\d+(\.|$)~', $ipRangeString))
			{
				return false;
			}

			$bits = 32 - 8 * substr_count($ipRangeString, '*');
			$ipRangeString = str_replace('*', '0', $ipRangeString);
		}

		// CIDR
		if(($pos = strpos($ipRangeString, '/')) !== false)
		{
			$bits = substr($ipRangeString, $pos + 1);
			$ipRangeString = substr($ipRangeString, 0, $pos);
		}

		// single IP
		if(($ip = @_inet_pton($ipRangeString)) === false)
			return false;

		$maxbits = $strlen($ip) * 8;
		if(!isset($bits))
			$bits = $maxbits;

		if($bits < 0 || $bits > $maxbits)
		{
			return false;
		}

		return "$ipRangeString/$bits";
	}

	/**
	 * Convert presentation format IP address to network address format
	 *
	 * @param string $ipString IP address, either IPv4 or IPv6, e.g., "127.0.0.1"
	 * @return string Binary-safe string, e.g., "\x7F\x00\x00\x01"
	 */
	static public function P2N($ipString)
	{
		// use @inet_pton() because it throws an exception and E_WARNING on invalid input
		$ip = @_inet_pton($ipString);
		return $ip === false ? "\x00\x00\x00\x00" : $ip;
	}

	/**
	 * Convert network address format to presentation format
	 *
	 * @see prettyPrint()
	 *
	 * @param string $ip IP address in network address format
	 * @return string IP address in presentation format
	 */
	static public function N2P($ip)
	{
		// use @inet_ntop() because it throws an exception and E_WARNING on invalid input
		$ipStr = @_inet_ntop($ip);
		return $ipStr === false ? '0.0.0.0' : $ipStr;
	}

	/**
	 * Alias for N2P()
	 *
	 * @param string $ip IP address in network address format
	 * @return string IP address in presentation format
	 */
	static public function prettyPrint($ip)
	{
		return self::N2P($ip);
	}

	/**
	 * Get low and high IP addresses for a specified range.
	 *
	 * @param array $ipRange An IP address range in presentation format
	 * @return array|false Array ($lowIp, $highIp) in network address format, or false if failure
	 */
	static public function getIpsForRange($ipRange)
	{
		// in case mbstring overloads strlen and substr functions
		$strlen = function_exists('mb_orig_strlen') ? 'mb_orig_strlen' : 'strlen';

		if(strpos($ipRange, '/') === false)
		{
			$ipRange = self::sanitizeIpRange($ipRange);
		}
		$pos = strpos($ipRange, '/');

		$bits = substr($ipRange, $pos + 1);
		$range = substr($ipRange, 0, $pos);
		$high = $low = @_inet_pton($range);
		if($low === false)
		{
			return false;
		}

		$lowLen = $strlen($low);
		$i = $lowLen - 1;
		$bits = $lowLen * 8 - $bits;

		for($n = (int)($bits / 8); $n > 0; $n--, $i--)
		{
			$low[$i] = chr(0);
			$high[$i] = chr(255);
		}

		$n = $bits % 8;
		if($n)
		{
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
	 * @return bool True if in any of the specified IP address ranges; else false.
	 */
	static public function isIpInRange($ip, $ipRanges)
	{
		// in case mbstring overloads strlen and substr functions
		$strlen = function_exists('mb_orig_strlen') ? 'mb_orig_strlen' : 'strlen';

		$ipLen = $strlen($ip);
		if(empty($ip) || empty($ipRanges) || ($ipLen != 4 && $ipLen != 16))
		{
			return false;
		}

		foreach($ipRanges as $range)
		{
			if(is_array($range))
			{
				// already split into low/high IP addresses
				$range[0] = self::P2N($range[0]);
				$range[1] = self::P2N($range[1]);
			}
			else
			{
				// expect CIDR format but handle some variations
				$range = self::getIpsForRange($range);
			}
			if($range === false)
			{
				continue;
			}

			$low = $range[0];
			$high = $range[1];
			if($strlen($low) != $ipLen)
			{
				continue;
			}

			// binary-safe string comparison
			if($ip >= $low && $ip <= $high)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the best possible IP of the current user, in the format A.B.C.D
	 * For example, this could be the proxy client's IP address.
	 *
	 * @return string IP address in presentation format
	 */
	static public function getIpFromHeader()
	{
		$clientHeaders = null;
		if(!empty($GLOBALS['PIWIK_TRACKER_MODE']))
		{
			$clientHeaders = @Piwik_Tracker_Config::getInstance()->General['proxy_client_headers'];
		}
		else
		{
			$config = Zend_Registry::get('config');
			if($config !== false && isset($config->General->proxy_client_headers))
			{
				$clientHeaders = $config->General->proxy_client_headers->toArray();
			}
		}
		if(!is_array($clientHeaders))
		{
			$clientHeaders = array();
		}

		$default = '0.0.0.0';
		if(isset($_SERVER['REMOTE_ADDR']))
		{
			$default = $_SERVER['REMOTE_ADDR'];
		}

		$ipString = self::getNonProxyIpFromHeader($default, $clientHeaders);
		return self::sanitizeIp($ipString);
	}

	/**
	 * Returns a non-proxy IP address from header
	 *
	 * @param string $default Default value to return if no matching proxy header
	 * @param array $proxyHeaders List of proxy headers
	 * @return string
	 */
	static public function getNonProxyIpFromHeader($default, $proxyHeaders)
	{
		$proxyIps = null;
		if(!empty($GLOBALS['PIWIK_TRACKER_MODE']))
		{
			$proxyIps = @Piwik_Tracker_Config::getInstance()->General['proxy_ips'];
		}
		else
		{
			$config = Zend_Registry::get('config');
			if($config !== false && isset($config->General->proxy_ips))
			{
				$proxyIps = $config->General->proxy_ips->toArray();
			}
		}
		if(!is_array($proxyIps))
		{
			$proxyIps = array();
		}
		$proxyIps[] = $default;

		// examine proxy headers
		foreach($proxyHeaders as $proxyHeader)
		{
			if(!empty($_SERVER[$proxyHeader]))
			{
				$proxyIp = self::getLastIpFromList($_SERVER[$proxyHeader], $proxyIps);
				if(strlen($proxyIp) && stripos($proxyIp, 'unknown') === false)
				{
					return $proxyIp;
				}
			}
		}

		return $default;
	}

	/**
	 * Returns the last IP address in a comma separated list, subject to an optional exclusion list.
	 *
	 * @param string $csv Comma separated list of elements
	 * @param array $excludedIps Optional list of excluded IP addresses (or IP address ranges)
	 * @return string Last (non-excluded) IP address in the list
	 */
	static public function getLastIpFromList($csv, $excludedIps = null)
	{
		$p = strrpos($csv, ',');
		if($p !== false)
		{
			$elements = explode(',', $csv);
			for($i = count($elements); $i--; )
			{
				$element = trim(Piwik_Common::sanitizeInputValue($elements[$i]));
				if(empty($excludedIps) || (!in_array($element, $excludedIps) && !self::isIpInRange(self::P2N(self::sanitizeIp($element)), $excludedIps)))
				{
					return $element;
				}
			}
		}
		return trim(Piwik_Common::sanitizeInputValue($csv));
	}

	/**
	 * Get hostname for a given IP address
	 *
	 * @param string $ipStr Human-readable IP address
	 * @return string Hostname or unmodified $ipStr if failure
	 */
	static public function getHostByAddr($ipStr)
	{
		// PHP's reverse lookup supports ipv4 and ipv6
		// except on Windows before PHP 5.3
		return @gethostbyaddr($ipStr);
	}
}

/**
 * Converts a packed internet address to a human readable representation
 *
 * @link http://php.net/inet_ntop
 *
 * @param string $in_addr 32-bit IPv4 or 128-bit IPv6 address
 * @return string|false string representation of address or false on failure
 */
function php_compat_inet_ntop($in_addr)
{
	switch (strlen($in_addr)) {
		case 4:
			$r = str_split(bin2hex($in_addr), 2);
			$r = array_map('hexdec', $r);
			$r = implode('.', $r);
			return $r;

		case 16:
			$r = bin2hex($in_addr);

			// IPv4-mapped address
			if(substr_compare($r, '00000000000000000000ffff', 0, 24) === 0)
			{
				$r = str_split(substr($r, 24), 2);
				$r = array_map('hexdec', $r);
				$r = implode('.', $r);
				return '::ffff:' . $r;
			}

			$r = str_split($r, 4);
			$r = implode(':', $r);

			// compress leading zeros
			$r = preg_replace(
				'/(^|:)0{1,3}/',
				'$1',
				$r
			);

			// compress groups of zeros
			if(preg_match_all('/(?:^|:)(0(:|$))+/', $r, $matches))
			{
				$longestMatch = 0;
				foreach($matches[0] as $aMatch)
				{
					if(strlen($aMatch) > strlen($longestMatch))
					{
						$longestMatch = $aMatch;
					}
				}
				$r = substr_replace($r, '::', strpos($r, $longestMatch), strlen($longestMatch));
			}

			return $r;
	}

	return false;
}

/**
 * Converts a human readable IP address to its packed in_addr representation
 *
 * @link http://php.net/inet_pton
 *
 * @param string $address a human readable IPv4 or IPv6 address
 * @return string in_addr representation or false on failure
 */
function php_compat_inet_pton($address)
{
	if(empty($address) || strspn($address, '01234567890abcdefABCDEF:.') !== strlen($address))
	{
		return false;
	}

	// IPv4
	if(preg_match('/^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$/i', $address, $matches))
	{
		for($i = count($matches); $i-- > 1; )
		{
			if($matches[$i] > 255 ||
				($matches[$i][0] == '0' && strlen($matches[$i]) > 1))
			{
				return false;
			}
		}

		$r = ip2long($address);
		if ($r === false)
		{
			return false;
		}
		return pack('N', $r);
	}


	// IPv6
	$colonCount = substr_count($address, ':');
	if($colonCount < 2 || $colonCount > 7 ||
		strpos($address, ':::') !== false ||
		substr_count($address, '::') > 1)
	{
		return false;
	}

	if(substr($address, 0, 2) == '::')
	{
		$address = '0'.$address;
	}
	else if($address[0] == ':')
	{
		return false;
	}

	if(substr($address, -2)  == '::')
	{
		$address .= '0';
	}
	else if(substr($address, -1) == ':')
	{
		return false;
	}

	$looksLikeIpv4Mapped = false;
	if(preg_match('/:ffff:([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$/i', $address, $matches))
	{
		for($i = count($matches); $i-- > 1; )
		{
			if($matches[$i] > 255 ||
				($matches[$i][0] == '0' && strlen($matches[$i]) > 1))
			{
				return false;
			}
		}

		$looksLikeIpv4Mapped = true;
		$address = substr_replace($address, ':ffff:' . dechex($matches[1]) . sprintf("%02x", $matches[2]) . ':' . dechex($matches[3]) . sprintf("%02x", $matches[4]), strrpos($address, $matches[0]));
	}
	if(strpos($address, '.') !== false)
	{
		return false;
	}

	$r = explode(':', $address);
	$count = count($r);
	if($count > 8)
	{
		return false;
	}
	if($count < 8)
	{
		// grouped zeros
		$zeroGroup = array_search('', $r, 1);
		if($zeroGroup === false)
		{
			return false;
		}

		array_splice($r, $zeroGroup, 1, array_fill(0, 8 - $count + 1, '0'));
	}

	// leading zeros
	foreach($r as $k => $v)
	{
		if(strlen($v) > 4)
		{
			return false;
		}
		$r[$k] = str_pad($v, 4, '0', STR_PAD_LEFT);
	}

	$r = implode(array_map(create_function('$v', 'return pack("H*", $v);'), $r));

	if($looksLikeIpv4Mapped && @substr_compare($r, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00", 0, 10) !== 0)
	{
		return false;
	}

	return $r;
}
