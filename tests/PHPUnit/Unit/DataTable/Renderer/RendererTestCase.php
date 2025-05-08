<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Renderer;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\DataTable\Simple;

/**
 * @group DataTableTest
 */
abstract class RendererTestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        DataTable\Manager::getInstance()->deleteAll();
    }

    protected static function getDataTable(): DataTable
    {
        $dataTable = new DataTable();

        $arraySubTableForRow2 = [
            [Row::COLUMNS => ['label' => 'sub1', 'count' => 1, 'bool' => false]],
            [Row::COLUMNS => ['label' => 'sub2', 'count' => 2, 'bool' => true]],
        ];
        $subDataTableForRow2 = new DataTable();
        $subDataTableForRow2->addRowsFromArray($arraySubTableForRow2);

        $array = [
            [
                Row::COLUMNS  => ['label' => 'Google&copy;', 'bool' => false, 'goals' => ['idgoal=1' => ['revenue' => 5.5, 'nb_conversions' => 10]], 'nb_uniq_visitors' => 11, 'nb_visits' => 11, 'nb_actions' => 17, 'max_actions' => '5', 'sum_visit_length' => 517, 'bounce_count' => 9],
                Row::METADATA => ['url' => 'http://www.google.com/display"and,properly', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.google.com.png'],
            ],
            [
                Row::COLUMNS              => ['label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'bool' => true, 'nb_visits' => 151, 'nb_actions' => 147, 'max_actions' => '50', 'sum_visit_length' => 517, 'bounce_count' => 90],
                Row::METADATA             => ['url' => 'http://www.yahoo.com', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png'],
                Row::DATATABLE_ASSOCIATED => $subDataTableForRow2,
            ],
        ];
        $dataTable->addRowsFromArray($array);
        return $dataTable;
    }

    protected static function getDataTableSimple(): Simple
    {
        $array = ['max_actions' => 14.0, 'nb_uniq_visitors' => 57.0, 'nb_visits' => 66.0, 'nb_actions' => 151.0, 'sum_visit_length' => 5118.0, 'bounce_count' => 44.0,];

        $table = new Simple();
        $table->addRowsFromArray($array);
        return $table;
    }

    protected static function getDataTableSimpleOneRow(): Simple
    {
        $array = ['nb_visits' => 14.0];

        $table = new Simple();
        $table->addRowsFromArray($array);
        return $table;
    }

    protected static function getDataTableEmpty(): DataTable
    {
        return new DataTable();
    }

    protected static function getDataTableSimpleOneZeroRow(): Simple
    {
        $array = ['nb_visits' => 0];
        $table = new Simple();
        $table->addRowsFromArray($array);
        return $table;
    }

    protected static function getDataTableSimpleOneFalseRow(): Simple
    {
        $array = ['is_excluded' => false];
        $table = new Simple();
        $table->addRowsFromArray($array);
        return $table;
    }

    protected static function getDataTableHavingAnArrayInRowMetadata(): DataTable
    {
        $array = [
            [Row::COLUMNS => ['label' => 'sub1', 'count' => 1]],
            [Row::COLUMNS => ['label' => 'sub2', 'count' => 2], Row::METADATA => ['test' => 'render']],
            [Row::COLUMNS => ['label' => 'sub3', 'count' => 2], Row::METADATA => ['test' => 'renderMe', 'testArray' => 'ignore']],
            [Row::COLUMNS => ['label' => 'sub4', 'count' => 6], Row::METADATA => ['testArray' => ['do not render']]],
            [Row::COLUMNS => ['label' => 'sub5', 'count' => 2], Row::METADATA => ['testArray' => 'do ignore', 'mymeta' => 'should be rendered']],
            [Row::COLUMNS => ['label' => 'sub6', 'count' => 3], Row::METADATA => ['mymeta' => 'renderrrrrr']],
        ];

        $table = new DataTable();
        $table->addRowsFromArray($array);

        return $table;
    }

    protected static function getDataTableMap(): DataTable\Map
    {
        $array1 = [
            [
                Row::COLUMNS  => ['label' => 'Google', 'nb_uniq_visitors' => 11, 'nb_visits' => 11,],
                Row::METADATA => ['url' => 'http://www.google.com', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.google.com.png'],
            ],
            [
                Row::COLUMNS  => ['label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'nb_visits' => 151,],
                Row::METADATA => ['url' => 'http://www.yahoo.com', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png'],
            ],
        ];
        $table1 = new DataTable();
        $table1->addRowsFromArray($array1);

        $array2 = [
            [
                Row::COLUMNS  => ['label' => 'Google1&copy;', 'nb_uniq_visitors' => 110, 'nb_visits' => 110,],
                Row::METADATA => ['url' => 'http://www.google.com1', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.google.com.png1'],
            ],
            [
                Row::COLUMNS  => ['label' => 'Yahoo!1', 'nb_uniq_visitors' => 150, 'nb_visits' => 1510,],
                Row::METADATA => ['url' => 'http://www.yahoo.com1', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png1'],
            ],
        ];
        $table2 = new DataTable();
        $table2->addRowsFromArray($array2);

        $table3 = new DataTable();

        $table = new DataTable\Map();
        $table->setKeyName('testKey');
        $table->addTable($table1, 'date1');
        $table->addTable($table2, 'date2');
        $table->addTable($table3, 'date3');

        return $table;
    }

    protected static function getDataTableSimpleMap(): DataTable\Map
    {
        $array1 = ['max_actions' => 14.0, 'nb_uniq_visitors' => 57.0,];
        $table1 = new Simple();
        $table1->addRowsFromArray($array1);

        $array2 = ['max_actions' => 140.0, 'nb_uniq_visitors' => 570.0,];
        $table2 = new Simple();
        $table2->addRowsFromArray($array2);

        $table3 = new Simple();

        $table = new DataTable\Map();
        $table->setKeyName('testKey');
        $table->addTable($table1, 'row1');
        $table->addTable($table2, 'row2');
        $table->addTable($table3, 'row3');

        return $table;
    }

    protected static function getDataTableSimpleOneRowMap(): DataTable\Map
    {
        $array1 = ['nb_visits' => 14.0];
        $table1 = new Simple();
        $table1->addRowsFromArray($array1);
        $array2 = ['nb_visits' => 15.0];
        $table2 = new Simple();
        $table2->addRowsFromArray($array2);

        $table3 = new Simple();

        $table = new DataTable\Map();
        $table->setKeyName('testKey');
        $table->addTable($table1, 'row1');
        $table->addTable($table2, 'row2');
        $table->addTable($table3, 'row3');

        return $table;
    }

    protected static function getDataTableMapContainsDataTableMapNormal(): DataTable\Map
    {
        $table = new DataTable\Map();
        $table->setKeyName('parentArrayKey');
        $table->addTable(self::getDataTableMap(), 'idSite');
        return $table;
    }

    protected static function getDataTableMapContainsDataTableMapSimple(): DataTable\Map
    {
        $table = new DataTable\Map();
        $table->setKeyName('parentArrayKey');
        $table->addTable(self::getDataTableSimpleMap(), 'idSite');
        return $table;
    }

    protected static function getDataTableMapContainsDataTableMapSimpleOneRow(): DataTable\Map
    {
        $table = new DataTable\Map();
        $table->setKeyName('parentArrayKey');
        $table->addTable(self::getDataTableSimpleOneRowMap(), 'idSite');
        return $table;
    }

    protected static function getComparisonDataTable(): DataTable
    {
        $dataTable = new DataTable();

        $row = new DataTable\Row();
        $row->addColumn('nb_visits', 5);
        $row->addColumn('nb_random', 10);

        $otherDataTable = new DataTable();
        $otherDataTable->addRowsFromSimpleArray([
            ['nb_visits' => 6, 'nb_random' => 7],
            ['nb_visits' => 8, 'nb_random' => 9],
        ]);
        $row->setComparisons($otherDataTable);

        $dataTable->addRow($row);

        return $dataTable;
    }
}
