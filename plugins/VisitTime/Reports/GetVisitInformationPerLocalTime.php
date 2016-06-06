<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\VisitTime\Columns\LocalTime;

class GetVisitInformationPerLocalTime extends Base
{

    protected $defaultSortColumn = '';

    protected function init()
    {
        parent::init();
        $this->dimension     = new LocalTime();
        $this->name          = Piwik::translate('VisitTime_WidgetLocalTime');
        $this->documentation = Piwik::translate('VisitTime_WidgetLocalTimeDocumentation', array('<strong>', '</strong>'));
        $this->constantRowsCount = true;
        $this->order = 20;
        $this->widgetTitle  = 'VisitTime_WidgetLocalTime';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->setBasicConfigViewProperties($view);

        $view->requestConfig->filter_limit = 24;

        $view->config->title = Piwik::translate('VisitTime_ColumnLocalTime');
        $view->config->addTranslation('label', Piwik::translate('VisitTime_LocalTime'));

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->max_graph_elements = false;
        }

        // add the visits by day of week as a related report, if the current period is not 'day'
        if (Common::getRequestVar('period', 'day') != 'day') {
            $view->config->addRelatedReport('VisitTime.getByDayOfWeek', Piwik::translate('VisitTime_VisitsByDayOfWeek'));
        }
    }
}
