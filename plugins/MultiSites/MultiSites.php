<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_MultiSites
 */

/**
 *
 * @package Piwik_MultiSites
 */
class Piwik_MultiSites extends Piwik_Plugin
{
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('MultiSites_PluginDescription'),
            'author'          => 'ClearCode.cc',
            'author_homepage' => "http://clearcode.cc/",
            'version'         => Piwik_Version::VERSION,
        );
    }

    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles' => 'getCssFiles',
            'AssetManager.getJsFiles'  => 'getJsFiles',
            'TopMenu.add'              => 'addTopMenu',
            'API.getReportMetadata'    => 'getReportMetadata',
        );
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getReportMetadata($notification)
    {
        $metadataMetrics = array();
        foreach (Piwik_MultiSites_API::getApiMetrics($enhanced = true) as $metricName => $metricSettings) {
            $metadataMetrics[$metricName] =
                Piwik_Translate($metricSettings[Piwik_MultiSites_API::METRIC_TRANSLATION_KEY]);
            $metadataMetrics[$metricSettings[Piwik_MultiSites_API::METRIC_EVOLUTION_COL_NAME_KEY]] =
                Piwik_Translate($metricSettings[Piwik_MultiSites_API::METRIC_TRANSLATION_KEY]) . " " . Piwik_Translate('MultiSites_Evolution');
        }

        $reports = & $notification->getNotificationObject();

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

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();

        $jsFiles[] = "plugins/MultiSites/templates/common.js";
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getCssFiles($notification)
    {
        $cssFiles = & $notification->getNotificationObject();

        $cssFiles[] = "plugins/MultiSites/templates/styles.css";
    }
}
