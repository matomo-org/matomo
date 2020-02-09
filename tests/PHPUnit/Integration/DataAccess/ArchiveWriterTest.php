<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Integration\Settings\IntegrationTestCase;

class TestArchiveWriter extends ArchiveWriter {
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
        Access::getInstance()->setSuperUserAccess(true);
        $this->idSite = Fixture::createWebsite('2019-08-29');
    }

    public function test_initNewArchive_doesNotWiteNewArchiveStatusToFileRightAway()
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

    public function test_finaliseArchive_writesArchiveStatusToFile()
    {
        $period = 'day';
        $date = '2019-08-29';

        $writer = $this->buildWriter($period, $date);
        $writer->initNewArchive();
        $writer->finalizeArchive();

        $this->assertNumericArchiveExists(Day::PERIOD_ID, $date, 'done', ArchiveWriter::DONE_OK);
    }

    public function test_insertRecord_notFlushedUntilFinaliseCalled()
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

    public function test_insertRecord_numericAndBlobRecords()
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

    public function test_insertRecord_multipleCallsBeforeFlushing()
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

    public function test_insertRecord_flushesAfterFiftyNumericRecords()
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

    private function buildWriter($period, $date)
    {
        $oPeriod = PeriodFactory::makePeriodFromQueryParams('UTC', $period, $date);
        $segment = new Segment('', []);
        $params  = new Parameters(new Site($this->idSite), $oPeriod, $segment);
        $writer  = new TestArchiveWriter($params, false);
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

    private function assertNumericArchiveNotExists($periodId, $date, $name) {
        $row = $this->getRowFromArchive($periodId, $date, $name);
        $this->assertEmpty($row);
    }

    private function assertBlobArchiveNotExists($periodId, $date, $name) {
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
}
