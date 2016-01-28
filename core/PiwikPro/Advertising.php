<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\PiwikPro;

use Piwik\Plugin;
use Piwik\Config;

/**
 * Piwik PRO Advertising related methods. Lets you for example check whether advertising is enabled, generate
 * links for differnt landing pages etc.
 *
 * @api
 * @since 2.16.0
 */
class Advertising
{
    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Plugin\Manager $pluginManager, Config $config)
    {
        $this->pluginManager = $pluginManager;
        $this->config = $config;
    }

    /**
     * Returns true if it is ok to show some Piwik PRO advertising in the Piwik UI.
     * @return bool
     */
    public function arePiwikProAdsEnabled()
    {
        if ($this->pluginManager->isPluginInstalled('EnterpriseAdmin')
            || $this->pluginManager->isPluginInstalled('LoginAdmin')
            || $this->pluginManager->isPluginInstalled('CloudAdmin')
            || $this->pluginManager->isPluginInstalled('WhiteLabel')) {
            return false;
        }

        $showAds = $this->config->General['piwik_pro_ads_enabled'];

        return !empty($showAds);
    }

    /**
     * Generates a link for promoting the Piwik Cloud.
     *
     * @param string $campaignMedium
     * @param string $campaignContent
     * @return string
     */
    public function getPromoLinkForCloud($campaignMedium, $campaignContent = '')
    {
        $url = 'https://piwik.pro/cloud/';
        $campaign = $this->getCampaignParametersForPromoLink($name = 'Upgrade_to_Cloud', $campaignMedium, $campaignContent);

        $url .= '?' . $campaign;

        return $url;
    }

    /**
     * Generates a link for promoting Piwik On Premises.
     * @param string $campaignMedium
     * @param string $campaignContent
     * @return string
     */
    public function getPromoLinkForOnPremises($campaignMedium, $campaignContent = '')
    {
        $url = 'https://piwik.pro/c/upgrade/';
        $campaign = $this->getCampaignParametersForPromoLink($name = 'Upgrade_to_Pro', $campaignMedium, $campaignContent);

        $url .= '?' . $campaign;

        return $url;
    }

    /**
     * Generates campaign URL parameters that can be used with any promotion link for Piwik PRO.
     *
     * @param string $campaignName
     * @param string $campaignMedium
     * @param string $content Optional
     * @return string URL parameters without a leading ? or &
     */
    public function getCampaignParametersForPromoLink($campaignName, $campaignMedium, $content = '')
    {
        $campaignName = sprintf('pk_campaign=%s&pk_medium=%s&pk_source=Piwik_App', $campaignName, $campaignMedium);

        if (!empty($content)) {
            $campaignName .= '&pk_content=' . $content;
        }

        return $campaignName;
    }
}
