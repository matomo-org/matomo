<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\Common;
use Piwik\DataAccess\TableMetadata;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class TableMetadataTest extends IntegrationTestCase
{
    /**
     * @var TableMetadata
     */
    private $tableMetadataAccess;

    public function setUp(): void
    {
        parent::setUp();

        $this->tableMetadataAccess = new TableMetadata();
    }

    public function test_getColumns_CorrectlyReturnsListOfColumnNames()
    {
        $expectedColumns = array('option_name', 'option_value', 'autoload');
        $columns = $this->tableMetadataAccess->getColumns(Common::prefixTable('option'));
        $this->assertEquals($expectedColumns, $columns);
    }

    /**
     * @dataProvider getTablesWithIdActionColumnsToTest
     */
    public function test_getIdActionColumnNames_CorrectlyReturnsColumnsWithIdActionName($table, $expectedColumns)
    {
        $columns = $this->tableMetadataAccess->getIdActionColumnNames(Common::prefixTable($table));
        $this->assertEquals($expectedColumns, $columns);
    }

    public function getTablesWithIdActionColumnsToTest()
    {
        return array(
            array('log_conversion', array('idaction_url')),
            array('log_conversion_item', array('idaction_sku', 'idaction_name', 'idaction_category', 'idaction_category2',
                                               'idaction_category3', 'idaction_category4', 'idaction_category5'))
        );
    }
}
