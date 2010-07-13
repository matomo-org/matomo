<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_VisitFrequency
 */

/**
 *
 * @package Piwik_VisitFrequency
 */
class Piwik_VisitFrequency_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = Piwik_View::factory('index');
		$view->graphEvolutionVisitFrequency = $this->getEvolutionGraph(true, array('nb_visits_returning') );
		$this->setSparklinesAndNumbers($view);
		echo $view->render();
	}
	
	public function getSparklines()
	{
		$view = Piwik_View::factory('sparklines');
		$this->setSparklinesAndNumbers($view);		
		echo $view->render();
	}
	
	public function getEvolutionGraph( $fetch = false, $columns = false)
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "VisitFrequency.get");
		if(empty($columns))
		{
			$columns = Piwik_Common::getRequestVar('columns');
		}
		$view->setColumnsToDisplay($columns);
		$view->setColumnsTranslations(array(	
			'nb_visits_returning' => Piwik_Translate('VisitFrequency_ColumnReturningVisits'),
			'nb_actions_returning' => Piwik_Translate('VisitFrequency_ColumnActionsByReturningVisits'), 
			'avg_visit_length_returning' => Piwik_Translate('VisitFrequency_ColumnAverageVisitDurationForReturningVisitors'),
			'bounce_rate_returning' => Piwik_Translate('VisitFrequency_ColumnBounceRateForReturningVisits'),
			'nb_actions_per_visit_returning' => Piwik_Translate('VisitFrequency_ColumnAvgActionsPerReturningVisit'),
		));
		return $this->renderView($view, $fetch);
	}
	
	protected function setSparklinesAndNumbers($view)
	{
		$view->urlSparklineNbVisitsReturning 		= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('nb_visits_returning')));
		$view->urlSparklineNbActionsReturning 		= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('nb_actions_returning')));
		$view->urlSparklineActionsPerVisitReturning 		= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('nb_actions_per_visit_returning')));
		$view->urlSparklineAvgVisitDurationReturning = $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('avg_visit_length_returning')));
		$view->urlSparklineBounceRateReturning 	= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('bounce_rate_returning')));
		
		$dataTableFrequency = $this->getSummary();
		$dataRow = $dataTableFrequency->getFirstRow();
		$nbVisitsReturning = $dataRow->getColumn('nb_visits_returning');
		$view->nbVisitsReturning = $nbVisitsReturning;
		$view->nbActionsReturning = $dataRow->getColumn('nb_actions_returning');
		$view->nbActionsPerVisitReturning = $dataRow->getColumn('nb_actions_per_visit_returning');
		$view->avgVisitDurationReturning = $dataRow->getColumn('avg_visit_length_returning');
		$nbBouncedReturningVisits = $dataRow->getColumn('bounce_count_returning');
		$view->bounceRateReturning = Piwik::getPercentageSafe($nbBouncedReturningVisits, $nbVisitsReturning);
		
	}

	protected function getSummary()
	{		
		$requestString = "method=VisitFrequency.get&format=original";
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}
}
