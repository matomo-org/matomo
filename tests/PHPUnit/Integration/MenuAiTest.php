<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Menu\MenuAi;
use Piwik\Menu\MenuTop;
use Piwik\Plugin\ControllerAi;
use Piwik\Plugin\Menu;
use Piwik\Plugins\CoreHome\Menu as CoreHomeMenu;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\View;

/**
 * @group MenuAiTest
 * @group Core
 */
class MenuAiTest extends IntegrationTestCase
{
    /**
     * @var array
     */
    private $backupGet;

    /**
     * @var array
     */
    private $backupRequest;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupGet = $_GET;
        $this->backupRequest = $_REQUEST;
    }

    public function tearDown(): void
    {
        $_GET = $this->backupGet;
        $_REQUEST = $this->backupRequest;

        MenuAi::unsetInstance();
        MenuTop::unsetInstance();
        FakeAccess::clearAccess();

        parent::tearDown();
    }

    public function provideContainerConfig(): array
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    public function testMenuAiBuildsEntriesFromConfigureAiInsightsMenu(): void
    {
        $menu = new MenuAiTestMenuAi([new MenuAiTestConfiguredMenu()]);

        $result = $menu->getMenu();

        $this->assertSame(['TestAi_Settings', 'TestAi_Reports'], array_keys($result));
        $this->assertSame(['TestAi_Overview', 'TestAi_Details'], $this->getVisibleMenuKeys($result['TestAi_Reports']));
    }

    public function testMenuAiDefaultUrlUsesFirstOrderedSubMenuUrl(): void
    {
        $menu = new MenuAiTestMenuAi([new MenuAiTestConfiguredMenu()]);

        $this->assertSame(
            ['module' => 'TestAi', 'action' => 'overview'],
            $menu->getDefaultUrl()
        );
    }

    public function testCoreHomeDoesNotAddAiInsightsTopMenuItemWhenAiMenuIsEmpty(): void
    {
        MenuAi::setSingletonInstance(new MenuAiTestMenuAi([]));

        $topMenu = $this->buildTopMenuWithDashboard();
        (new CoreHomeMenu())->configureTopMenu($topMenu);

        $result = $topMenu->getMenu();

        $this->assertArrayNotHasKey('CoreHome_AIInsights', $result);
    }

    public function testCoreHomeAddsAiInsightsTopMenuItemAfterDashboardWhenAiMenuHasItems(): void
    {
        MenuAi::setSingletonInstance(new MenuAiTestMenuAi([new MenuAiTestSubmenuOnlyMenu()]));

        $topMenu = $this->buildTopMenuWithDashboard();
        (new CoreHomeMenu())->configureTopMenu($topMenu);

        $result = $topMenu->getMenu();

        $this->assertSame(['Dashboard_Dashboard', 'CoreHome_AIInsights'], array_slice(array_keys($result), 0, 2));
        $this->assertSame(2, $result['CoreHome_AIInsights']['_order']);
        $this->assertSame(
            ['module' => 'TestAi', 'action' => 'overview'],
            $result['CoreHome_AIInsights']['_url']
        );
    }

    public function testControllerAiAssignsAiMenuAndTopMenuActiveRoute(): void
    {
        $idSite = Fixture::createWebsite('2020-01-01 00:00:00');
        FakeAccess::clearAccess(false, [], [$idSite], 'viewUser');
        $this->setRequestForSite($idSite);
        MenuAi::setSingletonInstance(new MenuAiTestMenuAi([new MenuAiTestSubmenuOnlyMenu()]));

        $view = new View('@CoreHome/_notifications');
        (new MenuAiTestController())->assignGeneralVariables($view);
        $vars = $view->getTemplateVars();

        $this->assertArrayHasKey('aiMenu', $vars);
        $this->assertSame(['TestAi_Reports'], array_keys($vars['aiMenu']));
        $this->assertSame('TestAi', $vars['topMenuModule']);
        $this->assertSame('overview', $vars['topMenuAction']);
    }

    public function testControllerAiAssignsCategoryAndSubcategoryFromRequest(): void
    {
        $idSite = Fixture::createWebsite('2020-01-01 00:00:00');
        FakeAccess::clearAccess(false, [], [$idSite], 'viewUser');
        $_GET = [
            'idSite'      => (string) $idSite,
            'period'      => 'day',
            'date'        => 'today',
            'module'      => 'AITraffic',
            'action'      => 'index',
            'category'    => 'General_AIAssistants',
            'subcategory' => 'BotTracking_AIChatbotsOverview',
        ];
        $_REQUEST = $_GET;
        MenuAi::setSingletonInstance(new MenuAiTestMenuAi([]));

        $view = new View('@CoreHome/_notifications');
        (new MenuAiTestController())->assignGeneralVariables($view);
        $vars = $view->getTemplateVars();

        $this->assertSame('General_AIAssistants', $vars['currentCategory']);
        $this->assertSame('BotTracking_AIChatbotsOverview', $vars['currentSubcategory']);
    }

    public function testControllerAiCurrentCategoryDefaultsToEmptyStringWhenNotInRequest(): void
    {
        $idSite = Fixture::createWebsite('2020-01-01 00:00:00');
        FakeAccess::clearAccess(false, [], [$idSite], 'viewUser');
        $this->setRequestForSite($idSite);
        MenuAi::setSingletonInstance(new MenuAiTestMenuAi([]));

        $view = new View('@CoreHome/_notifications');
        (new MenuAiTestController())->assignGeneralVariables($view);
        $vars = $view->getTemplateVars();

        $this->assertSame('', $vars['currentCategory']);
        $this->assertSame('', $vars['currentSubcategory']);
    }

    private function buildTopMenuWithDashboard(): MenuAiTestTopMenu
    {
        $topMenu = new MenuAiTestTopMenu();
        $topMenu->addItem('Dashboard_Dashboard', null, ['module' => 'CoreHome', 'action' => 'index'], 1);

        return $topMenu;
    }

    private function setRequestForSite(int $idSite): void
    {
        $_GET = [
            'idSite' => (string) $idSite,
            'period' => 'day',
            'date' => 'today',
            'module' => 'TestAi',
            'action' => 'details',
        ];
        $_REQUEST = $_GET;
    }

    private function getVisibleMenuKeys(array $menu): array
    {
        $keys = [];
        foreach (array_keys($menu) as $key) {
            if (strpos((string) $key, '_') !== 0) {
                $keys[] = $key;
            }
        }

        return $keys;
    }
}

