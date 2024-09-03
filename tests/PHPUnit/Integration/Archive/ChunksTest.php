<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Archive;

use Piwik\Archive;
use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\Db;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\Mock\Site;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Period\Factory as PeriodFactory;

/**
 * @group ChunksTest
 * @group Chunks
 * @group Core
 */
class ChunksTest extends IntegrationTestCase
{
    private $date = '2015-01-01';

    public function setUp(): void
    {
        parent::setUp();

        // setup the access layer
        FakeAccess::$superUser = true;

        Fixture::createWebsite('2015-01-01 00:00:00');
    }

    public function testSubtablesWillBeSplitIntoChunks()
    {
        $numSubtablesToGenerate = 1053;

        $blobs = $this->generateDataTableWithManySubtables($numSubtablesToGenerate);
        $this->assertCount($numSubtablesToGenerate + 1, $blobs); // +1 for the root table

        $recordName = 'Actions_MyRecord';
        $archiver = $this->createPluginsArchiver();
        $archiver->archiveProcessor->insertBlobRecord($recordName, $blobs);
        $archiver->finalizeArchive();

        // verify they were split into chunks
        $archiveRows = $this->getAllRowsFromArchiveBlobTable('name');
        $expectedArchiveNames = array(
            $recordName,
            $recordName . '_chunk_0_99',
            $recordName . '_chunk_1000_1099',
            $recordName . '_chunk_100_199',
            $recordName . '_chunk_200_299',
            $recordName . '_chunk_300_399',
            $recordName . '_chunk_400_499',
            $recordName . '_chunk_500_599',
            $recordName . '_chunk_600_699',
            $recordName . '_chunk_700_799',
            $recordName . '_chunk_800_899',
            $recordName . '_chunk_900_999',
        );

        $this->assertEqualsCanonicalizing($expectedArchiveNames, array_column($archiveRows, 'name'));

        // verify all have same archiveIds
        $expectedArchiveIds = array_fill(0, count($expectedArchiveNames), $archiveId = '1');
        $this->assertEquals($expectedArchiveIds, array_column($archiveRows, 'idarchive'));

        // verify the subtables were actually splitted into chunks
        foreach ($archiveRows as $row) {
            $value = unserialize(gzuncompress($row['value']));
            $this->assertTrue(is_array($value));
            if ($row['name'] == $recordName) {
                $this->assertCount($numSubtablesToGenerate, $value); // 1053 rows
            } elseif ($row['name'] == $recordName . '_chunk_1000_1099') {
                $this->assertCount(($numSubtablesToGenerate % Archive\Chunk::NUM_TABLES_IN_CHUNK) + 1, $value); // 53 subtables
            } elseif ($row['name'] == $recordName . '_chunk_0_99') {
                $this->assertCount(Archive\Chunk::NUM_TABLES_IN_CHUNK - 1, $value); // one less as we do not store the root table here
            } else {
                $this->assertCount(Archive\Chunk::NUM_TABLES_IN_CHUNK, $value);
            }
        }

        // should be able to rebuild the datatable
        $archive = Archive::build(1, 'day', $this->date);
        $table = $archive->getDataTable($recordName);
        $this->assertSame(1053, $table->getRowsCount());
        $this->assertSame('Label Test 1', $table->getFirstRow()->getColumn('label'));
        $this->assertSame(1, $table->getFirstRow()->getColumn('nb_visits'));
    }

    private function getAllRowsFromArchiveBlobTable()
    {
        $table = ArchiveTableCreator::getBlobTable(Date::factory($this->date));
        $rows  = Db::fetchAll("SELECT * FROM " . $table);

        return $rows;
    }

    private function generateDataTableWithManySubtables($numSubtables)
    {
        $dataTable = new DataTable();

        for ($i = 1; $i <= $numSubtables; $i++) {
            $row = new Row(array(Row::COLUMNS => array('label' => 'Label Test ' . $i, 'nb_visits' => $i)));

            $subtable = DataTable::makeFromSimpleArray(array(array('label' => 'subtable' . $i, 'nb_visits' => $i)));
            $row->setSubtable($subtable);

            $dataTable->addRow($row);
        }

        return $dataTable->getSerialized();
    }

    private function createArchiveProcessorParameters()
    {
        $oPeriod = PeriodFactory::makePeriodFromQueryParams('UTC', 'day', $this->date);

        $segment = new Segment(false, array(1), $oPeriod->getDateTimeStart(), $oPeriod->getDateTimeEnd());
        $params  = new Parameters(new Site(1), $oPeriod, $segment);

        return $params;
    }

    private function createPluginsArchiver()
    {
        $params = $this->createArchiveProcessorParameters();

        return new ArchiveProcessor\PluginsArchiver($params);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
