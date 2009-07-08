<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_VisitsSummary
 */

class Piwik_VisitsSummary_Controller extends Piwik_Controller 
{
	public function index()
	{
		$view = new Piwik_View('VisitsSummary/templates/index.tpl');
		$this->setPeriodVariablesView($view);
		$view->graphEvolutionVisitsSummary = $this->getEvolutionGraph( true, array('nb_visits') );
		$this->setSparklinesAndNumbers($view);		
		echo $view->render();
	}
	
	public function getSparklines()
	{
		$view = new Piwik_View('VisitsSummary/templates/sparklines.tpl');
		$this->setSparklinesAndNumbers($view);		
		echo $view->render();
	}

	public function getEvolutionGraph( $fetch = false, $columns = false)
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "VisitsSummary.get");
		if(empty($columns))
		{
			$columns = Piwik_Common::getRequestVar('columns');
		}
		$view->setColumnsToDisplay($columns);
		return $this->renderView($view, $fetch);
	}

	static public function getVisitsSummary()
	{
		$requestString =	"method=VisitsSummary.get".
							"&format=original".
							// we disable filters for example "search for pattern", in the case this method is called 
							// by a method that already calls the API with some generic filters applied 
							"&disable_generic_filters=1"; 
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}

	static public function getVisits()
	{
		$requestString = 	"method=VisitsSummary.getVisits".
							"&format=original".
							"&disable_generic_filters=1"; 
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}
	
	protected function setSparklinesAndNumbers($view)
	{
		$view->urlSparklineNbVisits 		= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('nb_visits')));
		$view->urlSparklineNbActions 		= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('nb_actions')));
		$view->urlSparklineSumVisitLength 	= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('sum_visit_length')));
		$view->urlSparklineMaxActions 		= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('max_actions')));
		$view->urlSparklineBounceRate 		= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('bounce_rate')));
		
		$dataTableVisit = self::getVisitsSummary();
		$dataRow = $dataTableVisit->getFirstRow();
		if($view->period == 'day')
		{
			$view->urlSparklineNbUniqVisitors 	= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('nb_uniq_visitors')));
			$view->nbUniqVisitors = $dataRow->getColumn('nb_uniq_visitors');
		}
		$nbVisits = $dataRow->getColumn('nb_visits');
		$view->nbVisits = $nbVisits;
		$view->nbActions = $dataRow->getColumn('nb_actions');
		$view->sumVisitLength = $dataRow->getColumn('sum_visit_length');
		$nbBouncedVisits = $dataRow->getColumn('bounce_count');
		$view->bounceRate = Piwik::getPercentageSafe($nbBouncedVisits, $nbVisits);
		$view->maxActions = $dataRow->getColumn('max_actions');
	}
}
