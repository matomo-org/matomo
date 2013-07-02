<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CustomVariables
 */

/**
 * @package Piwik_CustomVariables
 */
class Piwik_CustomVariables_Controller extends Piwik_Controller
{
    public function index($fetch = false)
    {
        return Piwik_View::singleReport(
            Piwik_Translate('CustomVariables_CustomVariables'),
            $this->getCustomVariables(true), $fetch);
    }

    public function getCustomVariables($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, "CustomVariables.getCustomVariables", "getCustomVariablesValuesFromNameId");

        $this->setPeriodVariablesView($view);
        $this->setMetricsVariablesView($view);

        $this->configureView($view);
        $view->setColumnTranslation('label', Piwik_Translate('CustomVariables_ColumnCustomVariableName'));

        $view->setFooterMessage(Piwik_Translate('CustomVariables_TrackingHelp', array('<a target="_blank" href="http://piwik.org/docs/custom-variables/">', '</a>')));

        return $this->renderView($view, $fetch);
    }

    public function getCustomVariablesValuesFromNameId($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, 'CustomVariables.getCustomVariablesValuesFromNameId');

        $this->configureView($view);
        $view->disableSearchBox();
        $view->disableExcludeLowPopulation();
        $view->setColumnTranslation('label', Piwik_Translate('CustomVariables_ColumnCustomVariableValue'));
        return $this->renderView($view, $fetch);
    }

    protected function configureView($view)
    {
        $view->setColumnsToDisplay(array('label', 'nb_actions', 'nb_visits'));
        $view->setSortedColumn('nb_actions');
        $view->enableShowGoals();
    }


}

