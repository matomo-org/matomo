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

class GetLastVisitsDetails extends Base
{
    protected $defaultSortColumn = '';

    protected function init()
    {
        parent::init();
        $this->order = 2;
        $this->menuTitle = 'Live_VisitorLog';

        $this->createWidget()
             ->setName('Live_VisitorLog')
             ->setAction('getVisitorLog')
             ->setOrder(10)
             ->setParameters(array('small' => 1));
    }

    public function getDefaultTypeViewDataTable()
    {
        return VisitorLog::ID;
    }

    public function alwaysUseDefaultViewDataTable()
    {
        return true;
    }

    public function configureReportingMenu(MenuReporting $menu)
    {
        if ($this->isEnabled()) {
            $url = array('module' => $this->module, 'action' => 'indexVisitorLog');
            $menu->addVisitorsItem($this->menuTitle, $url, $order = 5);
        }
    }

}
