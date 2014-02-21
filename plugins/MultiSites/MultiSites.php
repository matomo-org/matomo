<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;


/**
 *
 */
class MultiSites extends \Piwik\Plugin
{
    public function getInformation()
    {
        $info = parent::getInformation();
        $info['authors'] = array(array('name' => 'Piwik PRO', 'homepage' => 'http://piwik.pro'));
        return $info;
    }

    /**
     * @see Piwik\Plugin::getListHooksRegistered
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
                Piwik::translate($metricSettings[API::METRIC_TRANSLATION_KEY]);
            $metadataMetrics[$metricSettings[API::METRIC_EVOLUTION_COL_NAME_KEY]] =
                Piwik::translate($metricSettings[API::METRIC_TRANSLATION_KEY]) . " " . Piwik::translate('MultiSites_Evolution');
        }

        $reports[] = array(
            'category'          => Piwik::translate('General_MultiSitesSummary'),
            'name'              => Piwik::translate('General_AllWebsitesDashboard'),
            'module'            => 'MultiSites',
            'action'            => 'getAll',
            'dimension'         => Piwik::translate('General_Website'), // re-using translation
            'metrics'           => $metadataMetrics,
            'processedMetrics'  => false,
            'constantRowsCount' => false,
            'order'             => 4
        );

        $reports[] = array(
            'category'          => Piwik::translate('General_MultiSitesSummary'),
            'name'              => Piwik::translate('General_SingleWebsitesDashboard'),
            'module'            => 'MultiSites',
            'action'            => 'getOne',
            'dimension'         => Piwik::translate('General_Website'), // re-using translation
            'metrics'           => $metadataMetrics,
            'processedMetrics'  => false,
            'constantRowsCount' => false,
            'order'             => 5
        );
    }

    public function addTopMenu()
    {
        $urlParams = array('module' => 'MultiSites', 'action' => 'index', 'segment' => false);
        $tooltip = Piwik::translate('MultiSites_TopLinkTooltip');
        MenuTop::addEntry('General_MultiSitesSummary', $urlParams, true, 3, $isHTML = false, $tooltip);
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
