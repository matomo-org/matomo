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
     * render: no comparison ('none'), date comparison of exactly two dates ('date', one extra
     * compareDate, no segments), or segment comparison over a single date ('segment'). Everything
     * else (segment + date combined, or three or more dates) returns null and falls back to the
     * legacy Twig layout.
     *
     * @dataProvider getRedesignComparisonModeData
     */
    public function testGetSupportedRedesignComparisonMode(bool $isComparing, array $request, ?string $expected)
    {
        // Stub the two request-reading helpers so the pure decision logic can be exercised without
        // the full ViewDataTable request/config setup; invoke the private method under test directly.
        $view = $this->getMockBuilder(Sparklines::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isComparing', 'getRequestArray'])
            ->getMock();
        $view->method('isComparing')->willReturn($isComparing);
        $view->method('getRequestArray')->willReturn($request);

        $method = new \ReflectionMethod(Sparklines::class, 'getSupportedRedesignComparisonMode');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($view));
    }

    public function getRedesignComparisonModeData(): array
    {
        return [
            // No comparison is always supported, regardless of the request contents.
            'no comparison' => [false, [], 'none'],
            // Two-date comparison = the original date plus exactly one compareDate, no segments.
            'two dates, no segments' => [true, ['compareDates' => ['2026-05-03']], 'date'],
            'two dates, empty segments' => [true, ['compareDates' => ['2026-05-03'], 'compareSegments' => []], 'date'],
            // Segment comparison over the single (base) date, no extra compareDates.
            'segment comparison' => [true, ['compareSegments' => ['browserCode==FF']], 'segment'],
            'segment comparison, empty compareDates' => [true, ['compareSegments' => ['browserCode==FF'], 'compareDates' => []], 'segment'],
            // Segment + date comparison combined stays on the legacy layout.
            'segment and date comparison' => [true, ['compareDates' => ['2026-05-03'], 'compareSegments' => ['browserCode==FF']], null],
            // Three or more compared dates (2+ compareDates) are out of scope.
            'three dates' => [true, ['compareDates' => ['2026-05-03', '2026-05-02']], null],
            // Comparing but no compareDates and no segments is not a supported comparison.
            'comparing without compare params' => [true, [], null],
        ];
    }
}
