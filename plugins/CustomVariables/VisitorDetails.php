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

    public function extendActionDetails(&$action)
    {
        $maxCustomVariables = CustomVariables::getNumUsableCustomVariables();
        $customVariablesPage = array();

        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            if (!empty($action['custom_var_k' . $i])) {
                $cvarKey = $action['custom_var_k' . $i];
                $cvarKey = static::getCustomVariablePrettyKey($cvarKey);
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
}