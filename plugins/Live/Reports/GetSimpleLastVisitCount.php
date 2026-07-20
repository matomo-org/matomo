<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\Reports;

use Piwik\Config\GeneralConfig;
use Piwik\Plugins\Live\Controller;
use Piwik\Report\ReportWidgetFactory;
use Piwik\View;
use Piwik\Widget\WidgetsList;

class GetSimpleLastVisitCount extends Base
{
    protected function init()
    {
        parent::init();
        $this->categoryId = 'General_Visitors';
        $this->order = 3;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widget = $factory->createWidget()->setName('Live_RealTimeVisitorCount')->setOrder(15);
        $widgetsList->addWidgetConfig($widget);
    }

    public function render()
    {
        $view = new View('@Live/getSimpleLastVisitCount');
        $view->lastMinutes = GeneralConfig::getIntegerConfigValue(Controller::SIMPLE_VISIT_COUNT_WIDGET_LAST_MINUTES_CONFIG_KEY, 0);
        $view->refreshAfterXSecs = GeneralConfig::getIntegerConfigValue('live_widget_refresh_after_seconds', 0);

        return $view->render();
    }
}
