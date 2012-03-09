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
		if ($this->label === '') throw new Exception("Parameter label not set.");
		
		$this->period = Piwik_Common::getRequestVar('period', '', 'string');
		if (empty($this->period)) throw new Exception("Parameter period not set.");
		
		$this->idSite = $idSite;
		
		if ($this->period != 'range')
		{
			// handle day, week, month and year: display last X periods
			$end = $date->toString();
			if ($this->period == 'year') $start = $date->subYear(10)->toString();
			else if ($this->period == 'month') $start = $date->subMonth(30)->toString();
			else if ($this->period == 'week') $start = $date->subWeek(30)->toString();
			else $start = $date->subDay(30)->toString();
			$this->date = $start.','.$end;
		}
		$this->segment = Piwik_Common::getRequestVar('segment', '', 'string');
		
		$this->loadEvolutionReport();
	}
	
	/**
	 * Render the popup
	 * @param Piwik_CoreHome_Controller
	 * @param Piwik_View (the popup_rowevolution template)
	 */
	public function renderPopup($controller, $view)
	{
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
			$metricsText .= ' '.$this->dimension.': '.$icon.' '.$this->rowLabel;
		}
		
		$view->availableMetricsText = $metricsText; 
		
		return $view->render();
	}
	
	protected function loadEvolutionReport($column = false)
	{
		list($apiModule, $apiAction) = explode('.', $this->apiMethod);
		
		$parameters = array(
			'method' => 'API.getRowEvolution',
			'label' => $this->label,
			'apiModule' => $apiModule,
			'apiAction' => $apiAction,
			'idSite' => $this->idSite,
			'period' => $this->period,
			'date' => $this->date,
			'format' => 'original',
			'serialize' => '0'
		);
		if(!empty($this->segment))
		{
			$parameters['segment'] = $this->segment;
		}
		
		if ($column !== false)
		{
			$parameters['column'] = $column;
		}
		
		$url = Piwik_Url::getQueryStringFromParameters($parameters);
		
		$request = new Piwik_API_Request($url);
		$report = $request->process();
		
		$this->extractEvolutionReport($report);
	}
	
	protected function extractEvolutionReport($report)
	{
		$this->dataTable = $report['data'];
		$this->rowLabel = $report['label'];
		$this->rowIcon = $report['logo'];
		$this->availableMetrics = $report['metadata']['metrics'];
		$this->dimension = $report['metadata']['dimension'];
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
		// TODO: can we find a way around this?
		$_GET['period'] = $this->period;
		$_GET['date'] = $this->date;
		
		// set up the view data table
		$view = Piwik_ViewDataTable::factory($this->graphType);
		$view->setDataTable($this->dataTable);
		$view->init('CoreHome', 'getRowEvolutionGraph', $this->apiMethod);
		$view->setColumnsToDisplay(array_keys($this->graphMetrics));
		$view->hideAllViewsIcons();
		
		foreach ($this->availableMetrics as $metric => $metadata)
		{
			$view->setColumnTranslation($metric, $metadata['name']);
		}
		
		if (method_exists($view, 'addRowEvolutionSeriesToggle'))
		{
			$view->addRowEvolutionSeriesToggle($this->initiallyShowAllMetrics);
		}
		
		return $view;
	}
	
	/** Prepare metrics toggles with spark lines */
	protected function getMetricsToggles($controller)
	{
		$chart = new Piwik_Visualization_Chart_Evolution;
		$colors = $chart->getSeriesColors();
		
		$i = 0;
		$metrics = array();
		foreach ($this->availableMetrics as $metric => $metricData)
		{
			$max = $metricData['max'];
			$min = $metricData['min'];
			$change = $metricData['change'];
			
			if ($max == 0 && !($this instanceof Piwik_CoreHome_DataTableAction_MultiRowEvolution))
			{
				// series with only 0 cause trouble in js
				continue;
			}
			
			if (substr($change, 0, 1) == '+')
			{
				$changeClass = 'up';
				$changeImage = 'arrow_up';
			}
			else if (substr($change, 0, 1) == '-')
			{
				$changeClass = 'down';
				$changeImage = 'arrow_down';
			}
			else
			{
				$changeClass = 'nochange';
				$changeImage = false;
			}
			
			$change = '<span class="'.$changeClass.'">'
					.($changeImage ? '<img src="plugins/MultiSites/images/'.$changeImage.'.png" /> ' : '')
					.$change.'</span>';
			
			$details = Piwik_Translate('RowEvolution_MetricDetailsText', array($min, $max, $change));
			
			$color = $colors[ $i % count($colors) ];
			
			$metrics[] = array(
				'label' => $metricData['name'],
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