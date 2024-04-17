<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Unit\DataAccess\LogQueryBuilder;

use Piwik\DataAccess\LogQueryBuilder\JoinTables;
use Piwik\Tests\Framework\Mock\Plugin\LogTablesProvider;
use Piwik\Tracker\Visit;

/**
 * @group Core
 */
class JoinTablesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var JoinTables
     */
    private $tables;

    public function setUp(): void
    {
        $this->tables = $this->makeTables(array(
            'log_visit',
            array('table' => 'log_conversion', 'joinOn' => 'log_conversion.idvisit = log_visit.idvisit'),
            'log_action'));
    }

    public function test_construct_shouldThrowException_IfTableIsNotPossibleToJoin()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Table \'log_foo_bar_baz\' can\'t be used for segmentation');

        $this->makeTables(array('log_visit', 'log_foo_bar_baz'));
    }

    public function test_hasJoinedTable_custom()
    {
        $tables = $this->makeTables(array('log_visit', 'log_custom'));
        $this->assertTrue($tables->hasJoinedTable('log_visit'));
        $this->assertTrue($tables->hasJoinedTable('log_custom'));
    }

    public function test_hasJoinedTable_custom2()
    {
        $tables = $this->makeTables(array('log_visit', 'log_custom_other'));
        $this->assertTrue($tables->hasJoinedTable('log_visit'));
        $this->assertTrue($tables->hasJoinedTable('log_custom_other'));
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

    public function test_addTableToJoin_shouldCheckIfTableCanBeUsedForSegmentation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Table \'log_foo_bar_baz\' can\'t be used for segmentation');

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
        $tables = $this->makeTables(array('log_action', 'log_conversion', 'log_visit', 'log_conversion_item'));
        $tables->sort();

        $expected = array('log_action', 'log_visit', 'log_conversion', 'log_conversion_item');
        $this->assertEquals($expected, $tables->getTables());
    }

    public function test_sortTablesForJoin_shouldSortTablesAsSpecified()
    {
        $tables = array(
            'log_link_visit_action',
            'log_action',
            array('table' => 'log_conversion', 'joinOn' => 'log_conversion.idvisit = log_visit.idvisit'),
            'log_conversion_item',
            'log_conversion',
            'log_visit',
            array('table' => 'log_foo_bar'),
        );

        $tables = $this->makeTables($tables);
        $tables->sort();

        $expected = array(
            'log_link_visit_action',
            'log_visit',
            array('table' => 'log_conversion', 'joinOn' => 'log_conversion.idvisit = log_visit.idvisit'),
            'log_conversion_item',
            'log_action',
            'log_conversion',
            array('table' => 'log_foo_bar'),
        );

        $this->assertEquals($expected, $tables->getTables());
    }

    public function test_sortTablesForJoin_anotherTestMakingSureWorksOhPhp5_5()
    {
        $tables = array (
            1 => 'log_link_visit_action',
            2 =>
                array (
                    'table' => 'log_action',
                    'tableAlias' => 'log_action_idaction_name',
                    'joinOn' => 'log_link_visit_action.idaction_name = log_action_idaction_name.idaction',
                ),
            3 =>
                array (
                    'table' => 'log_action',
                    'tableAlias' => 'log_action_visit_exit_idaction_name',
                    'joinOn' => 'log_visit.visit_exit_idaction_name = log_action_visit_exit_idaction_name.idaction',
                ),
        )
        ;

        $tables = $this->makeTables($tables);
        $tables->sort();

        $expected = array(
            'log_link_visit_action',
            array (
                'table' => 'log_action',
                'tableAlias' => 'log_action_idaction_name',
                'joinOn' => 'log_link_visit_action.idaction_name = log_action_idaction_name.idaction',
            ),
            array (
                'table' => 'log_action',
                'tableAlias' => 'log_action_visit_exit_idaction_name',
                'joinOn' => 'log_visit.visit_exit_idaction_name = log_action_visit_exit_idaction_name.idaction',
            ),
        );

        $this->assertEquals($expected, $tables->getTables());
    }

    public function test_sortTablesForJoin_anotherTest2MakingSureWorksOhPhp5_5()
    {
        $tables = array (
            1 => 'log_link_visit_action',
            3 =>
                array (
                    'table' => 'log_action',
                    'tableAlias' => 'log_action_visit_exit_idaction_name',
                    'joinOn' => 'log_visit.visit_exit_idaction_name = log_action_visit_exit_idaction_name.idaction',
                ),
            2 =>
                array (
                    'table' => 'log_action',
                    'tableAlias' => 'log_action_idaction_name',
                    'joinOn' => 'log_link_visit_action.idaction_name = log_action_idaction_name.idaction',
                ),
        )
        ;

        $tables = $this->makeTables($tables);
        $tables->sort();

        $expected = array(
            'log_link_visit_action',
            array (
                'table' => 'log_action',
                'tableAlias' => 'log_action_visit_exit_idaction_name',
                'joinOn' => 'log_visit.visit_exit_idaction_name = log_action_visit_exit_idaction_name.idaction',
            ),
            array (
                'table' => 'log_action',
                'tableAlias' => 'log_action_idaction_name',
                'joinOn' => 'log_link_visit_action.idaction_name = log_action_idaction_name.idaction',
            ),
        );

        $this->assertEquals($expected, $tables->getTables());
    }

    public function test_sortTablesForJoin_shouldSortTablesWithCustomJoinRequiringEachOther1()
    {
        $tables = array(
            'log_link_visit_action',
            'log_action',
            array(
                'table' => 'log_link_visit_action',
                'tableAlias' => 'log_link_visit_action_foo',
                'joinOn' => "log_link_visit_action.idvisit = log_link_visit_action_foo.idvisit"
            ),
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_foo',
                'joinOn' => "log_link_visit_action_foo.idaction_url = log_action_foo.idaction"
            )
        );

        $tables = $this->makeTables($tables);
        $tables->sort();

        $expected = array(
            'log_link_visit_action',
            'log_action',
            array (
                'table' => 'log_link_visit_action',
                'tableAlias' => 'log_link_visit_action_foo',
                'joinOn' => 'log_link_visit_action.idvisit = log_link_visit_action_foo.idvisit',
            ),
            array (
                'table' => 'log_action',
                'tableAlias' => 'log_action_foo',
                'joinOn' => 'log_link_visit_action_foo.idaction_url = log_action_foo.idaction',
            ),
        );

        $this->assertEquals($expected, $tables->getTables());

        // should still be the same if inverted
        $tables = array(
            'log_link_visit_action',
            'log_action',
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_foo',
                'joinOn' => "log_link_visit_action_foo.idaction_url = log_action_foo.idaction"
            ),
            array(
                'table' => 'log_link_visit_action',
                'tableAlias' => 'log_link_visit_action_foo',
                'joinOn' => "log_link_visit_action.idvisit = log_link_visit_action_foo.idvisit"
            ),
        );

        $tables = $this->makeTables($tables);
        $tables->sort();

        $this->assertEquals($expected, $tables->getTables());

        // should still be the same if inverted
        $tables = array(
            'log_link_visit_action',
            'log_action',
            array(
                'table' => 'log_link_visit_action',
                'tableAlias' => 'log_link_visit_action_foo',
                'joinOn' => "log_link_visit_action.idvisit = log_link_visit_action_foo.idvisit"
            ),
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_foo',
                'joinOn' => "log_link_visit_action_foo.idaction_url = log_action_foo.idaction"
            ),
        );

        $tables = $this->makeTables($tables);
        $tables->sort();

        $this->assertEquals($expected, $tables->getTables());
    }

    public function test_sortTablesForJoin_shouldSortTablesWithCustomJoinRequiringEachOther2()
    {
        $tables = array(
            'log_link_visit_action',
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_idaction_event_action',
                'joinOn' => "log_link_visit_action.idaction_event_action = log_action_idaction_event_action.idaction"
            ),
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_visit_entry_idaction_name',
                'joinOn' => "log_visit.visit_entry_idaction_name = log_action_visit_entry_idaction_name.idaction"
            ),
        );

        $tables = $this->makeTables($tables);
        $tables->sort();

        $expected = array(
            'log_link_visit_action',
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_idaction_event_action',
                'joinOn' => "log_link_visit_action.idaction_event_action = log_action_idaction_event_action.idaction"
            ),
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_visit_entry_idaction_name',
                'joinOn' => "log_visit.visit_entry_idaction_name = log_action_visit_entry_idaction_name.idaction"
            )
        );

        $this->assertEquals($expected, $tables->getTables());
    }

    private function makeTables($tables)
    {
        return new JoinTables(new LogTablesProvider(), $tables);
    }
}
