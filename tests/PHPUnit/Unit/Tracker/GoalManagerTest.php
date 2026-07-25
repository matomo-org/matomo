<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Tracker;

use Piwik\Tests\Framework\TestCase\UnitTestCase;
use Piwik\Tracker\GoalManager;

/**
 * @group Core
 */
class GoalManagerTest extends UnitTestCase
{
    private function makeGoalManager()
    {
        return new class extends GoalManager {
            public function exposeGetRevenue($revenue)
            {
                return $this->getRevenue($revenue);
            }
        };
    }

    /**
     * @dataProvider getInRangeRevenueData
     */
    public function testGetRevenueKeepsValuesWithinCap($revenue, $expected)
    {
        $this->assertEquals($expected, $this->makeGoalManager()->exposeGetRevenue($revenue));
    }

    public function getInRangeRevenueData()
    {
        return [
            'normal price'             => [40, 40],
            'decimal price is rounded' => [9.999, 10],
            'negative price kept'      => [-50, -50],
            'zero'                     => [0, 0],
            'exactly at the cap'       => [GoalManager::MAX_ALLOWED_REVENUE, GoalManager::MAX_ALLOWED_REVENUE],
        ];
    }

    /**
     * @dataProvider getOutOfRangeRevenueData
     */
    public function testGetRevenueRejectsValuesAboveCap($revenue)
    {
        $this->assertSame(0, $this->makeGoalManager()->exposeGetRevenue($revenue));
    }

    public function getOutOfRangeRevenueData()
    {
        return [
            'just above the cap'      => [GoalManager::MAX_ALLOWED_REVENUE + 1],
            'negative above the cap'  => [-GoalManager::MAX_ALLOWED_REVENUE - 1],
            'overflow value'          => ['1e308'],
            'negative overflow value' => ['-1e308'],
        ];
    }
}
