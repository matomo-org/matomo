<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\tests\Integration;

use Piwik\Access;
use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\FrontController;
use Piwik\Plugins\Goals\API;
use Piwik\Plugins\Goals\Columns\GoalName;
use Piwik\Plugins\Goals\Visualizations\Goals as GoalsVisualization;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

/**
 * Everything that lists goals shows starred goals first and marks them as starred.
 *
 * @group Goals
 * @group Plugins
 * @group StarredGoalsDisplayOrderTest
 */
class StarredGoalsDisplayOrderTest extends IntegrationTestCase
{
    private const NAMES_BY_ID = [1 => 'Newsletter', 2 => 'Download', 3 => 'Contact', 4 => 'Goal 10', 5 => 'goal 2'];

    // by name: Contact, Download, goal 2, Goal 10, Newsletter
    private const ORDER_NOTHING_STARRED = [3, 2, 5, 4, 1];

    // "Goal 10" and "Newsletter" are starred
    private const STARRED = [4, 1];
    private const ORDER_STARRED_FIRST = [4, 1, 3, 2, 5];

    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser(true);
        $this->idSite = Fixture::createWebsite('2015-01-01 00:00:00');

        foreach (self::NAMES_BY_ID as $idGoal => $name) {
            $created = API::getInstance()->addGoal($this->idSite, $name, 'url', 'page-' . $idGoal, 'contains');
            self::assertEquals($idGoal, $created);
        }

