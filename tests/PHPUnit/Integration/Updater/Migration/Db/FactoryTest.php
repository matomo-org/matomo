<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updater\Migration\Db;

use Piwik\Common;
use Piwik\Db\Schema;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater\Migration\Db\AddColumn;
use Piwik\Updater\Migration\Db\AddColumns;
use Piwik\Updater\Migration\Db\AddIndex;
use Piwik\Updater\Migration\Db\AddPrimaryKey;
use Piwik\Updater\Migration\Db\AddUniqueKey;
use Piwik\Updater\Migration\Db\BatchInsert;
use Piwik\Updater\Migration\Db\BoundSql;
use Piwik\Updater\Migration\Db\ChangeColumn;
use Piwik\Updater\Migration\Db\ChangeColumnType;
use Piwik\Updater\Migration\Db\ChangeColumnTypes;
use Piwik\Updater\Migration\Db\CreateTable;
use Piwik\Updater\Migration\Db\DropColumn;
use Piwik\Updater\Migration\Db\DropIndex;
use Piwik\Updater\Migration\Db\DropTable;
use Piwik\Updater\Migration\Db\Factory;
use Piwik\Updater\Migration\Db\Insert;
use Piwik\Updater\Migration\Db\Sql;

/**
 * @group Core
 * @group Updater
 * @group Migration
 */
class FactoryTest extends IntegrationTestCase
{
    /**
     * @var Factory
     */
    private $factory;

    private $testTable = 'tablename';
    private $testTablePrefixed = '';

    public function setUp(): void
    {
        parent::setUp();

        $this->testTablePrefixed = Common::prefixTable($this->testTable);
        $this->factory = new Factory();
    }

    public function testSqlReturnsSqlInstance()
    {
        $migration = $this->sql();

        $this->assertTrue($migration instanceof Sql);
    }

    public function testSqlForwardsQueryAndErrorCode()
    {
        $migration = $this->sql();

        $this->assertSame('SELECT 1;', '' . $migration);
        $this->assertSame(array(5), $migration->getErrorCodesToIgnore());
    }

    public function testBoundSqlReturnsSqlInstance()
    {
        $migration = $this->boundSql();

        $this->assertTrue($migration instanceof BoundSql);
    }

    public function testBoundSqlForwardsParameters()
    {
        $migration = $this->boundSql();

        $this->assertSame("SELECT 2 WHERE 'query';", '' . $migration);
        $this->assertSame(array(8), $migration->getErrorCodesToIgnore());
    }

    public function testCreateTableReturnsCreateTableInstance()
    {
        $migration = $this->createTable();

        $this->assertTrue($migration instanceof CreateTable);
    }

    public function testCreateTableForwardsParameters()
    {
        $migration = $this->createTable();

        $table = $this->testTablePrefixed;
        $createOptions = Schema::getInstance()->getTableCreateOptions();
        self::assertStringContainsString('ROW_FORMAT=DYNAMIC', $createOptions);
        $this->assertSame("CREATE TABLE `$table` (`column` INT(10) DEFAULT 0, `column2` VARCHAR(255)) $createOptions;", '' . $migration);
    }


    public function testCreateTableWithPrimaryKey()
    {
        $migration = $this->createTable('column2');

        $table = $this->testTablePrefixed;
        $createOptions = Schema::getInstance()->getTableCreateOptions();
        self::assertStringContainsString('ROW_FORMAT=DYNAMIC', $createOptions);
        $this->assertSame("CREATE TABLE `$table` (`column` INT(10) DEFAULT 0, `column2` VARCHAR(255), PRIMARY KEY ( `column2` )) $createOptions;", '' . $migration);
    }

    public function testDropTableReturnsDropTableInstance()
    {
        $migration = $this->factory->dropTable($this->testTable);

        $this->assertTrue($migration instanceof DropTable);
    }

    public function testDropTableForwardsParameters()
    {
        $migration = $this->factory->dropTable($this->testTable);

        $table = $this->testTablePrefixed;
        $this->assertSame("DROP TABLE IF EXISTS `$table`;", '' . $migration);
    }

    public function testDropColumnReturnsDropColumnInstance()
    {
        $migration = $this->dropColumn();

        $this->assertTrue($migration instanceof DropColumn);
    }

    public function testDropColumnForwardsParameters()
    {
        $migration = $this->dropColumn();

        $table = $this->testTablePrefixed;
        $this->assertSame("ALTER TABLE `$table` DROP COLUMN `column1`;", '' . $migration);
    }

    public function testAddColumnForwardsParametersWithLastColumn()
    {
        $migration = $this->addColumn('lastcolumn');

        $table = $this->testTablePrefixed;
        $this->assertSame("ALTER TABLE `$table` ADD COLUMN `column` INT(10) DEFAULT 0 AFTER `lastcolumn`;", '' . $migration);
    }

