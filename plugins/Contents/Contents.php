<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

use Piwik\Piwik;

class Contents extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Metrics.getDefaultMetricTranslations' => 'addMetricTranslations',
            'Metrics.getDefaultMetricDocumentationTranslations' => 'addMetricDocumentationTranslations',
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

    public function addMetricDocumentationTranslations(&$translations)
    {
        $translations['nb_impressions'] = Piwik::translate('Contents_ImpressionsMetricDocumentation');
        $translations['nb_interactions'] = Piwik::translate('Contents_InteractionsMetricDocumentation');
    }
}
