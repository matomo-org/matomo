<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CoreHome
 */

/**
 * ROW EVOLUTION
 * The class handles the popup that shows the evolution of a singe row in a data table
 * @package Piwik_CoreHome
 */
class Piwik_CoreHome_DataTableAction_RowEvolution
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
	
	/** The meta data for the requested report */
	protected $metaData;
	
	/** The metrics that are available for the requested report and period */
	protected $availableMetrics;
	
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
	
	/** Whether or not to show all metrics in the evolution graph when to popup opens */
	protected $initiallyShowAllMetrics = false;
	
	/**
	 * The constructor
	 * Initialize some local variables from the request
	 * @param int
	 * @param Piwik_Date ($this->date from controller)
	 */
	public function __construct($idSite, $date)
	{
		$this->apiMethod = Piwik_Common::getRequestVar('apiMethod', '', 'string');
		if (empty($this->apiMethod)) throw new Exception("Parameter apiMethod not set.");
		
		$this->label = Piwik_Common::getRequestVar('label', '', 'string');
		if ($this->label == '') throw new Exception("Parameter label not set.");
		
		$this->period = Piwik_Common::getRequestVar('period', '', 'string');
		if (empty($this->period)) throw new Exception("Parameter period not set.");
		
		$this->idSite = $idSite;
		$this->availableMetrics = $this->getAvailableMetrics();
		
		if ($this->period == 'range')
		{
			// handle range: display all days in range
			$this->period = 'day';
			$this->date = Piwik_Common::getRequestVar('date', '');
			if (empty($this->date)) throw new Exception("Parameter date not set.");
		}
		else
		{
			// handle day, week, month and year: display last 30 periods
			$end = $date->toString();
			if ($this->period == 'year') $start = $date->subYear(30)->toString();
			else if ($this->period == 'month') $start = $date->subMonth(30)->toString();
			else if ($this->period == 'week') $start = $date->subWeek(30)->toString();
			else $start = $date->subDay(30)->toString();
			$this->date = $start.','.$end;
		}
	}
	
	/** Get available metrics from metadata API */
	protected function getAvailableMetrics()
	{
		list($apiModule, $apiAction) = explode('.', $this->apiMethod);
		
		$this->metaData = Piwik_API_API::getInstance()->getMetadata(
					$this->idSite, $apiModule, $apiAction, array(), false, $this->period);
		
		$this->metaData = $this->metaData[0];
		
		$availableMetrics = $this->metaData['metrics'];
		if (isset($this->metaData['processedMetrics'])) {
			$availableMetrics += $this->metaData['processedMetrics'];
		}
		
		return $availableMetrics;
	}
	
	/**
	 * Load the data table from the API
	 * This is only done once, the views (evolution graph and spark lines) all use it
	 */
	protected function loadDataTable()
	{
		$dataTable = $this->doLoadDataTable();
		
		// remove the label from the data table rows
		// otherwise the evolution graph legend would show both the label and the metric name
		// this would be too long if multiple metrics are selected
		foreach ($dataTable->getArray() as $dayTable)
		{
			$dataTable->applyQueuedFilters();
			if ($dayTable->getRowsCount() > 0)
			{
				if (!$this->rowLabel)
				{
					$this->rowLabel = $dayTable->getFirstRow()->getColumn('label');
					$this->rowIcon = $dayTable->getFirstRow()->getMetadata('logo');
				}
				
				$dayTable->getFirstRow()->setColumn('label', false);
			}
		}
		
		return $dataTable;
	}
	
	/**
	 * Helper method for only the API call
	 * Used in MultiRowEvolution as well
	 * @return Piwik_DataTable_Array
	 */
	protected function doLoadDataTable()
	{
		$requestString = 'method='.$this->apiMethod.'&format=original'
				. '&date='.$this->date.'&period='.$this->period
				. '&label='.urlencode($this->label);
		
		// add "processed metrics" like actions per visit or bounce rate
		if (substr($this->apiMethod, 0, 8) != 'Actions.') {
			$requestString .= '&filter_add_columns_when_show_all_columns=1';
		}
		
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}
	
	/**
	 * Render the popup
	 * @param Piwik_CoreHome_Controller
	 * @param Piwik_View (the popup_rowevolution template)
	 */
	public function renderPopup($controller, $view)
	{
		$this->dataTable = $this->loadDataTable();
		
		// render main evolution graph
		$this->graphType = 'graphEvolution';
		$this->graphMetrics = $this->availableMetrics;
		$view->graph = $controller->getRowEvolutionGraph(true);
		
		// render metrics overview
		$view->metrics = $this->getMetricsToggles($controller);
		
		// available metrics text
		$metricsText = Piwik_Translate('RowEvolution_AvailableMetrics');
		if ($this->rowLabel)
		{
			$icon = $this->rowIcon ? '<img src="'.$this->rowIcon.'" alt="">' : '';
			$metricsText .= ' '.$this->metaData['dimension'].': '.$icon.' '.$this->rowLabel;
		}
		
		$view->availableMetricsText = $metricsText; 
		
		return $view->render();
	}
	
	/**
	 * Generic method to get an evolution graph or a sparkline for the row evolution popup.
	 * Do as much as possible from outside the controller.
	 * @return Piwik_ViewDataTable
	 */
	public function getRowEvolutionGraph()
	{	
		// update period and date in $_GET because this is what is passed to the export icons
		// under the evolution graph
		$_GET['period'] = $this->period;
		$_GET['date'] = $this->date;
		
		// set up the view data table
		$view = Piwik_ViewDataTable::factory($this->graphType);
		$view->setDataTable($this->dataTable);
		$view->init('CoreHome', 'getRowEvolutionGraph', $this->apiMethod);
		$view->setColumnsToDisplay(array_keys($this->graphMetrics));
		$view->setColumnsTranslations($this->graphMetrics);
		$view->hideAllViewsIcons();
		
		if (method_exists($view, 'addRowEvolutionSeriesToggle')) {
			$view->addRowEvolutionSeriesToggle($this->initiallyShowAllMetrics);
		}
		
		return $view;
	}
	
	/** Prepare metrics toggles with spark lines */
	protected function getMetricsToggles($controller)
	{
		// calculate meta-metrics
		$subDataTables = $this->dataTable->getArray();
		$firstDataTable = current($subDataTables);
		$firstDataTableRow = $firstDataTable->getFirstRow();
		$lastDataTable = end($subDataTables);
		$lastDataTableRow = $lastDataTable->getFirstRow();
		$maxValues = array();
		$minValues = array();
		foreach ($subDataTables as $subDataTable)
		{
			// $subDataTable is the report for one period, it has only one row
			$firstRow = $subDataTable->getFirstRow();
			foreach ($this->availableMetrics as $metric => $label)
			{
				$value = $firstRow ? floatval($firstRow->getColumn($metric)) : 0;
				if (!isset($minValues[$metric]) || $minValues[$metric] > $value)
				{
					$minValues[$metric] = $value;
				}
				if (!isset($maxValues[$metric]) || $maxValues[$metric] < $value)
				{
					$maxValues[$metric] = $value;
				}
			}
		}
		
		$chart = new Piwik_Visualization_Chart_Evolution;
		$colors = $chart->getSeriesColors();
		
		// put together metric info
		$i = 0;
		$metrics = array();
		foreach ($this->availableMetrics as $metric => $label)
		{
			if ($maxValues[$metric] == 0 && !($this instanceof Piwik_CoreHome_DataTableAction_MultiRowEvolution))
			{
				// series with only 0 cause trouble in js
				continue;
			}
			
			$first = $firstDataTableRow ? floatval($firstDataTableRow->getColumn($metric)) : 0;
			$last = $lastDataTableRow ? floatval($lastDataTableRow->getColumn($metric)) : 0;
			$changePercent = $first > 0 ? round((($last / $first) * 100) - 100) : 100;
			$changePercentHtml = $changePercent.'%';
			if ($changePercent > 0)
			{
				$changePercentHtml = '+'.$changePercentHtml;
				$changeClass = 'up';
				$changeImage = 'arrow_up';
			}
			else
			{
				$changeClass = $changePercent < 0 ? 'down' : 'nochange';
				$changeImage = $changePercent < 0 ? 'arrow_down' : false;
			}
			
			$changePercentHtml = '<span class="'.$changeClass.'">'
					.($changeImage ? '<img src="plugins/MultiSites/images/'.$changeImage.'.png" /> ' : '')
					.$changePercentHtml.'</span>';
			
			$details = Piwik_Translate('RowEvolution_MetricDetailsText', array(
					$minValues[$metric], $maxValues[$metric], $changePercentHtml));
			
			$color = $colors[ $i % count($colors) ];
			
			$metrics[] = array(
				'label' => $label,
				'color' => $color,
				'details' => $details,
				'sparkline' => $this->getSparkline($metric, $controller) 
			);
			
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
		return '<img src="data:image/png;base64,'.$spark.'" />';
	}
	
}