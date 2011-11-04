<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitsSummary
 */

/**
 *
 * @package Piwik_VisitsSummary
 */
class Piwik_VisitsSummary_Controller extends Piwik_Controller
{
	public function index()
	{
		$view = Piwik_View::factory('index');
		$this->setPeriodVariablesView($view);
		$view->graphEvolutionVisitsSummary = $this->getEvolutionGraph( true, 
				array('VisitsSummary.nb_visits', 'VisitsSummary.nb_uniq_visitors') );
		$this->setSparklinesAndNumbers($view);
		echo $view->render();
	}
	
	public function getSparklines()
	{
		$view = Piwik_View::factory('sparklines');
		$this->setPeriodVariablesView($view);
		$this->setSparklinesAndNumbers($view);
		echo $view->render();
	}

	public function getEvolutionGraph( $fetch = false, $columns = false )
	{
		if(empty($columns))
		{
			$columns = Piwik_Common::getRequestVar('columns');
			$columns = Piwik::getArrayFromApiParameter($columns);
		}
		
		$doc = Piwik_Translate('VisitsSummary_VisitsSummaryDocumentation').'<br />'
		     . Piwik_Translate('General_BrokenDownReportDocumentation').'<br /><br />'
		     
		     . '<b>'.Piwik_Translate('General_ColumnNbVisits').':</b> '
		     . Piwik_Translate('General_ColumnNbVisitsDocumentation').'<br />'
		     
		     . '<b>'.Piwik_Translate('General_ColumnNbUniqVisitors').':</b> '
		     . Piwik_Translate('General_ColumnNbUniqVisitorsDocumentation').'<br />'
		     
		     . '<b>'.Piwik_Translate('General_ColumnNbActions').':</b> '
		     . Piwik_Translate('General_ColumnNbActionsDocumentation').'<br />'
		     
		     . '<b>'.Piwik_Translate('General_ColumnActionsPerVisit').':</b> '
		     . Piwik_Translate('General_ColumnActionsPerVisitDocumentation');
		
		$view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns, 
							$selectableColumns = array('VisitsSummary.*', 'Actions.*'), $doc);
		
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
		$view->urlSparklineNbVisits 		= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => $view->displayUniqueVisitors ? array('VisitsSummary.nb_visits', 'VisitsSummary.nb_uniq_visitors') : array('VisitsSummary.nb_visits')));
		$view->urlSparklineNbPageviews 		= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('Actions.nb_pageviews', 'Actions.nb_uniq_pageviews')));
		$view->urlSparklineNbDownloads 	    = $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('Actions.nb_downloads', 'Actions.nb_uniq_downloads')));
		$view->urlSparklineNbOutlinks 		= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('Actions.nb_outlinks', 'Actions.nb_uniq_outlinks')));
		$view->urlSparklineAvgVisitDuration = $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('VisitsSummary.avg_time_on_site')));
		$view->urlSparklineMaxActions 		= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('VisitsSummary.max_actions')));
		$view->urlSparklineActionsPerVisit 	= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('VisitsSummary.nb_actions_per_visit')));
		$view->urlSparklineBounceRate 		= $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('VisitsSummary.bounce_rate')));
		
		$dataTableVisit = self::getVisitsSummary();
		$dataRow = $dataTableVisit->getFirstRow();
		
		$dataTableActions = Piwik_Actions_API::getInstance()->get(Piwik_Common::getRequestVar('idSite'), Piwik_Common::getRequestVar('period'), Piwik_Common::getRequestVar('date'));
		$dataActionsRow = $dataTableActions->getFirstRow();
		
		$view->nbUniqVisitors = $dataRow->getColumn('nb_uniq_visitors');
		$nbVisits = $dataRow->getColumn('nb_visits');
		$view->nbVisits = $nbVisits;
		$view->nbPageviews = $dataActionsRow->getColumn('nb_pageviews');
		$view->nbUniquePageviews = $dataActionsRow->getColumn('nb_uniq_pageviews');
		$view->nbDownloads = $dataActionsRow->getColumn('nb_downloads');
		$view->nbUniqueDownloads = $dataActionsRow->getColumn('nb_uniq_downloads');
		$view->nbOutlinks = $dataActionsRow->getColumn('nb_outlinks');
		$view->nbUniqueOutlinks = $dataActionsRow->getColumn('nb_uniq_outlinks');
		$view->averageVisitDuration = $dataRow->getColumn('avg_time_on_site');
		$nbBouncedVisits = $dataRow->getColumn('bounce_count');
		$view->bounceRate = Piwik::getPercentageSafe($nbBouncedVisits, $nbVisits);
		$view->maxActions = $dataRow->getColumn('max_actions');
		$view->nbActionsPerVisit = $dataRow->getColumn('nb_actions_per_visit');
		
		// backward compatibility:
		// show actions if the finer metrics are not archived
		$view->showOnlyActions = false;
		if (  $dataActionsRow->getColumn('nb_pageviews') 
			+ $dataActionsRow->getColumn('nb_downloads')
			+ $dataActionsRow->getColumn('nb_outlinks') == 0 
			&& $dataRow->getColumn('nb_actions') > 0)
		{
			$view->showOnlyActions = true;
			$view->nbActions = $dataRow->getColumn('nb_actions');
			$view->urlSparklineNbActions = $this->getUrlSparkline( 'getEvolutionGraph', array('columns' => array('nb_actions')));
		}
	}
}
