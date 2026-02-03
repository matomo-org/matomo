<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ArchivingMetrics\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\ArchiveProcessor\Rules;
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
    public function testItRecordsArchivingRunsWithCorrectTiming(array $events, array $microtimes, array $expectedRecords): void
    {
        $writer = new InMemoryWriter();
        $clock = $this->createMock(ClockInterface::class);
        $clock->method('microtime')->willReturnOnConsecutiveCalls(...$microtimes);
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
                'expectedRecords' => [
                    [
                        'idarchive' => 101,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-01-01',
                        'date2' => '2024-01-01',
                        'period' => 1,
                        'ts_started' => '2024-01-01 00:00:00',
                        'ts_finished' => '2024-01-01 00:00:01',
                        'total_time' => 1200,
                        'total_time_exclusive' => 1200,
                    ],
                ],
            ],
            'mix of events with no nesting' => [
                'events' => [
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2024-12-31'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2024-12-31']), 'idArchives' => [303], 'cached' => false],
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
                'expectedRecords' => [
                    [
                        'idarchive' => 303,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-01-01',
                        'date2' => '2024-12-31',
                        'period' => 4,
                        'ts_started' => '2024-01-01 00:00:00',
                        'ts_finished' => '2024-01-01 00:00:06',
                        'total_time' => 6300,
                        'total_time_exclusive' => 6300,
                    ],
                    [
                        'idarchive' => 204,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-02-01',
                        'date2' => '2024-02-01',
                        'period' => 1,
                        'ts_started' => '2024-02-01 00:00:01',
                        'ts_finished' => '2024-02-01 00:00:06',
                        'total_time' => 5400,
                        'total_time_exclusive' => 5400,
                    ],
                    [
                        'idarchive' => 202,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-02-01',
                        'date2' => '2024-02-29',
                        'period' => 3,
                        'ts_started' => '2024-02-01 00:00:01',
                        'ts_finished' => '2024-02-01 00:00:13',
                        'total_time' => 12300,
                        'total_time_exclusive' => 12300,
                    ],
                ],
            ],
            'mix of events with no nesting and some fetched from cache' => [
                'events' => [
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2024-12-31'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2024-12-31']), 'idArchives' => [303], 'cached' => true],
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
                'expectedRecords' => [
                    [
                        'idarchive' => 204,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-02-01',
                        'date2' => '2024-02-01',
                        'period' => 1,
                        'ts_started' => '2024-02-01 00:00:00',
                        'ts_finished' => '2024-02-01 00:00:03',
                        'total_time' => 3000,
                        'total_time_exclusive' => 3000,
                    ],
                ],
            ],
            'nested day inside week' => [
                'events' => [
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'week', 'date1' => '2024-01-08', 'date2' => '2024-01-14'])],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'day', 'date1' => '2024-01-10', 'date2' => '2024-01-10'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'day', 'date1' => '2024-01-10', 'date2' => '2024-01-10']), 'idArchives' => [202], 'cached' => false],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'week', 'date1' => '2024-01-08', 'date2' => '2024-01-14']), 'idArchives' => [303], 'cached' => false],
                ],
                'microtimes' => [
                    strtotime('2024-01-01 00:00:00'),
                    strtotime('2024-01-01 00:00:00') + 0.5,
                    strtotime('2024-01-01 00:00:00') + 1.1,
                    strtotime('2024-01-01 00:00:00') + 2.5,
                ],
                'expectedRecords' => [
                    [
                        'idarchive' => 202,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-01-10',
                        'date2' => '2024-01-10',
                        'period' => 1,
                        'ts_started' => '2024-01-01 00:00:00',
                        'ts_finished' => '2024-01-01 00:00:01',
                        'total_time' => 600,
                        'total_time_exclusive' => 600,
                    ],
                    [
                        'idarchive' => 303,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-01-08',
                        'date2' => '2024-01-14',
                        'period' => 2,
                        'ts_started' => '2024-01-01 00:00:00',
                        'ts_finished' => '2024-01-01 00:00:02',
                        'total_time' => 2500,
                        'total_time_exclusive' => 1900,
                    ],
                ],
            ],
            'full cascade year <-> day' => [
                'events' => [
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2024-12-31'])],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'month', 'date1' => '2024-01-01', 'date2' => '2024-01-31'])],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'week', 'date1' => '2024-01-08', 'date2' => '2024-01-14'])],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'day', 'date1' => '2024-01-10', 'date2' => '2024-01-10'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'day', 'date1' => '2024-01-10', 'date2' => '2024-01-10']), 'idArchives' => [202], 'cached' => false],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'week', 'date1' => '2024-01-08', 'date2' => '2024-01-14']), 'idArchives' => [303], 'cached' => false],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'week', 'date1' => '2024-01-15', 'date2' => '2024-01-22'])],
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'day', 'date1' => '2024-01-20', 'date2' => '2024-01-20'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'day', 'date1' => '2024-01-20', 'date2' => '2024-01-20']), 'idArchives' => [404], 'cached' => false],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'week', 'date1' => '2024-01-15', 'date2' => '2024-01-21']), 'idArchives' => [505], 'cached' => false],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'month', 'date1' => '2024-01-01', 'date2' => '2024-01-31']), 'idArchives' => [606], 'cached' => false],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2024-12-31']), 'idArchives' => [707], 'cached' => false],
                ],
                'microtimes' => [
                    strtotime('2024-01-01 00:00:00'),
                    strtotime('2024-01-01 00:00:00') + 1,
                    strtotime('2024-01-01 00:00:00') + 2,
                    strtotime('2024-01-01 00:00:00') + 4,
                    strtotime('2024-01-01 00:00:00') + 8,
                    strtotime('2024-01-01 00:00:00') + 16,
                    strtotime('2024-01-01 00:00:00') + 32,
                    strtotime('2024-01-01 00:00:00') + 64,
                    strtotime('2024-01-01 00:00:00') + 128,
                    strtotime('2024-01-01 00:00:00') + 256,
                    strtotime('2024-01-01 00:00:00') + 512,
                    strtotime('2024-01-01 00:00:00') + 1024,
                ],
                'expectedRecords' => [
                    [
                        'idarchive' => 202,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-01-10',
                        'date2' => '2024-01-10',
                        'period' => 1,
                        'ts_started' => '2024-01-01 00:00:04',
                        'ts_finished' => '2024-01-01 00:00:08',
                        'total_time' => 4000,
                        'total_time_exclusive' => 4000,
                    ],
                    [
                        'idarchive' => 303,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-01-08',
                        'date2' => '2024-01-14',
                        'period' => 2,
                        'ts_started' => '2024-01-01 00:00:02',
                        'ts_finished' => '2024-01-01 00:00:16',
                        'total_time' => 14000,
                        'total_time_exclusive' => 10000,
                    ],
                    [
                        'idarchive' => 404,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-01-20',
                        'date2' => '2024-01-20',
                        'period' => 1,
                        'ts_started' => '2024-01-01 00:01:04',
                        'ts_finished' => '2024-01-01 00:02:08',
                        'total_time' => 64000,
                        'total_time_exclusive' => 64000,
                    ],
                    [
                        'idarchive' => 505,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-01-15',
                        'date2' => '2024-01-21',
                        'period' => 2,
                        'ts_started' => '2024-01-01 00:00:32',
                        'ts_finished' => '2024-01-01 00:04:16',
                        'total_time' => 224000,
                        'total_time_exclusive' => 160000,
                    ],
                    [
                        'idarchive' => 606,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-01-01',
                        'date2' => '2024-01-31',
                        'period' => 3,
                        'ts_started' => '2024-01-01 00:00:01',
                        'ts_finished' => '2024-01-01 00:08:32',
                        'total_time' => 511000,
                        'total_time_exclusive' => 273000,
                    ],
                    [
                        'idarchive' => 707,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-01-01',
                        'date2' => '2024-12-31',
                        'period' => 4,
                        'ts_started' => '2024-01-01 00:00:00',
                        'ts_finished' => '2024-01-01 00:17:04',
                        'total_time' => 1024000,
                        'total_time_exclusive' => 513000,
                    ],
                ],
            ],
            'segments have timings recorded' => [
                'events' => [
                    ['action' => 'start', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2024-12-31', 'segment' => 'browserCode==FF'])],
                    ['action' => 'complete', 'context' => array_merge($base, ['period' => 'year', 'date1' => '2024-01-01', 'date2' => '2024-12-31', 'segment' => 'browserCode==FF']), 'idArchives' => [303], 'cached' => false],
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
                'expectedRecords' => [
                    [
                        'idarchive' => 303,
                        'idsite' => 1,
                        'archive_name' => 'done' . md5('browserCode==FF'),
                        'date1' => '2024-01-01',
                        'date2' => '2024-12-31',
                        'period' => 4,
                        'ts_started' => '2024-01-01 00:00:00',
                        'ts_finished' => '2024-01-01 00:00:06',
                        'total_time' => 6300,
                        'total_time_exclusive' => 6300,
                    ],
                    [
                        'idarchive' => 204,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-02-01',
                        'date2' => '2024-02-01',
                        'period' => 1,
                        'ts_started' => '2024-02-01 00:00:01',
                        'ts_finished' => '2024-02-01 00:00:06',
                        'total_time' => 5400,
                        'total_time_exclusive' => 5400,
                    ],
                    [
                        'idarchive' => 202,
                        'idsite' => 1,
                        'archive_name' => 'done',
                        'date1' => '2024-02-01',
                        'date2' => '2024-02-29',
                        'period' => 3,
                        'ts_started' => '2024-02-01 00:00:01',
                        'ts_finished' => '2024-02-01 00:00:13',
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
            $data['plugin'],
            $data['report'] ?? false
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

    public function write(Context $context, array $timing): void
    {
        $this->records[] = array_merge(
            [
                'idarchive' => $timing['idarchive'],
                'idsite' => $context->idSite,
                'archive_name' => Rules::getDoneStringFlagFor(
                    [$context->idSite],
                    $context->segment,
                    $context->period->getLabel(),
                    $context->plugin
                ),
                'date1' => $context->period->getDateTimeStart()->toString('Y-m-d'),
                'date2' => $context->period->getDateTimeEnd()->toString('Y-m-d'),
                'period' => $context->period->getId(),
            ],
            $timing
        );
    }
}
