<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\Reports;

use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Live\Visualizations\VisitorsInRealTime;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

class GetVisitorsInRealTime extends Base
{

    protected function init()
    {
        parent::init();
    }

    public function getDefaultTypeViewDataTable()
    {
        return VisitorsInRealTime::ID;
    }

    public function alwaysUseDefaultViewDataTable()
    {
        return true;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->requestConfig->apiMethodToRequestDataTable = 'Live.getLastVisitsDetails';
    }

    public function render()
    {
        $view = ViewDataTableFactory::build(null, 'Live.getVisitorsInRealTime', 'Live.getVisitorsInRealTime');

        $rendered  = $view->render();

        return $rendered;
    }

}