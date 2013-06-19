<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class ArchiveProcessingTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        // setup the access layer
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        Zend_Registry::set('access', $pseudoMockAccess);
    }

    /**
     * Creates a new website
     *
     * @param string $timezone
     * @return Piwik_Site
     */
    private function _createWebsite($timezone = 'UTC')
    {
        $idSite = Piwik_SitesManager_API::getInstance()->addSite(
            "site1",
            array("http://piwik.net"),
            $ecommerce = 0,
            $siteSearch = 1, $searchKeywordParameters = null, $searchCategoryParameters = null,
            $excludedIps = "",
            $excludedQueryParameters = "",
            $timezone);

        Piwik_Site::clearCache();
        return new Piwik_Site($idSite);
    }

    /**
     * Creates a new ArchiveProcessor object
     *
     * @param string $periodLabel
     * @param string $dateLabel
     * @param string $siteTimezone
     * @return Piwik_ArchiveProcessor
     */
    private function _createArchiveProcessor($periodLabel, $dateLabel, $siteTimezone)
    {
        $site = $this->_createWebsite($siteTimezone);
        $date = Piwik_Date::factory($dateLabel);
        $period = Piwik_Period::factory($periodLabel, $date);
        $segment = new Piwik_Segment('', $site->getId());


        if($period->getLabel() == 'day') {
            return new Piwik_ArchiveProcessor_Day($period, $site, $segment);
        } else {
            return new Piwik_ArchiveProcessor_Period($period, $site, $segment);
        }
    }

    /**
     * test of validity of an archive, for a month not finished
     * @group Core
     * @group ArchiveProcessor
     */
    public function testInitCurrentMonth()
    {
        $siteTimezone = 'UTC+10';
        $now = time();
        // this test fails in the last 10 hours of the last day of the month
        if(date('m', $now) != date('m', $now + 10 * 3600)) {
            $this->markTestSkipped('testInitCurrentMonth will fail in the last hours of the month, skipping...');
        }

        $dateLabel = date('Y-m-d', $now);
        $archiveProcessor = $this->_createArchiveProcessor('month', $dateLabel, $siteTimezone);
        $archiveProcessor->time = $now;

        $minTimestamp = $archiveProcessor->getMinTimeArchivedProcessed();



        // min finished timestamp considered when looking at archive timestamp
        $timeout = Piwik_ArchiveProcessor_Rules::getTodayArchiveTimeToLive();
        $this->assertTrue($timeout >= 10);
        $dateMinArchived = $now - $timeout;

        $messageIfFails = Piwik_Date::factory($minTimestamp)->getDatetime() . " != " . Piwik_Date::factory($dateMinArchived)->getDatetime();
        $this->assertTrue( $minTimestamp == $dateMinArchived || $minTimestamp == $dateMinArchived + 1, $messageIfFails);
        $this->assertTrue($archiveProcessor->isArchiveTemporary());
    }

    /**
     * test of validity of an archive, for a month in the past
     * @group Core
     * @group ArchiveProcessor
     */
    public function testInitDayInPast()
    {
        $archiveProcessor = $this->_createArchiveProcessor('day', '2010-01-01', 'UTC');

        // min finished timestamp considered when looking at archive timestamp 
        $dateMinArchived = Piwik_Date::factory('2010-01-02')->getTimestamp();
        $this->assertEquals($archiveProcessor->getMinTimeArchivedProcessed() + 1, $dateMinArchived);

        $this->assertEquals('2010-01-01 00:00:00', $archiveProcessor->getDateStart()->getDateStartUTC());
        $this->assertEquals('2010-01-01 23:59:59', $archiveProcessor->getDateEnd()->getDateEndUTC());
        $this->assertFalse($archiveProcessor->isArchiveTemporary());
    }

    /**
     * test of validity of an archive, for a non UTC date in the past
     * @group Core
     * @group ArchiveProcessor
     */
    public function testInitDayInPastNonUTCWebsite()
    {
        $timezone = 'UTC+5.5';
        $archiveProcessor = $this->_createArchiveProcessor('day', '2010-01-01', $timezone);
        // min finished timestamp considered when looking at archive timestamp 
        $dateMinArchived = Piwik_Date::factory('2010-01-01 18:30:00');
        $this->assertEquals($archiveProcessor->getMinTimeArchivedProcessed() + 1, $dateMinArchived->getTimestamp());

        $this->assertEquals('2009-12-31 18:30:00', $archiveProcessor->getDateStart()->getDateStartUTC());
        $this->assertEquals('2010-01-01 18:29:59', $archiveProcessor->getDateEnd()->getDateEndUTC());
        $this->assertFalse($archiveProcessor->isArchiveTemporary());
    }

    /**
     * test of validity of an archive, for a non UTC month in the past
     * @group Core
     * @group ArchiveProcessor
     */
    public function testInitMonthInPastNonUTCWebsite()
    {
        $timezone = 'UTC-5.5';
        $archiveProcessor = $this->_createArchiveProcessor('month', '2010-01-02', $timezone);
        // min finished timestamp considered when looking at archive timestamp 
        $dateMinArchived = Piwik_Date::factory('2010-02-01 05:30:00');
        $this->assertEquals($archiveProcessor->getMinTimeArchivedProcessed() + 1, $dateMinArchived->getTimestamp());

        $this->assertEquals('2010-01-01 05:30:00', $archiveProcessor->getDateStart()->getDateStartUTC());
        $this->assertEquals('2010-02-01 05:29:59', $archiveProcessor->getDateEnd()->getDateEndUTC());
        $this->assertFalse($archiveProcessor->isArchiveTemporary());
    }

    /**
     * test of validity of an archive, for today's archive
     * @group Core
     * @group ArchiveProcessor
     */
    public function testInitToday()
    {
        $now = time();
        $siteTimezone = 'UTC-1';
        $timestamp = Piwik_Date::factory('now', $siteTimezone)->getTimestamp();
        $dateLabel = date('Y-m-d', $timestamp);

        Piwik_ArchiveProcessor_Rules::setBrowserTriggerArchiving(true);

        $archiveProcessor = $this->_createArchiveProcessor('day', $dateLabel, $siteTimezone);
        $archiveProcessor->time = $now;

        // we look at anything processed within the time to live range
        $dateMinArchived = $now - Piwik_ArchiveProcessor_Rules::getTodayArchiveTimeToLive();
        $this->assertEquals($dateMinArchived, $archiveProcessor->getMinTimeArchivedProcessed());
        $this->assertTrue($archiveProcessor->isArchiveTemporary());

        // when browsers don't trigger archives, we force ArchiveProcessor
        // to fetch any of the most recent archive
        Piwik_ArchiveProcessor_Rules::setBrowserTriggerArchiving(false);
        // see isArchivingDisabled()
        // Running in CLI doesn't impact the time to live today's archive we are loading
        // From CLI, we will not return data that is 'stale' 
        if (!Piwik_Common::isPhpCliMode()) {
            $dateMinArchived = 0;
        }
        $this->assertEquals($archiveProcessor->getMinTimeArchivedProcessed(), $dateMinArchived);

        $this->assertEquals(date('Y-m-d', $timestamp) . ' 01:00:00', $archiveProcessor->getDateStart()->getDateStartUTC());
        $this->assertEquals(date('Y-m-d', $timestamp + 86400) . ' 00:59:59', $archiveProcessor->getDateEnd()->getDateEndUTC());
        $this->assertTrue($archiveProcessor->isArchiveTemporary());
    }

    /**
     * test of validity of an archive, for today's archive with european timezone
     * @group Core
     * @group ArchiveProcessor
     */
    public function testInitTodayEurope()
    {
        if (!Piwik::isTimezoneSupportEnabled()) {
            $this->markTestSkipped('timezones needs to be supported');
        }

        $now = time();
        $siteTimezone = 'Europe/Paris';
        $timestamp = Piwik_Date::factory('now', $siteTimezone)->getTimestamp();
        $dateLabel = date('Y-m-d', $timestamp);

        Piwik_ArchiveProcessor_Rules::setBrowserTriggerArchiving(true);

        $archiveProcessor = $this->_createArchiveProcessor('day', $dateLabel, $siteTimezone);
        $archiveProcessor->time = $now;

        // we look at anything processed within the time to live range
        $dateMinArchived = $now - Piwik_ArchiveProcessor_Rules::getTodayArchiveTimeToLive();
        $this->assertEquals($archiveProcessor->getMinTimeArchivedProcessed(), $dateMinArchived);
        $this->assertTrue($archiveProcessor->isArchiveTemporary());

        // when browsers don't trigger archives, we force ArchiveProcessor
        // to fetch any of the most recent archive
        Piwik_ArchiveProcessor_Rules::setBrowserTriggerArchiving(false);
        // see isArchivingDisabled()
        // Running in CLI doesn't impact the time to live today's archive we are loading
        // From CLI, we will not return data that is 'stale'
        if (!Piwik_Common::isPhpCliMode()) {
            $dateMinArchived = 0;
        }
        $this->assertEquals($archiveProcessor->getMinTimeArchivedProcessed(), $dateMinArchived);

        // this test varies with DST
        $this->assertTrue($archiveProcessor->getDateStart()->getDateStartUTC() == date('Y-m-d', $timestamp - 86400) . ' 22:00:00' ||
            $archiveProcessor->getDateStart()->getDateStartUTC() == date('Y-m-d', $timestamp - 86400) . ' 23:00:00');
        $this->assertTrue($archiveProcessor->getDateEnd()->getDateEndUTC() == date('Y-m-d', $timestamp) . ' 21:59:59' ||
            $archiveProcessor->getDateEnd()->getDateEndUTC() == date('Y-m-d', $timestamp) . ' 22:59:59');

        $this->assertTrue($archiveProcessor->isArchiveTemporary());
    }

    /**
     * test of validity of an archive, for today's archive with toronto's timezone
     * @group Core
     * @group ArchiveProcessor
     */
    public function testInitTodayToronto()
    {
        if (!Piwik::isTimezoneSupportEnabled()) {
            $this->markTestSkipped('timezones needs to be supported');
        }

        $now = time();
        $siteTimezone = 'America/Toronto';
        $timestamp = Piwik_Date::factory('now', $siteTimezone)->getTimestamp();
        $dateLabel = date('Y-m-d', $timestamp);

        Piwik_ArchiveProcessor_Rules::setBrowserTriggerArchiving(true);

        $archiveProcessor = $this->_createArchiveProcessor('day', $dateLabel, $siteTimezone);
        $archiveProcessor->time = $now;

        // we look at anything processed within the time to live range
        $dateMinArchived = $now - Piwik_ArchiveProcessor_Rules::getTodayArchiveTimeToLive();
        $this->assertEquals($archiveProcessor->getMinTimeArchivedProcessed(), $dateMinArchived);
        $this->assertTrue($archiveProcessor->isArchiveTemporary());

        // when browsers don't trigger archives, we force ArchiveProcessor
        // to fetch any of the most recent archive
        Piwik_ArchiveProcessor_Rules::setBrowserTriggerArchiving(false);
        // see isArchivingDisabled()
        // Running in CLI doesn't impact the time to live today's archive we are loading
        // From CLI, we will not return data that is 'stale'
        if (!Piwik_Common::isPhpCliMode()) {
            $dateMinArchived = 0;
        }
        $this->assertEquals($archiveProcessor->getMinTimeArchivedProcessed(), $dateMinArchived);

        // this test varies with DST
        $this->assertTrue($archiveProcessor->getDateStart()->getDateStartUTC() == date('Y-m-d', $timestamp) . ' 04:00:00' ||
            $archiveProcessor->getDateStart()->getDateStartUTC() == date('Y-m-d', $timestamp) . ' 05:00:00');
        $this->assertTrue($archiveProcessor->getDateEnd()->getDateEndUTC() == date('Y-m-d', $timestamp + 86400) . ' 03:59:59' ||
            $archiveProcessor->getDateEnd()->getDateEndUTC() == date('Y-m-d', $timestamp + 86400) . ' 04:59:59');

        $this->assertTrue($archiveProcessor->isArchiveTemporary());
    }

    /**
     * Testing batch insert
     * @group Core
     * @group ArchiveProcessor
     */
    public function testTableInsertBatch()
    {
        $table = Piwik_Common::prefixTable('site_url');
        $data = $this->_getDataInsert();
        try {
            $didWeUseBulk = Piwik::tableInsertBatch($table,
                array('idsite', 'url'),
                $data,
                $throwException = true);

        } catch (Exception $e) {
            $didWeUseBulk = $e->getMessage();
        }
        $this->_checkLoadDataInFileWasUsed($didWeUseBulk);

        if ($didWeUseBulk === true) {
            $this->_checkTableIsExpected($table, $data);

            // INSERT again the bulk. Because we use keyword LOCAL the data will be REPLACED automatically (see mysql doc)
            Piwik::tableInsertBatch($table, array('idsite', 'url'), $data);
            $this->_checkTableIsExpected($table, $data);
        }
    }

    protected function _checkLoadDataInFileWasUsed($didWeUseBulk)
    {
        static $skippedOnce = false;
        if ($didWeUseBulk !== true
            && $skippedOnce === false
            // HACK: Only alert for MysqlI since PDO is often failing and Jenkins should always run MYSQLI + PDO
            // This helps "hiding" the warning on PDO Mysql but we have to make sure mysqli tests are always executed!
            && Piwik_Config::getInstance()->database['adapter'] == 'MYSQLI'
        ) {
            $skippedOnce = true;
            $this->fail(
                'Performance notice: LOAD DATA [LOCAL] INFILE query is not working, so Piwik will fallback to using plain INSERTs '
                    . ' which will result in a slightly slower Archiving process.'
                    . ". \n"
                    . ' The error Messages from MySQL were: '
                    . $didWeUseBulk
                    . "\n\n Learn more how to enable LOAD LOCAL DATA INFILE see the Mysql doc (http://dev.mysql.com/doc/refman/5.0/en/load-data-local.html) "
                    . "\n   or ask in this Piwik ticket (http://dev.piwik.org/trac/ticket/3605)"
            );
        }
        return $didWeUseBulk;
    }

    /**
     * Testing plain inserts
     * @group Core
     * @group ArchiveProcessor
     */
    public function testTableInsertBatchIterate()
    {
        $table = Piwik_Common::prefixTable('site_url');
        $data = $this->_getDataInsert();
        Piwik::tableInsertBatchIterate($table, array('idsite', 'url'), $data);
        $this->_checkTableIsExpected($table, $data);

        // If we insert AGAIN, expect to throw an error because the primary key already exists
        try {
            Piwik::tableInsertBatchIterate($table, array('idsite', 'url'), $data, $ignoreWhenDuplicate = false);
        } catch (Exception $e) {
            // However if we insert with keyword REPLACE, then the new data should be saved
            Piwik::tableInsertBatchIterate($table, array('idsite', 'url'), $data, $ignoreWhenDuplicate = true);
            $this->_checkTableIsExpected($table, $data);
            return;
        }
        $this->fail('Exception expected');
    }

    /**
     * Testing batch insert (BLOB)
     * @group Core
     * @group ArchiveProcessor
     */
    public function testTableInsertBatchBlob()
    {
        $siteTimezone = 'America/Toronto';
        $dateLabel = '2011-03-31';
        $table = Piwik_DataAccess_ArchiveTableCreator::getBlobTable(Piwik_Date::factory($dateLabel));

        $data = $this->_getBlobDataInsert();
        try {
            $didWeUseBulk = Piwik::tableInsertBatch($table,
                array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'),
                $data,
                $throwException = true);
        } catch (Exception $e) {
            $didWeUseBulk = $e->getMessage();
        }
        $this->_checkLoadDataInFileWasUsed($didWeUseBulk);

        // If bulk wasn't used the exception was caught and the INSERT didn't work
        if ($didWeUseBulk === true) {
            $this->_checkTableIsExpectedBlob($table, $data);
        }
        // INSERT again the bulk. Because we use keyword LOCAL the data will be REPLACED automatically (see mysql doc)
        $didWeUseBulk = Piwik::tableInsertBatch($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data);
        if ($didWeUseBulk === true) {
            $this->_checkTableIsExpectedBlob($table, $data);
        }
    }

    /**
     * Testing plain inserts (BLOB)
     * @group Core
     * @group ArchiveProcessor
     */
    public function testTableInsertBatchIterateBlob()
    {
        $siteTimezone = 'America/Toronto';
        $dateLabel = '2011-03-31';
        $table = Piwik_DataAccess_ArchiveTableCreator::getBlobTable(Piwik_Date::factory($dateLabel));

        $data = $this->_getBlobDataInsert();
        Piwik::tableInsertBatchIterate($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data);
        $this->_checkTableIsExpectedBlob($table, $data);

        // If we insert AGAIN, expect to throw an error because the primary key already exist
        try {
            Piwik::tableInsertBatchIterate($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data, $ignoreWhenDuplicate = false);
        } catch (Exception $e) {
            // However if we insert with keyword REPLACE, then the new data should be saved
            Piwik::tableInsertBatchIterate($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data, $ignoreWhenDuplicate = true);
            $this->_checkTableIsExpectedBlob($table, $data);
            return;
        }
        $this->fail('Exception expected');
    }


    protected function _checkTableIsExpected($table, $data)
    {
        $fetched = Piwik_FetchAll('SELECT * FROM ' . $table);
        foreach ($data as $id => $row) {
            $this->assertEquals($fetched[$id]['idsite'], $data[$id][0], "record $id is not {$data[$id][0]}");
            $this->assertEquals($fetched[$id]['url'], $data[$id][1], "Record $id bug, not {$data[$id][1]} BUT {$fetched[$id]['url']}");
        }
    }

    protected function _checkTableIsExpectedBlob($table, $data)
    {
        $fetched = Piwik_FetchAll('SELECT * FROM ' . $table);
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
    protected function _getDataInsert()
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
    protected function _getBlobDataInsert()
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
}
