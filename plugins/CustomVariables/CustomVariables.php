<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\ArchiveProcessor;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Tracker\Cache;
use Piwik\Tracker;

/**
 */
class CustomVariables extends \Piwik\Plugin
{
    public function getInformation()
    {
        $info = parent::getInformation();
        $info['description'] .= ' <br/>Required to use <a href="http://piwik.org/docs/ecommerce-analytics/">Ecommerce Analytics</a> feature!';
        return $info;
    }

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'Goals.getReportsWithGoalMetrics' => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'           => 'getReportMetadata',
            'API.getSegmentDimensionMetadata' => 'getSegmentsMetadata',
            'ViewDataTable.configure'         => 'configureViewDataTable'
        );
        return $hooks;
    }

    public function install()
    {
        Model::install();
    }

    public function uninstall()
    {
        Model::uninstall();
    }

    /**
     * There are also some hardcoded places in JavaScript
     * @return int
     */
    public static function getMaxLengthCustomVariables()
    {
        return 200;
    }

    public static function getMaxCustomVariables()
    {
        $cache    = Cache::getCacheGeneral();
        $cacheKey = 'CustomVariables.MaxNumCustomVariables';

        if (!array_key_exists($cacheKey, $cache)) {

            $maxCustomVar = 0;

            foreach (Model::getScopes() as $scope) {
                $model = new Model($scope);
                $highestIndex = $model->getHighestCustomVarIndex();

                if ($highestIndex > $maxCustomVar) {
                    $maxCustomVar = $highestIndex;
                }
            }

            $cache[$cacheKey] = $maxCustomVar;
            Cache::setCacheGeneral($cache);
        }

        return $cache[$cacheKey];
    }

    /**
     * Returns metadata for available reports
     */
    public function getReportMetadata(&$reports)
    {
        $documentation = Piwik::translate('CustomVariables_CustomVariablesReportDocumentation',
            array('<br />', '<a href="http://piwik.org/docs/custom-variables/" target="_blank">', '</a>'));

        $reports[] = array('category'              => Piwik::translate('General_Visitors'),
                           'name'                  => Piwik::translate('CustomVariables_CustomVariables'),
                           'module'                => 'CustomVariables',
                           'action'                => 'getCustomVariables',
                           'actionToLoadSubTables' => 'getCustomVariablesValuesFromNameId',
                           'dimension'             => Piwik::translate('CustomVariables_ColumnCustomVariableName'),
                           'documentation'         => $documentation,
                           'order'                 => 10);

        $reports[] = array('category'         => Piwik::translate('General_Visitors'),
                           'name'             => Piwik::translate('CustomVariables_CustomVariables'),
                           'module'           => 'CustomVariables',
                           'action'           => 'getCustomVariablesValuesFromNameId',
                           'dimension'        => Piwik::translate('CustomVariables_ColumnCustomVariableValue'),
                           'documentation'    => $documentation,
                           'isSubtableReport' => true,
                           'order'            => 15);
    }

    public function getSegmentsMetadata(&$segments)
    {
        $maxCustomVariables = self::getMaxCustomVariables();

        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik::translate('CustomVariables_ColumnCustomVariableName') . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopeVisit') . ')',
                'segment'    => 'customVariableName' . $i,
                'sqlSegment' => 'log_visit.custom_var_k' . $i,
            );
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik::translate('CustomVariables_ColumnCustomVariableValue') . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopeVisit') . ')',
                'segment'    => 'customVariableValue' . $i,
                'sqlSegment' => 'log_visit.custom_var_v' . $i,
            );
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik::translate('CustomVariables_ColumnCustomVariableName') . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopePage') . ')',
                'segment'    => 'customVariablePageName' . $i,
                'sqlSegment' => 'log_link_visit_action.custom_var_k' . $i,
            );
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik::translate('CustomVariables_ColumnCustomVariableValue') . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopePage') . ')',
                'segment'    => 'customVariablePageValue' . $i,
                'sqlSegment' => 'log_link_visit_action.custom_var_v' . $i,
            );
        }
    }

    /**
     * Adds Goal dimensions, so that the dimensions are displayed in the UI Goal Overview page
     */
    public function getReportsWithGoalMetrics(&$dimensions)
    {
        $dimensions[] = array('category' => Piwik::translate('General_Visit'),
                              'name'     => Piwik::translate('CustomVariables_CustomVariables'),
                              'module'   => 'CustomVariables',
                              'action'   => 'getCustomVariables',
        );
    }

    public function configureViewDataTable(ViewDataTable $view)
    {
        switch ($view->requestConfig->apiMethodToRequestDataTable) {
            case 'CustomVariables.getCustomVariables':
                $this->configureViewForGetCustomVariables($view);
                break;
            case 'CustomVariables.getCustomVariablesValuesFromNameId':
                $this->configureViewForGetCustomVariablesValuesFromNameId($view);
                break;
        }
    }

    private function configureViewForGetCustomVariables(ViewDataTable $view)
    {
        $view->config->columns_to_display = array('label', 'nb_actions', 'nb_visits');
        $view->config->show_goals = true;
        $view->config->subtable_controller_action = 'getCustomVariablesValuesFromNameId';
        $view->config->addTranslation('label', Piwik::translate('CustomVariables_ColumnCustomVariableName'));
        $view->requestConfig->filter_sort_column = 'nb_actions';
        $view->requestConfig->filter_sort_order  = 'desc';
    }

    private function configureViewForGetCustomVariablesValuesFromNameId(ViewDataTable $view)
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
