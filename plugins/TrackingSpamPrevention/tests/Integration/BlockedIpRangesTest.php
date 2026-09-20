<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration;

use Piwik\Config;
use Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;
use Piwik\Plugins\TrackingSpamPrevention\Configuration;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TrackingSpamPrevention
 * @group BlockedIpRangesTest
 * @group Plugins
 */
class BlockedIpRangesTest extends IntegrationTestCase
{
    /** @var BlockedIpRanges */
    private $ranges;

    public function setUp(): void
    {
        parent::setUp();

        $ranges = [
            new BlockedIpRanges\VariableRange([
                '10.10.0.0/21',
                '11.11.0.0/22',
                '127.0.0.0/23',
                '2001:db8::/42',
                '2001:db8::/126',
                '192.168.10.0/24',
            ]),
            new BlockedIpRanges\VariableRange([
                '15.15.15.0/21',
                '127.0.255.0/22',
                '2001:db9::/42',
                '2001:db7::/125',
                '2002:db7::/126',
                '190.168.0.0/23',
                '192.168.0.0/23',
            ]),
        ];

        $this->ranges = new BlockedIpRanges($ranges, new Configuration());
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetBlockedRangesNothingStored()
    {
        $this->assertSame([], $this->ranges->getBlockedRanges());
    }

    public function testSetBlockedRangesGetBlockedRangesUnsetAllIpRanges()
    {
        $this->ranges->setBlockedRanges(['10.' => ['10.10.10.10/21'], '172.' => ['172.172.0.0/22']]);
        $this->assertSame(['10.' => ['10.10.10.10/21'], '172.' => ['172.172.0.0/22']], $this->ranges->getBlockedRanges());

        $this->ranges->unsetAllIpRanges();
        $this->assertSame([], $this->ranges->getBlockedRanges());
    }

    public function testBanIp()
    {
        $this->ranges->banIp('10.10.10.10');
        $this->assertSame(['10.' => ['10.10.10.10/32']], $this->ranges->getBlockedRanges());
        $this->ranges->banIp('172.172.0.0');
        $this->assertSame(['10.' => ['10.10.10.10/32'], '172.' => ['172.172.0.0/32']], $this->ranges->getBlockedRanges());
        $this->ranges->banIp('2000::');
        $this->assertSame(['10.' => ['10.10.10.10/32'], '172.' => ['172.172.0.0/32'], '2000:' => ['2000::/128']], $this->ranges->getBlockedRanges());
        $this->ranges->banIp('172.172.1.0');
        $this->assertSame(['10.' => ['10.10.10.10/32'], '172.' => ['172.172.0.0/32', '172.172.1.0/32'], '2000:' => ['2000::/128']], $this->ranges->getBlockedRanges());
    }

    public function testBanIpWontBanSameIpTwice()
    {
        $this->ranges->banIp('10.10.10.10');
        $this->assertSame(['10.' => ['10.10.10.10/32']], $this->ranges->getBlockedRanges());

        $this->ranges->banIp('10.10.10.10');
        $this->assertSame(['10.' => ['10.10.10.10/32']], $this->ranges->getBlockedRanges());
    }

    public function testUpdateBlockedIpRanges()
    {
        $this->ranges->updateBlockedIpRanges();
        $this->assertEquals(array (
            '10.' =>
                array (
                    0 => '10.10.0.0/21',
                ),
            '11.' =>
                array (
                    0 => '11.11.0.0/22',
                ),
            '127.' =>
                array (
                    0 => '127.0.0.0/23',
                    1 => '127.0.255.0/22',
                ),
            '2001:' =>
                array (
                    0 => '2001:db8::/42',
                    1 => '2001:db8::/126',
                    2 => '2001:db9::/42',
                    3 => '2001:db7::/125',
                ),
            '192.' =>
                array (
                    0 => '192.168.10.0/24',
                    1 => '192.168.0.0/23',
                ),
            '15.' =>
                array (
                    0 => '15.15.15.0/21',
                ),
            '2002:' =>
                array (
                    0 => '2002:db7::/126',
                ),
            '190.' =>
                array (
                    0 => '190.168.0.0/23',
                ),
        ), $this->ranges->getBlockedRanges());
    }

    public function testUpdateBlockedIpRangesWithExceptionButCaught()
    {
        $ranges = [
            new BlockedIpRanges\VariableRange([
                '15.15.15.0/21',
            ]),
            new BlockedIpRanges\ExceptionRange(),
            new BlockedIpRanges\VariableRange([
                '16.16.16.0/21',
            ]),
        ];
        $this->ranges = new BlockedIpRanges($ranges, new Configuration());

        $this->ranges->updateBlockedIpRanges();
        $this->assertSame([
            '15.' => ['15.15.15.0/21'],
            '16.' => ['16.16.16.0/21'],
        ], $this->ranges->getBlockedRanges());
    }

    public function testUpdateBlockedIpRangesIgnoresValuesThatAreNotIpRanges()
    {
        $ranges = [
            new BlockedIpRanges\VariableRange([
                'notanip',
                '15.15.15.0/21',
                '',
                '1.2.3.4',
                '999.999.0.0/21',
                ['16.16.16.0/21'],
                null,
            ]),
        ];
        $this->ranges = new BlockedIpRanges($ranges, new Configuration());

        $this->ranges->updateBlockedIpRanges();
        $this->assertSame([
            '15.' => ['15.15.15.0/21'],
            '1.' => ['1.2.3.4/32'],
        ], $this->ranges->getBlockedRanges());
    }

    public function testUpdateBlockedIpRangesWithExceptionNotCaught()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to get any range');

        Config::getInstance()->TrackingSpamPrevention[Configuration::KEY_RANGE_THROW_EXCEPTION] = 1;
        $ranges = [
            new BlockedIpRanges\VariableRange([
                '15.15.15.0/21',
            ]),
            new BlockedIpRanges\ExceptionRange(),
            new BlockedIpRanges\VariableRange([
                '16.16.16.0/21',
            ]),
        ];

        $ranges = new BlockedIpRanges($ranges, new Configuration());
        $ranges->updateBlockedIpRanges();
    }

