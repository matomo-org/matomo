<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\Tracker\Cache;

class CustomVariables extends \Piwik\Plugin
{
    const MAX_NUM_CUSTOMVARS_CACHEKEY = 'CustomVariables.MaxNumCustomVariables';

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'AssetManager.getStylesheetFiles'  => 'getStylesheetFiles',
        );
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

    /**
     * Returns the number of available custom variables that can be used.
     *
     * "Can be used" is identifed by the minimum number of available custom variables across all relevant tables. Eg
     * if there are 6 custom variables installed in log_visit but only 5 in log_conversion, we consider only 5 custom
     * variables as usable.
     * @return int
     */
    public static function getNumUsableCustomVariables()
    {
        $cache    = Cache::getCacheGeneral();
        $cacheKey = self::MAX_NUM_CUSTOMVARS_CACHEKEY;

        if (!array_key_exists($cacheKey, $cache)) {

            $minCustomVar = null;

            foreach (Model::getScopes() as $scope) {
                $model = new Model($scope);
                $highestIndex = $model->getHighestCustomVarIndex();

                if (!isset($minCustomVar)) {
                    $minCustomVar = $highestIndex;
                }

                if ($highestIndex < $minCustomVar) {
                    $minCustomVar = $highestIndex;
                }
            }

            if (!isset($minCustomVar)) {
                $minCustomVar = 0;
            }

            $cache[$cacheKey] = $minCustomVar;
            Cache::setCacheGeneral($cache);
        }

        return $cache[$cacheKey];
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'CustomVariables_CustomVariables';
        $translationKeys[] = 'CustomVariables_ManageDescription';
        $translationKeys[] = 'CustomVariables_ScopeX';
        $translationKeys[] = 'CustomVariables_Index';
        $translationKeys[] = 'CustomVariables_Usages';
        $translationKeys[] = 'CustomVariables_Unused';
        $translationKeys[] = 'CustomVariables_CreateNewSlot';
        $translationKeys[] = 'CustomVariables_UsageDetails';
        $translationKeys[] = 'CustomVariables_CurrentAvailableCustomVariables';
        $translationKeys[] = 'CustomVariables_ToCreateCustomVarExecute';
        $translationKeys[] = 'CustomVariables_CreatingCustomVariableTakesTime';
        $translationKeys[] = 'CustomVariables_SlotsReportIsGeneratedOverTime';
        $translationKeys[] = 'General_Loading';
        $translationKeys[] = 'General_TrackingScopeVisit';
        $translationKeys[] = 'General_TrackingScopePage';
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/CustomVariables/angularjs/manage-custom-vars/manage-custom-vars.directive.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/CustomVariables/angularjs/manage-custom-vars/manage-custom-vars.model.js";
        $jsFiles[] = "plugins/CustomVariables/angularjs/manage-custom-vars/manage-custom-vars.controller.js";
        $jsFiles[] = "plugins/CustomVariables/angularjs/manage-custom-vars/manage-custom-vars.directive.js";
    }

}
