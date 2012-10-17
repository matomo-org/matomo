<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Insight
 */

class Piwik_Insight_Controller extends Piwik_Controller
{
	
	/** The index of the plugin */
	public function index()
	{
		Piwik::checkUserHasViewAccess($this->idSite);
		
		$view = Piwik_View::factory('index');
		$view->idSite = $this->idSite;
		
		$sitesManager = Piwik_SitesManager_API::getInstance();
		$site = $sitesManager->getSiteFromId($this->idSite);
		
		// TODO: pass the excluded parameters to the client
		// use them in the normalization algorithm for link urls
		$excludedParamteres = $site['excluded_parameters'];
		
		// date
		$view->date = Piwik_Common::getRequestVar('date', 'today');
		$view->period = Piwik_Common::getRequestVar('period', 'day');
		
		echo $view->render();
	}
	
	/** Render the area left of the iframe */
	public function renderSidebar()
	{
		$idSite = Piwik_Common::getRequestVar('idSite');
		$period = Piwik_Common::getRequestVar('period');
		$date = Piwik_Common::getRequestVar('date');
		$currentUrl = Piwik_Common::getRequestVar('currentUrl');
		
		// load the appropriate row of the page urls report using the label filter
		Piwik_Actions_ArchivingHelper::reloadConfig();
		$path = Piwik_Actions_ArchivingHelper::getActionExplodedNames($currentUrl, Piwik_Tracker_Action::TYPE_ACTION_URL);
		$path = array_map('urlencode', $path);
		$label = implode('>', $path);
		$request = new Piwik_API_Request('
			method=Actions.getPageUrls
			&idSite='.urlencode($idSite).'
			&date='.urlencode($date).'
			&period='.urlencode($period).'
			&label='.urlencode($label).'
			&format=original
		');
 		$dataTable = $request->process();
		
		$data = array();
		if ($dataTable->getRowsCount() > 0)
		{
			$row = $dataTable->getFirstRow();
			
			$translations = Piwik_API_API::getDefaultMetricTranslations();
			$showMetrics = array('nb_hits', 'nb_visits', 'nb_uniq_visitors',
					'bounce_rate', 'exit_rate', 'avg_time_on_page');
			
			
			foreach ($showMetrics as $metric)
			{
				$value = $row->getColumn($metric);
				if ($value === false)
				{
					// skip unique visitors for period != day
					continue;
				}
				if ($metric == 'avg_time_on_page')
				{
					$value = Piwik::getPrettyTimeFromSeconds($value);
				}
				$data[] = array(
					'name' => $translations[$metric],
					'value' => $value
				);
			}
		}
		
		// generate page url string
		foreach ($path as &$part)
		{
			$part = preg_replace(';^/;', '', urldecode($part));
		}
		$page = '/'.implode('/', $path);
		$page = preg_replace(';/index$;', '/', $page);
		if ($page == '/')
		{
			$page = '/index';
		}
		
		// render template
		$view = Piwik_View::factory('sidebar');
		$view->data = $data;
		$view->location = $page;
		echo $view->render();
	}
	
	/**
	 * Start an Insight session: Redirect to the tracked website. The Piwik
	 * tracker will recognize this referrer and start the session. 
	 */
	public function startInsightSession()
	{
		$idSite = Piwik_Common::getRequestVar('idsite', 0, 'int');
		Piwik::checkUserHasViewAccess($idSite);
		
		$site = Piwik_SitesManager_API::getInstance()->getSiteFromId($idSite);
		
		echo '
			<script type="text/javascript">
				window.location.href = "'.$site['main_url'].'";
			</script>
		';
	}
	
	/**
	 * This method is used to pass information from the iframe back to Piwik.
	 * Due to the same origin policy, we can't do that directly, so we inject
	 * an additional iframe in the Insight session that calls this controller
	 * method.
	 * The rendered iframe is from the same origin as the Piwik window so we
	 * can bypass the same origin policy and call the parent.
	 */
	public function notifyParentIframe()
	{
		$view = Piwik_View::factory('notify_parent_iframe');
		echo $view->render();
	}
	
}
