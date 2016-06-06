<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables\Reports;

use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CustomVariables\Columns\CustomVariableName;

class GetCustomVariables extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new CustomVariableName();
        $this->name          = Piwik::translate('CustomVariables_CustomVariables');
        $this->documentation = Piwik::translate('CustomVariables_CustomVariablesReportDocumentation',
                               array('<br />', '<a href="http://piwik.org/docs/custom-variables/" rel="noreferrer"  target="_blank">', '</a>'));
        $this->actionToLoadSubTables = 'getCustomVariablesValuesFromNameId';
        $this->order = 10;
        $this->widgetTitle  = 'CustomVariables_CustomVariables';
        $this->menuTitle    = 'CustomVariables_CustomVariables';
        $this->hasGoalMetrics = true;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->columns_to_display = array('label', 'nb_actions', 'nb_visits');
        $view->config->addTranslation('label', Piwik::translate('CustomVariables_ColumnCustomVariableName'));
        $view->requestConfig->filter_sort_column = 'nb_actions';
        $view->requestConfig->filter_sort_order  = 'desc';

        $that = $this;
        $view->config->filters[] = function (DataTable $table) use ($view, $that) {
            if($that->isReportContainsUnsetVisitsColumns($table)) {
                $message = $that->getFooterMessageExplanationMissingMetrics();
                $view->config->show_footer_message = $message;
            }
        };
    }

    /**
     * @return array
     */
    public function getFooterMessageExplanationMissingMetrics()
    {
        $metrics = sprintf("'%s', '%s' %s '%s'",
            Piwik::translate('General_ColumnNbVisits'),
            Piwik::translate('General_ColumnNbUniqVisitors'),
            Piwik::translate('General_And'),
            Piwik::translate('General_ColumnNbUsers')
        );
        $messageStart = Piwik::translate('CustomVariables_MetricsAreOnlyAvailableForVisitScope', array($metrics, "'visit'"));

        $messageEnd = Piwik::translate('CustomVariables_MetricsNotAvailableForPageScope', array("'page'", '\'-\''));

        return $messageStart . ' ' . $messageEnd;
    }

    /**
     * @return bool
     */
    public function isReportContainsUnsetVisitsColumns(DataTable $report)
    {
        $visits = $report->getColumn('nb_visits');
        $isVisitsMetricsSometimesUnset = in_array(false, $visits);
        return $isVisitsMetricsSometimesUnset;
    }
}