    public function testAddColumnReturnsAddColumnInstance()
    {
        $migration = $this->addColumn(null);

        $this->assertTrue($migration instanceof AddColumn);
    }

    public function testAddColumnForwardsParametersNoLastColumn()
    {
        $migration = $this->addColumn(null);

        $table = $this->testTablePrefixed;
        $this->assertSame("ALTER TABLE `$table` ADD COLUMN `column` INT(10) DEFAULT 0;", '' . $migration);
    }

    public function testAddColumnsReturnsAddColumnsInstance()
    {
        $migration = $this->addColumns(null);

        $this->assertTrue($migration instanceof AddColumns);
    }

    public function testAddColumnsForwardsParameters()
    {
        $migration = $this->addColumns('columnafter');

        $table = $this->testTablePrefixed;

        $expectedStatement = "ALTER TABLE `$table` ADD COLUMN `column1` INT(10) DEFAULT 0 AFTER `columnafter`, ADD COLUMN `column2` VARCHAR(10) DEFAULT \"\" AFTER `column1`;";

        if (!Schema::getInstance()->supportsComplexColumnUpdates()) {
            $expectedStatement = "ALTER TABLE `$table` ADD COLUMN `column1` INT(10) DEFAULT 0 AFTER `columnafter`;ALTER TABLE `$table` ADD COLUMN `column2` VARCHAR(10) DEFAULT \"\" AFTER `column1`;";
        }

        $this->assertSame($expectedStatement, '' . $migration);
    }

    public function testAddColumnsNoAfterColumn()
    {
        $migration = $this->addColumns(null);

        $table = $this->testTablePrefixed;

        $expectedStatement = "ALTER TABLE `$table` ADD COLUMN `column1` INT(10) DEFAULT 0, ADD COLUMN `column2` VARCHAR(10) DEFAULT \"\";";

        if (!Schema::getInstance()->supportsComplexColumnUpdates()) {
            $expectedStatement = "ALTER TABLE `$table` ADD COLUMN `column1` INT(10) DEFAULT 0;ALTER TABLE `$table` ADD COLUMN `column2` VARCHAR(10) DEFAULT \"\";";
        }

        $this->assertSame($expectedStatement, '' . $migration);
    }

    public function testChangeColumnReturnsChangeColumnInstance()
    {
        $migration = $this->changeColumn();

        $this->assertTrue($migration instanceof ChangeColumn);
    }

    public function testChangeColumnForwardsParameters()
    {
        $migration = $this->changeColumn();

        $table = $this->testTablePrefixed;
        $this->assertSame("ALTER TABLE `$table` CHANGE `column_old` `column_new` INT(10) DEFAULT 0;", '' . $migration);
    }

    public function testChangeColumnTypeReturnsChangeColumnTypeInstance()
    {
        $migration = $this->changeColumnType();

        $this->assertTrue($migration instanceof ChangeColumnType);
    }

    public function testChangeColumnTypeForwardsParameters()
    {
        $migration = $this->changeColumnType();

        $table = $this->testTablePrefixed;
        $this->assertSame("ALTER TABLE `$table` CHANGE `column` `column` INT(10) DEFAULT 0;", '' . $migration);
    }

    public function testChangeColumnTypesReturnsChangeColumnTypesInstance()
    {
        $migration = $this->changeColumnTypes();

        $this->assertTrue($migration instanceof ChangeColumnTypes);
    }

    public function testChangeColumnTypesForwardsParameters()
    {
        $migration = $this->changeColumnTypes();

        $table = $this->testTablePrefixed;
        $this->assertSame("ALTER TABLE `$table` CHANGE `column1` `column1` INT(10) DEFAULT 0, CHANGE `column2` `column2` VARCHAR(10) DEFAULT \"\";", '' . $migration);
    }

    public function testAddIndexReturnsAddIndexInstance()
    {
        $migration = $this->addIndex();

        $this->assertTrue($migration instanceof AddIndex);
    }

    public function testAddIndexForwardsParametersGeneratesIndexNameAutomatically()
    {
        $migration = $this->addIndex();

        $table = $this->testTablePrefixed;
        $this->assertSame("ALTER TABLE `$table` ADD INDEX index_column1_column3 (`column1`, `column3` (10));", '' . $migration);
    }

    public function testAddIndexCustomIndexName()
    {
        $migration = $this->addIndex('myCustomIndex');

        $table = $this->testTablePrefixed;
        $this->assertSame("ALTER TABLE `$table` ADD INDEX myCustomIndex (`column1`, `column3` (10));", '' . $migration);
    }

    public function testAddUniqueKeyReturnsAddUniqueKeyInstance()
    {
        $migration = $this->addUniqueKey();

        $this->assertTrue($migration instanceof AddUniqueKey);
    }

    public function testAddUniqueKeyForwardsParametersGeneratesIndexNameAutomatically()
    {
        $migration = $this->addUniqueKey();

        $table = $this->testTablePrefixed;
        $this->assertSame("ALTER TABLE `$table` ADD UNIQUE KEY unique_column1_column3 (`column1`, `column3` (10));", '' . $migration);
    }

