<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\tests\System;

use Piwik\Archive\ArchivePurger;
use Piwik\ArchiveProcessor\Rules;
use Piwik\CronArchive;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\Model;
use Piwik\Http;
use Piwik\Log\Logger;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Site;
use Piwik\Tests\Framework\TestingEnvironmentVariables;
use Piwik\Container\Container;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Segment;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\ManySitesImportedLogs;
use Piwik\Tests\Framework\Fixture;

/**
 * Tests to call the cron core:archive command script and check there is no error,
 * Then call the API testing for "Browser archiving is disabled" (see disableArchiving)
 * This tests that, when archiving is disabled,
 *  then Piwik API will return data that was pre-processed during archive.php run
 *
 * @group Core
 * @group ArchiveCronTest
 */
class ArchiveCronTest extends SystemTestCase
{
    public const NEW_SEGMENT = 'operatingSystemCode==IOS';
    public const NEW_SEGMENT_NAME = 'segmentForToday';
    public const ENCODED_SEGMENT = 'pageUrl=@%252F';
    public const ENCODED_SEGMENT_NAME = 'segmentWithEncoding';

    /**
     * @var ManySitesImportedLogs
     */
    public static $fixture = null; // initialized below class definition

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Db::exec("UPDATE " . Common::prefixTable('site') . ' SET ts_created = \'2005-01-02 00:00:00\'');
        Site::clearCache();
    }

    private static function trackVisitInPast($ip = null)
    {
        $t = Fixture::getTracker(self::$fixture->idSite, '2012-08-09 16:00:00');
        if ($ip) {
            $t->setIp($ip);
        }
        $t->setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148');
        $t->setUrl('http://pastwebsite.com/here/we/go');
        Fixture::checkResponse($t->doTrackPageView('a page title'));

        // update ts_archived for archives in 2012_08 so they will be invalidated and rearchived
        $sql = "UPDATE " . ArchiveTableCreator::getNumericTable(Date::factory('2012-08-01')) . " SET ts_archived = ?";
        Db::query($sql, [Date::now()->subHour(2)->getDatetime()]);
    }

    private static function addNewSegmentToPast()
    {
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'enable_browser_archiving_triggering', 0);
        self::$fixture->getTestEnvironment()->save();

        Rules::setBrowserTriggerArchiving(false);

        // add one segment and set it's created/updated time to some time in the past so we don't re-archive for it
        $idSegment = API::getInstance()->add(self::NEW_SEGMENT_NAME, self::NEW_SEGMENT, self::$fixture->idSite, $autoArchive = 1, $enabledAllUsers = 1);

        // add another segment w/ special encoded value (but do it through the API which will use a definition query param which is not
        // encoded the same as Request::getRawSegmentFromRequest()
        $url = Fixture::getTestRootUrl() . '?' . http_build_query([
            'module' => 'API',
            'method' => 'SegmentEditor.add',
            'name' => self::ENCODED_SEGMENT_NAME,
            'definition' => self::ENCODED_SEGMENT,
            'idSite' => self::$fixture->idSite,
            'autoArchive' => 1,
            'enabledAllUsers' => 1,
            'format' => 'json',
            'token_auth' => Fixture::getTokenAuth(),
        ]);
        self::assertStringContainsString(urlencode(self::ENCODED_SEGMENT), $url);

        $idSegment2 = Http::sendHttpRequest($url, 10);
        $idSegment2 = json_decode($idSegment2, $isAssoc = true);
        $idSegment2 = $idSegment2['value'];

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'enable_browser_archiving_triggering', 1);
        self::$fixture->getTestEnvironment()->save();

        Rules::setBrowserTriggerArchiving(true);

        Db::exec("UPDATE " . Common::prefixTable('segment') . ' SET ts_created = \'2015-01-02 00:00:00\', ts_last_edit = \'2015-01-02 00:00:00\' WHERE idsegment IN (' . $idSegment . ", " . $idSegment2 . ")");
    }

    private static function trackVisitsForToday()
    {
        $startTime = Date::today()->addHour(12)->getDatetime();

        $t = Fixture::getTracker(self::$fixture->idSite, $startTime);
        $t->setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148');
        $t->setUrl('http://awebsite.com/here/we/go');
        Fixture::checkResponse($t->doTrackPageView('a page title'));

        $t->setUrl('http://awebsite.com/another/page');
        $t->setForceVisitDateTime(Date::factory($startTime)->addHour(1));
        Fixture::checkResponse($t->doTrackPageView('a second page title'));
    }

    public function getApiForTesting()
    {
        $apiRequiringSegments = ['Goals.get', 'VisitFrequency.get'];

        $results = array();

        foreach (self::$fixture->getDefaultSegments() as $segmentName => $info) {
            $results[] = array('VisitsSummary.get', array('idSite'     => 'all',
                                                          'date'       => '2012-08-09',
                                                          'periods'    => array('day', 'week', 'month', 'year'),
                                                          'segment'    => $info['definition'],
                                                          'testSuffix' => '_' . $segmentName));
        }

        $results[] = array('VisitsSummary.get', array('idSite' => 'all',
                                                      'date' => 'today',
                                                      'periods' => ['day', 'week', 'month', 'year'],
                                                      'segment' => self::NEW_SEGMENT,
                                                      'testSuffix' => '_' . self::NEW_SEGMENT_NAME));

        $results[] = array('VisitsSummary.get', array('idSite' => 'all',
            'date' => 'today',
            'periods' => ['day', 'week', 'month', 'year'],
            'segment' => self::ENCODED_SEGMENT,
            'testSuffix' => '_' . self::ENCODED_SEGMENT_NAME));

        // ExamplePlugin metric
        $results[] = ['ExamplePlugin.getExampleArchivedMetric', [
            'idSite' => 'all',
            'date' => '2007-04-05',
            'periods' => ['day', 'week'],
        ]];
        $results[] = ['Actions.get', [
            'idSite' => 'all',
            'date' => '2007-04-05',
            'periods' => ['day', 'week'],
            'testSuffix' => '_examplePluginNoMetricsBecauseNoOtherPluginsArchived',
        ]];

        // API Call Without segments
        $results[] = array('VisitsSummary.get', array('idSite'  => 'all',
                                                      'date'    => '2012-08-09',
                                                      'periods' => array('day', 'month', 'year',  'week')));

        $results[] = array($apiRequiringSegments, array('idSite'  => 'all',
            'date'    => '2012-08-09',
            'periods' => array('month')));

        $results[] = array('VisitsSummary.get', array('idSite'     => 'all',
                                                      'date'       => '2012-08-09',
                                                      'periods'    => array('day', 'week', 'month', 'year'),
                                                      'segment'    => 'browserCode==EP',
                                                      'testSuffix' => '_nonPreArchivedSegment'));

        $segments = array(ManySitesImportedLogs::SEGMENT_PRE_ARCHIVED,
                          ManySitesImportedLogs::SEGMENT_PRE_ARCHIVED_CONTAINS_ENCODED
        );
        foreach ($segments as $index => $segment) {
            // Test with a pre-processed segment
            $results[] = array(array('VisitsSummary.get', 'Live.getLastVisitsDetails', 'VisitFrequency.get'),
                               array('idSite'     => '1',
                                     'date'       => '2012-08-09',
                                     'periods'    => array('day', 'year'),
                                     'segment'    => $segment,
                                     'testSuffix' => '_preArchivedSegment' . $index,
                                     'otherRequestParameters' => array(
                                        'hideColumns' => 'latitude,longitude'
                                     ),
                                     'xmlFieldsToRemove' => array(
                                         'fingerprint'
                                     )
                               )
            );
        }

        return $results;
    }

    public function testArchivePhpCron()
    {
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'enable_browser_archiving_triggering', 0);
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'browser_archiving_disabled_enforce', 1);
        self::$fixture->getTestEnvironment()->save();

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        Config::getInstance()->General['browser_archiving_disabled_enforce'] = 1;

        Rules::setBrowserTriggerArchiving(false);

        // invalidate exampleplugin only archives in past
        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        $invalidator->markArchivesAsInvalidated(
            [1],
            ['2007-04-05'],
            'day',
            new Segment('', [1]),
            false,
            false,
            'ExamplePlugin'
        );

        // track a visit in 2007-04-05 so it will archive (don't want to force archiving because then this test will take another 15 mins)
        $tracker = Fixture::getTracker(1, '2007-04-05');
        $tracker->setUrl('http://example.com/test/url');
        Fixture::checkResponse($tracker->doTrackPageView('abcdefg'));

        $invalidationEntries = $this->getInvalidatedArchiveTableEntries();
        $this->assertGreaterThan(0, count($invalidationEntries));

        // empty the list so nothing is invalidated during core:archive (so we only archive ExamplePlugin and not all plugins)
        $invalidator->forgetRememberedArchivedReportsToInvalidate(1, Date::factory('2007-04-05'));

        $this->runArchivePhpCron();

        // add new segment w/ edited created/edit time so it will not trigger segment re-archiving, then track a visit
        // so the segments will be archived w/ other invalidation. this also runs core:archive forcing CURL requests.
        try {
            self::forceCurlCliMulti();
            self::addNewSegmentToPast();
            self::trackVisitsForToday();
            self::trackVisitInPast(); // track in the past so we end up invalidating and rearchiving a past month
            $output = $this->runArchivePhpCron();
        } finally {
            self::undoForceCurlCliMulti();
        }

        foreach ($this->getApiForTesting() as $testInfo) {
            [$api, $params] = $testInfo;

            if (!isset($params['testSuffix'])) {
                $params['testSuffix'] = '';
            }
            $params['testSuffix'] .= '_noOptions';
            $params['disableArchiving'] = true;

            $success = $this->runApiTests($api, $params);

            if (!$success) {
                var_dump($output);
            }
        }
    }

    /**
     * @depends testArchivePhpCron
     */
    public function testArchivePhpCronWithSingleReportRearchive()
    {
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'enable_browser_archiving_triggering', 0);
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'browser_archiving_disabled_enforce', 1);
        self::$fixture->getTestEnvironment()->save();

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        Config::getInstance()->General['browser_archiving_disabled_enforce'] = 1;

        Rules::setBrowserTriggerArchiving(false);

        // request a different date than other tests so we can test that only the single requested metric is archived
        $dateToRequest = '2007-04-04';

        // track a visit so archiving will go through
        $tracker = Fixture::getTracker(1, '2007-04-04');
        $tracker->setUrl('http://example.com/test/url2');
        Fixture::checkResponse($tracker->doTrackPageView('jkl'));

        $invalidator = StaticContainer::get(ArchiveInvalidator::class);
        $invalidator->forgetRememberedArchivedReportsToInvalidate(1, Date::factory('2007-04-04'));

        $table = ArchiveTableCreator::getNumericTable(Date::factory($dateToRequest));

        // three whole plugin archives from previous tests
        $countOfDoneExamplePluginArchives = Db::fetchOne("SELECT COUNT(*) FROM `$table` WHERE name = 'done.ExamplePlugin'");
        $this->assertEquals(3, $countOfDoneExamplePluginArchives);

        // invalidate a report so we get a partial archive (using the metric that gets incremented each time it is archived)
        // (do it after the last run so we don't end up just re-using the ExamplePlugin archive)
        $invalidator->markArchivesAsInvalidated([1], [$dateToRequest], 'day', new Segment('', [1]), false, false, 'ExamplePlugin.ExamplePlugin_example_metric');

        $output = $this->runArchivePhpCron(['-vvv' => null]);

        $this->runApiTests('ExamplePlugin.getExampleArchivedMetric', [
            'idSite' => 'all',
            'date' => $dateToRequest,
            'periods' => ['day', 'week'],
            'testSuffix' => '_singleMetric',
        ]);

        // three new partial plugin archives should appear
        $countOfDoneExamplePluginArchives = Db::fetchOne("SELECT COUNT(*) FROM `$table` WHERE name = 'done.ExamplePlugin'");
        $this->assertEquals(6, $countOfDoneExamplePluginArchives);

        // test that latest archives for ExamplePlugin are partial
        $archiveValues = Db::fetchAll("SELECT value FROM $table WHERE `name` = 'done.ExamplePlugin' ORDER BY ts_archived DESC LIMIT 8");
        $archiveValues = array_column($archiveValues, 'value');
        $archiveValues = array_unique($archiveValues);
        $this->assertEquals([5, 1], array_values($archiveValues));
    }

    /**
     * @depends testArchivePhpCronWithSingleReportRearchive
     */
    public function testArchivePhpCronArchivesFullRanges()
    {
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'enable_browser_archiving_triggering', 0);
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'archiving_range_force_on_browser_request', 0);
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'archiving_custom_ranges', ['2012-08-09,2012-08-13']);
        self::$fixture->getTestEnvironment()->save();

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        Config::getInstance()->General['archiving_range_force_on_browser_request'] = 0;
        Config::getInstance()->General['archiving_custom_ranges'][] = '';

        Rules::setBrowserTriggerArchiving(false);

        $output = $this->runArchivePhpCron(['--force-periods' => 'range', '--force-idsites' => 1]);

        $expectedInvalidations = [];
        $invalidationEntries = $this->getInvalidatedArchiveTableEntries();

        $invalidationEntries = array_filter($invalidationEntries, function ($entry) {
            return $entry['period'] == 5;
        });

        $this->assertEquals($expectedInvalidations, $invalidationEntries);

        $this->runApiTests(
            array(
            'VisitsSummary.get', 'Actions.get', 'DevicesDetection.getType'),
            array('idSite'     => '1',
                'date'       => '2012-08-09,2012-08-13',
                'periods'    => array('range'),
                'testSuffix' => '_range_archive')
        );
    }

    /**
     * @depends testArchivePhpCronArchivesFullRanges
     */
    public function testRangeBrowserArchivingWorksWhenInvalidatedByVisit()
    {
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'enable_browser_archiving_triggering', 0);
        // remove the value, since the default is to always force archiving on browser request
        self::$fixture->getTestEnvironment()->removeOverriddenConfig('General', 'archiving_range_force_on_browser_request');
        self::$fixture->getTestEnvironment()->save();

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        unset(Config::getInstance()->General['archiving_range_force_on_browser_request']);

        $table = ArchiveTableCreator::getNumericTable(Date::factory('2012-08-09'));
        $blobTable = ArchiveTableCreator::getBlobTable(Date::factory('2012-08-09'));

        // remove existing range archives
        $idArchives = Db::fetchAll("SELECT DISTINCT idarchive FROM $table WHERE period = 5");
        $idArchives = array_column($idArchives, 'idarchive');

        $model = new Model();
        $model->deleteArchiveIds($table, $blobTable, $idArchives);

        // process archives once
        $this->runApiTests(
            array(
            'VisitsSummary.get', 'Actions.get', 'DevicesDetection.getType'),
            array('idSite'     => '1',
                'date'       => '2012-08-09,2012-08-13',
                'periods'    => array('range'),
                'testSuffix' => '_range_archive_before_invalidate')
        );

        $this->trackVisitInPast($ip = '124.56.23.23');

        $cronArchive = new CronArchive();
        $cronArchive->init();
        $cronArchive->invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain(1);

        // process day archive so range archive will process below (if we don't, then we'll skip processing the range archive,
        // since it wouldn't trigger the child day archive. then later the day archive would be updated, and not the range, resulting
        // in inaccurate data)
        Rules::setBrowserTriggerArchiving(true);
        \Piwik\Plugins\VisitsSummary\API::getInstance()->get(1, 'day', '2012-08-09');
        Rules::setBrowserTriggerArchiving(false);

        $rangeArchivesInvalid = Db::fetchAll("SELECT idarchive, name, date1, date2 FROM " . $table . " WHERE period = 5 and name LIKE 'done.%' and value = " . ArchiveWriter::DONE_INVALIDATED);
        $this->assertNotEmpty($rangeArchivesInvalid);

        // cron archive not run, but ranges should still be rearchived
        $this->runApiTests(
            array(
            'VisitsSummary.get', 'Actions.get', 'DevicesDetection.getType'),
            array('idSite'     => '1',
                'date'       => '2012-08-09,2012-08-13',
                'periods'    => array('range'),
                'testSuffix' => '_range_archive_rearchived')
        );

        $archivePurger = StaticContainer::get(ArchivePurger::class);
        $archivePurger->purgeInvalidatedArchivesFrom(Date::factory('2020-08-09'));

        $rangeArchivesStillInvalid = Db::fetchAll("SELECT idarchive, name, date1, date2 FROM " . $table . " WHERE period = 5 and name = 'done.%' and value = " . ArchiveWriter::DONE_INVALIDATED);
        $this->assertEmpty($rangeArchivesStillInvalid);
    }

    public function testArchivePhpScriptDoesNotFailWhenCommandHelpRequested()
    {
        $output = $this->runArchivePhpCron(array('--help' => null), PIWIK_INCLUDE_PATH . '/misc/cron/archive.php');

        $this->assertRegExp('/Usage:\s*core:archive/', $output);
        self::assertStringNotContainsString("Starting Piwik reports archiving...", $output);
    }

    public function testArchivePhpCronWithTimeLimit()
    {
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'enable_browser_archiving_triggering', 0);
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'browser_archiving_disabled_enforce', 1);
        self::$fixture->getTestEnvironment()->save();

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        Config::getInstance()->General['browser_archiving_disabled_enforce'] = 1;

        Rules::setBrowserTriggerArchiving(false);

        // track a visit so archiving will go through
        $tracker = Fixture::getTracker(1, '2005-09-04');
        $tracker->setUrl('http://example.com/test/url2');
        Fixture::checkResponse($tracker->doTrackPageView('jkl'));

        $output = $this->runArchivePhpCron(['-vvv --stop-processing-after=1' => null]);

        self::assertStringContainsString('Maximum time limit per execution has been reached.', $output);

        // Ensure work has stopped and some invalidations are left over
        self::assertNotEmpty($this->getInvalidatedArchiveTableEntries());
    }

    private function runArchivePhpCron($options = array(), $archivePhpScript = false)
    {
        $archivePhpScript = $archivePhpScript ?: PIWIK_INCLUDE_PATH . '/tests/PHPUnit/proxy/archive.php';
        $urlToProxy = Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php';

        // create the command
        $cmd = "php \"$archivePhpScript\" --url=\"$urlToProxy\"";
        foreach ($options as $name => $value) {
            $cmd .= " $name";
            if ($value !== null) {
                $cmd .= "=" . escapeshellarg($value);
            }
        }
        $cmd .= " 2>&1";

        // run the command
        exec($cmd, $output, $result);
        $output = implode("\n", $output);

        if ($result !== 0 || strpos($output, "ERROR") || strpos($output, "Error")) {
            $this->fail("archive cron failed (result = $result): " . $output . "\n\ncommand used: $cmd");
        }

        return $output;
    }

    public static function provideContainerConfigBeforeClass()
    {
        return array(
            LoggerInterface::class => \Piwik\DI::get(Logger::class),

            // for some reason, w/o real translations archiving segments in CronArchive fails. the data returned by CliMulti
            // is a translation token, and nothing else.
            'Piwik\Translation\Translator' => function (Container $c) {
                return new \Piwik\Translation\Translator($c->get('Piwik\Translation\Loader\LoaderInterface'));
            },

            'Tests.log.allowAllHandlers' => true,

            CronArchive\SegmentArchiving::class => \Piwik\DI::autowire()
                // Oldest reports are for 2012, so ensure segments are processed for that year
                ->constructorParameter('beginningOfTimeLastNInYears', date('Y') - 2012)
        );
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

    private function getInvalidatedArchiveTableEntries()
    {
        return Db::fetchAll("SELECT idinvalidation, idarchive, idsite, date1, date2, period, name, status FROM " . Common::prefixTable('archive_invalidations'));
    }

    private static function undoForceCurlCliMulti()
    {
        $testVars = new TestingEnvironmentVariables();
        $testVars->forceCliMultiViaCurl = 0;
        $testVars->save();
    }

    private static function forceCurlCliMulti()
    {
        $testVars = new TestingEnvironmentVariables();
        $testVars->forceCliMultiViaCurl = 1;
        $testVars->save();
    }
}

ArchiveCronTest::$fixture = new ManySitesImportedLogs();
ArchiveCronTest::$fixture->addSegments = true;
