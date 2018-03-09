<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Dashboard\tests\Integration;

use Piwik\Plugins\Dashboard\API;
use Piwik\Plugins\Dashboard\Model;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group GoalsDashboard
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

    public function setUp()
    {
        parent::setUp();

        Fixture::createSuperUser();

        $this->model = new Model();
        $this->api = API::getInstance();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage General_ExceptionCheckUserHasSuperUserAccessOrIsTheUser
     */
    public function testCreateNewDashboardForOtherUserDoesNotWorkForNonSuperUser()
    {
        FakeAccess::$superUser = false;

        $layout ='[[{"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}}]]';
        $this->api->createNewDashboardForUser('eva', 'name', $layout);
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
        $layout ='[[{"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}}]]';
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

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Dashboard not found
     */
    public function testCopyDashboardToUserFails()
    {
        $this->api->copyDashboardToUser(5, 'eva', 'new name');
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

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}