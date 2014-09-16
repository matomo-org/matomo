<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

class Contents extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Metrics.getDefaultMetricTranslations' => 'addMetricTranslations',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
        );
    }

    public function addMetricTranslations(&$translations)
    {
        $translations['nb_impressions']   = 'Contents_Impressions';
        $translations['nb_interactions']  = 'Contents_Interactions';
        $translations['interaction_rate'] = 'Contents_InteractionRate';
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Contents/javascripts/contentsDataTable.js";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Contents/stylesheets/datatable.less";
    }

}
