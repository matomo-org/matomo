<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live\Reports;

use Piwik\Menu\MenuReporting;
use Piwik\Plugin\Report;
use Piwik\Plugins\Live\VisitorLog;
use Piwik\WidgetsList;

class GetLastVisitsDetails extends Base
{
    protected function init()
    {
        parent::init();
        $this->widgetTitle = 'Live_VisitorLog';
        $this->order = 2;
    }

    public function getDefaultTypeViewDataTable()
    {
        return VisitorLog::ID;
    }

    public function configureReportingMenu(MenuReporting $menu)
    {
        if ($this->isEnabled()) {
            $url = array('module' => $this->module, 'action' => 'indexVisitorLog');
            $menu->addVisitorsItem($this->widgetTitle, $url, $order = 5);
        }
    }

    public function configureWidget(WidgetsList $widget)
    {
        $widget->add($this->category, $this->widgetTitle, $this->module, 'getVisitorLog', array('small' => 1));
    }

}
