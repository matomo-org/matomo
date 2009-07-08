<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_VisitTime
 */

class Piwik_VisitTime_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('VisitTime/templates/index.tpl');
		$view->dataTableVisitInformationPerLocalTime = $this->getVisitInformationPerLocalTime(true);
		$view->dataTableVisitInformationPerServerTime = $this->getVisitInformationPerServerTime(true);
		echo $view->render();
	}
		
	function getVisitInformationPerServerTime( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory( 'graphVerticalBar');
		$view->init( $this->pluginName,  __FUNCTION__, "VisitTime.getVisitInformationPerServerTime" );
		
		$view->setColumnsToDisplay( array('label','nb_visits') );
		$view->setSortedColumn( 'label', 'asc' );		
		$view->setColumnTranslation('label', Piwik_Translate('VisitTime_ColumnServerTime'));
		$view->setLimit( 24 );
		$view->setGraphLimit( 24 );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		$view->enableShowGoals();
		
		return $this->renderView($view, $fetch);
	}
	
	function getVisitInformationPerLocalTime( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory( 'graphVerticalBar');
		$view->init( $this->pluginName,  __FUNCTION__, "VisitTime.getVisitInformationPerLocalTime" );
		$view->setColumnTranslation('label', Piwik_Translate('VisitTime_ColumnLocalTime'));
		$view->setColumnsToDisplay( array('label','nb_visits') );
		$view->setSortedColumn( 'label', 'asc' );
		$view->setLimit( 24 );
		$view->setGraphLimit( 24 );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		
		return $this->renderView($view, $fetch);
	}
}
