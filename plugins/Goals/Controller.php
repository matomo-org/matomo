<?php
class Piwik_Goals_Controller extends Piwik_Controller 
{
	const CONVERSION_RATE_PRECISION = 1;
	function goalReport()
	{
		$idGoal = Piwik_Common::getRequestVar('idGoal', null, 'int');
		$goalDefinition = Piwik_Tracker_GoalManager::getGoalDefinition($idGoal);
		
		$view = new Piwik_View('Goals/templates/single_goal.tpl');
		$view->currency = Piwik::getCurrency();
		$goal = $this->getMetricsForGoal($idGoal);
		foreach($goal as $name => $value)
		{
			$view->$name = $value;
		}
		$view->name = $goalDefinition['name'];
		$view->title = $goalDefinition['name'] . ' - Conversions';
		$view->graphEvolution = $this->getLastNbConversionsGraph(true);
		$view->nameGraphEvolution = 'GoalsgetLastNbConversionsGraph'; // must be the function name used above
		
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
//			echo $datatable;
		}
		
		$request = new Piwik_API_Request("method=Goals.getConversionRateReturningVisitors&format=original");
		$view->conversion_rate_returning = round( $request->process(), self::CONVERSION_RATE_PRECISION );
		$request = new Piwik_API_Request("method=Goals.getConversionRateNewVisitors&format=original");
		$view->conversion_rate_new = round( $request->process(), self::CONVERSION_RATE_PRECISION );
		
		$view->topSegments = $topSegments;
		echo $view->render();
		//todo next: nice legends for graphs
	}
	
	protected function getMetricsForGoal($goalId)
	{
		$request = new Piwik_API_Request("method=Goals.get&format=original&idGoal=$goalId");
		$datatable = $request->process();
		return array (
				'id'				=> $goalId,
				'nb_conversions' 	=> $datatable->getRowFromLabel(Piwik_Goals::getRecordName('nb_conversions', $goalId))->getColumn('value'),
				'conversion_rate'	=> round($datatable->getRowFromLabel(Piwik_Goals::getRecordName('conversion_rate', $goalId))->getColumn('value'), 1),
				'revenue'			=> $datatable->getRowFromLabel(Piwik_Goals::getRecordName('revenue', $goalId))->getColumn('value'),
				'urlSparklineConversions' 		=> $this->getUrlSparkline('getLastNbConversionsGraph', $goalId) . "&idGoal=".$goalId,
				'urlSparklineConversionRate' 	=> $this->getUrlSparkline('getLastConversionRateGraph', $goalId) . "&idGoal=".$goalId,
				'urlSparklineRevenue' 			=> $this->getUrlSparkline('getLastRevenueGraph', $goalId) . "&idGoal=".$goalId,
		);
	}
	
	function index()
	{
		$view = new Piwik_View('Goals/templates/overview.tpl');
		$view->currency = Piwik::getCurrency();
		
		$view->title = 'All goals - evolution';
		$view->graphEvolution = $this->getLastNbConversionsGraph(true);
		$view->nameGraphEvolution = 'GoalsgetLastNbConversionsGraph'; // must be the function name used above

		// sparkline for the historical data of the above values
		$view->urlSparklineConversions		= $this->getUrlSparkline('getLastNbConversionsGraph');
		$view->urlSparklineConversionRate 	= $this->getUrlSparkline('getLastConversionRateGraph');
		$view->urlSparklineRevenue 			= $this->getUrlSparkline('getLastRevenueGraph');

		$request = new Piwik_API_Request("method=Goals.get&format=original");
		$datatable = $request->process();
		$view->nb_conversions = $datatable->getRowFromLabel('Goal_nb_conversions')->getColumn('value');
		$view->conversion_rate = $datatable->getRowFromLabel('Goal_conversion_rate')->getColumn('value');
		$view->revenue = $datatable->getRowFromLabel('Goal_revenue')->getColumn('value');
		
		$goalMetrics = array();
		
		$goals = Piwik_Tracker_GoalManager::getGoalDefinitions();
		foreach($goals as $goal)
		{
			$goalId = $goal['id'];
			$goalMetrics[$goalId] = $this->getMetricsForGoal($goalId);
			$goalMetrics[$goalId]['name'] = $goal['name'];
		}
		
		$view->goalMetrics = $goalMetrics;
		echo $view->render();
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
