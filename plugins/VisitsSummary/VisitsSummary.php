<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_VisitsSummary
 */
	
/**
 * 
 * @package Piwik_VisitsSummary
 */
class Piwik_VisitsSummary extends Piwik_Plugin
{
	public function getInformation()
	{
		$info = array(
			// name must be the className prefix!
			'name' => 'Piwik_VisitsSummary',
			'description' => 'Description',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
			'translationAvailable' => false,
		);
		
		return $info;
	}
	
}

require_once "ViewDataTable.php";
class Piwik_VisitsSummary_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('VisitsSummary/index.tpl');
		// period
		$currentPeriod = Piwik_Common::getRequestVar('period');
		$view->period = $currentPeriod;
		
		$view->graphEvolutionVisitsSummary = $this->getLastVisitsGraph( true );
		
		$this->setSparklinesAndNumbers($view);		
		echo $view->render();
	}
	
	protected function setSparklinesAndNumbers($view)
	{
		$view->urlSparklineNbVisits 		= $this->getUrlSparkline( 'getLastVisitsGraph');
		$view->urlSparklineNbUniqVisitors 	= $this->getUrlSparkline( 'getLastUniqueVisitorsGraph');
		$view->urlSparklineNbActions 		= $this->getUrlSparkline( 'getLastActionsGraph');
		$view->urlSparklineSumVisitLength 	= $this->getUrlSparkline( 'getLastSumVisitsLengthGraph');
		$view->urlSparklineMaxActions 		= $this->getUrlSparkline( 'getLastMaxActionsGraph');
		$view->urlSparklineBounceCount 		= $this->getUrlSparkline( 'getLastBounceCountGraph');
		
		$dataTableVisit = self::getVisitsSummary();
		$view->nbUniqVisitors = $dataTableVisit->getColumn('nb_uniq_visitors');
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
	/**
	 * General visit
	 */
	static public function getVisitsSummary()
	{
		$requestString = 'method=' . "VisitsSummary.get" . '&format=original'.
			// we disable filters for example "search for pattern", in the case this method is called 
			// by a method that already calls the API with some generic filters applied 
			'&disable_generic_filters=true'; 
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

Piwik_AddWidget( 'VisitsSummary', 'getLastVisitsGraph', 'Last visits graph');
Piwik_AddWidget( 'VisitsSummary', 'getSparklines', 'Visits overview');
Piwik_AddWidget( 'VisitsSummary', 'getLastUniqueVisitorsGraph', 'Last unique visitors graph');
Piwik_AddWidget( 'VisitsSummary', 'index', 'Overview with graph');

Piwik_AddMenu('Visitors', 'Overview', array('module' => 'VisitsSummary'), true);