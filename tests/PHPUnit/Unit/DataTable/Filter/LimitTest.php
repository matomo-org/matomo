<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Filter;

use Piwik\DataTable\Filter\Limit;
use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * @group DataTableTest
 */
class LimitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Returns table used for the tests
     *
     * @return DataTable
     */
    protected function getDataTableCount10()
    {
        $table = new DataTable();
        $idcol = Row::COLUMNS;
        $rows = array(
            array($idcol => array('label' => 'google', 'idRow' => 0)),
            array($idcol => array('label' => 'ask', 'idRow' => 1)),
            array($idcol => array('label' => 'piwik', 'idRow' => 2)),
            array($idcol => array('label' => 'yahoo', 'idRow' => 3)),
            array($idcol => array('label' => 'amazon', 'idRow' => 4)),
            array($idcol => array('label' => '238949', 'idRow' => 5)),
            array($idcol => array('label' => 'test', 'idRow' => 6)),
            array($idcol => array('label' => 'amazing', 'idRow' => 7)),
            array($idcol => array('label' => 'great', 'idRow' => 8)),
            DataTable::ID_SUMMARY_ROW => array($idcol => array('label' => 'summary row', 'idRow' => 9)),
        );
        $table->addRowsFromArray($rows);
        return $table;
    }


    public function testNormal()
    {
        $offset = 2;
        $limit = 3;
        $table = $this->getDataTableCount10();
        $filter = new Limit($table, $offset, $limit);
        $filter->filter($table);
        $this->assertEquals(3, $table->getRowsCount());
        $this->assertEquals(2, $table->getFirstRow()->getColumn('idRow'));
        $this->assertEquals(4, $table->getLastRow()->getColumn('idRow'));
        $this->assertEquals(10, $table->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME));
    }


    public function testLimitLessThanCountShouldReturnCountLimit()
    {
        $offset = 2;
        $limit = 7;
        $table = $this->getDataTableCount10();
        $filter = new Limit($table, $offset, $limit);
        $filter->filter($table);
        $this->assertEquals(7, $table->getRowsCount());
        $this->assertEquals(2, $table->getFirstRow()->getColumn('idRow'));
        $this->assertEquals(8, $table->getLastRow()->getColumn('idRow'));
        $this->assertEquals(10, $table->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME));
    }


    public function testLimitIsCountShouldNotDeleteAnything()
    {
        $offset = 0;
        $limit = 10;
        $table = $this->getDataTableCount10();
        $this->assertEquals(10, $table->getRowsCount());
        $filter = new Limit($table, $offset, $limit);
        $filter->filter($table);
        $this->assertEquals(10, $table->getRowsCount());
        $this->assertEquals(0, $table->getFirstRow()->getColumn('idRow'));
        $this->assertEquals(9, $table->getLastRow()->getColumn('idRow'));
        $this->assertEquals(10, $table->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME));
    }


    public function testLimitGreaterThanCountShouldReturnCountUntilCount()
    {
        $offset = 5;
        $limit = 20;
        $table = $this->getDataTableCount10();
        $this->assertEquals(10, $table->getRowsCount());
        $filter = new Limit($table, $offset, $limit);
        $filter->filter($table);
        $this->assertEquals(5, $table->getRowsCount());
        $this->assertEquals(5, $table->getFirstRow()->getColumn('idRow'));
        $this->assertEquals(9, $table->getLastRow()->getColumn('idRow'));
        $this->assertEquals(10, $table->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME));
    }


    public function testLimitIsNullShouldReturnCountIsOffset()
    {
        $offset = 1;
        $table = $this->getDataTableCount10();
        $filter = new Limit($table, $offset);
        $filter->filter($table);
        $this->assertEquals(9, $table->getRowsCount());
        $this->assertEquals(1, $table->getFirstRow()->getColumn('idRow'));
        $this->assertEquals(9, $table->getLastRow()->getColumn('idRow'));
        $this->assertEquals(10, $table->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME));
    }


    public function testOffsetJustBeforeSummaryRowShouldJustReturnSummaryRow()
    {
        $offset = 9;
        $limit = 1;
        $table = $this->getDataTableCount10();
        $filter = new Limit($table, $offset, $limit);
        $filter->filter($table);
        $this->assertEquals(1, $table->getRowsCount());
        $this->assertEquals(9, $table->getFirstRow()->getColumn('idRow'));
        $this->assertEquals(9, $table->getLastRow()->getColumn('idRow'));
        $this->assertEquals(10, $table->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME));
    }


    public function testOffsetJustBeforeSummaryRowWithBigLimitShouldJustReturnSummaryRow()
    {
        $offset = 9;
        $limit = 100;
        $table = $this->getDataTableCount10();
        $filter = new Limit($table, $offset, $limit);
        $filter->filter($table);
        $this->assertEquals(1, $table->getRowsCount());
        $this->assertEquals(9, $table->getFirstRow()->getColumn('idRow'));
        $this->assertEquals(9, $table->getLastRow()->getColumn('idRow'));
        $this->assertEquals(10, $table->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME));
    }


    public function testOffsetBeforeSummaryRowShouldJustReturnRowAndSummaryRow()
    {
        $offset = 8;
        $limit = 3;
        $table = $this->getDataTableCount10();
        $filter = new Limit($table, $offset, $limit);
        $filter->filter($table);
        $this->assertEquals(2, $table->getRowsCount());
        $this->assertEquals(8, $table->getFirstRow()->getColumn('idRow'));
        $this->assertEquals(9, $table->getLastRow()->getColumn('idRow'));
        $this->assertEquals(10, $table->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME));
    }


    public function testOffsetGreaterThanCountShouldReturnEmptyTable()
    {
        $offset = 10;
        $limit = 10;
        $table = $this->getDataTableCount10();
        $filter = new Limit($table, $offset, $limit);
        $filter->filter($table);
        $this->assertEquals(0, $table->getRowsCount());
        $this->assertEquals(10, $table->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME));
    }


    public function testLimitIsZeroShouldReturnEmptyTable()
    {
        $offset = 0;
        $limit = 0;
        $table = $this->getDataTableCount10();
        $filter = new Limit($table, $offset, $limit);
        $filter->filter($table);
        $this->assertEquals(0, $table->getRowsCount());
        $this->assertEquals(10, $table->getMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME));
    }

    /**
     * Test to filter a table with a offset, limit
     *
     * @group Core
     */
    public function testFilterOffsetLimit()
    {
        $table = new DataTable();

        $idcol = Row::COLUMNS;

        $rows = array(
            array($idcol => array('label' => 'google')), //0
            array($idcol => array('label' => 'ask')), //1
            array($idcol => array('label' => 'piwik')), //2
            array($idcol => array('label' => 'yahoo')), //3
            array($idcol => array('label' => 'amazon')), //4
            array($idcol => array('label' => '238975247578949')), //5
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')) //6
        );

        $table->addRowsFromArray($rows);

        $expectedtable = clone $table;
        $expectedtable->deleteRows(array(0, 1, 6));

        $filter = new Limit($table, 2, 4);
        $filter->filter($table);

        $this->assertEquals(array_values($expectedtable->getRows()), array_values($table->getRows()));
    }

    /**
     * Test to filter a column with a offset, limit off bound
     *
     * @group Core
     */
    public function testFilterOffsetLimitOffbound()
    {
        $table = new DataTable();

        $idcol = Row::COLUMNS;

        $rows = array(
            array($idcol => array('label' => 'google')), //0
            array($idcol => array('label' => 'ask')), //1
            array($idcol => array('label' => 'piwik')), //2
            array($idcol => array('label' => 'yahoo')), //3
            array($idcol => array('label' => 'amazon')), //4
            array($idcol => array('label' => '238975247578949')), //5
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')) //6
        );

        $table->addRowsFromArray($rows);

        $expectedtable = clone $table;
        $expectedtable->deleteRows(array(0, 1, 3, 4, 5, 6));

        $filter = new Limit($table, 2, 1);
        $filter->filter($table);

        $this->assertEquals(array_values($expectedtable->getRows()), array_values($table->getRows()));
    }

    /**
     * Test to filter a column with a offset, limit 2
     *
     * @group Core
     */
    public function testFilterOffsetLimit2()
    {
        $table = new DataTable();

        $idcol = Row::COLUMNS;

        $rows = array(
            array($idcol => array('label' => 'google')), //0
            array($idcol => array('label' => 'ask')), //1
            array($idcol => array('label' => 'piwik')), //2
            array($idcol => array('label' => 'yahoo')), //3
            array($idcol => array('label' => 'amazon')), //4
            array($idcol => array('label' => '238975247578949')), //5
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')) //6
        );

        $table->addRowsFromArray($rows);

        $expectedtable = clone $table;

        $filter = new Limit($table, 0, 15);
        $filter->filter($table);

        $this->assertEquals(array_values($expectedtable->getRows()), array_values($table->getRows()));
    }

    /**
     * Test to filter a column with a offset, limit 3
     *
     * @group Core
     */
    public function testFilterOffsetLimit3()
    {
        $table = new DataTable();

        $idcol = Row::COLUMNS;

        $rows = array(
            array($idcol => array('label' => 'google')), //0
            array($idcol => array('label' => 'ask')), //1
            array($idcol => array('label' => 'piwik')), //2
            array($idcol => array('label' => 'yahoo')), //3
            array($idcol => array('label' => 'amazon')), //4
            array($idcol => array('label' => '238975247578949')), //5
            array($idcol => array('label' => 'Q*(%&*("$&%*(&"$*")"))')) //6
        );

        $table->addRowsFromArray($rows);

        $expectedtable = new DataTable();

        $filter = new Limit($table, 8, 15);
        $filter->filter($table);

        $this->assertEquals(array_values($expectedtable->getRows()), array_values($table->getRows()));
    }
}
