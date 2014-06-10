<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;

/**
 *
 */
class UserSettings extends \Piwik\Plugin
{
    /**
     * Mapping between the browser family shortcode and the displayed name
     *
     * @type array
     */
    static public $browserType_display = array(
        'ie'     => 'Trident (IE)',
        'gecko'  => 'Gecko (Firefox)',
        'khtml'  => 'KHTML (Konqueror)',
        'webkit' => 'WebKit (Safari, Chrome)',
        'opera'  => 'Presto (Opera)',
    );

    /**
     * Defines API reports.
     * Also used to define Widgets.
     *
     * @type array
     *
     * Category, Report Name, API Module, API action, Translated column name,
     * $segment, $sqlSegment, $acceptedValues, $sqlFilter
     */
    protected $reportMetadata = array(
        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetResolutions',
              'UserSettings',
              'getResolution',
              'UserSettings_ColumnResolution',
              'resolution',
              'log_visit.config_resolution',
              '1280x1024, 800x600, etc.',
              null),

        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetBrowsers',
              'UserSettings',
              'getBrowser',
              'UserSettings_ColumnBrowser',
              'browserCode',
              'log_visit.config_browser_name',
              'FF, IE, CH, SF, OP, etc.',
              null),

        // browser version
        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetBrowserVersion',
              'UserSettings',
              'getBrowserVersion',
              'UserSettings_ColumnBrowserVersion',
              'browserVersion',
              'log_visit.config_browser_version',
              '1.0, 8.0, etc.',
              null),

        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetBrowserFamilies',
              'UserSettings',
              'getBrowserType',
              'UserSettings_ColumnBrowserFamily',
              null,
              null,
              null,
              null),

        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetPlugins',
              'UserSettings',
              'getPlugin',
              'General_Plugin',
              null,
              null,
              null,
              null),

        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetWidescreen',
              'UserSettings',
              'getWideScreen',
              'UserSettings_ColumnTypeOfScreen',
              null,
              null,
              null,
              null),

        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetOperatingSystems',
              'UserSettings',
              'getOS',
              'UserSettings_ColumnOperatingSystem',
              'operatingSystemCode',
              'log_visit.config_os',
              'WXP, WI7, MAC, LIN, AND, IPD, etc.',
              null),

        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetGlobalVisitors',
              'UserSettings',
              'getConfiguration',
              'UserSettings_ColumnConfiguration',
              null,
              null,
              null,
              null),

        // operating system family
        array('UserSettings_VisitorSettings',
              'UserSettings_OperatingSystemFamily',
              'UserSettings',
              'getOSFamily',
              'UserSettings_OperatingSystemFamily',
              null,
              null,
              null,
              null),

        // device type
        array('UserSettings_VisitorSettings',
              'UserSettings_MobileVsDesktop',
              'UserSettings',
              'getMobileVsDesktop',
              'UserSettings_MobileVsDesktop',
              null,
              null,
              null,
              null),

        // Browser language
        array('UserSettings_VisitorSettings',
              'UserSettings_BrowserLanguage',
              'UserSettings',
              'getLanguage',
              'General_Language',
              null,
              null,
              null,
              null),
    );

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'API.getReportMetadata'           => 'getReportMetadata',
            'API.getSegmentDimensionMetadata' => 'getSegmentsMetadata',
            'ViewDataTable.configure'         => 'configureViewDataTable',
            'ViewDataTable.getDefaultType'    => 'getDefaultTypeViewDataTable'
        );
        return $hooks;
    }

    public function getDefaultTypeViewDataTable(&$defaultViewTypes)
    {
        $defaultViewTypes['UserSettings.getBrowserType'] = Pie::ID;
    }

    public function configureViewDataTable(ViewDataTable $view)
    {
        switch ($view->requestConfig->apiMethodToRequestDataTable) {
            case 'UserSettings.getResolution':
                $this->configureViewForGetResolution($view);
                break;
            case 'UserSettings.getConfiguration':
                $this->configureViewForGetConfiguration($view);
                break;
            case 'UserSettings.getOS':
                $this->configureViewForGetOS($view);
                break;
            case 'UserSettings.getOSFamily':
                $this->configureViewForGetOSFamily($view);
                break;
            case 'UserSettings.getBrowserVersion':
                $this->configureViewForGetBrowserVersion($view);
                break;
            case 'UserSettings.getBrowser':
                $this->configureViewForGetBrowser($view);
                break;
            case 'UserSettings.getBrowserType':
                $this->configureViewForGetBrowserType($view);
                break;
            case 'UserSettings.getWideScreen':
                $this->configureViewForGetWideScreen($view);
                break;
            case 'UserSettings.getMobileVsDesktop':
                $this->configureViewForGetMobileVsDesktop($view);
                break;
            case 'UserSettings.getPlugin':
                $this->configureViewForGetPlugin($view);
                break;
            case 'UserSettings.getLanguage':
                $this->configureViewForGetLanguage($view);
                break;
        }
    }

    private function configureViewForGetResolution(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->addTranslation('label', Piwik::translate('UserSettings_ColumnResolution'));
    }

    private function configureViewForGetConfiguration(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->addTranslation('label', Piwik::translate('UserSettings_ColumnConfiguration'));

        $view->requestConfig->filter_limit = 3;
    }

    private function configureViewForGetOS(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->title = Piwik::translate('UserSettings_OperatingSystems');
        $view->config->addTranslation('label', Piwik::translate('UserSettings_ColumnOperatingSystem'));
        $view->config->addRelatedReports($this->getOsRelatedReports());
    }

    private function configureViewForGetOSFamily(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->title = Piwik::translate('UserSettings_OperatingSystemFamily');
        $view->config->addTranslation('label', Piwik::translate('UserSettings_OperatingSystemFamily'));
        $view->config->addRelatedReports($this->getOsRelatedReports());
    }

    private function configureViewForGetBrowserVersion(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->title = Piwik::translate('UserSettings_ColumnBrowserVersion');
        $view->config->addTranslation('label', Piwik::translate('UserSettings_ColumnBrowserVersion'));
        $view->config->addRelatedReports($this->getBrowserRelatedReports());

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = 7;
        }
    }

    private function configureViewForGetBrowser(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->title = Piwik::translate('UserSettings_Browsers');
        $view->config->addTranslation('label', Piwik::translate('UserSettings_ColumnBrowser'));
        $view->config->addRelatedReports($this->getBrowserRelatedReports());

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = 7;
        }
    }

    private function configureViewForGetBrowserType(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->addTranslation('label', Piwik::translate('UserSettings_ColumnBrowserFamily'));
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_limit_control      = false;
    }

    private function configureViewForGetWideScreen(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->title = Piwik::translate('UserSettings_ColumnTypeOfScreen');
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_limit_control      = false;
        $view->config->addTranslation('label', Piwik::translate('UserSettings_ColumnTypeOfScreen'));
        $view->config->addRelatedReports($this->getWideScreenDeviceTypeRelatedReports());
    }

    private function configureViewForGetMobileVsDesktop(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->title = Piwik::translate('UserSettings_MobileVsDesktop');
        $view->config->addTranslation('label', Piwik::translate('UserSettings_MobileVsDesktop'));
        $view->config->addRelatedReports($this->getWideScreenDeviceTypeRelatedReports());
    }

    private function configureViewForGetPlugin(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->addTranslations(array(
            'label'                => Piwik::translate('General_Plugin'),
            'nb_visits_percentage' =>
            str_replace(' ', '&nbsp;', Piwik::translate('General_ColumnPercentageVisits'))
        ));

        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_limit_control      = false;
        $view->config->show_all_views_icons    = false;
        $view->config->show_table_all_columns  = false;
        $view->config->columns_to_display  = array('label', 'nb_visits_percentage', 'nb_visits');
        $view->config->show_footer_message = Piwik::translate('UserSettings_PluginDetectionDoesNotWorkInIE');

        $view->requestConfig->filter_sort_column = 'nb_visits_percentage';
        $view->requestConfig->filter_sort_order  = 'desc';
        $view->requestConfig->filter_limit       = 10;
    }

    private function configureViewForGetLanguage(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->columns_to_display = array('label', 'nb_visits');
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate('General_Language'));

        $view->requestConfig->filter_sort_column = 'nb_visits';
        $view->requestConfig->filter_sort_order  = 'desc';
    }

    private function getWideScreenDeviceTypeRelatedReports()
    {
        return array(
            'UserSettings.getMobileVsDesktop' => Piwik::translate('UserSettings_MobileVsDesktop'),
            'UserSettings.getWideScreen'      => Piwik::translate('UserSettings_ColumnTypeOfScreen')
        );
    }

    private function getBrowserRelatedReports()
    {
        return array(
            'UserSettings.getBrowser'        => Piwik::translate('UserSettings_Browsers'),
            'UserSettings.getBrowserVersion' => Piwik::translate('UserSettings_ColumnBrowserVersion')
        );
    }

    private function getOsRelatedReports()
    {
        return array(
            'UserSettings.getOSFamily' => Piwik::translate('UserSettings_OperatingSystemFamily'),
            'UserSettings.getOS'       => Piwik::translate('UserSettings_OperatingSystems')
        );
    }

    private function getBasicUserSettingsDisplayProperties(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;

        $view->requestConfig->filter_limit = 5;

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = 5;
        }
    }

    public function getRawReportMetadata()
    {
        return $this->reportMetadata;
    }

    /**
     * Registers reports metadata
     *
     * @param array $reports
     */
    public function getReportMetadata(&$reports)
    {
        $i = 0;
        foreach ($this->getRawReportMetadata() as $report) {
            list($category, $name, $apiModule, $apiAction, $columnName) = $report;
            if ($category == false) continue;

            $report = array(
                'category'  => Piwik::translate($category),
                'name'      => Piwik::translate($name),
                'module'    => $apiModule,
                'action'    => $apiAction,
                'dimension' => Piwik::translate($columnName),
                'order'     => $i++
            );

            $translation = $name . 'Documentation';
            $translated = Piwik::translate($translation, '<br />');
            if ($translated != $translation) {
                $report['documentation'] = $translated;
            }

            if ($apiAction == 'getMobileVsDesktop') {
                $report['constantRowsCount'] = true;
            }

            // getPlugin returns only a subset of metrics
            if ($apiAction == 'getPlugin') {
                $report['metrics'] = array(
                    'nb_visits',
                    'nb_visits_percentage' => Piwik::translate('General_ColumnPercentageVisits')
                );
                // There is no processedMetrics for this report
                $report['processedMetrics'] = array();
                // Always has same number of rows, 1 per plugin
                $report['constantRowsCount'] = true;
            }
            $reports[] = $report;
        }
    }

    /**
     * Get segments meta data
     */
    public function getSegmentsMetadata(&$segments)
    {
        foreach ($this->reportMetadata as $report) {
            @list($category, $name, $apiModule, $apiAction, $columnName, $segment, $sqlSegment, $acceptedValues) = $report;
            if (empty($segment)) continue;
            $segments[] = array(
                'type'           => 'dimension',
                'category'       => Piwik::translate('General_Visit'),
                'name'           => $columnName,
                'segment'        => $segment,
                'acceptedValues' => $acceptedValues,
                'sqlSegment'     => $sqlSegment
            );
        }
    }

}
