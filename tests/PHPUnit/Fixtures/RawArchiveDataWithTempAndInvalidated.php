<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;

/**
 * Fixture that inserts rows into archive tables for Jan. 2015 + Feb. 2015. The rows include
 * done rows + metrics/blobs, and the done rows have values of DONE_OK_TEMPORARY,
 * DONE_OK, DONE_INVALIDATED. There are also some custom range archives.
 *
 * This class is used to test archive purging.
 */
class RawArchiveDataWithTempAndInvalidated extends Fixture
{
    const ROWS_PER_ARCHIVE = 5;

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

        array(
            'idarchive' => 15,
            'idsite' => 1,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_INVALIDATED,
            'date1' => '2015-02-27',
            'date2' => '2015-02-27',
            'period' => 1,
            'ts_archived' => '2015-02-28 12:12:12'
        ),

        // reprocessed invalidated
        array(
            'idarchive' => 16,
            'idsite' => 1,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-10',
            'date2' => '2015-02-10',
            'period' => 1,
            'ts_archived' => '2015-02-11 12:13:14'
        ),

        array(
            'idarchive' => 17,
            'idsite' => 2,
            'name' => 'doneDUMMYHASHSTR',
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-08',
            'date2' => '2015-02-14',
            'period' => 2,
            'ts_archived' => '2015-02-15 14:00:00'
        ),
        array(
            'idarchive' => 18,
            'idsite' => 2,
            'name' => 'doneDUMMYHASHSTR',
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-08',
            'date2' => '2015-02-14',
            'period' => 2,
            'ts_archived' => '2015-02-16 00:00:00'
        ),

        array(
            'idarchive' => 19,
            'idsite' => 3,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-01',
            'date2' => '2015-02-28',
            'period' => 3,
            'ts_archived' => '2015-02-28 13:13:13'
        ),

        array(
            'idarchive' => 20,
            'idsite' => 1,
            'name' => 'doneDUMMYHASHSTR',
            'value' => ArchiveWriter::DONE_OK_TEMPORARY,
            'date1' => '2015-02-28',
            'date2' => '2015-02-28',
            'period' => 1,
            'ts_archived' => '2015-02-28 16:12:12' // must be late so it doesn't screw up the purgeOutdatedArchives test
        ),

        // newer done_ok
        array(
            'idarchive' => 21,
            'idsite' => 1,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-10',
            'date2' => '2015-02-10',
            'period' => 1,
            'ts_archived' => '2015-02-09 14:13:14'
        ),

        // old done partial (should be purged)
        array(
            'idarchive' => 22,
            'idsite' => 1,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_PARTIAL,
            'date1' => '2015-02-10',
            'date2' => '2015-02-10',
            'period' => 1,
            'ts_archived' => '2015-02-11 10:13:14'
        ),

