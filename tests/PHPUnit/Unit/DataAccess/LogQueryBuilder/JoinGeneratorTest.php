<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataAccess;

use Piwik\DataAccess\LogQueryBuilder\JoinGenerator;
use Piwik\DataAccess\LogQueryBuilder\JoinTables;
use Piwik\Tests\Framework\Mock\Plugin\LogTablesProvider;

/**
 * @group Core
 */
class JoinGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var JoinGenerator
     */
    private $generator;

    public function setUp(): void
    {
        $this->generator = $this->makeTables(array(
            'log_visit',
            array('table' => 'log_conversion', 'joinOn' => 'log_conversion.idvisit = log_visit.idvisit'),
            'log_action'));
    }

    public function testConstructorShouldAddTablesIfNeeded()
    {
        $tables = $this->makeTables(array('log_visit', 'log_action'));
        $this->makeGenerator($tables);

        $this->assertEquals(array('log_visit', 'log_action', 'log_link_visit_action'), $tables->getTables());
    }

    public function testGenerateShouldJoinWithSubselectIfBaseTableIsLogVisit()
    {
        $generator = $this->generate(array('log_visit', 'log_action'));
        $this->assertTrue($generator->shouldJoinWithSelect());
    }

    public function testGenerateShouldNotJoinWithSubselectIfBaseTableIsLogVisitButNoTableToJoin()
    {
        $generator = $this->generate(array('log_visit'));
        $this->assertFalse($generator->shouldJoinWithSelect());
    }

    public function testGenerateShouldNotJoinWithSubselectIfLogVisitIsGivenButItIsNotBaseTable()
    {
        $generator = $this->generate(array('log_conversion', 'log_visit'));
        $this->assertFalse($generator->shouldJoinWithSelect());
    }

    public function testGenerateGetJoinString()
    {
        $generator = $this->generate(array('log_action', 'log_link_visit_action', 'log_visit'));

        $expected  = 'log_action AS log_action ';
        $expected .= 'LEFT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idaction_url = log_action.idaction ';
        $expected .= 'LEFT JOIN log_visit AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function testGenerateGetJoinStringOnlyOneActionTable()
    {
        $generator = $this->generate(array('log_action'));
        $this->assertEquals('log_action AS log_action', $generator->getJoinString());
    }

    public function testGenerateGetJoinStringOnlyActionTables()
    {
        $generator = $this->generate(array('log_link_visit_action', 'log_action'));
        $expected  = 'log_link_visit_action AS log_link_visit_action';
        $expected .= ' LEFT JOIN log_action AS log_action ON log_link_visit_action.idaction_url = log_action.idaction';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function testGenerateGetJoinStringJoinCustomVisitTable()
    {
        $generator = $this->generate(array('log_visit', 'log_custom'));
        $this->assertEquals('log_visit AS log_visit LEFT JOIN log_custom AS log_custom ON `log_custom`.`user_id` = `log_visit`.`user_id`', $generator->getJoinString());
    }

    public function testGenerateGetJoinStringJoinMultipleCustomVisitTable()
    {
        $generator = $this->generate(array('log_visit', 'log_custom_other', 'log_custom'));
        $this->assertEquals('log_visit AS log_visit LEFT JOIN log_custom AS log_custom ON `log_custom`.`user_id` = `log_visit`.`user_id` LEFT JOIN log_custom_other AS log_custom_other ON `log_custom_other`.`other_id` = `log_custom`.`other_id`', $generator->getJoinString());
    }

    public function testGenerateGetJoinStringJoinMultipleCustomVisitTableWithMissingOne()
    {
        $generator = $this->generate(array('log_visit', 'log_custom_other'));
        $this->assertEquals('log_visit AS log_visit LEFT JOIN log_custom AS log_custom ON `log_custom`.`user_id` = `log_visit`.`user_id` LEFT JOIN log_custom_other AS log_custom_other ON `log_custom_other`.`other_id` = `log_custom`.`other_id`', $generator->getJoinString());
    }

    /**
     * Note: the exception reports `log_visit` and not `log_custom` as it resolves the dependencies as so resolves
     * from `log_custom` to `log_visit` but is then not able to find a way to join `log_visit` with `log_action`
     */
    public function testGenerateGetJoinStringCustomVisitTableCantBeJoinedWithAction()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Table \'log_visit\' can\'t be joined for segmentation');

        $generator = $this->generate(array('log_action', 'log_custom'));
        $generator->getJoinString();
    }

    public function testGenerateGetJoinStringJoinCustomVisitTableMultiple()
    {
        $generator = $this->generate(array('log_visit', 'log_action', 'log_custom'));
        $this->assertEquals('log_visit AS log_visit LEFT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit LEFT JOIN log_action AS log_action ON log_link_visit_action.idaction_url = log_action.idaction LEFT JOIN log_custom AS log_custom ON `log_custom`.`user_id` = `log_visit`.`user_id`', $generator->getJoinString());
    }

    public function testGenerateGetJoinStringManuallyJoinedAlready()
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

    public function testGenerateGetJoinStringManuallyJoinedAlreadyWithCustomConditionInArray()
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

    public function testGenerateGetJoinStringManuallyJoinedAlreadyWithCustomConditionInArrayAndFurtherTablesAfterwards()
    {
        $generator = $this->generate(array(
            'log_visit',
            array('table' => 'log_conversion', 'joinOn' => 'log_visit.idvisit2 = log_conversion.idvisit2'),
            'log_conversion',
            'log_link_visit_action'
        ));

        $expected  = 'log_visit AS log_visit ';
        $expected .= 'LEFT JOIN log_conversion AS log_conversion ON log_visit.idvisit2 = log_conversion.idvisit2 ';
        $expected .= 'LEFT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function testGenerateGetJoinStringManuallyJoinedAlreadyWithCustomConditionInArrayInverted()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please reorganize the joined tables as the table log_conversion in {"0":"log_visit","1":"log_conversion","2":"log_link_visit_action","3":{"table":"log_conversion","joinOn":"log_link_visit_action.idvisit2 = log_conversion.idvisit2"}} cannot be joined correctly.');

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

    public function testGenerateGetJoinStringManuallyJoinedAlreadyPlusCustomJoinButAlsoLeft()
    {
        $generator = $this->generate(array(
            'log_link_visit_action',
            array('table' => 'log_visit', 'joinOn' => 'log_visit.idvisit = log_link_visit_action.idvisit'),
            array('table' => 'log_action', 'join' => 'LeFt JOIN', 'joinOn' => 'log_link_visit_action.idaction_name = log_action.idaction'),
            'log_action'
        ));

        $expected  = 'log_link_visit_action AS log_link_visit_action ';
        $expected .= 'LEFT JOIN log_visit AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit ';
        $expected .= 'LEFT JOIN log_action AS log_action ON (log_link_visit_action.idaction_name = log_action.idaction AND log_link_visit_action.idaction_url = log_action.idaction)';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function testGenerateGetJoinStringManuallyJoinedAlreadyPlusCustomJoinButAlsoLeftNeedsKeepOrder()
    {
        $generator = $this->generate(array(
            'log_visit',
            array('table' => 'log_link_visit_action', 'join' => 'RIGHT JOIN'),
            'log_action'
        ));

        $expected  = 'log_visit AS log_visit ';
        $expected  .= 'RIGHT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit ';
        $expected  .= 'LEFT JOIN log_action AS log_action ON log_link_visit_action.idaction_url = log_action.idaction';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function testGenerateGetJoinStringForceIndexUse()
    {
        $generator = $this->generate([
            ['table' => 'log_visit', 'useIndex' => 'index_idsite_datetime'],
            ['table' => 'log_link_visit_action', 'join' => 'RIGHT JOIN'],
            'log_action'
        ]);

        $expected  = 'log_visit AS log_visit USE INDEX (index_idsite_datetime) ';
        $expected  .= 'RIGHT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit ';
        $expected  .= 'LEFT JOIN log_action AS log_action ON log_link_visit_action.idaction_url = log_action.idaction';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function testGenerateGetJoinStringManuallyJoinedAlreadyPlusCustomJoinAtEndButAlsoLeftNeedsKeepOrder()
    {
        $generator = $this->generate(array(
            'log_visit',
            'log_action',
            array('table' => 'log_link_visit_action', 'join' => 'RIGHT JOIN'),
        ));

        $expected  = 'log_visit AS log_visit ';
        $expected  .= 'RIGHT JOIN log_link_visit_action AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit ';
        $expected  .= 'LEFT JOIN log_action AS log_action ON log_link_visit_action.idaction_url = log_action.idaction';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function testGenerateGetJoinStringManualJoin()
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
        $expected .= 'RIGHT JOIN log_visit AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit ';
        $expected .= 'RIGHT JOIN log_action AS log_action_r ON log_link_visit_action.idaction_test = log_action_r.idaction ';
        $expected .= 'LEFT JOIN log_action AS log_action ON log_link_visit_action.idaction_url = log_action.idaction';
        $this->assertEquals($expected, $generator->getJoinString());
    }

    public function testGenerateGetJoinStringAllTables()
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
        $expected .= 'LEFT JOIN log_conversion_item AS log_conversion_item ON log_conversion_item.idvisit = log_link_visit_action.idvisit AND `log_conversion_item`.deleted = 0';
        $this->assertEquals($expected, $generator->getJoinString());
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
