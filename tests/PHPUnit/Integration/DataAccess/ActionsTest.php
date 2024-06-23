<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\Common;
use Piwik\DataAccess\Actions;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class ActionsTest extends IntegrationTestCase
{
    /**
     * @var Actions
     */
    private $actionsAccess;

    public function setUp(): void
    {
        parent::setUp();

        $this->insertTestActions();

        $this->actionsAccess = new Actions();
    }

    public function testDeleteDeletesSpecifiedActions()
    {
        $this->actionsAccess->delete(array(2,3,4,5));

        $expectedActions = array(
            array('name' => 'action1')
        );
        $actualActions = Db::fetchAll("SELECT name FROM " . Common::prefixTable('log_action'));
        $this->assertEquals($expectedActions, $actualActions);
    }

    public function testDeleteConvertsIdActionsToInt()
    {
        $this->actionsAccess->delete(array("2", "0, 1"));

        $expectedActions = array(
            array('name' => 'action1'),
            array('name' => 'action3')
        );
        $actualActions = Db::fetchAll("SELECT name FROM " . Common::prefixTable('log_action'));
        $this->assertEquals($expectedActions, $actualActions);
    }

    private function insertTestActions()
    {
        Db::query("INSERT INTO " . Common::prefixTable('log_action') . " (name, type, hash)
                        VALUES ('action1', 1, CRC32('action1')),
                               ('action2', 1, CRC32('action2')),
                               ('action3', 1, CRC32('action3'))");
    }
}
