<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreVisualizations
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\Period\Range;
use Piwik\Plugin\Controller;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;
use Piwik\Site;
use Piwik\Visualization\Config;
use Piwik\Visualization\Request;

/**
 * Visualization that renders HTML for a line graph using jqPlot.
 */
class Evolution extends JqplotGraph
{
    const ID = 'graphEvolution';
    const SERIES_COLOR_COUNT = 8;

    /**
     * Whether to show a line graph or a bar graph.
     *
     * Default value: true
     */
    const SHOW_LINE_GRAPH = 'show_line_graph';

    public static $clientSideConfigProperties = array('show_line_graph');

    public static $overridableProperties = array('show_line_graph');

    public function configureVisualization(Config $properties)
    {
        $this->calculateEvolutionDateRange($properties);

        parent::configureVisualization($properties);

        $properties->datatable_js_type = 'JqplotEvolutionGraphDataTable';
    }

    public function beforeLoadDataTable(Request $request, Config $properties)
    {
        parent::beforeLoadDataTable($request, $properties);

        // period will be overridden when 'range' is requested in the UI
        // but the graph will display for each day of the range.
        // Default 'range' behavior is to return the 'sum' for the range
        if (Common::getRequestVar('period', false) == 'range') {
            $request->request_parameters_to_modify['period'] = 'day';
        }
    }

    public function afterAllFilteresAreApplied(DataTableInterface $dataTable, Config $properties, Request $request)
    {
        parent::afterAllFilteresAreApplied($dataTable, $properties, $request);

        if ($properties->visualization_properties->x_axis_step_size === false) {

            $size = $this->getDefaultXAxisStepSize($dataTable->getRowsCount());
            $properties->visualization_properties->x_axis_step_size = $size;
        }
    }

    public static function getDefaultPropertyValues()
    {
        $result = parent::getDefaultPropertyValues();
        $result['show_all_views_icons'] = false;
        $result['show_table'] = false;
        $result['show_table_all_columns'] = false;
        $result['hide_annotations_view'] = false;
        $result['visualization_properties']['jqplot_graph']['x_axis_step_size'] = false;
        $result['visualization_properties']['graphEvolution']['show_line_graph'] = true;
        return $result;
    }

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory('evolution', $properties);
    }

    /**
     * Based on the period, date and evolution_{$period}_last_n query parameters,
     * calculates the date range this evolution chart will display data for.
     */
    private function calculateEvolutionDateRange(Config $properties)
    {
        $view = $this->viewDataTable;
        $period = Common::getRequestVar('period');

        $defaultLastN = self::getDefaultLastN($period);
        $originalDate = Common::getRequestVar('date', 'last' . $defaultLastN, 'string');

        if ($period != 'range') { // show evolution limit if the period is not a range
            $properties->show_limit_control = true;

            // set the evolution_{$period}_last_n query param
            if (Range::parseDateRange($originalDate)) { // if a multiple period
                // overwrite last_n param using the date range
                $oPeriod = new Range($period, $originalDate);
                $lastN = count($oPeriod->getSubperiods());
            } else { // if not a multiple period
                list($newDate, $lastN) = self::getDateRangeAndLastN($period, $originalDate, $defaultLastN);
                $view->request_parameters_to_modify['date'] = $newDate;
                $properties->custom_parameters['dateUsedInGraph'] = $newDate;
            }
            $lastNParamName = self::getLastNParamName($period);
            $properties->custom_parameters[$lastNParamName] = $lastN;
        }
    }

    /**
     * Returns the entire date range and lastN value for the current request, based on
     * a period type and end date.
     *
     * @param string $period The period type, 'day', 'week', 'month' or 'year'
     * @param string $endDate The end date.
     * @param int|null $defaultLastN The default lastN to use. If null, the result of
     *                               getDefaultLastN is used.
     * @return array An array w/ two elements. The first is a whole date range and the second
     *               is the lastN number used, ie, array('2010-01-01,2012-01-02', 2).
     */
    public static function getDateRangeAndLastN($period, $endDate, $defaultLastN = null)
    {
        if ($defaultLastN === null) {
            $defaultLastN = self::getDefaultLastN($period);
        }

        $lastNParamName = self::getLastNParamName($period);
        $lastN = Common::getRequestVar($lastNParamName, $defaultLastN, 'int');

        $site = new Site(Common::getRequestVar('idSite'));

        $dateRange = Controller::getDateRangeRelativeToEndDate($period, 'last' . $lastN, $endDate, $site);

        return array($dateRange, $lastN);
    }

    /**
     * Returns the default last N number of dates to display for a given period.
     *
     * @param string $period 'day', 'week', 'month' or 'year'
     * @return int
     */
    public static function getDefaultLastN($period)
    {
        switch ($period) {
            case 'week':
                return 26;
            case 'month':
                return 24;
            case 'year':
                return 5;
            case 'day':
            default:
                return 30;
        }
    }

    /**
     * Returns the query parameter that stores the lastN number of periods to get for
     * the evolution graph.
     *
     * @param string $period The period type, 'day', 'week', 'month' or 'year'.
     * @return string
     */
    public static function getLastNParamName($period)
    {
        return "evolution_{$period}_last_n";
    }

    public function getDefaultXAxisStepSize($countGraphElements)
    {
        // when the number of elements plotted can be small, make sure the X legend is useful
        if ($countGraphElements <= 7) {
            return 1;
        }

        $periodLabel = Common::getRequestVar('period');
        switch ($periodLabel) {
            case 'day':
            case 'range':
                $steps = 5;
                break;
            case 'week':
                $steps = 4;
                break;
            case 'month':
                $steps = 5;
                break;
            case 'year':
                $steps = 5;
                break;
            default:
                $steps = 5;
                break;
        }

        $paddedCount = $countGraphElements + 2; // pad count so last label won't be cut off
        return ceil($paddedCount / $steps);
    }
}