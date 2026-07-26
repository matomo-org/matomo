<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Integration;

use Piwik\Menu\MenuAdmin;
use Piwik\Plugins\DebugView\Menu;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group DebugView
 * @group DebugViewMenuTest
 * @group Plugins
 */
class MenuTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (class_exists('\Piwik\Plugins\TagManager\TagManager')) {
            \Piwik\Plugins\TagManager\TagManager::$enableAutoContainerCreation = false;
        }

        Fixture::createSuperUser();
        FakeAccess::$superUser = true;

        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2020-01-01 00:00:00');
        }
    }

    public function testMenuAddsDiagnosticItemWhenVisitorLogEnabled()
    {
        $items = $this->configureMenu();

        $this->assertSame([['CoreAdminHome_MenuDiagnostic', 'DebugView_DebugView']], $items);
    }

    public function testMenuAddsItemForViewOnlyUser()
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'aUser';
        FakeAccess::$idSitesView = [1];
        FakeAccess::$idSitesAdmin = [];

        $items = $this->configureMenu();

        $this->assertSame([['CoreAdminHome_MenuDiagnostic', 'DebugView_DebugView']], $items);
    }

    public function testMenuAddsNothingForAnonymousUser()
    {
        FakeAccess::$superUser = false;
        FakeAccess::$identity = 'anonymous';
        FakeAccess::$idSitesView = [1];
        FakeAccess::$idSitesAdmin = [];

        $this->assertSame([], $this->configureMenu());
    }

    public function testMenuAddsItemEvenWhenVisitorLogDisabledSystemWide()
    {
        $settings = new \Piwik\Plugins\Live\SystemSettings();
        $settings->disableVisitorLog->setValue(true);
        $settings->save();
        \Piwik\Cache::getTransientCache()->flushAll();

        $items = $this->configureMenu();

        // deliberately shown: the page itself explains why Debug View cannot
        // be used, instead of hiding that the feature exists
        $this->assertSame([['CoreAdminHome_MenuDiagnostic', 'DebugView_DebugView']], $items);
    }

    private function configureMenu(): array
    {
        $capturingMenu = new class extends MenuAdmin {
            public $items = [];

            public function __construct()
            {
            }

            public function addItem(string $menuName, ?string $subMenuName, $url, int $order = 50, $tooltip = false, $icon = false, $onclick = false, $attribute = false, $help = false, int $badgeCount = 0, string $cssClass = '')
            {
                $this->items[] = [$menuName, $subMenuName];
            }
        };

        (new Menu())->configureAdminMenu($capturingMenu);

        return $capturingMenu->items;
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
