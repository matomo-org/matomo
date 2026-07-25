<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\tests\Unit\Columns;

use Piwik\Plugins\Ecommerce\Columns\BaseConversion;
use Piwik\Tests\Framework\TestCase\UnitTestCase;
use Piwik\Tracker\GoalManager;

/**
 * @group Ecommerce
 */
class BaseConversionTest extends UnitTestCase
{
    private function makeConversion()
    {
        return new class extends BaseConversion {
            public function exposeRoundRevenueIfNeeded($revenue)
            {
                return $this->roundRevenueIfNeeded($revenue);
            }
        };
    }

    /**
     * @dataProvider getInRangeRevenueData
     */
    public function testRoundRevenueIfNeededKeepsValuesWithinCap($revenue, $expected)
    {
        $this->assertEquals($expected, $this->makeConversion()->exposeRoundRevenueIfNeeded($revenue));
    }

    public function getInRangeRevenueData()
    {
        return [
            'decimal is rounded'  => [25.559, 25.56],
            'integer kept'        => [40, 40],
            'negative kept'       => [-50, -50],
            'exactly at the cap'  => [GoalManager::MAX_ALLOWED_REVENUE, GoalManager::MAX_ALLOWED_REVENUE],
        ];
    }

    /**
     * @dataProvider getOutOfRangeRevenueData
     */
    public function testRoundRevenueIfNeededRejectsValuesAboveCap($revenue)
    {
        $this->assertFalse($this->makeConversion()->exposeRoundRevenueIfNeeded($revenue));
    }

    public function getOutOfRangeRevenueData()
    {
        return [
            'false is unchanged'      => [false],
            'just above the cap'      => [GoalManager::MAX_ALLOWED_REVENUE + 1],
            'negative above the cap'  => [-GoalManager::MAX_ALLOWED_REVENUE - 1],
            'overflow value'          => ['1e308'],
            'negative overflow value' => ['-1e308'],
        ];
    }
}
