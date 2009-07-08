<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_UserSettings
 */

class Piwik_UserSettings_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('UserSettings/templates/index.tpl');
		
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
		$view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnOperatinsSystem'));
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
		$view->disableOffsetInformation();
		return $this->renderView($view, $fetch);
	}
	
	function getWideScreen( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getWideScreen'
									);
		$view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnTypeOfScreen'));
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
		$view->disableShowAllColumns();
		$view->disallowPercentageInGraphTooltip();
		$view->setColumnsToDisplay( array('label','nb_visits') );
		$view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnPlugin'));
		$view->setGraphLimit( 10 );
		$view->setLimit( 10 );
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
		$column = 'nb_visits';
		if($view->period == 'day')
		{
			$column = 'nb_uniq_visitors';
		}
		$view->setSortedColumn( $column );
		$view->setColumnsToDisplay( array('label', $column) );
		return $view;
	}
}
