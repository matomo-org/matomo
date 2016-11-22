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
        $expected .= 'LEFT JOIN log_visit AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit ';
        $expected .= 'LEFT JOIN log_action AS log_action ON (log_link_visit_action.idaction_name = log_action.idaction AND log_link_visit_action.idaction_url = log_action.idaction)';
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
            array('table' => 'log_conversion', 'joinOn' => 'log_conversion.idvisit = log_visit.idvisit'),
            'log_link_visit_action',
            'log_action',
            'log_visit',
            'log_conversion',
            'log_conversion_item',
            'log_foo_bar'
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