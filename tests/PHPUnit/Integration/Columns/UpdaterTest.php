<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Test\Columns;

use Piwik\Columns\Updater as ColumnsUpdater;
use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater\Migration;

// NOTE: we can't use PHPUnit mock framework since we have to set columnName/columnType. reflection will set it, but
// for some reason, methods of base type don't see the set value.
class MockVisitDimension extends VisitDimension
{
    public function __construct($columnName, $columnType)
    {
        $this->columnName = $columnName;
        $this->columnType = $columnType;
    }
}

class MockActionDimension extends ActionDimension
{
    public function __construct($columnName, $columnType)
    {
        $this->columnName = $columnName;
        $this->columnType = $columnType;
    }
}

class MockConversionDimension extends ConversionDimension
{
    public function __construct($columnName, $columnType)
    {
        $this->columnName = $columnName;
        $this->columnType = $columnType;
    }
}

/**
 * @group Core
 */
class UpdaterTest extends IntegrationTestCase
{
    private $tableColumnsCache = array(); // for test performance

    /**
     * @var ColumnsUpdater
     */
    private $columnsUpdater;

    public function setUp(): void
    {
        parent::setUp();

        // recreate log_visit/log_link_visit_action/log_conversion tables w/o any dimensions
        $tablesToRecreate = array('log_visit', 'log_link_visit_action', 'log_conversion');
        foreach ($tablesToRecreate as $table) {
            Db::exec("DROP TABLE `" . Common::prefixTable($table) . "`");

            $tableCreateSql = DbHelper::getTableCreateSql($table);
            Db::exec($tableCreateSql);
        }

        $visitDimensions = array(
            $this->getMockVisitDimension("test_visit_col_1", "INTEGER(10) UNSIGNED NOT NULL"),
            $this->getMockVisitDimension("test_visit_col_2", "VARCHAR(32) NOT NULL")
        );

        $actionDimensions = array(
            $this->getMockActionDimension("test_action_col_1", "VARCHAR(32) NOT NULL"),
            $this->getMockActionDimension("test_action_col_2", "INTEGER(10) UNSIGNED DEFAULT NULL")
        );

        $conversionDimensions = array(
            $this->getMockConversionDimension("test_conv_col_1", "FLOAT DEFAULT NULL"),
            $this->getMockConversionDimension("test_conv_col_2", "VARCHAR(32) NOT NULL")
        );

        $this->columnsUpdater = new ColumnsUpdater($visitDimensions, $actionDimensions, $conversionDimensions);

        $this->tableColumnsCache = array();
    }


    /**
     * @param Migration\Db\Sql[] $queries
     * @return array
     */
    private function flattenQueries($queries)
    {
        $response = array();
        foreach ($queries as $query) {
            $response[$query->__toString()] = $query->getErrorCodesToIgnore();
        }
        return $response;
    }

    public function testDoUpdateAddsDimensionsWhenDimensionsNotInTables()
    {
        $updater = $this->getMockUpdater();
        $this->columnsUpdater->doUpdate($updater);

        $this->assertDimensionsAddedToTables();
    }

    public function testDoUpdateDoesNotErrorWhenDimensionsAlreadyInTables()
    {
        $this->addDimensionsToTables();

        $updater = $this->getMockUpdater();
        $this->columnsUpdater->doUpdate($updater);

        $this->assertDimensionsAddedToTables();
    }

    public function testGetAllVersionsReturnsFileVersionsOfAllDimensions()
    {
        $updater = $this->getMockUpdater();
        $actualVersions = $this->columnsUpdater->getAllVersions($updater);

        $expectedVersions = array(
            'log_visit.test_visit_col_1' => 'INTEGER(10) UNSIGNED NOT NULL',
            'log_visit.test_visit_col_2' => 'VARCHAR(32) NOT NULL',
            'log_link_visit_action.test_action_col_1' => 'VARCHAR(32) NOT NULL',
            'log_link_visit_action.test_action_col_2' => 'INTEGER(10) UNSIGNED DEFAULT NULL',
            'log_conversion.test_conv_col_1' => 'FLOAT DEFAULT NULL',
            'log_conversion.test_conv_col_2' => 'VARCHAR(32) NOT NULL'
        );
        $this->assertEquals($actualVersions, $expectedVersions);
    }

