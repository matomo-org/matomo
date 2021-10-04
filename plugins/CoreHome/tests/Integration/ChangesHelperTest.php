<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration;

use Piwik\Tests\Fixtures\CreateChangesJson;
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
     * @var CreateChangesJson
     */
    public static $fixture;

    public function test_CoreHomeChanges_ShouldSortChangeListMostRecentFirst()
    {

        $json = '{"title":"New feature z added","description":"Now you can do e with f like this",'.
                '"linkName":"For more information go here","link":"https:\/\/www.matomo.org","date":"2099-03-01","plugin":"CoreAdminHome"}';

        $a = ChangesHelper::getChanges();

        // getChanges will still find all changes for installed plugins, the fixture forces three changes to the top
        // of the list by using a distant future date, for comparison we will just compare the first change in the list
        $r = reset($a['changes']);

        $this->assertEquals($json, json_encode($r, true));
    }

}

ChangesHelperTest::$fixture = new CreateChangesJson();
