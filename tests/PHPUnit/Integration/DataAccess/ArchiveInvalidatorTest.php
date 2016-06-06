<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\ArchiveProcessor\Rules;
use Piwik\CronArchive\SitesToReprocessDistributedList;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\Segment;

/**
 * @group Archiver
 * @group ArchiveInvalidator
 * @group DataAccess
 */
class ArchiveInvalidatorTest extends IntegrationTestCase
{
    const TEST_SEGMENT_1 = 'browserCode==FF';
    const TEST_SEGMENT_2 = 'countryCode==uk';

    /**
     * @var ArchiveInvalidator
     */
    private $invalidator;

    /**
     * @var Segment
     */
    private static $segment1;

    /**
     * @var Segment
     */
    private static $segment2;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // these are static because it takes a long time to create new Segment instances (for some reason)
        self::$segment1 = new Segment(self::TEST_SEGMENT_1, array());
        self::$segment2 = new Segment(self::TEST_SEGMENT_2, array());
    }

    public function setUp()
    {
        parent::setUp();

        $this->invalidator = new ArchiveInvalidator(new Model());
    }

    public function test_rememberToInvalidateArchivedReportsLater_shouldCreateAnEntryInCaseThereIsNoneYet()
    {
        $key = 'report_to_invalidate_2_2014-04-05';
        $this->assertFalse(Option::get($key));

        $this->rememberReport(2, '2014-04-05');

        $this->assertSame('1', Option::get($key));
    }

    public function test_rememberToInvalidateArchivedReportsLater_shouldNotCreateEntryTwice()
    {
        $this->rememberReport(2, '2014-04-05');
        $this->rememberReport(2, '2014-04-05');
        $this->rememberReport(2, '2014-04-05');

        $this->assertCount(1, Option::getLike('report_to_invalidate%'));
    }

    public function test_getRememberedArchivedReportsThatShouldBeInvalidated_shouldNotReturnEntriesInCaseNoneAreRemembered()
    {
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $this->assertSame(array(), $reports);
    }

    public function test_getRememberedArchivedReportsThatShouldBeInvalidated_shouldGroupEntriesByDate()
    {
        $this->rememberReportsForManySitesAndDates();

        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $this->assertSame($this->getRememberedReportsByDate(), $reports);
    }

    public function test_forgetRememberedArchivedReportsToInvalidateForSite_shouldNotDeleteAnythingInCaseNoReportForThatSite()
    {
        $this->rememberReportsForManySitesAndDates();

        $this->invalidator->forgetRememberedArchivedReportsToInvalidateForSite(10);
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $this->assertSame($this->getRememberedReportsByDate(), $reports);
    }

    public function test_forgetRememberedArchivedReportsToInvalidateForSite_shouldOnlyDeleteReportsBelongingToThatSite()
    {
        $this->rememberReportsForManySitesAndDates();

        $this->invalidator->forgetRememberedArchivedReportsToInvalidateForSite(7);
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $expected = array(
            '2014-04-05' => array(1, 2, 4),
            '2014-05-05' => array(2, 5),
            '2014-04-06' => array(3)
        );
        $this->assertSame($expected, $reports);
    }

    public function test_forgetRememberedArchivedReportsToInvalidate_shouldNotForgetAnythingIfThereIsNoMatch()
    {
        $this->rememberReportsForManySitesAndDates();

        // site does not match
        $this->invalidator->forgetRememberedArchivedReportsToInvalidate(10, Date::factory('2014-04-05'));
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertSame($this->getRememberedReportsByDate(), $reports);

        // date does not match
        $this->invalidator->forgetRememberedArchivedReportsToInvalidate(7, Date::factory('2012-04-05'));
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertSame($this->getRememberedReportsByDate(), $reports);
    }

    public function test_forgetRememberedArchivedReportsToInvalidate_shouldOnlyDeleteReportBelongingToThatSiteAndDate()
    {
        $this->rememberReportsForManySitesAndDates();

        $this->invalidator->forgetRememberedArchivedReportsToInvalidate(2, Date::factory('2014-04-05'));
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $expected = array(
            '2014-04-05' => array(1, 4, 7),
            '2014-05-05' => array(2, 5),
            '2014-04-06' => array(3),
            '2014-04-08' => array(7),
            '2014-05-08' => array(7),
        );
        $this->assertSame($expected, $reports);

        unset($expected['2014-05-08']);

        $this->invalidator->forgetRememberedArchivedReportsToInvalidate(7, Date::factory('2014-05-08'));
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertSame($expected, $reports);
    }

    public function test_markArchivesAsInvalidated_shouldForgetInvalidatedSitesAndDates()
    {
        $this->rememberReportsForManySitesAndDates();

        $idSites = array(2, 10, 7, 5);
        $dates = array(
            Date::factory('2014-04-05'),
            Date::factory('2014-04-08'),
            Date::factory('2010-10-10'),
        );

        $this->invalidator->markArchivesAsInvalidated($idSites, $dates, 'week');
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $expected = array(
            '2014-04-05' => array(1, 4),
            '2014-05-05' => array(2, 5),
            '2014-04-06' => array(3),
            '2014-05-08' => array(7),
        );
        $this->assertSame($expected, $reports);
    }

    private function rememberReport($idSite, $date)
    {
        $date = Date::factory($date);
        $this->invalidator->rememberToInvalidateArchivedReportsLater($idSite, $date);
    }

    private function getRememberedReportsByDate()
    {
        return array(
            '2014-04-05' => array(1, 2, 4, 7),
            '2014-05-05' => array(2, 5),
            '2014-04-06' => array(3),
            '2014-04-08' => array(7),
            '2014-05-08' => array(7),
        );
    }

    private function rememberReportsForManySitesAndDates()
    {
        $this->rememberReport(2, '2014-04-05');
        $this->rememberReport(2, '2014-04-05'); // should appear only once for this site and date
        $this->rememberReport(3, '2014-04-06');
        $this->rememberReport(1, '2014-04-05');
        $this->rememberReport(2, '2014-05-05');
        $this->rememberReport(5, '2014-05-05');
        $this->rememberReport(4, '2014-04-05');
        $this->rememberReport(7, '2014-04-05');
        $this->rememberReport(7, '2014-05-08');
        $this->rememberReport(7, '2014-04-08');
    }

    public function test_markArchivesAsInvalidated_DoesNotInvalidateDatesBeforePurgeThreshold()
    {
        PrivacyManager::savePurgeDataSettings(array(
            'delete_logs_enable' => 1,
            'delete_logs_older_than' => 180,
        ));

        $dateBeforeThreshold = Date::factory('today')->subDay(190);
        $thresholdDate = Date::factory('today')->subDay(180);
        $dateAfterThreshold = Date::factory('today')->subDay(170);

        // can't test more than day since today will change, causing the test to fail w/ other periods randomly
        $this->insertArchiveRow(1, $dateBeforeThreshold, 'day');
        $this->insertArchiveRow(1, $dateAfterThreshold, 'day');

        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');
        $result = $archiveInvalidator->markArchivesAsInvalidated(array(1), array($dateBeforeThreshold, $dateAfterThreshold), 'day');

        $this->assertEquals($thresholdDate->toString(), $result->minimumDateWithLogs);

        $expectedProcessedDates = array($dateAfterThreshold->toString());
        $this->assertEquals($expectedProcessedDates, $result->processedDates);

        $expectedWarningDates = array($dateBeforeThreshold->toString());
        $this->assertEquals($expectedWarningDates, $result->warningDates);

        $invalidatedArchives = $this->getInvalidatedIdArchives();

        $countInvalidatedArchives = 0;
        foreach ($invalidatedArchives as $idarchives) {
            $countInvalidatedArchives += count($idarchives);
        }

        $this->assertEquals(1, $countInvalidatedArchives);
    }

    public function test_markArchivesAsInvalidated_CorrectlyModifiesDistributedLists()
    {
        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');

        $idSites = array(1, 3, 5);
        $dates = array(
            Date::factory('2014-12-31'),
            Date::factory('2015-01-01'),
            Date::factory('2015-01-10'),
        );
        $archiveInvalidator->markArchivesAsInvalidated($idSites, $dates, 'day');

        $idSites = array(1, 3, 5);
        $dates = array(
            Date::factory('2014-12-21'),
            Date::factory('2015-01-01'),
            Date::factory('2015-03-08'),
        );
        $archiveInvalidator->markArchivesAsInvalidated($idSites, $dates, 'week');

        $expectedSitesToProcessListContents = array(1, 3, 5);
        $this->assertEquals($expectedSitesToProcessListContents, $this->getSitesToReprocessListContents());

        $expectedArchivesToPurgeListContents = array('2014_12', '2014_01', '2015_01', '2015_03');
        $this->assertEquals($expectedArchivesToPurgeListContents, $this->getArchivesToPurgeListContents());
    }

    /**
     * @dataProvider getTestDataForMarkArchivesAsInvalidated
     */
    public function test_markArchivesAsInvalidated_MarksCorrectArchivesAsInvalidated($idSites, $dates, $period, $segment, $cascadeDown, $expectedIdArchives)
    {
        $dates = array_map(array('Piwik\Date', 'factory'), $dates);

        $this->insertArchiveRowsForTest();

        if (!empty($segment)) {
            $segment = new Segment($segment, $idSites);
        }

        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');
        $result = $archiveInvalidator->markArchivesAsInvalidated($idSites, $dates, $period, $segment, $cascadeDown);

        $this->assertEquals($dates, $result->processedDates);

        $idArchives = $this->getInvalidatedArchives();

        // Remove empty values (some new empty entries may be added each month)
        $idArchives = array_filter($idArchives);
        $expectedIdArchives = array_filter($expectedIdArchives);

        $this->assertEquals($expectedIdArchives, $idArchives);
    }

    public function getTestDataForMarkArchivesAsInvalidated()
    {
        // $idSites, $dates, $period, $segment, $cascadeDown, $expectedIdArchives
        return array(
            // day period, multiple sites, multiple dates across tables, cascade = true
            array(
                array(1, 2),
                array('2015-01-01', '2015-02-05', '2015-04-30'),
                'day',
                null,
                true,
                array(
                    '2015_04' => array(
                        '1.2015-04-30.2015-04-30.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '2.2015-04-30.2015-04-30.1.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-04-27.2015-05-03.2.done',
                        '2.2015-04-27.2015-05-03.2.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-04-01.2015-04-30.3.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '2.2015-04-01.2015-04-30.3.done5447835b0a861475918e79e932abdfd8',
                    ),
                    '2015_01' => array(
                        '1.2015-01-01.2015-01-01.1.done3736b708e4d20cfc10610e816a1b2341',
                        '2.2015-01-01.2015-01-01.1.done.VisitsSummary',
                        '1.2015-01-01.2015-01-31.3.done3736b708e4d20cfc10610e816a1b2341',
                        '2.2015-01-01.2015-01-31.3.done.VisitsSummary',
                        '1.2015-01-01.2015-12-31.4.done5447835b0a861475918e79e932abdfd8',
                        '2.2015-01-01.2015-12-31.4.done',
                        '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                    ),
                    '2015_02' => array(
                        '1.2015-02-05.2015-02-05.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '2.2015-02-05.2015-02-05.1.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-02-02.2015-02-08.2.done',
                        '2.2015-02-02.2015-02-08.2.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-02-01.2015-02-28.3.done.VisitsSummary',
                        '2.2015-02-01.2015-02-28.3.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    ),
                ),
            ),

            // month period, one site, one date, cascade = false
            array(
                array(1),
                array('2015-01-01'),
                'month',
                null,
                false,
                array(
                    '2015_01' => array(
                        '1.2015-01-01.2015-01-31.3.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-01.2015-12-31.4.done5447835b0a861475918e79e932abdfd8',
                    ),
                ),
            ),

            // month period, one site, one date, cascade = true
            array(
                array(1),
                array('2015-01-15'),
                'month',
                null,
                true,
                array(
                    '2014_12' => array(
                        '1.2014-12-29.2015-01-04.2.done3736b708e4d20cfc10610e816a1b2341',

                        // doesn't need to be invalidated since the month won't use the week above, but very difficult
                        // to keep it valid, while keeping invalidation logic simple.
                        '1.2014-12-01.2014-12-31.3.done5447835b0a861475918e79e932abdfd8',
                    ),
                    '2015_01' => array(
                        '1.2015-01-01.2015-01-01.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-02.2015-01-02.1.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-01-03.2015-01-03.1.done.VisitsSummary',
                        '1.2015-01-04.2015-01-04.1.done',
                        '1.2015-01-05.2015-01-05.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-06.2015-01-06.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-07.2015-01-07.1.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-01-08.2015-01-08.1.done.VisitsSummary',
                        '1.2015-01-09.2015-01-09.1.done',
                        '1.2015-01-10.2015-01-10.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-11.2015-01-11.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-12.2015-01-12.1.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-01-13.2015-01-13.1.done.VisitsSummary',
                        '1.2015-01-14.2015-01-14.1.done',
                        '1.2015-01-15.2015-01-15.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-16.2015-01-16.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-17.2015-01-17.1.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-01-18.2015-01-18.1.done.VisitsSummary',
                        '1.2015-01-19.2015-01-19.1.done',
                        '1.2015-01-20.2015-01-20.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-21.2015-01-21.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-22.2015-01-22.1.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-01-23.2015-01-23.1.done.VisitsSummary',
                        '1.2015-01-24.2015-01-24.1.done',
                        '1.2015-01-25.2015-01-25.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-26.2015-01-26.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-27.2015-01-27.1.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-01-28.2015-01-28.1.done.VisitsSummary',
                        '1.2015-01-29.2015-01-29.1.done',
                        '1.2015-01-30.2015-01-30.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-31.2015-01-31.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-05.2015-01-11.2.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-01-12.2015-01-18.2.done.VisitsSummary',
                        '1.2015-01-19.2015-01-25.2.done',
                        '1.2015-01-26.2015-02-01.2.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-01.2015-01-31.3.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-01.2015-12-31.4.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                    ),
                ),
            ),

            // week period, one site, multiple dates w/ redundant dates & periods, cascade = true
            array(
                array(1),
                array('2015-01-02', '2015-01-03', '2015-01-31'),
                'week',
                null,
                true,
                array(
                    '2014_12' => array(
                        '1.2014-12-29.2014-12-29.1.done',
                        '1.2014-12-30.2014-12-30.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2014-12-31.2014-12-31.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2014-12-29.2015-01-04.2.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2014-12-01.2014-12-31.3.done5447835b0a861475918e79e932abdfd8',
                        '1.2014-12-05.2015-01-01.5.done.VisitsSummary',
                    ),
                    '2015_01' => array(
                        '1.2015-01-01.2015-01-01.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-02.2015-01-02.1.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-01-03.2015-01-03.1.done.VisitsSummary',
                        '1.2015-01-04.2015-01-04.1.done',
                        '1.2015-01-26.2015-01-26.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-27.2015-01-27.1.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-01-28.2015-01-28.1.done.VisitsSummary',
                        '1.2015-01-29.2015-01-29.1.done',
                        '1.2015-01-30.2015-01-30.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-31.2015-01-31.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-26.2015-02-01.2.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-01.2015-01-31.3.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-01.2015-12-31.4.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                    ),
                    '2015_02' => array(
                        '1.2015-02-01.2015-02-01.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-02-01.2015-02-28.3.done.VisitsSummary',
                    ),
                ),
            ),

            // range period, one site, cascade = true
            array(
                array(1),
                array('2015-01-02', '2015-03-05'),
                'range',
                null,
                true,
                array(
                    '2015_01' => array(
                        '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                    ),
                    '2015_03' => array(
                        '1.2015-03-04.2015-03-05.5.done.VisitsSummary',
                        '1.2015-03-05.2015-03-10.5.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    ),
                ),
            ),

            // week period, one site, cascade = true, segment
            array(
                array(1),
                array('2015-01-05'),
                'month',
                self::TEST_SEGMENT_1,
                true,
                array(
                    '2014_12' => array(
                        '1.2014-12-29.2015-01-04.2.done3736b708e4d20cfc10610e816a1b2341',
                    ),
                    '2015_01' => array(
                        '1.2015-01-01.2015-01-01.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-05.2015-01-05.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-06.2015-01-06.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-10.2015-01-10.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-11.2015-01-11.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-15.2015-01-15.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-16.2015-01-16.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-20.2015-01-20.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-21.2015-01-21.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-25.2015-01-25.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-26.2015-01-26.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-30.2015-01-30.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-31.2015-01-31.1.done3736b708e4d20cfc10610e816a1b2341',
                        '1.2015-01-26.2015-02-01.2.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-01-01.2015-01-31.3.done3736b708e4d20cfc10610e816a1b2341',
                    ),
                ),
            ),

            // removing all periods
            array(
                array(1),
                array('2015-05-05'),
                '',
                null,
                false,
                array(
                    '2015_01' => array(
                        '1.2015-01-01.2015-12-31.4.done5447835b0a861475918e79e932abdfd8',
                    ),
                    '2015_05' => array(
                        '1.2015-05-05.2015-05-05.1.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                        '1.2015-05-04.2015-05-10.2.done5447835b0a861475918e79e932abdfd8',
                        '1.2015-05-01.2015-05-31.3.done3736b708e4d20cfc10610e816a1b2341',
                    ),
                ),
            ),
        );
    }

    private function getInvalidatedIdArchives()
    {
        $result = array();
        foreach (ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::NUMERIC_TABLE) as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);

            $idArchives = Db::fetchAll("SELECT idarchive FROM $table WHERE name LIKE 'done%' AND value = ?", array(ArchiveWriter::DONE_INVALIDATED));
            $idArchives = array_map('reset', $idArchives);

            $result[$date] = $idArchives;
        }
        return $result;
    }

    private function getInvalidatedArchives()
    {
        $result = array();
        foreach (ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::NUMERIC_TABLE) as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);

            $sql = "SELECT CONCAT(idsite, '.', date1, '.', date2, '.', period, '.', name) FROM $table WHERE name LIKE 'done%' AND value = ?";

            $archiveSpecs = Db::fetchAll($sql, array(ArchiveWriter::DONE_INVALIDATED));
            $archiveSpecs = array_map('reset', $archiveSpecs);

            $result[$date] = $archiveSpecs;
        }
        return $result;
    }

    private function insertArchiveRowsForTest()
    {
        $periods = array('day', 'week', 'month', 'year');
        $sites = array(1,2,3);

        $startDate = Date::factory('2014-12-01');
        $endDate = Date::factory('2015-05-31');

        foreach ($periods as $periodLabel) {
            $nextEndDate = $endDate->addPeriod(1, $periodLabel);
            for ($date = $startDate; $date->isEarlier($nextEndDate); $date = $date->addPeriod(1, $periodLabel)) {
                foreach ($sites as $idSite) {
                    $this->insertArchiveRow($idSite, $date->toString(), $periodLabel);
                }
            }
        }

        $rangePeriods = array('2015-03-04,2015-03-05', '2014-12-05,2015-01-01', '2015-03-05,2015-03-10', '2015-01-01,2015-01-10');
        foreach ($rangePeriods as $dateRange) {
            $this->insertArchiveRow($idSite = 1, $dateRange, 'range');
        }
    }

    private function insertArchiveRow($idSite, $date, $periodLabel)
    {
        $periodObject = \Piwik\Period\Factory::build($periodLabel, $date);
        $dateStart = $periodObject->getDateStart();
        $dateEnd = $periodObject->getDateEnd();

        $table = ArchiveTableCreator::getNumericTable($dateStart);

        $idArchive = (int) Db::fetchOne("SELECT MAX(idarchive) FROM $table WHERE name LIKE 'done%'");
        $idArchive = $idArchive + 1;

        $periodId = Piwik::$idPeriods[$periodLabel];

        $doneFlag = 'done';
        if ($idArchive % 5 == 1) {
            $doneFlag = Rules::getDoneFlagArchiveContainsAllPlugins(self::$segment1);
        } else if ($idArchive % 5 == 2) {
            $doneFlag .= '.VisitsSummary';
        } else if ($idArchive % 5 == 3) {
            $doneFlag = Rules::getDoneFlagArchiveContainsOnePlugin(self::$segment1, 'UserCountry');
        } else if ($idArchive % 5 == 4) {
            $doneFlag = Rules::getDoneFlagArchiveContainsAllPlugins(self::$segment2);
        }

        $sql = "INSERT INTO $table (idarchive, name, idsite, date1, date2, period, ts_archived)
                     VALUES ($idArchive, 'nb_visits', $idSite, '$dateStart', '$dateEnd', $periodId, NOW()),
                            ($idArchive, '$doneFlag', $idSite, '$dateStart', '$dateEnd', $periodId, NOW())";
        Db::query($sql);
    }

    private function getSitesToReprocessListContents()
    {
        $list = new SitesToReprocessDistributedList();
        $values = $list->getAll();
        return array_values($values);
    }

    private function getArchivesToPurgeListContents()
    {
        $list = new ArchivesToPurgeDistributedList();
        $values = $list->getAll();
        return array_values($values);
    }
}
