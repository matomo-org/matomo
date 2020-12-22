<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace PHPUnit\Unit\CronArchive;


use PHPUnit\Framework\TestCase;
use Piwik\CliMulti\RequestParser;
use Piwik\CronArchive;
use Piwik\CronArchive\QueueConsumer;
use Piwik\CronArchive\SegmentArchiving;
use Piwik\DataAccess\Model;
use Piwik\Period\Factory;
use Piwik\Piwik;
use Psr\Log\NullLogger;

class QueueConsumerTest extends TestCase
{
    /**
     * @dataProvider getTestDataForHasIntersectingPeriod
     */
    public function test_hasIntersectingPeriod($archivesToProcess, $invalidatedArchive, $expected)
    {
        $periods = array_flip(Piwik::$idPeriods);
        foreach ($archivesToProcess as &$archive) {
            $periodLabel = $periods[$archive['period']];
            $archive['periodObj'] = Factory::build($periodLabel, $archive['date1']);
        }

        $periodLabel = $periods[$invalidatedArchive['period']];
        $invalidatedArchive['periodObj'] = Factory::build($periodLabel, $invalidatedArchive['date1']);

        $actual = QueueConsumer::hasIntersectingPeriod($archivesToProcess, $invalidatedArchive);
        $this->assertEquals($expected, $actual);
    }

