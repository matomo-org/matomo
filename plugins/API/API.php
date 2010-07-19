<?php

/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_API
 */

/**
 * 
 * @package Piwik_API
 */
class Piwik_API extends Piwik_Plugin {

	public function getInformation() {
		return array(
			'description' => Piwik_Translate('API_PluginDescription'),
			'homepage' => 'misc/redirectToUrl.php?url=http://dev.piwik.org/trac/wiki/API/Reference',
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}
	
	public function getListHooksRegistered() {
		return array(
			'AssetManager.getCssFiles' => 'getCssFiles',
			'TopMenu.add' => 'addTopMenu',
		);
	}
	
	public function addTopMenu() {
		Piwik_AddTopMenu('General_API', array('module' => 'API', 'action' => 'listAllAPI'), true, 7);
	}

	public function getCssFiles($notification) {
		$cssFiles = &$notification->getNotificationObject();
		
		$cssFiles[] = "plugins/API/templates/styles.css";
	}

}


/**
 * 
 * @package Piwik_API
 */
class Piwik_API_API 
{
	static private $instance = null;

	/**
	 * @return Piwik_API_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}

	public function getDefaultMetrics() 
	{
		$translations = array(
			// Standard metrics
    		'nb_uniq_visitors' => 'General_ColumnNbUniqVisitors',
    		'nb_visits' => 'General_ColumnNbVisits',
    		'nb_actions' => 'General_ColumnNbActions',
			'nb_visits_converted' => 'General_ColumnVisitsWithConversions',
// Do not display these in reports, as they are not so relevant
//    		'max_actions' => 'General_ColumnMaxActions',
//    		'sum_visit_length' => 'General_ColumnSumVisitLength',
//			'bounce_count'
		);
		$translations = array_map('Piwik_Translate', $translations);
		return $translations;
	}

	public function getDefaultProcessedMetrics()
	{
		$translations = array(
			// Processed in AddColumnsWhenShowAllColumns
			'nb_actions_per_visit' => 'General_ColumnActionsPerVisit',
    		'avg_time_on_site' => 'General_ColumnAvgTimeOnSite',
    		'bounce_rate' => 'General_ColumnBounceRate',
		);
		return array_map('Piwik_Translate', $translations);
	}
	
	/**
	 * Triggers a hook to ask plugins for available Reports.
	 *
	 * @param string $idSites Comma separated list of website Ids
	 * @return array
	 */
	public function getReportMetadata($idSites = false) 
	{
		$idSites = Piwik_Site::getIdSitesFromIdSitesString($idSites);

		$availableReports = array();
		Piwik_PostEvent('API.getReportMetadata', $availableReports, $idSites);

		foreach ($availableReports as &$availableReport) {
			if (!isset($availableReport['metrics'])) {
				$availableReport['metrics'] = $this->getDefaultMetrics();
			}
			if (!isset($availableReport['processedMetrics'])) {
				$availableReport['processedMetrics'] = $this->getDefaultProcessedMetrics();
			}
		}
		
		// Some plugins need to add custom metrics after all plugins hooked in
		Piwik_PostEvent('API.getReportMetadata.end', $availableReports, $idSites);
		
		// If a translation is not set for a given column, 
		// Is it a know column?
		$knownMetrics = array_merge( $this->getDefaultMetrics(), $this->getDefaultProcessedMetrics() );
		foreach($availableReports as &$availableReport)
		{
			$metrics = $availableReport['metrics'];
			$cleanedMetrics = array();
			foreach($metrics as $metricId => $metricTranslation)
			{
				// simply the column name was given, ie 
				// 'metric' => array( 'nb_visits' )
				// $metricTranslation is in this case nb_visits
				if(is_numeric($metricId)
					&& isset($knownMetrics[$metricTranslation]))
				{
					$metricId = $metricTranslation;
					$metricTranslation = $knownMetrics[$metricTranslation];
				}
				// else, the column already has a translation set
				
				$cleanedMetrics[$metricId] = $metricTranslation;
			}
			$availableReport['metrics'] = $cleanedMetrics;
		}
		
		// Sort results to ensure consistent order
		usort($availableReports, array($this, "sort"));
		
		return $availableReports;
	}
	
	private function sort($a, $b)
	{
		return ($category = strcmp($a['category'], $b['category'])) != 0 	
				? $category
				: strcmp($a['action'], $b['action']);
	}
}