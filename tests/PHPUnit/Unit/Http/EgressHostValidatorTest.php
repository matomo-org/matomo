<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Http;

use Exception;
use Piwik\Http\EgressHostValidator;

/**
 * @group Core
 */
class EgressHostValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getIsPublicIpTestData
     */
    public function testIsPublicIp($ip, $expected)
    {
        $this->assertSame($expected, EgressHostValidator::isPublicIp($ip));
    }

    public function getIsPublicIpTestData()
    {
        return array(
            // public
            array('93.184.216.34', true),
            array('2606:2800:220:1:248:1893:25c8:1946', true),
            // loopback / private / link-local / reserved
            array('127.0.0.1', false),
            array('10.0.0.1', false),
            array('192.168.1.1', false),
            array('172.16.5.4', false),
            array('169.254.169.254', false),
            array('0.0.0.0', false),
            array('::1', false),
            // IPv4-mapped IPv6 pointing at private space is blocked in any textual form
            array('::ffff:169.254.169.254', false),
            array('::ffff:a9fe:a9fe', false),
            array('0:0:0:0:0:ffff:a9fe:a9fe', false),
            array('::ffff:7f00:1', false), // 127.0.0.1
            array('::ffff:5db8:d822', true), // 93.184.216.34, mapped public IPv4 stays allowed
            array('::ffff:6440:1', false), // 100.64.0.1, mapped CGNAT hits the extra IPv4 ranges
            // deprecated IPv4-compatible space (::/96)
            array('::a9fe:a9fe', false),
            array('::7f00:1', false),
            // extra IPv4 ranges filter_var does not flag (CGNAT, IETF protocol, benchmarking)
            array('100.64.0.1', false),
            array('192.0.0.1', false),
            array('198.18.0.1', false),
            // IPv6 transition ranges that could smuggle a private IPv4 destination
            array('2002:7f00:0001::', false), // 6to4 wrapping 127.0.0.1
            array('2001:0:1234::', false),    // Teredo
            array('64:ff9b::a9fe:a9fe', false), // NAT64 wrapping 169.254.169.254
            // TEST-NET / documentation ranges and 6to4 relay anycast
            array('192.0.2.1', false),
            array('198.51.100.1', false),
            array('203.0.113.1', false),
            array('192.88.99.1', false),
            array('2001:db8::1', false),
            // multicast
            array('224.0.0.1', false),
            array('239.255.255.250', false),
            array('ff02::1', false),
            // IPv6 discard and reserved fe00::/8 (link-local, site-local, unallocated)
            array('100::1', false),
            array('fe00::1', false), // bottom of fe00::/8
            array('fec0::1', false), // site-local
            array('feff::1', false), // top of fe00::/8
            // global unicast (2000::/3) must stay public
            array('2001:4860:4860::8888', true),
        );
    }

    /**
     * @dataProvider getResolveTargetTestData
     */
    public function testResolveTarget($host, $stubbedIps, $expected)
    {
        $validator = new EgressHostValidator(static function (string $host) use ($stubbedIps): array {
            return $stubbedIps[$host] ?? array();
        });

        if ($expected === false) {
            $this->expectException(Exception::class);
            $validator->resolveTarget($host);
            return;
        }

        $this->assertSame($expected, $validator->resolveTarget($host));
    }

    public function testResolveTargetAcceptsAdditionalAllowedIps()
    {
        $resolver = static function (): array {
            return array('127.0.0.1');
        };

        $validator = new EgressHostValidator($resolver, array('127.0.0.1'));
        $this->assertSame(array('local.test', '127.0.0.1'), $validator->resolveTarget('local.test'));

        $this->expectException(Exception::class);
        (new EgressHostValidator($resolver))->resolveTarget('local.test');
    }

    public function getResolveTargetTestData()
    {
        return array(
            // public IP literal: connects directly, no pin needed (canonicalHost === pinnedIp)
            'public ipv4 literal' => array('93.184.216.34', array(), array('93.184.216.34', '93.184.216.34')),
            // private / reserved IP literals are rejected
            'loopback literal' => array('127.0.0.1', array(), false),
            'metadata literal' => array('169.254.169.254', array(), false),
            // encoded numeric hosts that libc treats as IP literals are rejected before resolution
            'decimal encoded ip' => array('2130706433', array(), false),
            'hex encoded ip' => array('0x7f000001', array(), false),
            'octal encoded ip' => array('0177.0.0.1', array(), false),
            'short form ip' => array('127.1', array(), false),
            // DNS host resolving to a public address: pin the host to that address
            'public dns host' => array('example.com', array('example.com' => array('93.184.216.34')), array('example.com', '93.184.216.34')),
            // host is normalised (lowercased, trailing dot stripped) for both validation and pin
            'uppercase dns host' => array('ExAmple.COM', array('example.com' => array('93.184.216.34')), array('example.com', '93.184.216.34')),
            'trailing dot dns host' => array('example.com.', array('example.com' => array('93.184.216.34')), array('example.com', '93.184.216.34')),
            // any private address in the resolved set is rejected (rebinding / mixed answers)
            'dns host private answer' => array('evil.test', array('evil.test' => array('127.0.0.1')), false),
            'dns host mixed answers' => array('evil.test', array('evil.test' => array('93.184.216.34', '10.0.0.5')), false),
            // DNS host that does not resolve is rejected
            'unresolvable host' => array('nowhere.invalid', array(), false),
        );
    }
}
