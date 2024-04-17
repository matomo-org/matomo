<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Model;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\CoreAdminHome\Model\DuplicateActionRemover;
use Piwik\Plugins\CoreAdminHome\tests\Fixture\DuplicateActions;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class DuplicateActionRemoverTest extends IntegrationTestCase
{
    /**
     * @var DuplicateActions
     */
    public static $fixture = null;

    /**
     * @var DuplicateActionRemover
     */
    private $duplicateActionRemover;

    public function setUp(): void
    {
        parent::setUp();

        $this->duplicateActionRemover = new DuplicateActionRemover();
    }

    public function test_getDuplicateIdActions_ReturnsDuplicateIdActions_AndTreatsLowestIdActionAsOriginal()
    {
        $expectedResult = array(
            array('name' => 'action1', 'idaction' => 1, 'duplicateIdActions' => array(2, 3)),
            array('name' => 'ACTION2', 'idaction' => 5, 'duplicateIdActions' => array(7, 11)),
            array('name' => 'action2', 'idaction' => 4, 'duplicateIdActions' => array(9)),
            array('name' => 'action4', 'idaction' => 6, 'duplicateIdActions' => array(10, 12)),
        );
        $actualResult = $this->duplicateActionRemover->getDuplicateIdActions();
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function test_fixDuplicateActionsInTable_CorrectlyUpdatesIdActionColumns_InSpecifiedTable()
    {
        $this->duplicateActionRemover->fixDuplicateActionsInTable('log_conversion_item', 5, array(3, 6, 7, 10));

        $columns = array('idaction_sku', 'idaction_name', 'idaction_category', 'idaction_category2',
                         'idaction_category3', 'idaction_category4', 'idaction_category5');

        $expectedResult = array(
            array(
                'idaction_sku' => '1',
                'idaction_name' => '2',
                'idaction_category' => '5',
                'idaction_category2' => '4',
                'idaction_category3' => '5',
                'idaction_category4' => '5',
                'idaction_category5' => '5'
            ),
            array(
                'idaction_sku' => '2',
                'idaction_name' => '5',
                'idaction_category' => '5',
                'idaction_category2' => '5',
                'idaction_category3' => '8',
                'idaction_category4' => '9',
                'idaction_category5' => '5'
            ),
        );
        $actualResult = Db::fetchAll("SELECT " . implode(", ", $columns) . " FROM " . Common::prefixTable('log_conversion_item'));
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function test_getSitesAndDatesOfRowsUsingDuplicates_ReturnsTheServerTimeAndIdSite_OfRowsUsingSpecifiedActionIds()
    {
        $row = array(
            'idsite' => 3,
            'idvisitor' => pack("H*", DuplicateActions::DUMMY_IDVISITOR),
            'server_time' => '2012-02-13 00:00:00',
            'idvisit' => 5,
            'idorder' => 6,
            'price' => 15,
            'quantity' => 21,
            'deleted' => 1,
            'idaction_sku' => 3,
            'idaction_name' => 3,
            'idaction_category' => 12,
            'idaction_category2' => 3,
            'idaction_category3' => 3,
            'idaction_category4' => 3,
            'idaction_category5' => 3,
        );
        Db::query("INSERT INTO " . Common::prefixTable('log_conversion_item') . " (" . implode(", ", array_keys($row))
            . ") VALUES ('" . implode("', '", array_values($row)) . "')");

        $expectedResult = array(
            array('idsite' => 1, 'server_time' => '2012-02-01'),
            array('idsite' => 3, 'server_time' => '2012-02-13')
        );
        $actualResult = $this->duplicateActionRemover->getSitesAndDatesOfRowsUsingDuplicates('log_conversion_item', array(4, 6, 12));
        $this->assertEquals($expectedResult, $actualResult);
    }
}

DuplicateActionRemoverTest::$fixture = new DuplicateActions();
