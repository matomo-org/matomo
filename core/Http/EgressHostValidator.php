<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Http;

use Matomo\Network\IP;
use Piwik\Config\GeneralConfig;

/**
 * Validates that an outgoing request target is a public host, for the SSRF-safe fetch
 * path in {@see \Piwik\Http}. Canonicalises the host to what the transport will connect
 * to, rejects encoded/numeric hosts, and confirms that every resolved address is public
 * or covered by the `[General] allowed_private_egress_ranges` config allowlist.
 */
class EgressHostValidator
{
    // Ranges filter_var does not flag but an SSRF-safe fetch must not reach: CGNAT, IETF protocol,
    // TEST-NET, benchmarking, multicast, documentation, discard, reserved space, plus IPv4-mapped/
    // compatible and transition ranges (6to4, Teredo, NAT64) that can insert a private IPv4 target.
    private const EXTRA_BLOCKED_RANGES = [
        '100.64.0.0/10',
        '192.0.0.0/24',
        '192.0.2.0/24',
        '192.88.99.0/24',
        '198.18.0.0/15',
        '198.51.100.0/24',
        '203.0.113.0/24',
        '224.0.0.0/4',
        '::/96',
        '::ffff:0:0/96',
        '2002::/16',
        '2001::/32',
        '2001:db8::/32',
        '64:ff9b::/96',
        '64:ff9b:1::/48',
        '100::/64',
        'fe00::/8',
        'ff00::/8',
    ];

    /** @var callable(string): string[] */
    private $resolver;

    /** @var string[] */
    private $allowedPrivateRanges;

    /**
     * @param callable|null $resolver Host-to-IPs resolver, defaults to DNS. Injectable for tests.
     * @param string[]|null $allowedPrivateRanges Non-public addresses to accept anyway (IPs, CIDR or
     *                                            wildcard ranges), defaults to the config allowlist.
     */
    public function __construct(?callable $resolver = null, ?array $allowedPrivateRanges = null)
    {
        $this->resolver = $resolver ?? [self::class, 'resolveHostIpsViaDns'];
        $this->allowedPrivateRanges = $allowedPrivateRanges
            ?? GeneralConfig::getArrayConfigValue('allowed_private_egress_ranges', []);
    }

    /**
     * Canonicalises and validates a host for connection pinning.
     *
     * @return array{0: string, 1: string} `[canonicalHost, pinnedIp]`; equal when the host is an IP literal (no pin needed).
     * @throws EgressBlockedException when the host is unparseable or resolves to a non-public address.
     */
    public function resolveTarget(string $host): array
    {
        $host = trim($host, '[]');
        if ($host === '') {
            throw new EgressBlockedException('Refusing to fetch: empty host.');
        }

        // Fold IDN to the ASCII form the transport parses, so validation and pin match it.
        if (preg_match('/[^\x20-\x7e]/', $host)) {
            if (!function_exists('idn_to_ascii')) {
                throw new EgressBlockedException('Refusing to fetch: cannot normalise internationalised host without the intl extension.');
            }
            $ascii = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if ($ascii === false || $ascii === '') {
                throw new EgressBlockedException('Refusing to fetch: host cannot be converted to ASCII.');
            }
            $host = $ascii;
        }

        // Normalise to the lowercase, dot-trimmed form the pin keys on, so it matches the lookup.
        $host = rtrim(strtolower($host), '.');
        if ($host === '') {
            throw new EgressBlockedException('Refusing to fetch: empty host.');
        }

        // An IP literal connects directly (no DNS, no rebinding window): validate it, skip pinning.
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!$this->isAllowedIp($host)) {
                throw new EgressBlockedException('Refusing to fetch: host resolves to a private or reserved address.');
            }
            return [$host, $host];
        }

        // Reject numeric/hex hosts (e.g. 2130706433, 0x7f000001, 127.1) since libc treats these as IP
        // literals via inet_aton, which would sidestep the resolve pin.
        if (preg_match('/^0x[0-9a-f]+$/i', $host) || preg_match('/^[0-9.]+$/', $host)) {
            throw new EgressBlockedException('Refusing to fetch: numeric or encoded host is not allowed.');
        }

        if (!preg_match('/^(?=.{1,253}$)([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/i', $host)) {
            throw new EgressBlockedException('Refusing to fetch: host is not a valid IP or DNS name.');
        }

        $ips = ($this->resolver)($host);
        if (empty($ips)) {
            throw new EgressBlockedException('Refusing to fetch: host could not be resolved.');
        }
        foreach ($ips as $ip) {
            if (!$this->isAllowedIp($ip)) {
                throw new EgressBlockedException('Refusing to fetch: host resolves to a private or reserved address.');
            }
        }

        // Pin the first address so the connection cannot be rebound between validation and connect.
        return [$host, $ips[0]];
    }

    private function isAllowedIp(string $ip): bool
    {
        if (self::isPublicIp($ip)) {
            return true;
        }

        // matched per address family, so an IPv4-mapped IPv6 address cannot match an IPv4 range
        return !empty($this->allowedPrivateRanges)
            && IP::fromStringIP($ip)->isInRanges($this->allowedPrivateRanges);
    }

    public static function isPublicIp(string $ip): bool
    {
        if (false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        return !IP::fromStringIP($ip)->isInRanges(self::EXTRA_BLOCKED_RANGES);
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
