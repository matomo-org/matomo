<?php
/**
 * Piwik - Open source web analytics
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
        );
        return $hooks;
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
