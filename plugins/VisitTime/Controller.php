<?php

require_once "ViewDataTable.php";
class Piwik_VisitTime_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('VisitTime/index.tpl');
		
		/* VisitorTime */
		$view->dataTableVisitInformationPerLocalTime = $this->getVisitInformationPerLocalTime(true);
		$view->dataTableVisitInformationPerServerTime = $this->getVisitInformationPerServerTime(true);
		
		echo $view->render();
	}
		
	/**
	 * VisitTime
	 */
	function getVisitInformationPerServerTime( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory( 'graphVerticalBar');
		$view->init( $this->pluginName,  __FUNCTION__, 
								"VisitTime.getVisitInformationPerServerTime" );
		
		$view->setColumnsToDisplay( array('label','nb_visits') );
		$view->setSortedColumn( 0, 'asc' );
		$view->setLimit( 24 );
		$view->setGraphLimit( 24 );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		
		return $this->renderView($view, $fetch);
	}
	
	function getVisitInformationPerLocalTime( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory( 'graphVerticalBar');
		$view->init( $this->pluginName,  __FUNCTION__, 
								"VisitTime.getVisitInformationPerLocalTime" );
		
		$view->setColumnsToDisplay( array('label','nb_visits') );
		$view->setSortedColumn( 0, 'asc' );
		$view->setLimit( 24 );
		$view->setGraphLimit( 24 );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		
		return $this->renderView($view, $fetch);
	}
}
