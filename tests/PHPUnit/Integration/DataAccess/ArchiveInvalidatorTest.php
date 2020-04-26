<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\ArchiveProcessor\ArchivingStatus;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Tests\Framework\Fixture;
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

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // these are static because it takes a long time to create new Segment instances (for some reason)
        self::$segment1 = new Segment(self::TEST_SEGMENT_1, array());
        self::$segment2 = new Segment(self::TEST_SEGMENT_2, array());
    }

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        for ($i = 0; $i != 10; ++$i) {
            Fixture::createWebsite('2012-03-04');
        }
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->invalidator = new ArchiveInvalidator(new Model(), StaticContainer::get(ArchivingStatus::class));
    }

    public function test_rememberToInvalidateArchivedReportsLater_shouldCreateAnEntryInCaseThereIsNoneYet()
    {
        //Updated for change to allow for multiple transactions to invalidate the same report without deadlock.
        $key = 'report_to_invalidate_2_2014-04-05' . '_' . getmypid();
        $this->assertEmpty(Option::getLike('%'. $key . '%'));

        $keyStored = $this->rememberReport(2, '2014-04-05');

        $this->assertStringEndsWith($key, $keyStored);
        $this->assertSame('1', Option::get($keyStored));
    }

    public function test_rememberToInvalidateArchivedReportsLater_shouldNotCreateEntryTwice()
    {
        $this->rememberReport(2, '2014-04-05');
        $this->rememberReport(2, '2014-04-05');
        $this->rememberReport(2, '2014-04-05');

        $this->assertCount(1, Option::getLike('%report_to_invalidate%'));
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

        $this->assertSameReports($this->getRememberedReportsByDate(), $reports);
    }

    private function assertSameReports($expected, $actual)
    {
        $keys1 = array_keys($expected);
        $keys2 = array_keys($actual);
        sort($keys1);
        sort($keys2);

        $this->assertSame($keys1, $keys2);
        foreach ($expected as $index => $values) {
            sort($values);
            sort($actual[$index]);
            $this->assertSame($values, $actual[$index]);
        }
    }

    public function test_forgetRememberedArchivedReportsToInvalidateForSite_shouldNotDeleteAnythingInCaseNoReportForThatSite()
    {
        $this->rememberReportsForManySitesAndDates();

        $this->invalidator->forgetRememberedArchivedReportsToInvalidateForSite(10);
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        $this->assertSameReports($this->getRememberedReportsByDate(), $reports);
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
        $this->assertSameReports($expected, $reports);
    }

    public function test_forgetRememberedArchivedReportsToInvalidate_shouldNotForgetAnythingIfThereIsNoMatch()
    {
        $this->rememberReportsForManySitesAndDates();

        // site does not match
        $this->invalidator->forgetRememberedArchivedReportsToInvalidate(10, Date::factory('2014-04-05'));
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertSameReports($this->getRememberedReportsByDate(), $reports);

        // date does not match
        $this->invalidator->forgetRememberedArchivedReportsToInvalidate(7, Date::factory('2012-04-05'));
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertSameReports($this->getRememberedReportsByDate(), $reports);
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
        $this->assertSameReports($expected, $reports);

        unset($expected['2014-05-08']);

        $this->invalidator->forgetRememberedArchivedReportsToInvalidate(7, Date::factory('2014-05-08'));
        $reports = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertSameReports($expected, $reports);
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
        $this->assertSameReports($expected, $reports);
    }

    private function rememberReport($idSite, $date)
    {
        $date = Date::factory($date);
        return $this->invalidator->rememberToInvalidateArchivedReportsLater($idSite, $date);
    }

    private function getRememberedReportsByDate()
    {
        return array(
            '2014-04-06' => array(3),
            '2014-04-05' => array(4, 7, 2, 1),
            '2014-05-05' => array(5, 2),
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

        // the day, day w/ a segment, week, month & year are invalidated
        $this->assertEquals(1, $countInvalidatedArchives);

        $invalidatedArchiveTableEntries = $this->getInvalidatedArchiveTableEntries();
        $this->assertCount(4, $invalidatedArchiveTableEntries);
    }

    public function test_markArchivesAsInvalidated_InvalidatesCorrectlyWhenNoArchiveTablesExist()
    {
        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');
        $result = $archiveInvalidator->markArchivesAsInvalidated([1], [Date::factory('2016-03-04')], false, null, false);

        $this->assertEquals([
            '2016-03-04',
        ], $result->processedDates);

        $expectedIdArchives = [];

        $idArchives = $this->getInvalidatedArchives();

        // Remove empty values (some new empty entries may be added each month)
        $idArchives = array_filter($idArchives);
        $expectedIdArchives = array_filter($expectedIdArchives);

        $this->assertEquals($expectedIdArchives, $idArchives);

        $expectedEntries = [
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2016-01-01',
                'date2' => '2016-12-31',
                'period' => '4',
                'name' => 'done',
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2016-02-29',
                'date2' => '2016-03-06',
                'period' => '2',
                'name' => 'done',
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2016-03-01',
                'date2' => '2016-03-31',
                'period' => '3',
                'name' => 'done',
            ],
            [
                'idarchive' => null,
                'idsite' => '1',
                'date1' => '2016-03-04',
                'date2' => '2016-03-04',
                'period' => '1',
                'name' => 'done',
            ],
        ];

        $invalidatedArchiveTableEntries = $this->getInvalidatedArchiveTableEntries();
        $this->assertEquals($expectedEntries, $invalidatedArchiveTableEntries);
    }

    /**
     * @dataProvider getTestDataForMarkArchivesAsInvalidated
     */
    public function test_markArchivesAsInvalidated_MarksCorrectArchivesAsInvalidated($idSites, $dates, $period, $segment, $cascadeDown, $expectedIdArchives,
                                                                                     $expectedInvalidatedArchives)
    {
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

        $invalidatedIdArchives = $this->getInvalidatedArchiveTableEntries();
        try {
            $this->assertEquals($expectedInvalidatedArchives, $invalidatedIdArchives);
        } catch (\Exception $ex) {
            print "\n";
            var_export($invalidatedIdArchives);
            print "\n";
            throw $ex;
        }
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
                    '2014_12' => [
                        '1.2014-12-29.2015-01-04.2.done3736b708e4d20cfc10610e816a1b2341',
                        '2.2014-12-29.2015-01-04.2.done.VisitsSummary',
                    ],
                ),
                [
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => '3', 'name' => 'done'],
                    ['idarchive' => '85', 'idsite' => '1', 'date1' => '2015-02-02', 'date2' => '2015-02-08', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-02-05', 'date2' => '2015-02-05', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-04-01', 'date2' => '2015-04-30', 'period' => '3', 'name' => 'done'],
                    ['idarchive' => '100', 'idsite' => '1', 'date1' => '2015-04-27', 'date2' => '2015-05-03', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-04-30', 'date2' => '2015-04-30', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '2', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '2', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '2', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done'],
                    ['idarchive' => '110', 'idsite' => '2', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '2', 'date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => '3', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '2', 'date1' => '2015-02-02', 'date2' => '2015-02-08', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '2', 'date1' => '2015-02-05', 'date2' => '2015-02-05', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '2', 'date1' => '2015-04-01', 'date2' => '2015-04-30', 'period' => '3', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '2', 'date1' => '2015-04-27', 'date2' => '2015-05-03', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '2', 'date1' => '2015-04-30', 'date2' => '2015-04-30', 'period' => '1', 'name' => 'done'],
                ],
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
                [
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done'],
                ],
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
                [
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-02', 'date2' => '2015-01-02', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-03', 'date2' => '2015-01-03', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => '10', 'idsite' => '1', 'date1' => '2015-01-04', 'date2' => '2015-01-04', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-05', 'date2' => '2015-01-05', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-05', 'date2' => '2015-01-11', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-06', 'date2' => '2015-01-06', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-07', 'date2' => '2015-01-07', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-08', 'date2' => '2015-01-08', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => '25', 'idsite' => '1', 'date1' => '2015-01-09', 'date2' => '2015-01-09', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-10', 'date2' => '2015-01-10', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-11', 'date2' => '2015-01-11', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-12', 'date2' => '2015-01-12', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-12', 'date2' => '2015-01-18', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-13', 'date2' => '2015-01-13', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => '40', 'idsite' => '1', 'date1' => '2015-01-14', 'date2' => '2015-01-14', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-15', 'date2' => '2015-01-15', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-16', 'date2' => '2015-01-16', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-17', 'date2' => '2015-01-17', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-18', 'date2' => '2015-01-18', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => '55', 'idsite' => '1', 'date1' => '2015-01-19', 'date2' => '2015-01-19', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => '100', 'idsite' => '1', 'date1' => '2015-01-19', 'date2' => '2015-01-25', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-20', 'date2' => '2015-01-20', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-21', 'date2' => '2015-01-21', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-22', 'date2' => '2015-01-22', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-23', 'date2' => '2015-01-23', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => '70', 'idsite' => '1', 'date1' => '2015-01-24', 'date2' => '2015-01-24', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-25', 'date2' => '2015-01-25', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-26', 'date2' => '2015-01-26', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-26', 'date2' => '2015-02-01', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-27', 'date2' => '2015-01-27', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-28', 'date2' => '2015-01-28', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => '85', 'idsite' => '1', 'date1' => '2015-01-29', 'date2' => '2015-01-29', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-30', 'date2' => '2015-01-30', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-31', 'date2' => '2015-01-31', 'period' => '1', 'name' => 'done'],
                ],
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
                    '2014_01' => [
                        '1.2014-01-01.2014-12-31.4.done3736b708e4d20cfc10610e816a1b2341',
                    ],
                ),
                [
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2014-01-01', 'date2' => '2014-12-31', 'period' => '4', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2014-12-01', 'date2' => '2014-12-31', 'period' => '3', 'name' => 'done'],
                    ['idarchive' => '85', 'idsite' => '1', 'date1' => '2014-12-29', 'date2' => '2014-12-29', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2014-12-30', 'date2' => '2014-12-30', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2014-12-31', 'date2' => '2014-12-31', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-02', 'date2' => '2015-01-02', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-03', 'date2' => '2015-01-03', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => '10', 'idsite' => '1', 'date1' => '2015-01-04', 'date2' => '2015-01-04', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-26', 'date2' => '2015-01-26', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-26', 'date2' => '2015-02-01', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-27', 'date2' => '2015-01-27', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-28', 'date2' => '2015-01-28', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => '85', 'idsite' => '1', 'date1' => '2015-01-29', 'date2' => '2015-01-29', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-30', 'date2' => '2015-01-30', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-31', 'date2' => '2015-01-31', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-02-01', 'date2' => '2015-02-01', 'period' => '1', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => '3', 'name' => 'done'],
                ],
            ),

            // range period, exact match, cascade = true
            array(
                array(1),
                array('2015-01-01', '2015-01-10'),
                'range',
                null,
                true,
                array(
                    '2015_01' => array(
                        '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                    ),
                ),
                [
                    // empty
                ],
            ),

            // range period, overlapping a range in the DB
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
                    '2015_03' => [
                        '1.2015-03-04.2015-03-05.5.done.VisitsSummary',
                        '1.2015-03-05.2015-03-10.5.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    ],
                ),
                [
                    // empty
                ],
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
                [
                    ['idarchive' => '106', 'idsite' => '1', 'date1' => '2014-12-29', 'date2' => '2015-01-04', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => '1', 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-01', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => '106', 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-01-31', 'period' => '3', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-02', 'date2' => '2015-01-02', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-03', 'date2' => '2015-01-03', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-04', 'date2' => '2015-01-04', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-05', 'date2' => '2015-01-05', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-05', 'date2' => '2015-01-11', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => '16', 'idsite' => '1', 'date1' => '2015-01-06', 'date2' => '2015-01-06', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-07', 'date2' => '2015-01-07', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-08', 'date2' => '2015-01-08', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-09', 'date2' => '2015-01-09', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-10', 'date2' => '2015-01-10', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => '31', 'idsite' => '1', 'date1' => '2015-01-11', 'date2' => '2015-01-11', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-12', 'date2' => '2015-01-12', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-12', 'date2' => '2015-01-18', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-13', 'date2' => '2015-01-13', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-14', 'date2' => '2015-01-14', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-15', 'date2' => '2015-01-15', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => '46', 'idsite' => '1', 'date1' => '2015-01-16', 'date2' => '2015-01-16', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-17', 'date2' => '2015-01-17', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-18', 'date2' => '2015-01-18', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-19', 'date2' => '2015-01-19', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-19', 'date2' => '2015-01-25', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-20', 'date2' => '2015-01-20', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => '61', 'idsite' => '1', 'date1' => '2015-01-21', 'date2' => '2015-01-21', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-22', 'date2' => '2015-01-22', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-23', 'date2' => '2015-01-23', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-24', 'date2' => '2015-01-24', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-25', 'date2' => '2015-01-25', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => '76', 'idsite' => '1', 'date1' => '2015-01-26', 'date2' => '2015-01-26', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-26', 'date2' => '2015-02-01', 'period' => '2', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-27', 'date2' => '2015-01-27', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-28', 'date2' => '2015-01-28', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-29', 'date2' => '2015-01-29', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-30', 'date2' => '2015-01-30', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                    ['idarchive' => '91', 'idsite' => '1', 'date1' => '2015-01-31', 'date2' => '2015-01-31', 'period' => '1', 'name' => 'done3736b708e4d20cfc10610e816a1b2341'],
                ],
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
                [
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => '4', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-05-01', 'date2' => '2015-05-31', 'period' => '3', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-05-04', 'date2' => '2015-05-10', 'period' => '2', 'name' => 'done'],
                    ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-05-05', 'date2' => '2015-05-05', 'period' => '1', 'name' => 'done'],
                ],
            ),
        );
    }

    /**
     * @dataProvider getTestDataForMarkArchiveRangesAsInvalidated
     */
    public function test_markArchivesAsInvalidated_MarksAllSubrangesOfRange($idSites, $dates, $segment, $expectedIdArchives)
    {
        $dates = array_map(array('Piwik\Date', 'factory'), $dates);

        $this->insertArchiveRowsForTest();

        if (!empty($segment)) {
            $segment = new Segment($segment, $idSites);
        }

        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');
        $result = $archiveInvalidator->markArchivesOverlappingRangeAsInvalidated($idSites, array($dates), $segment);

        $this->assertEquals(array($dates[0]), $result->processedDates);

        $idArchives = $this->getInvalidatedArchives();

        // Remove empty values (some new empty entries may be added each month)
        $idArchives = array_filter($idArchives);
        $expectedIdArchives = array_filter($expectedIdArchives);

        $this->assertEquals($expectedIdArchives, $idArchives);
    }

    public function test_markArchivesAsInvalidated_DoesNotInvalidateInProgressArchives()
    {
        $this->insertArchiveRow(1, '2015-02-03', 'day', $value = ArchiveWriter::DONE_IN_PROGRESS);
        $this->insertArchiveRow(1, '2015-02-07', 'week', $value = ArchiveWriter::DONE_IN_PROGRESS);

        /** @var ArchiveInvalidator $archiveInvalidator */
        $archiveInvalidator = self::$fixture->piwikEnvironment->getContainer()->get('Piwik\Archive\ArchiveInvalidator');
        $result = $archiveInvalidator->markArchivesAsInvalidated([1], [Date::factory('2015-02-07')], 'day');

        $this->assertEquals([Date::factory('2015-02-07')->toString()], array_map('strval', $result->processedDates));

        $idArchives = $this->getInvalidatedArchives($anyTsArchived = false);
        $this->assertEmpty($idArchives);
    }

    public function getTestDataForMarkArchiveRangesAsInvalidated()
    {
        // $idSites, $dates, $segment, $expectedIdArchives
        return array(
            // range period, has an exact match, also a match where DB end date = reference start date
            array(
                array(1),
                array('2015-01-01', '2015-01-10'),
                null,
                array(
                    '2014_12' => array(
                        '1.2014-12-05.2015-01-01.5.done.VisitsSummary',
                    ),
                    '2015_01' => array(
                        '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                    ),
                ),
                [
                    // empty
                ],
            ),

            // range period, overlapping range = a match
            array(
                array(1),
                array('2015-01-02', '2015-03-05'),
                null,
                array(
                    '2015_01' => array(
                        '1.2015-01-01.2015-01-10.5.done.VisitsSummary',
                    ),
                    '2015_03' => array(
                        '1.2015-03-04.2015-03-05.5.done.VisitsSummary',
                        '1.2015-03-05.2015-03-10.5.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    ),
                ),
                [
                    // empty
                ],
            ),

            // range period, small range within the 2014-12-05 to 2015-01-01 range should cause it to be invalidated
            array(
                array(1),
                array('2014-12-18', '2014-12-20'),
                null,
                array(
                    '2014_12' => array(
                        '1.2014-12-05.2015-01-01.5.done.VisitsSummary',
                    ),
                ),
                [
                    // empty
                ],
            ),

            // range period, range that overlaps start of archived range
            array(
                array(1),
                array('2014-12-01', '2014-12-05'),
                null,
                array(
                    '2014_12' => array(
                        '1.2014-12-05.2015-01-01.5.done.VisitsSummary',
                    ),
                ),
                [
                    // empty
                ],
            ),

            // range period, large range that includes the smallest archived range (3 to 4 March)
            array(
                array(1),
                array('2015-01-11', '2015-03-30'),
                null,
                array(
                    '2015_03' => array(
                        '1.2015-03-04.2015-03-05.5.done.VisitsSummary',
                        '1.2015-03-05.2015-03-10.5.done3736b708e4d20cfc10610e816a1b2341.UserCountry',
                    ),
                ),
                [
                    // empty
                ],
            ),

            // range period, doesn't match any archived ranges
            array(
                array(1),
                array('2014-12-01', '2014-12-04'),
                null,
                array(
                ),
                [
                    // empty
                ],
            ),

            // three-month range period, there's a range archive for the middle month
            array(
                array(1),
                array('2014-09-01', '2014-11-08'),
                null,
                array(
                    '2014_10' => array(
                        '1.2014-10-15.2014-10-20.5.done3736b708e4d20cfc10610e816a1b2341',
                    ),
                ),
                [
                    // empty
                ],
            ),
        );
    }

    public function test_markArchivesAsInvalidated_forceInvalidatesNonExistantRangesWhenRequired()
    {
        $archives = $this->getInvalidatedArchives();
        $this->assertEmpty($archives);

        $this->invalidator->markArchivesAsInvalidated([1], ['2015-03-04,2015-03-06', '2016-04-03,2016-05-12'], 'range', null, false);

        $archives = $this->getInvalidatedArchives();
        $this->assertEmpty($archives);

        $this->invalidator->markArchivesAsInvalidated([1], ['2015-03-04,2015-03-06', '2016-04-03,2016-05-12'], 'range', null, false, true);

        $archives = $this->getInvalidatedArchives();
        $this->assertEquals([], $archives);

        $expectedInvalidatedTableEntries = [
            ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2015-03-04', 'date2' => '2015-03-06', 'period' => '5', 'name' => 'done'],
            ['idarchive' => NULL, 'idsite' => '1', 'date1' => '2016-04-03', 'date2' => '2016-05-12', 'period' => '5', 'name' => 'done'],
        ];

        $invalidatedTableEntries = $this->getInvalidatedArchiveTableEntries();
        $this->assertEquals($expectedInvalidatedTableEntries, $invalidatedTableEntries);
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

    private function getInvalidatedArchives($anyTsArchived = true)
    {
        $result = array();
        foreach (ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::NUMERIC_TABLE) as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);

            $sql = "SELECT CONCAT(idsite, '.', date1, '.', date2, '.', period, '.', name) FROM $table WHERE name LIKE 'done%' AND value = ?";
            if (!$anyTsArchived) {
                $sql .= " AND ts_archived IS NOT NULL";
            }

            $archiveSpecs = Db::fetchAll($sql, array(ArchiveWriter::DONE_INVALIDATED));
            $archiveSpecs = array_map('reset', $archiveSpecs);
            if (empty($archiveSpecs)) {
                continue;
            }

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

        $rangePeriods = array(
            '2015-03-04,2015-03-05', 
            '2014-12-05,2015-01-01', 
            '2015-03-05,2015-03-10', 
            '2015-01-01,2015-01-10',
            '2014-10-15,2014-10-20'
        );
        foreach ($rangePeriods as $dateRange) {
            $this->insertArchiveRow($idSite = 1, $dateRange, 'range');
        }
    }

    private function insertArchiveRow($idSite, $date, $periodLabel, $doneValue = ArchiveWriter::DONE_OK)
    {
        $periodObject = \Piwik\Period\Factory::build($periodLabel, $date);
        $dateStart = $periodObject->getDateStart();
        $dateEnd = $periodObject->getDateEnd();

        $table = ArchiveTableCreator::getNumericTable($dateStart);

        $model = new Model();
        $idArchive = $model->allocateNewArchiveId($table);

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

        $sql = "INSERT INTO $table (idarchive, name, value, idsite, date1, date2, period, ts_archived)
                     VALUES ($idArchive, 'nb_visits', 1, $idSite, '$dateStart', '$dateEnd', $periodId, NOW()),
                            ($idArchive, '$doneFlag', $doneValue, $idSite, '$dateStart', '$dateEnd', $periodId, NOW())";
        Db::query($sql);
    }

    private function getInvalidatedArchiveTableEntries()
    {
        return Db::fetchAll("SELECT idarchive, idsite, date1, date2, period, name FROM " . Common::prefixTable('archive_invalidations'));
    }
}
