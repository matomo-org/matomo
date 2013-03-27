<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserSettings
 */

/**
 *
 * @package Piwik_UserSettings
 */
class Piwik_UserSettings_Controller extends Piwik_Controller
{
    /** The set of related reports displayed under the 'Operating Systems' header. */
    private $osRelatedReports = null;

    public function __construct()
    {
        parent::__construct();
        $this->osRelatedReports = array(
            'UserSettings.getOSFamily' => Piwik_Translate('UserSettings_OperatingSystemFamily'),
            'UserSettings.getOS'       => Piwik_Translate('UserSettings_OperatingSystems')
        );
    }

    function index()
    {
        $view = Piwik_View::factory('index');

        $view->dataTablePlugin = $this->getPlugin(true);
        $view->dataTableResolution = $this->getResolution(true);
        $view->dataTableConfiguration = $this->getConfiguration(true);
        $view->dataTableOS = $this->getOS(true);
        $view->dataTableBrowser = $this->getBrowser(true);
        $view->dataTableBrowserType = $this->getBrowserType(true);
        $view->dataTableMobileVsDesktop = $this->getMobileVsDesktop(true);
        $view->dataTableBrowserLanguage = $this->getLanguage(true);

        echo $view->render();
    }

    function getResolution($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
            __FUNCTION__,
            'UserSettings.getResolution'
        );
        $view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnResolution'));
        return $this->renderView($view, $fetch);
    }

    function getConfiguration($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
            __FUNCTION__,
            'UserSettings.getConfiguration'
        );
        $view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnConfiguration'));
        $view->setLimit(3);
        return $this->renderView($view, $fetch);
    }

    function getOS($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
            __FUNCTION__,
            'UserSettings.getOS'
        );
        $view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnOperatingSystem'));
        $view->addRelatedReports(Piwik_Translate('UserSettings_OperatingSystems'), $this->osRelatedReports);
        return $this->renderView($view, $fetch);
    }

    /**
     * Returns or echos a report displaying the number of visits by operating system family.
     */
    public function getOSFamily($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(__FUNCTION__, 'UserSettings.getOSFamily');
        $view->setColumnTranslation('label', Piwik_Translate('UserSettings_OperatingSystemFamily'));
        $view->addRelatedReports(Piwik_Translate('UserSettings_OperatingSystemFamily'), $this->osRelatedReports);
        return $this->renderView($view, $fetch);
    }

    function getBrowserVersion($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
            __FUNCTION__,
            'UserSettings.getBrowserVersion'
        );
        $view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnBrowserVersion'));
        $view->setGraphLimit(7);
        $view->addRelatedReports(Piwik_Translate('UserSettings_ColumnBrowserVersion'), array(
                                                                                            'UserSettings.getBrowser' => Piwik_Translate('UserSettings_Browsers')
                                                                                       ));
        return $this->renderView($view, $fetch);
    }

    /**
     * Returns or echos a report displaying the number of visits by browser type. The browser
     * version is not included in this report.
     */
    public function getBrowser($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(__FUNCTION__, 'UserSettings.getBrowser');
        $view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnBrowser'));
        $view->setGraphLimit(7);
        $view->addRelatedReports(Piwik_Translate('UserSettings_Browsers'), array(
                                                                                'UserSettings.getBrowserVersion' => Piwik_Translate('UserSettings_ColumnBrowserVersion')
                                                                           ));
        return $this->renderView($view, $fetch);
    }

    function getBrowserType($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
            __FUNCTION__,
            'UserSettings.getBrowserType',
            'graphPie'
        );
        $view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnBrowserFamily'));
        $view->disableOffsetInformationAndPaginationControls();
        return $this->renderView($view, $fetch);
    }

    function getWideScreen($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
            __FUNCTION__,
            'UserSettings.getWideScreen'
        );
        $view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnTypeOfScreen'));
        $view->disableOffsetInformationAndPaginationControls();
        $view->addRelatedReports(Piwik_Translate('UserSettings_ColumnTypeOfScreen'), array(
                                                                                          'UserSettings.getMobileVsDesktop' => Piwik_Translate('UserSettings_MobileVsDesktop')
                                                                                     ));
        return $this->renderView($view, $fetch);
    }

    /**
     * Returns or echos a report displaying the number of visits by device type (Mobile or Desktop).
     */
    public function getMobileVsDesktop($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(__FUNCTION__, 'UserSettings.getMobileVsDesktop');
        $view->setColumnTranslation('label', Piwik_Translate('UserSettings_MobileVsDesktop'));
        $view->addRelatedReports(Piwik_Translate('UserSettings_MobileVsDesktop'), array(
                                                                                       'UserSettings.getWideScreen' => Piwik_Translate('UserSettings_ColumnTypeOfScreen')
                                                                                  ));
        return $this->renderView($view, $fetch);
    }

    function getPlugin($fetch = false)
    {
        $view = $this->getStandardDataTableUserSettings(
            __FUNCTION__,
            'UserSettings.getPlugin'
        );
        $view->disableShowAllViewsIcons();
        $view->disableShowAllColumns();
        $view->disableOffsetInformationAndPaginationControls();
        $view->setColumnsToDisplay(array('label', 'nb_visits_percentage', 'nb_visits'));
        $view->setColumnTranslation('label', Piwik_Translate('UserSettings_ColumnPlugin'));
        $view->setColumnTranslation('nb_visits_percentage', str_replace(' ', '&nbsp;', Piwik_Translate('General_ColumnPercentageVisits')));
        $view->setSortedColumn('nb_visits_percentage');
        $view->setLimit(10);
        $view->setFooterMessage(Piwik_Translate('UserSettings_PluginDetectionDoesNotWorkInIE'));
        return $this->renderView($view, $fetch);
    }

    protected function getStandardDataTableUserSettings($currentControllerAction,
                                                        $APItoCall,
                                                        $defaultDatatableType = null)
    {
        $view = Piwik_ViewDataTable::factory($defaultDatatableType);
        $view->init($this->pluginName, $currentControllerAction, $APItoCall);
        $view->disableSearchBox();
        $view->disableExcludeLowPopulation();
        $view->setLimit(5);
        $view->setGraphLimit(5);

        $this->setPeriodVariablesView($view);
        $this->setMetricsVariablesView($view);

        return $view;
    }

    /**
     * Renders datatable for browser language
     *
     * @param bool $fetch
     *
     * @return string|void
     */
    public function getLanguage($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, "UserSettings.getLanguage");
        $view->disableExcludeLowPopulation();

        $view->setColumnsToDisplay(array('label', 'nb_visits'));
        $view->setColumnTranslation('label', Piwik_Translate('General_Language'));
        $view->setSortedColumn('nb_visits');
        $view->disableSearchBox();
        $view->setLimit(5);

        return $this->renderView($view, $fetch);
    }
}