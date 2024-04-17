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
            'nb_something_else' => 1
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
            'bar' => 2
        ));

        $bar->afterAllFiltersAreApplied();

        $columns = array_column($bar->config->selectable_columns, 'column');
        $this->assertEquals(array('foo', 'bar'), $columns);
    }

    public function testSelectableColumnsNoDefaultColumnsPresentLabelNotPresent()
    {
        $bar = $this->getMockGraph(array(
            'foo' => 5,
            'bar' => 2
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
            'bar' => 2
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
            'nb_actions' => 80
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
            'nb_actions' => 80
        ));
        // columns_to_display is not present in selectable_columbs, but as long as it's in the data set that's OK
        $bar->config->selectable_columns = array('nb_visits');
        $bar->config->columns_to_display = array('nb_actions');

        $bar->afterAllFiltersAreApplied();

        $this->assertEquals(array('nb_actions'), $bar->config->columns_to_display);
    }

    /**
     * @return \Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Bar
     */
    private function getMockGraph(array $firstDataRow)
    {
        $row = new DataTable\Row(array(
            DataTable\Row::COLUMNS => $firstDataRow
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
