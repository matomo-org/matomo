<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class TablePartitioningTest extends DatabaseTestCase
{
    /**
     * test no timestamp => exception
     * @group Core
     * @group TablePartitioning
     */
    public function testNoTimestamp()
    {
        try {
            $p = new Piwik_TablePartitioning_Monthly('testtable');
            $p->getTableName();
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * test table absent => create
     * @group Core
     * @group TablePartitioning
     */
    public function testNoTable()
    {
        $tableName = 'archive_numeric';
        $p = new Piwik_TablePartitioning_Monthly($tableName);
        $timestamp = strtotime("10 September 2000");
        $suffixShouldBe = "_2000_09";
        $prefixTables = Piwik_Config::getInstance()->database['tables_prefix'];
        $tablename = $prefixTables . $tableName . $suffixShouldBe;

        $p->setTimestamp($timestamp);

        $allTablesInstalled = Piwik::getTablesInstalled($forceReload = true);

        $this->assertContains($tablename, $allTablesInstalled);
        $this->assertEquals($tablename, $p->getTableName());
        $this->assertEquals($tablename, (string)$p->__toString());
    }

    /**
     * test monthly
     * @group Core
     * @group TablePartitioning
     */
    public function testMonthlyPartition()
    {
        $tableName = 'archive_numeric';
        $p = new Piwik_TablePartitioning_Monthly($tableName);
        $timestamp = strtotime("10 September 2000");
        $suffixShouldBe = "_2000_09";
        $prefixTables = Piwik_Config::getInstance()->database['tables_prefix'];
        $tablename = $prefixTables . $tableName . $suffixShouldBe;

        $p->setTimestamp($timestamp);

        $allTablesInstalled = Piwik::getTablesInstalled($forceReload = true);
        $this->assertContains($tablename, $allTablesInstalled);
        $this->assertEquals($tablename, $p->getTableName());
        $this->assertEquals($tablename, (string)$p->__toString());
    }

    /**
     * test daily
     * @group Core
     * @group TablePartitioning
     */
    public function testDailyPartition()
    {
        $tableName = 'archive_numeric';
        $p = new Piwik_TablePartitioning_Daily($tableName);
        $timestamp = strtotime("10 September 2000");
        $suffixShouldBe = "_2000_09_10";
        $prefixTables = Piwik_Config::getInstance()->database['tables_prefix'];
        $tablename = $prefixTables . $tableName . $suffixShouldBe;

        $p->setTimestamp($timestamp);

        $allTablesInstalled = Piwik::getTablesInstalled();
        $this->assertContains($tablename, $allTablesInstalled);
        $this->assertEquals($tablename, $p->getTableName());
        $this->assertEquals($tablename, (string)$p->__toString());
    }
}
