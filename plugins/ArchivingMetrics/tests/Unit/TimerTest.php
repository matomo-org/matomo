<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ArchivingMetrics\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Period\Factory;
use Piwik\Plugins\ArchivingMetrics\Clock\ClockInterface;
use Piwik\Plugins\ArchivingMetrics\Context;
use Piwik\Plugins\ArchivingMetrics\Timer;
use Piwik\Plugins\ArchivingMetrics\Writer\WriterInterface;
use Piwik\Segment;

/**
 * @group ArchivingMetrics
 * @group ArchivingMetrics_Timer
 * @group Plugins
 */
class TimerTest extends TestCase
{
    /**
     * @dataProvider timerProvider
     */
    public function testItRecordsArchivingRunsWithCorrectTiming(array $events, array $microtimes, array $nowValues, array $expectedRecords): void
    {
        $writer = new InMemoryWriter();
        $clock = $this->createMock(ClockInterface::class);
        $clock->method('microtime')->willReturnOnConsecutiveCalls(...$microtimes);
        $clock->method('now')->willReturnOnConsecutiveCalls(...$nowValues);
        $timer = new Timer(true, $clock, $writer);

        foreach ($events as $event) {
            $context = $this->createContext($event['context']);
            if ($event['action'] === 'start') {
                $timer->start($context);
                continue;
            }

            $timer->complete(
                $context,
                $event['idArchives'],
                $event['cached']
            );
        }

        $this->assertSame($expectedRecords, $writer->records);
    }

    public function testItSkipsWhenArchivePhpNotTriggered(): void
    {
        $writer = new InMemoryWriter();
        $clock = $this->createMock(ClockInterface::class);
        $clock->method('microtime')->willReturnOnConsecutiveCalls(0.0);
        $clock->method('now')->willReturnOnConsecutiveCalls('2024-01-01 00:00:02');
        $timer = new Timer(false, $clock, $writer);

        $context = $this->createContext([
            'idSite' => 1,
            'segment' => '',
            'plugin' => '',
            'date1' => '2024-01-01',
            'date2' => '2024-01-01',
            'period' => 'day',
        ]);

        $timer->start($context);
        $timer->complete($context, [123], false);

        $this->assertSame([], $writer->records);
    }

