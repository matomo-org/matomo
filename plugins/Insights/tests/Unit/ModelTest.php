<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Insights\tests\Unit;

use Piwik\Plugins\API\ProcessedReport;
use Piwik\Plugins\Insights\Model;

/**
 * @group Insights
 * @group ModelTest
 * @group Unit
 */
class ModelTest extends BaseUnitTest
{
    /**
     * @dataProvider getComparableDates
     */
    public function testIsComparableDate(string $period, string $date, string $comparedDate, bool $expected)
    {
        $model = new Model($this->createMock(ProcessedReport::class));

        $this->assertSame($expected, $model->isComparableDate($period, $date, $comparedDate));
    }

    public function getComparableDates(): array
    {
        return [
            'a date of the same period type' => ['month', '2012-08-09', '2011-08-09', true],
            'a date range for a single period type' => ['day', '2012-08-09', '2012-08-01,2012-08-02', false],
            'several periods' => ['day', '2012-08-09', 'last7', false],
            'an invalid date' => ['day', '2012-08-09', 'not a date', false],
            'a range of the same length' => ['range', '2012-08-01,2012-08-31', '2011-08-01,2011-08-31', true],
            'a range of another length' => ['range', '2012-08-01,2012-08-31', '2011-08-01,2011-08-02', false],
            'an invalid range' => ['range', '2012-08-01,2012-08-31', 'not a range', false],
        ];
    }
}
