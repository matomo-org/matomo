<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration;

use Piwik\Category\Subcategory;
use Piwik\EventDispatcher;
use Piwik\Menu\MenuTop;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreHome
 * @group CoreHomeTest
 * @group Menu
 */
class ReportingMenuGroupsTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2020-01-01 00:00:00');
        $_GET['idSite'] = 1;

        // Add a subcategory into the AI Assistants category (which opts into the "AI Insights" group),
        // so the section has a landing page even when no AI plugins register their own subcategories.
        EventDispatcher::getInstance()->addObserver('Category.addSubcategories', function (&$subcategories) {
            $subcategory = new Subcategory();
            $subcategory->setId('CoreHome_TestAiInsightsSub');
            $subcategory->setCategoryId('General_AIAssistants');
            $subcategory->setOrder(1);
            $subcategories[] = $subcategory;
        });

        MenuTop::unsetInstance();
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);
        MenuTop::unsetInstance();
        parent::tearDown();
    }

    public function testTopMenuContainsAnEntryForTheAiInsightsReportingGroup()
    {
        $menu = MenuTop::getInstance()->getMenu();

        $this->assertArrayHasKey('CoreHome_AIInsights', $menu);

        // The section is carried in the URL hash (not the query string) so it does not leak into other
        // links; category/subcategory are resolved client-side by the reporting SPA.
        $url = $menu['CoreHome_AIInsights']['_url'];
        $this->assertIsString($url);
        $this->assertStringStartsWith('index.php?', $url);

        [$queryPart, $hashPart] = explode('#', $url, 2);
        $this->assertStringContainsString('module=CoreHome', $queryPart);
        $this->assertStringContainsString('action=index', $queryPart);
        $this->assertStringNotContainsString('group=', $queryPart);
        $this->assertStringContainsString('group=CoreHome_AIInsights', $hashPart);

        // the entry is tagged so the active highlight can be synced client-side from the hash
        $this->assertSame('data-reporting-group="CoreHome_AIInsights"', $menu['CoreHome_AIInsights']['_attribute']);
    }

    public function testTopMenuDoesNotCreateEntriesForDefaultGroupCategories()
    {
        $menu = MenuTop::getInstance()->getMenu();

        // Regular (Analytics) categories must not get their own top-menu entry.
        $this->assertArrayNotHasKey('General_Visitors', $menu);
        $this->assertArrayNotHasKey('General_Actions', $menu);
    }

    public function testAnalyticsTopMenuEntryCarriesTheDefaultReportingGroupInTheHashOnly()
    {
        $menu = MenuTop::getInstance()->getMenu();

        $this->assertArrayHasKey('Dashboard_TopMenuTitle', $menu);

        // The (default) section is carried in the hash with an empty group id, not in the query string.
        $url = $menu['Dashboard_TopMenuTitle']['_url'];
        $this->assertIsString($url);
        $this->assertStringStartsWith('index.php?', $url);

        [$queryPart, $hashPart] = explode('#', $url, 2);
        $this->assertStringContainsString('module=CoreHome', $queryPart);
        $this->assertStringContainsString('action=index', $queryPart);
        $this->assertStringNotContainsString('group=', $queryPart);
        $this->assertStringContainsString('group=', $hashPart);
        $this->assertStringNotContainsString('group=CoreHome', $hashPart);

        // the entry is tagged as the default section so the active highlight can be synced client-side
        $this->assertSame('data-reporting-group=""', $menu['Dashboard_TopMenuTitle']['_attribute']);
    }
}