    public function testIsExcludedIpv4()
    {
        $this->ranges->banIp('10.10.10.10');
        $this->assertTrue($this->ranges->isExcluded('10.10.10.10'));
        $this->assertFalse($this->ranges->isExcluded('10.10.10.9'));
        $this->assertFalse($this->ranges->isExcluded('10.10.10.11'));
    }

    public function testIsExcludedIpv6()
    {
        $this->ranges->banIp('2001:db8::');
        $this->assertTrue($this->ranges->isExcluded('2001:db8::'));
        // banning a single IP only blocks that IP, not its neighbours
        $this->assertFalse($this->ranges->isExcluded('2001:db8::1'));
        // no range is indexed for this IP at all
        $this->assertFalse($this->ranges->isExcluded('2002:db8::'));
    }

    public function testIsExcludedManyRanges()
    {
        $this->ranges->updateBlockedIpRanges();
        $this->ranges->banIp('10.10.10.10');
        $this->assertTrue($this->ranges->isExcluded('10.10.0.0'));
        $this->assertTrue($this->ranges->isExcluded('10.10.0.1'));
        $this->assertTrue($this->ranges->isExcluded('192.168.10.0'));
        $this->assertTrue($this->ranges->isExcluded('192.168.10.255'));
        $this->assertFalse($this->ranges->isExcluded('192.168.11.0'));
        $this->assertFalse($this->ranges->isExcluded('18.18.18.17'));

        // 2001:db8::/42 spans 2001:db8:: to 2001:db8:3f:ffff:ffff:ffff:ffff:ffff
        $this->assertTrue($this->ranges->isExcluded('2001:db8:0000:0000:ffff:ffff:ffff:ffff'));
        $this->assertTrue($this->ranges->isExcluded('2001:db8:003f:ffff:ffff:ffff:ffff:ffff'));
        $this->assertFalse($this->ranges->isExcluded('2001:db8:0040::'));
        $this->assertFalse($this->ranges->isExcluded('2002:db8:0000:0000:ffff:ffff:ffff:fffe'));
    }
}
