<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Goals
 */

class Piwik_Goals_Controller extends Piwik_Controller 
{
	const CONVERSION_RATE_PRECISION = 1;
	
	function __construct()
	{
		parent::__construct();
		$this->idSite = Piwik_Common::getRequestVar('idSite');
		$this->goals = Piwik_Goals_API::getGoals($this->idSite);
	}
	
	function goalReport()
	{
		$idGoal = Piwik_Common::getRequestVar('idGoal', null, 'int');
		if(!isset($this->goals[$idGoal]))
		{
			throw new Exception("idgoal $idGoal not valid.");
		}
		$goalDefinition = $this->goals[$idGoal];
		
		$view = new Piwik_View('Goals/templates/single_goal.tpl');
		$view->currency = Piwik::getCurrency();
		$goal = $this->getMetricsForGoal($idGoal);
		foreach($goal as $name => $value)
		{
			$view->$name = $value;
		}
		$view->name = $goalDefinition['name'];
		$view->title = $goalDefinition['name'] . ' - Conversions';
		$view->graphEvolution = $this->getEvolutionGraph(true, array(Piwik_Goals::getRecordName('nb_conversions', $idGoal)), $idGoal);
		$view->nameGraphEvolution = 'GoalsgetEvolutionGraph';
		$view->topSegments = $this->getTopSegments($idGoal);
		
		// conversion rate for new and returning visitors
		$request = new Piwik_API_Request("method=Goals.getConversionRateReturningVisitors&format=original");
		$view->conversion_rate_returning = round( $request->process(), self::CONVERSION_RATE_PRECISION );
		$request = new Piwik_API_Request("method=Goals.getConversionRateNewVisitors&format=original");
		$view->conversion_rate_new = round( $request->process(), self::CONVERSION_RATE_PRECISION );
		
		$verticalSlider = array();
		// string label
		// array parameters to ajax call on click (module, action)
		// specific order
		// (intermediate labels)
		// automatically load the first from the list, highlights it
		$view->tableByConversion = Piwik_FrontController::getInstance()->fetchDispatch('Referers', 'getKeywords', array(false, 'tableGoals'));
		echo $view->render();
	}
	
