<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Transitions
 */

/**
 * @package Piwik_Transitions
 */
class Piwik_Transitions_API
{
	
	static private $instance = null;
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * This method combines various reports (both from this and from other plugins) and
	 * returns a complete report. The report is used in the Transitions API to load all
	 * data at once.
	 */
	public function getFullReport($pageUrl, $idSite, $period, $date, $segment = false, $limitBeforeGrouping = false)
	{
		Piwik::checkUserHasViewAccess($idSite);
		
		$pageUrl = Piwik_Common::unsanitizeInputValue($pageUrl);
		
		$report = array(
			'generalText' => '',
		);
		
		$this->addLiveTransitionsDataToReport($report, $pageUrl, $idSite, $period, $date, $segment, $limitBeforeGrouping);
		
		// replace column names in the data tables
		$columnNames = array(
			'label' => 'url',
			Piwik_Archive::INDEX_NB_ACTIONS => 'referrals'
		);
		$reportNames = array('previousPages', 'followingPages', 'outlinks', 'downloads');
		foreach ($reportNames as $reportName)
		{
			if (isset($report[$reportName]))
			{
				$report[$reportName]->filter('ReplaceColumnNames', array($columnNames));
			}
		}
		
		// add general text
		$allPageviews = Piwik_Actions_API::getInstance()->get($idSite, $period, $date, $segment, 'nb_pageviews');
		$allPageviewsInt = intval($allPageviews->getFirstRow()->getColumn('nb_pageviews'));
		$pageviews = $report['pageMetrics']['pageviews'];
		$pageviewsShare = round($pageviews / $allPageviewsInt * 100, 1).'%';
		
		$prettyDate = Piwik_Period_Day::advancedFactory($period, $date)->getLocalizedShortString();
		
		$report['generalText'] = htmlentities(Piwik_Translate('Transitions_ShareOfAllPageviews', array($pageviews, $pageviewsShare)))
				. '<br /><i>' . htmlentities(Piwik_Translate('General_DateRange').' '.$prettyDate) . '</i>';
		
		return $report;
	}

	/**
	 * Add transitions data to the report.
	 * Fake ArchiveProcessing to do the queries live.
	 */
	private function addLiveTransitionsDataToReport(&$report, $pageUrl, $idSite, $period, $date,
				$segment, $limitBeforeGrouping)
	{
		// get idaction of page url
		$actionsPlugin = new Piwik_Actions;
		$idaction = $actionsPlugin->getIdActionFromSegment($pageUrl, 'idaction');
		
		// prepare archive processing that can be reused by the archiving code
		$archiveProcessing = new Piwik_ArchiveProcessing_Day();
		$archiveProcessing->setSite(new Piwik_Site($idSite));
		$archiveProcessing->setPeriod(Piwik_Period::advancedFactory($period, $date));
		$archiveProcessing->setSegment(new Piwik_Segment($segment, $idSite));
		$archiveProcessing->initForLiveUsage();
		
		// launch the archiving code - but live
		$transitionsArchiving = new Piwik_Transitions;
		
		$data = $transitionsArchiving->queryInternalReferrers($idaction, $archiveProcessing, $limitBeforeGrouping);
		
		if ($data['pageviews'] == 0) {
			throw new Exception('NoDataForUrl');
		}
		
		$report['previousPages'] = &$data['previousPages'];
		$report['pageMetrics']['loops'] = $data['loops'];
		$report['pageMetrics']['pageviews'] = $data['pageviews'];
		
		$data = $transitionsArchiving->queryFollowingActions($idaction, $archiveProcessing, $limitBeforeGrouping);
		foreach ($data as $tableName => $table) {
			$report[$tableName] = $table;
		}
		
		$data = $transitionsArchiving->queryExternalReferrers($idaction, $archiveProcessing, $limitBeforeGrouping);
		
		$report['pageMetrics']['entries'] = 0;
		$report['referrers'] = array();
		foreach ($data->getRows() as $row)
		{
			$referrerId = $row->getColumn('label');
			$visits = $row->getColumn(Piwik_Archive::INDEX_NB_VISITS);
			if ($visits)
			{
				// load details (i.e. subtables)
				$details = array();
				if ($idSubTable = $row->getIdSubDataTable())
				{
					$subTable = Piwik_DataTable_Manager::getInstance()->getTable($idSubTable);
					foreach ($subTable->getRows() as $subRow)
					{
						$details[] = array(
							'label' => $subRow->getColumn('label'),
							'referrals' => $subRow->getColumn(Piwik_Archive::INDEX_NB_VISITS)
						);
					}
				}
				$report['referrers'][] = array(
					'label' => $this->getReferrerLabel($referrerId),
					'shortName' => Piwik_getRefererTypeFromShortName($referrerId),
					'visits' => $visits,
					'details' => $details
				);
				$report['pageMetrics']['entries'] += $visits;
			}
		}
		
		// derive the number of exits from the other metrics
		$report['pageMetrics']['exits'] = $report['pageMetrics']['pageviews']
				- $transitionsArchiving->getTotalTransitionsToFollowingActions()
				- $report['pageMetrics']['loops'];
		
		// if there's no data for referrers, Piwik_API_ResponseBuilder::handleMultiDimensionalArray
		// does not detect the multi dimensional array and the data is rendered differently, which 
		// causes an exception.
		if (count($report['referrers']) == 0)
		{
			$report['referrers'][] = array(
				'label' => $this->getReferrerLabel(Piwik_Common::REFERER_TYPE_DIRECT_ENTRY),
				'shortName' => Piwik_getRefererTypeLabel(Piwik_Common::REFERER_TYPE_DIRECT_ENTRY),
				'visits' => 0
			);
		}
	}
	
	private function getReferrerLabel($referrerId) {
		switch ($referrerId)
		{
			case Piwik_Common::REFERER_TYPE_DIRECT_ENTRY:
				return Piwik_Transitions_Controller::getTranslation('directEntries');
			case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
				return Piwik_Transitions_Controller::getTranslation('fromSearchEngines');
			case Piwik_Common::REFERER_TYPE_WEBSITE:
				return Piwik_Transitions_Controller::getTranslation('fromWebsites');
			case Piwik_Common::REFERER_TYPE_CAMPAIGN:
				return Piwik_Transitions_Controller::getTranslation('fromCampaigns');
			default:
				return Piwik_Translate('General_Others');
		}
	}
	
}