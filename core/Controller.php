<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    /**
     * @var int
     */
    protected $idSite;

    /**
     * @var Piwik_Site
     */
    protected $site = null;

    /**
     * Builds the controller object, reads the date from the request, extracts plugin name from
     */
    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
        $aPluginName = explode('_', get_class($this));
        $this->pluginName = $aPluginName[1];
        $date = Piwik_Common::getRequestVar('date', 'yesterday', 'string');
        try {
            $this->idSite = Piwik_Common::getRequestVar('idSite', false, 'int');
            $this->site = new Piwik_Site($this->idSite);
            $date = $this->getDateParameterInTimezone($date, $this->site->getTimezone());
            $this->setDate($date);
        } catch (Exception $e) {
            // the date looks like YYYY-MM-DD,YYYY-MM-DD or other format
            $this->date = null;
        }
    }

    /**
     * Helper method to convert "today" or "yesterday" to the default timezone specified.
     * If the date is absolute, ie. YYYY-MM-DD, it will not be converted to the timezone
     *
     * @param string $date             today, yesterday, YYYY-MM-DD
     * @param string $defaultTimezone  default timezone to use
     * @return Piwik_Date
     */
    protected function getDateParameterInTimezone($date, $defaultTimezone)
    {
        $timezone = null;
        // if the requested date is not YYYY-MM-DD, we need to ensure
        //  it is relative to the website's timezone
        if (in_array($date, array('today', 'yesterday'))) {
            // today is at midnight; we really want to get the time now, so that
            // * if the website is UTC+12 and it is 5PM now in UTC, the calendar will allow to select the UTC "tomorrow"
            // * if the website is UTC-12 and it is 5AM now in UTC, the calendar will allow to select the UTC "yesterday"
            if ($date == 'today') {
                $date = 'now';
            } elseif ($date == 'yesterday') {
                $date = 'yesterdaySameTime';
            }
            $timezone = $defaultTimezone;
        }
        return Piwik_Date::factory($date, $timezone);
    }

    /**
     * Sets the date to be used by all other methods in the controller.
     * If the date has to be modified, it should be called just after the controller construct
     *
     * @param Piwik_Date $date
     * @return void
     */
    protected function setDate(Piwik_Date $date)
    {
        $this->date = $date;
        $strDate = $this->date->toString();
        $this->strDate = $strDate;
    }

    /**
     * Returns the name of the default method that will be called
     * when visiting: index.php?module=PluginName without the action parameter
     *
     * @return string
     */
    public function getDefaultAction()
    {
        return 'index';
    }

    /**
     * Given an Object implementing Piwik_View_Interface, we either:
     * - echo the output of the rendering if fetch = false
     * - returns the output of the rendering if fetch = true
     *
     * @param Piwik_ViewDataTable $view   view object to use
     * @param bool $fetch  indicates whether to output or return the content
     * @return string|void
     */
    protected function renderView(Piwik_ViewDataTable $view, $fetch = false)
    {
        Piwik_PostEvent('Controller.renderView',
            $this,
            array('view'                                      => $view,
                  'controllerName'                            => $view->getCurrentControllerName(),
                  'controllerAction'                          => $view->getCurrentControllerAction(),
                  'apiMethodToRequestDataTable'               => $view->getApiMethodToRequestDataTable(),
                  'controllerActionCalledWhenRequestSubTable' => $view->getControllerActionCalledWhenRequestSubTable(),
            )
        );

        $view->main();

        $rendered = $view->getView()->render();
        if ($fetch) {
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
        $view->init($currentModuleName, $currentControllerAction, $apiMethod);
        return $view;
    }

    /**
     * This method is similar to self::getLastUnitGraph. It works with API.get to combine metrics
     * of different *.get reports. The returned ViewDataTable is configured with column
     * translations and selectable metrics.
     *
     * @param string $currentModuleName
     * @param string $currentControllerAction
     * @param array $columnsToDisplay
     * @param array $selectableColumns
     * @param bool|string $reportDocumentation
     * @param string $apiMethod The method to request the report from
     *                                (by default, this is API.get but it can be changed for custom stuff)
     * @return Piwik_ViewDataTable_GenerateGraphHTML_ChartEvolution
     */
    protected function getLastUnitGraphAcrossPlugins($currentModuleName, $currentControllerAction,
                                                     $columnsToDisplay, $selectableColumns = array(), $reportDocumentation = false, $apiMethod = 'API.get')
    {
        // back up and manipulate the columns parameter
        $backupColumns = false;
        if (isset($_GET['columns'])) {
            $backupColumns = $_GET['columns'];
        }

        $_GET['columns'] = implode(',', $columnsToDisplay);

        // load translations from meta data
        $idSite = Piwik_Common::getRequestVar('idSite');
        $period = Piwik_Common::getRequestVar('period');
        $date = Piwik_Common::getRequestVar('date');
        $meta = Piwik_API_API::getInstance()->getReportMetadata($idSite, $period, $date);

        $columns = array_merge($columnsToDisplay, $selectableColumns);
        $translations = array();
        foreach ($meta as $reportMeta) {
            if ($reportMeta['action'] == 'get' && !isset($reportMeta['parameters'])) {
                foreach ($columns as $column) {
                    if (isset($reportMeta['metrics'][$column])) {
                        $translations[$column] = $reportMeta['metrics'][$column];
                    }
                }
            }
        }

        // initialize the graph and load the data
        $view = $this->getLastUnitGraph($currentModuleName, $currentControllerAction, $apiMethod);
        $view->setColumnsToDisplay($columnsToDisplay);
        $view->setSelectableColumns($selectableColumns);
        $view->setColumnsTranslations($translations);

        if ($reportDocumentation) {
            $view->setReportDocumentation($reportDocumentation);
        }

        $view->main();

        // restore the columns parameter
        if ($backupColumns !== false) {
            $_GET['columns'] = $backupColumns;
        } else {
            unset($_GET['columns']);
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
     * @param array $paramsToSet  array( 'date' => 'last50', 'viewDataTable' =>'sparkline' )
     * @throws Piwik_Access_NoAccessException
     * @return array
     */
    protected function getGraphParamsModified($paramsToSet = array())
    {
        if (!isset($paramsToSet['period'])) {
            $period = Piwik_Common::getRequestVar('period');
        } else {
            $period = $paramsToSet['period'];
        }
        if ($period == 'range') {
            return $paramsToSet;
        }
        if (!isset($paramsToSet['range'])) {
            $range = 'last30';
        } else {
            $range = $paramsToSet['range'];
        }

        if (!isset($paramsToSet['date'])) {
            $endDate = $this->strDate;
        } else {
            $endDate = $paramsToSet['date'];
        }

        if (is_null($this->site)) {
            throw new Piwik_Access_NoAccessException("Website not initialized, check that you are logged in and/or using the correct token_auth.");
        }
        $paramDate = self::getDateRangeRelativeToEndDate($period, $range, $endDate, $this->site);

        $params = array_merge($paramsToSet, array('date' => $paramDate));
        return $params;
    }

    /**
     * Given for example, $period = month, $lastN = 'last6', $endDate = '2011-07-01',
     * It will return the $date = '2011-01-01,2011-07-01' which is useful to draw graphs for the last N periods
     *
     * @param string $period
     * @param string $lastN
     * @param string $endDate
     * @param Piwik_Site $site
     * @return string
     */
    public static function getDateRangeRelativeToEndDate($period, $lastN, $endDate, $site)
    {
        $last30Relative = new Piwik_Period_Range($period, $lastN, $site->getTimezone());
        $last30Relative->setDefaultEndDate(Piwik_Date::factory($endDate));
        $date = $last30Relative->getDateStart()->toString() . "," . $last30Relative->getDateEnd()->toString();
        return $date;
    }

    /**
     * Returns a numeric value from the API.
     * Works only for API methods that originally returns numeric values (there is no cast here)
     *
     * @param string             $methodToCall  Name of method to call, eg. Referers.getNumberOfDistinctSearchEngines
     * @param bool|string        $date          A custom date to use when getting the value. If false, the 'date' query
     *                                          parameter is used.
     *
     * @return int|float
     */
    protected function getNumericValue($methodToCall, $date = false)
    {
        $params = $date === false ? array() : array('date' => $date);

        $return = Piwik_API_Request::processRequest($methodToCall, $params);
        $columns = $return->getFirstRow()->getColumns();
        return reset($columns);
    }

    /**
     * Returns the current URL to use in a img src=X to display a sparkline.
     * $action must be the name of a Controller method that requests data using the Piwik_ViewDataTable::factory
     * It will automatically build a sparkline by setting the viewDataTable=sparkline parameter in the URL.
     * It will also computes automatically the 'date' for the 'last30' days/weeks/etc.
     *
     * @param string $action            Method name of the controller to call in the img src
     * @param array $customParameters  Array of name => value of parameters to set in the generated GET url
     * @return string The generated URL
     */
    protected function getUrlSparkline($action, $customParameters = array())
    {
        $params = $this->getGraphParamsModified(
            array('viewDataTable' => 'sparkline',
                  'action'        => $action,
                  'module'        => $this->pluginName)
                + $customParameters
        );
        // convert array values to comma separated
        foreach ($params as &$value) {
            if (is_array($value)) {
                $value = rawurlencode(implode(',', $value));
            }
        }
        $url = Piwik_Url::getCurrentQueryStringWithParametersModified($params);
        return $url;
    }

    /**
     * Sets the first date available in the calendar
     *
     * @param Piwik_Date $minDate
     * @param Piwik_View $view
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
     *
     * @param Piwik_Date $maxDate
     * @param Piwik_View $view
     * @return void
     */
    protected function setMaxDateView(Piwik_Date $maxDate, $view)
    {
        $view->maxDateYear = $maxDate->toString('Y');
        $view->maxDateMonth = $maxDate->toString('m');
        $view->maxDateDay = $maxDate->toString('d');
    }

    /**
     * Sets general variables to the view that are used by
     * various templates and Javascript.
     * If any error happens, displays the login screen
     *
     * @param Piwik_View $view
     * @throws Exception
     * @return void
     */
    protected function setGeneralVariablesView($view)
    {
        $view->date = $this->strDate;

        try {
            $view->idSite = $this->idSite;
            if (empty($this->site) || empty($this->idSite)) {
                throw new Exception("The requested website idSite is not found in the request, or is invalid.
				Please check that you are logged in Piwik and have permission to access the specified website.");
            }
            $this->setPeriodVariablesView($view);

            $rawDate = Piwik_Common::getRequestVar('date');
            $periodStr = Piwik_Common::getRequestVar('period');
            if ($periodStr != 'range') {
                $date = Piwik_Date::factory($this->strDate);
                $period = Piwik_Period::factory($periodStr, $date);
            } else {
                $period = new Piwik_Period_Range($periodStr, $rawDate, $this->site->getTimezone());
            }
            $view->rawDate = $rawDate;
            $view->prettyDate = self::getCalendarPrettyDate($period);

            $view->siteName = $this->site->getName();
            $view->siteMainUrl = $this->site->getMainUrl();

            $datetimeMinDate = $this->site->getCreationDate()->getDatetime();
            $minDate = Piwik_Date::factory($datetimeMinDate, $this->site->getTimezone());
            $this->setMinDateView($minDate, $view);

            $maxDate = Piwik_Date::factory('now', $this->site->getTimezone());
            $this->setMaxDateView($maxDate, $view);

            // Setting current period start & end dates, for pre-setting the calendar when "Date Range" is selected
            $dateStart = $period->getDateStart();
            if ($dateStart->isEarlier($minDate)) {
                $dateStart = $minDate;
            }
            $dateEnd = $period->getDateEnd();
            if ($dateEnd->isLater($maxDate)) {
                $dateEnd = $maxDate;
            }

            $view->startDate = $dateStart;
            $view->endDate = $dateEnd;

            $language = Piwik_LanguagesManager::getLanguageForSession();
            $view->language = !empty($language) ? $language : Piwik_LanguagesManager::getLanguageCodeForCurrentUser();

            $view->config_action_url_category_delimiter = Piwik_Config::getInstance()->General['action_url_category_delimiter'];

            $this->setBasicVariablesView($view);

            $view->topMenu = Piwik_GetTopMenu();
        } catch (Exception $e) {
            Piwik_ExitWithMessage($e->getMessage(), '' /* $e->getTraceAsString() */);
        }
    }

    /**
     * Set the minimal variables in the view object
     *
     * @param Piwik_View $view
     */
    protected function setBasicVariablesView($view)
    {
        $view->debugTrackVisitsInsidePiwikUI = Piwik_Config::getInstance()->Debug['track_visits_inside_piwik_ui'];
        $view->isSuperUser = Zend_Registry::get('access')->isSuperUser();
        $view->hasSomeAdminAccess = Piwik::isUserHasSomeAdminAccess();
        $view->isCustomLogo = Piwik_Config::getInstance()->branding['use_custom_logo'];
        $view->logoHeader = Piwik_API_API::getInstance()->getHeaderLogoUrl();
        $view->logoLarge = Piwik_API_API::getInstance()->getLogoUrl();
        $view->logoSVG = Piwik_API_API::getInstance()->getSVGLogoUrl();
        $view->hasSVGLogo = Piwik_API_API::getInstance()->hasSVGLogo();

        $view->enableFrames = Piwik_Config::getInstance()->General['enable_framed_pages']
            || @Piwik_Config::getInstance()->General['enable_framed_logins'];
        if (!$view->enableFrames) {
            $view->setXFrameOptions('sameorigin');
        }

        self::setHostValidationVariablesView($view);
    }

    /**
     * Checks if the current host is valid and sets variables on the given view, including:
     *
     * isValidHost - true if host is valid, false if otherwise
     * invalidHostMessage - message to display if host is invalid (only set if host is invalid)
     * invalidHost - the invalid hostname (only set if host is invalid)
     * mailLinkStart - the open tag of a link to email the super user of this problem (only set
     *                 if host is invalid)
     */
    public static function setHostValidationVariablesView($view)
    {
        // check if host is valid
        $view->isValidHost = Piwik_Url::isValidHost();
        if (!$view->isValidHost) {
            // invalid host, so display warning to user
            $validHost = Piwik_Config::getInstance()->General['trusted_hosts'][0];
            $invalidHost = Piwik_Common::sanitizeInputValue($_SERVER['HTTP_HOST']);

            $emailSubject = rawurlencode(Piwik_Translate('CoreHome_InjectedHostEmailSubject', $invalidHost));
            $emailBody = rawurlencode(Piwik_Translate('CoreHome_InjectedHostEmailBody'));
            $superUserEmail = Piwik::getSuperUserEmail();

            $mailToUrl = "mailto:$superUserEmail?subject=$emailSubject&body=$emailBody";
            $mailLinkStart = "<a href=\"$mailToUrl\">";

            $invalidUrl = Piwik_Url::getCurrentUrlWithoutQueryString($checkIfTrusted = false);
            $validUrl = Piwik_Url::getCurrentScheme() . '://' . $validHost
                . Piwik_Url::getCurrentScriptName();
            $invalidUrl = Piwik_Common::sanitizeInputValue($invalidUrl);
            $validUrl = Piwik_Common::sanitizeInputValue($validUrl);

            $changeTrustedHostsUrl = "index.php"
                . Piwik_Url::getCurrentQueryStringWithParametersModified(array(
                                                                              'module' => 'CoreAdminHome',
                                                                              'action' => 'generalSettings'
                                                                         ))
                . "#trustedHostsSection";

            $warningStart = Piwik_Translate('CoreHome_InjectedHostWarningIntro', array(
                                                                                      '<strong>' . $invalidUrl . '</strong>',
                                                                                      '<strong>' . $validUrl . '</strong>'
                                                                                 )) . ' <br/>';

            if (Piwik::isUserIsSuperUser()) {
                $view->invalidHostMessage = $warningStart . ' '
                    . Piwik_Translate('CoreHome_InjectedHostSuperUserWarning', array(
                                                                                    "<a href=\"$changeTrustedHostsUrl\">",
                                                                                    $invalidHost,
                                                                                    '</a>',
                                                                                    "<br/><a href=\"$validUrl\">",
                                                                                    $validHost,
                                                                                    '</a>'
                                                                               ));
            } else {
                $view->invalidHostMessage = $warningStart . ' '
                    . Piwik_Translate('CoreHome_InjectedHostNonSuperUserWarning', array(
                                                                                       "<br/><a href=\"$validUrl\">",
                                                                                       '</a>',
                                                                                       $mailLinkStart,
                                                                                       '</a>'
                                                                                  ));
            }
            $view->invalidHostMessageHowToFix = '<b>How do I fix this problem and how do I login again?</b><br/> The Piwik Super User can manually edit the file piwik/config/config.ini.php
						and add the following lines: <pre>[General]' . "\n" . 'trusted_hosts[] = "' . $validHost . '"</pre><br/>After making the change, you will be able to login again.<br/><br/>
						You may also <i>disable this security feature (not recommended)</i>. To do so edit config/config.ini.php and add:
						<pre>[General]' . "\n" . 'enable_trusted_host_check=0</pre>';

            $view->invalidHost = $invalidHost; // for UserSettings warning
            $view->invalidHostMailLinkStart = $mailLinkStart;
        }
    }

    /**
     * Sets general period variables (available periods, current period, period labels) used by templates
     *
     * @param Piwik_View $view
     * @throws Exception
     * @return void
     */
    public static function setPeriodVariablesView($view)
    {
        if (isset($view->period)) {
            return;
        }

        $currentPeriod = Piwik_Common::getRequestVar('period');
        $view->displayUniqueVisitors = Piwik::isUniqueVisitorsEnabled($currentPeriod);
        $availablePeriods = array('day', 'week', 'month', 'year', 'range');
        if (!in_array($currentPeriod, $availablePeriods)) {
            throw new Exception("Period must be one of: " . implode(",", $availablePeriods));
        }
        $periodNames = array(
            'day'   => array('singular' => Piwik_Translate('CoreHome_PeriodDay'), 'plural' => Piwik_Translate('CoreHome_PeriodDays')),
            'week'  => array('singular' => Piwik_Translate('CoreHome_PeriodWeek'), 'plural' => Piwik_Translate('CoreHome_PeriodWeeks')),
            'month' => array('singular' => Piwik_Translate('CoreHome_PeriodMonth'), 'plural' => Piwik_Translate('CoreHome_PeriodMonths')),
            'year'  => array('singular' => Piwik_Translate('CoreHome_PeriodYear'), 'plural' => Piwik_Translate('CoreHome_PeriodYears')),
            // Note: plural is not used for date range
            'range' => array('singular' => Piwik_Translate('General_DateRangeInPeriodList'), 'plural' => Piwik_Translate('General_DateRangeInPeriodList')),
        );

        $found = array_search($currentPeriod, $availablePeriods);
        if ($found !== false) {
            unset($availablePeriods[$found]);
        }
        $view->period = $currentPeriod;
        $view->otherPeriods = $availablePeriods;
        $view->periodsNames = $periodNames;
    }

    /**
     * Set metrics variables (displayed metrics, available metrics) used by template
     * Handles the server-side of the metrics picker
     *
     * @param Piwik_View|Piwik_ViewDataTable $view
     * @param string $defaultMetricDay      name of the default metric for period=day
     * @param string $defaultMetric         name of the default metric for other periods
     * @param array $metricsForDay         metrics that are only available for period=day
     * @param array $metricsForAllPeriods  metrics that are available for all periods
     * @param bool $labelDisplayed        add 'label' to columns to display?
     * @return void
     */
    protected function setMetricsVariablesView(Piwik_ViewDataTable $view, $defaultMetricDay = 'nb_uniq_visitors',
                                               $defaultMetric = 'nb_visits', $metricsForDay = array('nb_uniq_visitors'),
                                               $metricsForAllPeriods = array('nb_visits', 'nb_actions'), $labelDisplayed = true)
    {
        // columns is set in the request if metrics picker has been used
        $columns = Piwik_Common::getRequestVar('columns', false);
        if ($columns !== false) {
            $columns = Piwik::getArrayFromApiParameter($columns);
            $firstColumn = $columns[0];
        } else {
            // default columns
            $firstColumn = isset($view->period) && $view->period == 'day' ? $defaultMetricDay : $defaultMetric;
            $columns = array($firstColumn);
        }
        // displayed columns
        if ($labelDisplayed
            && !($view instanceof Piwik_ViewDataTable_GenerateGraphData)
        ) {
            array_unshift($columns, 'label');
        }
        $view->setColumnsToDisplay($columns);


        // Continue only for graphs
        if (!($view instanceof Piwik_ViewDataTable_GenerateGraphData)) {
            return;
        }
        // do not sort if sorted column was initially "label" or eg. it would make "Visits by Server time" not pretty
        if ($view->getSortedColumn() != 'label') {
            $view->setSortedColumn($firstColumn);
        }
        // selectable columns
        if (isset($view->period) && $view->period == 'day') {
            $selectableColumns = array_merge($metricsForDay, $metricsForAllPeriods);
        } else {
            $selectableColumns = $metricsForAllPeriods;
        }
        $view->setSelectableColumns($selectableColumns);
    }

    /**
     * Helper method used to redirect the current http request to another module/action
     * If specified, will also redirect to a given website, period and /or date
     *
     * @param string $moduleToRedirect  Module, eg. "MultiSites"
     * @param string $actionToRedirect  Action, eg. "index"
     * @param string $websiteId         Website ID, eg. 1
     * @param string $defaultPeriod     Default period, eg. "day"
     * @param string $defaultDate       Default date, eg. "today"
     * @param array $parameters        Parameters to append to url
     */
    public function redirectToIndex($moduleToRedirect, $actionToRedirect, $websiteId = null, $defaultPeriod = null, $defaultDate = null, $parameters = array())
    {
        if (is_null($websiteId)) {
            $websiteId = $this->getDefaultWebsiteId();
        }
        if (is_null($defaultDate)) {
            $defaultDate = $this->getDefaultDate();
        }
        if (is_null($defaultPeriod)) {
            $defaultPeriod = $this->getDefaultPeriod();
        }
        $parametersString = '';
        if (!empty($parameters)) {
            $parametersString = '&' . Piwik_Url::getQueryStringFromParameters($parameters);
        }

        if ($websiteId) {
            $url = "Location: index.php?module=" . $moduleToRedirect
                . "&action=" . $actionToRedirect
                . "&idSite=" . $websiteId
                . "&period=" . $defaultPeriod
                . "&date=" . $defaultDate
                . $parametersString;
            header($url);
            exit;
        }

        if (Piwik::isUserIsSuperUser()) {
            Piwik_ExitWithMessage("Error: no website was found in this Piwik installation.
			<br />Check the table '" . Piwik_Common::prefixTable('site') . "' in your database, it should contain your Piwik websites.", false, true);
        }

        $currentLogin = Piwik::getCurrentUserLogin();
        if (!empty($currentLogin)
            && $currentLogin != 'anonymous'
        ) {
            $errorMessage = sprintf(Piwik_Translate('CoreHome_NoPrivilegesAskPiwikAdmin'), $currentLogin, "<br/><a href='mailto:" . Piwik::getSuperUserEmail() . "?subject=Access to Piwik for user $currentLogin'>", "</a>");
            $errorMessage .= "<br /><br />&nbsp;&nbsp;&nbsp;<b><a href='index.php?module=" . Zend_Registry::get('auth')->getName() . "&amp;action=logout'>&rsaquo; " . Piwik_Translate('General_Logout') . "</a></b><br />";
            Piwik_ExitWithMessage($errorMessage, false, true);
        }

        Piwik_FrontController::getInstance()->dispatch(Piwik::getLoginPluginName(), false);
        exit;
    }


    /**
     * Returns default website that Piwik should load
     *
     * @return Piwik_Site
     */
    protected function getDefaultWebsiteId()
    {
        $defaultWebsiteId = false;

        // User preference: default website ID to load
        $defaultReport = Piwik_UsersManager_API::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT);
        if (is_numeric($defaultReport)) {
            $defaultWebsiteId = $defaultReport;
        }

        Piwik_PostEvent('Controller.getDefaultWebsiteId', $defaultWebsiteId);

        if ($defaultWebsiteId) {
            return $defaultWebsiteId;
        }

        $sitesId = Piwik_SitesManager_API::getInstance()->getSitesIdWithAtLeastViewAccess();
        if (!empty($sitesId)) {
            return $sitesId[0];
        }
        return false;
    }

    /**
     * Returns default date for Piwik reports
     *
     * @return string  today, 2010-01-01, etc.
     */
    protected function getDefaultDate()
    {
        // NOTE: a change in this function might mean a change in plugins/UsersManager/templates/userSettings.js as well
        $userSettingsDate = Piwik_UsersManager_API::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT_DATE);
        if ($userSettingsDate == 'yesterday') {
            return $userSettingsDate;
        }
        // if last7, last30, etc.
        if (strpos($userSettingsDate, 'last') === 0
            || strpos($userSettingsDate, 'previous') === 0
        ) {
            return $userSettingsDate;
        }
        return 'today';
    }

    /**
     * Returns default date for Piwik reports
     *
     * @return string  today, 2010-01-01, etc.
     */
    protected function getDefaultPeriod()
    {
        $userSettingsDate = Piwik_UsersManager_API::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT_DATE);
        if ($userSettingsDate === false) {
            return Piwik_Config::getInstance()->General['default_period'];
        }
        if (in_array($userSettingsDate, array('today', 'yesterday'))) {
            return 'day';
        }
        if (strpos($userSettingsDate, 'last') === 0
            || strpos($userSettingsDate, 'previous') === 0
        ) {
            return 'range';
        }
        return $userSettingsDate;
    }

    /**
     * Checks that the specified token matches the current logged in user token.
     * Note: this protection against CSRF should be limited to controller
     * actions that are either invoked via AJAX or redirect to a page
     * within the site.  The token should never appear in the browser's
     * address bar.
     *
     * @throws Piwik_Access_NoAccessException  if token doesn't match
     * @return void
     */
    protected function checkTokenInUrl()
    {
        if (Piwik_Common::getRequestVar('token_auth', false) != Piwik::getCurrentUserTokenAuth()) {
            throw new Piwik_Access_NoAccessException(Piwik_TranslateException('General_ExceptionInvalidToken'));
        }
    }

    /**
     * Returns pretty date for use in period selector widget.
     *
     * @param Piwik_Period $period
     * @return string
     */
    public static function getCalendarPrettyDate($period)
    {
        if ($period instanceof Piwik_Period_Month) // show month name when period is for a month
        {
            return $period->getLocalizedLongString();
        } else {
            return $period->getPrettyString();
        }
    }


    /**
     * Returns the pretty date representation
     *
     * @param $date string
     * @param $period string
     * @return string Pretty date
     */
    public static function getPrettyDate($date, $period)
    {
        return self::getCalendarPrettyDate(Piwik_Period::factory($period, Piwik_Date::factory($date)));
    }


    /**
     * Calculates the evolution from one value to another and returns HTML displaying
     * the evolution percent. The HTML includes an up/down arrow and is colored red, black or
     * green depending on whether the evolution is negative, 0 or positive.
     *
     * No HTML is returned if the current value and evolution percent are both 0.
     *
     * @param string $date The date of the current value.
     * @param int $currentValue The value to calculate evolution to.
     * @param string $pastDate The date of past value.
     * @param int $pastValue The value in the past to calculate evolution from.
     * @return string|false The HTML or false if the evolution is 0 and the current value is 0.
     */
    protected function getEvolutionHtml($date, $currentValue, $pastDate, $pastValue)
    {
        $evolutionPercent = Piwik_DataTable_Filter_CalculateEvolutionFilter::calculate(
            $currentValue, $pastValue, $precision = 1);

        // do not display evolution if evolution percent is 0 and current value is 0
        if ($evolutionPercent == 0
            && $currentValue == 0
        ) {
            return false;
        }

        $titleEvolutionPercent = $evolutionPercent;
        if ($evolutionPercent < 0) {
            $color = "#e02a3b"; //red
            $img = "arrow_down.png";
        } else if ($evolutionPercent == 0) {
            $img = "stop.png";
        } else {
            $color = "green";
            $img = "arrow_up.png";
            $titleEvolutionPercent = '+' . $titleEvolutionPercent;
        }

        $title = Piwik_Translate('General_EvolutionSummaryGeneric', array(
                                                                         Piwik_Translate('General_NVisits', $currentValue),
                                                                         $date,
                                                                         Piwik_Translate('General_NVisits', $pastValue),
                                                                         $pastDate,
                                                                         $titleEvolutionPercent
                                                                    ));

        $result = '<span class="metricEvolution" title="' . $title
            . '"><img style="padding-right:4px" src="plugins/MultiSites/images/' . $img . '"/><strong';

        if (isset($color)) {
            $result .= ' style="color:' . $color . '"';
        }
        $result .= '>' . $evolutionPercent . '</strong></span>';

        return $result;
    }
}
