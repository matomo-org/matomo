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
use Piwik\CronArchive\QueueConsumer;
use Piwik\Period\Factory;
use Piwik\Piwik;

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
        return  [
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
}