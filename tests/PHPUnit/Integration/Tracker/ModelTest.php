<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Matomo\Network\IPUtils;
use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Model;

/**
 * @group ModelTest
 * @group Tracker
 */
class ModelTest extends IntegrationTestCase
{
    public const TEST_ACTION_NAME = 'action_name';
    public const TEST_ACTION_TYPE = 1;
    public const TEST_ACTION_URL_PREFIX = 1;

    /**
     * @var Model
     */
    private $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = new Model();
    }

    public function test_hasVisit()
    {
        $this->model->createVisit(array(
            'idvisitor' => hex2bin('1234567812345678'),
            'config_id' => '1234567',
            'location_ip' => IPUtils::binaryToStringIP('10.10.10.10'),
            'idvisit' => '4',
            'idsite' => '3',
            'visitor_count_visits' => 0,
            'visit_total_time' => 0,
            'visit_first_action_time' => Date::now()->getDatetime(),
            'visit_last_action_time' => Date::now()->getDatetime(),
        ));

        $this->assertTrue($this->model->hasVisit(3, 4));
        $this->assertTrue($this->model->hasVisit('3', '4'));

        // idsite not match
        $this->assertFalse($this->model->hasVisit(9, 4));

        // idvisit not match
        $this->assertFalse($this->model->hasVisit(3, 8));
    }

    public function test_createNewIdAction_CreatesNewAction_WhenNoActionWithSameNameAndType()
    {
        $newIdAction = $this->model->createNewIdAction(self::TEST_ACTION_NAME, self::TEST_ACTION_TYPE, self::TEST_ACTION_URL_PREFIX);

        $this->assertLogActionTableContainsTestAction($newIdAction);
    }

    public function test_createNewIdAction_DoesNotCreateDuplicateActions_AndReturnsExistingIdAction_IfActionAlreadyExists()
    {
        $this->insertSingleDuplicateAction();

        $newIdAction = $this->model->createNewIdAction(self::TEST_ACTION_NAME, self::TEST_ACTION_TYPE, self::TEST_ACTION_URL_PREFIX);

        $this->assertEquals(5, $newIdAction);
        $this->assertLogActionTableContainsTestAction(5);
    }

    public function test_getIdsAction_CorrectlyReturnsRequestedActionIds()
    {
        $this->insertManyActions();

        $result = $this->model->getIdsAction(array(
            array('name' => 'action1', 'type' => 1),
            array('name' => 'ACTION1', 'type' => 1),
            array('name' => 'action1', 'type' => 2),
            array('name' => 'action2', 'type' => 2)
        ));

        $expectedResult = array(
            array(
                'idaction' => '2',
                'type' => '1',
                'name' => 'action1'
            ),
            array(
                'idaction' => '3',
                'type' => '1',
                'name' => 'ACTION1'
            ),
            array(
                'idaction' => '4',
                'type' => '2',
                'name' => 'action1'
            ),
            array(
                'idaction' => '5',
                'type' => '2',
                'name' => 'action2'
            ),
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function test_getIdsAction_CorrectlyReturnsLowestIdActions_IfDuplicateIdActionsExistInDb()
    {
        $this->insertManyActionsWithDuplicates();

        $result = $this->model->getIdsAction(array(
            array('name' => 'action1', 'type' => 1),
            array('name' => 'action2', 'type' => 2)
        ));

        $expectedResult = array(
            array(
                'idaction' => '1',
                'type' => '1',
                'name' => 'action1'
            ),
            array(
                'idaction' => '4',
                'type' => '2',
                'name' => 'action2'
            )
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function test_isSiteEmpty()
    {
        $this->assertTrue($this->model->isSiteEmpty(1));

        $fixture = new OneVisitorTwoVisits();
        $fixture->setUp();

        $this->assertFalse($this->model->isSiteEmpty(1));
    }

    public function test_createEcommerceItems_shouldNotFail_IfWritingSameItemTwice()
    {
        $item = array(
            'idsite' => '1',
            'idvisitor' => 'test',
            'server_time' => '2014-01-01 00:00:00',
            'idvisit' => '1',
            'idorder' => '12',
            'idaction_sku' => '1',
            'idaction_name' => '2',
            'idaction_category' => '3',
            'idaction_category2' => '4',
            'idaction_category3' => '5',
            'idaction_category4' => '6',
            'idaction_category5' => '7',
            'price' => '10.00',
            'quantity' => '1',
            'deleted' => '0'
        );
        $item2 = [
            'idsite' => '1',
            'idvisitor' => 'test',
            'server_time' => '2014-01-01 00:00:00',
            'idvisit' => '1',
            'idorder' => '12',
            'idaction_sku' => '2',
            'idaction_name' => '2',
            'idaction_category' => '3',
            'idaction_category2' => '4',
            'idaction_category3' => '5',
            'idaction_category4' => '6',
            'idaction_category5' => '7',
            'price' => '20.00',
            'quantity' => '1',
            'deleted' => '0'
        ];
        $this->model->createEcommerceItems([$item]);
        $this->model->createEcommerceItems([$item, $item2]);

        $itemsInDb = Db::fetchAll("SELECT idsite, HEX(idvisitor) as idvisitor, idorder, idaction_sku FROM " . Common::prefixTable('log_conversion_item'));
        $expectedItemsInDb = [
            [
                'idsite' => '1',
                'idvisitor' => '7465737400000000',
                'idorder' => '12',
                'idaction_sku' => '1',
            ],
            [
                'idsite' => '1',
                'idvisitor' => '7465737400000000',
                'idorder' => '12',
                'idaction_sku' => '2',
            ],
        ];

        $this->assertEquals($expectedItemsInDb, $itemsInDb);
    }

    private function assertLogActionTableContainsTestAction($idaction)
    {
        $expectedRows = array(
            array(
                'idaction' => $idaction,
                'name' => self::TEST_ACTION_NAME,
                'type' => self::TEST_ACTION_TYPE,
                'url_prefix' => self::TEST_ACTION_URL_PREFIX
            )
        );
        $this->assertEquals($expectedRows, Db::fetchAll("SELECT idaction, name, type, url_prefix FROM " . Common::prefixTable('log_action')));
    }

    private function insertSingleDuplicateAction()
    {
        $logActionTable = Common::prefixTable('log_action');
        Db::query(
            "INSERT INTO $logActionTable (idaction, name, type, url_prefix, hash) VALUES (?, ?, ?, ?, CRC32(?))",
            array(5, self::TEST_ACTION_NAME, self::TEST_ACTION_TYPE, self::TEST_ACTION_URL_PREFIX,
            self::TEST_ACTION_NAME)
        );
    }

    private function insertManyActions()
    {
        $logActionTable = Common::prefixTable('log_action');
        Db::query(
            "INSERT INTO $logActionTable (idaction, name, type, hash)
                  VALUES (1, 'action0', 1, CRC32('action0')),
                         (2, 'action1', 1, CRC32('action1')),
                         (3, 'ACTION1', 1, CRC32('ACTION1')),
                         (4, 'action1', 2, CRC32('action1')),
                         (5, 'action2', 2, CRC32('action2')),
                         (6, 'action2', 3, CRC32('action2'))"
        );
    }

    private function insertManyActionsWithDuplicates()
    {
        $logActionTable = Common::prefixTable('log_action');
        Db::query(
            "INSERT INTO $logActionTable (idaction, name, type, hash)
                  VALUES (1, 'action1', 1, CRC32('action1')),
                         (2, 'action1', 2, CRC32('action1')),
                         (3, 'action1', 3, CRC32('action1')),
                         (6, 'action2', 2, CRC32('action2')),
                         (5, 'action2', 2, CRC32('action2')),
                         (4, 'action2', 2, CRC32('action2'))"
        );
    }
}
