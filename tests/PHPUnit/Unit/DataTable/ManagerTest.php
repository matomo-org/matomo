<?php

namespace Piwik\Tests\Unit\DataTable;

use Piwik\DataTable\Manager;
use Piwik\DataTable;

/**
 * @group DataTable
 * @group ManagerTest
 * @group Core
 */
class ManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Manager
     */
    private $manager;

    public function setUp(): void
    {
        parent::setUp();
        $this->manager = new Manager();
    }

    private function createTestDataTable()
    {
        return new DataTable();
    }

    public function testGetTableShouldThrowExceptionIfTableIdDoesNotExist()
    {
        $this->expectException(\Piwik\DataTable\TableNotFoundException::class);
        $this->expectExceptionMessage('table id 1 not found in memory');

        $this->manager->getTable(1);
    }

    public function testGetTableShouldFindAnExistingTableIfTableExists()
    {
        $table1 = $this->createTestDataTable();
        $this->manager->addTable($table1);

        $table2 = $this->manager->getTable($this->manager->getMostRecentTableId());

        $this->assertSame($table1, $table2);
    }

    public function testAddTableShouldIncreaseTheTableId()
    {
        $table = $this->createTestDataTable();
        $id = $this->manager->addTable($table);

        $this->assertSame(1, $id);

        // another one
        $id = $this->manager->addTable($table);

        $this->assertSame(2, $id);
    }

    public function testGetMostRecentTableIdShouldAlwaysReturnTheMostRecentlyCreatedId()
    {
        $this->assertSame(0, $this->manager->getMostRecentTableId());

        $this->addDataTables(1);
        $this->assertSame(1, $this->manager->getMostRecentTableId());

        // another one
        $this->addDataTables(1);
        $this->assertSame(2, $this->manager->getMostRecentTableId());

        $this->addDataTables(3);
        $this->assertSame(5, $this->manager->getMostRecentTableId());
    }

    public function testSetTableDeletedShouldActuallyUnsetTheTable()
    {
        $this->addDataTables(1);

        $this->manager->setTableDeleted($id = 1);

        $this->assertDataTablesInManager(array(1 => null));
    }

    public function testSetTableDeletedShouldOnlyUnsetOneTableHavingTheGivenId()
    {
        $tables = $this->addDataTables(3);

        $this->manager->setTableDeleted($id = 2);

        $this->assertDataTablesInManager(array(1 => $tables[1], 2 => null, 3 => $tables[3]));
    }

    public function testDeleteTableShouldNotDeleteAnythingIfTableDoesNotExist()
    {
        $tables = $this->addDataTables(1);

        $this->manager->deleteTable($id = 0);
        $this->manager->deleteTable($id = 99);
        $this->manager->deleteTable($id = 5);

        $this->assertDataTablesInManager($tables);
    }

    public function testDeleteTableShouldSetTheGivenDataTableDeletedIfIdExists()
    {
        $tables = $this->addDataTables(3);

        $this->manager->deleteTable($id = 2);

        $this->assertDataTablesInManager(array(1 => $tables[1], 2 => null, 3 => $tables[3]));
    }

    public function testDeleteAllShouldActuallyRemoveAllTables()
    {
        $this->addDataTables(3);

        $this->manager->deleteAll();

        $this->assertDataTablesInManager(array());
    }

    public function testDeleteAllShouldRemoveAllTablesHigherThanTheGivenId()
    {
        $tables = $this->addDataTables(4);

        $this->manager->deleteAll($id = 2);

        $this->assertDataTablesInManager(array(1 => $tables[1], 2 => $tables[2], 3 => null, 4 => null));
    }

    public function testDeleteAllShouldNotRemoveAnythingIfIdIsTooHighToMatchAny()
    {
        $tables = $this->addDataTables(4);

        $this->manager->deleteAll($id = 99);

        $this->assertDataTablesInManager($tables);
    }

    public function testDeleteAllShouldNotAlterTheNextDataTableIdIfAnIdIsGiven()
    {
        $this->addDataTables(4);

        $this->manager->deleteAll($id = 2);

        $this->assertMostRecentDataTableId(4);
    }

    public function testDeleteAllShouldResetNextDataTableIdIfAllTablesAreDeleted()
    {
        $this->addDataTables(4);
        $this->assertMostRecentDataTableId(4); // verify it is not yet 0
        $this->manager->deleteAll();
        $this->assertMostRecentDataTableId(0);

        $this->addDataTables(4);
        $this->assertMostRecentDataTableId(4); // verify it is not yet 0
        $this->manager->deleteAll($id = 0);
        $this->assertMostRecentDataTableId(0);
    }

    public function testDeleteTablesExceptIgnoredShouldRemoveAllTablesButIgnoreTheGivenOnes()
    {
        $tables = $this->addDataTables(8);

        $this->manager->deleteTablesExceptIgnored(array(4, 6), $id = 2);

        $this->assertDataTablesInManager(array(
            1 => $tables[1],
            2 => null,
            3 => null,
            4 => $tables[4], // supposed to be deleted but ignored
            5 => null,
            6 => $tables[6], // ignored as well
            7 => null,
            8 => null
        ));
    }

    public function testDeleteTablesExceptIgnoredShouldRemoveAllTablesIfNoIgnoredAreGiven()
    {
        $tables = $this->addDataTables(5);

        $this->manager->deleteTablesExceptIgnored(array(), $id = 2);

        $this->assertDataTablesInManager(array(
            1 => $tables[1],
            2 => null,
            3 => null,
            4 => null,
            5 => null,
        ));
    }

    public function testDeleteTablesExceptIgnoredShouldNotResetMostRecentDataTableIdEvenWhenDeletingAll()
    {
        $this->addDataTables(5);

        $this->manager->deleteTablesExceptIgnored(array(), $id = 0);
        $this->assertDataTablesInManager(array(
            1 => null,
            2 => null,
            3 => null,
            4 => null,
            5 => null,
        ));

        $this->assertMostRecentDataTableId(5);
    }

    private function assertMostRecentDataTableId($id)
    {
        $this->assertSame($id, $this->manager->getMostRecentTableId());
    }

    private function assertDataTablesInManager($expectedDataTables)
    {
        $this->assertSame($expectedDataTables, $this->manager->getArrayCopy());
    }

    /**
     * @param $numTables
     * @return DataTable[]
     */
    private function addDataTables($numTables)
    {
        $table  = $this->createTestDataTable();
        $tables = array();

        for ($i = 0; $i < $numTables; $i++) {
            $id = $this->manager->addTable($table);
            $tables[$id] = $table;
        }

        return $tables;
    }
}
