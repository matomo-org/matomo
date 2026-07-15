<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Http;

use Exception;

/**
 * Validates that an outgoing request target is a public host, for the SSRF-safe fetch
 * path in {@see \Piwik\Http}. Canonicalises the host to what the transport will connect
 * to, rejects encoded/numeric hosts, and confirms that every resolved address is public.
 */
class EgressHostValidator
{
    // IPv4 ranges filter_var does not flag but an SSRF-safe fetch must not reach (CGNAT, IETF protocol, benchmarking).
    private const EXTRA_BLOCKED_CIDRS = ['100.64.0.0/10', '192.0.0.0/24', '198.18.0.0/15'];

    // IPv6 transition ranges that can smuggle a private IPv4 destination past filter_var (6to4, Teredo, NAT64).
    private const EXTRA_BLOCKED_IPV6_CIDRS = ['2002::/16', '2001::/32', '64:ff9b::/96'];

    /** @var callable(string): string[] */
    private $resolver;

    /**
     * @param callable|null $resolver Host-to-IPs resolver, defaults to DNS. Injectable for tests.
     */
    public function __construct(?callable $resolver = null)
    {
        $this->resolver = $resolver ?? [self::class, 'resolveHostIpsViaDns'];
    }

    /**
     * Canonicalises and validates a host for connection pinning. Fails with an exception
     * when the host is unparseable or resolves to a non-public address.
     *
     * @return array{0: string, 1: string} `[canonicalHost, pinnedIp]`; equal when the host is an IP literal (no pin needed).
     */
    public function resolveTarget(string $host): array
    {
        $host = trim($host, '[]');
        if ($host === '') {
            throw new Exception('Refusing to fetch: empty host.');
        }

        // Fold IDN to the ASCII form the transport parses, so validation and pin match it.
        if (preg_match('/[^\x20-\x7e]/', $host)) {
            if (!function_exists('idn_to_ascii')) {
                throw new Exception('Refusing to fetch: cannot normalise internationalised host without the intl extension.');
            }
            $ascii = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if ($ascii === false || $ascii === '') {
                throw new Exception('Refusing to fetch: host cannot be converted to ASCII.');
            }
            $host = $ascii;
        }

        // Normalise to the lowercase, dot-trimmed form the pin keys on, so it matches the lookup.
        $host = rtrim(strtolower($host), '.');
        if ($host === '') {
            throw new Exception('Refusing to fetch: empty host.');
        }

        // An IP literal connects directly (no DNS, no rebinding window): validate it, skip pinning.
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!self::isPublicIp($host)) {
                throw new Exception('Refusing to fetch: host resolves to a private or reserved address.');
            }
            return [$host, $host];
        }

        // Reject numeric/hex hosts (e.g. 2130706433, 0x7f000001, 127.1) since libc treats these as IP
        // literals via inet_aton, which would sidestep the resolve pin.
        if (preg_match('/^0x[0-9a-f]+$/i', $host) || preg_match('/^[0-9.]+$/', $host)) {
            throw new Exception('Refusing to fetch: numeric or encoded host is not allowed.');
        }

        if (!preg_match('/^(?=.{1,253}$)([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/i', $host)) {
            throw new Exception('Refusing to fetch: host is not a valid IP or DNS name.');
        }

        $ips = ($this->resolver)($host);
        if (empty($ips)) {
            throw new Exception('Refusing to fetch: host could not be resolved.');
        }
        foreach ($ips as $ip) {
            if (!self::isPublicIp($ip)) {
                throw new Exception('Refusing to fetch: host resolves to a private or reserved address.');
            }
        }

        // Pin the first address so the connection cannot be rebound between validation and connect.
        return [$host, $ips[0]];
    }

    public static function isPublicIp(string $ip): bool
    {
        // Unwrap IPv4-mapped IPv6 (e.g. ::ffff:169.254.169.254); PHP < 8.1 does not flag these.
        // @todo When min PHP version >= 8.1 (Matomo 6), verify filter_var() flags IPv4-mapped IPv6
        //      in the reserved/private ranges natively - if so, remove this block and rely on filter_var()
        if (stripos($ip, '::ffff:') === 0) {
            $mapped = substr($ip, 7);
            if (filter_var($mapped, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $ip = $mapped;
            }
        }

        if (false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        foreach (self::EXTRA_BLOCKED_CIDRS as $cidr) {
            if (self::ipv4InCidr($ip, $cidr)) {
                return false;
            }
        }

        foreach (self::EXTRA_BLOCKED_IPV6_CIDRS as $cidr) {
            if (self::ipv6InCidr($ip, $cidr)) {
                return false;
            }
        }

        return true;
    }

    private static function ipv4InCidr(string $ip, string $cidr): bool
    {
        $ipLong = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? ip2long($ip) : false;
        if ($ipLong === false) {
            return false; // not IPv4; the extra list is IPv4-only
        }

        [$subnet, $bits] = explode('/', $cidr);
        $mask = -1 << (32 - (int) $bits);

        return ($ipLong & $mask) === (ip2long($subnet) & $mask);
    }

    private static function ipv6InCidr(string $ip, string $cidr): bool
    {
        $ipBin = @inet_pton($ip);
        if ($ipBin === false || strlen($ipBin) !== 16) {
            return false; // not IPv6; the extra list is IPv6-only
        }

        // EXTRA_BLOCKED_IPV6_CIDRS uses byte-aligned prefixes only, so a prefix-byte compare suffices.
        [$subnet, $bits] = explode('/', $cidr);
        $subnetBin = @inet_pton($subnet);

        return $subnetBin !== false && strncmp($ipBin, $subnetBin, intdiv((int) $bits, 8)) === 0;
    }

    /**
     * @return string[]
     */
    private static function resolveHostIpsViaDns(string $host): array
    {
        $ips = [];

        $ipv4 = @gethostbynamel($host);
        if (is_array($ipv4)) {
            $ips = $ipv4;
        }

        $records = @dns_get_record($host, DNS_AAAA);
        if (is_array($records)) {
            foreach ($records as $record) {
                if (!empty($record['ipv6'])) {
                    $ips[] = (string) $record['ipv6'];
                }
            }
        }

        return $ips;
    }
}
