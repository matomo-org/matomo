<?php
require_once "ViewDataTable.php";

class Piwik_VisitsSummary_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('VisitsSummary/index.tpl');
		$this->setPeriodVariablesView($view);
		$view->graphEvolutionVisitsSummary = $this->getLastVisitsGraph( true );
		$this->setSparklinesAndNumbers($view);		
		echo $view->render();
	}
	
	protected function setSparklinesAndNumbers($view)
	{
		$view->urlSparklineNbVisits 		= $this->getUrlSparkline( 'getLastVisitsGraph');
		$view->urlSparklineNbActions 		= $this->getUrlSparkline( 'getLastActionsGraph');
		$view->urlSparklineSumVisitLength 	= $this->getUrlSparkline( 'getLastSumVisitsLengthGraph');
		$view->urlSparklineMaxActions 		= $this->getUrlSparkline( 'getLastMaxActionsGraph');
		$view->urlSparklineBounceCount 		= $this->getUrlSparkline( 'getLastBounceCountGraph');
		
		$dataTableVisit = self::getVisitsSummary();
		
		if($view->period == 'day')
		{
			$view->urlSparklineNbUniqVisitors 	= $this->getUrlSparkline( 'getLastUniqueVisitorsGraph');
			$view->nbUniqVisitors = $dataTableVisit->getColumn('nb_uniq_visitors');
		}
		$view->nbVisits = $dataTableVisit->getColumn('nb_visits');
		$view->nbActions = $dataTableVisit->getColumn('nb_actions');
		$view->sumVisitLength = $dataTableVisit->getColumn('sum_visit_length');
		$view->bounceCount = $dataTableVisit->getColumn('bounce_count');
		$view->maxActions = $dataTableVisit->getColumn('max_actions');
	}
	
	function getSparklines()
	{
		$view = new Piwik_View('VisitsSummary/sparklines.tpl');
		$this->setSparklinesAndNumbers($view);		
		echo $view->render();
	}

	static public function getVisits()
	{
		$requestString = 	"method=VisitsSummary.getVisits".
							"&format=original".
							"&disable_generic_filters=true"; 
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}
	
	static public function getVisitsSummary()
	{
		$requestString =	"method=VisitsSummary.get".
							"&format=original".
							// we disable filters for example "search for pattern", in the case this method is called 
							// by a method that already calls the API with some generic filters applied 
							"&disable_generic_filters=true"; 
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}

	function getLastVisitsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph('VisitsSummary', __FUNCTION__, "VisitsSummary.getVisits");
		return $this->renderView($view, $fetch);
	}
	
	function getLastUniqueVisitorsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph('VisitsSummary', __FUNCTION__, "VisitsSummary.getUniqueVisitors");
		return $this->renderView($view, $fetch);
	}
	
	function getLastActionsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph('VisitsSummary', __FUNCTION__, "VisitsSummary.getActions");
		return $this->renderView($view, $fetch);
	}
	
	function getLastSumVisitsLengthGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph('VisitsSummary', __FUNCTION__, "VisitsSummary.getSumVisitsLength");
		return $this->renderView($view, $fetch);
	}
	
	function getLastMaxActionsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph('VisitsSummary', __FUNCTION__, "VisitsSummary.getMaxActions");
		return $this->renderView($view, $fetch);
	}
	
	function getLastBounceCountGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph('VisitsSummary', __FUNCTION__, "VisitsSummary.getBounceCount");
		return $this->renderView($view, $fetch);
	}
}
