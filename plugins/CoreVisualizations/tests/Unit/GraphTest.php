<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\tests\Unit;

use Piwik\DataTable;

/**
 * @group CoreVisualizations
 * @group SparklinesConfigTest
 * @group Sparklines
 * @group Plugins
 */
class GraphTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        unset($_GET['idGoal']);
        parent::tearDown();
    }

    public function testSelectableColumnsAlreadySet()
    {
        $bar = $this->getMockGraph(array());
        $bar->config->selectable_columns = array('nb_abc', 'nb_xyz');
        $bar->afterAllFiltersAreApplied();

        $columns = array_column($bar->config->selectable_columns, 'column');
        $this->assertEquals(array('nb_abc', 'nb_xyz'), $columns);
    }

    public function testSelectableColumnsNbVisitsOnlyPresent()
    {
        $bar = $this->getMockGraph(array(
            'label' => 'abc',
            'nb_visits' => 0,
        ));

        $bar->afterAllFiltersAreApplied();

        $columns = array_column($bar->config->selectable_columns, 'column');
        $this->assertEquals(array('nb_visits'), $columns);
    }

    public function testSelectableColumnsUniqueVisitorsPresent()
    {
        $bar = $this->getMockGraph(array(
            'label' => 'abc',
            'nb_visits' => 5,
            'nb_uniq_visitors' => 2,
            'nb_something_else' => 1,
        ));

        $bar->afterAllFiltersAreApplied();

        $columns = array_column($bar->config->selectable_columns, 'column');
        $this->assertEquals(array('nb_visits', 'nb_uniq_visitors'), $columns);
    }

    public function testSelectableColumnsNoDefaultColumnsPresent()
    {
        $bar = $this->getMockGraph(array(
            'label' => 'abc',
            'foo' => 5,
            'bar' => 2,
        ));

        $bar->afterAllFiltersAreApplied();

        $columns = array_column($bar->config->selectable_columns, 'column');
        $this->assertEquals(array('foo', 'bar'), $columns);
    }

    public function testSelectableColumnsNoDefaultColumnsPresentLabelNotPresent()
    {
        $bar = $this->getMockGraph(array(
            'foo' => 5,
            'bar' => 2,
        ));

        $bar->afterAllFiltersAreApplied();

        $columns = array_column($bar->config->selectable_columns, 'column');
        $this->assertEquals(array('foo', 'bar'), $columns);
    }

    public function testSelectableColumnsAlreadySetToEmptyArray()
    {
        $bar = $this->getMockGraph(array(
            'label' => 'abc',
            'foo' => 5,
            'bar' => 2,
        ));
        $bar->config->selectable_columns = array();

        $bar->afterAllFiltersAreApplied();

        $this->assertEmpty($bar->config->selectable_columns);
    }

    public function testColumnsToDisplayNotInDataset()
    {
        $bar = $this->getMockGraph(array(
            'label' => 'abc',
            'nb_visits' => 25,
            'nb_actions' => 80,
        ));
        // Not present in the dataset = should be thrown out and replaced with first value from dataset
        $bar->config->columns_to_display = array('nb_uniq_visitors');

        $bar->afterAllFiltersAreApplied();

        $this->assertEquals(array('nb_visits'), $bar->config->columns_to_display);
    }

    public function testColumnsToDisplayNotInSelectableColumns()
    {
        $bar = $this->getMockGraph(array(
            'label' => 'abc',
            'nb_visits' => 25,
            'nb_actions' => 80,
        ));
        // columns_to_display is not present in selectable_columbs, but as long as it's in the data set that's OK
        $bar->config->selectable_columns = array('nb_visits');
        $bar->config->columns_to_display = array('nb_actions');

        $bar->afterAllFiltersAreApplied();

        $this->assertEquals(array('nb_actions'), $bar->config->columns_to_display);
    }

    public function testSelectableColumnsUsesGoalSpecificColumnsWhenSpecificGoalIsSelected()
    {
        $_GET['idGoal'] = '1';

        $bar = $this->getMockGraph(array(
            'label'                 => 'abc',
            'nb_visits'             => 5,
            'nb_conversions'        => 12,
            'revenue'               => 30,
            'goal_1_nb_conversions' => 3,
            'goal_1_revenue'        => 8,
        ));
        $bar->config->show_goals = true;

        $bar->afterAllFiltersAreApplied();

        $columns = array_column($bar->config->selectable_columns, 'column');
        // the per-goal columns are offered, not the aggregated (all-goals) ones
        $this->assertContains('goal_1_nb_conversions', $columns);
        $this->assertContains('goal_1_revenue', $columns);
        $this->assertNotContains('nb_conversions', $columns);
        $this->assertNotContains('revenue', $columns);
    }

    public function testSelectableColumnsUsesAggregatedGoalColumnsWhenNoSpecificGoalIsSelected()
    {
        $bar = $this->getMockGraph(array(
            'label'          => 'abc',
            'nb_visits'      => 5,
            'nb_conversions' => 12,
            'revenue'        => 30,
        ));
        $bar->config->show_goals = true;

        $bar->afterAllFiltersAreApplied();

        $columns = array_column($bar->config->selectable_columns, 'column');
        $this->assertContains('nb_conversions', $columns);
        $this->assertContains('revenue', $columns);
    }

    public function testBeforeLoadDataTableRemapsAggregatedGoalColumnsForSpecificGoal()
    {
        $_GET['idGoal'] = '2';

        $bar = $this->getMockGraph(array('label' => 'abc', 'nb_visits' => 5));
        $bar->config->show_goals = true;
        $bar->config->columns_to_display = array('label', 'nb_conversions', 'revenue', 'nb_visits');

        $bar->beforeLoadDataTable();

        // aggregated goal columns are swapped for the selected goal's columns, other columns kept
        $this->assertSame(
            array('label', 'goal_2_nb_conversions', 'goal_2_revenue', 'nb_visits'),
            array_values($bar->config->columns_to_display)
        );
        // the goal-column processing is triggered so those columns get computed
        $this->assertSame(2, $bar->requestConfig->request_parameters_to_modify['filter_update_columns_when_show_all_goals']);
        $this->assertSame(2, $bar->requestConfig->request_parameters_to_modify['filter_show_goal_columns_process_goals']);
    }

    public function testGoalColumnsNotUsedForActionsPageReport()
    {
        // Actions page/entry reports expose goal metrics through different columns
        // (goal_X_nb_conversions_attrib etc.), so the normal goal columns must not be forced here.
        $_GET['idGoal'] = '1';

        $bar = $this->getMockGraph(array(
            'label'                 => 'abc',
            'nb_visits'             => 5,
            'nb_conversions'        => 12,
            'goal_1_nb_conversions' => 3,
        ));
        $bar->config->show_goals = true;
        $bar->requestConfig->apiMethodToRequestDataTable = 'Actions.getPageUrls';

        $bar->afterAllFiltersAreApplied();

        $columns = array_column($bar->config->selectable_columns, 'column');
        $this->assertContains('nb_conversions', $columns);
        $this->assertNotContains('goal_1_nb_conversions', $columns);
    }

    public function testEcommerceOrderGoalUsesGoalSpecificColumns()
    {
        $_GET['idGoal'] = 'ecommerceOrder';

        $bar = $this->getMockGraph(array(
            'label'                             => 'abc',
            'nb_visits'                         => 5,
            'nb_conversions'                    => 12,
            'revenue'                           => 30,
            'goal_ecommerceOrder_nb_conversions' => 3,
            'goal_ecommerceOrder_revenue'        => 8,
        ));
        $bar->config->show_goals = true;

        $bar->afterAllFiltersAreApplied();

        $columns = array_column($bar->config->selectable_columns, 'column');
        $this->assertContains('goal_ecommerceOrder_nb_conversions', $columns);
        $this->assertContains('goal_ecommerceOrder_revenue', $columns);
        $this->assertNotContains('nb_conversions', $columns);
    }

    public function testBeforeRenderRestrictsExportColumnsWhenChartingSpecificGoalMetric()
    {
        $_GET['idGoal'] = '1';

        $bar = $this->getMockGraph(array('label' => 'abc', 'goal_1_nb_conversions' => 3));
        $bar->config->show_goals = true;
        $bar->config->columns_to_display = array('goal_1_nb_conversions');

        $bar->beforeRender();

        // export is restricted to the charted goal column so the exported data matches the chart
        $this->assertSame(
            'label,goal_1_nb_conversions',
            $bar->config->export_parameters_to_modify['showColumns']
        );
    }

    public function testBeforeRenderDoesNotRestrictExportForNonGoalMetric()
    {
        $_GET['idGoal'] = '1';

        $bar = $this->getMockGraph(array('label' => 'abc', 'nb_visits' => 5));
        $bar->config->show_goals = true;
        // charting a non-goal metric while a goal is selected must not restrict the export
        $bar->config->columns_to_display = array('nb_visits');

        $bar->beforeRender();

        $this->assertArrayNotHasKey('showColumns', $bar->config->export_parameters_to_modify);
    }

    public function testBeforeRenderDoesNotRestrictExportWhenNoSpecificGoal()
    {
        $bar = $this->getMockGraph(array('label' => 'abc', 'nb_conversions' => 5));
        $bar->config->show_goals = true;
        $bar->config->columns_to_display = array('nb_conversions');

        $bar->beforeRender();

        $this->assertArrayNotHasKey('showColumns', $bar->config->export_parameters_to_modify);
    }

    public function testBeforeLoadDataTableKeepsAggregatedGoalColumnsWhenNoSpecificGoal()
    {
        $bar = $this->getMockGraph(array('label' => 'abc', 'nb_visits' => 5));
        $bar->config->show_goals = true;
        $bar->config->columns_to_display = array('label', 'nb_conversions', 'revenue');

        $bar->beforeLoadDataTable();

        $this->assertSame(
            array('label', 'nb_conversions', 'revenue'),
            array_values($bar->config->columns_to_display)
        );
        $this->assertArrayNotHasKey('filter_update_columns_when_show_all_goals', $bar->requestConfig->request_parameters_to_modify);
    }

    /**
     * @return \Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Bar
     */
    private function getMockGraph(array $firstDataRow)
    {
        $row = new DataTable\Row(array(
            DataTable\Row::COLUMNS => $firstDataRow,
        ));
        $dataTable = new DataTable();
        $dataTable->setRows(array($row));

        $bar = $this->getMockBuilder('Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Bar')
            ->setMethods(['getDataTable'])
            ->setConstructorArgs(['', ''])
            ->getMock();
        $bar->expects($this->any())
            ->method('getDataTable')
            ->will($this->returnValue($dataTable));

        return $bar;
    }
}
