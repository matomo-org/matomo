<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

use Piwik\Common;
use Piwik\Period\Range;
use Piwik\Controller;
use Piwik\Site;

/**
 * Generates HTML embed for the Evolution graph
 *
 * @package Piwik
 * @subpackage ViewDataTable
 */
class Piwik_ViewDataTable_GenerateGraphHTML_ChartEvolution extends Piwik_ViewDataTable_GenerateGraphHTML
{
    const GRAPH_HEIGHT = 170;

    /**
     * The value of the date query parameter (or a default value) before it is turned
     * into a date range. Set in 'calculateEvolutionDateRange' and used by
     * 'getJavascriptVariablesToSet'.
     *
     * @var string
     */
    private $originalDate;
    
    public function __construct()
    {
        parent::__construct();
        $this->viewProperties['graph_type'] = 'evolution';
        $this->viewProperties['graph_height'] = self::GRAPH_HEIGHT.'px';
    }

    protected function getViewDataTableId()
    {
        return 'graphEvolution';
    }

    protected function getViewDataTableIdToLoad()
    {
        return 'generateDataChartEvolution';
    }

    public function init($currentControllerName,
                  $currentControllerAction,
                  $apiMethodToRequestDataTable,
                  $controllerActionCalledWhenRequestSubTable = null,
                  $defaultProperties = array())
    {
        parent::init($currentControllerName,
            $currentControllerAction,
            $apiMethodToRequestDataTable,
            $controllerActionCalledWhenRequestSubTable,
            $defaultProperties);

        $this->calculateEvolutionDateRange();
        $this->disableShowAllViewsIcons();
        $this->disableShowTable();
        $this->disableShowAllColumns();
        $this->showAnnotationsView();
    }

    /**
     * Makes sure 'date' parameter is not overridden.
     */
    protected function getJavascriptVariablesToSet()
    {
        $result = parent::getJavascriptVariablesToSet();

        // Graphs use a Range instead of the input date - we will use this same range for "Export" icons
        $result['dateUsedInGraph'] = $result['date'];

        // Other datatable features may require the original input date (eg. the limit dropdown below evolution graph)
        $result['date'] = $this->originalDate;
        return $result;
    }

    /**
     * Based on the period, date and evolution_{$period}_last_n query parameters,
     * calculates the date range this evolution chart will display data for.
     */
    private function calculateEvolutionDateRange()
    {
        $period = Common::getRequestVar('period');

        $defaultLastN = self::getDefaultLastN($period);
        $this->originalDate = Common::getRequestVar('date', 'last' . $defaultLastN, 'string');

        if ($period != 'range') // show evolution limit if the period is not a range
        {
            $this->alwaysShowLimitDropdown();

            // set the evolution_{$period}_last_n query param
            if (Range::parseDateRange($this->originalDate)) // if a multiple period
            {
                // overwrite last_n param using the date range
                $oPeriod = new Range($period, $this->originalDate);
                $lastN = count($oPeriod->getSubperiods());
            } else // if not a multiple period
            {
                list($newDate, $lastN) = self::getDateRangeAndLastN($period, $this->originalDate, $defaultLastN);
                $this->viewProperties['request_parameters_to_modify']['date'] = $newDate;
            }
            $lastNParamName = self::getLastNParamName($period);
            $this->viewProperties['request_parameters_to_modify'][$lastNParamName] = $lastN;
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

    protected function getRequestArray()
    {
        // period will be overridden when 'range' is requested in the UI // TODO: this code probably shouldn't be here...
        // but the graph will display for each day of the range.
        // Default 'range' behavior is to return the 'sum' for the range
        if (Common::getRequestVar('period', false) == 'range') {
            $this->viewProperties['request_parameters_to_modify']['period'] = 'day';
        }

        // FIXME: This appears to be a hack used to ensure a graph is plotted even if there is no data. there's probably
        //        a less complicated way of doing it... (this is complicated because it modifies the request used to get 
        //        data so a loop is entered in JqplotDataGenerator_Evolution::initChartObjectData)
        if (!empty($this->viewProperties['columns_to_display'])) {
            $columns = implode(',', $this->viewProperties['columns_to_display']);
            $this->viewProperties['request_parameters_to_modify']['columns'] = $columns;
        }

        return parent::getRequestArray();
    }
    
    public function getDefaultDataTableCssClass()
    {
        return 'dataTableEvolutionGraph';
    }
}