    public function testAddUniqueKeyCustomIndexName()
    {
        $migration = $this->addUniqueKey('myCustomIndex');

        $table = $this->testTablePrefixed;
        $this->assertSame("ALTER TABLE `$table` ADD UNIQUE KEY myCustomIndex (`column1`, `column3` (10));", '' . $migration);
    }

    public function testDropIndexReturnsAddIndexInstance()
    {
        $migration = $this->dropIndex();

        $this->assertTrue($migration instanceof DropIndex);
    }

    public function testAddIndexForwardsParameters()
    {
        $migration = $this->dropIndex();

        $table = $this->testTablePrefixed;
        $this->assertSame("ALTER TABLE `$table` DROP INDEX `index_column1_column5`;", '' . $migration);
    }

    public function testAddPrimaryKey()
    {
        $migration = $this->addPrimaryKey();

        $this->assertTrue($migration instanceof AddPrimaryKey);
    }

    public function testAddPrimaryKeyForwardsParameters()
    {
        $migration = $this->addPrimaryKey();

        $table = $this->testTablePrefixed;
        $this->assertSame("ALTER TABLE `$table` ADD PRIMARY KEY(`column1`, `column2`);", '' . $migration);
    }

    public function testInsertReturnsInsertInstance()
    {
        $migration = $this->insert();

        $this->assertTrue($migration instanceof Insert);
    }

    public function testInsertForwardsParameters()
    {
        $migration = $this->insert();

        $table = $this->testTablePrefixed;
        $this->assertSame("INSERT INTO `$table` (`column1`, `column3`) VALUES ('val1',5);", '' . $migration);
    }

    public function testBatchInsertReturnsBatchInsertInstance()
    {
        $migration = $this->batchInsert();
        $this->assertTrue($migration instanceof BatchInsert);
    }

    public function testBatchInsertForwardsParameters()
    {
        $migration = $this->batchInsert();
        $this->assertSame('<batch insert>', '' . $migration);
        $this->assertSame($this->testTablePrefixed, $migration->getTable());
        $this->assertSame(array('col1'), $migration->getColumnNames());
        $this->assertSame(array(array('val1')), $migration->getValues());
        $this->assertSame('utf8', $migration->getCharset());
        $this->assertTrue($migration->doesThrowException());
    }

    private function sql()
    {
        return $this->factory->sql('SELECT 1;', 5);
    }

    private function boundSql()
    {
        return $this->factory->boundSql('SELECT 2 WHERE ?;', array('column' => 'query'), array(8));
    }

    private function createTable($primaryKey = array())
    {
        return $this->factory->createTable($this->testTable, array('column' => 'INT(10) DEFAULT 0', 'column2' => 'VARCHAR(255)'), $primaryKey);
    }

    private function addColumn($placeAfterColumn)
    {
        return $this->factory->addColumn($this->testTable, 'column', 'INT(10) DEFAULT 0', $placeAfterColumn);
    }

    private function dropColumn()
    {
        return $this->factory->dropColumn($this->testTable, 'column1');
    }

    private function addColumns($placeAfterColumn)
    {
        return $this->factory->addColumns($this->testTable, array(
            'column1' => 'INT(10) DEFAULT 0',
            'column2' => 'VARCHAR(10) DEFAULT ""',
        ), $placeAfterColumn);
    }

    private function changeColumn()
    {
        return $this->factory->changeColumn($this->testTable, 'column_old', 'column_new', 'INT(10) DEFAULT 0');
    }

    private function changeColumnType()
    {
        return $this->factory->changeColumnType($this->testTable, 'column', 'INT(10) DEFAULT 0');
    }

    private function changeColumnTypes()
    {
        return $this->factory->changeColumnTypes($this->testTable, array(
            'column1' => 'INT(10) DEFAULT 0',
            'column2' => 'VARCHAR(10) DEFAULT ""',
        ));
    }

    private function addIndex($customIndex = '')
    {
        return $this->factory->addIndex($this->testTable, array('column1', 'column3 (10)'), $customIndex);
    }

    private function addUniqueKey($customIndex = '')
    {
        return $this->factory->addUniqueKey($this->testTable, array('column1', 'column3 (10)'), $customIndex);
    }

    private function dropIndex()
    {
        return $this->factory->dropIndex($this->testTable, 'index_column1_column5');
    }

    private function addPrimaryKey()
    {
        return $this->factory->addPrimaryKey($this->testTable, array('column1', 'column2'));
    }

    private function insert()
    {
        return $this->factory->insert($this->testTable, array('column1' => 'val1', 'column3' => 5));
    }

    private function batchInsert()
    {
        $columns = array('col1');
        $values = array(array('val1'));
        return $this->factory->batchInsert($this->testTable, $columns, $values, $throwException = true, $charset = 'utf8');
    }
}
