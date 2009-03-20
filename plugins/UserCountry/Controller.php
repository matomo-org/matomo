<?php
require_once "ViewDataTable.php";

class Piwik_UserCountry_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('UserCountry/index.tpl');
		
		$view->urlSparklineCountries = $this->getUrlSparkline('getLastDistinctCountriesGraph');
		$view->numberDistinctCountries = $this->getNumberOfDistinctCountries(true);
		
		$view->dataTableCountry = $this->getCountry(true);
		$view->dataTableContinent = $this->getContinent(true);
		
		echo $view->render();
	}
	
	function getCountry( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName, __FUNCTION__, "UserCountry.getCountry" );
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors') );
		$view->setSortedColumn( 1 );
		$view->setLimit( 5 );
		$view->enableShowGoals();
		
		return $this->renderView($view, $fetch);
	}

	function getContinent( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory( 'graphVerticalBar' );
		$view->init( $this->pluginName, __FUNCTION__, "UserCountry.getContinent" );
		$view->disableExcludeLowPopulation();
		$view->disableSearchBox();
		$view->disableOffsetInformation();
		$view->disableSort();
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors') );
		$view->setSortedColumn( 1 );
		$view->enableShowGoals();
		
		return $this->renderView($view, $fetch);
	}
	
	function getNumberOfDistinctCountries( $fetch = false)
	{
		return $this->getNumericValue('UserCountry.getNumberOfDistinctCountries');
	}

	function getLastDistinctCountriesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph('UserCountry',__FUNCTION__, "UserCountry.getNumberOfDistinctCountries");
		return $this->renderView($view, $fetch);
	}
}
