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
	
	public function getTransitionsForPageTitle($pageTitle, $idSite, $period, $date, $segment = false, $limitBeforeGrouping = false)
	{
		return $this->getTransitionsForAction($pageTitle, 'title', $idSite, $period, $date, $segment = false, $limitBeforeGrouping = false);
	}
	
	public function getTransitionsForPageUrl($pageUrl, $idSite, $period, $date, $segment = false, $limitBeforeGrouping = false)
	{
		return $this->getTransitionsForAction($pageUrl, 'url', $idSite, $period, $date, $segment = false, $limitBeforeGrouping = false);
	}

	/**
	 * General method to get transitions for an action
	 * 
	 * @param $actionName
	 * @param $actionType "url"|"title"
	 * @param $idSite
	 * @param $period
	 * @param $date
	 * @param bool $segment
	 * @param bool $limitBeforeGrouping
	 * @return array
	 * @throws Exception
	 */
	public function getTransitionsForAction($actionName, $actionType,
			$idSite, $period, $date, $segment = false, $limitBeforeGrouping = false)
	{
		Piwik::checkUserHasViewAccess($idSite);
		
		// get idaction of the requested action
		$idaction = $this->deriveIdAction($actionName, $actionType);
		if ($idaction < 0)
		{
			throw new Exception('NoDataForAction');
		}
		
		// prepare archive processing that can be used by the archiving code
		$archiveProcessing = new Piwik_ArchiveProcessing_Day();
		$archiveProcessing->setSite(new Piwik_Site($idSite));
		$archiveProcessing->setPeriod(Piwik_Period::advancedFactory($period, $date));
		$archiveProcessing->setSegment(new Piwik_Segment($segment, $idSite));
		$archiveProcessing->initForLiveUsage();
		
		// prepare the report
		$report = array(
			'date' => Piwik_Period_Day::advancedFactory($period, $date)->getLocalizedShortString()
		);
		
		// add data to the report
		$transitionsArchiving = new Piwik_Transitions;
		$this->addInternalReferrers($transitionsArchiving, $archiveProcessing, $report, $idaction, $actionType, $limitBeforeGrouping);
		$this->addFollowingActions($transitionsArchiving, $archiveProcessing, $report, $idaction, $actionType, $limitBeforeGrouping);
		$this->addExternalReferrers($transitionsArchiving, $archiveProcessing, $report, $idaction, $actionType, $limitBeforeGrouping);
		
		// derive the number of exits from the other metrics
		$report['pageMetrics']['exits'] = $report['pageMetrics']['pageviews']
				- $transitionsArchiving->getTotalTransitionsToFollowingActions()
				- $report['pageMetrics']['loops'];
		
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
	 * Derive the action ID from the request action name and type.
	 */
	private function deriveIdAction($actionName, $actionType)
	{
		$actionsPlugin = new Piwik_Actions;
		$actionName = Piwik_Common::unsanitizeInputValue($actionName);
		switch ($actionType)
		{
			case 'url':
				return $actionsPlugin->getIdActionFromSegment($actionName, 'idaction_url');
			
			case 'title':
				// Transitions is called with Page titles separated by > so we transform back
				$actionName = explode(Piwik_API_DataTableManipulator_LabelFilter::SEPARATOR_RECURSIVE_LABEL, $actionName);
				$actionName = array_map('trim', $actionName );
				$actionName = implode("/", $actionName);
				$id = $actionsPlugin->getIdActionFromSegment($actionName, 'idaction_name');
				
				if ($id < 0)
				{
					$unkown = Piwik_Actions_ArchivingHelper::getUnknownActionName(
								Piwik_Tracker_Action::TYPE_ACTION_NAME);
					
					if (trim($actionName) == trim($unkown))
					{
						$id = $actionsPlugin->getIdActionFromSegment('', 'idaction_name');
					}
				}
				
				return $id;
			
			default:
				throw new Exception('Unknown action type');
		}
	}

	/**
	 * Add the internal referrers to the report:
	 * previous pages
	 * 
	 * @param Piwik_Transitions $transitionsArchiving
	 * @param $archiveProcessing
	 * @param $report
	 * @param $idaction
	 * @param string $actionType
	 * @param $limitBeforeGrouping
	 * @throws Exception
	 */
	private function addInternalReferrers($transitionsArchiving, $archiveProcessing, &$report,
				$idaction, $actionType, $limitBeforeGrouping) {
		
		$data = $transitionsArchiving->queryInternalReferrers(
					$idaction, $actionType, $archiveProcessing, $limitBeforeGrouping);
				
		if ($data['pageviews'] == 0)
		{
			throw new Exception('NoDataForAction');
		}
		
		$report['previousPages'] = &$data['previousPages'];
		$report['pageMetrics']['loops'] = $data['loops'];
		$report['pageMetrics']['pageviews'] = $data['pageviews'];
	}

	/**
	 * Add the following actions to the report:
	 * following pages, downloads, outlinks
	 * 
	 * @param Piwik_Transitions $transitionsArchiving
	 * @param $archiveProcessing
	 * @param $report
	 * @param $idaction
	 * @param string $actionType
	 * @param $limitBeforeGrouping
	 */
	private function addFollowingActions($transitionsArchiving, $archiveProcessing, &$report,
				$idaction, $actionType, $limitBeforeGrouping) {
		
		$data = $transitionsArchiving->queryFollowingActions(
					$idaction, $actionType, $archiveProcessing, $limitBeforeGrouping);
		
		foreach ($data as $tableName => $table)
		{
			$report[$tableName] = $table;
		}
	}

	/**
	 * Add the external referrers to the report:
	 * direct entries, websites, campaigns, search engines
	 * 
	 * @param Piwik_Transitions $transitionsArchiving 
	 * @param $archiveProcessing
	 * @param $report
	 * @param $idaction
	 * @param string $actionType
	 * @param $limitBeforeGrouping
	 */
	private function addExternalReferrers($transitionsArchiving, $archiveProcessing, &$report,
					$idaction, $actionType, $limitBeforeGrouping) {
		
		$data = $transitionsArchiving->queryExternalReferrers(
					$idaction, $actionType, $archiveProcessing, $limitBeforeGrouping);
		
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
	
	public function getTranslations() {
		$controller = new Piwik_Transitions_Controller();
		return $controller->getTranslations();
	}
	
}