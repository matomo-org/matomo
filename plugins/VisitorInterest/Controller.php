<?php

require_once "ViewDataTable.php";
class Piwik_VisitorInterest_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('VisitorInterest/index.tpl');
		$view->dataTableNumberOfVisitsPerVisitDuration = $this->getNumberOfVisitsPerVisitDuration(true);
		$view->dataTableNumberOfVisitsPerPage = $this->getNumberOfVisitsPerPage(true);
		echo $view->render();
	}
	
	function getNumberOfVisitsPerVisitDuration( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory( 'cloud' );
		$view->init( $this->pluginName,  __FUNCTION__, 
									"VisitorInterest.getNumberOfVisitsPerVisitDuration" );
		
		$view->setColumnsToDisplay( array('label','nb_visits') );
		$view->disableSort();
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		$view->disableSearchBox();
		
		return $this->renderView($view, $fetch);
	}
	
	function getNumberOfVisitsPerPage( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory( 'cloud' );
		$view->init( $this->pluginName,  __FUNCTION__, 
									"VisitorInterest.getNumberOfVisitsPerPage" );
		
		$view->setColumnsToDisplay( array('label','nb_visits') );
		$view->setSortedColumn( 'nb_visits', 'asc' );
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		$view->disableSearchBox();
		$view->disableSort();
		$view->main();
		
		return $this->renderView($view, $fetch);
	}
}
