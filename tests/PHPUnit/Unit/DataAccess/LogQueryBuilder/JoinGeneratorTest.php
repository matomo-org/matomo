<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tests\Unit\DataAccess;

use Piwik\DataAccess\LogQueryBuilder\JoinGenerator;
use Piwik\DataAccess\LogQueryBuilder\JoinTables;
use Piwik\Tests\Framework\Mock\Plugin\LogTablesProvider;
use Piwik\Tracker\Visit;

/**
 * @group Core
 */
class JoinGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JoinGenerator
     */
    private $generator;

    public function setUp()
    {
        $this->generator = $this->makeTables(array(
            'log_visit',
            array('table' => 'log_conversion', 'joinOn' => 'log_conversion.idvisit = log_visit.idvisit'),
            'log_action'));
    }

    public function test_constructor_shouldAddTablesIfNeeded()
    {
        $tables = $this->makeTables(array('log_visit', 'log_action'));
        $this->makeGenerator($tables);

        $this->assertEquals(array('log_visit', 'log_action', 'log_link_visit_action'), $tables->getTables());
    }

    public function test_generate_shouldJoinWithSubselect_IfBaseTableIsLogVisit()
    {
        $generator = $this->generate(array('log_visit', 'log_action'));
        $this->assertTrue($generator->shouldJoinWithSelect());
    }

    public function test_generate_shouldNotJoinWithSubselect_IfBaseTableIsLogVisitButNoTableToJoin()
    {
        $generator = $this->generate(array('log_visit'));
        $this->assertFalse($generator->shouldJoinWithSelect());
    }

    public function test_generate_shouldNotJoinWithSubselect_IfLogVisitIsGivenButItIsNotBaseTable()
    {
        $generator = $this->generate(array('log_conversion', 'log_visit'));
        $this->assertFalse($generator->shouldJoinWithSelect());
    }

    public function test_generate_getJoinString()
    {
        $generator = $this->generate(array('log_action', 'log_link_visit_action', 'log_visit'));

        $expected  = 'log_action AS log_action ';
        $expected .= 'LEFT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idaction_url = log_action.idaction ';
        $expected .= 'LEFT JOIN log_visit AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function test_generate_getJoinString_OnlyOneTable()
    {
        $generator = $this->generate(array('log_visit'));
        $this->assertEquals('log_visit AS log_visit', $generator->getJoinString());
    }

    public function test_generate_getJoinString_OnlyOneActionTable()
    {
        $generator = $this->generate(array('log_action'));
        $this->assertEquals('log_action AS log_action', $generator->getJoinString());
    }

    public function test_generate_getJoinString_OnlyActionTables()
    {
        $generator = $this->generate(array('log_link_visit_action', 'log_action'));
        $expected  = 'log_link_visit_action AS log_link_visit_action';
        $expected .= ' LEFT JOIN log_action AS log_action ON log_link_visit_action.idaction_url = log_action.idaction';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function test_generate_getJoinString_manuallyJoinedAlready()
    {
        $generator = $this->generate(array(
            'log_link_visit_action',
            array('table' => 'log_visit', 'joinOn' => 'log_visit.idvisit = log_link_visit_action.idvisit'),
            array('table' => 'log_action', 'joinOn' => 'log_link_visit_action.idaction_name = log_action.idaction'),
            'log_action'
        ));

        $expected  = 'log_link_visit_action AS log_link_visit_action ';
        $expected .= 'LEFT JOIN log_action AS log_action ON (log_link_visit_action.idaction_name = log_action.idaction AND log_link_visit_action.idaction_url = log_action.idaction) ';
        $expected .= 'LEFT JOIN log_visit AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function test_generate_getJoinString_manuallyJoinedAlreadyWithCustomConditionInArray()
    {
        $generator = $this->generate(array(
            'log_visit',
            array('table' => 'log_conversion', 'joinOn' => 'log_visit.idvisit2 = log_conversion.idvisit2'),
            'log_conversion'
        ));

        $expected  = 'log_visit AS log_visit ';
        $expected .= 'LEFT JOIN log_conversion AS log_conversion ON log_visit.idvisit2 = log_conversion.idvisit2';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function test_generate_getJoinString_manuallyJoinedAlreadyWithCustomConditionInArrayAndFurtherTablesAfterwards()
    {
        $generator = $this->generate(array(
            'log_visit',
            array('table' => 'log_conversion', 'joinOn' => 'log_visit.idvisit2 = log_conversion.idvisit2'),
            'log_conversion',
            'log_link_visit_action'
        ));

        $expected  = 'log_visit AS log_visit ';
        $expected .= 'LEFT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit ';
        $expected .= 'LEFT JOIN log_conversion AS log_conversion ON log_visit.idvisit2 = log_conversion.idvisit2';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Please reorganize the joined tables as the table log_conversion in {"0":"log_visit","1":"log_conversion","2":"log_link_visit_action","3":{"table":"log_conversion","joinOn":"log_link_visit_action.idvisit2 = log_conversion.idvisit2"}} cannot be joined correctly.
     */
    public function test_generate_getJoinString_manuallyJoinedAlreadyWithCustomConditionInArrayInverted()
    {
        $generator = $this->generate(array(
            'log_visit',
            'log_conversion',
            'log_link_visit_action',
            array('table' => 'log_conversion', 'joinOn' => 'log_link_visit_action.idvisit2 = log_conversion.idvisit2'),
        ));

        $expected  = 'log_visit AS log_visit ';
        $expected .= 'LEFT JOIN log_conversion AS log_conversion ON log_visit.idvisit2 = log_conversion.idvisit2 ';
        $expected .= 'LEFT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit ';
        $expected .= 'LEFT JOIN log_conversion AS log_conversion ON log_conversion.idvisit = log_visit.idvisit ';

        $expected .= 'LEFT JOIN log_conversion AS log_conversion ON log_visit.idvisit2 = log_conversion.idvisit2 ';
        $expected .= 'LEFT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function test_generate_getJoinString_manuallyJoinedAlreadyPlusCustomJoinButAlsoLeft()
    {
        $generator = $this->generate(array(
            'log_link_visit_action',
            array('table' => 'log_visit', 'joinOn' => 'log_visit.idvisit = log_link_visit_action.idvisit'),
            array('table' => 'log_action', 'join' => 'LeFt JOIN', 'joinOn' => 'log_link_visit_action.idaction_name = log_action.idaction'),
            'log_action'
        ));

        $expected  = 'log_link_visit_action AS log_link_visit_action ';
        $expected .= 'LEFT JOIN log_action AS log_action ON (log_link_visit_action.idaction_name = log_action.idaction AND log_link_visit_action.idaction_url = log_action.idaction) ';
        $expected .= 'LEFT JOIN log_visit AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function test_generate_getJoinString_manualJoin()
    {
        $generator = $this->generate(array(
            'log_link_visit_action',
            array('table' => 'log_visit',
                'join' => 'RIGHT JOIN','joinOn' => 'log_visit.idvisit = log_link_visit_action.idvisit'),
            array('table' => 'log_action',
                  'tableAlias' => 'log_action_r',
                  'join' => 'RIGHT JOIN',
                  'joinOn' => 'log_link_visit_action.idaction_test = log_action_r.idaction'),
            'log_action'
        ));

        $expected  = 'log_link_visit_action AS log_link_visit_action ';
        $expected .= 'LEFT JOIN log_action AS log_action ON log_link_visit_action.idaction_url = log_action.idaction ';
        $expected .= 'RIGHT JOIN log_visit AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit ';
        $expected .= 'RIGHT JOIN log_action AS log_action_r ON log_link_visit_action.idaction_test = log_action_r.idaction';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function test_generate_getJoinString_allTables()
    {
        $generator = $this->generate(array(
            'log_action',
            'log_conversion_item',
            'log_link_visit_action',
            'log_conversion',
            'log_visit',
        ));

        $expected  = 'log_action AS log_action ';
        $expected .= 'LEFT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idaction_url = log_action.idaction ';
        $expected .= 'LEFT JOIN log_visit AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit ';
        $expected .= 'LEFT JOIN log_conversion AS log_conversion ON log_conversion.idvisit = log_link_visit_action.idvisit ';
        $expected .= 'LEFT JOIN log_conversion_item AS log_conversion_item ON log_conversion_item.idvisit = log_link_visit_action.idvisit';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function test_sortTablesForJoin_shouldSortTablesAsSpecified()
    {
        $tables = array(
            'log_action',
            array('table' => 'log_conversion', 'joinOn' => 'log_conversion.idvisit = log_visit.idvisit'),
            'log_conversion_item',
            'log_link_visit_action',
            'log_conversion',
            'log_visit',
        );

        $generator = $this->makeGenerator($tables);
        $tables[] = 'log_foo_bar';
        usort($tables, array($generator, 'sortTablesForJoin'));

        $expected = array(
            'log_link_visit_action',
            'log_action',
            'log_visit',
            'log_conversion',
            'log_conversion_item',
            'log_foo_bar',
            array('table' => 'log_conversion', 'joinOn' => 'log_conversion.idvisit = log_visit.idvisit'),
        );

        $this->assertEquals($expected, $tables);
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

        $generator = $this->makeGenerator($tables);
        usort($tables, array($generator, 'sortTablesForJoin'));

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

        $this->assertEquals($expected, $tables);
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

        $generator = $this->makeGenerator($tables);
        usort($tables, array($generator, 'sortTablesForJoin'));

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

        $this->assertEquals($expected, $tables);
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

        $generator = $this->makeGenerator($tables);
        usort($tables, array($generator, 'sortTablesForJoin'));

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

        $this->assertEquals($expected, $tables);

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

        $generator = $this->makeGenerator($tables);
        usort($tables, array($generator, 'sortTablesForJoin'));

        $this->assertEquals($expected, $tables);

        // should still be the same if inverted
        $tables = array(
            'log_link_visit_action',
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_foo',
                'joinOn' => "log_link_visit_action_foo.idaction_url = log_action_foo.idaction"
            ),
            'log_action',
            array(
                'table' => 'log_link_visit_action',
                'tableAlias' => 'log_link_visit_action_foo',
                'joinOn' => "log_link_visit_action.idvisit = log_link_visit_action_foo.idvisit"
            ),
        );

        $generator = $this->makeGenerator($tables);
        usort($tables, array($generator, 'sortTablesForJoin'));

        $this->assertEquals($expected, $tables);
    }

    public function test_sortTablesForJoin_shouldSortTablesWithCustomJoinRequiringEachOther2()
    {
        $tables = array(
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_visit_entry_idaction_name',
                'joinOn' => "log_visit.visit_entry_idaction_name = log_action_visit_entry_idaction_name.idaction"
            ),
            'log_link_visit_action',
            array(
                'table' => 'log_action',
                'tableAlias' => 'log_action_idaction_event_action',
                'joinOn' => "log_link_visit_action.idaction_event_action = log_action_idaction_event_action.idaction"
            )
        );

        $generator = $this->makeGenerator($tables);
        usort($tables, array($generator, 'sortTablesForJoin'));

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

        $this->assertEquals($expected, $tables);
    }

    private function generate($tables)
    {
        $generator = $this->makeGenerator($tables);
        $generator->generate();
        return $generator;
    }

    private function makeGenerator($tables)
    {
        if (is_array($tables)) {
            $tables = $this->makeTables($tables);
        }

        return new JoinGenerator($tables);
    }

    private function makeTables($tables)
    {
        return new JoinTables(new LogTablesProvider(), $tables);
    }
}