<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Period\Range;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;
use Piwik\Site;

/**
 * Visualization that renders HTML for a line graph using jqPlot.
 *
 * @property Evolution\Config $config
 */
class Evolution extends JqplotGraph
{
    const ID = 'graphEvolution';
    const SERIES_COLOR_COUNT = 8;

    public static function getDefaultConfig()
    {
        return new Evolution\Config();
    }

    public function beforeRender()
    {
        parent::beforeRender();

        $this->config->datatable_js_type = 'JqplotEvolutionGraphDataTable';
    }

    public function beforeLoadDataTable()
    {
        $this->calculateEvolutionDateRange();

        parent::beforeLoadDataTable();

        // period will be overridden when 'range' is requested in the UI
        // but the graph will display for each day of the range.
        // Default 'range' behavior is to return the 'sum' for the range
        if (Common::getRequestVar('period', false) == 'range') {
            $this->requestConfig->request_parameters_to_modify['period'] = 'day';
        }

        $this->config->custom_parameters['columns'] = $this->config->columns_to_display;
    }

    public function afterAllFiltersAreApplied()
    {
        parent::afterAllFiltersAreApplied();

        if (false === $this->config->x_axis_step_size) {
            $rowCount = $this->dataTable->getRowsCount();

            $this->config->x_axis_step_size = $this->getDefaultXAxisStepSize($rowCount);
        }
    }

    protected function makeDataGenerator($properties)
    {
        return JqplotDataGenerator::factory('evolution', $properties);
    }

    /**
     * Based on the period, date and evolution_{$period}_last_n query parameters,
     * calculates the date range this evolution chart will display data for.
     */
    private function calculateEvolutionDateRange()
    {
        $period = Common::getRequestVar('period');

        $defaultLastN = self::getDefaultLastN($period);
        $originalDate = Common::getRequestVar('date', 'last' . $defaultLastN, 'string');

        if ('range' != $period) { // show evolution limit if the period is not a range
            $this->config->show_limit_control = true;

            // set the evolution_{$period}_last_n query param
            if (Range::parseDateRange($originalDate)) {
                // if a multiple period

                // overwrite last_n param using the date range
                $oPeriod = new Range($period, $originalDate);
                $lastN   = count($oPeriod->getSubperiods());

            } else {

                // if not a multiple period
                list($newDate, $lastN) = self::getDateRangeAndLastN($period, $originalDate, $defaultLastN);
                $this->requestConfig->request_parameters_to_modify['date'] = $newDate;
                $this->config->custom_parameters['dateUsedInGraph'] = $newDate;
            }

            $lastNParamName = self::getLastNParamName($period);
            $this->config->custom_parameters[$lastNParamName] = $lastN;
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

        $dateRange = Range::getRelativeToEndDate($period, 'last' . $lastN, $endDate, $site);

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