class MenuAiTestMenuAi extends MenuAi
{
    /**
     * @var Menu[]
     */
    private $menus;

    /**
     * @param Menu[] $menus
     */
    public function __construct(array $menus)
    {
        $this->menus = $menus;
    }

    /**
     * @return Menu[]
     */
    protected function getAllMenus(): array
    {
        return $this->menus;
    }
}

class MenuAiTestTopMenu extends MenuTop
{
    public function __construct()
    {
    }

    /**
     * @return Menu[]
     */
    protected function getAllMenus(): array
    {
        return [];
    }
}

class MenuAiTestConfiguredMenu extends Menu
{
    public function configureAiInsightsMenu(MenuAi $menu): void
    {
        $menu->addItem('TestAi_Reports', 'TestAi_Details', ['module' => 'TestAi', 'action' => 'details'], 20);
        $menu->addItem('TestAi_Reports', 'TestAi_Overview', ['module' => 'TestAi', 'action' => 'overview'], 10);
        $menu->addItem('TestAi_Settings', null, ['module' => 'TestAi', 'action' => 'settings'], 5);
    }
}

class MenuAiTestSubmenuOnlyMenu extends Menu
{
    public function configureAiInsightsMenu(MenuAi $menu): void
    {
        $menu->addItem('TestAi_Reports', 'TestAi_Details', ['module' => 'TestAi', 'action' => 'details'], 20);
        $menu->addItem('TestAi_Reports', 'TestAi_Overview', ['module' => 'TestAi', 'action' => 'overview'], 10);
    }
}

class MenuAiTestController extends ControllerAi
{
    public function assignGeneralVariables(View $view): void
    {
        $this->setGeneralVariablesView($view);
    }
}
