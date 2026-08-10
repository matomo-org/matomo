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
     * compareDate, no segments), segment comparison over a single date ('segment'), or segment
     * comparison over exactly two dates ('segmentDate'). Everything else (three or more dates, with
     * or without segments) returns null and falls back to the legacy Twig layout.
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
            // Segment + date: segments compared over exactly two dates (one extra compareDate).
            'segment and date comparison' => [true, ['compareDates' => ['2026-05-03'], 'compareSegments' => ['browserCode==FF']], 'segmentDate'],
            // Three or more compared dates (2+ compareDates) are out of scope, with or without segments.
            'three dates' => [true, ['compareDates' => ['2026-05-03', '2026-05-02']], null],
            'segment and three dates' => [true, ['compareDates' => ['2026-05-03', '2026-05-02'], 'compareSegments' => ['browserCode==FF']], null],
            // Comparing but no compareDates and no segments is not a supported comparison.
            'comparing without compare params' => [true, [], null],
        ];
    }

    /**
     * @dataProvider getResolveMetricTitleData
     */
    public function testResolveMetricTitle(
        bool $useMetricLabelsAsTitles,
        array $viewTranslations,
        array $metricTranslations,
        string $description,
        string $expected
    ): void {
        $reflection = new \ReflectionClass(Sparklines::class);
        $sparklines = $reflection->newInstanceWithoutConstructor();
        $sparklines->config = new Sparklines\Config();
        $sparklines->config->use_metric_labels_as_titles = $useMetricLabelsAsTitles;
        $sparklines->config->translations = $viewTranslations;

        $method = $reflection->getMethod('resolveMetricTitle');
        $method->setAccessible(true);

        $actual = $method->invoke(
            $sparklines,
            'nb_conversions',
            $description,
            $metricTranslations
        );

        $this->assertSame($expected, $actual);
    }

    public function getResolveMetricTitleData(): array
    {
        return [
            'default mode prefers the generic metric title' => [
                false,
                ['nb_conversions' => 'Ecommerce orders'],
                ['nb_conversions' => 'Conversions'],
                'converted visits',
                'Conversions',
            ],
            'opt-in mode prefers the view-specific metric title' => [
                true,
                ['nb_conversions' => 'Ecommerce orders'],
                ['nb_conversions' => 'Conversions'],
                'converted visits',
                'Ecommerce orders',
            ],
            'opt-in mode falls back to the generic metric title' => [
                true,
                [],
                ['nb_conversions' => 'Conversions'],
                'converted visits',
                'Conversions',
            ],
            'default mode falls back to the description' => [
                false,
                ['nb_conversions' => 'Ecommerce orders'],
                [],
                'converted visits',
                'converted visits',
            ],
            'opt-in mode falls back to the description' => [
                true,
                [],
                [],
                'converted visits',
                'converted visits',
            ],
        ];
    }
}
