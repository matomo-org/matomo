<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\Piwik;
use Piwik\Plugins\Actions\Actions\ActionSiteSearch;
use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\View;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $customVariables = array();

        $maxCustomVariables = CustomVariables::getNumUsableCustomVariables();

        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            if (!empty($this->details['custom_var_k' . $i])) {
                $customVariables[$i] = array(
                    'customVariableName' . $i  => $this->details['custom_var_k' . $i],
                    'customVariableValue' . $i => $this->details['custom_var_v' . $i],
                );
            }
        }

        $visitor['customVariables'] = $customVariables;
    }

    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
        $maxCustomVariables  = CustomVariables::getNumUsableCustomVariables();
        $customVariablesPage = array();

        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            if (!empty($action['custom_var_k' . $i])) {
                $cvarKey                 = $action['custom_var_k' . $i];
                $cvarKey                 = static::getCustomVariablePrettyKey($cvarKey);
                $customVariablesPage[$i] = array(
                    'customVariablePageName' . $i  => $cvarKey,
                    'customVariablePageValue' . $i => $action['custom_var_v' . $i],
                );
            }
            unset($action['custom_var_k' . $i]);
            unset($action['custom_var_v' . $i]);
        }
        if (!empty($customVariablesPage)) {
            $action['customVariables'] = $customVariablesPage;
        }
    }

    public function renderActionTooltip($action, $visitInfo)
    {
        if (empty($action['customVariables'])) {
            return [];
        }

        $view         = new View('@CustomVariables/_actionTooltip');
        $view->action = $action;
        return [[ 40, $view->render() ]];
    }

    public function renderVisitorDetails($visitInfo)
    {
        if (empty($visitInfo['customVariables'])) {
            return [];
        }

        $view            = new View('@CustomVariables/_visitorDetails');
        $view->visitInfo = $visitInfo;
        return [[ 50, $view->render() ]];
    }

    private static function getCustomVariablePrettyKey($key)
    {
        $rename = array(
            ActionSiteSearch::CVAR_KEY_SEARCH_CATEGORY => Piwik::translate('Actions_ColumnSearchCategory'),
            ActionSiteSearch::CVAR_KEY_SEARCH_COUNT    => Piwik::translate('Actions_ColumnSearchResultsCount'),
        );
        if (isset($rename[$key])) {
            return $rename[$key];
        }
        return $key;
    }


    protected $customVariables = [];

    public function initProfile($visits, &$profile)
    {
        $this->customVariables = [
            Model::SCOPE_PAGE => [],
            Model::SCOPE_VISIT  => [],
        ];
    }

    public function handleProfileAction($action, &$profile)
    {
        if (empty($action['customVariables'])) {
            return;
        }

        foreach ($action['customVariables'] as $index => $customVariable) {

            $scope = Model::SCOPE_PAGE;
            $name = $customVariable['customVariablePageName'.$index];
            $value = $customVariable['customVariablePageValue'.$index];

            if (empty($value)) {
                continue;
            }

            if (!array_key_exists($name, $this->customVariables[$scope])) {
                $this->customVariables[$scope][$name] = [];
            }

            if (!array_key_exists($value, $this->customVariables[$scope][$name])) {
                $this->customVariables[$scope][$name][$value] = 0;
            }

            $this->customVariables[$scope][$name][$value]++;
        }
    }

    public function handleProfileVisit($visit, &$profile)
    {
        if (empty($visit['customVariables'])) {
            return;
        }

        foreach ($visit['customVariables'] as $index => $customVariable) {

            $scope = Model::SCOPE_VISIT;
            $name = $customVariable['customVariableName'.$index];
            $value = $customVariable['customVariableValue'.$index];

            if (empty($value)) {
                continue;
            }

            if (!array_key_exists($name, $this->customVariables[$scope])) {
                $this->customVariables[$scope][$name] = [];
            }

            if (!array_key_exists($value, $this->customVariables[$scope][$name])) {
                $this->customVariables[$scope][$name][$value] = 0;
            }

            $this->customVariables[$scope][$name][$value]++;
        }
    }

    public function finalizeProfile($visits, &$profile)
    {
        $customVariables = $this->customVariables;
        foreach ($customVariables as $scope => &$variables) {

            if (empty($variables)) {
                unset($customVariables[$scope]);
                continue;
            }

            foreach ($variables AS $name => &$values) {
                arsort($values);
            }
        }
        if (!empty($customVariables)) {

            $profile['customVariables'] = $this->convertForProfile($customVariables);
        }
    }

    protected function convertForProfile($customVariables)
    {
        $convertedVariables = [];

        foreach ($customVariables as $scope => $scopeVariables) {

            $convertedVariables[$scope] = [];

            foreach ($scopeVariables as $name => $values) {

                $variable = [
                    'name' => $name,
                    'values' => []
                ];

                foreach ($values as $value => $count) {
                    $variable['values'][] = [
                        'value' => $value,
                        'count' => $count
                    ];
                }

                $convertedVariables[$scope][] = $variable;
            }
        }

        return $convertedVariables;
    }
}