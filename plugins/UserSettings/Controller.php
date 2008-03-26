<?php
require_once "ViewDataTable.php";

class Piwik_UserSettings_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('UserSettings/index.tpl');
		
		/* User settings */		
		$view->dataTablePlugin = $this->getPlugin( true );
		$view->dataTableResolution = $this->getResolution( true );
		$view->dataTableConfiguration = $this->getConfiguration( true );
		$view->dataTableOS = $this->getOS( true );
		$view->dataTableBrowser = $this->getBrowser( true );
		$view->dataTableBrowserType = $this->getBrowserType ( true );
		$view->dataTableWideScreen = $this->getWideScreen( true );
		
		echo $view->render();
	}
	

	/**
	 * User settings
	 */
	protected function getStandardDataTableUserSettings( $currentControllerAction, 
												$APItoCall,
												$defaultDatatableType = null )
	{
		$view = Piwik_ViewDataTable::factory( $defaultDatatableType);
		$view->init( $this->pluginName,  $currentControllerAction, $APItoCall );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors') );
		$view->setSortedColumn( 1 );
		$view->setLimit( 5 );
		$view->setGraphLimit(5);
		return $view;
	}
	
	function getResolution( $fetch = false)
	{
		$view = $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getResolution'
									);
		return $this->renderView($view, $fetch);
	}
	
	function getConfiguration( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getConfiguration'
									);
		$view->setLimit( 3 );
		return $this->renderView($view, $fetch);
	}
	
	function getOS( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getOS'
									);
		return $this->renderView($view, $fetch);
	}
	
	function getBrowser( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getBrowser'
									);
		$view->setGraphLimit(7);
		return $this->renderView($view, $fetch);
	}
	
	function getBrowserType ( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getBrowserType',
										'graphPie'
									);
		$view->disableOffsetInformation();
		return $this->renderView($view, $fetch);
	}
	
	function getWideScreen( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getWideScreen'
									);
		$view->disableOffsetInformation();
		return $this->renderView($view, $fetch);
	}
	
	function getPlugin( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory( 'graphVerticalBar');
		$view->init( $this->pluginName,  __FUNCTION__, 'UserSettings.getPlugin' );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->disableSort();
		$view->disableOffsetInformation();
		
		$view->setColumnsToDisplay( array('label','nb_visits') );
		$view->setSortedColumn( 'nb_visits' );
		$view->setGraphLimit( 10 );
		$view->setLimit( 10 );
		
		return $this->renderView($view, $fetch);
	}
}