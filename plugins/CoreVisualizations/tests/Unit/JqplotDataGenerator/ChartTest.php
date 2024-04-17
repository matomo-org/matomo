<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\tests\Unit\JqplotDataGenerator;

use PHPUnit\Framework\TestCase;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\Chart;
use Piwik\Tests\Framework\Mock\FakeLogger;

/**
 * @group Chart
 * @group CoreVisualizations
 */
class ChartTest extends TestCase
{
    /**
     * @dataProvider dataSpanFullGraphLength
     *
     * @param array<int> $dataCounts
     */
    public function testItDoesNotLogIfDataStatesSpanFullGraphLength(array $dataCounts, int $stateCount): void
    {
        $logger = new FakeLogger();
        $chart = $this->createChart($logger, $dataCounts, $stateCount);

        $chart->render();

        self::assertSame('', $logger->output);
    }

    /**
     * @return iterable<string, array{array<int>, int}>
     */
    public function dataSpanFullGraphLength(): iterable
    {
        yield 'empty chart' => [
            [],
            0,
        ];

        yield 'single series, no states' => [
            [3],
            0,
        ];

        yield 'multiple series, no states' => [
            [5, 5, 5],
            0,
        ];

        yield 'single series, matching state' => [
            [3],
            3,
        ];

        yield 'multiple series, matching state' => [
            [5, 5, 5],
            5,
        ];

        yield 'multiple series, matching longest series' => [
            [5, 9, 5],
            9,
        ];
    }

    /**
     * @dataProvider dataSpanNotMatchingGraphLength
     *
     * @param array<int> $dataCounts
     */
    public function testIfLogsIfDataStateCountDoesMatchFullGraphLength(array $dataCounts, int $stateCount): void
    {
        $logger = new FakeLogger();
        $chart = $this->createChart($logger, $dataCounts, $stateCount);

        $chart->render();

        self::assertStringContainsString(
            sprintf(
                'Data state information does not span graph length (%u ticks, %u states)',
                [] === $dataCounts ? 0 : max(...$dataCounts),
                $stateCount
            ),
            $logger->output
        );
    }

    /**
     * @return iterable<string, array{array<int>, int}>
     */
    public function dataSpanNotMatchingGraphLength(): iterable
    {
        yield 'only state' => [
            [],
            1,
        ];

        yield 'too many state points' => [
            [3, 5],
            7,
        ];

        yield 'not enough state points' => [
            [5, 9],
            7,
        ];
    }

    /**
     * @param array<int> $dataCounts
     */
    private function createChart(LoggerInterface $logger, array $dataCounts, int $stateCount): Chart
    {
        $chart = new Chart($logger);

        foreach ($dataCounts as $index => $dataCount) {
            $label = 'yAxis' . $index;
            $data = array_fill(0, $dataCount, $index);
            $values = [$label => $data];

            $chart->setAxisYValues($values);
        }

        if (0 !== $stateCount) {
            $chart->setDataStates(array_fill(0, $stateCount, 'test'));
        }

        return $chart;
    }
}
