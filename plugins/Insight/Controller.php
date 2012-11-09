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
		$currentUrl = Piwik_Common::unsanitizeInputValue($currentUrl);
		
		$normalizedCurrentUrl = Piwik_Tracker_Action::excludeQueryParametersFromUrl($currentUrl, $idSite);
		$normalizedCurrentUrl = Piwik_Common::unsanitizeInputValue($normalizedCurrentUrl);
		
		// load the appropriate row of the page urls report using the label filter
		Piwik_Actions_ArchivingHelper::reloadConfig();
		$path = Piwik_Actions_ArchivingHelper::getActionExplodedNames($normalizedCurrentUrl, Piwik_Tracker_Action::TYPE_ACTION_URL);
		$path = array_map('urlencode', $path);
		$label = implode('>', $path);
		$request = new Piwik_API_Request(
			'method=Actions.getPageUrls'
			.'&idSite='.urlencode($idSite)
			.'&date='.urlencode($date)
			.'&period='.urlencode($period)
			.'&label='.urlencode($label)
			.'&format=original'
		);
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
		$view->idSite = $idSite;
		$view->period = $period;
		$view->date = $date;
		$view->currentUrl = $currentUrl;
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
		
		$sitesManager = Piwik_SitesManager_API::getInstance();
		$site = $sitesManager->getSiteFromId($idSite);
		$urls = $sitesManager->getSiteUrlsFromId($idSite);
		
		echo '
			<script type="text/javascript">
				function removeUrlPrefix(url) {
					return url.replace(/http(s)?:\/\/(www\.)?/i, "");
				}
				
				function htmlEntities(str) {
				    return String(str).replace(/&/g, \'&amp;\').replace(/</g, \'&lt;\').replace(/>/g, \'&gt;\').replace(/"/g, \'&quot;\');
				}
				
				if (window.location.hash) {
					var match = false;
					
					var urlToRedirect = window.location.hash.substr(1);
					var urlToRedirectWithoutPrefix = removeUrlPrefix(urlToRedirect);
					
					var knownUrls = '.json_encode($urls).';
					for (var i = 0; i < knownUrls.length; i++) {
						var testUrl = removeUrlPrefix(knownUrls[i]);
						if (urlToRedirectWithoutPrefix.substr(0, testUrl.length) == testUrl) {
							match = true;
							window.location.href = urlToRedirect;
							break;
						}
					}
					
					if (!match) {
						var error = "'.htmlentities(Piwik_Translate('Insight_RedirectUrlError')).'";
						error = error.replace(/%s/, htmlEntities(urlToRedirect));
						error = error.replace(/%s/, "<br />");
						document.write(error);
					}
				}
				else {
					window.location.replace("'.$site['main_url'].'");
				};
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
