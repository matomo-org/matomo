<?php


require_once "ViewDataTable.php";
class Piwik_Provider_Controller extends Piwik_Controller 
{	
	/**
	 * Provider
	 */
	function getProvider()
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( 'Provider',  __FUNCTION__, "Provider.getProvider" );
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 1 );
		$view->setLimit( 5 );
		
		$view->main();
		echo $view->render();
	}
	
}

