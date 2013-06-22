<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CoreHome
 */

/**
 * ROW EVOLUTION
 * The class handles the popover that shows the evolution of a singe row in a data table
 * @package Piwik_CoreHome
 */
class Piwik_CoreHome_DataTableRowAction_RowEvolution
{

    /** The current site id */
    protected $idSite;

    /** The api method to get the data. Format: Plugin.apiAction */
    protected $apiMethod;

    /** The label of the requested row */
    protected $label;

    /** The requested period */
    protected $period;

    /** The requested date */
    protected $date;

    /** The request segment */
    protected $segment;

    /** The metrics that are available for the requested report and period */
    protected $availableMetrics;

    /** The name of the dimension of the current report */
    protected $dimension;

    /**
     * The data
     * @var Piwik_DataTable_Array
     */
    protected $dataTable;

    /** The label of the current record */
    protected $rowLabel;

    /** The icon of the current record */
    protected $rowIcon;

    /** The type of graph that has been requested last */
    protected $graphType;

    /** The metrics for the graph that has been requested last */
    protected $graphMetrics;

    /** Whether or not to show all metrics in the evolution graph when to popover opens */
    protected $initiallyShowAllMetrics = false;

    /**
     * The constructor
     * Initialize some local variables from the request
     * @param int $idSite
     * @param Piwik_Date $date ($this->date from controller)
     * @throws Exception
     */
    public function __construct($idSite, $date, $graphType = null)
    {
        $this->apiMethod = Piwik_Common::getRequestVar('apiMethod', '', 'string');
        if (empty($this->apiMethod)) throw new Exception("Parameter apiMethod not set.");

        $this->label = Piwik_API_ResponseBuilder::getLabelFromRequest($_GET);
        $this->label = $this->label[0];

        if ($this->label === '') throw new Exception("Parameter label not set.");

        $this->period = Piwik_Common::getRequestVar('period', '', 'string');
        if (empty($this->period)) throw new Exception("Parameter period not set.");

        $this->idSite = $idSite;
        $this->graphType = $graphType;

        if ($this->period != 'range') {
            // handle day, week, month and year: display last X periods
            $end = $date->toString();
            list($this->date, $lastN) =
                Piwik_ViewDataTable_GenerateGraphHTML_ChartEvolution::getDateRangeAndLastN($this->period, $end);
        }
        $this->segment = Piwik_ViewDataTable::getRawSegmentFromRequest();

        $this->loadEvolutionReport();
    }

    /**
     * Render the popover
     * @param Piwik_CoreHome_Controller
     * @param Piwik_View (the popover_rowevolution template)
     */
    public function renderPopover($controller, $view)
    {
        // render main evolution graph
        $this->graphType = 'graphEvolution';
        $this->graphMetrics = $this->availableMetrics;
        $view->graph = $controller->getRowEvolutionGraph(true);

        // render metrics overview
        $view->metrics = $this->getMetricsToggles($controller);

        // available metrics text
        $metricsText = Piwik_Translate('RowEvolution_AvailableMetrics');
        $popoverTitle = '';
        if ($this->rowLabel) {
            $icon = $this->rowIcon ? '<img src="' . $this->rowIcon . '" alt="">' : '';
            $rowLabel = str_replace('/', '<wbr>/', str_replace('&', '<wbr>&', $this->rowLabel));
            $metricsText = sprintf(Piwik_Translate('RowEvolution_MetricsFor'), $this->dimension . ': ' . $icon . ' ' . $rowLabel);
            $popoverTitle = $icon . ' ' . $rowLabel;
        }

        $view->availableMetricsText = $metricsText;
        $view->popoverTitle = $popoverTitle;

        return $view->render();
    }

    protected function loadEvolutionReport($column = false)
    {
        list($apiModule, $apiAction) = explode('.', $this->apiMethod);

        $parameters = array(
            'method'    => 'API.getRowEvolution',
            'label'     => $this->label,
            'apiModule' => $apiModule,
            'apiAction' => $apiAction,
            'idSite'    => $this->idSite,
            'period'    => $this->period,
            'date'      => $this->date,
            'format'    => 'original',
            'serialize' => '0'
        );
        if (!empty($this->segment)) {
            $parameters['segment'] = $this->segment;
        }

        if ($column !== false) {
            $parameters['column'] = $column;
        }

        $url = Piwik_Url::getQueryStringFromParameters($parameters);

        $request = new Piwik_API_Request($url);
        $report = $request->process();

        $this->extractEvolutionReport($report);
    }

