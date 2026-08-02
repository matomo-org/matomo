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
        unset($_GET['idSite'], $_GET['date'], $_GET['period'], $_GET['idGoal']);

        parent::tearDown();
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
    }

    public function testExportIsNotRestrictedForTheGoalsOverview(): void
    {
        $view = $this->buildGoalsView();
        $view->beforeLoadDataTable();
        $view->beforeRender();

        $this->assertArrayNotHasKey('showColumns', $view->config->export_parameters_to_modify);
    }

    private function buildGoalsView(): Goals
    {
        /** @var Goals $view */
        $view = ViewDataTableFactory::build(
            Goals::ID,
            'Referrers.getReferrerType',
            $controllerAction = 'Referrers.getReferrerType',
            $forceDefault = false,
            $loadViewDataTableParametersForUser = false
        );

        // the report data itself is irrelevant here, but the render hooks expect a loaded table
        $view->setDataTable(new DataTable());

        return $view;
    }
}
