<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\tests\Integration\Visualizations;

use Piwik\DataTable;
use Piwik\Plugins\Goals\Visualizations\Goals;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

/**
 * @group Goals
 * @group GoalsVisualization
 */
class GoalsTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-01 00:00:00');

        $_GET['idSite'] = 1;
        $_GET['date']   = '2014-01-01';
        $_GET['period'] = 'day';
    }

    public function tearDown(): void
    {
        unset($_GET['idSite'], $_GET['date'], $_GET['period'], $_GET['idGoal'], $_GET['filter_sort_column'], $_GET['filter_sort_order']);

        parent::tearDown();
    }

    /**
     * @dataProvider getSingleGoalViews
     */
    public function testSortRequestedByTheUserIsKept(string $apiMethod, string $idGoal): void
    {
        $_GET['idGoal'] = $idGoal;
        $_GET['filter_sort_column'] = 'revenue';
        $_GET['filter_sort_order'] = 'asc';

        $view = $this->buildGoalsView($apiMethod);
        $view->beforeLoadDataTable();

        $this->assertSame('revenue', $view->requestConfig->filter_sort_column);
        $this->assertSame('asc', $view->requestConfig->filter_sort_order);

        // the browser relies on the sort sent back for the sort icon and to toggle the order on the next click
        $clientSideParameters = (new \ReflectionMethod($view, 'getClientSideParametersToSet'))->invoke($view);
        $this->assertSame('revenue', $clientSideParameters['filter_sort_column']);
        $this->assertSame('asc', $clientSideParameters['filter_sort_order']);
    }

    /**
     * @dataProvider getDefaultSortsOfSingleGoalViews
     */
    public function testSortDefaultsToTheGoalColumnWhenNoneIsRequested(string $apiMethod, string $idGoal, string $expectedSortColumn): void
    {
        $_GET['idGoal'] = $idGoal;

        $view = $this->buildGoalsView($apiMethod);
        $view->beforeLoadDataTable();

        $this->assertSame($expectedSortColumn, $view->requestConfig->filter_sort_column);
        $this->assertSame('desc', $view->requestConfig->filter_sort_order);
    }

    public function getSingleGoalViews(): iterable
    {
        foreach ($this->getDefaultSortsOfSingleGoalViews() as $name => [$apiMethod, $idGoal]) {
            yield $name => [$apiMethod, $idGoal];
        }
    }

    public function getDefaultSortsOfSingleGoalViews(): iterable
    {
        yield 'site goal' => ['Referrers.getReferrerType', '1', 'goal_1_nb_conversions'];
        yield 'ecommerce order' => ['Referrers.getReferrerType', 'ecommerceOrder', 'goal_ecommerceOrder_revenue'];
        yield 'site goal, pages' => ['Actions.getPageUrls', '1', 'goal_1_nb_conversions_attrib'];
        yield 'ecommerce order, pages' => ['Actions.getPageUrls', 'ecommerceOrder', 'goal_ecommerceOrder_revenue_attrib'];
        yield 'site goal, entry pages' => ['Actions.getEntryPageUrls', '1', 'goal_1_nb_conversions_entry'];
        yield 'ecommerce order, entry pages' => ['Actions.getEntryPageUrls', 'ecommerceOrder', 'goal_ecommerceOrder_revenue_entry'];
    }

    public function testExportIsRestrictedToTheDisplayedColumnsForASingleGoal(): void
    {
        $_GET['idGoal'] = '1';

        $view = $this->buildGoalsView();
        $view->beforeLoadDataTable();
        $view->beforeRender();

        // the export must return the displayed goal's columns instead of the aggregated all-goals
        // columns and every other goal's columns
        $this->assertArrayHasKey('showColumns', $view->config->export_parameters_to_modify);
        $this->assertSame(
            implode(',', $view->config->columns_to_display),
            $view->config->export_parameters_to_modify['showColumns']
        );
        $this->assertStringContainsString('goal_1_nb_conversions', $view->config->export_parameters_to_modify['showColumns']);

        // a flattened export must keep its dimension columns despite the allowlist
        $this->assertSame(1, $view->config->export_parameters_to_modify['keep_flattened_dimension_columns']);
    }

    public function testExportIsNotRestrictedForTheGoalsOverview(): void
    {
        $view = $this->buildGoalsView();
        $view->beforeLoadDataTable();
        $view->beforeRender();

        $this->assertArrayNotHasKey('showColumns', $view->config->export_parameters_to_modify);
        $this->assertArrayNotHasKey('keep_flattened_dimension_columns', $view->config->export_parameters_to_modify);
    }

    private function buildGoalsView(string $apiMethod = 'Referrers.getReferrerType'): Goals
    {
        /** @var Goals $view */
        $view = ViewDataTableFactory::build(
            Goals::ID,
            $apiMethod,
            $controllerAction = $apiMethod,
            $forceDefault = false,
            $loadViewDataTableParametersForUser = false
        );

        // the report data itself is irrelevant here, but the render hooks expect a loaded table
        $view->setDataTable(new DataTable());

        return $view;
    }
}
