<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Live
 */

/**
 * @package Piwik_Live
 */
class Piwik_Live_Controller extends Piwik_Controller
{
	function index($fetch = false)
	{
		return $this->widget($fetch);
	}

	public function widget($fetch = false)
	{
		$view = Piwik_View::factory('index');
		$view->idSite = $this->idSite;
		$view = $this->setCounters($view);
		$view->liveRefreshAfterMs = (int)Piwik_Config::getInstance()->General['live_widget_refresh_after_seconds'] * 1000;
		$view->visitors = $this->getLastVisitsStart($fetchPlease = true);
		$view->liveTokenAuth = Piwik::getCurrentUserTokenAuth();
		return $this->render($view, $fetch);
	}

	public function ajaxTotalVisitors($fetch = false)
	{
		$view = Piwik_View::factory('totalVisits');
		$view = $this->setCounters($view);
		$view->idSite = $this->idSite;
		return $this->render($view, $fetch);
	}
	
	private function render($view, $fetch)
	{
		$rendered = $view->render();
		if($fetch) {
			return $rendered;
		}
		echo $rendered;
	}
	
	public function getVisitorLog($fetch = false)
	{
		// If previous=1 is set, user clicked previous
		// we can't deal with previous so we force display of the first page
		if(Piwik_Common::getRequestVar('previous', 0, 'int') == 1) {
			$_GET['maxIdVisit'] = '';
		}
		
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName,
							__FUNCTION__,
						'Live.getLastVisitsDetails'
						);
		$view->disableGenericFilters();
		$view->disableSort();
		$view->setTemplate("Live/templates/visitorLog.tpl");
		$view->setSortedColumn('idVisit', 'ASC');
		$view->disableSearchBox();
		$view->setLimit(20);
		$view->disableOffsetInformation();
		$view->disableExcludeLowPopulation();
		
		// disable the tag cloud,  pie charts, bar chart icons
		$view->disableShowAllViewsIcons();
		// disable the button "show more datas"
		$view->disableShowAllColumns();
		// disable the RSS feed
		$view->disableShowExportAsRssFeed();
		
		// disable all row actions
		if ($view instanceof Piwik_ViewDataTable_HtmlTable)
		{
			$view->disableRowActions();
		}
		
		$view->setReportDocumentation(Piwik_Translate('Live_VisitorLogDocumentation', array('<br />', '<br />')));
		$view->setCustomParameter('dataTablePreviousIsFirst', 1);
		$view->setCustomParameter('filterEcommerce', Piwik_Common::getRequestVar('filterEcommerce', 0, 'int'));
		$view->setCustomParameter('pageUrlNotDefined', Piwik_Translate('General_NotDefined', Piwik_Translate('Actions_ColumnPageURL')));
		return $this->renderView($view, $fetch);
	}

	public function getLastVisitsStart($fetch = false)
	{
		// hack, ensure we load today's visits by default
		$_GET['date'] = 'today';
		$_GET['period'] = 'day';
		$view = Piwik_View::factory('lastVisits');
		$view->idSite = $this->idSite;

		$api = new Piwik_API_Request("method=Live.getLastVisitsDetails&idSite=$this->idSite&filter_limit=10&format=php&serialize=0&disable_generic_filters=1");
		$visitors = $api->process();
		$view->visitors = $visitors;

		return $this->render($view, $fetch);
	}
	
	private function setCounters($view)
	{
		$segment = Piwik_Common::getRequestVar('segment', false, 'string');
		$last30min = Piwik_Live_API::getInstance()->getCounters($this->idSite, $lastMinutes = 30, $segment);
		$last30min = $last30min[0];
		$today = Piwik_Live_API::getInstance()->getCounters($this->idSite, $lastMinutes = 24*60, $segment);
		$today = $today[0];
		$view->visitorsCountHalfHour = $last30min['visits'];
		$view->visitorsCountToday = $today['visits'];
		$view->pisHalfhour = $last30min['actions'];
		$view->pisToday = $today['actions'];
		return $view;
	}

}