	protected function getTopSegments($idGoal)
	{
		$columnNbConversions = 'goal_'.$idGoal.'_nb_conversions';
		$columnConversionRate = 'goal_'.$idGoal.'_conversion_rate';
		
		$topSegmentsToLoad = array(
			'country' => 'UserCountry.getCountry',
			'keyword' => 'Referers.getKeywords',
			'website' => 'Referers.getWebsites',
		);
		
		$topSegments = array();
		foreach($topSegmentsToLoad as $segmentName => $apiMethod)
		{
			$request = new Piwik_API_Request("method=$apiMethod
												&format=original
												&filter_update_columns_when_show_all_goals=1
												&filter_sort_order=desc
												&filter_sort_column=$columnNbConversions
												&filter_limit=3");
			$datatable = $request->process();
			$topSegment = array();
			foreach($datatable->getRows() as $row)
			{
				$topSegment[] = array (
					'name' => $row->getColumn('label'),
					'nb_conversions' => $row->getColumn($columnNbConversions),
					'conversion_rate' => $row->getColumn($columnConversionRate),
					'metadata' => $row->getMetadata(),
				);
			}
			$topSegments[$segmentName] = $topSegment;
		}
		return $topSegments;
	}
	
	protected function getMetricsForGoal($idGoal)
	{
		$request = new Piwik_API_Request("method=Goals.get&format=original&idGoal=$idGoal");
		$datatable = $request->process();
		$dataRow = $datatable->getFirstRow();
		return array (
				'id'				=> $idGoal,
				'nb_conversions' 	=> $dataRow->getColumn(Piwik_Goals::getRecordName('nb_conversions', $idGoal)),
				'conversion_rate'	=> round($dataRow->getColumn(Piwik_Goals::getRecordName('conversion_rate', $idGoal)), 1),
				'revenue'			=> $dataRow->getColumn(Piwik_Goals::getRecordName('revenue', $idGoal)),
				'urlSparklineConversions' 		=> $this->getUrlSparkline('getEvolutionGraph', array('columns' => array(Piwik_Goals::getRecordName('nb_conversions', $idGoal)), 'idGoal' => $idGoal)),
				'urlSparklineConversionRate' 	=> $this->getUrlSparkline('getEvolutionGraph', array('columns' => array(Piwik_Goals::getRecordName('conversion_rate', $idGoal)), 'idGoal' => $idGoal)),
				'urlSparklineRevenue' 			=> $this->getUrlSparkline('getEvolutionGraph', array('columns' => array(Piwik_Goals::getRecordName('revenue', $idGoal)), 'idGoal' => $idGoal)),
		);
	}
	
	function index()
	{
		$view = new Piwik_View('Goals/templates/overview.tpl');
		$view->currency = Piwik::getCurrency();
		
		$view->title = 'All goals - evolution';
		$view->graphEvolution = $this->getEvolutionGraph(true, array(Piwik_Goals::getRecordName('nb_conversions')));
		$view->nameGraphEvolution = 'GoalsgetEvolutionGraph'; 

		// sparkline for the historical data of the above values
		$view->urlSparklineConversions		= $this->getUrlSparkline('getEvolutionGraph', array('columns' => array(Piwik_Goals::getRecordName('nb_conversions'))));
		$view->urlSparklineConversionRate 	= $this->getUrlSparkline('getEvolutionGraph', array('columns' => array(Piwik_Goals::getRecordName('conversion_rate'))));
		$view->urlSparklineRevenue 			= $this->getUrlSparkline('getEvolutionGraph', array('columns' => array(Piwik_Goals::getRecordName('revenue'))));

		$request = new Piwik_API_Request("method=Goals.get&format=original");
		$datatable = $request->process();
		$dataRow = $datatable->getFirstRow();
		$view->nb_conversions = $dataRow->getColumn('Goal_nb_conversions');
		$view->conversion_rate = $dataRow->getColumn('Goal_conversion_rate');
		$view->revenue = $dataRow->getColumn('Goal_revenue');
		
		$goalMetrics = array();
		foreach($this->goals as $idGoal => $goal)
		{
			$goalMetrics[$idGoal] = $this->getMetricsForGoal($idGoal);
			$goalMetrics[$idGoal]['name'] = $goal['name'];
		}
		
		$view->goalMetrics = $goalMetrics;
		$view->goals = $this->goals;
		$view->goalsJSON = json_encode($this->goals);
		$view->userCanEditGoals = Piwik::isUserHasAdminAccess($this->idSite);
		echo $view->render();
	}
	
	function addNewGoal()
	{
		$view = new Piwik_View('Goals/templates/add_new_goal.tpl');
		$view->userCanEditGoals = Piwik::isUserHasAdminAccess($this->idSite);
		$view->currency = Piwik::getCurrency();
		$view->onlyShowAddNewGoal = true;
		echo $view->render();
	}

	protected $goalColumnNameToLabel = array(
		'nb_conversions' => 'Goals_ColumnConversions',
		'conversion_rate'=> 'Goals_ColumnConversionRate',
		'revenue' => 'Goals_ColumnRevenue',
	);
	
	public function getEvolutionGraph( $fetch = false, $columns = false, $idGoal = false)
	{
		if(empty($columns))
		{
			$columns = Piwik_Common::getRequestVar('columns');
		}
		if(empty($idGoal))
		{
			$idGoal = Piwik_Common::getRequestVar('idGoal', false);
		}
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'Goals.get');
		$view->setParametersToModify(array('idGoal' => $idGoal));
		
		foreach($columns as $columnName)
		{
			// find the right translation for this column, eg. find 'revenue' if column is Goal_1_revenue
			foreach($this->goalColumnNameToLabel as $metric => $metricTranslation)
			{
				if(strpos($columnName, $metric) !== false)
				{
					$columnTranslation = Piwik_Translate($metricTranslation);
					break;
				}
			}
			
			if(!empty($idGoal) && isset($this->goals[$idGoal]))
			{
				$goalName = $this->goals[$idGoal]['name'];
				$columnTranslation = "$columnTranslation (goal \"$goalName\")";
			}
			$view->setColumnTranslation($columnName, $columnTranslation);
		}
		$view->setColumnsToDisplay($columns);
		return $this->renderView($view, $fetch);
	}
	
	function getLastNbConversionsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'Goals.getConversions');
		return $this->renderView($view, $fetch);
	}
	
	function getLastConversionRateGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'Goals.getConversionRate');
		return $this->renderView($view, $fetch); 
	}

	function getLastRevenueGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'Goals.getRevenue');
		return $this->renderView($view, $fetch);
	}
}
