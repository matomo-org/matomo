<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tests\Unit\DataAccess;

use Piwik\DataAccess\LogQueryBuilder\JoinTables;
use Piwik\Tests\Framework\Mock\Plugin\LogTablesProvider;
use Piwik\Tracker\Visit;

/**
 * @group Core
 */
class JoinTablesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JoinTables
     */
    private $tables;

    public function setUp()
    {
        $this->tables = $this->makeTables(array(
            'log_visit',
            array('table' => 'log_conversion', 'joinOn' => 'log_conversion.idvisit = log_visit.idvisit'),
            'log_action'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Table 'log_foo_bar_baz' can't be used for segmentation
     */
    public function test_construct_shouldThrowException_IfTableIsNotPossibleToJoin()
    {
        $this->makeTables(array('log_visit', 'log_foo_bar_baz'));
    }

    public function test_hasJoinedTable_shouldDetectIfTableIsAlreadyAdded()
    {
        $this->assertTrue($this->tables->hasJoinedTable('log_visit'));
        $this->assertTrue($this->tables->hasJoinedTable('log_action'));

        $this->assertFalse($this->tables->hasJoinedTable('log_foo_bar_baz'));
        $this->assertFalse($this->tables->hasJoinedTable('log_conversion')); // we do not check for manually joined tables
    }

    public function test_addTableToJoin_shouldAddGivenTable()
    {
        $table = 'log_conversion_item';
        $this->assertFalse($this->tables->hasJoinedTable($table));

        $this->tables->addTableToJoin($table);

        $this->assertTrue($this->tables->hasJoinedTable($table));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Table 'log_foo_bar_baz' can't be used for segmentation
     */
    public function test_addTableToJoin_shouldCheckIfTableCanBeUsedForSegmentation()
    {
        $table = 'log_foo_bar_baz';
        $this->assertFalse($this->tables->hasJoinedTable($table));

        $this->tables->addTableToJoin($table);

        $this->assertTrue($this->tables->hasJoinedTable($table));
    }

    public function test_hasJoinedTableManually_shouldReturnTrue_IfTableJoinExistsExactlyAsGiven()
    {
        $result = $this->tables->hasJoinedTableManually('log_conversion', 'log_conversion.idvisit = log_visit.idvisit');

        $this->assertTrue($result);
    }

    public function test_hasJoinedTableManually_shouldReturnFalse_IfTableOrJoinDoesNotMatch()
    {
        $result = $this->tables->hasJoinedTableManually('log_foo_bar_baz', 'log_conversion.idvisit = log_visit.idvisit');
        $this->assertFalse($result);

        $result = $this->tables->hasJoinedTableManually('log_conversion', 'log_foo_bar_baz.idvisit = log_visit.idvisit');
        $this->assertFalse($result);
    }

    public function test_hasJoinedTableManually_shouldReturnFalse_IfTableOrJoinHasCustomJoin()
    {
        $this->tables = $this->makeTables(array(
            'log_visit',
            array('table' => 'log_conversion', 'join' => 'right JOIN', 'joinOn' => 'log_conversion.idvisit = log_visit.idvisit'),
            'log_action'));

        $result = $this->tables->hasJoinedTableManually('log_conversion', 'log_conversion.idvisit = log_visit.idvisit');
        $this->assertFalse($result);
    }

    public function test_hasAddedTableManually_shouldReturnTrue_IfTableWasAddedManually()
    {
        $result = $this->tables->hasAddedTableManually('log_conversion');

        $this->assertTrue($result);
    }

    public function test_hasAddedTableManually_shouldReturnFalse_IfTableWasNotAddedManually()
    {
        $result = $this->tables->hasAddedTableManually('log_foo_bar_baz');
        $this->assertFalse($result);

        $result = $this->tables->hasAddedTableManually('log_conversion_item');
        $this->assertFalse($result);
    }

    public function test_getLogTable_shouldReturnInstanceOfLogTable_IfTableExists()
    {
        $visit = $this->tables->getLogTable('log_visit');
        $this->assertFalse($visit instanceof Visit);
    }

    public function test_getLogTable_shouldReturnNull_IfLogTableDoesNotExist()
    {
        $visit = $this->tables->getLogTable('log_foo_bar_baz');
        $this->assertNull($visit);
    }

    public function test_findIndexOfManuallyAddedTable_shouldReturnTheIndex_IfTableWasAddedManually()
    {
        $this->assertSame(1, $this->tables->findIndexOfManuallyAddedTable('log_conversion'));
    }

    public function test_findIndexOfManuallyAddedTable_shouldReturnNull_IfTableWasNotAddedManually()
    {
        $this->assertNull($this->tables->findIndexOfManuallyAddedTable('log_visit'));
        $this->assertNull($this->tables->findIndexOfManuallyAddedTable('log_action'));
        $this->assertNull($this->tables->findIndexOfManuallyAddedTable('log_foo_bar_baz'));
    }

    public function test_sort_shouldNeverSortFirstEntry_AndNotMaintainKeys()
    {
        $tables = $this->makeTables(array('log_conversion', 'log_visit', 'log_action', 'log_conversion_item'));
        $tables->sort(function($a, $b) {
            return strcmp($a, $b);
        });

        $expected = array('log_conversion', 'log_action', 'log_conversion_item', 'log_visit');
        $this->assertEquals($expected, $tables->getTables());
    }

    public function test_sort_ifAllReturn0_ThenSortByGivenOrder()
    {
        $tables = $this->makeTables(array('log_conversion', 'log_visit', 'log_action', 'log_conversion_item'));
        $tables->sort(function($a, $b) {
            return 0;
        });

        $expected = array('log_conversion', 'log_visit', 'log_action', 'log_conversion_item');
        $this->assertEquals($expected, $tables->getTables());
    }

    private function makeTables($tables)
    {
        return new JoinTables(new LogTablesProvider(), $tables);
    }
}