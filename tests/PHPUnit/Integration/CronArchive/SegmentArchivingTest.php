<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\CronArchive;

use Piwik\Config;
use Piwik\Date;
use Piwik\CronArchive\SegmentArchiving;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group SegmentArchivingTest
 */
class SegmentArchivingTest extends IntegrationTestCase
{
    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();
        Fixture::createWebsite('2020-01-04 12:00:00');
    }

    /**
     * @dataProvider getTestDataForGetReArchiveSegmentStartDate
     */
    public function test_getReArchiveSegmentStartDate($processNewSegmentFrom, $segmentInfo, $expected)
    {
        Date::$now = strtotime('2020-10-12 13:45:00');

        Config::getInstance()->General['process_new_segments_from'] = $processNewSegmentFrom;

        $segmentArchiving = new SegmentArchiving();
        $result = $segmentArchiving->getReArchiveSegmentStartDate($segmentInfo);
        if (!empty($result)) {
            $result = $result->toString();
        }
        $this->assertEquals($expected, $result);
    }

    public function getTestDataForGetReArchiveSegmentStartDate()
    {
        return [
            // no segment creation time
            [
                SegmentArchiving::CREATION_TIME,
                [],
                null,
            ],

            // creation time
            [
                SegmentArchiving::CREATION_TIME,
                ['ts_created' => '2020-04-12 03:34:55'],
                '2020-04-12',
            ],

            // last edit time
            [
                SegmentArchiving::LAST_EDIT_TIME,
                ['ts_created' => '2020-02-02 03:00:00', 'ts_last_edit' => '2020-04-13 05:15:15'],
                '2020-04-13',
            ],

            // last edit time, no creation time
            [
                SegmentArchiving::LAST_EDIT_TIME,
                ['ts_last_edit' => '2020-04-13 05:15:15'],
                '2020-04-13',
            ],

            // creation time, last edit time is 0000-00-00,
            [
                SegmentArchiving::CREATION_TIME,
                ['ts_created' => '2020-04-12 03:34:55', 'ts_last_edit' => '0000-00-00 00:00:00'],
                '2020-04-12',
            ],

            // last edit time, last edit time is 0000-00-00
            [
                SegmentArchiving::LAST_EDIT_TIME,
                ['ts_created' => '2020-04-12 03:34:55', 'ts_last_edit' => '0000-00-00 00:00:00'],
                null,
            ],

            // last edit time, no edit time in segment
            [
                SegmentArchiving::LAST_EDIT_TIME,
                ['ts_created' => '2020-04-14 00:00:00'],
                '2020-04-14',
            ],

            // last edit time, no edit or create time in segment
            [
                SegmentArchiving::LAST_EDIT_TIME,
                [],
                null,
            ],

            // lastN
            [
                'last30',
                ['ts_created' => '2020-06-12'],
                '2020-05-13',
            ],

            // lastN, no date available
            [
                'last30',
                [],
                null,
            ],

            // editLastN
            [
                'editLast30',
                ['ts_created' => '2020-06-12 05:00:00', 'ts_last_edit' => '2020-09-13 05:15:15'],
                '2020-08-14',
            ],


            // editLastN, no date available
            [
                'editLast30',
                [],
                null,
            ],

            // beginning of time
            [
                SegmentArchiving::BEGINNING_OF_TIME,
                ['ts_created' => '2020-06-12'],
                '2013-01-01',
            ],

            // beginning of time (unreadable value)
            [
                'aslkdfjsdlkjf',
                ['ts_created' => '2020-06-12'],
                '2013-01-01',
            ],
        ];
    }

    public function test_getReArchiveSegmentStartDate_whenSiteCreationDateIsLater()
    {
        $segmentInfo = ['ts_created' => '2019-05-03 00:00:00', 'enable_only_idsite' => 1];
        $this->test_getReArchiveSegmentStartDate(SegmentArchiving::BEGINNING_OF_TIME, $segmentInfo, '2020-01-03');
    }

    public function test_getReArchiveSegmentStartDate_whenEarliestVisitTimeIsLater()
    {
        $t = Fixture::getTracker(1, '2020-02-05 03:00:00');
        $t->setUrl('http://abc.com');
        Fixture::checkResponse($t->doTrackPageView('abc'));

        $segmentInfo = ['ts_created' => '2019-05-03 00:00:00', 'enable_only_idsite' => 1];
        $this->test_getReArchiveSegmentStartDate(SegmentArchiving::BEGINNING_OF_TIME, $segmentInfo, '2020-02-05');
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
