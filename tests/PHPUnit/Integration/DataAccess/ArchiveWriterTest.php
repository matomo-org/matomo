<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\Access;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\Common;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period\Day;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Segment;
use Piwik\Sequence;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class TestArchiveWriter extends ArchiveWriter
{
    public function flushSpools()
    {
        parent::flushSpools();
    }
}

/**
 * @group ArchiveWriterTest
 * @group Archive
 * @group Core
 */
class ArchiveWriterTest extends IntegrationTestCase
{
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        Access::getInstance()->setSuperUserAccess(true);
        $this->idSite = Fixture::createWebsite('2019-08-29');
    }

    public function testFinalizeArchiveRemovesOldArchivesIfNotPartial()
    {
        Date::$now = strtotime('2020-04-05 03:00:00');

        $period = 'day';
        $date = '2019-08-29';

        $initialArchiveData = [
            ['idarchive' => 1, 'idsite' => $this->idSite, 'date1' => '2019-08-29', 'date2' => '2019-08-29', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_PARTIAL, 'ts_archived' => '2020-02-02 03:44:44'],
            ['idarchive' => 2, 'idsite' => $this->idSite, 'date1' => '2019-08-29', 'date2' => '2019-08-29', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_OK, 'ts_archived' => '2020-02-04 03:44:44'],
        ];

        $this->insertArchiveData($initialArchiveData);

        $writer = $this->buildWriter($period, $date);
        $writer->initNewArchive();
        $writer->insertRecord('nb_visits', 5);

        $this->assertEquals($initialArchiveData, $this->getAllColsOfAllNumericRows($date));

        $writer->finalizeArchive();

        $expected = [
            ['idarchive' => 3, 'idsite' => 1, 'date1' => '2019-08-29', 'date2' => '2019-08-29', 'period' => 1, 'name' => 'done', 'value' => 1, 'ts_archived' => '2020-04-05 03:00:00'],
            ['idarchive' => 3, 'idsite' => 1, 'date1' => '2019-08-29', 'date2' => '2019-08-29', 'period' => 1, 'name' => 'nb_visits', 'value' => 5, 'ts_archived' => '2020-04-05 03:00:00'],
        ];
        $this->assertEquals($expected, $this->getAllColsOfAllNumericRows($date));
    }

    public function testFinalizeArchiveDoesNotRemoveOldArchivesIfPartial()
    {
        Date::$now = strtotime('2020-04-05 03:00:00');

        $period = 'day';
        $date = '2019-08-29';

        $initialArchiveData = [
            ['idarchive' => 1, 'idsite' => $this->idSite, 'date1' => '2019-08-29', 'date2' => '2019-08-29', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_OK, 'ts_archived' => '2020-02-02 03:44:44'],
            ['idarchive' => 2, 'idsite' => $this->idSite, 'date1' => '2019-08-29', 'date2' => '2019-08-29', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_PARTIAL, 'ts_archived' => '2020-02-04 03:44:44'],
        ];

        $this->insertArchiveData($initialArchiveData);

        $writer = $this->buildWriter($period, $date, $isPartial = true);
        $writer->initNewArchive();
        $writer->insertRecord('nb_visits', 5);

        $this->assertEquals($initialArchiveData, $this->getAllColsOfAllNumericRows($date));

        $writer->finalizeArchive();

        $expected = [
            ['idarchive' => 1, 'idsite' => $this->idSite, 'date1' => '2019-08-29', 'date2' => '2019-08-29', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_OK, 'ts_archived' => '2020-02-02 03:44:44'],
            ['idarchive' => 2, 'idsite' => $this->idSite, 'date1' => '2019-08-29', 'date2' => '2019-08-29', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_PARTIAL, 'ts_archived' => '2020-02-04 03:44:44'],
            ['idarchive' => 3, 'idsite' => 1, 'date1' => '2019-08-29', 'date2' => '2019-08-29', 'period' => 1, 'name' => 'done', 'value' => 5, 'ts_archived' => '2020-04-05 03:00:00'],
            ['idarchive' => 3, 'idsite' => 1, 'date1' => '2019-08-29', 'date2' => '2019-08-29', 'period' => 1, 'name' => 'nb_visits', 'value' => 5, 'ts_archived' => '2020-04-05 03:00:00'],
        ];
        $this->assertEquals($expected, $this->getAllColsOfAllNumericRows($date));
    }

    public function testFinaliseArchiveWritesArchiveStatusToFile()
    {
        $period = 'day';
        $date = '2019-08-29';

        $writer = $this->buildWriter($period, $date);
        $writer->initNewArchive();
        $writer->finalizeArchive();

        $this->assertNumericArchiveExists(Day::PERIOD_ID, $date, 'done', ArchiveWriter::DONE_OK);
    }

    public function testInitNewArchiveDoesNotWiteNewArchiveStatusToFileRightAway()
    {
        $period = 'day';
        $date = '2019-08-29';

        $writer = $this->buildWriter($period, $date);
        $writer->initNewArchive();

        $this->assertEquals(array(), $this->getAllNumericRows($date));

        // now we flush and it should be written
        $writer->flushSpools();
        $this->assertCount(1, $this->getAllNumericRows($date));
        $this->assertNumericArchiveExists(Day::PERIOD_ID, $date, 'done', ArchiveWriter::DONE_ERROR);
    }

    public function testInsertRecordNotFlushedUntilFinaliseCalled()
    {
        $period = 'day';
        $date = '2019-08-29';
        $fieldName = 'MyPlugin.myDataField';

        $writer = $this->buildWriter($period, $date);
        $writer->initNewArchive();
        $writer->insertRecord($fieldName, 29);

        $this->assertNumericArchiveNotExists(Day::PERIOD_ID, $date, $fieldName);

        $writer->finalizeArchive();
        $this->assertNumericArchiveExists(Day::PERIOD_ID, $date, $fieldName, 29);
    }

    public function testInsertRecordNumericAndBlobRecords()
    {
        $period = 'day';
        $date = '2019-08-29';
        $numericFieldName = 'MyPlugin.myDataField';
        $blobFieldName = 'MyPlugin.myDataTable';

        $writer = $this->buildWriter($period, $date);
        $writer->initNewArchive();
        $writer->insertRecord($numericFieldName, 42);
        $writer->insertRecord($blobFieldName, 'here is a blob');

        $writer->finalizeArchive();
        $this->assertNumericArchiveExists(Day::PERIOD_ID, $date, $numericFieldName, 42);
        $this->assertBlobArchiveNotExists(Day::PERIOD_ID, $date, $numericFieldName);
        $this->assertBlobArchiveExists(Day::PERIOD_ID, $date, $blobFieldName, 'here is a blob');
        $this->assertNumericArchiveNotExists(Day::PERIOD_ID, $date, $blobFieldName);
    }

    public function testInsertRecordMultipleCallsBeforeFlushing()
    {
        $period = 'day';
        $date = '2019-08-29';
        $fields = array(
            'MyPlugin.field1' => 3,
            'MyPlugin.field2' => '3983',
            'MyPlugin.field3' => 0.235
        );

        $writer = $this->buildWriter($period, $date);
        $writer->initNewArchive();
        foreach ($fields as $name => $value) {
            $writer->insertRecord($name, $value);
        }

        foreach ($fields as $name => $value) {
            $this->assertNumericArchiveNotExists(Day::PERIOD_ID, $date, $name);
        }

        $writer->finalizeArchive();

        foreach ($fields as $name => $value) {
            $this->assertNumericArchiveExists(Day::PERIOD_ID, $date, $name, $value);
        }
    }

    public function testInsertRecordFlushesAfterFiftyNumericRecords()
    {
        $period = 'day';
        $date = '2019-08-29';

        // 51 records => all should be written except last one. Skip 0 as empty values do not get added to spool
        $fields = array();
        for ($i = 1; $i <= 51; $i++) {
            $fields['MyPlugin.field' . $i] = $i;
        }

        $writer = $this->buildWriter($period, $date);
        $writer->initNewArchive();
        foreach ($fields as $name => $value) {
            $writer->insertRecord($name, $value);
            if ($value <= 48) {
                $this->assertNumericArchiveNotExists(Day::PERIOD_ID, $date, $name);
            }
        }

        foreach ($fields as $name => $value) {
            if ($value <= 49) {
                $this->assertNumericArchiveExists(Day::PERIOD_ID, $date, $name, $value);
            } else {
                $this->assertNumericArchiveNotExists(Day::PERIOD_ID, $date, $name);
            }
        }
    }

    private function buildWriter($period, $date, $isPartial = false, $segment = '')
    {
        $oPeriod = PeriodFactory::makePeriodFromQueryParams('UTC', $period, $date);
        $segment = new Segment($segment, []);
        $params  = new Parameters(new Site($this->idSite), $oPeriod, $segment);
        if ($isPartial) {
            $params->setRequestedPlugin('ExamplePlugin');
            $params->setIsPartialArchive(true);
        }
        $writer  = new TestArchiveWriter($params);
        return $writer;
    }

    private function assertNumericArchiveExists($periodId, $date, $name, $expected)
    {
        $row = $this->getRowFromArchive($periodId, $date, $name);
        $this->assertNotEmpty($row);
        $this->assertEquals($expected, $row['value']);
    }

    private function assertBlobArchiveExists($periodId, $date, $name, $expected)
    {
        $row = $this->getRowFromArchive($periodId, $date, $name, false);
        $this->assertNotEmpty($row);
        $this->assertEquals($expected, $row['value']);
    }

    private function assertNumericArchiveNotExists($periodId, $date, $name)
    {
        $row = $this->getRowFromArchive($periodId, $date, $name);
        $this->assertEmpty($row);
    }

    private function assertBlobArchiveNotExists($periodId, $date, $name)
    {
        $row = $this->getRowFromArchive($periodId, $date, $name, false);
        $this->assertEmpty($row);
    }

    private function getAllNumericRows($date)
    {
        $archiveTableName = ArchiveTableCreator::getNumericTable(Date::factory($date));
        $sql = 'SELECT value FROM ' . $archiveTableName;

        return Db::fetchAll($sql);
    }
    private function getRowFromArchive($periodId, $date, $name, $numeric = true)
    {
        if ($numeric) {
            $archiveTableName = ArchiveTableCreator::getNumericTable(Date::factory($date));
        } else {
            $archiveTableName = ArchiveTableCreator::getBlobTable(Date::factory($date));
        }
        $sql = 'SELECT value FROM ' . $archiveTableName . ' WHERE name="' . $name
            . '" AND idsite=' . $this->idSite
            . ' AND date1="' . $date
            . '" AND date2="' . $date
            . '" AND period=' . $periodId;
        $result = Db::get()->query($sql);
        return $result->fetch();
    }

    private function insertArchiveData($archiveRows)
    {
        if (!empty($archiveRows)) {
            $idarchives = array_column($archiveRows, 'idarchive');
            $max = max($idarchives);

            $d = Date::factory($archiveRows[0]['date1']);
            $tableName = Common::prefixTable('archive_numeric_' . $d->toString('Y_m'));
            $seq = new Sequence($tableName);
            $seq->create($max);
        }

        foreach ($archiveRows as $row) {
            $d = Date::factory($row['date1']);
            $table = ArchiveTableCreator::getNumericTable($d);
            $tsArchived = isset($row['ts_archived']) ? $row['ts_archived'] : Date::now()->getDatetime();

            Db::query(
                "INSERT INTO `$table` (idarchive, idsite, period, date1, date2, `name`, `value`, ts_archived) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$row['idarchive'], $row['idsite'], $row['period'], $row['date1'], $row['date2'], $row['name'], $row['value'], $tsArchived]
            );
        }
    }

    private function getAllColsOfAllNumericRows(string $date)
    {
        $archiveTableName = ArchiveTableCreator::getNumericTable(Date::factory($date));
        $sql = 'SELECT idarchive, idsite, date1, date2, period, name, value, ts_archived FROM ' . $archiveTableName;

        return Db::fetchAll($sql);
    }
}
