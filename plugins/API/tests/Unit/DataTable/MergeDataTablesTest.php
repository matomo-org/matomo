<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Unit\DataTable;

use Piwik\DataTable;
use Piwik\Plugins\API\DataTable\MergeDataTables;

class MergeDataTablesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MergeDataTables
     */
    private $instance;

    public function setUp(): void
    {
        parent::setUp();
        $this->instance = new MergeDataTables();
    }

    public function testMergeDataTablesReturnsCorrectDataWhenTwoTablesAreMerged()
    {
        $table1 = new DataTable();
        $table1->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 1', 'nb_visits' => 2, 'nb_other' => 3],
            ]),
        ]);
        $table1->setAllTableMetadata([
            'a' => 'b',
        ]);

        $table2 = new DataTable();
        $table2->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 1', 'nb_visits' => 1, 'nb_other' => 1],
            ]),
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 2', 'nb_visits' => 5, 'nb_other' => 5],
            ]),
        ]);
        $table2->setAllTableMetadata([
            'c' => '3',
        ]);

        $this->instance->mergeDataTables($table1, $table2);

        $xml = $this->getTableAsXml($table1);

        $expectedXml = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<label>label 1</label>
		<nb_visits>1</nb_visits>
		<nb_other>1</nb_other>
	</row>
</result>
END;

        $this->assertEquals($expectedXml, $xml);
    }

    public function testMergeDataTablesReturnsCorrectDataWhenTwoMapsAreMergedAndBothHaveTheSameAmountOfData()
    {
        $table1 = new DataTable\Map();
        $table1->setKeyName('period');

        $childTable1 = new DataTable();
        $childTable1->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 1', 'nb_visits' => 1, 'nb_other' => 1],
            ]),
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 2', 'nb_visits' => 5, 'nb_other' => 5],
            ]),
        ]);
        $childTable1->setAllTableMetadata(['a' => 'b']);
        $table1->addTable($childTable1, 'p1');

        $childTable2 = new DataTable();
        $childTable2->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 5', 'nb_visits' => 1],
            ]),
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 6', 'nb_other' => 5],
            ]),
        ]);
        $childTable2->setAllTableMetadata(['c' => 'd']);
        $table1->addTable($childTable1, 'p2');

        $table2 = new DataTable\Map();
        $table2->setKeyName('period');

        $childTable1 = new DataTable();
        $childTable1->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 1', 'nb_visits' => 4, 'nb_other' => 4],
            ]),
        ]);
        $childTable1->setAllTableMetadata(['a' => 'b']);
        $table2->addTable($childTable1, 'p1');

        $childTable2 = new DataTable();
        $childTable2->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 6', 'nb_other' => 5],
            ]),
        ]);
        $childTable2->setAllTableMetadata(['c' => 'd']);
        $table2->addTable($childTable1, 'p2');

        $this->instance->mergeDataTables($table1, $table2);

        $xml = $this->getTableAsXml($table1);

        $expectedXml = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result period="p1">
		<row>
			<label>label 1</label>
			<nb_visits>4</nb_visits>
			<nb_other>4</nb_other>
		</row>
		<row>
			<label>label 2</label>
			<nb_visits>5</nb_visits>
			<nb_other>5</nb_other>
		</row>
	</result>
	<result period="p2">
		<row>
			<label>label 1</label>
			<nb_visits>4</nb_visits>
			<nb_other>4</nb_other>
		</row>
		<row>
			<label>label 2</label>
			<nb_visits>5</nb_visits>
			<nb_other>5</nb_other>
		</row>
	</result>
</results>
END;

        $this->assertEquals($expectedXml, $xml);
    }

    public function testMergeDataTablesReturnsCorrectDataWhenTwoMapsAreMergedAndFirstHasLessThanSecond()
    {
        $table1 = new DataTable\Map();
        $table1->setKeyName('period');

        $childTable2 = new DataTable();
        $childTable2->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 5', 'nb_visits' => 1],
            ]),
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 6', 'nb_other' => 5],
            ]),
        ]);
        $childTable2->setAllTableMetadata(['c' => 'd']);
        $table1->addTable($childTable2, 'p2');

        $table2 = new DataTable\Map();
        $table2->setKeyName('period');

        $childTable1 = new DataTable();
        $childTable1->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 1', 'nb_visits' => 4, 'nb_other' => 4],
            ]),
        ]);
        $childTable1->setAllTableMetadata(['a' => 'b']);
        $table2->addTable($childTable1, 'p1');

        $childTable2 = new DataTable();
        $childTable2->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 6', 'nb_other' => 5],
            ]),
        ]);
        $childTable1->setAllTableMetadata(['c' => 'd']);
        $table2->addTable($childTable1, 'p2');

        $this->instance->mergeDataTables($table1, $table2);

        $xml = $this->getTableAsXml($table1);

        $expectedXml = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result period="p2">
		<row>
			<label>label 1</label>
			<nb_visits>4</nb_visits>
			<nb_other>4</nb_other>
		</row>
		<row>
			<label>label 6</label>
			<nb_other>5</nb_other>
		</row>
	</result>
	<result period="p1">
		<row>
			<label>label 1</label>
			<nb_visits>4</nb_visits>
			<nb_other>4</nb_other>
		</row>
	</result>
