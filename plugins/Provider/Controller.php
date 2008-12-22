<?php
class Piwik_Provider_Controller extends Piwik_Controller 
{	
	/**
	 * Provider
	 */
	function getProvider($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName,  __FUNCTION__, "Provider.getProvider" );
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors') );
		$view->setSortedColumn( 1 );
		$view->setLimit( 5 );
		return $this->renderView($view, $fetch);
	}
	
}

