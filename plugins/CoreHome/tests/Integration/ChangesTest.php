<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration;

use Piwik\Tests\Fixtures\CreateChanges;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Changes\Model as ChangesModel;

/**
 * @group CoreHome
 * @group CoreHomeTest
 * @group CoreHomeChanges
 */
class ChangesTest extends IntegrationTestCase
{
    /**
     * @var CreateChanges
     */
    public static $fixture;

    public function testCoreHomeChangesShouldNotReturnChangesOlderThan6Months()
    {
        $changesModel = new ChangesModel();
        $changes = $changesModel->getChangeItems();
        self::assertCount(3, $changes);
        self::assertNotContains(1, array_column($changes, 'idchange'));
    }

    public function testCoreHomeChangesShouldSortChangeListMostRecentFirst()
    {
        $json = '{"idchange":4,"plugin_name":"CoreHome","version":"4.6.0b5","title":"New feature x added","description":"Now you can do a with b like this","link_name":"For more information go here","link":"https:\/\/www.matomo.org"}';
        $changesModel = new ChangesModel();
        $changes = $changesModel->getChangeItems();
        $r = reset($changes);
        unset($r['created_time']);
        $this->assertEquals($json, json_encode($r, true));
    }

    public function testCoreHomeChangesShouldAllowChangeItemAddWithoutLink()
    {
        $json = '{"idchange":3,"plugin_name":"CoreHome","version":"4.5.0","title":"New feature y added","description":"Now you can do c with d like this","link_name":null,"link":null}';
        $changesModel = new ChangesModel();
        $changes = $changesModel->getChangeItems();
        $r = $changes[1];
        unset($r['created_time']);
        $this->assertEquals($json, json_encode($r, true));
    }
}

ChangesTest::$fixture = new CreateChanges();
