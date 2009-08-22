<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_UserCountry
 */

/**
 *
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = Piwik_View::factory('index');
		
		$view->urlSparklineCountries = $this->getUrlSparkline('getLastDistinctCountriesGraph');
		$view->numberDistinctCountries = $this->getNumberOfDistinctCountries(true);
		
		$view->dataTableCountry = $this->getCountry(true);
		$view->dataTableContinent = $this->getContinent(true);
		
		echo $view->render();
	}
	
	function getCountry( $fetch = false)
	{
		$view = $this->getStandardDataTableUserCountry(__FUNCTION__, "UserCountry.getCountry");
		$view->setLimit( 5 );
		$view->setColumnTranslation('label', Piwik_Translate('UserCountry_Country'));
		return $this->renderView($view, $fetch);
	}

	function getContinent( $fetch = false)
	{
		$view = $this->getStandardDataTableUserCountry(__FUNCTION__, "UserCountry.getContinent", 'graphVerticalBar');
		$view->disableSearchBox();
		$view->disableOffsetInformation();
		$view->setColumnTranslation('label', Piwik_Translate('UserCountry_Continent'));
		return $this->renderView($view, $fetch);
	}
	
	protected function getStandardDataTableUserCountry( $currentControllerAction, 
												$APItoCall,
												$defaultDatatableType = null )
	{
		$view = Piwik_ViewDataTable::factory( $defaultDatatableType );
		$view->init( $this->pluginName, $currentControllerAction, $APItoCall );
		$view->disableExcludeLowPopulation();
	
		$this->setPeriodVariablesView($view);
		$column = 'nb_visits';
		if($view->period == 'day')
		{
			$column = 'nb_uniq_visitors';
		}
		$view->setColumnsToDisplay( array('label',$column) );
		$view->setSortedColumn( $column );
		$view->enableShowGoals();
		return $view;
	}
	
	function getNumberOfDistinctCountries( $fetch = false)
	{
		return $this->getNumericValue('UserCountry.getNumberOfDistinctCountries');
	}

	function getLastDistinctCountriesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph('UserCountry',__FUNCTION__, "UserCountry.getNumberOfDistinctCountries");
		$view->setColumnsToDisplay('UserCountry_distinctCountries');
		return $this->renderView($view, $fetch);
	}
}
