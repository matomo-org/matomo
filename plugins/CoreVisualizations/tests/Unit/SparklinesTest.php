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

    /**
     * Encodes the product-scope decision of which comparison modes the redesigned Vue grid may
     * render: no comparison, or date comparison of exactly two dates (one extra compareDate) with
     * no segment comparison. Everything else falls back to the legacy Twig layout.
     *
     * @dataProvider getRedesignComparisonSupportData
     */
    public function testIsRedesignSupportedForComparison(bool $isComparing, array $request, bool $expected)
    {
        // Stub the two request-reading helpers so the pure decision logic can be exercised without
        // the full ViewDataTable request/config setup; invoke the private method under test directly.
        $view = $this->getMockBuilder(Sparklines::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isComparing', 'getRequestArray'])
            ->getMock();
        $view->method('isComparing')->willReturn($isComparing);
        $view->method('getRequestArray')->willReturn($request);

        $method = new \ReflectionMethod(Sparklines::class, 'isRedesignSupportedForComparison');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($view));
    }

    public function getRedesignComparisonSupportData(): array
    {
        return [
            // No comparison is always supported, regardless of the request contents.
            'no comparison' => [false, [], true],
            // Two-date comparison = the original date plus exactly one compareDate, no segments.
            'two dates, no segments' => [true, ['compareDates' => ['2026-05-03']], true],
            'two dates, empty segments' => [true, ['compareDates' => ['2026-05-03'], 'compareSegments' => []], true],
            // Segment comparison stays on the legacy layout, even with a single compareDate.
            'segment comparison' => [true, ['compareDates' => ['2026-05-03'], 'compareSegments' => ['browserCode==FF']], false],
            // Three or more compared dates (2+ compareDates) are out of scope.
            'three dates' => [true, ['compareDates' => ['2026-05-03', '2026-05-02']], false],
            // Comparing but no compareDates at all is not a supported date comparison.
            'comparing without compareDates' => [true, [], false],
        ];
    }
}
