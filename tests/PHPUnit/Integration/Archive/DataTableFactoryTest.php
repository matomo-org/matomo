<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Archive;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Period;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Archive\DataTableFactory;

/**
 * @group DataTableFactoryTest
 * @group DataTableFactoryjj
 * @group Archive
 * @group Core
 */
class DataTableFactoryTest extends IntegrationTestCase
{
    private $site1 = 3;
    private $site2 = 4;
    private $date1range = '2012-12-12,2012-12-12';
    private $date2range = '2012-12-13,2012-12-13';

    private $date1 = '2012-12-12';
    private $date2 = '2012-12-13';

    private $defaultRow = array(
        'nb_visits' => 97
    );

    public function setUp(): void
    {
        parent::setUp();

        // setup the access layer
        FakeAccess::$superUser = true;

        for ($i = 0; $i < $this->site2; $i++) {
            Fixture::createWebsite('2015-01-01 00:00:00');
        }

        API::getInstance()->add('TEST SEGMENT', 'browserCode==ff');
    }

    public function test_getSiteIdFromMetadata_no_site()
    {
        $siteid = DataTableFactory::getSiteIdFromMetadata(new DataTable());
        $this->assertNull($siteid);
    }

    public function test_getSiteIdFromMetadata()
    {
        $table = new DataTable();
        $table->setMetadata('site', new Site($this->site1));
        $siteid = DataTableFactory::getSiteIdFromMetadata($table);
        $this->assertEquals($this->site1, $siteid);
    }

    public function test_makeMerged_numeric_noIndices_shouldContainDefaultRow_IfNoDataGiven()
    {
        $indices = $this->getResultIndices($period = false, $site = false);
        $factory = $this->createFactory($indices);

        $table = $factory->makeMerged($index = array(), $indices);

        $this->assertTableIsDataTableSimpleInstance($table);
        $this->assertRowCountEquals(1, $table);
        $this->assertRowEquals($this->defaultRow, $this->site1, $table->getFirstRow());
        $this->assertTableMetadataEquals($this->date1, $table);
    }

    public function test_makeMerged_numeric_noIndices_shouldContainOnlyOneRowWithTheData_IfAnyDataIsSet()
    {
        $indices = $this->getResultIndices($period = false, $site = false);
        $factory = $this->createFactory($indices);

        $index = array('nb_visits' => 10, 'nb_pageviews' => 21);

        $dataTable = $factory->makeMerged($index, $indices);

        $this->assertTableIsDataTableSimpleInstance($dataTable);
        $this->assertRowCountEquals(1, $dataTable);
        $this->assertRowEquals($index, $this->site1, $dataTable->getFirstRow());
        $this->assertTableMetadataEquals($this->date1, $dataTable);
    }

    public function test_makeMerged_numeric_periodIndices_shouldGenerateAMapOfTables_AndUseDefaultRow_IfNoData()
    {
        $indices = $this->getResultIndices($period = true, $site = false);
        $factory = $this->createFactory($indices);

        $index = array(
            $this->date1range => array(),
            $this->date2range => array(),
        );

        $map = $factory->makeMerged($index, $indices);

        $this->assertTrue($map instanceof DataTable\Map);
        $this->assertRowCountEquals(2, $map);

        foreach ($map->getDataTables() as $label => $table) {
            $this->assertTrue(in_array($label, array($this->date1, $this->date2)));
            $this->assertTableIsDataTableSimpleInstance($table);
            $this->assertRowCountEquals(1, $table);
            $this->assertRowEquals($this->defaultRow, $this->site1, $table->getFirstRow());
            $this->assertTableMetadataEquals($label, $table);
        }
    }

    public function test_makeMerged_numeric_periodIndices_shouldGenerateAMapOfTables_WithData()
    {
        $indices = $this->getResultIndices($period = true, $site = false);
        $factory = $this->createFactory($indices);

        $row1 = array('nb_visits' => 37, 'nb_pageviews' => 10);
        $row2 = array('nb_visits' => 34, 'nb_hits' => 21);

        $index = array(
            $this->date1range => $row1,
            $this->date2range => $row2,
        );

        $map = $factory->makeMerged($index, $indices);

        $this->assertTrue($map instanceof DataTable\Map);
        $this->assertRowCountEquals(2, $map);

        foreach ($map->getDataTables() as $label => $table) {
            $this->assertTableIsDataTableSimpleInstance($table);
            $this->assertRowCountEquals(1, $table);
            $this->assertTableMetadataEquals($label, $table);
        }

        $this->assertRowEquals($row1, $this->site1, $map->getTable($this->date1)->getFirstRow());
        $this->assertRowEquals($row2, $this->site1, $map->getTable($this->date2)->getFirstRow());
    }

