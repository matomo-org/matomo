<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package MultiSites
 */
namespace Piwik\Plugins\MultiSites;


/**
 *
 * @package MultiSites
 */
class MultiSites extends \Piwik\Plugin
{
    public function getInformation()
    {
        $info = parent::getInformation();
        $info['author'] = 'Piwik PRO';
        $info['author_homepage'] = 'http://piwik.pro';
        return $info;
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'Menu.Top.addItems'               => 'addTopMenu',
            'API.getReportMetadata'           => 'getReportMetadata',
        );
    }

    public function getReportMetadata(&$reports)
    {
        $metadataMetrics = array();
        foreach (API::getApiMetrics($enhanced = true) as $metricName => $metricSettings) {
            $metadataMetrics[$metricName] =
                Piwik_Translate($metricSettings[API::METRIC_TRANSLATION_KEY]);
            $metadataMetrics[$metricSettings[API::METRIC_EVOLUTION_COL_NAME_KEY]] =
                Piwik_Translate($metricSettings[API::METRIC_TRANSLATION_KEY]) . " " . Piwik_Translate('MultiSites_Evolution');
        }

        $reports[] = array(
            'category'          => Piwik_Translate('General_MultiSitesSummary'),
            'name'              => Piwik_Translate('General_AllWebsitesDashboard'),
            'module'            => 'MultiSites',
            'action'            => 'getAll',
            'dimension'         => Piwik_Translate('General_Website'), // re-using translation
            'metrics'           => $metadataMetrics,
            'processedMetrics'  => false,
            'constantRowsCount' => false,
            'order'             => 5
        );

        $reports[] = array(
            'category'          => Piwik_Translate('General_MultiSitesSummary'),
            'name'              => Piwik_Translate('General_SingleWebsitesDashboard'),
            'module'            => 'MultiSites',
            'action'            => 'getOne',
            'dimension'         => Piwik_Translate('General_Website'), // re-using translation
            'metrics'           => $metadataMetrics,
            'processedMetrics'  => false,
            'constantRowsCount' => false,
            'order'             => 5
        );
    }

    public function addTopMenu()
    {
        $urlParams = array('module' => 'MultiSites', 'action' => 'index', 'segment' => false);
        $tooltip = Piwik_Translate('MultiSites_TopLinkTooltip');
        Piwik_AddTopMenu('General_MultiSitesSummary', $urlParams, true, 3, $isHTML = false, $tooltip);
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/MultiSites/javascripts/multiSites.js";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/MultiSites/stylesheets/multiSites.less";
    }
}
