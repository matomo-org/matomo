<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables\Reports;

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
    }
}
