<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary\Widgets;

use Piwik\Plugin\ReportsProvider;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class Index extends \Piwik\Widget\WidgetContainerConfig
{
    protected $categoryId = 'General_Visitors';
    protected $name = 'VisitsSummary_WidgetOverviewGraph';
    protected $id = 'VisitOverviewWithGraph';
    protected $isWidgetizable = true;

    public function isEnabled()
    {
        return ReportsProvider::factory('VisitsSummary', 'get')->isEnabled();
    }

    public function getWidgetConfigs()
    {
        $report  = ReportsProvider::factory('VisitsSummary', 'get');

        $factory = new ReportWidgetFactory($report);
        $widgets = array();

        $list = new WidgetsList();
        $report->configureWidgets($list, $factory);

        foreach ($list->getWidgetConfigs() as $config) {
            $config->setIsNotWidgetizable();
            $widgets[] = $config;
        }

        return $widgets;
    }
}
