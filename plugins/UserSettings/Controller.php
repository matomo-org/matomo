<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_UserSettings
 */

/**
 *
 * @package Piwik_UserSettings
 */
class Piwik_UserSettings_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = Piwik_View::factory('index');
		
		$view->dataTablePlugin = $this->getPlugin( true );
		$view->dataTableResolution = $this->getResolution( true );
		$view->dataTableConfiguration = $this->getConfiguration( true );
		$view->dataTableOS = $this->getOS( true );
		$view->dataTableBrowser = $this->getBrowser( true );
		$view->dataTableBrowserType = $this->getBrowserType ( true );
		$view->dataTableWideScreen = $this->getWideScreen( true );
		
		echo $view->render();
	}

	function getResolution( $fetch = false)
	{
		$view = $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getResolution'
									);		
		$view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnResolution'));
		return $this->renderView($view, $fetch);
	}
	
	function getConfiguration( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getConfiguration'
									);
		$view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnConfiguration'));
		$view->setLimit( 3 );
		return $this->renderView($view, $fetch);
	}
	
	function getOS( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getOS'
									);
		$view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnOperatingSystem'));
		return $this->renderView($view, $fetch);
	}
	
	function getBrowser( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getBrowser'
									);
		$view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnBrowser'));
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
		$view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnBrowserFamily'));
		$view->disableOffsetInformationAndPaginationControls();
		return $this->renderView($view, $fetch);
	}
	
	function getWideScreen( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getWideScreen'
									);
		$view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnTypeOfScreen'));
		$view->disableOffsetInformationAndPaginationControls();
		return $this->renderView($view, $fetch);
	}
	
	function getPlugin( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getPlugin'
									);
		$view->disableShowAllViewsIcons();
		$view->disableShowAllColumns();
		$view->setColumnsToDisplay( array('label','nb_visits_percentage','nb_visits') );
		$view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnPlugin'));
		$view->setColumnTranslation('nb_visits_percentage', str_replace(' ', '&nbsp;', Piwik_Translate('General_ColumnPercentageVisits')));
		$view->setSortedColumn('nb_visits_percentage');
		$view->setLimit( 10 );
		$view->setFooterMessage( Piwik_Translate('UserSettings_PluginDetectionDoesNotWorkInIE'));
		return $this->renderView($view, $fetch);
	}
	
	protected function getStandardDataTableUserSettings( $currentControllerAction, 
												$APItoCall,
												$defaultDatatableType = null )
	{
		$view = Piwik_ViewDataTable::factory( $defaultDatatableType);
		$view->init( $this->pluginName,  $currentControllerAction, $APItoCall );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setLimit( 5 );
		$view->setGraphLimit(5);
		
		$this->setPeriodVariablesView($view);
		
		// columns is set in the request if metrics picker has been used
		$columns = Piwik_Common::getRequestVar('columns', false);
		if ($columns !== false)
		{
			$columns = Piwik::getArrayFromApiParameter($columns);
			$firstColumn = $columns[0];
		}
		else
		{
			// default columns
			$firstColumn = $view->period == 'day' ? 'nb_uniq_visitors' : 'nb_visits';
			$columns = array($firstColumn);
		}
		
		$view->setSortedColumn( $firstColumn );
		
		// displayed columns
		array_unshift($columns, 'label');
		$view->setColumnsToDisplay( $columns );
		
		// selectable columns
		$selectableColumns = array();
		if ($view->period == 'day')
		{
			$selectableColumns = array('nb_uniq_visitors');
		}
		else
		{
			$selectableColumns = array();
		}
		$selectableColumns[] = 'nb_visits';
		$selectableColumns[] = 'nb_actions';
		
		$view->setSelectableColumns($selectableColumns);
		
		return $view;
	}
}
