<?php
require_once "ViewDataTable.php";

class Piwik_VisitFrequency_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('VisitFrequency/index.tpl');
		$view->graphEvolutionVisitFrequency = $this->getLastVisitsReturningGraph( true );
		$this->setSparklinesAndNumbers($view);
		echo $view->render();
	}
	
	protected function setSparklinesAndNumbers($view)
	{
		
		$view->urlSparklineNbVisitsReturning 		= $this->getUrlSparkline( 'getLastVisitsReturningGraph');
		$view->urlSparklineNbActionsReturning 		= $this->getUrlSparkline( 'getLastActionsReturningGraph');
		$view->urlSparklineSumVisitLengthReturning 	= $this->getUrlSparkline( 'getLastSumVisitsLengthReturningGraph');
		$view->urlSparklineMaxActionsReturning 		= $this->getUrlSparkline( 'getLastMaxActionsReturningGraph');
		$view->urlSparklineBounceCountReturning 	= $this->getUrlSparkline( 'getLastBounceCountReturningGraph');
		
		$dataTableFrequency = $this->getSummary();
		$view->nbVisitsReturning = $dataTableFrequency->getColumn('nb_visits_returning');
		$view->nbActionsReturning = $dataTableFrequency->getColumn('nb_actions_returning');
		$view->maxActionsReturning = $dataTableFrequency->getColumn('max_actions_returning');
		$view->sumVisitLengthReturning = $dataTableFrequency->getColumn('sum_visit_length_returning');
		$view->bounceCountReturning = $dataTableFrequency->getColumn('bounce_count_returning');
	}

	function getSparklines()
	{
		$view = new Piwik_View('VisitFrequency/sparklines.tpl');
		$this->setSparklinesAndNumbers($view);		
		echo $view->render();
	}

	protected function getSummary()
	{		
		$requestString = 'method='."VisitFrequency.getSummary".'&format=original';
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}
	
	function getLastVisitsReturningGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "VisitFrequency.getVisitsReturning");
		return $this->renderView($view, $fetch);
	}
		
	function getLastActionsReturningGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "VisitFrequency.getActionsReturning");
		return $this->renderView($view, $fetch);
	}
	
	function getLastSumVisitsLengthReturningGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "VisitFrequency.getSumVisitsLengthReturning");
		return $this->renderView($view, $fetch);
	}
	
	function getLastMaxActionsReturningGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "VisitFrequency.getMaxActionsReturning");
		return $this->renderView($view, $fetch);
	}
	
	function getLastBounceCountReturningGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "VisitFrequency.getBounceCountReturning");
		return $this->renderView($view, $fetch);
	}
}
