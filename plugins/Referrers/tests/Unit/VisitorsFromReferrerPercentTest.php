<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\Unit;

use Piwik\DataTable\Row;
use Piwik\Plugins\Referrers\Columns\Metrics\VisitorsFromReferrerPercent;

/**
 * @group Referrers
 */
class VisitorsFromReferrerPercentTest extends \PHPUnit\Framework\TestCase
{
    private const SOURCE_COLUMN = 'Referrers_visitorsFromSearchEngines';

    /**
     * @dataProvider getComputeTestData
     */
    public function testComputePreservesPrecisionForSmallShares($visits, $totalVisits, $expectedQuotient)
    {
        $metric = new VisitorsFromReferrerPercent('name', self::SOURCE_COLUMN, $totalVisits);

        $row = new Row([Row::COLUMNS => [self::SOURCE_COLUMN => $visits]]);

        $this->assertEqualsWithDelta($expectedQuotient, $metric->compute($row), 1e-9);
    }

    public function getComputeTestData(): array
    {
        return [
            // A very small but non-zero share (0.4%) must not collapse to 0. This is the
            // regression this metric fixes: 4 / 1000 = 0.004 used to be rounded to 0.00.
            'small non-zero share stays non-zero' => [4, 1000, 0.004],
            'sub-percent share' => [4, 10000, 0.0004],
            'even third keeps two percent decimals' => [1, 3, 0.3333],
            'exact share' => [250, 1000, 0.25],
            'zero visits' => [0, 1000, 0],
            'zero total avoids division by zero' => [5, 0, 0],
        ];
    }
}
