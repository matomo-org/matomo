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
     * @dataProvider dataConsistentAmounts
     *
     * @param array<int> $dataCounts
     */
    public function testItDoesNotLogIfDataAmountIsConsistent(array $dataCounts, int $stateCount): void
    {
        $logger = new FakeLogger();
        $chart = $this->createChart($logger, $dataCounts, $stateCount);

        $chart->render();

        self::assertSame('', $logger->output);
    }

    /**
     * @return iterable<string, array{array<int>, int}>
     */
    public function dataConsistentAmounts(): iterable
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

        yield 'single series, with state' => [
            [3],
            3,
        ];

        yield 'multiple series, with state' => [
            [5, 5, 5],
            5,
        ];
    }

    /**
     * @dataProvider dataInconsistentAmounts
     *
     * @param array<int> $dataCounts
     */
    public function testItLogsIfDataAmountIsInconsistent(
        array $dataCounts,
        int $stateCount,
        string $expectedMessage
    ): void {
        $logger = new FakeLogger();
        $chart = $this->createChart($logger, $dataCounts, $stateCount);

        $chart->render();

        self::assertStringContainsString($expectedMessage, $logger->output);
    }

    /**
     * @return iterable<string, array{array<int>, int, string}>
     */
    public function dataInconsistentAmounts(): iterable
    {
        $messageDataInconsistency = 'Chart rendered with different data point count per series';
        $messageStateInconsistency = 'Count of data states does not match count of data points';

        yield 'inconsistent series, no state' => [
            [3, 5],
            0,
            $messageDataInconsistency,
        ];

        yield 'inconsistent series, state not checked' => [
            [3, 5],
            7,
            $messageDataInconsistency,
        ];

        yield 'no data, only state' => [
            [],
            1,
            $messageStateInconsistency,
        ];

        yield 'consistent series, inconsistent state' => [
            [3, 3, 3],
            5,
            $messageStateInconsistency,
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