        // new done partial (should not be purged)
        array(
            'idarchive' => 23,
            'idsite' => 1,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_PARTIAL,
            'date1' => '2015-02-10',
            'date2' => '2015-02-10',
            'period' => 1,
            'ts_archived' => '2015-02-11 16:13:14'
        ),
    );


    private static $segmentArchiveData = array(
        array(
            'idarchive' => 24,
            'idsite' => 1,
            'name' => 'doneeb5d2797aedd15d819b1a20425982850',   // Raw segment = abcd1234abcd5678
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-03',
            'date2' => '2015-02-03',
            'period' => 1,
            'ts_archived' => '2015-02-03 12:12:12'
        ),
        array(
            'idarchive' => 25,
            'idsite' => 1,
            'name' => 'doneeb5d2797aedd15d819b1a20425982850.MyPlugin',   // Raw segment = abcd1234abcd5678
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-03',
            'date2' => '2015-02-03',
            'period' => 1,
            'ts_archived' => '2015-02-03 12:12:12'
        ),
        array(
            'idarchive' => 26,
            'idsite' => 2,
            'name' => 'doneeb5d2797aedd15d819b1a20425982850',   // Raw segment = abcd1234abcd5678
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-03',
            'date2' => '2015-02-03',
            'period' => 1,
            'ts_archived' => '2015-02-03 12:12:12'
        ),
        array(
            'idarchive' => 27,
            'idsite' => 2,
            'name' => 'doneeb5d2797aedd15d819b1a20425982850.MyPlugin',   // Raw segment = abcd1234abcd5678
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-03',
            'date2' => '2015-02-03',
            'period' => 1,
            'ts_archived' => '2015-02-03 12:12:12'
        ),
        array(
            'idarchive' => 28,
            'idsite' => 1,
            'name' => 'done1e39a89fcc269acc36bd4d7c742763ed',   // Raw segment = 9876fedc5432abcd
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-03',
            'date2' => '2015-02-03',
            'period' => 1,
            'ts_archived' => '2015-02-03 12:12:12'
        ),
        array(
            'idarchive' => 29,
            'idsite' => 2,
            'name' => 'done00c6ee2e21a7548de6260cf72c4f4b5b',   // Raw segment = hash1
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-03',
            'date2' => '2015-02-03',
            'period' => 1,
            'ts_archived' => '2015-02-03 12:12:12'
        ),
        array(
            'idarchive' => 30,
            'idsite' => 2,
            'name' => 'done58833651db311ba4bc11cb26b1900b0f',   // Raw segment = hash2
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-03',
            'date2' => '2015-02-03',
            'period' => 1,
            'ts_archived' => '2015-02-03 12:12:12'
        ),
        array(
            'idarchive' => 31,
            'idsite' => 2,
            'name' => 'done58833651db311ba4bc11cb26b1900b0f.MyPlugin',  // Raw segment = hash2
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-03',
            'date2' => '2015-02-03',
            'period' => 1,
            'ts_archived' => '2015-02-03 12:12:12'
        ),
        array(
            'idarchive' => 32,
            'idsite' => 2,
            'name' => 'done1a4ead8b39d17dfe89418452c9bba770',   // Raw segment = hash3
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-03',
            'date2' => '2015-02-03',
            'period' => 1,
            'ts_archived' => '2015-02-03 12:12:12'
        ),
        array(
            'idarchive' => 33,
            'idsite' => 2,
            'name' => 'done1a4ead8b39d17dfe89418452c9bba770',   // Raw segment = hash3
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-03',
            'date2' => '2015-02-03',
            'period' => 1,
            'ts_archived' => '2015-02-27 12:12:12'
        ),
    );

    public static $dummyArchiveDataNoInvalidated = [
        // two archives w/ DONE_OK for a new site (no invalidated archives for site)
        [
            'idarchive' => 34,
            'idsite' => 4,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-11',
            'date2' => '2015-02-11',
            'period' => 1,
            'ts_archived' => '2015-02-27 10:12:12'
        ],
        [
            'idarchive' => 35,
            'idsite' => 4,
            'name' => 'done',
            'value' => ArchiveWriter::DONE_OK,
            'date1' => '2015-02-11',
            'date2' => '2015-02-11',
            'period' => 1,
            'ts_archived' => '2015-02-27 12:12:12'
        ],
    ];

    /**
     * @var Date
     */
    public $january;

    /**
     * @var Date
     */
    public $february;

    public function setUp(): void
    {
        parent::setUp();

        $this->january = Date::factory('2015-01-01');
        $this->february = Date::factory('2015-02-01');

        $this->insertOutdatedArchives($this->january);
        $this->insertOutdatedArchives($this->february);
    }

    public function insertSegmentArchives(Date $archiveDate)
    {
        $dummyArchiveData = $this->setDatesOnArchiveData($archiveDate, self::$segmentArchiveData);
        $this->insertArchiveRows($archiveDate, $dummyArchiveData);
    }

    private function insertOutdatedArchives(Date $archiveDate)
    {
        $dummyArchiveData = $this->setDatesOnArchiveData($archiveDate, self::$dummyArchiveData);
        $this->insertArchiveRows($archiveDate, $dummyArchiveData);

        $dummySiteNoInvalidated = $this->setDatesOnArchiveData($archiveDate, self::$dummyArchiveDataNoInvalidated);
        $this->insertArchiveRows($archiveDate, $dummySiteNoInvalidated);
    }

    private function insertArchiveRows(Date $archiveDate, array $dummyArchiveData)
    {
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

    private function setDatesOnArchiveData($archiveDate, $data)
    {
        foreach ($data as &$row) {
            $row['date1'] = $this->setDateMonthAndYear($row['date1'], $archiveDate);
            $row['date2'] = $this->setDateMonthAndYear($row['date1'], $archiveDate);
        }
        return $data;
    }

    private function setDateMonthAndYear($dateString, Date $archiveDate)
    {
        return $archiveDate->toString('Y-m') . '-' . Date::factory($dateString)->toString('d');
    }

    public function assertTemporaryArchivesPurged($isBrowserTriggeredArchivingEnabled, Date $date)
    {
        if ($isBrowserTriggeredArchivingEnabled) {
            $expectedPurgedArchives = array(1,2,3,4,6,7,10); // only archives from 2 hours before "now" are purged
        } else {
            $expectedPurgedArchives = array(1,2,3,4,7); // only archives before start of "yesterday" are purged
        }

        $this->assertArchivesDoNotExist($expectedPurgedArchives, $date);
    }

    public function assertCustomRangesPurged(Date $date)
    {
        $expectedPurgedArchives = array(8,9,10);
        $this->assertArchivesDoNotExist($expectedPurgedArchives, $date);
    }

    public function assertTemporaryArchivesNotPurged(Date $date)
    {
        $expectedPresentArchives = array(1,2,3,4,5,6,7);
        $this->assertArchivesExist($expectedPresentArchives, $date);
    }

    public function assertInvalidatedArchivesNotPurged(Date $date)
    {
        $expectedPresentArchives = array(11, 12, 13, 14);
        $this->assertArchivesExist($expectedPresentArchives, $date);
    }

    public function assertCustomRangesNotPurged(Date $date, $includeTemporary = true)
    {
        $expectedPresentArchives = array(8, 9);
        if ($includeTemporary) {
            $expectedPresentArchives[] = 10;
        }
        $this->assertArchivesExist($expectedPresentArchives, $date);
    }

    public function assertArchivesDoNotExist($expectedPurgedArchiveIds, $archiveDate)
    {
        $numericTable = ArchiveTableCreator::getNumericTable($archiveDate);
        $blobTable = ArchiveTableCreator::getBlobTable($archiveDate);

        $numericPurgedArchiveCount = $this->getArchiveRowCountWithId($numericTable, $expectedPurgedArchiveIds);
        $this->assertEquals(0, $numericPurgedArchiveCount);

        $blobPurgedArchiveCount = $this->getArchiveRowCountWithId($blobTable, $expectedPurgedArchiveIds);
        $this->assertEquals(0, $blobPurgedArchiveCount);
    }

    public function assertArchivesExist($expectedPresentArchiveIds, $archiveDate)
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
        return Db::fetchOne("SELECT COUNT(*) FROM $table WHERE idarchive IN (" . implode(',', $archiveIds) . ")");
    }

    public function assertInvalidatedArchivesPurged(Date $date)
    {
        // check invalidated archives for all sites are purged
        $expectedPurgedArchives = array(11, 12, 13, 14);
        $this->assertArchivesDoNotExist($expectedPurgedArchives, $date);

        // check archive 15 is not purged since it doesn't have newer DONE_OK/DONE_TEMPORARY archive
        $expectedExistingArchives = array(15);
        $this->assertArchivesExist($expectedExistingArchives, $date);
    }

    public function assertPartialArchivesPurged(Date $date)
    {
        $expectedPurgedArchives = [22];
        $this->assertArchivesDoNotExist($expectedPurgedArchives, $date);

        $expectedExistingArchives = [23];
        $this->assertArchivesExist($expectedExistingArchives, $date);
    }
}