    public function test_makeMerged_numeric_periodIndices_shouldSetAKeyName()
    {
        $indices = $this->getResultIndices($period = true, $site = false);
        $factory = $this->createFactory($indices);

        $index = array(
            $this->date1range => array(),
            $this->date2range => array(),
        );

        $map = $factory->makeMerged($index, $indices);

        $this->assertSame('date', $map->getKeyName());
    }

    public function test_makeMerged_numeric_siteIndices_shouldUseDefaultRow_IfNoData()
    {
        $indices = $this->getResultIndices($period = false, $site = true);
        $factory = $this->createFactory($indices);

        $index = array(
            $this->site1 => array(),
            $this->site2 => array(),
        );

        $table = $factory->makeMerged($index, $indices);

        $this->assertTableIsDataTableInstance($table);
        $this->assertRowCountEquals(2, $table);

        $this->assertRowEquals($this->defaultRow, $this->site1, $table->getRowFromId(0));
        $this->assertRowEquals($this->defaultRow, $this->site2, $table->getRowFromId(1));
        $this->assertTableMetadataEquals($this->date1, $table);
    }

    public function test_makeMerged_numeric_siteIndices_shouldGenerateAMapOfTables_WithData()
    {
        $indices = $this->getResultIndices($period = false, $site = true);
        $factory = $this->createFactory($indices);

        $row1 = array('nb_visits' => 37, 'nb_pageviews' => 10);
        $row2 = array('nb_visits' => 34, 'nb_hits' => 21);

        $index = array(
            $this->site1 => $row1,
            $this->site2 => $row2,
        );

        $table = $factory->makeMerged($index, $indices);

        $this->assertTableIsDataTableInstance($table);
        $this->assertRowCountEquals(2, $table);

        $this->assertRowEquals($row1, $this->site1, $table->getRowFromId(0));
        $this->assertRowEquals($row2, $this->site2, $table->getRowFromId(1));
        $this->assertTableMetadataEquals($this->date1, $table);
    }

    public function test_makeMerged_numeric_siteAndPeriodIndices_shouldUseDefaultRow_IfNoData()
    {
        $indices = $this->getResultIndices($period = true, $site = true);
        $factory = $this->createFactory($indices);

        $index = array(
            $this->site1 => array(
                $this->date1range => array(),
                $this->date2range => array()
            ),
            $this->site2 => array(
                $this->date1range => array(),
                $this->date2range => array()
            ),
        );

        $map = $factory->makeMerged($index, $indices);

        $this->assertTrue($map instanceof DataTable\Map);
        $this->assertRowCountEquals(2, $map);
        $this->assertSame('date', $map->getKeyName());

        foreach ($map->getDataTables() as $label => $table) {
            $this->assertTrue(in_array($label, array($this->date1, $this->date2)));
            $this->assertTableIsDataTableInstance($table);
            $this->assertRowCountEquals(2, $table);
            $this->assertTableMetadataEquals($label, $table);
        }

        $this->assertRowEquals($this->defaultRow, $this->site1, $map->getTable($this->date1)->getRowFromId(0));
        $this->assertRowEquals($this->defaultRow, $this->site2, $map->getTable($this->date1)->getRowFromId(1));
        $this->assertRowEquals($this->defaultRow, $this->site1, $map->getTable($this->date2)->getRowFromId(0));
        $this->assertRowEquals($this->defaultRow, $this->site2, $map->getTable($this->date2)->getRowFromId(1));
    }

