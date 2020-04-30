<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Integration\ArchiveProcessor;


use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Loader;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period\Factory;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class LoaderTest extends IntegrationTestCase
{
    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        Fixture::createWebsite('2012-02-03 00:00:00');
        Fixture::createWebsite('2012-02-03 00:00:00');
    }

    public function test_loadExistingArchiveIdFromDb_returnsFalsesIfNoArchiveFound()
    {
        $params = new Parameters(new Site(1), Factory::build('day', '2015-03-03'), new Segment('', [1]));
        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();

        $this->assertEquals([false, false, false, false, false], $archiveInfo);
    }

    /**
     * @dataProvider getTestDataForLoadExistingArchiveIdFromDbDebugConfig
     */
    public function test_loadExistingArchiveIdFromDb_returnsFalsesPeriodIsForcedToArchive($periodType, $configSetting)
    {
        $date = $periodType == 'range' ? '2015-03-03,2015-03-04' : '2015-03-03';
        $params = new Parameters(new Site(1), Factory::build($periodType, $date), new Segment('', [1]));
        $this->insertArchive($params);

        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();

        $this->assertNotEmpty($archiveInfo[4]);
        $this->assertLessThanOrEqual(time(), strtotime($archiveInfo[4]));
        unset($archiveInfo[4]);

        $this->assertNotEquals([false, false, false, false], $archiveInfo);

        Config::getInstance()->Debug[$configSetting] = 1;

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();
        $this->assertEquals([false, false, false, false], $archiveInfo);
    }

    public function getTestDataForLoadExistingArchiveIdFromDbDebugConfig()
    {
        return [
            ['day', 'always_archive_data_day'],
            ['week', 'always_archive_data_period'],
            ['month', 'always_archive_data_period'],
            ['year', 'always_archive_data_period'],
            ['range', 'always_archive_data_range'],
        ];
    }

    public function test_loadExistingArchiveIdFromDb_returnsArchiveIfArchiveInThePast()
    {
        $params = new Parameters(new Site(1), Factory::build('month', '2015-03-03'), new Segment('', [1]));
        $this->insertArchive($params);

        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();

        $this->assertNotEmpty($archiveInfo[4]);
        unset($archiveInfo[4]);

        $this->assertEquals([['1'], '10', '0', true], $archiveInfo);
    }

    public function test_loadExistingArchiveIdFromDb_returnsArchiveIfForACurrentPeriod_AndNewEnough()
    {
        $params = new Parameters(new Site(1), Factory::build('day', 'now'), new Segment('', [1]));
        $this->insertArchive($params, $tsArchived = time() - 1);

        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();

        $this->assertNotEmpty($archiveInfo[4]);
        unset($archiveInfo[4]);

        $this->assertEquals([['1'], '10', '0', true], $archiveInfo);
    }

    public function test_loadExistingArchiveIdFromDb_returnsNoArchiveIfForACurrentPeriod_AndNoneAreNewEnough()
    {
        $params = new Parameters(new Site(1), Factory::build('month', 'now'), new Segment('', [1]));
        $this->insertArchive($params, $tsArchived = time() - 3 * 3600);

        $loader = new Loader($params);

        $archiveInfo = $loader->loadExistingArchiveIdFromDb();

        $this->assertNotEmpty($archiveInfo[4]);
        unset($archiveInfo[4]);

        $this->assertEquals([false, '10', '0', true], $archiveInfo); // visits are still returned as this was the original behavior
    }

    /**
     * @dataProvider getTestDataForGetReportsToInvalidate
     */
    public function test_getReportsToInvalidate_returnsCorrectReportsToInvalidate($rememberedReports, $idSite, $period, $date, $segment, $expected)
    {
        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        foreach ($rememberedReports as $entry) {
            $invalidator->rememberToInvalidateArchivedReportsLater($entry['idSite'], Date::factory($entry['date']));
        }

        $params = new Parameters(new Site($idSite), Factory::build($period, $date), new Segment($segment, [$idSite]));
        $loader = new Loader($params);

        $reportsToInvalidate = $loader->getReportsToInvalidate();
        foreach ($reportsToInvalidate as &$sites) {
            sort($sites);
        }
        $this->assertEquals($expected, $reportsToInvalidate);
    }

    public function getTestDataForGetReportsToInvalidate()
    {
        return [
            // two dates for one site
            [
                [
                    ['idSite' => 1, 'date' => '2013-04-05'],
                    ['idSite' => 1, 'date' => '2013-03-05'],
                    ['idSite' => 2, 'date' => '2013-05-05'],
                ],
                1,
                'day',
                '2013-04-05',
                '',
                [
                    '2013-04-05' => [1],
                ],
            ],

            // no dates for a site
            [
                [
                    ['idSite' => '', 'date' => '2013-04-05'],
                    ['idSite' => '', 'date' => '2013-04-06'],
                    ['idSite' => 2, 'date' => '2013-05-05'],
                ],
                1,
                'day',
                '2013-04-05',
                'browserCode==ff',
                [],
            ],

            // day period not within range
            [
                [
                    ['idSite' => 1, 'date' => '2014-03-04'],
                    ['idSite' => 1, 'date' => '2014-03-06'],
                ],
                1,
                'day',
                '2013-03-05',
                '',
                [],
            ],

            // non-day periods
            [
                [
                    ['idSite' => 1, 'date' => '2014-03-01'],
                    ['idSite' => 1, 'date' => '2014-03-06'],
                    ['idSite' => 2, 'date' => '2014-03-01'],
                ],
                1,
                'week',
                '2014-03-01',
                '',
                [
                    '2014-03-01' => [1, 2],
                ],
            ],
            [
                [
                    ['idSite' => 1, 'date' => '2014-02-01'],
                    ['idSite' => 1, 'date' => '2014-03-06'],
                    ['idSite' => 2, 'date' => '2014-03-05'],
                    ['idSite' => 2, 'date' => '2014-03-06'],
                ],
                1,
                'month',
                '2014-03-01',
                '',
                [
                    '2014-03-06' => [1, 2],
                ],
            ],
        ];
    }

    public function test_canSkipThisArchive_returnsFalseIfSiteIsNotUsingTracker()
    {
        Piwik::addAction('CronArchive.getIdSitesNotUsingTracker', function (&$idSites) {
            $idSites[] = 1;
        });

        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('', []));
        $loader = new Loader($params);

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function test_canSkipThisArchive_returnsFalseIfSiteHasVisitWithinTimeframe_ForPeriodDay()
    {
        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('', []));
        $loader = new Loader($params);

        $tracker = Fixture::getTracker(1, '2016-02-03 04:00:00');
        $tracker->setUrl('http://example.org/abc');
        Fixture::checkResponse($tracker->doTrackPageView('abc'));

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function test_canSkipThisArchive_returnsFalseIfSiteHasVisitWithinTimeframe_ForPeriodYear()
    {
        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('', []));
        $loader = new Loader($params);

        $tracker = Fixture::getTracker(1, '2016-03-04 00:00:00');
        $tracker->setUrl('http://example.org/abc');
        Fixture::checkResponse($tracker->doTrackPageView('abc'));

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function test_canSkipThisArchive_returnsFalseIfSiteHasChildArchiveWithinPeriod_ForPeriodWeek()
    {
        $params = new Parameters(new Site(1), Factory::build('week', '2016-02-03'), new Segment('browserCode==ch', []));
        $loader = new Loader($params);

        $dayParams = new Parameters(new Site(1), Factory::build('day', '2016-02-03'), new Segment('', []));

        $archiveWriter = new ArchiveWriter($dayParams);
        $archiveWriter->initNewArchive();
        $archiveWriter->finalizeArchive();

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function test_canSkipThisArchive_returnsFalseIfSiteHasChildArchiveWithinPeriod_ForPeriodMonth_WhenWeekChildSpansTwoMonths()
    {
        $params = new Parameters(new Site(1), Factory::build('month', '2016-02-01'), new Segment('browserCode==ch', []));
        $loader = new Loader($params);

        $dayParams = new Parameters(new Site(1), Factory::build('week', '2016-02-01'), new Segment('', []));

        $archiveWriter = new ArchiveWriter($dayParams);
        $archiveWriter->initNewArchive();
        $archiveWriter->finalizeArchive();

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function test_canSkipThisArchive_returnsFalseIfSiteHasChildArchiveWithinPeriod_ForPeriodYear()
    {
        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('browserCode==ch', []));
        $loader = new Loader($params);

        $dayParams = new Parameters(new Site(1), Factory::build('day', '2016-03-04'), new Segment('', []));

        $archiveWriter = new ArchiveWriter($dayParams);
        $archiveWriter->initNewArchive();
        $archiveWriter->finalizeArchive();

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function test_canSkipThisArchive_returnsTrueIfThereAreNoVisits_NoChildArchives_AndSiteIsUsingTheTracker()
    {
        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('', []));
        $loader = new Loader($params);

        $this->assertTrue($loader->canSkipThisArchive());

        $tracker = Fixture::getTracker(2, '2016-03-04 00:00:00');
        $tracker->setUrl('http://example.org/abc');
        Fixture::checkResponse($tracker->doTrackPageView('abc'));

        $this->assertTrue($loader->canSkipThisArchive());
    }

    public function test_canSkipThisArchive_ignoresSegments()
    {
        $params = new Parameters(new Site(1), Factory::build('year', '2016-02-03'), new Segment('browserCode==ch', []));
        $loader = new Loader($params);

        $tracker = Fixture::getTracker(1, '2016-03-04 00:00:00');
        $tracker->setUrl('http://example.org/abc');
        Fixture::checkResponse($tracker->doTrackPageView('abc'));

        $this->assertFalse($loader->canSkipThisArchive());
    }

    public function test_forcePluginArchiving_createsPluginSpecificArchive()
    {
        $_GET['trigger'] = 'archivephp';
        $_GET['pluginOnly'] = '1';

        $params = new Parameters(new Site(1), Factory::build('day', '2016-02-03'), new Segment('', [1]));
        $loader = new Loader($params);

        $tracker = Fixture::getTracker(1, '2016-02-03 00:00:00');
        $tracker->setUrl('http://example.org/abc');
        Fixture::checkResponse($tracker->doTrackPageView('abc'));

        $idArchive = $loader->prepareArchive('Actions')[0];
        $this->assertNotEmpty($idArchive);

        $table = ArchiveTableCreator::getNumericTable(Date::factory('2016-02-03'));
        $doneFlag = Db::fetchOne("SELECT `name` FROM `$table` WHERE `name` LIKE 'done%' AND idarchive = $idArchive");
        $this->assertEquals('done.Actions', $doneFlag);
    }

    private function insertArchive(Parameters $params, $tsArchived = null, $visits = 10)
    {
        $archiveWriter = new ArchiveWriter($params);
        $archiveWriter->initNewArchive();
        $archiveWriter->insertRecord('nb_visits', $visits);
        $archiveWriter->finalizeArchive();

        if ($tsArchived) {
            Db::query("UPDATE " . ArchiveTableCreator::getNumericTable($params->getPeriod()->getDateStart()) . " SET ts_archived = ?",
                [Date::factory($tsArchived)->getDatetime()]);
        }
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}