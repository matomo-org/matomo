<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Integration\Filter\DataComparisonFilter;

use Piwik\DataTable;
use Piwik\Period\Factory;
use Piwik\Plugins\API\Filter\DataComparisonFilter\ComparisonRowGenerator;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ComparisonRowGeneratorTest extends IntegrationTestCase
{
    public const TEST_SEGMENT = 'browserCode==ff';
    public const OTHER_SEGMENT = 'operatingSystemCode=WIN';

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        API::getInstance()->add('test segment', self::TEST_SEGMENT);
    }

    public function test_compareTables_shouldCompareTwoDataTablesCorrectly()
    {
        $table1 = $this->makeTable([
            ['label' => 'row1', 'nb_visits' => 5, 'nb_actions' => 10],
            ['label' => 'row2', 'nb_visits' => 10, 'nb_actions' => 25],
            ['label' => 'row3', 'nb_visits' => 20],
            ['label' => 'row4', 'nb_actions' => 30],
        ]);

        $table2 = $this->makeTable([
            ['label' => 'row1', 'nb_visits' => 10, 'nb_actions' => 5],
            ['label' => 'row3', 'somethingelse' => 25],
        ]);

        $compareMetadata = [
            'compareSegment' => self::TEST_SEGMENT,
            'comparePeriod' => 'day',
            'compareDate' => '2012-03-04',
        ];

        $comparisonRowGenerator = new ComparisonRowGenerator('reportSegment', false, []);
        $comparisonRowGenerator->compareTables($compareMetadata, $table1, $table2);

        $xmlContent = $this->toXml($table1);

        $expectedXml = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<label>row1</label>
		<nb_visits>5</nb_visits>
		<nb_actions>10</nb_actions>
		<comparisons>
			<row>
				<nb_visits>10</nb_visits>
				<nb_actions>5</nb_actions>
				<compareSegment>browserCode==ff</compareSegment>
				<comparePeriod>day</comparePeriod>
				<compareDate>2012-03-04</compareDate>
				<idsubdatatable>-1</idsubdatatable>
			</row>
		</comparisons>
	</row>
	<row>
		<label>row2</label>
		<nb_visits>10</nb_visits>
		<nb_actions>25</nb_actions>
		<comparisons>
			<row>
				<nb_visits>0</nb_visits>
				<nb_actions>0</nb_actions>
				<compareSegment>browserCode==ff</compareSegment>
				<comparePeriod>day</comparePeriod>
				<compareDate>2012-03-04</compareDate>
				<idsubdatatable>-1</idsubdatatable>
			</row>
		</comparisons>
	</row>
	<row>
		<label>row3</label>
		<nb_visits>20</nb_visits>
		<comparisons>
			<row>
				<somethingelse>25</somethingelse>
				<compareSegment>browserCode==ff</compareSegment>
				<comparePeriod>day</comparePeriod>
				<compareDate>2012-03-04</compareDate>
				<idsubdatatable>-1</idsubdatatable>
			</row>
		</comparisons>
	</row>
	<row>
		<label>row4</label>
		<nb_actions>30</nb_actions>
		<comparisons>
			<row>
				<nb_actions>0</nb_actions>
				<compareSegment>browserCode==ff</compareSegment>
				<comparePeriod>day</comparePeriod>
				<compareDate>2012-03-04</compareDate>
				<idsubdatatable>-1</idsubdatatable>
			</row>
		</comparisons>
	</row>
</result>
END;
        $this->assertEquals($expectedXml, $xmlContent);
    }

    public function test_compareTables_shouldUseFirstTableRowsForComparisons()
    {
        $table1 = $this->makeTable([
            ['label' => 'row1', 'nb_visits' => 10, 'nb_actions' => 5],
            ['label' => 'row3', 'somethingelse' => 25],
        ]);

        $table2 = $this->makeTable([
            ['label' => 'row1', 'nb_visits' => 5, 'nb_actions' => 10],
            ['label' => 'row2', 'nb_visits' => 10, 'nb_actions' => 25],
            ['label' => 'row3', 'nb_visits' => 20],
            ['label' => 'row4', 'nb_actions' => 30],
        ]);

        $compareMetadata = [
            'compareSegment' => self::TEST_SEGMENT,
            'comparePeriod' => 'day',
            'compareDate' => '2012-03-04',
        ];

        $comparisonRowGenerator = new ComparisonRowGenerator('reportSegment', false, []);
        $comparisonRowGenerator->compareTables($compareMetadata, $table1, $table2);

        $xmlContent = $this->toXml($table1);

        $expectedXml = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<label>row1</label>
		<nb_visits>10</nb_visits>
		<nb_actions>5</nb_actions>
		<comparisons>
			<row>
				<nb_visits>5</nb_visits>
				<nb_actions>10</nb_actions>
				<compareSegment>browserCode==ff</compareSegment>
				<comparePeriod>day</comparePeriod>
				<compareDate>2012-03-04</compareDate>
				<idsubdatatable>-1</idsubdatatable>
			</row>
		</comparisons>
	</row>
	<row>
		<label>row3</label>
		<somethingelse>25</somethingelse>
		<comparisons>
			<row>
				<nb_visits>20</nb_visits>
				<compareSegment>browserCode==ff</compareSegment>
				<comparePeriod>day</comparePeriod>
				<compareDate>2012-03-04</compareDate>
				<idsubdatatable>-1</idsubdatatable>
			</row>
		</comparisons>
	</row>
</result>
END;
        $this->assertEquals($expectedXml, $xmlContent);
    }

    public function test_compareTables_shouldCompareTwoDataTableMapsCorrectly()
    {
        $tableSet1 = $this->makeTableMap([
            '2012-01-01' => [
                ['label' => 'row1', 'nb_visits' => 10, 'nb_actions' => 15],
                ['label' => 'row2', 'nb_visits' => 15, 'nb_actions' => 15],
                ['label' => 'row3', 'nb_visits' => 20, 'nb_actions' => 10],
            ],
            '2012-02-01' => [
                ['label' => 'row2', 'nb_visits' => 25, 'nb_actions' => 25],
            ],
            '2012-03-01' => [
                ['label' => 'row3', 'nb_visits' => 20, 'nb_actions' => 10],
                ['label' => 'row4', 'nb_visits' => 40, 'nb_actions' => 50],
            ],
        ]);

        $tableSet2 = $this->makeTableMap([
            '2012-01-01' => [
                ['label' => 'row1', 'nb_visits' => 10, 'nb_actions' => 15],
            ],
            '2012-02-01' => [
                ['label' => 'row2', 'nb_visits' => 15, 'nb_actions' => 15],
                ['label' => 'row3', 'nb_visits' => 20, 'nb_actions' => 10],
            ],
            '2012-03-01' => [
                ['label' => 'row2', 'nb_visits' => 25, 'nb_actions' => 25],
                ['label' => 'row3', 'nb_visits' => 20, 'nb_actions' => 10],
                ['label' => 'row4', 'nb_visits' => 40, 'nb_actions' => 50],
            ],
        ]);

        $compareMetadata = [
            'compareSegment' => self::OTHER_SEGMENT,
            'comparePeriod' => 'month',
            'compareDate' => '2012-01-01,2012-03-01',
        ];

        $comparisonRowGenerator = new ComparisonRowGenerator('reportSegment', false, []);
        $comparisonRowGenerator->compareTables($compareMetadata, $tableSet1, $tableSet2);

        $xmlContent = $this->toXml($tableSet1);

        $expectedXml = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result date="2012-01">
		<row>
			<label>row1</label>
			<nb_visits>10</nb_visits>
			<nb_actions>15</nb_actions>
			<comparisons>
				<row>
					<nb_visits>10</nb_visits>
					<nb_actions>15</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
		<row>
			<label>row2</label>
			<nb_visits>15</nb_visits>
			<nb_actions>15</nb_actions>
			<comparisons>
				<row>
					<nb_visits>0</nb_visits>
					<nb_actions>0</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
		<row>
			<label>row3</label>
			<nb_visits>20</nb_visits>
			<nb_actions>10</nb_actions>
			<comparisons>
				<row>
					<nb_visits>0</nb_visits>
					<nb_actions>0</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
	</result>
	<result date="2012-02">
		<row>
			<label>row2</label>
			<nb_visits>25</nb_visits>
			<nb_actions>25</nb_actions>
			<comparisons>
				<row>
					<nb_visits>15</nb_visits>
					<nb_actions>15</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
	</result>
	<result date="2012-03">
		<row>
			<label>row3</label>
			<nb_visits>20</nb_visits>
			<nb_actions>10</nb_actions>
			<comparisons>
				<row>
					<nb_visits>20</nb_visits>
					<nb_actions>10</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
		<row>
			<label>row4</label>
			<nb_visits>40</nb_visits>
			<nb_actions>50</nb_actions>
			<comparisons>
				<row>
					<nb_visits>40</nb_visits>
					<nb_actions>50</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
	</result>
</results>
END;
        $this->assertEquals($expectedXml, $xmlContent);
    }

    public function test_compareTables_shouldCompareTwoDataTaleMapsOfDifferentLengthsCorrectly_whenFirstIsLonger()
    {
        $tableSet1 = $this->makeTableMap([
            '2012-01-01' => [
                ['label' => 'row1', 'nb_visits' => 10, 'nb_actions' => 15],
                ['label' => 'row2', 'nb_visits' => 15, 'nb_actions' => 15],
                ['label' => 'row3', 'nb_visits' => 20, 'nb_actions' => 10],
            ],
            '2012-02-01' => [
                ['label' => 'row2', 'nb_visits' => 25, 'nb_actions' => 25],
            ],
            '2012-03-01' => [
                ['label' => 'row3', 'nb_visits' => 20, 'nb_actions' => 10],
                ['label' => 'row4', 'nb_visits' => 40, 'nb_actions' => 50],
            ],
        ]);

        $tableSet2 = $this->makeTableMap([
            '2012-01-01' => [
                ['label' => 'row1', 'nb_visits' => 10, 'nb_actions' => 15],
            ],
            '2012-02-01' => [
                // empty
            ],
        ]);

        $compareMetadata = [
            'compareSegment' => self::OTHER_SEGMENT,
            'comparePeriod' => 'month',
            'compareDate' => '2012-01-01,2012-03-01',
        ];

        $comparisonRowGenerator = new ComparisonRowGenerator('reportSegment', false, []);
        $comparisonRowGenerator->compareTables($compareMetadata, $tableSet1, $tableSet2);

        $xmlContent = $this->toXml($tableSet1);

        $expectedXml = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result date="2012-01">
		<row>
			<label>row1</label>
			<nb_visits>10</nb_visits>
			<nb_actions>15</nb_actions>
			<comparisons>
				<row>
					<nb_visits>10</nb_visits>
					<nb_actions>15</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
		<row>
			<label>row2</label>
			<nb_visits>15</nb_visits>
			<nb_actions>15</nb_actions>
			<comparisons>
				<row>
					<nb_visits>0</nb_visits>
					<nb_actions>0</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
		<row>
			<label>row3</label>
			<nb_visits>20</nb_visits>
			<nb_actions>10</nb_actions>
			<comparisons>
				<row>
					<nb_visits>0</nb_visits>
					<nb_actions>0</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
	</result>
	<result date="2012-02">
		<row>
			<label>row2</label>
			<nb_visits>25</nb_visits>
			<nb_actions>25</nb_actions>
			<comparisons>
				<row>
					<nb_visits>0</nb_visits>
					<nb_actions>0</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
	</result>
	<result date="2012-03">
		<row>
			<label>row3</label>
			<nb_visits>20</nb_visits>
			<nb_actions>10</nb_actions>
			<comparisons>
				<row>
					<nb_visits>0</nb_visits>
					<nb_actions>0</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
		<row>
			<label>row4</label>
			<nb_visits>40</nb_visits>
			<nb_actions>50</nb_actions>
			<comparisons>
				<row>
					<nb_visits>0</nb_visits>
					<nb_actions>0</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
	</result>
</results>
END;
        $this->assertEquals($expectedXml, $xmlContent);
    }

    public function test_compareTables_shouldCompareTwoDataTaleMapsOfDifferentLengthsCorrectly_whenFirstIsShorter()
    {
        $tableSet1 = $this->makeTableMap([
            '2012-01-01' => [
                ['label' => 'row1', 'nb_visits' => 10, 'nb_actions' => 15],
                ['label' => 'row2', 'nb_visits' => 15, 'nb_actions' => 15],
                ['label' => 'row3', 'nb_visits' => 20, 'nb_actions' => 10],
            ],
            '2012-02-01' => [
                // empty
            ],
        ]);

        $tableSet2 = $this->makeTableMap([
            '2012-01-01' => [
                ['label' => 'row1', 'nb_visits' => 10, 'nb_actions' => 15],
            ],
            '2012-02-01' => [
                ['label' => 'row2', 'nb_visits' => 15, 'nb_actions' => 15],
                ['label' => 'row3', 'nb_visits' => 20, 'nb_actions' => 10],
            ],
            '2012-03-01' => [
                ['label' => 'row2', 'nb_visits' => 25, 'nb_actions' => 25],
                ['label' => 'row3', 'nb_visits' => 20, 'nb_actions' => 10],
                ['label' => 'row4', 'nb_visits' => 40, 'nb_actions' => 50],
            ],
        ]);

        $compareMetadata = [
            'compareSegment' => self::OTHER_SEGMENT,
            'comparePeriod' => 'month',
            'compareDate' => '2012-01-01,2012-03-01',
        ];

        $comparisonRowGenerator = new ComparisonRowGenerator('reportSegment', false, []);
        $comparisonRowGenerator->compareTables($compareMetadata, $tableSet1, $tableSet2);

        $xmlContent = $this->toXml($tableSet1);

        $expectedXml = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result date="2012-01">
		<row>
			<label>row1</label>
			<nb_visits>10</nb_visits>
			<nb_actions>15</nb_actions>
			<comparisons>
				<row>
					<nb_visits>10</nb_visits>
					<nb_actions>15</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
		<row>
			<label>row2</label>
			<nb_visits>15</nb_visits>
			<nb_actions>15</nb_actions>
			<comparisons>
				<row>
					<nb_visits>0</nb_visits>
					<nb_actions>0</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
		<row>
			<label>row3</label>
			<nb_visits>20</nb_visits>
			<nb_actions>10</nb_actions>
			<comparisons>
				<row>
					<nb_visits>0</nb_visits>
					<nb_actions>0</nb_actions>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
	</result>
	<result date="2012-02">
		<row>
			<comparisons>
				<row>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
	</result>
	<result date="2012-03">
		<row>
			<comparisons>
				<row>
					<compareSegment>operatingSystemCode=WIN</compareSegment>
					<comparePeriod>month</comparePeriod>
					<compareDate>2012-01-01,2012-03-01</compareDate>
					<idsubdatatable>-1</idsubdatatable>
				</row>
			</comparisons>
		</row>
	</result>
</results>
END;
        $this->assertEquals($expectedXml, $xmlContent);
    }

    private function makeTable(array $rows)
    {
        $table = new DataTable();
        $table->addRowsFromSimpleArray($rows);
        return $table;
    }

    private function makeTableMap(array $tableRows)
    {
        $result = new DataTable\Map();
        $result->setKeyName('date');
        foreach ($tableRows as $label => $rows) {
            $period = Factory::build('month', $label);

            $table = $this->makeTable($rows);
            $table->setMetadata('period', $period);

            $result->addTable($table, $period->getPrettyString());
        }
        return $result;
    }

    private function toXml(DataTable\DataTableInterface $table)
    {
        $renderer = new DataTable\Renderer\Xml();
        $renderer->setTable($table);
        return $renderer->render();
    }
}
