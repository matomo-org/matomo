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
        return Piwik_ViewDataTable::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getCustomVariablesValuesFromNameId($fetch = false)
    {
        return Piwik_ViewDataTable::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }
}