    public function test_makeMerged_numeric_siteAndPeriodIndices_shouldGenerateAMapOfTables_WithData()
    {
        $indices = $this->getResultIndices($period = true, $site = true);
        $factory = $this->createFactory($indices);

        $row1 = array('nb_visits' => 37, 'nb_pageviews' => 10);
        $row2 = array('nb_visits' => 34, 'nb_hits' => 21);
        $row3 = array('nb_visits' => 23);

        $index = array(
            $this->site1 => array(
                $this->date1range => $row1,
                $this->date2range => array()
            ),
            $this->site2 => array(
                $this->date1range => $row2,
                $this->date2range => $row3
            ),
        );

        $map = $factory->makeMerged($index, $indices);

        $this->assertTrue($map instanceof DataTable\Map);
        $this->assertRowCountEquals(2, $map);

        foreach ($map->getDataTables() as $label => $table) {
            $this->assertTrue(in_array($label, array($this->date1, $this->date2)));
            $this->assertTableIsDataTableInstance($table);
            $this->assertRowCountEquals(2, $table);
            $this->assertTableMetadataEquals($label, $table);
        }

        $this->assertRowEquals($row1, $this->site1, $map->getTable($this->date1)->getRowFromId(0));
        $this->assertRowEquals($row2, $this->site2, $map->getTable($this->date1)->getRowFromId(1));
        $this->assertRowEquals($this->defaultRow, $this->site1, $map->getTable($this->date2)->getRowFromId(0));
        $this->assertRowEquals($row3, $this->site2, $map->getTable($this->date2)->getRowFromId(1));
    }

    public function test_makeMerged_shouldThrowAnException_IfANonNumericDataTypeIsGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('supposed to work with non-numeric data types but it is not tested');

        $dataType  = 'blob';
        $dataNames = array('nb_visits');

        $factory = new DataTableFactory($dataNames, $dataType, array($this->site1), $periods = array(), new Segment('browserCode==ff', []), $this->defaultRow);
        $factory->makeMerged(array(), array());
    }

    private function assertTableMetadataEquals($expectedPeriod, DataTable $dataTable)
    {
        $period = $dataTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);

        $this->assertFalse($dataTable->getMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX));
        $this->assertTrue($period instanceof Period);
        $this->assertSame($expectedPeriod, $period->toString());

        $segment = $dataTable->getMetadata(DataTableFactory::TABLE_METADATA_SEGMENT_INDEX);
        $this->assertEquals('browserCode==ff', $segment);

        $segmentPretty = $dataTable->getMetadata(DataTableFactory::TABLE_METADATA_SEGMENT_PRETTY_INDEX);
        $this->assertEquals('TEST SEGMENT', $segmentPretty);
    }

    private function assertRowCountEquals($expectedCount, $tableOrMap)
    {
        if ($tableOrMap instanceof DataTable\Map) {
            $this->assertSame($expectedCount, $tableOrMap->getRowsCount());
        } elseif ($tableOrMap instanceof DataTable) {
            $this->assertSame($expectedCount, $tableOrMap->getRowsCountRecursive());
        } else {
            throw new \Exception('wrong argument passed to assertRowCountEquals()');
        }
    }

    private function assertRowEquals($expectedColumns, $expectedSiteIdInMetadata, Row $row)
    {
        $this->assertEquals($expectedColumns, $row->getColumns());
        $this->assertEquals(array('idsite' => $expectedSiteIdInMetadata), $row->getMetadata());
    }

    private function assertTableIsDataTableInstance($table)
    {
        $this->assertTrue($table instanceof DataTable);
        $this->assertFalse($table instanceof DataTable\Simple);
    }

    private function assertTableIsDataTableSimpleInstance($table)
    {
        $this->assertTrue($table instanceof DataTable\Simple);
    }

    private function createFactory($resultIndices)
    {
        $periods   = array(
            $this->date1range => PeriodFactory::build('day', $this->date1),
            $this->date2range => PeriodFactory::build('day', $this->date2),
        );
        $dataType  = 'numeric';
        $siteIds   = array($this->site1, $this->site2);
        $dataNames = array('nb_visits', 'nb_pageviews');
        $defaultRow = $this->defaultRow;

        if (!array_key_exists(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, $resultIndices)) {
            $periods = array($periods[$this->date1range]);
        }

        if (!array_key_exists(DataTableFactory::TABLE_METADATA_SITE_INDEX, $resultIndices)) {
            $siteIds = array($siteIds[0]);
        }

        return new DataTableFactory($dataNames, $dataType, $siteIds, $periods, new Segment('browserCode==ff', []), $defaultRow);
    }

    private function getResultIndices($periodIndex = false, $siteIndex = false)
    {
        $indices = array();

        if ($siteIndex) {
            $indices[DataTableFactory::TABLE_METADATA_SITE_INDEX] = 'idSite';
        }

        if ($periodIndex) {
            $indices[DataTableFactory::TABLE_METADATA_PERIOD_INDEX] = 'date';
        }

        return $indices;
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