    protected function extractEvolutionReport($report)
    {
        $this->dataTable = $report['reportData'];
        $this->rowLabel = Piwik_Common::sanitizeInputValue($report['label']);
        $this->rowIcon = !empty($report['logo']) ? $report['logo'] : false;
        $this->availableMetrics = $report['metadata']['metrics'];
        $this->dimension = $report['metadata']['dimension'];
    }

    /**
     * Generic method to get an evolution graph or a sparkline for the row evolution popover.
     * Do as much as possible from outside the controller.
     * @return Piwik_ViewDataTable
     */
    public function getRowEvolutionGraph()
    {
        // set up the view data table
        $view = Piwik_ViewDataTable::factory($this->graphType);
        $view->setDataTable($this->dataTable);
        $view->init('CoreHome', 'getRowEvolutionGraph', $this->apiMethod);

        if (!empty($this->graphMetrics)) // In row Evolution popover, this is empty
        {
            $view->setColumnsToDisplay(array_keys($this->graphMetrics));
        }
        $view->hideAllViewsIcons();

        foreach ($this->availableMetrics as $metric => $metadata) {
            $view->setColumnTranslation($metric, $metadata['name']);
        }

        if (method_exists($view, 'addRowEvolutionSeriesToggle')) {
            $view->addRowEvolutionSeriesToggle($this->initiallyShowAllMetrics);
        }

        return $view;
    }

    /**
     * Prepare metrics toggles with spark lines
     * @param $controller
     * @return array
     */
    protected function getMetricsToggles($controller)
    {
        $chart = new Piwik_Visualization_Chart_Evolution;
        $colors = $chart->getSeriesColors();

        $i = 0;
        $metrics = array();
        foreach ($this->availableMetrics as $metric => $metricData) {
            $max = isset($metricData['max']) ? $metricData['max'] : 0;
            $min = isset($metricData['min']) ? $metricData['min'] : 0;
            $change = isset($metricData['change']) ? $metricData['change'] : false;

            $unit = Piwik_API_API::getUnit($metric, $this->idSite);
            $min .= $unit;
            $max .= $unit;

            $details = Piwik_Translate('RowEvolution_MetricBetweenText', array($min, $max));

            if ($change !== false) {
                $lowerIsBetter = Piwik_API_API::isLowerValueBetter($metric);
                if (substr($change, 0, 1) == '+') {
                    $changeClass = $lowerIsBetter ? 'bad' : 'good';
                    $changeImage = $lowerIsBetter ? 'arrow_up_red' : 'arrow_up';
                } else if (substr($change, 0, 1) == '-') {
                    $changeClass = $lowerIsBetter ? 'good' : 'bad';
                    $changeImage = $lowerIsBetter ? 'arrow_down_green' : 'arrow_down';
                } else {
                    $changeClass = 'neutral';
                    $changeImage = false;
                }

                $change = '<span class="' . $changeClass . '">'
                    . ($changeImage ? '<img src="plugins/MultiSites/images/' . $changeImage . '.png" /> ' : '')
                    . $change . '</span>';

                $details .= ', ' . Piwik_Translate('RowEvolution_MetricChangeText', $change);
            }

            $color = $colors[$i % count($colors)];
            $newMetric = array(
                'label'     => $metricData['name'],
                'color'     => $color,
                'details'   => $details,
                'sparkline' => $this->getSparkline($metric, $controller),
            );
            // Multi Rows, each metric can be for a particular row and display an icon
            if (!empty($metricData['logo'])) {
                $newMetric['logo'] = $metricData['logo'];
            }
            $metrics[] = $newMetric;
            $i++;
        }

        return $metrics;
    }

    /** Get the img tag for a sparkline showing a single metric */
    protected function getSparkline($metric, $controller)
    {
        $this->graphType = 'sparkline';
        $this->graphMetrics = array($metric => $metric);

        // sparkline is always echoed, so we need to buffer the output
        ob_start();
        $controller->getRowEvolutionGraph();
        $spark = ob_get_contents();
        ob_end_clean();

        // undo header change by sparkline renderer
        header('Content-type: text/html');

        // base64 encode the image and put it in an img tag
        $spark = base64_encode($spark);
        return '<img src="data:image/png;base64,' . $spark . '" />';
    }

    /** Use the available metrics for the metrics of the last requested graph. */
    public function useAvailableMetrics()
    {
        $this->graphMetrics = $this->availableMetrics;
    }
}
