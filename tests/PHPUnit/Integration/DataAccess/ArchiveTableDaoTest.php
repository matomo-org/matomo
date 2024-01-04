<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\Common;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveTableDao;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class ArchiveTableDaoTest extends IntegrationTestCase
{
    /**
     * @var ArchiveTableDao
     */
    private $archiveTableDao;

    public function setUp(): void
    {
        parent::setUp();

        $this->archiveTableDao = self::$fixture->piwikEnvironment->getContainer()->get(
            'Piwik\DataAccess\ArchiveTableDao');

        ArchiveTableCreator::getBlobTable(Date::factory('2015-01-01'));
        ArchiveTableCreator::getNumericTable(Date::factory('2015-01-01'));
    }

    /**
     *
     */
    public function test_getArchiveTableAnalysis_QueriesNumericAndBlobTable_IncludingArchivesInBlobThatAreNotInNumeric()
    {
        $tableMonth = '2015_01';

        $this->insertArchive($tableMonth, $idSite = 1, $period = 'day', $date1 = '2015-01-01', $date2 = '2015-01-01');
        $this->insertArchive($tableMonth, $idSite = 2, $period = 'day', $date1 = '2015-01-03', $date2 = '2015-01-03');
        $this->insertArchive($tableMonth, $idSite = 1, $period = 'week', $date1 = '2015-01-04', $date2 = '2015-01-11');
        $this->insertArchive($tableMonth, $idSite = 3, $period = 'month', $date1 = '2015-01-01', $date2 = '2015-01-31');
        $this->insertArchive($tableMonth, $idSite = 4, $period = 'year', $date1 = '2015-01-01', $date2 = '2015-12-31',
            $segment = 'browserCode==FF');
        $this->insertArchive($tableMonth, $idSite = 1, $period = 'range', $date1 = '2015-01-15', $date2 = '2015-01-20');

        // invalid
        $this->insertArchive($tableMonth, $idSite = 1, $period = 'day', $date1 = '2015-01-01', $date2 = '2015-01-01',
            $segment = false, $doneValue = ArchiveWriter::DONE_INVALIDATED);
        $this->insertArchive($tableMonth, $idSite = 1, $period = 'day', $date1 = '2015-01-01', $date2 = '2015-01-01',
            $segment = false, $doneValue = ArchiveWriter::DONE_INVALIDATED);
        $this->insertArchive($tableMonth, $idSite = 4, $period = 'year', $date1 = '2015-01-01', $date2 = '2015-12-31',
            $segment = 'browserCode==FF', $doneValue = ArchiveWriter::DONE_INVALIDATED);

        // temporary
        $this->insertArchive($tableMonth, $idSite = 1, $period = 'week', $date1 = '2015-01-04', $date2 = '2015-01-11',
            $segment = false, $doneValue = ArchiveWriter::DONE_OK_TEMPORARY);
        $this->insertArchive($tableMonth, $idSite = 3, $period = 'month', $date1 = '2015-01-01', $date2 = '2015-01-31',
            $segment = 'daysSinceFirstVisit==1', $doneValue = ArchiveWriter::DONE_OK_TEMPORARY);

        // error
        $this->insertArchive($tableMonth, $idSite = 1, $period = 'week', $date1 = '2015-01-04', $date2 = '2015-01-11',
            $segment = false, $doneValue = ArchiveWriter::DONE_ERROR);
        $this->insertArchive($tableMonth, $idSite = 3, $period = 'month', $date1 = '2015-01-01', $date2 = '2015-01-31',
            $segment = 'daysSinceFirstVisit==1', $doneValue = ArchiveWriter::DONE_ERROR);

        // blob only
        $this->insertBlobArchive($tableMonth, $idSite = 1, $period = 'day', $date1 = '2015-01-20',
            $date2 = '2015-01-20');
        $this->insertBlobArchive($tableMonth, $idSite = 2, $period = 'day', $date1 = '2015-01-21',
            $date2 = '2015-01-21', $segment = 'browserCode==SF');

        $expectedStats = array(
            '1.2015-01-01.2015-01-01.1' => array(
                'label' => '1.2015-01-01.2015-01-01.1',
                'count_archives' => '3',
                'count_invalidated_archives' => '2',
                'count_temporary_archives' => '0',
                'count_error_archives' => '0',
                'count_segment_archives' => '0',
                'count_numeric_rows' => '9',
                'count_blob_rows' => '9',
                'sum_blob_length' => '108',
            ),
            '1.2015-01-04.2015-01-11.2' => array(
                'label' => '1.2015-01-04.2015-01-11.2',
                'count_archives' => '3',
                'count_invalidated_archives' => '0',
                'count_temporary_archives' => '1',
                'count_error_archives' => '1',
                'count_segment_archives' => '0',
                'count_numeric_rows' => '9',
                'count_blob_rows' => '9',
                'sum_blob_length' => '108',
            ),
            '1.2015-01-15.2015-01-20.5' => array(
                'label' => '1.2015-01-15.2015-01-20.5',
                'count_archives' => '1',
                'count_invalidated_archives' => '0',
                'count_temporary_archives' => '0',
                'count_error_archives' => '0',
                'count_segment_archives' => '0',
                'count_numeric_rows' => '3',
                'count_blob_rows' => '3',
                'sum_blob_length' => '36',
            ),
            '2.2015-01-03.2015-01-03.1' => array(
                'label' => '2.2015-01-03.2015-01-03.1',
                'count_archives' => '1',
                'count_invalidated_archives' => '0',
                'count_temporary_archives' => '0',
                'count_error_archives' => '0',
                'count_segment_archives' => '0',
                'count_numeric_rows' => '3',
                'count_blob_rows' => '3',
                'sum_blob_length' => '36',
            ),
            '3.2015-01-01.2015-01-31.3' => array(
                'label' => '3.2015-01-01.2015-01-31.3',
                'count_archives' => '3',
                'count_invalidated_archives' => '0',
                'count_temporary_archives' => '1',
                'count_error_archives' => '1',
                'count_segment_archives' => '2',
                'count_numeric_rows' => '9',
                'count_blob_rows' => '9',
                'sum_blob_length' => '108',
            ),
            '4.2015-01-01.2015-12-31.4' => array(
                'label' => '4.2015-01-01.2015-12-31.4',
                'count_archives' => '2',
                'count_invalidated_archives' => '1',
                'count_temporary_archives' => '0',
                'count_error_archives' => '0',
                'count_segment_archives' => '2',
                'count_numeric_rows' => '6',
                'count_blob_rows' => '6',
                'sum_blob_length' => '72',
            ),
            '1.2015-01-20.2015-01-20.1' => array(
                'label' => '1.2015-01-20.2015-01-20.1',
                'count_blob_rows' => '3',
                'count_archives' => '-',
                'count_invalidated_archives' => '-',
                'count_temporary_archives' => '-',
                'count_error_archives' => '-',
                'count_segment_archives' => '-',
                'count_numeric_rows' => '-',
                'sum_blob_length' => '36',
            ),
            '2.2015-01-21.2015-01-21.1' => array(
                'label' => '2.2015-01-21.2015-01-21.1',
                'count_blob_rows' => '3',
                'count_archives' => '-',
                'count_invalidated_archives' => '-',
                'count_temporary_archives' => '-',
                'count_error_archives' => '-',
                'count_segment_archives' => '-',
                'count_numeric_rows' => '-',
                'sum_blob_length' => '36',
            ),
        );

        $actualStats = $this->archiveTableDao->getArchiveTableAnalysis($tableMonth);

        $this->assertEquals($expectedStats, $actualStats);
    }

    private function insertArchive($tableMonth, $idSite, $period, $date1, $date2, $segment = false,
                                   $doneValue = ArchiveWriter::DONE_OK)
    {
        $this->insertNumericArchive($tableMonth, $idSite, $period, $date1, $date2, $segment, $doneValue);
        $this->insertBlobArchive($tableMonth, $idSite, $period, $date1, $date2, $segment);
    }

    private function insertNumericArchive($tableMonth, $idSite, $period, $date1, $date2, $segment, $doneValue)
    {
        $this->insertRow('archive_numeric', $tableMonth, $idSite, $period, $date1, $date2, 'nb_schweetz', 2);
        $this->insertRow('archive_numeric', $tableMonth, $idSite, $period, $date1, $date2, 'nb_fixes', 3);
        $this->insertRow('archive_numeric', $tableMonth, $idSite, $period, $date1, $date2, 'nb_wrecks', 4);

        $doneFlag = 'done';
        if (!empty($segment)) {
            $segmentObj = new Segment($segment, array());
            $doneFlag .= $segmentObj->getHash();
        }

        $this->insertRow('archive_numeric', $tableMonth, $idSite, $period, $date1, $date2, $doneFlag, $doneValue);
    }

    private function insertBlobArchive($tableMonth, $idSite, $period, $date1, $date2, $segment = false)
    {
        $this->insertRow('archive_blob', $tableMonth, $idSite, $period, $date1, $date2, 'nb_cybugz', 'blob value 1');
        $this->insertRow('archive_blob', $tableMonth, $idSite, $period, $date1, $date2, 'max_turbo', 'blob value 2');
        $this->insertRow('archive_blob', $tableMonth, $idSite, $period, $date1, $date2, 'nb_fps', 'blob value 3');
    }

    private function insertRow($type, $tableMonth, $idSite, $period, $date1, $date2, $name, $value)
    {
        $table = Common::prefixTable($type . '_' . $tableMonth);

        $idArchive = (int)Db::fetchOne("SELECT MAX(idarchive) FROM $table") + 1;

        $sql = "INSERT INTO $table (idarchive, name, idsite, date1, date2, period, ts_archived, value)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $bind = array($idArchive, $name, $idSite, $date1, $date2, Piwik::$idPeriods[$period], date('Y-m-d'), $value);

        Db::query($sql, $bind);
    }
}
