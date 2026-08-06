<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration;

use Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;
use Piwik\Plugins\TrackingSpamPrevention\Configuration;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;
use Piwik\Plugins\TrackingSpamPrevention\Tasks;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TrackingSpamPrevention
 * @group TasksTest
 * @group Plugins
 */
class TasksTest extends IntegrationTestCase
{
    /** @var Tasks */
    private $task;

    private $settings;
    private $ranges;

    public function setUp(): void
    {
        parent::setUp();

        $this->settings = new SystemSettings();

        $ranges = [
            new BlockedIpRanges\VariableRange([
                '10.10.0.0/21',
            ]),
        ];

        $this->ranges = new BlockedIpRanges($ranges, new Configuration());
        $this->task = new Tasks($this->settings, $this->ranges);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_updateBlockedIpRanges_blockCloudDisabled_shouldEmptyQueue()
    {
        $this->ranges->setBlockedRanges(['11.' => ['11.12.10.10/1']]);
        $this->settings->block_clouds->setValue(false);
        $this->task->updateBlockedIpRanges();
        $this->assertSame([], $this->ranges->getBlockedRanges());
    }

    public function test_updateBlockedIpRanges_blockCloudEnabled()
    {
        $this->settings->block_clouds->setValue(true);
        $this->task->updateBlockedIpRanges();
        $this->assertSame(['10.' => ['10.10.0.0/21']], $this->ranges->getBlockedRanges());
    }
}
