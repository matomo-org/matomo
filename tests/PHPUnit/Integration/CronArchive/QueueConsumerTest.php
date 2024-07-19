<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\CronArchive;

use Piwik\ArchiveProcessor\Rules;
use Piwik\CliMulti\RequestParser;
use Piwik\Common;
use Piwik\Config;
use Piwik\Period\Day;
use Piwik\Period\Factory;
use Piwik\Period\Month;
use Piwik\Period\Week;
use Piwik\Period\Year;
use Piwik\Plugins\CustomDimensions;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\CronArchive\FixedSiteIds;
use Piwik\CronArchive\QueueConsumer;
use Piwik\CronArchive\SegmentArchiving;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Log\LoggerInterface;
use Piwik\Log\NullLogger;

class QueueConsumerTest extends IntegrationTestCase
{
    public function testConsumerIgnoresPeriodsThatHaveBeenDisabledInApi()
    {
        Fixture::createWebsite('2015-02-03');
        Fixture::createWebsite('2015-02-03');
        Fixture::createWebsite('2015-02-03');
        Fixture::createWebsite('2015-02-03');

        // force archiving so we don't skip those without visits
        Piwik::addAction('Archiving.getIdSitesToArchiveWhenNoVisits', function (&$idSites) {
            $idSites[] = 1;
            $idSites[] = 2;
            $idSites[] = 3;
            $idSites[] = 4;
        });

        $cronArchive = $this->getMockCronArchive();
        $cronArchive->init();

        $archiveFilter = $this->makeTestArchiveFilter();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1, 2, 3, 4]),
            3,
            24,
            new Model(),
            new SegmentArchiving(),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );

        $invalidations = [
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 2, 'name' => 'done', 'idsite' => 2, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => Week::PERIOD_ID, 'report' => null],
            ['idarchive' => 3, 'name' => 'done', 'idsite' => 3, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => Month::PERIOD_ID, 'report' => null],
            ['idarchive' => 4, 'name' => 'done', 'idsite' => 4, 'date1' => '2018-01-01', 'date2' => '2018-12-31', 'period' => Year::PERIOD_ID, 'report' => null],
        ];

        $this->insertInvalidations($invalidations);

        Config::getInstance()->General['enabled_periods_API'] = 'day,week,range';

        $iteratedInvalidations = [];
        while (true) {
            $next = $queueConsumer->getNextArchivesToProcess();
            if ($next === null) {
                break;
            }

            foreach ($next as &$item) {
                $this->simulateJobStart($item['idinvalidation']);

                unset($item['periodObj']);
                unset($item['idinvalidation']);
                unset($item['ts_invalidated']);
            }

            $iteratedInvalidations[] = $next;
        }

        $expectedInvalidationsFound = [
            [
                [
                    'idarchive' => '1',
                    'name' => 'done',
                    'idsite' => '1',
                    'date1' => '2018-03-04',
                    'date2' => '2018-03-04',
                    'period' => '1',
                    'ts_started' => null,
                    'status' => '0',
                    'report' => null,
                    'plugin' => null,
                    'segment' => '',
                ],
            ],
            [],
            [
                [
                    'idarchive' => '2',
                    'name' => 'done',
                    'idsite' => '2',
                    'date1' => '2018-03-04',
                    'date2' => '2018-03-11',
                    'period' => '2',
                    'ts_started' => null,
                    'status' => '0',
                    'report' => null,
                    'plugin' => null,
                    'segment' => '',
                ],
            ],
            [],
            [],
            [],
        ];

        $this->assertEquals($expectedInvalidationsFound, $iteratedInvalidations, "Invalidations inserted:\n" . var_export($invalidations, true));
    }

    public function testInvalidateConsumeOrder()
    {
        Fixture::createWebsite('2015-02-03');
        Fixture::createWebsite('2020-04-06');
        Fixture::createWebsite('2010-04-06');

        CustomDimensions\API::getInstance()->configureNewCustomDimension(1, 'custom 1', 'visit', true);

        Rules::setBrowserTriggerArchiving(false);
        API::getInstance()->add('testegment', 'browserCode==IE;dimension1==val', 1, true);
        API::getInstance()->add('testegment2', 'browserCode==ff', false);
        Rules::setBrowserTriggerArchiving(true);

        // force archiving so we don't skip those without visits
        Piwik::addAction('Archiving.getIdSitesToArchiveWhenNoVisits', function (&$idSites) {
            $idSites[] = 1;
            $idSites[] = 2;
        });

        $cronArchive = $this->getMockCronArchive();
        $cronArchive->init();

        $archiveFilter = $this->makeTestArchiveFilter();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1,2,3]),
            3,
            24,
            new Model(),
            new SegmentArchiving(),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );

        $segmentHash = (new Segment('browserCode==IE;dimension1==val', [1]))->getHash();
        $segmentHash2 = (new Segment('browserCode==ff', [1]))->getHash();

        $invalidations = [
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-07', 'date2' => '2018-03-07', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-08', 'date2' => '2018-03-08', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => Month::PERIOD_ID, 'report' => null], // intersecting period
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => Week::PERIOD_ID, 'report' => null], // intersecting period

            ['idarchive' => 1, 'name' => 'done.Actions', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => Day::PERIOD_ID, 'report' => 'testReport'], // intersecting period
            ['idarchive' => 1, 'name' => 'done.Actions', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => Month::PERIOD_ID, 'report' => 'testReport'], // intersecting period
            ['idarchive' => 1, 'name' => 'done.Actions', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => Week::PERIOD_ID, 'report' => 'testReport'], // intersecting period

            // some or all subperiods before site was created
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 2, 'date1' => '2020-04-04', 'date2' => '2020-04-04', 'period' => Day::PERIOD_ID, 'report' => 'testReport'],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 2, 'date1' => '2020-03-30', 'date2' => '2020-04-05', 'period' => Week::PERIOD_ID, 'report' => 'testReport'], // intersecting period

            // segments are skipped due to insersecting period with all visits
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2018-03-07', 'date2' => '2018-03-07', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2018-03-08', 'date2' => '2018-03-08', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => Month::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => Week::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 2, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => Week::PERIOD_ID, 'report' => null],

            // removed as segment not configured to auto archive
            ['idarchive' => 1, 'name' => 'done' . $segmentHash2, 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => Week::PERIOD_ID, 'report' => null],

            // removed as invalid plugin
            ['idarchive' => 1, 'name' => 'done.MyPlugin', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => Week::PERIOD_ID, 'report' => 'testReport'],

            // removed as duplicates
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => Day::PERIOD_ID, 'report' => null],
            // dupliactes not removed as skipped due to intersecting period
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => Month::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => Month::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => Month::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => Month::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => Month::PERIOD_ID, 'report' => null],

            // high ts_invalidated, should not be selected
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-01-01', 'date2' => '2018-01-31', 'period' => Month::PERIOD_ID, 'report' => null, 'ts_invalidated' => Date::factory(time() + 300)->getDatetime()],
        ];

        shuffle($invalidations);

        $this->insertInvalidations($invalidations);

        $iteratedInvalidations = [];
        while (true) {
            $next = $queueConsumer->getNextArchivesToProcess();
            if ($next === null) {
                break;
            }

            foreach ($next as &$item) {
                $this->simulateJobStart($item['idinvalidation']);

                unset($item['periodObj']);
                unset($item['idinvalidation']);
                unset($item['ts_invalidated']);
            }

            $iteratedInvalidations[] = $next;
        }

        $expectedInvalidationsFound = [
            [
                [
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-08',
                    'date2' => '2018-03-08',
                    'period' => '1',
                    'name' => 'done',
                    'report' => null,
                    'plugin' => null,
                    'segment' => '',
                    'ts_started' => null,
                    'status' => '0',
                ],
                [
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-07',
                    'date2' => '2018-03-07',
                    'period' => '1',
                    'name' => 'done',
                    'report' => null,
                    'plugin' => null,
                    'segment' => '',
                    'ts_started' => null,
                    'status' => '0',
                ],
                [
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-06',
                    'date2' => '2018-03-06',
                    'period' => '1',
                    'name' => 'done',
                    'report' => null,
                    'plugin' => null,
                    'segment' => '',
                    'ts_started' => null,
                    'status' => '0',
                ],
            ],
            [
                [
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-04',
                    'date2' => '2018-03-04',
                    'period' => '1',
                    'name' => 'done',
                    'report' => null,
                    'plugin' => null,
                    'segment' => '',
                    'ts_started' => null,
                    'status' => '0',
                ],
            ],
            [],
            [], // end of idsite=1
            [
                [
                    'idarchive' => '1',
                    'idsite' => '2',
                    'date1' => '2020-03-30',
                    'date2' => '2020-04-05',
                    'period' => '2',
                    'name' => 'done',
                    'report' => 'testReport',
                    'plugin' => null,
                    'segment' => '',
                    'ts_started' => null,
                    'status' => '0',
                ],
            ],
            [ // end of idsite=2
            ],
            [ // end of idsite=3
            ],
        ];

        $this->assertEquals($expectedInvalidationsFound, $iteratedInvalidations);

        // automated check for no duplicates
        $invalidationDescs = [];
        foreach ($iteratedInvalidations as $group) {
            foreach ($group as $invalidation) {
                unset($invalidation['idarchive']);
                $invalidationDescs[] = implode('.', $invalidation);
            }
        }
        $uniqueInvalidationDescs = array_unique($invalidationDescs);

        $this->assertEquals($uniqueInvalidationDescs, $invalidationDescs, "Found duplicate archives being processed.");

        // check that segment hash 2 is no longer in the invalidations table
        $count = Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE name = ?', [
            'done' . $segmentHash2,
        ]);
        $this->assertEquals(0, $count);

        // simulate a second run after the first round being finished
        Db::query('DELETE FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE status = 1');

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1,2,3]),
            3,
            24,
            new Model(),
            new SegmentArchiving(),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );

        $iteratedInvalidations = [];
        while (true) {
            $next = $queueConsumer->getNextArchivesToProcess();
            if ($next === null) {
                break;
            }

            foreach ($next as &$item) {
                $this->simulateJobStart($item['idinvalidation']);

                unset($item['periodObj']);
                unset($item['idinvalidation']);
                unset($item['ts_invalidated']);
            }

            $iteratedInvalidations[] = $next;
        }

        $expectedInvalidationsFound = [
            [
                [
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-08',
                    'date2' => '2018-03-08',
                    'period' => '1',
                    'name' => 'donec3afbf588c35606b9cd9ecd1ac781428',
                    'report' => null,
                    'plugin' => null,
                    'segment' => 'browserCode==IE;dimension1==val',
                    'ts_started' => null,
                    'status' => '0',
                ],
                [
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-07',
                    'date2' => '2018-03-07',
                    'period' => '1',
                    'name' => 'donec3afbf588c35606b9cd9ecd1ac781428',
                    'report' => null,
                    'plugin' => null,
                    'segment' => 'browserCode==IE;dimension1==val',
                    'ts_started' => null,
                    'status' => '0',
                ],
                [
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-06',
                    'date2' => '2018-03-06',
                    'period' => '1',
                    'name' => 'done.Actions',
                    'report' => 'testReport',
                    'plugin' => 'Actions',
                    'segment' => '',
                    'ts_started' => null,
                    'status' => '0',
                ],
            ],
            [
                [
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-04',
                    'date2' => '2018-03-04',
                    'period' => '1',
                    'name' => 'donec3afbf588c35606b9cd9ecd1ac781428',
                    'report' => null,
                    'plugin' => null,
                    'segment' => 'browserCode==IE;dimension1==val',
                    'ts_started' => null,
                    'status' => '0',
                ],
            ],
            [],
            [], // end of idsite=1
            [], // end of idsite=2
            [], // end of idsite=3
        ];

        $this->assertEquals($expectedInvalidationsFound, $iteratedInvalidations);

        // automated check for no duplicates
        $invalidationDescs = [];
        foreach ($iteratedInvalidations as $group) {
            foreach ($group as $invalidation) {
                unset($invalidation['idarchive']);
                $invalidationDescs[] = implode('.', $invalidation);
            }
        }
        $uniqueInvalidationDescs = array_unique($invalidationDescs);

        $this->assertEquals($uniqueInvalidationDescs, $invalidationDescs, "Found duplicate archives being processed.");
    }

    public function testPluginInvalidationDeletedIfUsableArchiveExists()
    {
        Fixture::createWebsite('2015-02-03');

        // force archiving so we don't skip those without visits
        Piwik::addAction('Archiving.getIdSitesToArchiveWhenNoVisits', function (&$idSites) {
            $idSites[] = 1;
        });

        $cronArchive = $this->getMockCronArchive();
        $cronArchive->init();

        $archiveFilter = $this->makeTestArchiveFilter();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1]),
            3,
            24,
            new Model(),
            new SegmentArchiving(),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );

        Date::$now = strtotime('2018-03-04 01:00:00');

        $invalidations = [
            ['idarchive' => 1, 'name' => "done.Actions", 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => Month::PERIOD_ID, 'report' => null],
        ];

        shuffle($invalidations);

        $this->insertInvalidations($invalidations);

        $usableArchive = ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => Day::PERIOD_ID, 'ts_archived' => Date::now()->getDatetime(), 'value' => 3.0];
        $this->insertArchive($usableArchive);

        $iteratedInvalidations = [];
        while (true) {
            $next = $queueConsumer->getNextArchivesToProcess();
            if ($next === null) {
                break;
            }

            foreach ($next as &$item) {
                $this->simulateJobStart($item['idinvalidation']);

                unset($item['periodObj']);
                unset($item['idinvalidation']);
                unset($item['ts_invalidated']);
            }

            $iteratedInvalidations[] = $next;
        }

        $expectedInvalidationsFound = [
            array(
                ['idarchive' => '1', 'idsite' => '1', 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => '3', 'name' => 'done', 'report' => null, 'plugin' => null, 'segment' => '', 'ts_started' => null, 'status' => '0']
            ),
            array()
        ];

        $this->assertEquals($expectedInvalidationsFound, $iteratedInvalidations, "Invalidations inserted:\n" . var_export($invalidations, true));

        // check that our plugin invalidation is no longer in the invalidations table
        $count = Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE name = ?', [
            "done.Actions",
        ]);
        $this->assertEquals(0, $count);
    }

    public function testSkipSegmentsToday()
    {
        Date::$now = strtotime('2018-03-04 01:00:00');

        Fixture::createWebsite('2015-02-03');

        Rules::setBrowserTriggerArchiving(false);
        API::getInstance()->add('testegment', 'browserCode==IE', false, true);
        API::getInstance()->add('testegment', 'browserCode==FF', false, true);
        Rules::setBrowserTriggerArchiving(true);

        // force archiving so we don't skip those without visits
        Piwik::addAction('Archiving.getIdSitesToArchiveWhenNoVisits', function (&$idSites) {
            $idSites[] = 1;
        });

        $cronArchive = $this->getMockCronArchive();
        $cronArchive->init();

        $archiveFilter = $this->makeTestArchiveFilter(null, null, null, false, true);

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1]),
            3,
            24,
            new Model(),
            new SegmentArchiving(),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );

        $segmentHash1 = (new Segment('browserCode==IE', [1]))->getHash();
        $segmentHash2 = (new Segment('browserCode==FF', [1]))->getHash();

        $invalidations = [
            ['idarchive' => 1, 'name' => 'done' . $segmentHash1, 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash2, 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash2 . '.ExamplePlugin', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash1, 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => Month::PERIOD_ID, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => Day::PERIOD_ID, 'report' => null],
        ];
        shuffle($invalidations);

        $this->insertInvalidations($invalidations);

        $iteratedInvalidations = [];
        while (true) {
            $next = $queueConsumer->getNextArchivesToProcess();
            if ($next === null) {
                break;
            }

            foreach ($next as &$item) {
                Db::query("UPDATE " . Common::prefixTable('archive_invalidations') . " SET status = 1 WHERE idinvalidation = ?", [$item['idinvalidation']]);

                unset($item['periodObj']);
                unset($item['idinvalidation']);
                unset($item['ts_invalidated']);
            }

            $iteratedInvalidations[] = $next;
        }

        $expectedInvalidationsFound = [
            [
                [
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-04',
                    'date2' => '2018-03-04',
                    'period' => '1',
                    'name' => 'done',
                    'report' => null,
                    'plugin' => null,
                    'segment' => '',
                    'ts_started' => null,
                    'status' => '0',
                ],
                [
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-03',
                    'date2' => '2018-03-03',
                    'period' => '1',
                    'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                    'report' => null,
                    'plugin' => null,
                    'segment' => 'browserCode==IE',
                    'ts_started' => null,
                    'status' => '0',
                ],
            ],
            [],
            []
        ];

        try {
            $this->assertEquals($expectedInvalidationsFound, $iteratedInvalidations);
        } catch (\Exception $ex) {
            print "\nInvalidations inserted:\n" . var_export($invalidations, true) . "\n";
            throw $ex;
        }

        // automated check for no duplicates
        $invalidationDescs = [];
        foreach ($iteratedInvalidations as $group) {
            foreach ($group as $invalidation) {
                unset($invalidation['idarchive']);
                $invalidationDescs[] = implode('.', $invalidation);
            }
        }
        $uniqueInvalidationDescs = array_unique($invalidationDescs);

        $this->assertEquals($uniqueInvalidationDescs, $invalidationDescs, "Found duplicate archives being processed.");
    }

    public function testMaxWebsitesToProcess()
    {
        Fixture::createWebsite('2021-11-16');
        Fixture::createWebsite('2021-11-16');
        Fixture::createWebsite('2021-11-16');

        // force archiving so we don't skip those without visits
        Piwik::addAction('Archiving.getIdSitesToArchiveWhenNoVisits', function (&$idSites) {
            $idSites[] = 1;
            $idSites[] = 2;
            $idSites[] = 3;
        });

        $cronArchive = $this->getMockCronArchive();
        $cronArchive->init();

        $archiveFilter = $this->makeTestArchiveFilter();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1, 2, 3]),
            3,
            24,
            new Model(),
            new SegmentArchiving(),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );
        $this->assertNull($queueConsumer->setMaxSitesToProcess());
        $this->assertEquals(1, $queueConsumer->setMaxSitesToProcess(1));

        $invalidations = [
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2021-11-16', 'date2' => '2021-11-16', 'period' => Day::PERIOD_ID, 'report' => null],
            ['idarchive' => 2, 'name' => 'done', 'idsite' => 2, 'date1' => '2021-11-16', 'date2' => '2021-11-16', 'period' => Week::PERIOD_ID, 'report' => null],
            ['idarchive' => 3, 'name' => 'done', 'idsite' => 3, 'date1' => '2021-11-16', 'date2' => '2021-11-16', 'period' => Month::PERIOD_ID, 'report' => null],
        ];

        $this->insertInvalidations($invalidations);

        Config::getInstance()->General['enabled_periods_API'] = 'day,week,range';

        $iteratedInvalidations = [];
        while (true) {
            $next = $queueConsumer->getNextArchivesToProcess();
            if ($next === null) {
                break;
            }
            if (empty($next)) {
                continue;
            }

            foreach ($next as &$item) {
                $this->simulateJobStart($item['idinvalidation']);

                unset($item['periodObj']);
                unset($item['idinvalidation']);
                unset($item['ts_invalidated']);
            }

            $iteratedInvalidations[] = $next;
        }

        $expectedInvalidationsFound = [
            [
                [
                    'idarchive' => '1',
                    'name' => 'done',
                    'idsite' => '1',
                    'date1' => '2021-11-16',
                    'date2' => '2021-11-16',
                    'period' => '1',
                    'ts_started' => null,
                    'status' => '0',
                    'report' => null,
                    'plugin' => null,
                    'segment' => '',
                ],
            ]
        ];

        $this->assertEquals($expectedInvalidationsFound, $iteratedInvalidations, "Invalidations inserted:\n" . var_export($invalidations, true));
    }

    private function makeTestArchiveFilter(
        $restrictToDateRange = null,
        $restrictToPeriods = null,
        $segmentsToForce = null,
        $disableSegmentsArchiving = false,
        $skipSegmentsToday = false
    ) {
        $archiveFilter = new CronArchive\ArchiveFilter();
        if ($restrictToDateRange) {
            $archiveFilter->setRestrictToDateRange();
        }
        $archiveFilter->setDisableSegmentsArchiving($disableSegmentsArchiving);
        if ($restrictToPeriods) {
            $archiveFilter->setRestrictToPeriods($restrictToPeriods);
        }
        if ($segmentsToForce) {
            $archiveFilter->setSegmentsToForceFromSegmentIds($segmentsToForce);
        }
        if ($skipSegmentsToday) {
            $archiveFilter->setSkipSegmentsForToday(true);
        }
        return $archiveFilter;
    }

    private function insertInvalidations(array $invalidations)
    {
        $now = Date::now()->getDatetime();

        $table = Common::prefixTable('archive_invalidations');
        foreach ($invalidations as $inv) {
            $bind = [
                $inv['idarchive'] ?? null,
                $inv['name'],
                $inv['idsite'],
                $inv['date1'],
                $inv['date2'],
                $inv['period'],
                isset($inv['ts_invalidated']) ? $inv['ts_invalidated'] : $now,
                $inv['report'] ?? null,
                $inv['status'] ?? 0,
                $inv['ts_started'] ?? null,
            ];
            Db::query("INSERT INTO `$table` (idarchive, name, idsite, date1, date2, period, ts_invalidated, report, status, ts_started)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $bind);
        }
    }

    private function insertArchive(array $archive)
    {
        $table = ArchiveTableCreator::getNumericTable(Date::now());

        $bind = [
            $archive['idarchive'],
            $archive['name'],
            $archive['idsite'],
            $archive['date1'],
            $archive['date2'],
            $archive['period'],
            $archive['ts_archived'],
            $archive['value']
        ];

        Db::query("INSERT INTO `$table` (idarchive, name, idsite, date1, date2, period, ts_archived, value)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)", $bind);
    }

    public function testCanSkipArchiveBecauseNoPointReturnsTrueIfDateRangeHasNoVisits()
    {
        Fixture::createWebsite('2010-04-06');

        Date::$now = strtotime('2020-04-05');

        $cronArchive = new CronArchive();
        $cronArchive->init();

        $archiveFilter = $this->makeTestArchiveFilter();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1]),
            3,
            24,
            new Model(),
            new SegmentArchiving(),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );

        $invalidation = [
            'idsite' => 1,
            'period' => Day::PERIOD_ID,
            'date1' => '2020-04-05',
            'date2' => '2020-04-05',
            'name' => 'done',
            'segment' => '',
        ];

        $result = $queueConsumer->canSkipArchiveBecauseNoPoint($invalidation);
        $this->assertTrue($result);
    }

    public function testCanSkipArchiveBecauseNoPointReturnsFalseIfDateRangeHasVisitsAndPeriodDoesNotIncludeToday()
    {
        $this->setUpSiteAndTrackVisit('2020-03-05 10:34:00');

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1]),
            3,
            24,
            new Model(),
            new SegmentArchiving(),
            new CronArchive(),
            new RequestParser(true),
            $this->makeTestArchiveFilter()
        );

        $invalidation = [
            'idsite' => 1,
            'period' => Day::PERIOD_ID,
            'date1' => '2020-03-05',
            'date2' => '2020-03-05',
            'name' => 'done',
            'segment' => '',
        ];

        $result = $queueConsumer->canSkipArchiveBecauseNoPoint($invalidation);
        $this->assertFalse($result);
    }

    public function testUsableArchiveExistsReturnsTrueIfDateRangeHasVisitsAndPeriodIncludesTodayAndExistingArchiveIsRecent()
    {
        $this->setUpSiteAndTrackVisit();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1]),
            3,
            24,
            new Model(),
            new SegmentArchiving(),
            new CronArchive(),
            new RequestParser(true),
            $this->makeTestArchiveFilter()
        );

        $invalidation = [
            'idsite' => 1,
            'period' => Week::PERIOD_ID,
            'date1' => '2020-03-30',
            'date2' => '2020-04-05',
            'name' => 'done',
            'segment' => '',
        ];

        $tsArchived = Date::factory('now')->subSeconds(100)->getDatetime();

        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2020-03-30'));
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1,2, '2020-03-30', '2020-04-05', 'done', ArchiveWriter::DONE_OK, $tsArchived
        ]);

        $result = $queueConsumer->usableArchiveExists($invalidation);
        $this->assertEquals([true, '2020-04-04 23:58:20'], $result);
    }

    public function testUsableArchiveExistsReturnsTrueIfDateRangeHasVisitsAndPeriodIncludesTodayAndExistingPluginArchiveIsRecent()
    {
        $this->setUpSiteAndTrackVisit();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1]),
            3,
            24,
            new Model(),
            new SegmentArchiving(),
            new CronArchive(),
            new RequestParser(true),
            $this->makeTestArchiveFilter()
        );

        $segmentHash = (new Segment('browserCode==IE', [1]))->getHash();

        $invalidation = [
            'idsite' => 1,
            'period' => Week::PERIOD_ID,
            'date1' => '2020-03-30',
            'date2' => '2020-04-05',
            'name' => 'done' . $segmentHash . '.ExamplePlugin',
            'segment' => 'browserCode==IE',
            'plugin' => 'ExamplePlugin'
        ];

        $tsArchived = Date::factory('now')->subSeconds(100)->getDatetime();

        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2020-03-30'));
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1,2, '2020-03-30', '2020-04-05', 'done' . $segmentHash . '.ExamplePlugin', ArchiveWriter::DONE_PARTIAL, $tsArchived
        ]);

        $result = $queueConsumer->usableArchiveExists($invalidation);
        $this->assertEquals([true, '2020-04-04 23:58:20'], $result);
    }

    public function testCanSkipArchiveBecauseNoPointReturnsFalseIfDateRangeHasVisitsAndPeriodIncludesTodayAndOnlyExistingArchiveIsRecentButPartial()
    {
        $this->setUpSiteAndTrackVisit();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1]),
            3,
            24,
            new Model(),
            new SegmentArchiving(),
            new CronArchive(),
            new RequestParser(true),
            $this->makeTestArchiveFilter()
        );

        $invalidation = [
            'idsite' => 1,
            'period' => Week::PERIOD_ID,
            'date1' => '2020-03-30',
            'date2' => '2020-04-05',
            'name' => 'done',
            'segment' => '',
        ];

        $tsArchived = Date::factory('now')->subSeconds(100)->getDatetime();

        $archiveTable = ArchiveTableCreator::getNumericTable(Date::factory('2020-03-30'));
        Db::query("INSERT INTO $archiveTable (idarchive, idsite, period, date1, date2, name, value, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
            1, 1,2, '2020-03-30', '2020-04-05', 'done', ArchiveWriter::DONE_PARTIAL, $tsArchived
        ]);

        $result = $queueConsumer->canSkipArchiveBecauseNoPoint($invalidation);
        $this->assertFalse($result);
    }

    public function testCanSkipArchiveBecauseNoPointReturnsTrueSegmentArchivingForPluginIsDisabled()
    {
        $this->setUpSiteAndTrackVisit();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1]),
            3,
            24,
            new Model(),
            new SegmentArchiving(),
            new CronArchive(),
            new RequestParser(true),
            $this->makeTestArchiveFilter()
        );

        $segmentHash = (new Segment('browserCode==IE', [1]))->getHash();

        $invalidation = [
            'idsite' => 1,
            'period' => Week::PERIOD_ID,
            'date1' => '2020-03-30',
            'date2' => '2020-04-05',
            'name' => 'done' . $segmentHash . '.ExamplePlugin',
            'segment' => 'browserCode==IE',
            'plugin' => 'ExamplePlugin'
        ];

        $this->assertFalse($queueConsumer->canSkipArchiveBecauseNoPoint($invalidation));

        Config::getInstance()->General['disable_archiving_segment_for_plugins'] = 'ExamplePlugin';

        $this->assertTrue($queueConsumer->canSkipArchiveBecauseNoPoint($invalidation));
    }

    private function setUpSiteAndTrackVisit($visitDateTime = '2020-04-05 10:34:00')
    {
        $idSite = Fixture::createWebsite('2015-02-03');

        Date::$now = strtotime('2020-04-05');

        $t = Fixture::getTracker($idSite, $visitDateTime);
        $t->setUrl('http://whatever.com');
        Fixture::checkResponse($t->doTrackPageView('test title'));
    }

    /**
     * @dataProvider getTestDataForHasIntersectingPeriod
     */
    public function testHasIntersectingPeriod($archivesToProcess, $invalidatedArchive, $expected)
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
                    ['period' => Day::PERIOD_ID, 'date1' => '2020-03-04', 'date2' => '2020-03-04'],
                    ['period' => Month::PERIOD_ID, 'date1' => '2020-04-01', 'date2' => '2020-04-30'],
                    ['period' => Day::PERIOD_ID, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==abc'],
                ],
                ['period' => Day::PERIOD_ID, 'date1' => '2020-03-05', 'date2' => '2020-03-05'],
                false,
            ],

            // intersecting periods
            [
                [
                    ['period' => Day::PERIOD_ID, 'date1' => '2020-03-04', 'date2' => '2020-03-04'],
                    ['period' => Month::PERIOD_ID, 'date1' => '2020-04-01', 'date2' => '2020-04-30'],
                    ['period' => Day::PERIOD_ID, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==abc'],
                ],
                ['period' => Week::PERIOD_ID, 'date1' => '2020-03-02', 'date2' => '2020-03-08'],
                true,
            ],

            // all same period, different segments
            [
                [
                    ['period' => Day::PERIOD_ID, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==def'],
                    ['period' => Day::PERIOD_ID, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==ghi'],
                    ['period' => Day::PERIOD_ID, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==abc'],
                ],
                ['period' => Day::PERIOD_ID, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==lmn'],
                false,
            ],

            // all same period, all visits in one
            [
                [
                    ['period' => Day::PERIOD_ID, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => ''],
                ],
                ['period' => Day::PERIOD_ID, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==lmn'],
                true,
            ],

            // all same period, different segments, all visits in next
            [
                [
                    ['period' => Day::PERIOD_ID, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==def'],
                    ['period' => Day::PERIOD_ID, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==ghi'],
                    ['period' => Day::PERIOD_ID, 'date1' => '2020-03-15', 'date2' => '2020-03-15', 'segment' => 'pageUrl==abc'],
                ],
                ['period' => Day::PERIOD_ID, 'date1' => '2020-03-15', 'date2' => '2020-03-15'],
                true,
            ],
        ];
    }

    /**
     * @dataProvider getTestDataForShouldSkipArchiveBecauseLowerPeriodOrSegmentIsInProgress
     */
    public function testShouldSkipArchiveBecauseLowerPeriodOrSegmentIsInProgress($existingInvalidations, $archiveToProcess, $expected)
    {
        Fixture::createWebsite('2020-01-01 00:00:01');
        Fixture::createWebsite('2020-01-01 00:00:01');

        Rules::setBrowserTriggerArchiving(false);
        API::getInstance()->add('testegment', 'browserCode==IE', 0, true);
        API::getInstance()->add('testegment2', 'browserCode==FF', 0, true);
        Rules::setBrowserTriggerArchiving(true);

        $this->insertInvalidations($existingInvalidations);
        $cliRequestProcessor = $this->getMockRequestParser([]);

        /** @var QueueConsumer $queueConsumer */
        $queueConsumer = $this->getQueueConsumerWithMocks($cliRequestProcessor);

        $periods = array_flip(Piwik::$idPeriods);

        $archiveToProcess['periodObj'] = Factory::build($periods[$archiveToProcess['period']], $archiveToProcess['date1']);
        $actual = $queueConsumer->shouldSkipArchiveBecauseLowerPeriodOrSegmentIsInProgress($archiveToProcess);
        $this->assertSame($expected, $actual);
    }

    public function getTestDataForShouldSkipArchiveBecauseLowerPeriodOrSegmentIsInProgress(): iterable
    {
        yield 'different period and different idSite should not be detected as intersecting' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 3, 'date1' => '2022-03-04', 'date2' => '2022-03-04', 'period' => Day::PERIOD_ID],
            'expected' => null
        ];

        yield 'same period, but different idSite should not be detected as intersecting' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 3, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID],
            'expected' => null
        ];

        yield 'same day period should be detected as intersecting' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = day, date = 2020-03-04)'
        ];

        yield 'week period should be detected as intersecting when day is processed' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'period' => Week::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = day, date = 2020-03-04)'
        ];

        yield 'month period should be detected as intersecting when day is processed' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-01', 'date2' => '2020-03-31', 'period' => Month::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = day, date = 2020-03-04)'
        ];

        yield 'year period should be detected as intersecting when day is processed' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-01-01', 'date2' => '2020-12-31', 'period' => Year::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = day, date = 2020-03-04)'
        ];

        yield 'same week period should be detected as intersecting' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'period' => Week::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'period' => Week::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = week, date = 2020-03-02)'
        ];

        yield 'day period should not be detected as intersecting when week is processed' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'period' => Week::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID],
            'expected' => null
        ];

        yield 'month period should be detected as intersecting when week is processed' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'period' => Week::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-01', 'date2' => '2020-03-31', 'period' => Month::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = week, date = 2020-03-02)'
        ];

        yield 'year period should be detected as intersecting when week is processed' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'period' => Week::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-01-01', 'date2' => '2020-12-31', 'period' => Year::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = week, date = 2020-03-02)'
        ];

        yield 'same month period should be detected as intersecting' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-01', 'date2' => '2020-03-31', 'period' => Month::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-01', 'date2' => '2020-03-31', 'period' => Month::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = month, date = 2020-03-01)'
        ];

        yield 'day period should not be detected as intersecting when month is processed' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-01', 'date2' => '2020-03-31', 'period' => Month::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID],
            'expected' => null
        ];

        yield 'week period should not be detected as intersecting when month is processed' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-01', 'date2' => '2020-03-31', 'period' => Month::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'period' => Week::PERIOD_ID],
            'expected' => null
        ];

        yield 'year period should be detected as intersecting when month is processed' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-01', 'date2' => '2020-03-31', 'period' => Month::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-01-01', 'date2' => '2020-12-31', 'period' => Year::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = month, date = 2020-03-01)'
        ];

        yield 'same year period should be detected as intersecting' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-01-01', 'date2' => '2020-12-31', 'period' => Year::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-01-01', 'date2' => '2020-12-31', 'period' => Year::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = year, date = 2020-01-01)'
        ];

        yield 'day period should not be detected as intersecting when year is processed' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-01-01', 'date2' => '2020-12-31', 'period' => Year::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID],
            'expected' => null
        ];

        yield 'week period should not be detected as intersecting when year is processed' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-01-01', 'date2' => '2020-12-31', 'period' => Year::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'period' => Week::PERIOD_ID],
            'expected' => null
        ];

        yield 'month period should be detected as intersecting when year is processed' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-01', 'date2' => '2020-03-31', 'period' => Month::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 5, 'date1' => '2020-01-01', 'date2' => '2020-12-31', 'period' => Year::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = month, date = 2020-03-01)'
        ];

        yield 'plugin and normal invalidation for same day period should be detected as intersecting' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done.Actions', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = day, date = 2020-03-04)'
        ];

        // @todo is this needed?
        yield 'different plugin invalidations for same day period should be detected as intersecting' => [
            'existingInvalidations' => [
                ['name' => 'done.VisitsSummary', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done.Actions', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID],
            'expected' => 'lower or same period in progress (period = day, date = 2020-03-04)'
        ];

        yield 'week period should be detected as intersecting when day is processed for a segment' => [
            'existingInvalidations' => [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 5, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'period' => Week::PERIOD_ID, 'segment' => 'browserCode==IE'],
            'expected' => 'lower or same period in progress (period = day, date = 2020-03-04)'
        ];

        yield 'plugin archive should be detected as intersecting when lower period is processed for a segment' => [
            'existingInvalidations' => [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 5, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc.Actions', 'idsite' => 5, 'date1' => '2020-03-02', 'date2' => '2020-03-08', 'period' => Week::PERIOD_ID, 'segment' => 'browserCode==IE'],
            'expected' => 'lower or same period in progress (period = day, date = 2020-03-04)'
        ];

        yield 'segment archiving during "all visits" archiving should be detected as intersecting with same period' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'segment' => 'browserCode==IE'],
            'expected' => 'all visits archive in progress for same site with lower or same period (period = day, date = 2020-03-04)'
        ];

        yield 'segment archiving for plugin during "all visits" archiving should be detected as intersecting with same period' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc.VisitsSummary', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'segment' => 'browserCode==IE'],
            'expected' => 'all visits archive in progress for same site with lower or same period (period = day, date = 2020-03-04)'
        ];

        yield 'segment archiving during "all visits" plugin archiving should be detected as intersecting with same period' => [
            'existingInvalidations' => [
                ['name' => 'done.VisitsSummary', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'segment' => 'browserCode==IE'],
            'expected' => 'all visits archive in progress for same site with lower or same period (period = day, date = 2020-03-04)'
        ];

        yield 'segment archiving for plugin during "all visits" plugin archiving should be detected as intersecting with same period' => [
            'existingInvalidations' => [
                ['name' => 'done.VisitsSummary', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc.Actions', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'segment' => 'browserCode==IE'],
            'expected' => 'all visits archive in progress for same site with lower or same period (period = day, date = 2020-03-04)'
        ];

        yield 'segment archiving during "all visits" archiving should be detected as intersecting with lower period' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-31', 'period' => Month::PERIOD_ID, 'segment' => 'browserCode==IE'],
            'expected' => 'all visits archive in progress for same site with lower or same period (period = day, date = 2020-03-04)'
        ];

        yield 'segment archiving during "all visits" archiving not should be detected as intersecting with different periods' => [
            'existingInvalidations' => [
                ['name' => 'done', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2020-04-01', 'date2' => '2020-04-30', 'period' => Month::PERIOD_ID, 'segment' => 'browserCode==IE'],
            'expected' => null
        ];

        yield '"all visits" archiving, while running a segment should be detected as intersecting' => [
            'existingInvalidations' => [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID],
            'expected' => 'segment archive in progress for same site with lower or same period (browserCode==IE, period = day, date = 2020-03-04)'
        ];

        yield '"all visits" plugin archiving, while running a segment should be detected as intersecting' => [
            'existingInvalidations' => [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done.VisitsSummary', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID],
            'expected' => 'segment archive in progress for same site with lower or same period (browserCode==IE, period = day, date = 2020-03-04)'
        ];

        yield '"all visits" archiving with bigger period, while running a segment should be detected as intersecting' => [
            'existingInvalidations' => [
                ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done', 'idsite' => 1, 'date1' => '2020-03-01', 'date2' => '2020-03-31', 'period' => Month::PERIOD_ID],
            'expected' => 'segment archive in progress for same site with lower or same period (browserCode==IE, period = day, date = 2020-03-04)'
        ];

        yield 'same period, but different segments archiving should not be detected as intersecting' => [
            'existingInvalidations' => [
                ['name' => 'done3736b708e4d20cfc10610e816a1b2341', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'status' => 1, 'ts_started' => date('Y-m-d H:i:s')],
            ],
            'archiveToProcess' => ['name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc', 'idsite' => 1, 'date1' => '2020-03-04', 'date2' => '2020-03-04', 'period' => Day::PERIOD_ID, 'segment' => 'browserCode==IE'],
            'expected' => null
        ];
    }

    private function getMockCronArchive()
    {
        return $this->getMockBuilder(CronArchive::class)
                     ->onlyMethods(['invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain'])
                     ->getMock();
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

        return new QueueConsumer(new NullLogger(), null, null, null, new Model(), new SegmentArchiving(), $mockCronArchive, $cliRequestProcessor);
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    private function simulateJobStart($idinvalidation)
    {
        Db::query("UPDATE " . Common::prefixTable('archive_invalidations') . " SET status = 1 WHERE idinvalidation = ?", [$idinvalidation]);
    }
}
