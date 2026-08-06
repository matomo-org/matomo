<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration;

use Piwik\Plugins\TrackingSpamPrevention\BlockListIpRange;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TrackingSpamPrevention
 * @group BlockListIpRangeTest
 * @group Plugins
 */
class BlockListIpRangeTest extends IntegrationTestCase
{
    /**
     * @var SystemSettings
     */
    private $settings;

    /**
     * @var BlockListIpRange
     */
    private $blockList;

    public function setUp(): void
    {
        parent::setUp();

        $this->settings = new SystemSettings();
        $this->blockList = new BlockListIpRange($this->settings);
    }

    public function testIsBlocked()
    {
        $this->settings->ipBlockList->setValue(['10.10.0.0/21', '15.15.0.0/21', '2001:db8::/64']);

        $this->assertTrue($this->blockList->isBlocked('10.10.0.0'));
        $this->assertTrue($this->blockList->isBlocked('10.10.0.1'));
        $this->assertTrue($this->blockList->isBlocked('10.10.0.255'));
        $this->assertTrue($this->blockList->isBlocked('15.15.0.255'));

        $this->assertFalse($this->blockList->isBlocked('11.11.0.0'));

        $this->assertTrue($this->blockList->isBlocked('2001:db8::'));
        $this->assertTrue($this->blockList->isBlocked('2001:db8:0000:0000:ffff:ffff:ffff:fffe'));

        $this->assertFalse($this->blockList->isBlocked('2001:db8:0000:0001:ffff:ffff:ffff:fffe'));
        $this->assertFalse($this->blockList->isBlocked('2002:db8:0000:0000:ffff:ffff:ffff:fffe'));
    }

    public function testIsBlockedBareIpsMatchWithoutExplicitRange()
    {
        $this->settings->ipBlockList->setValue(['12.14.15.16', 'f::f']);

        $this->assertTrue($this->blockList->isBlocked('12.14.15.16'));
        $this->assertTrue($this->blockList->isBlocked('f::f'));

        $this->assertFalse($this->blockList->isBlocked('12.14.15.17'));
        $this->assertFalse($this->blockList->isBlocked('f::e'));
    }

    public function testIsBlockedEmptyList()
    {
        $this->assertFalse($this->blockList->isBlocked('10.10.0.0'));
    }
}
