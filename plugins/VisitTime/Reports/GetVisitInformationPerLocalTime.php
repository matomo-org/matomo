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
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\VisitTime\Columns\LocalTime;
use Piwik\Plugin\ReportsProvider;

class GetVisitInformationPerLocalTime extends Base
{

    protected $defaultSortColumn = '';

    protected function init()
    {
        parent::init();
        $this->dimension     = new LocalTime();
        $this->name          = Piwik::translate('VisitTime_LocalTime');
        $this->documentation = Piwik::translate('VisitTime_WidgetLocalTimeDocumentation', array('<strong>', '</strong>'));
        $this->constantRowsCount = true;
        $this->order = 15;

        $this->subcategoryId = 'VisitTime_SubmenuTimes';
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
    }

    public function getRelatedReports()
    {
        return array(
            ReportsProvider::factory('VisitTime', 'getByDayOfWeek')
        );
    }
}