    public function timerProvider(): array
    {
        // Blank segment ensures Rules::getDoneStringFlagFor returns "done" so the timer is active.
        $base = [
            'idSite' => 1,
            'segment' => '',
            'plugin' => '',
            'date1' => '2024-01-01',
            'date2' => '2024-01-01',
        ];

        return [
            'single period' => [
                'events' => [
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'day'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'day']), 'idArchives' => [101], 'cached' => false],
                ],
                'microtimes' => [
                    strtotime('2024-01-01 00:00:00'),
                    strtotime('2024-01-01 00:00:00') + 1.2,
                ],
                'nowValues' => [
                    '2024-01-01 00:00:02',
                ],
                'expectedRecords' => [
                    [
                        'idarchive' => 101,
                        'idsite' => 1,
                        'segment' => '',
                        'date1' => '2024-01-01',
                        'date2' => '2024-01-01',
                        'period' => 1,
                        'ts_started' => '2024-01-01 00:00:00',
                        'ts_finished' => '2024-01-01 00:00:02',
                        'total_time' => 1200,
                        'total_time_exclusive' => 1200,
                    ],
                ],
            ],
            'mix of events with no nesting' => [
                'events' => [
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2025-01-01'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2025-01-01']), 'idArchives' => [303], 'cached' => false],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'day', 'date1' => '2024-02-01', 'date2' => '2024-02-01'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'day', 'date1' => '2024-02-01', 'date2' => '2024-02-01']), 'idArchives' => [204], 'cached' => false],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'month', 'date1' => '2024-02-01', 'date2' => '2024-02-29'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'month', 'date1' => '2024-02-01', 'date2' => '2024-02-29']), 'idArchives' => [202], 'cached' => false],
                ],
                'microtimes' => [
                    strtotime('2024-01-01 00:00:00'),
                    strtotime('2024-01-01 00:00:00') + 6.3,
                    strtotime('2024-02-01 00:00:01'),
                    strtotime('2024-02-01 00:00:01') + 5.4,
                    strtotime('2024-02-01 00:00:01'),
                    strtotime('2024-02-01 00:00:01') + 12.3,
                ],
                'nowValues' => [
                    '2024-02-01 00:00:00',
                    '2024-12-31 23:59:59',
                    '2024-12-31 23:59:59',
                ],
                'expectedRecords' => [
                    [
                        'idarchive' => 303,
                        'idsite' => 1,
                        'segment' => '',
                        'date1' => '2024-01-01',
                        'date2' => '2024-12-31',
                        'period' => 4,
                        'ts_started' => '2024-01-01 00:00:00',
                        'ts_finished' => '2024-02-01 00:00:00',
                        'total_time' => 6300,
                        'total_time_exclusive' => 6300,
                    ],
                    [
                        'idarchive' => 204,
                        'idsite' => 1,
                        'segment' => '',
                        'date1' => '2024-02-01',
                        'date2' => '2024-02-01',
                        'period' => 1,
                        'ts_started' => '2024-02-01 00:00:01',
                        'ts_finished' => '2024-12-31 23:59:59',
                        'total_time' => 5400,
                        'total_time_exclusive' => 5400,
                    ],
                    [
                        'idarchive' => 202,
                        'idsite' => 1,
                        'segment' => '',
                        'date1' => '2024-02-01',
                        'date2' => '2024-02-29',
                        'period' => 3,
                        'ts_started' => '2024-02-01 00:00:01',
                        'ts_finished' => '2024-12-31 23:59:59',
                        'total_time' => 12300,
                        'total_time_exclusive' => 12300,
                    ],
                ],
            ],
            'mix of events with no nesting and some fetched from cache' => [
                'events' => [
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2025-01-01'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2025-01-01']), 'idArchives' => [303], 'cached' => true],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'day', 'date1' => '2024-02-01', 'date2' => '2024-02-01'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'day', 'date1' => '2024-02-01', 'date2' => '2024-02-01']), 'idArchives' => [204], 'cached' => false],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'month', 'date1' => '2024-02-01', 'date2' => '2024-02-29'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'month', 'date1' => '2024-02-01', 'date2' => '2024-02-29']), 'idArchives' => [202], 'cached' => true],
                ],
                'microtimes' => [
                    strtotime('2024-01-01 00:00:00'),
                    strtotime('2024-02-01 00:00:00'),
                    strtotime('2024-02-01 00:00:00') + 3.0,
                    strtotime('2024-02-01 00:00:01'),
                ],
                'nowValues' => [
                    '2024-02-01 00:00:01',
                ],
                'expectedRecords' => [
                    [
                        'idarchive' => 204,
                        'idsite' => 1,
                        'segment' => '',
                        'date1' => '2024-02-01',
                        'date2' => '2024-02-01',
                        'period' => 1,
                        'ts_started' => '2024-02-01 00:00:00',
                        'ts_finished' => '2024-02-01 00:00:01',
                        'total_time' => 3000,
                        'total_time_exclusive' => 3000,
                    ],
                ],
            ],
            'nested month inside year' => [
                'events' => [
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2025-01-01'])],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'month', 'date1' => '2024-02-01', 'date2' => '2024-02-29'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'month', 'date1' => '2024-02-01', 'date2' => '2024-02-29']), 'idArchives' => [202], 'cached' => false],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2025-01-01']), 'idArchives' => [303], 'cached' => false],
                ],
                'microtimes' => [
                    strtotime('2024-01-01 00:00:00'),
                    strtotime('2024-01-01 00:00:00') + 0.5,
                    strtotime('2024-01-01 00:00:00') + 1.1,
                    strtotime('2024-01-01 00:00:00') + 2.5,
                ],
                'nowValues' => [
                    '2024-02-01 00:00:01',
                    '2024-12-31 23:59:59',
                ],
                'expectedRecords' => [
                    [
                        'idarchive' => 202,
                        'idsite' => 1,
                        'segment' => '',
                        'date1' => '2024-02-01',
                        'date2' => '2024-02-29',
                        'period' => 3,
                        'ts_started' => '2024-01-01 00:00:00',
                        'ts_finished' => '2024-02-01 00:00:01',
                        'total_time' => 600,
                        'total_time_exclusive' => 600,
                    ],
                    [
                        'idarchive' => 303,
                        'idsite' => 1,
                        'segment' => '',
                        'date1' => '2024-01-01',
                        'date2' => '2024-12-31',
                        'period' => 4,
                        'ts_started' => '2024-01-01 00:00:00',
                        'ts_finished' => '2024-12-31 23:59:59',
                        'total_time' => 2500,
                        'total_time_exclusive' => 1900,
                    ],
                ],
            ],
            'segments have timings recorded' => [
                'events' => [
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2025-01-01', 'segment' => 'browserCode==FF'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2025-01-01', 'segment' => 'browserCode==FF']), 'idArchives' => [303], 'cached' => false],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'day', 'date1' => '2024-02-01', 'date2' => '2024-02-01'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'day', 'date1' => '2024-02-01', 'date2' => '2024-02-01']), 'idArchives' => [204], 'cached' => false],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'month', 'date1' => '2024-02-01', 'date2' => '2024-02-29'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'month', 'date1' => '2024-02-01', 'date2' => '2024-02-29']), 'idArchives' => [202], 'cached' => false],
                ],
                'microtimes' => [
                    strtotime('2024-01-01 00:00:00'),
                    strtotime('2024-01-01 00:00:00') + 6.3,
                    strtotime('2024-02-01 00:00:01'),
                    strtotime('2024-02-01 00:00:01') + 5.4,
                    strtotime('2024-02-01 00:00:01'),
                    strtotime('2024-02-01 00:00:01') + 12.3,
                ],
                'nowValues' => [
                    '2024-02-01 00:00:00',
                    '2024-12-31 23:59:59',
                    '2024-12-31 23:59:59',
                ],
                'expectedRecords' => [
                    [
                        'idarchive' => 303,
                        'idsite' => 1,
                        'segment' => md5('browserCode==FF'),
                        'date1' => '2024-01-01',
                        'date2' => '2024-12-31',
                        'period' => 4,
                        'ts_started' => '2024-01-01 00:00:00',
                        'ts_finished' => '2024-02-01 00:00:00',
                        'total_time' => 6300,
                        'total_time_exclusive' => 6300,
                    ],
                    [
                        'idarchive' => 204,
                        'idsite' => 1,
                        'segment' => '',
                        'date1' => '2024-02-01',
                        'date2' => '2024-02-01',
                        'period' => 1,
                        'ts_started' => '2024-02-01 00:00:01',
                        'ts_finished' => '2024-12-31 23:59:59',
                        'total_time' => 5400,
                        'total_time_exclusive' => 5400,
                    ],
                    [
                        'idarchive' => 202,
                        'idsite' => 1,
                        'segment' => '',
                        'date1' => '2024-02-01',
                        'date2' => '2024-02-29',
                        'period' => 3,
                        'ts_started' => '2024-02-01 00:00:01',
                        'ts_finished' => '2024-12-31 23:59:59',
                        'total_time' => 12300,
                        'total_time_exclusive' => 12300,
                    ],
                ],
            ],
        ];
    }

    private function createContext(array $data): Context
    {
        $period = Factory::build($data['period'], $data['date1']);

        $segment = $this->createSegment($data['segment']);

        return new Context(
            $data['idSite'],
            $period,
            $segment,
            $data['plugin']
        );
    }
    private function createSegment(string $segmentString): Segment
    {
        $segment = $this->createMock(Segment::class);
        $segment->method('getString')->willReturn($segmentString);
        $segment->method('getHash')->willReturn($segmentString === '' ? '' : md5(urldecode($segmentString)));
        $segment->method('isEmpty')->willReturn($segmentString === '');
        return $segment;
    }
}

class InMemoryWriter implements WriterInterface
{
    public $records = [];

    public function write(array $record): void
    {
        $this->records[] = $record;
    }
}
