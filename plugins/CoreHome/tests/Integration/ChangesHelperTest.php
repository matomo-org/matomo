<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration;

use Piwik\Tests\Fixtures\CreateChanges;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\CoreHome\ChangesHelper;

/**
 * @group CoreHome
 * @group CoreHomeTest
 * @group CoreHomeChanges
 */
class ChangesHelperTest extends IntegrationTestCase
{

    /**
     * @var CreateChanges
     */
    public static $fixture;

    public function test_CoreHomeChanges_ShouldSortChangeListMostRecentFirst()
    {
        $json = '{"idchange":"3","plugin_name":"CoreHome","version":"4.6.0b5","title":"New feature x added","description":"Now you can do a with b like this","link_name":"For more information go here","link":"https:\/\/www.matomo.org"}';
        $changes = ChangesHelper::getChanges();
        $r = reset($changes);
        unset($r['created_time']);
        $this->assertEquals($json, json_encode($r, true));
    }

}

ChangesHelperTest::$fixture = new CreateChanges();
