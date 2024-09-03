<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Exception;
use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Db;
use Piwik\Db\BatchInsert;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\SettingsServer;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ArchiveProcessorTest extends ArchiveProcessor\Loader
{
    public $time;

    public function getParams()
    {
        return $this->params;
    }
}

/**
 * @group Core
 */
class ArchiveProcessingTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // setup the access layer
        FakeAccess::$superUser = true;

        ArchiveTableCreator::$tablesAlreadyInstalled = null;
    }

    public function tearDown(): void
    {
        ArchiveTableCreator::$tablesAlreadyInstalled = null;
    }

    /**
     * Creates a new website
     *
     * @param string $timezone
     * @return Site
     */
    private function createWebsite($timezone = 'UTC')
    {
        $idSite = Fixture::createWebsite('2013-03-04', 0, false, false, 1, null, null, $timezone);
        Site::clearCache();
        return new Site($idSite);
    }

    /**
     * Creates a new ArchiveProcessor object
     *
     * @param string $periodLabel
     * @param string $dateLabel
     * @param string $siteTimezone
     * @return ArchiveProcessorTest
     */
    private function createArchiveProcessor($periodLabel, $dateLabel, $siteTimezone)
    {
        $site = $this->createWebsite($siteTimezone);
        $date = Date::factory($dateLabel);
        $period = Period\Factory::build($periodLabel, $date);
        $segment = new Segment('', [$site->getId()], $period->getDateTimeStart(), $period->getDateTimeEnd());

        $params = new ArchiveProcessor\Parameters($site, $period, $segment);
        return new ArchiveProcessorTest($params);
    }

    private function createArchiveProcessorInst($periodLabel, $dateLabel, $idSite, $archiveOnly = false, $plugin = false)
    {
        $period = Period\Factory::build($periodLabel, $dateLabel);
        $segment = new Segment('', [$idSite], $period->getDateTimeStart(), $period->getDateTimeEnd());

        $params = new ArchiveProcessor\Parameters(new Site($idSite), $period, $segment);
        if ($archiveOnly) {
            $params->setRequestedPlugin($plugin);
            $params->setArchiveOnlyReport($archiveOnly);
        }
        $archiveWriter = new ArchiveWriter($params);
        $logAggregator = new LogAggregator($params);
        $archiveProcessor = new class ($params, $archiveWriter, $logAggregator) extends ArchiveProcessor {
            private $captureInserts = false;
            private $capturedInserts = [];

            public function captureInserts()
            {
                $this->captureInserts = true;
            }

            public function insertNumericRecord($name, $value)
            {
                if ($this->captureInserts) {
                    $this->capturedInserts[] = [$name, $value];
                }

                parent::insertNumericRecord($name, $value);
            }

            public function insertBlobRecord($name, $values)
            {
                if ($this->captureInserts) {
                    $this->capturedInserts[] = [$name, $values];
                }

                parent::insertBlobRecord($name, $values);
            }

            public function getCapturedInserts()
            {
                return $this->capturedInserts;
            }
        };

        return [$archiveProcessor, $archiveWriter, $params];
    }

    /**
     * test of validity of an archive, for a month not finished
     */
    public function testInitCurrentMonth()
    {
        $siteTimezone = 'UTC+10';
        $now = time();
        // this test fails in the last 10 hours of the last day of the month
        if (date('m', $now) != date('m', $now + 10 * 3600)) {
            $this->markTestSkipped('testInitCurrentMonth will fail in the last hours of the month, skipping...');
        }

        $dateLabel = date('Y-m-d', $now);
        $archiveProcessor = $this->createArchiveProcessor('month', $dateLabel, $siteTimezone);
        $archiveProcessor->time = $now;

        // min finished timestamp considered when looking at archive timestamp
        $timeout = Rules::getTodayArchiveTimeToLive();
        $this->assertTrue($timeout >= 10);
    }

    private function compareTimestamps($expected, $processed)
    {
//        $messageIfFails = Date::factory($expected)->getDatetime() . " != " . Date::factory($processed)->getDatetime();
        $messageIfFails = "Expected [$expected] but got [$processed]";
        $this->assertTrue(abs($expected - $processed) <= 4, $messageIfFails);
    }

    /**
     * test of validity of an archive, for a month in the past
     */
    public function testInitDayInPast()
    {
        $archiveProcessor = $this->createArchiveProcessor('day', '2010-01-01', 'UTC');

        $this->assertEquals('2010-01-01 00:00:00', $archiveProcessor->getParams()->getDateStart()->getDateStartUTC());
        $this->assertEquals('2010-01-01 23:59:59', $archiveProcessor->getParams()->getDateEnd()->getDateEndUTC());
    }

    /**
     * test of validity of an archive, for a non UTC date in the past
     */
    public function testInitDayInPastNonUTCWebsite()
    {
        $timezone = 'UTC+5.5';
        $archiveProcessor = $this->createArchiveProcessor('day', '2010-01-01', $timezone);

        $this->assertEquals('2009-12-31 18:30:00', $archiveProcessor->getParams()->getDateStart()->getDateStartUTC());
        $this->assertEquals('2010-01-01 18:29:59', $archiveProcessor->getParams()->getDateEnd()->getDateEndUTC());
    }

    /**
     * test of validity of an archive, for a non UTC month in the past
     */
    public function testInitMonthInPastNonUTCWebsite()
    {
        $timezone = 'UTC-5.5';
        $archiveProcessor = $this->createArchiveProcessor('month', '2010-01-02', $timezone);

        $this->assertEquals('2010-01-01 05:30:00', $archiveProcessor->getParams()->getDateStart()->getDateStartUTC());
        $this->assertEquals('2010-02-01 05:29:59', $archiveProcessor->getParams()->getDateEnd()->getDateEndUTC());
    }

    /**
     * test of validity of an archive, for today's archive
     */
    public function testInitToday()
    {
        $now = time();
        $siteTimezone = 'UTC-1';
        $timestamp = Date::factory('now', $siteTimezone)->getTimestamp();
        $dateLabel = date('Y-m-d', $timestamp);

        Rules::setBrowserTriggerArchiving(true);

        $archiveProcessor = $this->createArchiveProcessor('day', $dateLabel, $siteTimezone);
        $archiveProcessor->time = $now;

        // when browsers don't trigger archives...
        Rules::setBrowserTriggerArchiving(false);
        // ...we force ArchiveProcessor to fetch any of the most recent archive

        $this->assertEquals(date('Y-m-d', $timestamp) . ' 01:00:00', $archiveProcessor->getParams()->getDateStart()->getDateStartUTC());
        $this->assertEquals(date('Y-m-d', $timestamp + 86400) . ' 00:59:59', $archiveProcessor->getParams()->getDateEnd()->getDateEndUTC());
    }

    /**
     * test of validity of an archive, for today's archive with european timezone
     */
    public function testInitTodayEurope()
    {
        if (!SettingsServer::isTimezoneSupportEnabled()) {
            $this->markTestSkipped('timezones needs to be supported');
        }

        $now = time();
        $siteTimezone = 'Europe/Paris';
        $timestamp = Date::factory('now', $siteTimezone)->getTimestamp();
        $dateLabel = date('Y-m-d', $timestamp);

        Rules::setBrowserTriggerArchiving(true);

        $archiveProcessor = $this->createArchiveProcessor('day', $dateLabel, $siteTimezone);
        $archiveProcessor->time = $now;

        // when browsers don't trigger archives...
        Rules::setBrowserTriggerArchiving(false);
        // ...we force ArchiveProcessor to fetch any of the most recent archive

        // this test varies with DST
        $this->assertTrue($archiveProcessor->getParams()->getDateStart()->getDateStartUTC() == date('Y-m-d', $timestamp - 86400) . ' 22:00:00' ||
            $archiveProcessor->getParams()->getDateStart()->getDateStartUTC() == date('Y-m-d', $timestamp - 86400) . ' 23:00:00');
        $this->assertTrue($archiveProcessor->getParams()->getDateEnd()->getDateEndUTC() == date('Y-m-d', $timestamp) . ' 21:59:59' ||
            $archiveProcessor->getParams()->getDateEnd()->getDateEndUTC() == date('Y-m-d', $timestamp) . ' 22:59:59');
    }

    /**
     * test of validity of an archive, for today's archive with toronto's timezone
     */
    public function testInitTodayToronto()
    {
        if (!SettingsServer::isTimezoneSupportEnabled()) {
            $this->markTestSkipped('timezones needs to be supported');
        }

        $now = time();
        $siteTimezone = 'America/Toronto';
        $timestamp = Date::factory('now', $siteTimezone)->getTimestamp();
        $dateLabel = date('Y-m-d', $timestamp);

        Rules::setBrowserTriggerArchiving(true);

        $archiveProcessor = $this->createArchiveProcessor('day', $dateLabel, $siteTimezone);
        $archiveProcessor->time = $now;

        // when browsers don't trigger archives...
        Rules::setBrowserTriggerArchiving(false);

        // this test varies with DST
        $this->assertTrue($archiveProcessor->getParams()->getDateStart()->getDateStartUTC() == date('Y-m-d', $timestamp) . ' 04:00:00' ||
            $archiveProcessor->getParams()->getDateStart()->getDateStartUTC() == date('Y-m-d', $timestamp) . ' 05:00:00');
        $this->assertTrue($archiveProcessor->getParams()->getDateEnd()->getDateEndUTC() == date('Y-m-d', $timestamp + 86400) . ' 03:59:59' ||
            $archiveProcessor->getParams()->getDateEnd()->getDateEndUTC() == date('Y-m-d', $timestamp + 86400) . ' 04:59:59');
    }

    /**
     * Testing batch insert
     */
    public function testTableInsertBatch()
    {
        $table = Common::prefixTable('site_url');
        $data = $this->getDataInsert();
        try {
            $didWeUseBulk = BatchInsert::tableInsertBatch(
                $table,
                array('idsite', 'url'),
                $data,
                $throwException = true,
                'utf8'
            );
        } catch (Exception $e) {
            $didWeUseBulk = $e->getMessage();
        }

        $this->checkLoadDataInFileWasUsed($didWeUseBulk);

        if ($didWeUseBulk === true) {
            $this->checkTableIsExpected($table, $data);

            // INSERT again the bulk. Because we use keyword LOCAL the data will be REPLACED automatically (see mysql doc)
            BatchInsert::tableInsertBatch($table, array('idsite', 'url'), $data);
            $this->checkTableIsExpected($table, $data);
        }
    }

    protected function checkLoadDataInFileWasUsed($didWeUseBulk)
    {
        static $skippedOnce = false;
        if (
            $didWeUseBulk !== true
            && $skippedOnce === false
        ) {
            $skippedOnce = true;
            $this->fail(
                'Performance notice: LOAD DATA [LOCAL] INFILE query is not working, so Piwik will fallback to using plain INSERTs '
                . ' which will result in a slightly slower Archiving process.'
                . ". \n"
                . ' The error Messages from MySQL were: '
                . $didWeUseBulk
                . "\n\n Learn more how to enable LOAD LOCAL DATA INFILE see the Mysql doc (http://dev.mysql.com/doc/refman/5.0/en/load-data-local.html) "
                . "\n   or ask in this Piwik ticket (https://github.com/matomo-org/matomo/issues/3605)"
            );
        }
        return $didWeUseBulk;
    }

    /**
     * Testing plain inserts
     */
    public function testTableInsertBatchIterate()
    {
        $table = Common::prefixTable('site_url');
        $data = $this->getDataInsert();
        BatchInsert::tableInsertBatchIterate($table, array('idsite', 'url'), $data);
        $this->checkTableIsExpected($table, $data);

        // If we insert AGAIN, expect to throw an error because the primary key already exists
        try {
            BatchInsert::tableInsertBatchIterate($table, array('idsite', 'url'), $data, $ignoreWhenDuplicate = false);
        } catch (Exception $e) {
            // However if we insert with keyword REPLACE, then the new data should be saved
            BatchInsert::tableInsertBatchIterate($table, array('idsite', 'url'), $data, $ignoreWhenDuplicate = true);
            $this->checkTableIsExpected($table, $data);
            return;
        }
        $this->fail('Exception expected');
    }

    /**
     * Testing batch insert (BLOB)
     */
    public function testTableInsertBatchBlob()
    {
        $dateLabel = '2011-03-31';
        $table = ArchiveTableCreator::getBlobTable(Date::factory($dateLabel));

        $data = $this->getBlobDataInsert();
        try {
            $didWeUseBulk = BatchInsert::tableInsertBatch(
                $table,
                array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'),
                $data,
                $throwException = true,
                $charset = 'latin1'
            );
        } catch (Exception $e) {
            $didWeUseBulk = $e->getMessage();
        }
        $this->checkLoadDataInFileWasUsed($didWeUseBulk);

        // If bulk wasn't used the exception was caught and the INSERT didn't work
        if ($didWeUseBulk === true) {
            $this->checkTableIsExpectedBlob($table, $data);
        }
        // INSERT again the bulk. Because we use keyword LOCAL the data will be REPLACED automatically (see mysql doc)
        $didWeUseBulk = BatchInsert::tableInsertBatch($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data, $throw = false, $charset = 'latin1');
        if ($didWeUseBulk === true) {
            $this->checkTableIsExpectedBlob($table, $data);
        }
    }

    /**
     * Testing plain inserts (BLOB)
     */
    public function testTableInsertBatchIterateBlob()
    {
        $dateLabel = '2011-03-31';
        $table = ArchiveTableCreator::getBlobTable(Date::factory($dateLabel));

        $data = $this->getBlobDataInsert();
        BatchInsert::tableInsertBatchIterate($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data);
        $this->checkTableIsExpectedBlob($table, $data);

        // If we insert AGAIN, expect to throw an error because the primary key already exist
        try {
            BatchInsert::tableInsertBatchIterate($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data, $ignoreWhenDuplicate = false);
        } catch (Exception $e) {
            // However if we insert with keyword REPLACE, then the new data should be saved
            BatchInsert::tableInsertBatchIterate($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data, $ignoreWhenDuplicate = true);
            $this->checkTableIsExpectedBlob($table, $data);
            return;
        }
        $this->fail('Exception expected');
    }

    public function testAggregateNumericMetricsAggregatesCorrectly()
    {
        $allMetrics = [
            '2015-02-03' => [
                'nb_visits' => 2,
                'max_actions' => 3,
            ],
            '2015-02-04' => [
                'nb_visits' => 2,
                'max_actions' => 4,
            ],
            '2015-02-05' => [
                'nb_visits' => 2,
                'max_actions' => 1,
            ],
        ];

        $site = $this->createWebsite('UTC');

        foreach ($allMetrics as $date => $metrics) {
            /** @var ArchiveWriter $archiveWriter */
            list($archiveProcessor, $archiveWriter, $params) = $this->createArchiveProcessorInst('day', $date, $site->getId());
            $archiveWriter->initNewArchive();

            $archiveProcessor->insertNumericRecords($metrics);

            $archiveWriter->finalizeArchive();
        }

        /** @var ArchiveProcessor $archiveProcessor */
        list($archiveProcessor, $archiveWriter, $params) = $this->createArchiveProcessorInst('week', '2015-02-03', $site->getId());
        $archiveWriter->initNewArchive();

        $archiveProcessor->captureInserts();
        $archiveProcessor->aggregateNumericMetrics(['nb_visits', 'max_actions']);

        $archiveWriter->finalizeArchive();

        $capturedInserts = $archiveProcessor->getCapturedInserts();

        $expected = [
            [
                'nb_visits',
                6,
            ],
            [
                'max_actions',
                4,
            ]
        ];

        $this->assertEquals($expected, $capturedInserts);
    }

    public function testAggregateNumericMetricsHandlesPartialArchives()
    {
        $allMetrics = [
            '2015-02-03' => [
                'nb_visits' => 2,
                'max_actions' => 1,
            ],
            '2015-02-04' => [
                'nb_visits' => 2,
                'max_actions' => 3,
            ],
            '2015-02-05' => [
                'nb_visits' => 2,
                'max_actions' => 4,
            ],
        ];

        $site = $this->createWebsite('UTC');

        foreach ($allMetrics as $date => $metrics) {
            /** @var ArchiveWriter $archiveWriter */
            list($archiveProcessor, $archiveWriter) = $this->createArchiveProcessorInst('day', $date, $site->getId());
            $archiveWriter->initNewArchive();

            $archiveProcessor->insertNumericRecords($metrics);

            $archiveWriter->finalizeArchive();
        }

        /** @var ArchiveProcessor $archiveProcessor */
        list($archiveProcessor, $archiveWriter, $params) = $this->createArchiveProcessorInst('week', '2015-02-03', $site->getId(), 'nb_visits', 'VisitsSummary');
        $params->setIsPartialArchive(true);
        $idArchive = $archiveWriter->initNewArchive();

        $archiveProcessor->captureInserts();
        $archiveProcessor->aggregateNumericMetrics(['nb_visits']);

        $archiveWriter->finalizeArchive();

        $capturedInserts = $archiveProcessor->getCapturedInserts();

        $expected = [
            [
                'nb_visits',
                6,
            ],
        ];

        $archiveDoneFlag = Db::fetchOne("SELECT `value` FROM " . ArchiveTableCreator::getNumericTable(Date::factory('2015-02-03')) . " WHERE idarchive = ? AND name LIKE 'done%'", [$idArchive]);
        $this->assertEquals(ArchiveWriter::DONE_PARTIAL, $archiveDoneFlag);

        $this->assertEquals($expected, $capturedInserts);
    }

    public function testAggregateDataTableRecordsAggregatesCorrectly()
    {
        $table1 = new DataTable();
        $table1->addRowsFromSimpleArray([
            ['label' => 'a', 'nb_visits' => 5, 'nb_actions' => 1],
            ['label' => 'b', 'nb_visits' => 3, 'nb_actions' => 1],
        ]);
        $table2 = new DataTable();
        $table2->addRowsFromSimpleArray([
            ['label' => 'a', 'nb_visits' => 2, 'nb_actions' => 2],
        ]);
        $table3 = new DataTable();
        $table3->addRowsFromSimpleArray([
            ['label' => 'b', 'nb_visits' => 4, 'nb_actions' => 3],
        ]);

        $tables = [
            '2015-02-03' => $table1,
            '2015-02-04' => $table2,
            '2015-02-05' => $table3,
        ];

        $site = $this->createWebsite('UTC');

        foreach ($tables as $date => $table) {
            /** @var ArchiveWriter $archiveWriter */
            list($archiveProcessor, $archiveWriter) = $this->createArchiveProcessorInst('day', $date, $site->getId());
            $archiveWriter->initNewArchive();

            $tableSerialized = $table->getSerialized();
            $archiveProcessor->insertBlobRecord('Actions_test_value', $tableSerialized);

            $archiveWriter->finalizeArchive();
        }

        list($archiveProcessor, $archiveWriter) = $this->createArchiveProcessorInst('week', '2015-02-03', $site->getId());
        $archiveWriter->initNewArchive();

        $archiveProcessor->captureInserts();
        $archiveProcessor->aggregateDataTableRecords('Actions_test_value');

        $archiveWriter->finalizeArchive();

        $capturedInserts = $archiveProcessor->getCapturedInserts();
        $capturedInsertTable = DataTable::fromSerializedArray($capturedInserts[0][1][0]);
        $capturedInsertTable = $this->getXml($capturedInsertTable);

        $expectedXml = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<label>a</label>
		<nb_visits>7</nb_visits>
		<nb_actions>3</nb_actions>
	</row>
	<row>
		<label>b</label>
		<nb_visits>7</nb_visits>
		<nb_actions>4</nb_actions>
	</row>
</result>
END;

        $this->assertEquals($expectedXml, $capturedInsertTable);
    }

    public function testAggregateDataTableRecordsHandlesPartialArchives()
    {
        $table1 = new DataTable();
        $table1->addRowsFromSimpleArray([
            ['label' => 'a', 'nb_visits' => 5, 'nb_actions' => 1],
            ['label' => 'b', 'nb_visits' => 3, 'nb_actions' => 1],
        ]);
        $table2 = new DataTable();
        $table2->addRowsFromSimpleArray([
            ['label' => 'a', 'nb_visits' => 2, 'nb_actions' => 2],
        ]);
        $table3 = new DataTable();
        $table3->addRowsFromSimpleArray([
            ['label' => 'b', 'nb_visits' => 4, 'nb_actions' => 3],
        ]);

        $tables = [
            '2015-02-03' => $table1,
            '2015-02-04' => $table2,
            '2015-02-05' => $table3,
        ];

        $site = $this->createWebsite('UTC');

        foreach ($tables as $date => $table) {
            /** @var ArchiveWriter $archiveWriter */
            list($archiveProcessor, $archiveWriter) = $this->createArchiveProcessorInst('day', $date, $site->getId());
            $archiveWriter->initNewArchive();

            $tableSerialized = $table->getSerialized();
            $archiveProcessor->insertBlobRecord('Actions_test_value', $tableSerialized);

            $archiveWriter->finalizeArchive();
        }

        /** @var ArchiveProcessor $archiveProcessor */
        list($archiveProcessor, $archiveWriter, $params) = $this->createArchiveProcessorInst('week', '2015-02-03', $site->getId(), 'Actions_test_value', 'VisitsSummary');
        $params->setIsPartialArchive(true);
        $idArchive = $archiveWriter->initNewArchive();

        $archiveProcessor->captureInserts();
        $archiveProcessor->aggregateDataTableRecords('Actions_test_value');

        $archiveWriter->finalizeArchive();

        $capturedInserts = $archiveProcessor->getCapturedInserts();
        $this->assertNotEmpty($capturedInserts);

        $archiveDoneFlag = Db::fetchOne("SELECT `value` FROM " . ArchiveTableCreator::getNumericTable(Date::factory('2015-02-03')) . " WHERE idarchive = ? AND name LIKE 'done%'", [$idArchive]);
        $this->assertEquals(ArchiveWriter::DONE_PARTIAL, $archiveDoneFlag);
    }

    public function testAggregateDataTableRecordsHandlesNegativeOneLabels()
    {
        $table1 = new DataTable();
        $table1->addRowsFromSimpleArray([
            ['label' => 'a', 'nb_visits' => 5, 'nb_actions' => 1],
            ['label' => '-1', 'nb_visits' => 3, 'nb_actions' => 1],
        ]);
        $table1->addSummaryRow(new DataTable\Row([
            DataTable\Row::COLUMNS => ['label' => -1, 'nb_visits' => 10, 'nb_actions' => 15],
        ]));
        $table2 = new DataTable();
        $table2->addRowsFromSimpleArray([
            ['label' => 'a', 'nb_visits' => 2, 'nb_actions' => 2],
            ['label' => -1, 'nb_visits' => 9, 'nb_actions' => 5],
        ]);
        $table2->addSummaryRow(new DataTable\Row([
            DataTable\Row::COLUMNS => ['label' => -1, 'nb_visits' => 15, 'nb_actions' => 25],
        ]));

        $tables = [
            '2015-02-03' => $table1,
            '2015-02-04' => $table2,
        ];

        $site = $this->createWebsite('UTC');

        foreach ($tables as $date => $table) {
            /** @var ArchiveWriter $archiveWriter */
            list($archiveProcessor, $archiveWriter) = $this->createArchiveProcessorInst('day', $date, $site->getId());
            $archiveWriter->initNewArchive();

            $tableSerialized = $table->getSerialized();
            $archiveProcessor->insertBlobRecord('Actions_test_value', $tableSerialized);

            $archiveWriter->finalizeArchive();
        }

        list($archiveProcessor, $archiveWriter) = $this->createArchiveProcessorInst('week', '2015-02-03', $site->getId());
        $archiveWriter->initNewArchive();

        $archiveProcessor->captureInserts();
        $archiveProcessor->aggregateDataTableRecords('Actions_test_value');

        $archiveWriter->finalizeArchive();

        $capturedInserts = $archiveProcessor->getCapturedInserts();
        $capturedInsertTable = DataTable::fromSerializedArray($capturedInserts[0][1][0]);
        $capturedInsertTable = $this->getXml($capturedInsertTable);

        $expectedXml = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<label>a</label>
		<nb_visits>7</nb_visits>
		<nb_actions>3</nb_actions>
	</row>
	<row>
		<label>-1</label>
		<nb_visits>12</nb_visits>
		<nb_actions>6</nb_actions>
	</row>
	<row>
		<label>-1</label>
		<nb_visits>25</nb_visits>
		<nb_actions>40</nb_actions>
	</row>
</result>
END;

        $this->assertEquals($expectedXml, $capturedInsertTable);
    }

    protected function checkTableIsExpected($table, $data)
    {
        $fetched = Db::fetchAll('SELECT * FROM ' . $table);

        foreach ($data as $id => $row) {
            $this->assertEquals($data[$id][0], $fetched[$id]['idsite'], "record $id is not {$data[$id][0]}");
            $this->assertEquals($data[$id][1], $fetched[$id]['url'], "Record $id bug, not {$data[$id][1]} BUT {$fetched[$id]['url']}");
        }
    }

    protected function checkTableIsExpectedBlob($table, $data)
    {
        $fetched = Db::fetchAll('SELECT * FROM ' . $table);
        foreach ($data as $id => $row) {
            $this->assertEquals($fetched[$id]['idarchive'], $data[$id][0], "record $id idarchive is not '{$data[$id][0]}'");
            $this->assertEquals($fetched[$id]['name'], $data[$id][1], "record $id name is not '{$data[$id][1]}'");
            $this->assertEquals($fetched[$id]['idsite'], $data[$id][2], "record $id idsite is not '{$data[$id][2]}'");
            $this->assertEquals($fetched[$id]['date1'], $data[$id][3], "record $id date1 is not '{$data[$id][3]}'");
            $this->assertEquals($fetched[$id]['date2'], $data[$id][4], "record $id date2 is not '{$data[$id][4]}'");
            $this->assertEquals($fetched[$id]['period'], $data[$id][5], "record $id period is not '{$data[$id][5]}'");
            $this->assertEquals($fetched[$id]['ts_archived'], $data[$id][6], "record $id ts_archived is not '{$data[$id][6]}'");
            $this->assertEquals($fetched[$id]['value'], $data[$id][7], "record $id value is unexpected");
        }
    }

    /*
     * Schema for site_url table:
     *    site_url (
     *        idsite INTEGER(10) UNSIGNED NOT NULL,
     *        url VARCHAR(255) NOT NULL,
     *        PRIMARY KEY(idsite, url)
     *    )
     */
    protected function getDataInsert()
    {
        return array(
            array(1, 'test'),
            array(2, 'te" \n st2'),
            array(3, " \n \r \t test"),

            // these aren't expected to work on a column of datatype VARCHAR
//            array(4, gzcompress( " \n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942")),
//            array(5, gzcompress('test4')),

            array(6, 'test5'),
            array(7, '简体中文'),
            array(8, '"'),
            array(9, "'"),
            array(10, '\\'),
            array(11, '\\"'),
            array(12, '\\\''),
            array(13, "\t"),
            array(14, "test \x00 null"),
            array(15, "\x00\x01\x02\0x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f"),
        );
    }

    /**
     * see archive_blob table
     */
    protected function getBlobDataInsert()
    {
        $ts = '2011-03-31 17:48:00';
        $str = '';
        for ($i = 0; $i < 256; $i++) {
            $str .= chr($i);
        }

        $array[] = array(1, 'bytes 0-255', 1, '2011-03-31', '2011-03-31', Piwik::$idPeriods['day'], $ts, $str);

        $array[] = array(2, 'compressed string', 1, '2011-03-31', '2011-03-31', Piwik::$idPeriods['day'], $ts, gzcompress(" \n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942"));

        $str = file_get_contents(PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/lipsum.txt');
        $array[] = array(3, 'lorem ipsum', 1, '2011-03-31', '2011-03-31', Piwik::$idPeriods['day'], $ts, $str);

        $array[] = array(4, 'lorem ipsum compressed', 1, '2011-03-31', '2011-03-31', Piwik::$idPeriods['day'], $ts, gzcompress($str));

        return $array;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }

    private function getXml(DataTable $capturedInsertTable)
    {
        $xml = new DataTable\Renderer\Xml();
        $xml->setTable($capturedInsertTable);
        return $xml->render();
    }
}
