<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Integration\CronArchive;

use Piwik\ArchiveProcessor\Rules;
use Piwik\CliMulti\RequestParser;
use Piwik\Common;
use Piwik\Period\Factory;
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
use Piwik\Plugins\SitesManager\SitesManager;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class QueueConsumerTest extends IntegrationTestCase
{
    public function test_invalidateConsumeOrder()
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

        $cronArchive = new CronArchive();
        $cronArchive->init();

        $archiveFilter = $this->makeTestArchiveFilter();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1,2,3]),
            3,
            24,
            new Model(),
            new SegmentArchiving('beginning_of_time'),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );

        $segmentHash = (new Segment('browserCode==IE;dimension1==val', [1]))->getHash();
        $segmentHash2 = (new Segment('browserCode==ff', [1]))->getHash();

        $invalidations = [
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-07', 'date2' => '2018-03-07', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-08', 'date2' => '2018-03-08', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => 3, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => 2, 'report' => null],

            ['idarchive' => 1, 'name' => 'done.Actions', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => 1, 'report' => 'testReport'],
            ['idarchive' => 1, 'name' => 'done.Actions', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => 3, 'report' => 'testReport'],
            ['idarchive' => 1, 'name' => 'done.Actions', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => 2, 'report' => 'testReport'],

            // some or all subperiods before site was created
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 2, 'date1' => '2020-04-04', 'date2' => '2020-04-04', 'period' => 1, 'report' => 'testReport'],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 2, 'date1' => '2020-03-30', 'date2' => '2020-04-05', 'period' => 2, 'report' => 'testReport'],

            // segments
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2018-03-07', 'date2' => '2018-03-07', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2018-03-08', 'date2' => '2018-03-08', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => 3, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => 2, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash, 'idsite' => 2, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => 2, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash2, 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => 2, 'report' => null],

            // invalid plugin
            ['idarchive' => 1, 'name' => 'done.MyPlugin', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-11', 'period' => 2, 'report' => 'testReport'],

            // duplicates
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-06', 'date2' => '2018-03-06', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => 3, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => 3, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => 3, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => 3, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => 3, 'report' => null],

            // high ts_invalidated, should not be selected
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-01-01', 'date2' => '2018-01-31', 'period' => 3, 'report' => null, 'ts_invalidated' => Date::factory(time() + 300)->getDatetime()],
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
            array (
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-08',
                    'date2' => '2018-03-08',
                    'period' => '1',
                    'name' => 'done',
                    'report' => NULL,
                    'plugin' => NULL,
                    'segment' => '',
                ),
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-07',
                    'date2' => '2018-03-07',
                    'period' => '1',
                    'name' => 'done',
                    'report' => NULL,
                    'plugin' => NULL,
                    'segment' => '',
                ),
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-06',
                    'date2' => '2018-03-06',
                    'period' => '1',
                    'name' => 'done',
                    'report' => NULL,
                    'plugin' => NULL,
                    'segment' => '',
                ),
            ),
            array (
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-08',
                    'date2' => '2018-03-08',
                    'period' => '1',
                    'name' => 'donec3afbf588c35606b9cd9ecd1ac781428',
                    'report' => NULL,
                    'plugin' => NULL,
                    'segment' => 'browserCode==IE;dimension1==val',
                ),
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-07',
                    'date2' => '2018-03-07',
                    'period' => '1',
                    'name' => 'donec3afbf588c35606b9cd9ecd1ac781428',
                    'report' => NULL,
                    'plugin' => NULL,
                    'segment' => 'browserCode==IE;dimension1==val',
                ),
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-06',
                    'date2' => '2018-03-06',
                    'period' => '1',
                    'name' => 'done.Actions',
                    'report' => 'testReport',
                    'plugin' => 'Actions',
                    'segment' => '',
                ),
            ),
            array (
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-06',
                    'date2' => '2018-03-06',
                    'period' => '1',
                    'name' => 'donec3afbf588c35606b9cd9ecd1ac781428',
                    'report' => NULL,
                    'plugin' => NULL,
                    'segment' => 'browserCode==IE;dimension1==val',
                ),
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-04',
                    'date2' => '2018-03-04',
                    'period' => '1',
                    'name' => 'done',
                    'report' => NULL,
                    'plugin' => NULL,
                    'segment' => '',
                ),
            ),
            array (
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-04',
                    'date2' => '2018-03-04',
                    'period' => '1',
                    'name' => 'donec3afbf588c35606b9cd9ecd1ac781428',
                    'report' => NULL,
                    'plugin' => NULL,
                    'segment' => 'browserCode==IE;dimension1==val',
                ),
            ),
            array (
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-04',
                    'date2' => '2018-03-11',
                    'period' => '2',
                    'name' => 'done',
                    'report' => NULL,
                    'plugin' => NULL,
                    'segment' => '',
                ),
            ),
            array (
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-04',
                    'date2' => '2018-03-11',
                    'period' => '2',
                    'name' => 'done.Actions',
                    'report' => 'testReport',
                    'plugin' => 'Actions',
                    'segment' => '',
                ),
            ),
            array (
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-04',
                    'date2' => '2018-03-11',
                    'period' => '2',
                    'name' => 'donec3afbf588c35606b9cd9ecd1ac781428',
                    'report' => NULL,
                    'plugin' => NULL,
                    'segment' => 'browserCode==IE;dimension1==val',
                ),
            ),
            array (
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-01',
                    'date2' => '2018-03-31',
                    'period' => '3',
                    'name' => 'done',
                    'report' => NULL,
                    'plugin' => NULL,
                    'segment' => '',
                ),
            ),
            array (
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-01',
                    'date2' => '2018-03-31',
                    'period' => '3',
                    'name' => 'done.Actions',
                    'report' => 'testReport',
                    'plugin' => 'Actions',
                    'segment' => '',
                ),
            ),
            array (
                array (
                    'idarchive' => '1',
                    'idsite' => '1',
                    'date1' => '2018-03-01',
                    'date2' => '2018-03-31',
                    'period' => '3',
                    'name' => 'donec3afbf588c35606b9cd9ecd1ac781428',
                    'report' => NULL,
                    'plugin' => NULL,
                    'segment' => 'browserCode==IE;dimension1==val',
                ),
            ),
            array ( // end of idsite=1
            ),
            array (
                array (
                    'idarchive' => '1',
                    'idsite' => '2',
                    'date1' => '2020-03-30',
                    'date2' => '2020-04-05',
                    'period' => '2',
                    'name' => 'done',
                    'report' => 'testReport',
                    'plugin' => NULL,
                    'segment' => '',
                ),
            ),
            array ( // end of idsite=2
            ),
            array ( // end of idsite=3
            ),
        ];

        $this->assertEquals($expectedInvalidationsFound, $iteratedInvalidations, "Invalidations inserted:\n" . var_export($invalidations, true));

        // automated ccheck for no duplicates
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
    }

    public function test_skipSegmentsToday()
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

        $cronArchive = new CronArchive();
        $cronArchive->init();

        $archiveFilter = $this->makeTestArchiveFilter(null, null, null, false, true);

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1]),
            3,
            24,
            new Model(),
            new SegmentArchiving('beginning_of_time'),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );

        $segmentHash1 = (new Segment('browserCode==IE', [1]))->getHash();
        $segmentHash2 = (new Segment('browserCode==FF', [1]))->getHash();

        $invalidations = [
            ['idarchive' => 1, 'name' => 'done' . $segmentHash1, 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash2, 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash1, 'idsite' => 1, 'date1' => '2018-03-03', 'date2' => '2018-03-03', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash2 . '.ExamplePlugin', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => 1, 'report' => null],
            ['idarchive' => 1, 'name' => 'done' . $segmentHash1, 'idsite' => 1, 'date1' => '2018-03-01', 'date2' => '2018-03-31', 'period' => 3, 'report' => null],
            ['idarchive' => 1, 'name' => 'done', 'idsite' => 1, 'date1' => '2018-03-04', 'date2' => '2018-03-04', 'period' => 1, 'report' => null],
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
            array (
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-04',
                        'date2' => '2018-03-04',
                        'period' => '1',
                        'name' => 'done',
                        'report' => NULL,
                        'plugin' => NULL,
                        'segment' => '',
                    ),
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-03',
                        'date2' => '2018-03-03',
                        'period' => '1',
                        'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                        'report' => NULL,
                        'plugin' => NULL,
                        'segment' => 'browserCode==IE',
                    ),
            ),
            array (
                0 =>
                    array (
                        'idarchive' => '1',
                        'idsite' => '1',
                        'date1' => '2018-03-01',
                        'date2' => '2018-03-31',
                        'period' => '3',
                        'name' => 'done5f4f9bafeda3443c3c2d4b2ef4dffadc',
                        'report' => NULL,
                        'plugin' => NULL,
                        'segment' => 'browserCode==IE',
                    ),
            ),
            array (// end of idsite=1
            ),
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

    private function makeTestArchiveFilter($restrictToDateRange = null, $restrictToPeriods = null, $segmentsToForce = null,
                                           $disableSegmentsArchiving = false, $skipSegmentsToday = false)
    {
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
                $inv['idarchive'],
                $inv['name'],
                $inv['idsite'],
                $inv['date1'],
                $inv['date2'],
                $inv['period'],
                isset($inv['ts_invalidated']) ? $inv['ts_invalidated'] : $now,
                $inv['report'],
            ];
            Db::query("INSERT INTO `$table` (idarchive, name, idsite, date1, date2, period, ts_invalidated, report, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)", $bind);
        }
    }

    public function test_canSkipArchiveBecauseNoPoint_returnsTrueIfDateRangeHasNoVisits()
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
            new SegmentArchiving('beginning_of_time'),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );

        $invalidation = [
            'idsite' => 1,
            'period' => 1,
            'date1' => '2020-04-05',
            'date2' => '2020-04-05',
            'name' => 'done',
            'segment' => '',
        ];

        $result = $queueConsumer->canSkipArchiveBecauseNoPoint($invalidation);
        $this->assertTrue($result);
    }

    public function test_canSkipArchiveBecauseNoPoint_returnsFalseIfDateRangeHasVisits_AndPeriodDoesNotIncludeToday()
    {
        $idSite = Fixture::createWebsite('2015-02-03');

        Date::$now = strtotime('2020-04-05');

        $t = Fixture::getTracker($idSite, '2020-03-05 10:34:00');
        $t->setUrl('http://whatever.com');
        Fixture::checkResponse($t->doTrackPageView('test title'));

        $cronArchive = new CronArchive();

        $archiveFilter = $this->makeTestArchiveFilter();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1]),
            3,
            24,
            new Model(),
            new SegmentArchiving('beginning_of_time'),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );

        $invalidation = [
            'idsite' => 1,
            'period' => 1,
            'date1' => '2020-03-05',
            'date2' => '2020-03-05',
            'name' => 'done',
            'segment' => '',
        ];

        $result = $queueConsumer->canSkipArchiveBecauseNoPoint($invalidation);
        $this->assertFalse($result);
    }

    public function test_usableArchiveExists_returnsTrueIfDateRangeHasVisits_AndPeriodIncludesToday_AndExistingArchiveIsRecent()
    {
        $idSite = Fixture::createWebsite('2015-02-03');

        Date::$now = strtotime('2020-04-05');

        $t = Fixture::getTracker($idSite, '2020-04-05 10:34:00');
        $t->setUrl('http://whatever.com');
        Fixture::checkResponse($t->doTrackPageView('test title'));

        $cronArchive = new CronArchive();

        $archiveFilter = $this->makeTestArchiveFilter();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1]),
            3,
            24,
            new Model(),
            new SegmentArchiving('beginning_of_time'),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );

        $invalidation = [
            'idsite' => 1,
            'period' => 2,
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

    public function test_canSkipArchiveBecauseNoPoint_returnsFalseIfDateRangeHasVisits_AndPeriodIncludesToday_AndOnlyExistingArchiveIsRecentButPartial()
    {
        $idSite = Fixture::createWebsite('2015-02-03');

        Date::$now = strtotime('2020-04-05');

        $t = Fixture::getTracker($idSite, '2020-04-05 10:34:00');
        $t->setUrl('http://whatever.com');
        Fixture::checkResponse($t->doTrackPageView('test title'));

        $cronArchive = new CronArchive();

        $archiveFilter = $this->makeTestArchiveFilter();

        $queueConsumer = new QueueConsumer(
            StaticContainer::get(LoggerInterface::class),
            new FixedSiteIds([1]),
            3,
            24,
            new Model(),
            new SegmentArchiving('beginning_of_time'),
            $cronArchive,
            new RequestParser(true),
            $archiveFilter
        );

        $invalidation = [
            'idsite' => 1,
            'period' => 2,
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