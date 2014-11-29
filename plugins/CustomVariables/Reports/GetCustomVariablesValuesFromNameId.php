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
use Piwik\Plugins\CustomVariables\Columns\CustomVariableValue;

class GetCustomVariablesValuesFromNameId extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new CustomVariableValue();
        $this->name          = Piwik::translate('CustomVariables_CustomVariables');
        $this->documentation = Piwik::translate('CustomVariables_CustomVariablesReportDocumentation',
            array('<br />', '<a href="http://piwik.org/docs/custom-variables/" rel="noreferrer"  target="_blank">', '</a>'));
        $this->isSubtableReport = true;
        $this->order = 15;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->columns_to_display = array('label', 'nb_actions', 'nb_visits');
        $view->config->show_goals  = true;
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate('CustomVariables_ColumnCustomVariableValue'));
        $view->requestConfig->filter_sort_column = 'nb_actions';
        $view->requestConfig->filter_sort_order  = 'desc';
    }

}
