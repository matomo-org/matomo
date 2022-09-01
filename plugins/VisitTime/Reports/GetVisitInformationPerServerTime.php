<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreHome\Columns\VisitFirstActionTime;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;

class GetVisitInformationPerServerTime extends Base
{

    protected $defaultSortColumn = '';

    protected function init()
    {
        parent::init();
        $this->dimension     = new VisitFirstActionTime();
        $this->name          = Piwik::translate('VisitTime_SiteTime');
        $this->documentation = Piwik::translate('VisitTime_WidgetSiteTimeDocumentation', array('<strong>', '</strong>'));
        $this->constantRowsCount = true;
        $this->hasGoalMetrics = true;
        $this->order = 20;

        $this->subcategoryId = 'VisitTime_SubmenuTimes';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->setBasicConfigViewProperties($view);

        $view->requestConfig->filter_limit = 24;
        $view->requestConfig->request_parameters_to_modify['hideFutureHoursWhenToday'] = 1;

        $view->config->addTranslation('label', Piwik::translate('VisitTime_ColumnServerHour'));

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = false;
        }
    }
}