    /**
     * @dataProvider getCoreDimensionsForGetAllVersionsTest
     */
    public function testGetAllVersionsReturnsNoVersionsForCoreDimensionsThatWereRefactoredAndHaveNoDbVersion($table, $columnName, $columnType)
    {
        $this->addDimensionsToTables();
        $this->addDimensionToTable($table, $columnName, $columnType);

        $updater = $this->getMockUpdater();
        $actualVersions = $this->columnsUpdater->getAllVersions($updater);

        $expectedVersions = array(
            'log_visit.test_visit_col_1' => 'INTEGER(10) UNSIGNED NOT NULL',
            'log_visit.test_visit_col_2' => 'VARCHAR(32) NOT NULL',
            'log_link_visit_action.test_action_col_1' => 'VARCHAR(32) NOT NULL',
            'log_link_visit_action.test_action_col_2' => 'INTEGER(10) UNSIGNED DEFAULT NULL',
            'log_conversion.test_conv_col_1' => 'FLOAT DEFAULT NULL',
            'log_conversion.test_conv_col_2' => 'VARCHAR(32) NOT NULL'
        );
        $this->assertEquals($actualVersions, $expectedVersions);
    }

    public function getCoreDimensionsForGetAllVersionsTest()
    {
        // only one test per table. otherwise test will be too slow (~2 mins for all).
        return array(
            array('log_visit', 'user_id', 'VARCHAR(200) NULL'),
            array('log_link_visit_action', 'idaction_event_category', 'INTEGER(10) UNSIGNED DEFAULT NULL'),
        );
    }

    private function getMockVisitDimension($columnName, $columnType)
    {
        return new MockVisitDimension($columnName, $columnType);
    }

    private function getMockActionDimension($columnName, $columnType)
    {
        return new MockActionDimension($columnName, $columnType);
    }

    private function getMockConversionDimension($columnName, $columnType)
    {
        return new MockConversionDimension($columnName, $columnType);
    }

    private function getMockUpdater($hasNewVersion = true)
    {
        $result = $this->getMockBuilder("Piwik\\Updater")->onlyMethods(array('hasNewVersion'))->getMock();

        $result->expects($this->any())->method('hasNewVersion')->will($this->returnCallback(function () use ($hasNewVersion) {
            return $hasNewVersion;
        }));

        return $result;
    }

    private function assertDimensionsAddedToTables()
    {
        $this->assertTableHasColumn('log_visit', 'test_visit_col_1', 'int(10) unsigned', $allowNull = false);
        $this->assertTableHasColumn('log_visit', 'test_visit_col_2', 'varchar(32)', $allowNull = false);

        $this->assertTableHasColumn('log_link_visit_action', 'test_action_col_1', 'varchar(32)', $allowNull = false);
        $this->assertTableHasColumn('log_link_visit_action', 'test_action_col_2', 'int(10) unsigned', $allowNull = true);

        $this->assertTableHasColumn('log_conversion', 'test_conv_col_1', 'float', $allowNull = true);
        $this->assertTableHasColumn('log_conversion', 'test_conv_col_2', 'varchar(32)', $allowNull = false);
    }

    private function assertTableHasColumn($table, $columnName, $columnType, $allowNull)
    {
        $column = $this->getTableColumnInfo($table, $columnName);

        $this->assertNotNull($column, "Column '$columnName' does not exist in '$table'.");

        $this->assertEquals(strtolower($columnType), strtolower($column['Type']));
        if ($allowNull) {
            $this->assertEquals("yes", strtolower($column['Null']));
        } else {
            $this->assertEquals("no", strtolower($column['Null']));
        }
    }

    private function getTableColumns($table)
    {
        if (empty($this->tableColumnsCache[$table])) {
            $this->tableColumnsCache[$table] = Db::fetchAll("SHOW COLUMNS IN `" . Common::prefixTable($table) . "`");
        }
        return $this->tableColumnsCache[$table];
    }

    private function getTableColumnInfo($table, $columnName)
    {
        $columns = $this->getTableColumns($table);
        foreach ($columns as $row) {
            if ($row['Field'] == $columnName) {
                return $row;
            }
        }
        return null;
    }

    private function addDimensionsToTables()
    {
        $this->addDimensionToTable('log_visit', 'test_visit_col_1', "INTEGER UNSIGNED NOT NULL");
        $this->addDimensionToTable('log_visit', 'test_visit_col_2', "VARCHAR(32) NOT NULL");

        $this->addDimensionToTable('log_link_visit_action', 'test_action_col_1', "VARCHAR(32) NOT NULL");
        $this->addDimensionToTable('log_link_visit_action', 'test_action_col_2', "INTEGER(10) UNSIGNED DEFAULT NULL");

        $this->addDimensionToTable('log_conversion', 'test_conv_col_1', "FLOAT DEFAULT NULL");
        $this->addDimensionToTable('log_conversion', 'test_conv_col_2', "VARCHAR(32) NOT NULL");
    }

    private function addDimensionToTable($table, $column, $type)
    {
        Db::exec("ALTER TABLE `" . Common::prefixTable($table) . "` ADD COLUMN $column $type");
    }
}
