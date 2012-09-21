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
		
		$report = array();
		$this->addMainPageMetricsToReport($report, $pageUrl, $idSite, $period, $date, $segment);
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
		
		return $report;
	}

	/**
	 * Add the main metrics (pageviews, exits, bounces) to the full report.
	 * Data is loaded from Actions.getPageUrls using the label filter.
	 */
	private function addMainPageMetricsToReport(&$report, $pageUrl, $idSite, $period, $date, $segment)
	{
		$label = Piwik_Actions::getActionExplodedNames($pageUrl, Piwik_Tracker_Action::TYPE_ACTION_URL);
		if (count($label) == 1)
		{
			$label = $label[0];
		}
		else
		{
			$label = array_map('urlencode', $label);
			$label = implode('>', $label);
		}
		
		$parameters = array(
			'method' => 'Actions.getPageUrls',
			'idSite' => $idSite,
			'period' => $period,
			'date' => $date,
			'label' => $label,
			'format' => 'original',
			'serialize' => '0',
			'expanded' => '0'
		);
		if (!empty($segment))
		{
			$parameters['segment'] = $segment;
		}
		
		$url = Piwik_Url::getQueryStringFromParameters($parameters);
		$request = new Piwik_API_Request($url);
		try
		{
			/** @var $dataTable Piwik_DataTable */
			$dataTable = $request->process();
		}
		catch(Exception $e)
		{
			throw new Exception("Actions.getPageUrls returned an error: ".$e->getMessage()."\n");
		}
		
		if ($dataTable->getRowsCount() == 0)
		{
			throw new Exception("The label '$label' could not be found in Actions.getPageUrls\n");
		}
		
		$row = $dataTable->getFirstRow();

        if ($row !== false) {
            $report['pageMetrics'] = array(
                'pageviews' => intval($row->getColumn('nb_hits')),
                'exits'     => intval($row->getColumn('exit_nb_visits')),
                'bounces'   => intval($row->getColumn('entry_bounce_count'))
            );
        } else {
            $report['pageMetrics'] = array(
                'pageviews' => 0,
                'exits'     => 0,
                'bounces'   => 0
            );
        }
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
		$report['previousPages'] = &$data['previousPages'];
		$report['pageMetrics']['loops'] = intval($data['loops']);
		
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