        $_GET['idSite'] = (string) $this->idSite;
        $_GET['date']   = '2015-01-02';
        $_GET['period'] = 'day';
    }

    public function tearDown(): void
    {
        $_GET = [];

        parent::tearDown();
    }

    public function testGoalReportsAreOrderedByNameWhenNoGoalIsStarred(): void
    {
        foreach (['get', 'getDaysToConversion', 'getVisitsUntilConversion'] as $action) {
            $reports = $this->getGoalReports($action);

            self::assertSame(self::ORDER_NOTHING_STARRED, array_keys($reports), $action);
            foreach ($reports as $report) {
                // API.getReportMetadata removes attributes that are false
                self::assertArrayNotHasKey('isStarred', $report);
            }
        }
    }

    public function testGoalReportsOfStarredGoalsComeFirstAndAreMarkedAsStarred(): void
    {
        $nameBeforeStarring = $this->getGoalReports('get')[4]['name'];

        $this->starGoals();

        foreach (['get', 'getDaysToConversion', 'getVisitsUntilConversion'] as $action) {
            $reports = $this->getGoalReports($action);

            self::assertSame(self::ORDER_STARRED_FIRST, array_keys($reports), $action);
            self::assertSame(self::STARRED, $this->getIdsMarkedAsStarred($reports), $action);
        }

        // the name of a report does not change when its goal is starred
        self::assertSame($nameBeforeStarring, $this->getGoalReports('get')[4]['name']);
    }

    public function testGoalWidgetsAreOrderedByNameWhenNoGoalIsStarred(): void
    {
        $widgets = $this->getGoalWidgets();

        self::assertSame(self::ORDER_NOTHING_STARRED, array_keys($widgets));
        foreach ($widgets as $widget) {
            self::assertFalse($widget['isStarred']);
        }
    }

    public function testEveryWidgetAndItsSubcategorySayWhetherTheyAreStarred(): void
    {
        $this->starGoals();

        $widgets = Request::processRequest('API.getWidgetMetadata', [
            'idSite' => $this->idSite,
            'filter_limit' => '-1',
        ]);
        self::assertGreaterThan(count(self::NAMES_BY_ID), count($widgets));

        $starredUniqueIds = [];
        foreach ($widgets as $widget) {
            // the flag is always there, also for widgets that have nothing to do with goals
            self::assertArrayHasKey('isStarred', $widget, (string) $widget['uniqueId']);
            self::assertIsBool($widget['isStarred'], (string) $widget['uniqueId']);

            if (isset($widget['subcategory'])) {
                self::assertArrayHasKey('isStarred', $widget['subcategory'], (string) $widget['uniqueId']);
                self::assertIsBool($widget['subcategory']['isStarred'], (string) $widget['uniqueId']);
            }

            if ($widget['isStarred']) {
                $starredUniqueIds[] = $widget['uniqueId'];
            }
        }

        sort($starredUniqueIds);
        self::assertSame(['widgetGoal_1', 'widgetGoal_4'], $starredUniqueIds);
    }

    public function testGoalWidgetsOfStarredGoalsComeFirstAndAreMarkedAsStarred(): void
    {
        $this->starGoals();

        $widgets = $this->getGoalWidgets();

        self::assertSame(self::ORDER_STARRED_FIRST, array_keys($widgets));
        self::assertSame(self::STARRED, $this->getIdsMarkedAsStarred($widgets));

        // the name of a widget does not change when its goal is starred
        self::assertSame('Goal 10', $widgets[4]['name']);
    }

    public function testGoalPagesInTheReportingMenuAreOrderedByNameWhenNoGoalIsStarred(): void
    {
        $subcategories = $this->getGoalSubcategories();

        self::assertSame(self::ORDER_NOTHING_STARRED, array_keys($subcategories));
        foreach ($subcategories as $subcategory) {
            self::assertFalse($subcategory['isStarred']);
        }
    }

    public function testGoalPagesOfStarredGoalsComeFirstInTheReportingMenuAndAreMarkedAsStarred(): void
    {
        $this->starGoals();

        $subcategories = $this->getGoalSubcategories();

        self::assertSame(self::ORDER_STARRED_FIRST, array_keys($subcategories));
        self::assertSame(self::STARRED, $this->getIdsMarkedAsStarred($subcategories));
        self::assertSame('Goal 10', $subcategories[4]['name']);
    }

    public function testUnstarringAGoalMovesItBackAndRemovesTheFlag(): void
    {
        $this->starGoals();
        API::getInstance()->unstarGoal($this->idSite, 4);

        // only "Newsletter" is still starred
        $expectedOrder = [1, 3, 2, 5, 4];

        self::assertSame($expectedOrder, array_keys($this->getGoalReports('get')));
        self::assertSame($expectedOrder, array_keys($this->getGoalWidgets()));
        self::assertSame($expectedOrder, array_keys($this->getGoalSubcategories()));
        self::assertSame([1], $this->getIdsMarkedAsStarred($this->getGoalWidgets()));
        self::assertFalse($this->getGoalWidgets()[4]['isStarred']);
        self::assertFalse($this->getGoalSubcategories()[4]['isStarred']);
        self::assertArrayNotHasKey('isStarred', $this->getGoalReports('get')[4]);
    }

    public function testADeletedStarredGoalIsNoLongerListed(): void
    {
        $this->starGoals();
        API::getInstance()->deleteGoal($this->idSite, 4);

        $expectedOrder = [1, 3, 2, 5];

        self::assertSame($expectedOrder, array_keys($this->getGoalReports('get')));
        self::assertSame($expectedOrder, array_keys($this->getGoalWidgets()));
        self::assertSame($expectedOrder, array_keys($this->getGoalSubcategories()));
    }

    public function testTheGoalsTableShowsTheColumnsOfStarredGoalsFirst(): void
    {
        $view = $this->buildGoalsTable();
        self::assertSame(self::ORDER_NOTHING_STARRED, $this->getGoalIdsOfColumns($view, 'conversion_rate'));

        $this->starGoals();

        $view = $this->buildGoalsTable();
        self::assertSame(self::ORDER_STARRED_FIRST, $this->getGoalIdsOfColumns($view, 'conversion_rate'));
    }

    public function testTheGoalsTableWithAllGoalMetricsShowsTheColumnsOfStarredGoalsFirst(): void
    {
        $this->starGoals();

        // 0 requests the columns of all goals (AddColumnsProcessedMetricsGoal::GOALS_FULL_TABLE)
        $_GET['idGoal'] = '0';
        $view = $this->buildGoalsTable();

        foreach (['nb_conversions', 'conversion_rate', 'revenue', 'revenue_per_visit'] as $metric) {
            self::assertSame(self::ORDER_STARRED_FIRST, $this->getGoalIdsOfColumns($view, $metric), $metric);
        }
    }

    public function testTheGoalsTableMarksTheColumnHeadersOfStarredGoals(): void
    {
        $this->starGoals();

        $view = $this->buildGoalsTable();

        self::assertSame(['data-starred-goal' => 4], $view->getHeaderCellHtmlAttributes('goal_4_conversion_rate'));
        self::assertSame(['data-starred-goal' => 1], $view->getHeaderCellHtmlAttributes('goal_1_conversion_rate'));
        self::assertNull($view->getHeaderCellHtmlAttributes('goal_3_conversion_rate'));
        self::assertNull($view->getHeaderCellHtmlAttributes('label'));
        self::assertNull($view->getHeaderCellHtmlAttributes('nb_visits'));
        self::assertNull($view->getHeaderCellHtmlAttributes('revenue_per_visit'));
    }

    public function testTheGoalsOverviewListsStarredGoalsFirstAndMarksTheirTitles(): void
    {
        $html = $this->renderGoalsOverviewSparklines();
        self::assertSame(self::ORDER_NOTHING_STARRED, $this->getGoalIdsOfTitles($html));
        self::assertStringNotContainsString('data-starred-goal', $html);

        $this->starGoals();

        $html = $this->renderGoalsOverviewSparklines();
        self::assertSame(self::ORDER_STARRED_FIRST, $this->getGoalIdsOfTitles($html));

        preg_match_all('/data-starred-goal="(\d+)"/', $html, $matches);
        self::assertSame(self::STARRED, array_map('intval', $matches[1]));
    }

    public function testGoalNameSuggestionsForSegmentsListStarredGoalsFirst(): void
    {
        $this->starGoals();

        // the goals visitors converted, most converted first
        $table = new DataTable();
        $table->addRowsFromSimpleArray([
            ['visitConvertedGoalId__segmentValue' => 2],
            ['visitConvertedGoalId__segmentValue' => 5],
            ['visitConvertedGoalId__segmentValue' => 1],
            ['visitConvertedGoalId__segmentValue' => 3],
        ]);

        $suggestions = GoalName::getGoalNameForSuggestedValues($this->idSite, 10, $table);

        self::assertSame(['Newsletter', 'Contact', 'Download', 'goal 2'], $suggestions);
    }

    private function starGoals(): void
    {
        foreach (self::STARRED as $idGoal) {
            API::getInstance()->starGoal($this->idSite, $idGoal);
        }
    }

    /**
     * @return array<int, array<string, mixed>> report metadata by goal ID, in the order of the reports
     */
    private function getGoalReports(string $action): array
    {
        $reports = Request::processRequest('API.getReportMetadata', [
            'idSites' => $this->idSite,
            'period' => 'day',
            'date' => '2015-01-02',
            'filter_limit' => '-1',
        ]);

        $goalReports = [];
        foreach ($reports as $report) {
            $idGoal = $report['parameters']['idGoal'] ?? null;
            if ('Goals' === $report['module'] && $action === $report['action'] && is_numeric($idGoal)) {
                $goalReports[(int) $idGoal] = $report;
            }
        }

        return $goalReports;
    }

    /**
     * @return array<int, array<string, mixed>> widget metadata by goal ID, in the order of the widgets
     */
    private function getGoalWidgets(): array
    {
        $widgets = Request::processRequest('API.getWidgetMetadata', [
            'idSite' => $this->idSite,
            'filter_limit' => '-1',
        ]);

        $goalWidgets = [];
        foreach ($widgets as $widget) {
            if (preg_match('/^widgetGoal_(\d+)$/', (string) $widget['uniqueId'], $matches)) {
                $goalWidgets[(int) $matches[1]] = $widget;
            }
        }

        return $goalWidgets;
    }

    /**
     * @return array<int, array<string, mixed>> subcategory metadata by goal ID, in the order of the reporting menu
     */
    private function getGoalSubcategories(): array
    {
        $pages = Request::processRequest('API.getReportPagesMetadata', [
            'idSite' => $this->idSite,
            'filter_limit' => '-1',
        ]);

        $subcategories = [];
        foreach ($pages as $page) {
            if ('Goals_Goals' === $page['category']['id'] && is_numeric($page['subcategory']['id'])) {
                $subcategories[(int) $page['subcategory']['id']] = $page['subcategory'];
            }
        }

        // the reporting menu orders the pages of a category by the order of their subcategory
        uasort($subcategories, function (array $a, array $b): int {
            return $a['order'] <=> $b['order'];
        });

        return $subcategories;
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     * @return list<int>
     */
    private function getIdsMarkedAsStarred(array $entries): array
    {
        $starred = array_filter($entries, function (array $entry): bool {
            return true === ($entry['isStarred'] ?? null);
        });

        return array_keys($starred);
    }

    private function buildGoalsTable(): GoalsVisualization
    {
        /** @var GoalsVisualization $view */
        $view = ViewDataTableFactory::build(
            GoalsVisualization::ID,
            'Referrers.getReferrerType',
            'Referrers.getReferrerType'
        );
        $view->setDataTable(new DataTable());
        $view->beforeLoadDataTable();

        return $view;
    }

    /**
     * @return list<int> the goal IDs of the columns of the given metric, in the order of the columns
     */
    private function getGoalIdsOfColumns(GoalsVisualization $view, string $metric): array
    {
        $idGoals = [];
        foreach ($view->config->columns_to_display as $column) {
            if (preg_match('/^goal_(\d+)_' . preg_quote($metric, '/') . '$/', (string) $column, $matches)) {
                $idGoals[] = (int) $matches[1];
            }
        }

        return $idGoals;
    }

    private function renderGoalsOverviewSparklines(): string
    {
        $_GET['module'] = 'Goals';
        $_GET['action'] = 'getSparklines';

        return (string) Access::doAsSuperUser(function () {
            return FrontController::getInstance()->fetchDispatch('Goals', 'getSparklines');
        });
    }

    /**
     * @return list<int> the goal IDs of the titles on the Goals overview, in the order of the page
     */
    private function getGoalIdsOfTitles(string $html): array
    {
        preg_match_all('/goal-page-link="(\d+)"/', $html, $matches);

        return array_map('intval', $matches[1]);
    }
}
