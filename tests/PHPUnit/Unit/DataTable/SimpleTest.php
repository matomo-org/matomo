<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\DataTable\Simple;

class SimpleTest extends \PHPUnit\Framework\TestCase
{
    public function testSerializeIncludesAllRequiredData()
    {
        $dataTable = new Simple();
        $dataTable->addRowFromSimpleArray([
            'column1' => 'value1',
            'column2' => 'value2',
        ]);
        $dataTable->addSummaryRow(new Row([
            Row::COLUMNS => ['column1' => 'total1', 'column2' => 'total2']
        ]));
        $dataTable->setAllTableMetadata([
            'metadataKey1' => 10,
            'metadataKey2' => ['a', 'b', 'c'],
        ]);

        $serialized = serialize($dataTable);

        /** @var Simple $unserialized */
        $unserialized = unserialize($serialized);

        $this->assertEquals(1, $unserialized->getRowsCountWithoutSummaryRow());
        $this->assertEquals([
            'column1' => 'value1',
            'column2' => 'value2',
        ], $unserialized->getRows()[0]->getColumns());

        $this->assertEquals(2, $unserialized->getRowsCount());
        $this->assertEquals(['column1' => 'total1', 'column2' => 'total2'], $unserialized->getRows()[DataTable::ID_SUMMARY_ROW]->getColumns());

        $this->assertEquals([
            'metadataKey1' => 10,
            'metadataKey2' => ['a', 'b', 'c'],
        ], $unserialized->getAllTableMetadata());
    }
}
