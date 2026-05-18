<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\tests\Unit;

use Piwik\DataTable\Row;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;

/**
 * @group CoreVisualizations
 * @group Sparklines
 * @group Plugins
 */
class SparklinesTest extends \PHPUnit\Framework\TestCase
{
    public function testFindComparisonRowMatchesNormalizedRangeDate()
    {
        $expectedRow = new Row(['columns' => ['nb_conversions' => 1]]);
        $comparisonRows = [
            '' => [
                'month' => [
                    '2026-03-01,2026-03-31' => $expectedRow,
                ],
            ],
        ];

        $reflection = new \ReflectionClass(Sparklines::class);
        $sparklines = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('findComparisonRow');
        $method->setAccessible(true);

        $actualRow = $method->invoke($sparklines, $comparisonRows, '', 'month', '2026-03-01');

        $this->assertSame($expectedRow, $actualRow);
    }
}
