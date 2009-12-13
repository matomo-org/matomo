<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Live
 */

/**
 *
 * @package Piwik_Live
 */
class Piwik_Live_Controller extends Piwik_Controller
{
	
	function __construct()
	{
		parent::__construct();
		$this->idSite = Piwik_Common::getRequestVar('idSite');
		$this->minIdVisit = Piwik_Common::getRequestVar('minIdVisit', 0, 'int');
	}
		
	function index()
	{
		$this->widget(true);
	}	
	
	public function widget($fetch = false)
	{
		$view = Piwik_View::factory('index');		
		$this->setGeneralVariablesView($view);
		$view->visitorsCountHalfHour = $this->getUsersInLastXMin(30);
		$view->visitorsCountToday = $this->getUsersInLastXDays(1);
		$view->pisHalfhour = $this->getPageImpressionsInLastXMin(30);
		$view->pisToday = $this->getPageImpressionsInLastXDays(1);
		$view->visitors = $this->getLastVisitsStart($fetch = true);

		echo $view->render();		
	}
	
	public function getLastVisitsDetails($fetch = false)
	{
		$view = Piwik_ViewDataTable::factory('');
		$view->init( $this->pluginName,
							__FUNCTION__, 
						'Live.getLastVisitsDetails',
						'getPagesFromVisitId');											
// All colomns in DB which could be shown
//'ip', 'idVisit', 'countActions', 'isVisitorReturning', 'country', 'countryFlag', 'continent', 'provider', 'providerUrl', 'idSite', 
//'serverDate', 'visitLength', 'visitLengthPretty', 'firstActionTimestamp', 'lastActionTimestamp', 'refererType', 'refererName', 	
//'keywords', 'refererUrl', 'searchEngineUrl', 'searchEngineIcon', 'operatingSystem', 'operatingSystemShortName', 'operatingSystemIcon',
//'browserFamily', 'browserFamilyDescription', 'browser', 'browserIcon', 'screen', 'resolution', 'screenIcon', 'plugins', 'lastActionDateTime',
//'serverDatePretty', 'serverTimePretty', 'actionDetails'
		
		$view->setColumnsToDisplay(array(
//			'label',
			'idVisit',
			'serverDatePretty',
			'serverTimePretty',
			'ip',
			'countActions',
			'visitLengthPretty',
			'keywords',
			'refererUrl',
			'operatingSystemShortName',
			'browser',
			'screen',
			'resolution',
			'plugins',
		));

		$view->setColumnsTranslations(array(
//			'label' => Piwik_Translate('translation'),
			'idVisit' => Piwik_Translate(''),
			'serverDatePretty' => Piwik_Translate('Live_Date'),
			'serverTimePretty' => Piwik_Translate('Live_Time'),
			'ip' => 'IP', 
			'countActions' => Piwik_Translate('VisitorInterest_ColumnPagesPerVisit'),
			'visitLengthPretty' => Piwik_Translate('VisitorInterest_ColumnVisitDuration'),
			'keywords' => Piwik_Translate('Referers_ColumnKeyword'),
			'refererUrl' => Piwik_Translate('Live_Referrer_URL'),
			'operatingSystemShortName' => Piwik_Translate('UserSettings_ColumnOperatingSystem'),
			'browser' => Piwik_Translate('UserSettings_ColumnBrowser'),
			'screen' => Piwik_Translate('UserSettings_ColumnTypeOfScreen'),
			'resolution' => Piwik_Translate('UserSettings_ColumnResolution'),
			'plugins' => Piwik_Translate('UserSettings_ColumnPlugin'),
		));

		$view->disableSort();
		$view->setLimit(10);
		$view->disableExcludeLowPopulation();		
		$view->setSortedColumn('idVisit', 'ASC');
		
		return $this->renderView($view, $fetch);
	}

	function getPagesFromVisitId( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory('');
		$view->init( $this->pluginName,
							__FUNCTION__, 
						'Live.getLastVisitsForVisitor',
						'getPagesFromVisitId');	

		#$view->disableSearchBox();
		#$view->disableExcludeLowPopulation();
		#$view->setColumnsToDisplay( array('label','nb_visits') );
		#$view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnKeyword'));

		return $this->renderView($view, $fetch);
	}	

	public function getLastVisitsStart($fetch = false) 
	{
		$view = Piwik_View::factory('lastVisits');
		$view->visitors = $this->getLastVisits(10);
		
		$rendered = $view->render($fetch);
		
		if($fetch)
		{
			return $rendered;
		}
		echo $rendered;		
	}
		
	public function getLastVisits($limit = 10)
	{
		$api = new Piwik_API_Request("method=Live.getLastVisits&idSite=$this->idSite&limit=$limit&minIdVisit=$this->minIdVisit&format=php&serialize=0&disable_generic_filters=1");
		$visitors = $api->process();

		return $visitors;
	}
	
	public function getUsersInLastXMin($minutes = 30) {
		$api = new Piwik_API_Request("method=Live.getUsersInLastXMin&idSite=".$this->idSite."&limit=10000&minIdVisit=".$this->minIdVisit."&minutes=".$minutes."&format=php&serialize=0&disable_generic_filters=1");
		$visitors_halfhour = $api->process();
		
		return count($visitors_halfhour);
	}
	
	public function getUsersInLastXDays($days = 1) {
		$api = new Piwik_API_Request("method=Live.getUsersInLastXDays&idSite=$this->idSite&limit=50000&minIdVisit=$this->minIdVisit&days=$days&format=php&serialize=0&disable_generic_filters=1");
		$visitors_today = $api->process();
		
		return count($visitors_today);
	}
	
	public function getPageImpressionsInLastXMin($minutes = 30) {
		$api = new Piwik_API_Request("method=Live.getPageImpressionsInLastXMin&idSite=$this->idSite&limit=10000&minIdVisit=$this->minIdVisit&minutes=$minutes&format=php&serialize=0&disable_generic_filters=1");
		$pis_halfhour = $api->process();
		
		return count($pis_halfhour);
	}
	
	public function getPageImpressionsInLastXDays($days = 1) {
		$api = new Piwik_API_Request("method=Live.getPageImpressionsInLastXDays&idSite=$this->idSite&limit=50000&minIdVisit=$this->minIdVisit&days=$days&format=php&serialize=0&disable_generic_filters=1");
		$pis_today = $api->process();
		
		return count($pis_today);
	}
	
	public function ajaxTotalVisitors($fetch = false)
	{
		$view = Piwik_View::factory('totalVisits');		
		$this->setGeneralVariablesView($view);
		$view->visitorsCountHalfHour = $this->getUsersInLastXMin(30);
		$view->visitorsCountToday = $this->getUsersInLastXDays(1);
		$view->pisHalfhour = $this->getPageImpressionsInLastXMin(30);
		$view->pisToday = $this->getPageImpressionsInLastXDays(1);
		
		echo $view->render();		
	}	
}
