<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updater\Migration\Db;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater\Migration\Db\Factory;

/**
 * Test where migrations are actually executed.
 *
 * @group Core
 * @group Updater
 * @group Migration
 * @group SqlTest
 */
class MigrationsTest extends IntegrationTestCase
{
    /**
     * @var Factory
     */
    private $factory;

    private $testTable = 'tablename';
    private $testTablePrefixed = '';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::dropTestTableIfNeeded();
    }

    public static function tearDownAfterClass(): void
    {
        self::dropTestTableIfNeeded();

        parent::tearDownAfterClass();
    }

    private static function dropTestTableIfNeeded()
    {
        $table = Common::prefixTable('tablename');
        Db::exec("DROP TABLE IF EXISTS `$table`");
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->testTablePrefixed = Common::prefixTable($this->testTable);
        $this->factory = new Factory();
    }

    public function test_createTable()
    {
        $columns = array('column1' => 'VARCHAR(200) DEFAULT ""', 'column2' => 'INT(11) NOT NULL');
        $this->factory->createTable($this->testTable, $columns)->exec();

        $this->assertTableIsInstalled();
        $this->assertSame(array('column1', 'column2'), $this->getInstalledColumnNames());
    }

    /**
     * @depends test_createTable
     */
    public function test_addColumn()
    {
        $this->factory->addColumn($this->testTable, 'column3', 'SMALLINT(1)')->exec();

        $this->assertSame(array('column1', 'column2', 'column3'), $this->getInstalledColumnNames());
    }

    /**
     * @depends test_addColumn
     */
    public function test_addIndex()
    {
        $this->factory->addIndex($this->testTable, array('column1', 'column3'))->exec();

        $index = $this->getIndexesDefinedOnTable();

        $this->assertCount(2, $index);
        $this->assertSame('column1', $index[0]['Column_name']);
        $this->assertSame('index_column1_column3', $index[0]['Key_name']);
        $this->assertSame('column3', $index[1]['Column_name']);
        $this->assertSame('index_column1_column3', $index[1]['Key_name']);
    }

    /**
     * @depends test_addIndex
     */
    public function test_dropIndex()
    {
        $this->factory->dropIndex($this->testTable, 'index_column1_column3')->exec();

        $index = $this->getIndexesDefinedOnTable();

        $this->assertSame(array(), $index);
    }

    /**
     * @depends test_dropIndex
     */
    public function test_addUniqueKey()
    {
        $this->factory->addUniqueKey($this->testTable, array('column1', 'column3'), 'custom_name')->exec();

        $index = $this->getIndexesDefinedOnTable();

        $this->assertCount(2, $index);
        $this->assertSame('column1', $index[0]['Column_name']);
        $this->assertSame('custom_name', $index[0]['Key_name']);
        $this->assertSame('column3', $index[1]['Column_name']);
        $this->assertSame('custom_name', $index[1]['Key_name']);
    }

    /**
     * @depends test_addUniqueKey
     */
    public function test_addPrimaryIndex()
    {
        $this->factory->addPrimaryKey($this->testTable, array('column3'))->exec();

        $index = Db::fetchAll("SHOW INDEX FROM {$this->testTablePrefixed} WHERE Key_name = 'PRIMARY'");

        $this->assertCount(1, $index);
        $this->assertSame('column3', $index[0]['Column_name']);
    }

    /**
     * @depends test_addPrimaryIndex
     */
    public function test_dropPrimaryKey()
    {
        $index = Db::fetchAll("SHOW INDEX FROM {$this->testTablePrefixed} WHERE Key_name = 'PRIMARY'");
        $this->assertCount(1, $index);

        $this->factory->dropPrimaryKey($this->testTable)->exec();

        $index = Db::fetchAll("SHOW INDEX FROM {$this->testTablePrefixed} WHERE Key_name = 'PRIMARY'");
        $this->assertCount(0, $index);
    }

    /**
     * @depends test_addPrimaryIndex
     */
    public function test_changeColumnType()
    {
        self::expectNotToPerformAssertions();

        $this->factory->changeColumnType($this->testTable, 'column2', 'SMALLINT(4) NOT NULL')->exec();
    }

    /**
     * @depends test_changeColumnType
     */
    public function test_insert()
    {
        $values = array(
            'column1' => 'my text',
            'column2' => '554934',
            'column3' => '1'
        );
        $this->factory->insert($this->testTable, $values)->exec();

        $row = $this->fetchRow();

        $values['column2'] = 32767; // because we changed type to smallint before
        $this->assertEquals($values, $row);
    }

    /**
     * @depends test_insert
     */
    public function test_sql()
    {
        $this->factory->sql("ALTER TABLE {$this->testTablePrefixed} CHANGE COLUMN `column2` `column5` SMALLINT(4) NOT NULL")->exec();

        $this->assertSame(array('column1', 'column5', 'column3'), $this->getInstalledColumnNames());
    }

    /**
     * @depends test_sql
     */
    public function test_addColumns()
    {
        $this->factory->addColumns($this->testTable, array(
            'column10' => 'VARCHAR(255) DEFAULT ""',
            'column11' => 'VARCHAR(55) DEFAULT ""',
        ))->exec();

        $this->assertSame(array('column1', 'column5', 'column3', 'column10', 'column11'), $this->getInstalledColumnNames());
    }

    /**
     * @depends test_addColumns
     */
    public function test_changeColumnTypes()
    {
        self::expectNotToPerformAssertions();

        $this->factory->changeColumnTypes($this->testTable, array(
            'column5' => 'VARCHAR(10) DEFAULT ""',
            'column11' => 'VARCHAR(255) DEFAULT "test"',
        ))->exec();
    }

    /**
     * @depends test_changeColumnTypes
     */
    public function test_dropColumn()
    {
        $this->factory->dropColumn($this->testTable, 'column10')->exec();

        $this->assertSame(array('column1', 'column5', 'column3', 'column11'), $this->getInstalledColumnNames());
    }

    /**
     * @depends test_dropColumn
     */
    public function test_changeColumn()
    {
        $this->factory->changeColumn($this->testTable, 'column11', 'column12', 'VARCHAR(255)')->exec();

        $this->assertSame(array('column1', 'column5', 'column3', 'column12'), $this->getInstalledColumnNames());
    }

    /**
     * @depends test_changeColumn
     */
    public function test_dropTable()
    {
        $this->factory->dropTable($this->testTable)->exec();

        $this->assertTableIsNotInstalled();
    }

    public function test_dropColumns()
    {
        DbHelper::createTable('foobarbaz', 'barbaz VARCHAR(1), foobaz VARCHAR(1), foobaz2 VARCHAR(1)');
        $this->factory->dropColumns('foobarbaz', array('column10', 'barbaz', 'column3', 'foobaz'))->exec();

        $columns = DbHelper::getTableColumns(Common::prefixTable('foobarbaz'));
        $columns = array_keys($columns);

        $this->assertSame(array('foobaz2'), $columns);
    }

    private function fetchRow()
    {
        return Db::fetchRow("SELECT * FROM {$this->testTablePrefixed}");
    }

    private function assertTableIsInstalled()
    {
        $this->assertNotEmpty($this->getInstalledTable());
    }

    private function assertTableIsNotInstalled()
    {
        $this->assertEmpty($this->getInstalledTable());
    }

    private function getInstalledTable()
    {
        return Db::fetchAll("SHOW TABLES LIKE '{$this->testTablePrefixed}'");
    }

    private function getInstalledColumnNames()
    {
        $columns = DbHelper::getTableColumns($this->testTablePrefixed);
        return array_keys($columns);
    }

    private function getIndexesDefinedOnTable()
    {
        return Db::fetchAll("SHOW INDEX FROM {$this->testTablePrefixed}");
    }
}
