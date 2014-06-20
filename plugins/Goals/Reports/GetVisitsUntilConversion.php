<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Goals\Columns\VisitsUntilConversion;
use Piwik\Plugins\Goals\Archiver;

class GetVisitsUntilConversion extends BaseGoal
{
    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('Goals_VisitsUntilConv');
        $this->dimension = new VisitsUntilConversion();
        $this->constantRowsCount = true;
        $this->processedMetrics = false;
        $this->parameters = array();
        $this->metrics  = array('nb_conversions');
        $this->order = 5;
        $this->orderGoal = 51;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_table_all_columns  = false;
        $view->config->columns_to_display      = array('label', 'nb_conversions');
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_all_views_icons  = false;

        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order  = 'asc';
        $view->requestConfig->filter_limit       = count(Archiver::$visitCountRanges);

        $view->config->addTranslations(array('label' => $this->dimension->getName()));
    }

    public function configureReportMetadata(&$availableReports, $infos)
    {
        if (!$this->isEnabled()) {
            return;
        }

        if (null !== $this->getIdSiteFromInfos($infos)) {
            parent::configureReportMetadata($availableReports, $infos);
        }

        $name = $this->name;

        $this->addReportMetadataForEachGoal($availableReports, $infos, function ($goal) use ($name) {
            return $goal['name'] . ' - ' . $name;
        });
    }

}
