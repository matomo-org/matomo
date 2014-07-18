<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\VisitTime\Columns\ServerTime;

class GetVisitInformationPerServerTime extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new ServerTime();
        $this->name          = Piwik::translate('VisitTime_WidgetServerTime');
        $this->documentation = Piwik::translate('VisitTime_WidgetServerTimeDocumentation', array('<strong>', '</strong>'));
        $this->constantRowsCount = true;
        $this->hasGoalMetrics = true;
        $this->order = 15;
        $this->widgetTitle  = 'VisitTime_WidgetServerTime';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->setBasicConfigViewProperties($view);

        $view->requestConfig->filter_limit = 24;
        $view->requestConfig->request_parameters_to_modify['hideFutureHoursWhenToday'] = 1;

        $view->config->addTranslation('label', $this->dimension->getName());

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = false;
        }
    }
}
