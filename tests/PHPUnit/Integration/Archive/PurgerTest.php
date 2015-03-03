<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration\Archive;

use Piwik\Archive\Purger;
use Piwik\Config;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\InvalidatedReports;
use Piwik\Date;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class PurgerTest extends IntegrationTestCase
{
    /* TODO: things to test
     * - that archive purging is done daily (when called through cron archiving and through API request for scheduled task running) [SYSTEM TESTS]
     */

    private static $dummyArchiveData = array(
        // outdated temporary
        array(
            'idarchive' => 1,
            'idsite' => 1,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_OK_TEMPORARY,
            'date1' => '2015-02-03',
            'date2' => '2015-02-03',
            'period' => 1,
            'ts_archived' => '2015-02-03 12:12:12'
        ),

        array(
            'idarchive' => 2,
            'idsite' => 2,
            'name' => 'doneDUMMYHASHSTR',
            'value' => ArchiveWriter::DONE_OK_TEMPORARY,
            'date1' => '2015-02-01',
            'date2' => '2015-02-31',
            'period' => 3,
            'ts_archived' => '2015-02-18 10:10:10'
        ),

        array(
            'idarchive' => 3,
            'idsite' => 3,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_OK_TEMPORARY,
            'date1' => '2015-02-04',
            'date2' => '2015-02-10',
            'period' => 2,
            'ts_archived' => '2015-02-10 12:34:56'
        ),

        array(
            'idarchive' => 4,
            'idsite' => 1,
            'name' => 'doneDUMMYHASHSTR',
            'value' => ArchiveWriter::DONE_OK_TEMPORARY,
            'date1' => '2015-02-15',
            'date2' => '2015-02-15',
            'period' => 1,
            'ts_archived' => '2015-02-15 08:12:13'
        ),


        // valid temporary
        array( // only valid
            'idarchive' => 5,
            'idsite' => 1,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_OK_TEMPORARY,
            'date1' => '2015-02-27',
            'date2' => '2015-02-27',
            'period' => 1,
            'ts_archived' => '2015-02-27 08:08:08'
        ),

        array(
            'idarchive' => 6,
            'idsite' => 2,
            'name' => 'doneDUMMYHASHSTR',
            'value' => ArchiveWriter::DONE_OK_TEMPORARY,
            'date1' => '2015-02-26',
            'date2' => '2015-02-26',
            'period' => 1,
            'ts_archived' => '2015-02-26 07:07:07'
        ),

        array(
            'idarchive' => 7,
            'idsite' => 3,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_OK_TEMPORARY,
            'date1' => '2015-02-01',
            'date2' => '2015-02-28',
            'period' => 3,
            'ts_archived' => '2015-02-15 00:00:00'
        ),

        // custom ranges
        array(
            'idarchive' => 8,
            'idsite' => 1,
            'name' => 'doneDUMMYHASHSTR',
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-03',
            'date2' => '2015-02-14',
            'period' => 5,
            'ts_archived' => '2015-02-27 00:00:00'
        ),

        array(
            'idarchive' => 9,
            'idsite' => 2,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-05',
            'date2' => '2015-02-14',
            'period' => 5,
            'ts_archived' => '2015-02-15 00:00:00'
        ),

        array(
            'idarchive' => 10,
            'idsite' => 3,
            'name' => 'doneDUMMYHASHSTR',
            'value' => ArchiveWriter::DONE_OK_TEMPORARY,
            'date1' => '2015-02-05',
            'date2' => '2015-03-05',
            'period' => 5,
            'ts_archived' => '2015-02-26 00:00:00'
        ),

        // invalidated
        array(
            'idarchive' => 11,
            'idsite' => 1,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_INVALIDATED,
            'date1' => '2015-02-10',
            'date2' => '2015-02-10',
            'period' => 1,
            'ts_archived' => '2015-02-10 12:13:14'
        ),

        array(
            'idarchive' => 12,
            'idsite' => 2,
            'name' => 'doneDUMMYHASHSTR',
            'value' => ArchiveWriter::DONE_INVALIDATED,
            'date1' => '2015-02-08',
            'date2' => '2015-02-14',
            'period' => 2,
            'ts_archived' => '2015-02-15 00:00:00'
        ),

        array(
            'idarchive' => 13,
            'idsite' => 3,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_INVALIDATED,
            'date1' => '2015-02-01',
            'date2' => '2015-02-28',
            'period' => 3,
            'ts_archived' => '2015-02-27 13:13:13'
        ),

        array(
            'idarchive' => 14,
            'idsite' => 1,
            'name' => 'doneDUMMYHASHSTR',
            'value' => ArchiveWriter::DONE_INVALIDATED,
            'date1' => '2015-02-28',
            'date2' => '2015-02-28',
            'period' => 1,
            'ts_archived' => '2015-02-28 12:12:12'
        ),
    );

    /**
     * @var Purger
     */
    private $archivePurger;

    /**
     * @var Date
     */
    private $january;

    /**
     * @var Date
     */
    private $february;

    public function setUp()
    {
        parent::setUp();

        $this->january = Date::factory('2015-01-01');
        $this->february = Date::factory('2015-02-01');

        $this->archivePurger = new Purger();
        $this->archivePurger->setTodayDate(Date::factory('2015-02-27'));
        $this->archivePurger->setYesterdayDate(Date::factory('2015-02-26'));
        $this->archivePurger->setNow(Date::factory('2015-02-27 08:00:00')->getTimestamp());

        $this->insertOutdatedArchives($this->january);
        $this->insertOutdatedArchives($this->february);

        $this->configureCustomRangePurging();
    }

    public function test_purgeOutdatedArchives_PurgesCorrectTemporaryArchives_AndRangeArchives_WhileKeepingNewerTemporaryArchives_WithBrowserTriggeringEnabled()
    {
        $this->enableBrowserTriggeredArchiving();

        $this->archivePurger->purgeOutdatedArchives($this->february);

        $this->assertFebruaryTemporaryArchivesPurged($browserTriggeringEnabled = true);
        $this->assertFebruaryCustomRangesPurged();

        $this->assertJanuaryTemporaryArchivesNotPurged();
    }

    public function test_purgeOutdatedArchives_PurgesCorrectTemporaryArchives_AndRangeArchives_WhileKeepingNewerTemporaryArchives_WithBrowserTriggeringDisabled()
    {
        $this->disableBrowserTriggeredArchiving();

        $this->archivePurger->purgeOutdatedArchives($this->february);

        $this->assertFebruaryTemporaryArchivesPurged($browserTriggeringEnabled = false);
        $this->assertFebruaryCustomRangesPurged();

        $this->assertJanuaryTemporaryArchivesNotPurged();
    }

    public function test_purgeInvalidatedArchives_PurgesCorrectInvalidatedArchives_AndOnlyPurgesDataForDatesAndSites_InInvalidatedReportsDistributedList()
    {
        $this->setUpInvalidatedReportsDistributedList($dates = array($this->february), $sites = array(1, 3));

        $this->archivePurger->purgeInvalidatedArchives();

        // check invalidated archives for idsite = 1, idsite = 3 are purged
        $expectedPurgedArchives = array(11, 13);
        $this->assertArchivesDoNotExist($expectedPurgedArchives, $this->february);

        $expectedPresentArchives = array(12, 14);
        $this->assertArchivesExist($expectedPresentArchives, $this->february);

        $this->assertJanuaryInvalidatedArchivesNotPurged();
    }

    public function test_purgeInvalidatedArchivesFrom_PurgesCorrectInvalidatedArchives_AndMarksDatesAndSitesAsInvalidated()
    {
        $this->setUpInvalidatedReportsDistributedList($dates = array($this->february), $sites = array(1, 2, 3));

        $this->archivePurger->purgeInvalidatedArchivesFrom("2015_01", array(1,2));

        // check invalidated archives for idsite = 1, idsite = 2 are purged
        $expectedPurgedArchives = array(11, 13, 14);
        $this->assertArchivesDoNotExist($expectedPurgedArchives, $this->february);

        $expectedPresentArchives = array(12);
        $this->assertArchivesExist($expectedPresentArchives, $this->february);

        $this->assertJanuaryInvalidatedArchivesNotPurged();

        // assert invalidated reports distributed list has changed
        $invalidatedReports = new InvalidatedReports();
        $sitesByYearMonth = $invalidatedReports->getSitesByYearMonthArchiveToPurge();

        $this->assertEquals(array(
            '2015_02' => array(3)
        ), $sitesByYearMonth);
    }

    private function configureCustomRangePurging()
    {
        Config::getInstance()->General['purge_date_range_archives_after_X_days'] = 3;
    }

    private function enableBrowserTriggeredArchiving()
    {
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;
    }

    private function disableBrowserTriggeredArchiving()
    {
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
    }

    /**
     * @param Date[] $dates
     * @param int[] $sites
     */
    private function setUpInvalidatedReportsDistributedList($dates, $sites)
    {
        $yearMonths = array();
        foreach ($dates as $date) {
            $yearMonths[] = $date->toString('Y_m');
        }

        $invalidatedReports = new InvalidatedReports();
        $invalidatedReports->addSitesToPurgeForYearMonths($sites, $yearMonths);
    }

    private function insertOutdatedArchives(Date $archiveDate)
    {
        $dummyArchiveData = $this->getDummyArchiveDataForDate($archiveDate);

        $numericTable = ArchiveTableCreator::getNumericTable($archiveDate);
        foreach ($dummyArchiveData as $row) {
            // done row
            $this->insertTestArchiveRow($numericTable, $row);

            // two metrics
            $row['name'] = 'nb_visits';
            $row['value'] = 1;
            $this->insertTestArchiveRow($numericTable, $row);

            $row['name'] = 'nb_actions';
            $row['value'] = 2;
            $this->insertTestArchiveRow($numericTable, $row);
        }

        $blobTable = ArchiveTableCreator::getBlobTable($archiveDate);
        foreach ($dummyArchiveData as $row) {
            // two blobs
            $row['name'] = 'blobname';
            $row['value'] = 'dummyvalue';
            $this->insertTestArchiveRow($blobTable, $row);

            $row['name'] = 'blobname2';
            $row['value'] = 'dummyvalue';
            $this->insertTestArchiveRow($blobTable, $row);
        }
    }

    private function insertTestArchiveRow($table, $row)
    {
        $insertSqlTemplate = "INSERT INTO %s (idarchive, idsite, name, value, date1, date2, period, ts_archived) VALUES ('%s')";

        Db::exec(sprintf($insertSqlTemplate, $table, implode("','", $row)));
    }

    private function getDummyArchiveDataForDate($archiveDate)
    {
        $rows = self::$dummyArchiveData;
        foreach ($rows as &$row) {
            $row['date1'] = $this->setDateMonthAndYear($row['date1'], $archiveDate);
            $row['date2'] = $this->setDateMonthAndYear($row['date1'], $archiveDate);
        }
        return$rows;
    }

    private function setDateMonthAndYear($dateString, Date $archiveDate)
    {
        return $archiveDate->toString('Y-m') . '-' . Date::factory($dateString)->toString('d');
    }

    private function assertFebruaryTemporaryArchivesPurged($isBrowserTriggeredArchivingEnabled)
    {
        if ($isBrowserTriggeredArchivingEnabled) {
            $expectedPurgedArchives = array(1,2,3,4,6,7);
        } else {
            $expectedPurgedArchives = array(1,2,3,4,5,6,7);
        }

        $this->assertArchivesDoNotExist($expectedPurgedArchives, $this->february);
    }

    private function assertFebruaryCustomRangesPurged()
    {
        $expectedPurgedArchives = array(8,9,10);
        $this->assertArchivesDoNotExist($expectedPurgedArchives, $this->february);
    }

    private function assertJanuaryTemporaryArchivesNotPurged()
    {
        $expectedPresentArchives = array(1,2,3,4,5,6,7);
        $this->assertArchivesExist($expectedPresentArchives, $this->january);
    }

    private function assertJanuaryInvalidatedArchivesNotPurged()
    {
        $expectedPresentArchives = array(11, 12, 13, 14);
        $this->assertArchivesExist($expectedPresentArchives, $this->january);
    }

    private function assertArchivesDoNotExist($expectedPurgedArchiveIds, $archiveDate)
    {
        $numericTable = ArchiveTableCreator::getNumericTable($archiveDate);
        $blobTable = ArchiveTableCreator::getBlobTable($archiveDate);

        $numericPurgedArchiveCount = $this->getArchiveRowCountWithId($numericTable, $expectedPurgedArchiveIds);
        $this->assertEquals(0, $numericPurgedArchiveCount);

        $blobPurgedArchiveCount = $this->getArchiveRowCountWithId($blobTable, $expectedPurgedArchiveIds);
        $this->assertEquals(0, $blobPurgedArchiveCount);
    }

    private function assertArchivesExist($expectedPresentArchiveIds, $archiveDate)
    {
        $numericTable = ArchiveTableCreator::getNumericTable($archiveDate);
        $blobTable = ArchiveTableCreator::getBlobTable($archiveDate);

        $numericArchiveCount = $this->getArchiveRowCountWithId($numericTable, $expectedPresentArchiveIds);
        $expectedNumericRowCount = count($expectedPresentArchiveIds) * 3; // two metrics + 1 done row
        $this->assertEquals($expectedNumericRowCount, $numericArchiveCount);

        $blobArchiveCount = $this->getArchiveRowCountWithId($blobTable, $expectedPresentArchiveIds);
        $expectedBlobRowCount = count($expectedPresentArchiveIds) * 2; // two blob rows
        $this->assertEquals($expectedBlobRowCount, $blobArchiveCount);
    }

    private function getArchiveRowCountWithId($table, $archiveIds)
    {
        return Db::fetchOne("SELECT COUNT(*) FROM $table WHERE idarchive IN (".implode(',', $archiveIds).")");
    }
}
