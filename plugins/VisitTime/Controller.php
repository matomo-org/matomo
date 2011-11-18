<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_VisitTime
 */

/**
 *
 * @package Piwik_VisitTime
 */
class Piwik_VisitTime_Controller extends Piwik_Controller 
{
	public function index()
	{
		$view = Piwik_View::factory('index');
		$view->dataTableVisitInformationPerLocalTime = $this->getVisitInformationPerLocalTime(true);
		$view->dataTableVisitInformationPerServerTime = $this->getVisitInformationPerServerTime(true);
		echo $view->render();
	}
		
	public function getVisitInformationPerServerTime( $fetch = false)
	{
		$view = $this->getGraph(__FUNCTION__, 'VisitTime.getVisitInformationPerServerTime',
				'VisitTime_ColumnServerTime');
		
		$view->setCustomParameter('hideFutureHoursWhenToday', 1);
		$view->enableShowGoals();
		
		return $this->renderView($view, $fetch);
	}
	
	public function getVisitInformationPerLocalTime( $fetch = false)
	{
		$view = $this->getGraph(__FUNCTION__, 'VisitTime.getVisitInformationPerLocalTime',
					'VisitTime_ColumnLocalTime');
		
		return $this->renderView($view, $fetch);
	}
	
	private function getGraph( $controllerMethod, $apiMethod, $labelTranslation )
	{
		$view = Piwik_ViewDataTable::factory('graphVerticalBar');
		$view->init($this->pluginName, $controllerMethod, $apiMethod);
		
		$this->setMetricsVariablesView($view);
		
		$view->setColumnTranslation('label', Piwik_Translate($labelTranslation));
		$view->setSortedColumn( 'label', 'asc' );
		
		$view->setLimit( 24 );
		$view->setGraphLimit( 24 );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformationAndPaginationControls();
		
		return $view;
	}
	
}