</results>
END;

        $this->assertEquals($expectedXml, $xml);
    }

    public function testMergeDataTablesReturnsCorrectDataWhenTwoMapsAreMergedAndSecondHasLessThanFirst()
    {
        $table1 = new DataTable\Map();
        $table1->setKeyName('period');

        $childTable1 = new DataTable();
        $childTable1->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 1', 'nb_visits' => 1, 'nb_other' => 1],
            ]),
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 2', 'nb_visits' => 5, 'nb_other' => 5],
            ]),
        ]);
        $childTable1->setAllTableMetadata(['a' => 'b']);
        $table1->addTable($childTable1, 'p1');

        $childTable2 = new DataTable();
        $childTable2->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 5', 'nb_visits' => 1],
            ]),
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 6', 'nb_other' => 5],
            ]),
        ]);
        $childTable2->setAllTableMetadata(['c' => 'd']);
        $table1->addTable($childTable1, 'p2');

        $table2 = new DataTable\Map();
        $table2->setKeyName('period');

        $childTable1 = new DataTable();
        $childTable1->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 1', 'nb_visits' => 4, 'nb_other' => 4],
            ]),
        ]);
        $childTable1->setAllTableMetadata(['a' => 'b']);
        $table2->addTable($childTable1, 'p1');

        $this->instance->mergeDataTables($table1, $table2);

        $xml = $this->getTableAsXml($table1);

        $expectedXml = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result period="p1">
		<row>
			<label>label 1</label>
			<nb_visits>4</nb_visits>
			<nb_other>4</nb_other>
		</row>
		<row>
			<label>label 2</label>
			<nb_visits>5</nb_visits>
			<nb_other>5</nb_other>
		</row>
	</result>
	<result period="p2">
		<row>
			<label>label 1</label>
			<nb_visits>4</nb_visits>
			<nb_other>4</nb_other>
		</row>
		<row>
			<label>label 2</label>
			<nb_visits>5</nb_visits>
			<nb_other>5</nb_other>
		</row>
	</result>
</results>
END;

        $this->assertEquals($expectedXml, $xml);
    }

    public function testMergeDataTablesReturnsCorrectDataWhenMapsAreNested()
    {
        $table1 = new DataTable\Map();
        $table1->setKeyName('site');

        $childTableMap1 = new DataTable\Map();
        $childTableMap1->setKeyName('period');
        $table1->addTable($childTableMap1, 's1');

        $childTable1 = new DataTable();
        $childTable1->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 1', 'nb_visits' => 1, 'nb_other' => 1],
            ]),
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 2', 'nb_visits' => 5, 'nb_other' => 5],
            ]),
        ]);
        $childTable1->setAllTableMetadata(['a' => 'b']);
        $childTableMap1->addTable($childTable1, 'p1');

        $table2 = new DataTable\Map();
        $table2->setKeyName('site');

        $childTableMap2 = new DataTable\Map();
        $childTableMap2->setKeyName('period');
        $table2->addTable($childTableMap2, 's2');

        $childTable2 = new DataTable();
        $childTable2->addRowsFromArray([
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 1', 'nb_visits' => 1, 'nb_other' => 1],
            ]),
            new DataTable\Row([
                DataTable\Row::COLUMNS => ['label' => 'label 2', 'nb_visits' => 5, 'nb_other' => 5],
            ]),
        ]);
        $childTable2->setAllTableMetadata(['c' => 'd']);
        $childTableMap2->addTable($childTable2, 'p1');

        $this->instance->mergeDataTables($table1, $table2);

        $xml = $this->getTableAsXml($table1);

        $expectedXml = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result site="s1">
		<result period="p1">
			<row>
				<label>label 1</label>
				<nb_visits>1</nb_visits>
				<nb_other>1</nb_other>
			</row>
			<row>
				<label>label 2</label>
				<nb_visits>5</nb_visits>
				<nb_other>5</nb_other>
			</row>
		</result>
	</result>
	<result site="s2">
		<result period="p1">
			<row>
				<label>label 1</label>
				<nb_visits>1</nb_visits>
				<nb_other>1</nb_other>
			</row>
		</result>
	</result>
</results>
END;

        $this->assertEquals($expectedXml, $xml);
    }
// TODO: test for metadata
    private function getTableAsXml(DataTable\DataTableInterface $table1)
    {
        $renderer = new DataTable\Renderer\Xml();
        $renderer->setTable($table1);
        return $renderer->render();
    }
}
