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

/**
 * Generates HTML embed for the Evolution graph
 *
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */

class Piwik_ViewDataTable_GenerateGraphHTML_ChartEvolution extends Piwik_ViewDataTable_GenerateGraphHTML
{
    protected $height = 170;
    protected $graphType = 'evolution';

    /**
     * The value of the date query parameter (or a default value) before it is turned
     * into a date range. Set in 'calculateEvolutionDateRange' and used by
     * 'getJavascriptVariablesToSet'.
     *
     * @var string
     */
    private $originalDate;

    protected function getViewDataTableId()
    {
        return 'graphEvolution';
    }

    protected function getViewDataTableIdToLoad()
    {
        return 'generateDataChartEvolution';
    }

    function init($currentControllerName,
                  $currentControllerAction,
                  $apiMethodToRequestDataTable,
                  $controllerActionCalledWhenRequestSubTable = null)
    {
        parent::init($currentControllerName,
            $currentControllerAction,
            $apiMethodToRequestDataTable,
            $controllerActionCalledWhenRequestSubTable);

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
     * We ensure that the graph for a given Goal has a different ID than the 'Goals Overview' graph
     * so that both can display on the dashboard at the same time
     * @return null|string
     */
    public function getUniqueIdViewDataTable()
    {
        $id = parent::getUniqueIdViewDataTable();
        if (!empty($this->parametersToModify['idGoal'])) {
            $id .= $this->parametersToModify['idGoal'];
        }
        return $id;
    }

    /**
     * Sets the columns that will be displayed on output evolution chart
     * By default all columns are displayed ($columnsNames = array() will display all columns)
     *
     * @param array $columnsNames Array of column names eg. array('nb_visits','nb_hits')
     */
    public function setColumnsToDisplay($columnsNames)
    {
        if (!is_array($columnsNames)) {
            if (strpos($columnsNames, ',') !== false) {
                // array values are comma separated
                $columnsNames = explode(',', $columnsNames);
            } else {
                $columnsNames = array($columnsNames);
            }
        }
        $this->setParametersToModify(array('columns' => $columnsNames));
    }

    /**
     * Based on the period, date and evolution_{$period}_last_n query parameters,
     * calculates the date range this evolution chart will display data for.
     */
    private function calculateEvolutionDateRange()
    {
        $period = Piwik_Common::getRequestVar('period');

        $defaultLastN = self::getDefaultLastN($period);
        $this->originalDate = Piwik_Common::getRequestVar('date', 'last' . $defaultLastN, 'string');

        if ($period != 'range') // show evolution limit if the period is not a range
        {
            $this->alwaysShowLimitDropdown();

            // set the evolution_{$period}_last_n query param
            if (Piwik_Period_Range::parseDateRange($this->originalDate)) // if a multiple period
            {
                // overwrite last_n param using the date range
                $oPeriod = new Piwik_Period_Range($period, $this->originalDate);
                $lastN = count($oPeriod->getSubperiods());
            } else // if not a multiple period
            {
                list($newDate, $lastN) = self::getDateRangeAndLastN($period, $this->originalDate, $defaultLastN);
                $this->setParametersToModify(array('date' => $newDate));
            }
            $lastNParamName = self::getLastNParamName($period);
            $this->setParametersToModify(array($lastNParamName => $lastN));
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
        $lastN = Piwik_Common::getRequestVar($lastNParamName, $defaultLastN, 'int');

        $site = new Piwik_Site(Piwik_Common::getRequestVar('idSite'));

        $dateRange = Piwik_Controller::getDateRangeRelativeToEndDate($period, 'last' . $lastN, $endDate, $site);

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
}