    public function getTestDataForHasIntersectingPeriod()
    {
        return [
            // no intersecting periods
            [
                [
                    ['period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04'],
                    ['period' => 3, 'date1' => '2020-04-01', 'date2' => '2020-04-30'],
                    ['period' => 1, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==abc'],
                ],
                ['period' => 1, 'date1' => '2020-03-05', 'date2' => '2020-03-05'],
                false,
            ],

            // intersecting periods
            [
                [
                    ['period' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04'],
                    ['period' => 3, 'date1' => '2020-04-01', 'date2' => '2020-04-30'],
                    ['period' => 1, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==abc'],
                ],
                ['period' => 2, 'date1' => '2020-03-02', 'date2' => '2020-03-08'],
                true,
            ],

            // all same period, different segments
            [
                [
                    ['period' => 1, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==def'],
                    ['period' => 1, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==ghi'],
                    ['period' => 1, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==abc'],
                ],
                ['period' => 1, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==lmn'],
                false,
            ],

            // all same period, all visits in one
            [
                [
                    ['period' => 1, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => ''],
                ],
                ['period' => 1, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==lmn'],
                true,
            ],

            // all same period, different segments, all visits in next
            [
                [
                    ['period' => 1, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==def'],
                    ['period' => 1, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==ghi'],
                    ['period' => 1, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==abc'],
                ],
                ['period' => 1, 'date1' => '2020-03-15', 'date2' => '2020-03-15'],
                true,
            ],
        ];
    }

    /**
     * @dataProvider getTestDataForShouldSkipArchiveBecauseLowerPeriodOrSegmentIsInProgress
     */
    public function test_shouldSkipArchiveBecauseLowerPeriodOrSegmentIsInProgress($cliMultiProcesses, $archiveToProcess, $expected)
    {
        $cliRequestProcessor = $this->getMockRequestParser($cliMultiProcesses);

        /** @var QueueConsumer $queueConsumer */
        $queueConsumer = $this->getQueueConsumerWithMocks($cliRequestProcessor);

        $periods = array_flip(Piwik::$idPeriods);

        $archiveToProcess['periodObj'] = Factory::build($periods[$archiveToProcess['period']], $archiveToProcess['date']);
        $actual = $queueConsumer->shouldSkipArchiveBecauseLowerPeriodOrSegmentIsInProgress($archiveToProcess);
        $this->assertEquals($expected, $actual);
    }

    public function getTestDataForShouldSkipArchiveBecauseLowerPeriodOrSegmentIsInProgress()
    {
        return [
            // test idSite different
            [
                [
                    ['idSite' => 5, 'date' => '2020-03-04', 'period' => 'day'],
                ],
                ['idsite' => 3, 'date' => '2020-03-04', 'period' => 1],
                false,
            ],

            // test no period/date
            [
                [
                    ['idSite' => 3],
                ],
                ['idsite' => 3, 'date' => '2020-03-04', 'period' => 1],
                false,
            ],

            // test same segment
            [
                [
                    ['idSite' => 3, 'date' => '2020-03-04', 'period' => 'day', 'segment' => 'pageUrl=@%2C'],
                ],
                ['idsite' => 3, 'date' => '2020-03-04', 'period' => 1, 'segment' => 'pageUrl=@%2C'],
                'lower or same period in progress in another local climulti process (period = day, date = 2020-03-04)',
            ],
            [
                [
                    ['idSite' => 3, 'date' => '2020-03-04', 'period' => 'day', 'segment' => 'pageUrl=@%252C'],
                ],
                ['idsite' => 3, 'date' => '2020-03-04', 'period' => 1, 'segment' => 'pageUrl=@%2C'],
                'lower or same period in progress in another local climulti process (period = day, date = 2020-03-04)',
            ],

            // test different segment
            [
                [
                    ['idSite' => 3, 'date' => '2020-03-04', 'period' => 'day', 'segment' => 'pageUrl=@%2C'],
                ],
                ['idsite' => 3, 'date' => '2020-03-04', 'period' => 1, 'segment' => 'pageUrl=@%2Ca'],
                false,
            ],
            [
                [
                    ['idSite' => 3, 'date' => '2020-03-04', 'period' => 'day', 'segment' => 'pageUrl=@%252C'],
                ],
                ['idsite' => 3, 'date' => '2020-03-04', 'period' => 1, 'segment' => 'pageUrl%3D%40%252C'],
                false,
            ],

            // test lower periods together
            [
                [
                    ['idSite' => 3, 'date' => '2020-03-04', 'period' => 'day'],
                ],
                ['idsite' => 3, 'date' => '2020-03-04', 'period' => 1],
                'lower or same period in progress in another local climulti process (period = day, date = 2020-03-04)',
            ],
            [
                [
                    ['idSite' => 3, 'date' => '2020-03-04', 'period' => 'day'],
                ],
                ['idsite' => 3, 'date' => '2020-03-01', 'period' => 3],
                'lower or same period in progress in another local climulti process (period = day, date = 2020-03-04)',
            ],
            [
                [
                    ['idSite' => 3, 'date' => '2020-03-01', 'period' => 'month'],
                ],
                ['idsite' => 3, 'date' => '2020-03-01', 'period' => 1],
                false,
            ],

            // test segment w/ non-segment
            [
                [
                    ['idSite' => 3, 'date' => '2020-03-04', 'period' => 'day', 'segment' => ''],
                ],
                ['idsite' => 3, 'date' => '2020-03-04', 'period' => 2, 'segment' => 'pageUrl=@%2C'],
                'lower or same period in progress in another local climulti process (period = day, date = 2020-03-04)',
            ],
            [
                [
                    ['idSite' => 3, 'date' => '2020-03-04', 'period' => 'day', 'segment' => 'pageUrl=@%2C'],
                ],
                ['idsite' => 3, 'date' => '2020-03-04', 'period' => 1, 'segment' => ''],
                'lower or same period in progress in another local climulti process (period = day, date = 2020-03-04)',
            ],
        ];
    }

    private function getMockRequestParser($cliMultiProcesses)
    {
        $mock = $this->getMockBuilder(RequestParser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getInProgressArchivingCommands'])
            ->getMock();
        $mock->method('getInProgressArchivingCommands')->willReturn($cliMultiProcesses);
        return $mock;
    }

    private function getQueueConsumerWithMocks($cliRequestProcessor)
    {
        $mockCronArchive = $this->getMockBuilder(CronArchive::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new QueueConsumer(new NullLogger(), null, null, null, new Model(), new SegmentArchiving(null), $mockCronArchive, $cliRequestProcessor);
    }
}