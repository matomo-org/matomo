<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;

abstract class Base extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->category = 'UserSettings_VisitorSettings';
    }

    protected function getBasicUserSettingsDisplayProperties(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;

        $view->requestConfig->filter_limit = 5;

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = 5;
        }
    }

    protected function getBrowserRelatedReports()
    {
        return array(
            'UserSettings.getBrowser'        => Piwik::translate('UserSettings_Browsers'),
            'UserSettings.getBrowserVersion' => Piwik::translate('UserSettings_ColumnBrowserVersion')
        );
    }

    protected function getOsRelatedReports()
    {
        return array(
            'UserSettings.getOSFamily' => Piwik::translate('UserSettings_OperatingSystemFamily'),
            'UserSettings.getOS'       => Piwik::translate('UserSettings_OperatingSystems')
        );
    }

    protected function getWideScreenDeviceTypeRelatedReports()
    {
        return array(
            'UserSettings.getMobileVsDesktop' => Piwik::translate('UserSettings_MobileVsDesktop'),
            'UserSettings.getWideScreen'      => Piwik::translate('UserSettings_ColumnTypeOfScreen')
        );
    }
}
