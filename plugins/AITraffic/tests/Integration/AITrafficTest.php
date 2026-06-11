<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\AITraffic\tests\Integration;

use Piwik\Menu\MenuAi;
use Piwik\Plugin\Menu;
use Piwik\Plugin\Report;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\AITraffic\AITraffic;
use Piwik\Plugins\AITraffic\Menu as AITrafficMenu;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group AITrafficTest
 * @group Plugins
 */
class AITrafficTest extends IntegrationTestCase
{
    private const AI_CATEGORY = 'General_AIAssistants';

    private $backupGet;
    private $backupRequest;
    private $backupQueryString;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupGet = $_GET;
        $this->backupRequest = $_REQUEST;
        $this->backupQueryString = $_SERVER['QUERY_STRING'] ?? null;
    }

    public function tearDown(): void
    {
        $_GET = $this->backupGet;
        $_REQUEST = $this->backupRequest;

        if ($this->backupQueryString === null) {
            unset($_SERVER['QUERY_STRING']);
        } else {
            $_SERVER['QUERY_STRING'] = $this->backupQueryString;
        }

        FakeAccess::clearAccess();

        parent::tearDown();
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    // -------------------------------------------------------------------------
    // Menu tests
    // -------------------------------------------------------------------------

    public function testConfigureAiInsightsMenuDoesNothingWithoutSite(): void
    {
        $menu = new AITrafficTestMenuAi([new AITrafficMenu($this->makeReportsProvider())]);

        $result = $menu->getMenu();

        $this->assertSame([], $result);
    }

    public function testConfigureAiInsightsMenuAddsSubcategoriesForAiAssistantsReports(): void
    {
        $idSite = Fixture::createWebsite('2020-01-01 00:00:00');
        FakeAccess::clearAccess(false, [], [$idSite], 'viewUser');
        $this->setRequest($idSite);

        // Use a fake reports provider so the test does not depend on which other plugins
        // (e.g. BotTracking) happen to be active and what reports they register.
        $reportsProvider = $this->makeReportsProvider([
            ['category' => self::AI_CATEGORY, 'subcategory' => 'TestAi_Details', 'order' => 20],
            ['category' => self::AI_CATEGORY, 'subcategory' => 'TestAi_Overview', 'order' => 10],
            ['category' => 'General_Visitors', 'subcategory' => 'TestAi_Ignored', 'order' => 5],
        ]);

        $menu = new AITrafficTestMenuAi([new AITrafficMenu($reportsProvider)]);
        $result = $menu->getMenu();

        $this->assertArrayHasKey('AITraffic_AITraffic', $result);
        $aiTrafficMenu = $result['AITraffic_AITraffic'];

        // Only AI Assistants subcategories are included, ordered by report order (10 before 20).
        $subMenuKeys = $this->getVisibleMenuKeys($aiTrafficMenu);
        $this->assertSame(['TestAi_Overview', 'TestAi_Details'], $subMenuKeys);
        $this->assertArrayNotHasKey('TestAi_Ignored', $aiTrafficMenu);

        $overviewUrl = $aiTrafficMenu['TestAi_Overview']['_url'];
        $this->assertSame('AITraffic', $overviewUrl['module']);
        $this->assertSame('index', $overviewUrl['action']);
        $this->assertSame(self::AI_CATEGORY, $overviewUrl['category']);
        $this->assertSame('TestAi_Overview', $overviewUrl['subcategory']);

        // The category default URL points at the first (lowest-ordered) subcategory.
        $this->assertSame($aiTrafficMenu['TestAi_Overview']['_url'], $aiTrafficMenu['_url']);
    }

    // -------------------------------------------------------------------------
    // Notice tests
    // -------------------------------------------------------------------------

    public function testNoticeDoesNotFireForNonDashboardContext(): void
    {
        $_GET = ['category' => self::AI_CATEGORY];
        $_REQUEST = $_GET;

        $out = '';
        (new AITraffic())->addMovingToAIInsightsNotice($out, 'aiInsights');

        $this->assertSame('', $out);
    }

    public function testNoticeDoesNotFireForNonAIAssistantsCategory(): void
    {
        $_GET = ['category' => 'General_Visitors'];
        $_REQUEST = $_GET;

        $out = '';
        (new AITraffic())->addMovingToAIInsightsNotice($out, 'dashboard');

        $this->assertSame('', $out);
    }

    public function testNoticeFiresForDashboardContextWithAIAssistantsCategory(): void
    {
        $_SERVER['QUERY_STRING'] = 'module=CoreHome&action=index&category=General_AIAssistants'
            . '&subcategory=BotTracking_AIChatbotsOverview&idSite=1&period=day&date=today';
        $_GET = ['category' => self::AI_CATEGORY];
        $_REQUEST = $_GET;

        $out = '';
        (new AITraffic())->addMovingToAIInsightsNotice($out, 'dashboard');

        $this->assertStringContainsString('alert-info', $out);
        $this->assertStringContainsString('module=AITraffic', $out);
        $this->assertStringContainsString('action=index', $out);
        $this->assertStringNotContainsString('subcategory=', $out);
    }

    /**
     * @param array<int, array{category: string, subcategory: ?string, order: int}> $reports
     */
    private function makeReportsProvider(array $reports = []): ReportsProvider
    {
        $instances = [];
        foreach ($reports as $report) {
            $instance = new AITrafficTestReport();
            $instance->setTestData($report['category'], $report['subcategory'], $report['order']);
            $instances[] = $instance;
        }

        return new AITrafficTestReportsProvider($instances);
    }

    private function setRequest(int $idSite): void
    {
        $_GET = [
            'idSite' => (string) $idSite,
            'period' => 'day',
            'date'   => 'today',
            'module' => 'AITraffic',
            'action' => 'index',
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

class AITrafficTestMenuAi extends MenuAi
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
    protected function getAllMenus()
    {
        return $this->menus;
    }
}

class AITrafficTestReportsProvider extends ReportsProvider
{
    /**
     * @var Report[]
     */
    private $reports;

    /**
     * @param Report[] $reports
     */
    public function __construct(array $reports)
    {
        $this->reports = $reports;
    }

    public function getAllReports()
    {
        return $this->reports;
    }
}

class AITrafficTestReport extends Report
{
    // Report::__construct() is final, so the test data is injected through a setter
    // rather than the constructor.
    public function setTestData(string $categoryId, ?string $subcategoryId, int $order): void
    {
        $this->categoryId = $categoryId;
        $this->subcategoryId = $subcategoryId;
        $this->order = $order;
    }
}
