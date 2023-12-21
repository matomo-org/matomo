<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Dashboard\tests\Integration;

use Piwik\Plugins\Dashboard\API;
use Piwik\Plugins\Dashboard\Dashboard;
use Piwik\Plugins\Dashboard\Model;
use Piwik\Plugins\UsersManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Dashboard
 * @group Plugins
 * @group APITest
 * @group Plugins
 */
class APITest extends IntegrationTestCase
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var API
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        UsersManager\API::getInstance()->addUser('eva', '12345.3m2', 'eva@example.com');

        $this->model = new Model();
        $this->api = API::getInstance();
    }

    public function testGetDashboardsNoParamsNoDashboardShouldReturnDefault()
    {
        $result = $this->api->getDashboards();
        $this->assertCount(1, $result);
    }

    public function testGetDashboardsNoDefaultShouldReturnEmpty()
    {
        $result = $this->api->getDashboards(null, false);
        $this->assertCount(0, $result);
    }

    public function testGetDashboardsShouldReturnOwnDashboardsForSuperUser()
    {
        $layout = '[[{"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}}]]';
        $this->model->createNewDashboardForUser('eva', 'any name', $layout);

        FakeAccess::$superUser = true;
        FakeAccess::$identity = 'eva';

        $result = $this->api->getDashboards('eva', false);

        $expected = [
            'name' => 'any name',
            'id' => 1,
            'widgets' => [
                ['module' => 'Live', 'action' => 'widget']
            ]
        ];

        $this->assertCount(1, $result);
        $this->assertEquals([$expected], $result);
    }

    public function testGetDashboardsShouldReturnForeignDashboardsForSuperUser()
    {
        $layout = '[[{"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}}]]';
        $this->model->createNewDashboardForUser('peter', 'any name', $layout);

        FakeAccess::$superUser = true;
        FakeAccess::$identity = 'eva';

        $result = $this->api->getDashboards('peter', false);
        $this->assertCount(1, $result);
    }


    public function testGetDashboardsShouldReturnOwnDashboardsForUser()
    {
        $layout = '[[{"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}}]]';
        $this->model->createNewDashboardForUser('eva', 'any name', $layout);

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'eva';

        $result = $this->api->getDashboards('eva', false);
        $this->assertCount(1, $result);
    }

    public function testGetDashboardsShouldNotReturnForeignDashboardsForNonSuperUser()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ExceptionCheckUserHasSuperUserAccessOrIsTheUser');

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'eva';

        $this->api->getDashboards('peter', false);
    }

    public function testCreateNewDashboardForOtherUserDoesNotWorkForNonSuperUser()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ExceptionCheckUserHasSuperUserAccessOrIsTheUser');

        FakeAccess::$superUser = false;

        $layout = '[[{"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}}]]';
        $this->api->createNewDashboardForUser('eva', 'name', $layout);
    }

    public function testCreateNewDashboardForAnonymousDoesNotWork()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_YouMustBeLoggedIn');

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'anonymous';

        $layout = '[[{"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}}]]';
        $this->api->createNewDashboardForUser('anonymous', 'name', $layout);
    }

    public function testCreateNewDashboardForAnonymousDoesNotWorkForSuperUser()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This method can\'t be performed for anonymous user');

        FakeAccess::$superUser = true;
        FakeAccess::$identity = 'eva';

        $layout = '[[{"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}}]]';
        $this->api->createNewDashboardForUser('anonymous', 'name', $layout);
    }

    public function testCreateNewDashboardForUserHimself()
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'eva';

        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertEmpty($dashboards);

        $this->api->createNewDashboardForUser('eva', 'name', $addDefaultWidgets = true);

        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertCount(1, $dashboards);
    }

    public function testCreateNewDashboardForOtherUser()
    {
        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertEmpty($dashboards);
        $this->api->createNewDashboardForUser('eva', 'name', $addDefaultWidgets = true);

        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertCount(1, $dashboards);
    }

    public function testCopyDashboardToUser()
    {
        $layout = '[[{"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}}]]';
        $id = $this->model->createNewDashboardForUser('superUserLogin', 'any name', $layout);

        $this->assertNotEmpty($id);

        $newId = $this->api->copyDashboardToUser($id, 'eva', 'new name');
        $dashboards = $this->model->getAllDashboardsForUser('eva');

        $this->assertCount(1, $dashboards);
        $dashboard = end($dashboards);
        $this->assertEquals($dashboard['iddashboard'], $newId);
        $this->assertEquals($dashboard['name'], 'new name');
        $this->assertEquals($dashboard['layout'], $layout);
    }

    public function testCopyDashboardToUserFails()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Dashboard not found');

        $this->api->copyDashboardToUser(5, 'eva', 'new name');
    }

    public function testRemoveDashboardForAnonymousDoesntWork()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_YouMustBeLoggedIn');

        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'anonymous';

        $this->api->removeDashboard(1, 'anonymous');
    }

    public function testRemoveDashboardForAnonymousDoesntWorkForSuperUser()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This method can\'t be performed for anonymous user');

        FakeAccess::$superUser = true;
        FakeAccess::$identity = 'eva';

        $this->api->removeDashboard(1, 'anonymous');
    }

    public function testRemoveDashboardForUserHimself()
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'eva';

        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertEmpty($dashboards);

        // first dashboard shouldn't be removed
        $this->api->createNewDashboardForUser('eva', 'name', $addDefaultWidgets = true);
        $id = $this->api->createNewDashboardForUser('eva', 'new name', $addDefaultWidgets = true);

        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertCount(2, $dashboards);

        $this->api->removeDashboard($id);

        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertCount(1, $dashboards);
    }

    public function testRemoveDashboardForOtherUser()
    {
        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertEmpty($dashboards);

        // first dashboard shouldn't be removed
        $this->api->createNewDashboardForUser('eva', 'name', $addDefaultWidgets = true);
        $id = $this->api->createNewDashboardForUser('eva', 'new name', $addDefaultWidgets = true);

        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertCount(2, $dashboards);

        $this->api->removeDashboard($id, 'eva');

        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertCount(1, $dashboards);
    }

    public function testRemoveDashboardAllowsRemovingFirst()
    {
        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertEmpty($dashboards);

        // we allow removing first dashboard for an automation use case, so tested here
        // but if another dashboard isn't immediately added, it can cause problems.
        $id = $this->api->createNewDashboardForUser('eva', 'name', $addDefaultWidgets = true);

        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertCount(1, $dashboards);

        $this->api->removeDashboard($id, 'eva');

        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertEmpty($dashboards);
    }

    public function testResetDashboardForAnonymousDoesntWork()
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'anonymous';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_YouMustBeLoggedIn');

        $this->api->resetDashboardLayout(1, 'anonymous');
    }

    public function testResetDashboardForAnonymousDoesntWorkForSuperUser()
    {
        FakeAccess::$superUser = true;
        FakeAccess::$identity = 'eva';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This method can\'t be performed for anonymous user');

        $this->api->resetDashboardLayout(1, 'anonymous');
    }

    public function testResetDashboard()
    {
        $db = new Dashboard();
        $dashboards = $this->model->getAllDashboardsForUser('eva');
        $this->assertEmpty($dashboards);

        $id = $this->api->createNewDashboardForUser('eva', 'name', false);

        $dashboard = $db->getLayoutForUser('eva', $id);
        $this->assertEquals('{}', $dashboard);

        $this->api->resetDashboardLayout($id, 'eva');

        $dashboard = $db->getLayoutForUser('eva', $id);
        $this->assertEquals($db->getDefaultLayout(), $dashboard);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
