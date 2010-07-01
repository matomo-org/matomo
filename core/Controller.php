<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Parent class of all plugins Controllers (located in /plugins/PluginName/Controller.php
 * It defines some helper functions controllers can use.
 * 
 * @package Piwik
 */
abstract class Piwik_Controller
{
	/**
	 * Plugin name, eg. Referers
	 * @var string
	 */
	protected $pluginName;
	
	/**
	 * Date string
	 * 
	 * @var string
	 */
	protected $strDate;
	
	/**
	 * Piwik_Date object or null if the requested date is a range
	 * 
	 * @var Piwik_Date|null 
	 */
	protected $date;
	protected $idSite;
	protected $site = null;
	
	/**
	 * Builds the controller object, reads the date from the request, extracts plugin name from 
	 */
	function __construct()
	{
		$aPluginName = explode('_', get_class($this));
		$this->pluginName = $aPluginName[1];
		$date = Piwik_Common::getRequestVar('date', 'yesterday', 'string');
		try {
			$this->idSite = Piwik_Common::getRequestVar('idSite', false, 'int');
			$this->site = new Piwik_Site($this->idSite);
			$date = $this->getDateParameterInTimezone($date, $this->site->getTimezone());
			$this->setDate($date);
		} catch(Exception $e){
			// the date looks like YYYY-MM-DD,YYYY-MM-DD or other format
			$this->date = null;
		}
	}
	
	/**
	 * Helper method to convert "today" or "yesterday" to the default timezone specified.
	 * If the date is absolute, ie. YYYY-MM-DD, it will not be converted to the timezone
	 * @param $date today, yesterday, YYYY-MM-DD
	 * @param $defaultTimezone
	 * @return Piwik_Date
	 */
	protected function getDateParameterInTimezone($date, $defaultTimezone )
	{
		$timezone = null;
		// if the requested date is not YYYY-MM-DD, we need to ensure
		//  it is relative to the website's timezone
		if(in_array($date, array('today', 'yesterday')))
		{
			// today is at midnight; we really want to get the time now, so that
			// * if the website is UTC+12 and it is 5PM now in UTC, the calendar will allow to select the UTC "tomorrow"
			// * if the website is UTC-12 and it is 5AM now in UTC, the calendar will allow to select the UTC "yesterday" 
			if($date == 'today')
			{
				$date = 'now';
			}
			elseif($date == 'yesterday')
			{
				$date = 'yesterdaySameTime';
			}
			$timezone = $defaultTimezone;
		}
		return Piwik_Date::factory($date, $timezone);
	}

	/**
	 * Sets the date to be used by all other methods in the controller.
	 * If the date has to be modified, it should be called just after the controller construct
	 * @param $date
	 * @return void
	 */
	protected function setDate(Piwik_Date $date)
	{
		$this->date = $date;
		$this->strDate = $this->date->toString();
	}
	
	/**
	 * Returns the name of the default method that will be called 
	 * when visiting: index.php?module=PluginName without the action parameter
	 * 
	 * @return string
	 */
	function getDefaultAction()
	{
		return 'index';
	}

	protected $standardColumnNameToTranslation = array(
		'label' => 'General_ColumnLabel',
		'nb_visits' => 'General_ColumnNbVisits',
		'nb_actions' => 'General_ColumnNbActions',
		'max_actions' => 'General_ColumnMaxActions',
		'sum_visit_length' => 'General_ColumnSumVisitLength',
		'nb_uniq_visitors' => 'General_ColumnNbUniqVisitors',
		'nb_actions_per_visit' => 'General_ColumnActionsPerVisit',
		'avg_time_on_site' => 'General_ColumnAvgTimeOnSite',
		'bounce_rate' => 'General_ColumnBounceRate',
		'revenue_per_visit' => 'General_ColumnValuePerVisit',
		'goals_conversion_rate' => 'General_ColumnVisitsWithConversions',
	);

	/**
	 * Given an Object implementing Piwik_iView interface, we either:
	 * - echo the output of the rendering if fetch = false
	 * - returns the output of the rendering if fetch = true
	 *
	 * @param Piwik_ViewDataTable $view
	 * @param bool $fetch
	 * @return string|void
	 */
	protected function renderView( Piwik_ViewDataTable $view, $fetch = false)
	{
		Piwik_PostEvent(	'Controller.renderView', 
							$this, 
							array(	'view' => $view,
									'controllerName' => $view->getCurrentControllerName(),
									'controllerAction' => $view->getCurrentControllerAction(),
									'apiMethodToRequestDataTable' => $view->getApiMethodToRequestDataTable(),
									'controllerActionCalledWhenRequestSubTable' => $view->getControllerActionCalledWhenRequestSubTable(),
							)
				);

		$standardColumnNameToTranslation = array_map('Piwik_Translate', $this->standardColumnNameToTranslation);
		$view->setColumnsTranslations($standardColumnNameToTranslation);
		$view->main();
		$rendered = $view->getView()->render();
		if($fetch)
		{
			return $rendered;
		}
		echo $rendered;
	}
	
	/**
	 * Returns a ViewDataTable object of an Evolution graph 
	 * for the last30 days/weeks/etc. of the current period, relative to the current date.
	 *
	 * @param string $currentModuleName
	 * @param string $currentControllerAction
	 * @param string $apiMethod
	 * @return Piwik_ViewDataTable_GenerateGraphHTML_ChartEvolution
	 */
	protected function getLastUnitGraph($currentModuleName, $currentControllerAction, $apiMethod)
	{
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $currentModuleName, $currentControllerAction, $apiMethod );
		
		// if the date is not yet a nicely formatted date range ie. YYYY-MM-DD,YYYY-MM-DD we build it
		// otherwise the current controller action is being called with the good date format already so it's fine
		// see constructor
		if( !is_null($this->date))
		{
			$view->setParametersToModify( 
				$this->getGraphParamsModified( array('date' => $this->strDate))
				);
		}
		
		return $view;
	}

	/**
	 * Returns the array of new processed parameters once the parameters are applied.
	 * For example: if you set range=last30 and date=2008-03-10, 
	 *  the date element of the returned array will be "2008-02-10,2008-03-10"
	 * 
	 * Parameters you can set:
	 * - range: last30, previous10, etc.
	 * - date: YYYY-MM-DD, today, yesterday
	 * - period: day, week, month, year
	 * 
	 * @param array  paramsToSet = array( 'date' => 'last50', 'viewDataTable' =>'sparkline' )
	 */
	protected function getGraphParamsModified($paramsToSet = array())
	{
		if(!isset($paramsToSet['range']))
		{
			$range = 'last30';
		}
		else
		{
			$range = $paramsToSet['range'];
		}
		
		if(!isset($paramsToSet['date']))
		{
			$endDate = $this->strDate;
		}
		else
		{
			$endDate = $paramsToSet['date'];
		}
		
		if(!isset($paramsToSet['period']))
		{
			$period = Piwik_Common::getRequestVar('period');
		}
		else
		{
			$period = $paramsToSet['period'];
		}
		$last30Relative = new Piwik_Period_Range($period, $range, $this->site->getTimezone() );
		
		$last30Relative->setDefaultEndDate(Piwik_Date::factory($endDate));
		
		$paramDate = $last30Relative->getDateStart()->toString() . "," . $last30Relative->getDateEnd()->toString();
		
		$params = array_merge($paramsToSet , array(	'date' => $paramDate ) );
		return $params;
	}
	
	/**
	 * Returns a numeric value from the API.
	 * Works only for API methods that originally returns numeric values (there is no cast here)
	 *
	 * @param string $methodToCall, eg. Referers.getNumberOfDistinctSearchEngines
	 * @return int|float
	 */
	protected function getNumericValue( $methodToCall )
	{
		$requestString = 'method='.$methodToCall.'&format=original';
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}

	/**
	 * Returns the current URL to use in a img src=X to display a sparkline.
	 * $action must be the name of a Controller method that requests data using the Piwik_ViewDataTable::factory
	 * It will automatically build a sparkline by setting the viewDataTable=sparkline parameter in the URL.
	 * It will also computes automatically the 'date' for the 'last30' days/weeks/etc. 
	 *
	 * @param string $action, eg. method name of the controller to call in the img src
	 * @param array array of name => value of parameters to set in the generated GET url 
	 * @return string the generated URL
	 */
	protected function getUrlSparkline( $action, $customParameters = array() )
	{
		$params = $this->getGraphParamsModified( 
					array(	'viewDataTable' => 'sparkline', 
							'action' => $action,
							'module' => $this->pluginName)
					+ $customParameters
				);
		$url = Piwik_Url::getCurrentQueryStringWithParametersModified($params);
		return $url;
	}
	
	/**
	 * Sets the first date available in the calendar
	 * @param $minDate
	 * @param $view
	 * @return void
	 */
	protected function setMinDateView(Piwik_Date $minDate, $view)
	{
		$view->minDateYear = $minDate->toString('Y');
		$view->minDateMonth = $minDate->toString('m');
		$view->minDateDay = $minDate->toString('d');
	}
	
	/**
	 * Sets "today" in the calendar. Today does not always mean "UTC" today, eg. for websites in UTC+12.
	 * @param $maxDate
	 * @param $view
	 * @return void
	 */
	protected function setMaxDateView(Piwik_Date $maxDate, $view)
	{
		$view->maxDateYear = $maxDate->toString('Y');
		$view->maxDateMonth = $maxDate->toString('m');
		$view->maxDateDay = $maxDate->toString('d');
	}
	
	/**
	 * Sets general variables to the view that are used by various templates and Javascript
	 * @param $view
	 * @return void
	 */
	protected function setGeneralVariablesView($view)
	{
		$view->date = $this->strDate;
		
		try {
			$this->setPeriodVariablesView($view);
			$period = Piwik_Period::factory(Piwik_Common::getRequestVar('period'), Piwik_Date::factory($this->strDate));
			$view->prettyDate = $period->getLocalizedLongString();
			$view->idSite = $this->idSite;
			if(is_null($this->site))
			{
				throw new Exception("invalid website");
			}
			$view->siteName = $this->site->getName();
			$view->siteMainUrl = $this->site->getMainUrl();
			
			$datetimeMinDate = $this->site->getCreationDate()->getDatetime();
			$minDate = Piwik_Date::factory($datetimeMinDate, $this->site->getTimezone());
			$this->setMinDateView($minDate, $view);

			$maxDate = Piwik_Date::factory('now', $this->site->getTimezone());
			$this->setMaxDateView($maxDate, $view);

			$view->topMenu = Piwik_GetTopMenu();
			$view->currentAdminMenuName = Piwik_GetCurrentAdminMenuName();
			$view->debugTrackVisitsInsidePiwikUI = Zend_Registry::get('config')->Debug->track_visits_inside_piwik_ui;

			$view->isSuperUser = Zend_Registry::get('access')->isSuperUser();
		} catch(Exception $e) {
			self::redirectToIndex(Piwik::getModule(), Piwik::getAction());
		}
	}
	
	/**
	 * Sets general period variables (available periods, current period, period labels) used by templates 
	 * @param $view
	 * @return void
	 */
	public static function setPeriodVariablesView($view)
	{
		if(isset($view->period))
		{
			return;
		}
		$currentPeriod = Piwik_Common::getRequestVar('period');
		$availablePeriods = array('day', 'week', 'month', 'year');
		if(!in_array($currentPeriod,$availablePeriods))
		{
			throw new Exception("Period must be one of: ".implode(",",$availablePeriods));
		}
		$periodNames = array(
			'day' => array('singular' => Piwik_Translate('CoreHome_PeriodDay'), 'plural' => Piwik_Translate('CoreHome_PeriodDays')),
			'week' => array('singular' => Piwik_Translate('CoreHome_PeriodWeek'), 'plural' => Piwik_Translate('CoreHome_PeriodWeeks')),
			'month' => array('singular' => Piwik_Translate('CoreHome_PeriodMonth'), 'plural' => Piwik_Translate('CoreHome_PeriodMonths')),
			'year' => array('singular' => Piwik_Translate('CoreHome_PeriodYear'), 'plural' => Piwik_Translate('CoreHome_PeriodYears')),
		);
		
		$found = array_search($currentPeriod,$availablePeriods);
		if($found !== false)
		{
			unset($availablePeriods[$found]);
		}
		$view->period = $currentPeriod;
		$view->otherPeriods = $availablePeriods;
		$view->periodsNames = $periodNames;
	}
	
	/**
	 * Helper method used to redirect the current http request to another module/action
	 * If specified, will also redirect to a given website, period and /or date
	 * 
	 * @param $moduleToRedirect eg. "MultiSites"
	 * @param $actionToRedirect eg. "index"
	 * @param $websiteId eg. 1
	 * @param $defaultPeriod eg. "day"
	 * @param $defaultDate eg. "today"
	 * @return issues a http header redirect and exits
	 */
	function redirectToIndex($moduleToRedirect, $actionToRedirect, $websiteId = null, $defaultPeriod = null, $defaultDate = null)
	{
		if(is_null($websiteId))
		{
			$websiteId = $this->getDefaultWebsiteId();
		}
		if(is_null($defaultDate))
		{
			$defaultDate = $this->getDefaultDate();
		}
		if(is_null($defaultPeriod))
		{
			$defaultPeriod = $this->getDefaultPeriod();
		}

		if($websiteId) {
			header("Location:index.php?module=".$moduleToRedirect
									."&action=".$actionToRedirect
									."&idSite=".$websiteId
									."&period=".$defaultPeriod
									."&date=".$defaultDate);
			exit;
		}
		
		if(Piwik::isUserIsSuperUser())
		{
			Piwik_ExitWithMessage("Error: no website were found in this Piwik installation. 
			<br />Check the table '". Piwik_Common::prefixTable('site') ."' that should contain your Piwik websites.", false, true);
		}
		
		$currentLogin = Piwik::getCurrentUserLogin();
		if(!empty($currentLogin)
			&& $currentLogin != 'anonymous')
		{
			$errorMessage = sprintf(Piwik_Translate('CoreHome_NoPrivileges'),$currentLogin);
			$errorMessage .= "<br /><br />&nbsp;&nbsp;&nbsp;<b><a href='index.php?module=". Zend_Registry::get('auth')->getName() ."&amp;action=logout'>&rsaquo; ". Piwik_Translate('General_Logout'). "</a></b><br />";
			Piwik_ExitWithMessage($errorMessage, false, true);
		}

		Piwik_FrontController::dispatch(Piwik::getLoginPluginName(), false);
		exit;
	}
	

	/**
	 * Returns default website that Piwik should load 
	 * @return Piwik_Site
	 */
	protected function getDefaultWebsiteId()
	{
		$defaultWebsiteId = false;
	
		// User preference: default website ID to load
		$defaultReport = Piwik_UsersManager_API::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT);
		if(is_numeric($defaultReport)) 
		{
			$defaultWebsiteId = $defaultReport;
		}
		
		Piwik_PostEvent( 'Controller.getDefaultWebsiteId', $defaultWebsiteId );
		
		if($defaultWebsiteId) 
		{
			return $defaultWebsiteId;
		}
		
		$sitesId = Piwik_SitesManager_API::getInstance()->getSitesIdWithAtLeastViewAccess();
		if(!empty($sitesId))
		{
			return $sitesId[0];
		}
		return false;
	}

	/**
	 * Returns default date for Piwik reports
	 * @return string today, 2010-01-01, etc.
	 */
	protected function getDefaultDate()
	{
		$userSettingsDate = Piwik_UsersManager_API::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT_DATE);
		if($userSettingsDate === false)
		{
			return Zend_Registry::get('config')->General->default_day;
		}
		if($userSettingsDate == 'yesterday')
		{
			return $userSettingsDate;
		}
		return 'today';
	}
	
	/**
	 * Returns default date for Piwik reports
	 * @return string today, 2010-01-01, etc.
	 */
	protected function getDefaultPeriod()
	{
		$userSettingsDate = Piwik_UsersManager_API::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT_DATE);
		if($userSettingsDate === false)
		{
			return Zend_Registry::get('config')->General->default_period;
		}
		if(in_array($userSettingsDate, array('today','yesterday')))
		{
			return 'day';
		}
		return $userSettingsDate;
	}
	
	/**
	 * Checks that the specified token matches the current logged in user token
	 * Protection against CSRF
	 * 
	 * @return throws exception if token doesn't match
	 */
	protected function checkTokenInUrl()
	{
		if(Piwik_Common::getRequestVar('token_auth', false) != Piwik::getCurrentUserTokenAuth()) {
			throw new Piwik_Access_NoAccessException(Piwik_TranslateException('General_ExceptionInvalidToken'));
		}
	}
}
